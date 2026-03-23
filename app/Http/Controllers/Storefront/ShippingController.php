<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\ShippingService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
     * GET /entrega/consultar-cep?cep=20551030
     */
    public function consultarCep(Request $request): JsonResponse
    {
        $cep = $request->input('cep', '');

        $result = $this->shippingService->lookupCep($cep);

        if (!$result) {
            return response()->json([
                'atendido' => false,
                'mensagem' => 'CEP não encontrado ou não atendido para entrega.',
            ]);
        }

        return response()->json([
            'atendido' => $result['loja'] !== null,
            'regiao'   => $result['regiao']?->nome,
            'loja'     => $result['loja'] ? [
                'id'   => $result['loja']->id,
                'nome' => $result['loja']->nome,
            ] : null,
            'endereco' => $result['logradouro']?->Endereco,
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
     * GET /entrega/periodos?cep=20551030
     */
    public function periodos(Request $request): JsonResponse
    {
        $cep = $request->input('cep', '');

        $slots = $this->shippingService->getDeliverySlots($cep);

        if (empty($slots)) {
            return response()->json([
                'disponivel' => false,
                'mensagem'   => 'Não há períodos de entrega disponíveis para este CEP.',
                'slots'      => [],
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
        ]);
    }

    /**
     * Retorna lojas disponíveis para retirada.
     * GET /entrega/lojas-retirada
     */
    public function lojasRetirada(): JsonResponse
    {
        $lojas = $this->shippingService->getPickupStores();

        return response()->json([
            'lojas' => $lojas->map(function ($loja) {
                return [
                    'id'          => $loja->id,
                    'nome'        => $loja->nome,
                    'endereco'    => $loja->full_address,
                    'horarios'    => $loja->business_hours,
                    'maps_link'   => $loja->link_google_maps,
                ];
            }),
        ]);
    }
}
