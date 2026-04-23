<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\CepQueryLog;
use App\Services\CartService;
use App\Services\ShippingService;
use App\Services\PaymentService;
use App\Services\ViaCepService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller de Frete e Entregas (AJAX).
 *
 * Endpoints usados no checkout para:
 * - Verificar se CEP é atendido
 * - Calcular valor do frete
 * - Listar períodos de entrega disponíveis
 * - Listar lojas para retirada
 */
class ShippingController extends Controller
{
    protected ShippingService $shippingService;

    public function __construct(ShippingService $shippingService)
    {
        $this->shippingService = $shippingService;
    }

    /**
     * Consulta CEP: verifica se é atendido e retorna região/loja.
     * Registra a consulta no log de estatísticas (banco sync).
     * GET /entrega/consultar-cep?cep=20551030
     */
    public function consultarCep(Request $request): JsonResponse
    {
        $cep = $request->input('cep', '');
        $result = $this->shippingService->lookupCep($cep);

        $atendido = $result !== null && $result['loja'] !== null;

        // Enriquece com dados de localização via ViaCEP (cache 24h)
        $viaCep = app(ViaCepService::class)->lookup($cep);

        // Registra consulta no log de estatísticas (banco sync)
        CepQueryLog::create([
            'cep'        => preg_replace('/\D/', '', $cep),
            'atendido'   => $atendido,
            'estado'     => $viaCep['uf'] ?? null,
            'cidade'     => $viaCep['localidade'] ?? null,
            'bairro'     => $viaCep['bairro'] ?? null,
            'regiao_id'  => $atendido ? $result['regiao']?->id : null,
            'loja_id'    => $atendido ? $result['loja']?->id : null,
            'created_at' => now(),
        ]);

        if (!$atendido) {
            return response()->json([
                'atendido' => false,
                'mensagem' => 'Infelizmente ainda não atendemos sua região. Registramos seu interesse para futura expansão.',
            ]);
        }

        return response()->json([
            'atendido' => true,
            'regiao'   => $result['regiao']?->nome,
            'loja'     => $result['loja'] ? [
                'id'   => $result['loja']->id,
                'nome' => $result['loja']->nome,
            ] : null,
            'endereco' => $viaCep ? ($viaCep['bairro'] . ', ' . $viaCep['localidade'] . ' - ' . $viaCep['uf']) : null,
        ]);
    }

    /**
     * Calcula frete para um CEP.
     * POST /entrega/calcular-frete
     */
    public function calcularFrete(Request $request): JsonResponse
    {
        $request->validate([
            'cep' => 'required|string|min:8',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $cep = $request->input('cep');
        $subtotal = (float) $request->input('subtotal');
        $formaPagamentoId = $request->input('formas_pagamento_id') ? (int) $request->input('formas_pagamento_id') : null;

        $frete = $this->shippingService->calculateShipping($cep, $subtotal, $formaPagamentoId);

        return response()->json([
            'valor'           => $frete['valor'],
            'valor_original'  => $frete['valor_original'],
            'valor_formatado' => 'R$ ' . number_format($frete['valor'], 2, ',', '.'),
            'frete_gratis'    => $frete['valor'] == 0,
            'distancia_km'    => $frete['distancia_km'],
            'motivo'          => $frete['motivo'],
        ]);
    }

    /**
     * Retorna períodos de entrega disponíveis para um CEP.
     * Aplica regras de logística (regras_entregas) baseado no valor do carrinho.
     * GET /entrega/periodos?cep=20551030
     */
    public function periodos(Request $request): JsonResponse
    {
        $cep = $request->input('cep', '');

        // Valor do carrinho e produtos (server-side, não confia em input do cliente)
        $cartService = app(CartService::class);
        $orderTotal = $cartService->getSubtotal();

        // IDs dos produtos no carrinho para cálculo de margem de produção (#196)
        $cart = $cartService->getCart();
        $productIds = array_column($cart, 'product_id');

        // ID do cliente logado (para regra de pedido mínimo de cliente novo)
        $pessoaId = Auth::guard('customer')->check()
            ? Auth::guard('customer')->id()
            : null;

        $result = $this->shippingService->getDeliverySlots($cep, 14, $orderTotal, $pessoaId, $productIds);

        $slots = $result['slots'];
        $avisos = $result['avisos'];

        if (empty($slots)) {
            return response()->json([
                'disponivel'    => false,
                'mensagem'      => $avisos[0] ?? 'Não há períodos de entrega disponíveis para este CEP.',
                'avisos'        => $avisos,
                'pedido_minimo' => $result['pedido_minimo'],
                'slots'         => [],
            ]);
        }

        // Agrupa por data para exibição no frontend
        $grouped = collect($slots)->groupBy('date')->map(function ($daySlots, $date) {
            return [
                'date'           => $date,
                'date_formatted' => $daySlots->first()['date_formatted'],
                'slots'          => $daySlots->map(function ($slot) {
                    return [
                        'time_formatted'     => $slot['time_formatted'],
                        'veiculo_periodo_id' => $slot['veiculo_periodo_id'],
                        'periodo_id'         => $slot['periodo_id'],
                    ];
                })->values()->toArray(),
            ];
        })->values()->toArray();

        return response()->json([
            'disponivel' => true,
            'dias'       => $grouped,
            'avisos'     => $avisos,
        ]);
    }

    /**
     * Retorna lojas disponíveis para retirada.
     * Inclui indicador de qual loja atende a região do CEP informado.
     * GET /entrega/lojas-retirada?cep=20551030
     */
    public function lojasRetirada(Request $request): JsonResponse
    {
        $lojas = $this->shippingService->getPickupStores();

        // Identifica a loja que atende a região do CEP (se informado)
        $lojaRegiaoId = null;
        $cep = $request->input('cep', '');
        if ($cep) {
            $lookup = $this->shippingService->lookupCep($cep);
            if ($lookup && $lookup['loja']) {
                $lojaRegiaoId = $lookup['loja']->id;
            }
        }

        return response()->json([
            'lojas' => $lojas->map(function ($loja) use ($lojaRegiaoId) {
                return [
                    'id'               => $loja->id,
                    'nome'             => $loja->nome,
                    'endereco'         => $loja->full_address,
                    'horarios'         => $loja->business_hours,
                    'maps_link'        => $loja->link_google_maps,
                    'atende_regiao'    => $loja->id === $lojaRegiaoId,
                ];
            }),
        ]);
    }

    /**
     * Retorna datas de retirada disponíveis para uma loja (#199).
     * Agrupa por mês para exibição progressiva no frontend.
     * GET /entrega/datas-retirada?loja_id=22
     */
    public function datasRetirada(Request $request): JsonResponse
    {
        $request->validate([
            'loja_id' => 'required|integer',
        ]);

        $lojaId = (int) $request->input('loja_id');

        // Obtém IDs dos produtos no carrinho para cálculo de margem de produção
        $cartService = app(CartService::class);
        $cart = $cartService->getCart();
        $productIds = array_column($cart, 'product_id');

        $result = $this->shippingService->getPickupDates($lojaId, $productIds);

        if (!$result['disponivel']) {
            return response()->json([
                'disponivel' => false,
                'mensagem'   => 'Nenhuma data disponível para retirada nesta loja.',
                'meses'      => [],
                'loja'       => $result['loja'],
            ]);
        }

        // Agrupa datas por mês (YYYY-MM) para navegação progressiva
        $meses = collect($result['datas'])->groupBy(function ($d) {
            return substr($d['date'], 0, 7); // '2026-04'
        })->map(function ($dias, $mes) {
            // Monta label do mês em português
            $carbon = \Carbon\Carbon::parse($mes . '-01');
            $nomeMes = ucfirst($carbon->translatedFormat('F Y'));

            return [
                'mes'   => $mes,
                'label' => $nomeMes,
                'dias'  => $dias->map(function ($d) {
                    $carbon = \Carbon\Carbon::parse($d['date']);
                    return [
                        'date'        => $d['date'],
                        'dia'         => (int) $carbon->day,
                        'dia_semana'  => $d['dia_semana'],
                        'dia_nome'    => ucfirst($carbon->translatedFormat('l')),
                        'horario'     => $d['horario'],
                    ];
                })->values()->toArray(),
            ];
        })->values()->toArray();

        return response()->json([
            'disponivel' => true,
            'meses'      => $meses,
            'loja'       => $result['loja'],
        ]);
    }
}
