<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Legacy\PagamentoCielo;
use App\Models\Legacy\Pedido;
use App\Services\CieloCheckoutService;
use App\Services\PaymentService;
use App\Services\RedePaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controller de pagamento da loja virtual.
 *
 * Gerencia 3 fluxos de pagamento:
 * A) Cielo Checkout — redirect para página hospedada da Cielo + polling
 * B) Rede e-Rede — formulário de cartão processado via SDK
 * C) Callback genérico — webhook para notificações futuras
 *
 * O webhook de retorno da Cielo é tratado pelo SISTEMA LEGADO (CieloController::notificacao),
 * que grava em pagamentos_cielo. O sync apenas faz polling nessa tabela.
 *
 * Fluxo Cielo:
 *   1. redirectToCielo → cria ordem na API Cielo → redireciona cliente
 *   2. Cliente paga na Cielo → Cielo notifica legado → grava pagamentos_cielo
 *   3. aguardarCielo → mostra polling view (AJAX consulta statusCielo)
 *   4. statusCielo → retorna JSON com status do pagamento
 *
 * Fluxo Rede:
 *   1. redeCartao → exibe formulário de cartão (crédito ou débito)
 *   2. redeProcessar → envia dados ao SDK Rede → resultado imediato ou 3DS
 *   3. redeConsultarTid → callback do 3D Secure (só débito)
 */
class PaymentController extends Controller
{
    protected PaymentService $paymentService;
    protected CieloCheckoutService $cieloService;
    protected RedePaymentService $redeService;

    public function __construct(
        PaymentService $paymentService,
        CieloCheckoutService $cieloService,
        RedePaymentService $redeService
    ) {
        $this->paymentService = $paymentService;
        $this->cieloService = $cieloService;
        $this->redeService = $redeService;
    }

    /**
     * Verifica se o cliente logado é dono do pedido.
     * Proteção contra acesso indevido a pedidos de outros clientes.
     * Permite acesso se: é o dono do pedido OU o pedido acabou de ser criado nesta sessão.
     */
    private function verifyOwnership(Pedido $pedido): bool
    {
        $customer = auth()->user();

        // Cliente logado é o dono do pedido
        if ($customer && $pedido->pessoa_id === $customer->id) {
            return true;
        }

        // Pedido acabou de ser criado nesta sessão (last_order_session)
        $lastOrderSession = session('last_order_session');
        if ($lastOrderSession !== null && (int) $lastOrderSession === (int) $pedido->sessao) {
            return true;
        }

        return false;
    }

    // ==================== A) CIELO CHECKOUT ====================

    /**
     * Redireciona o cliente para a página de pagamento da Cielo.
     *
     * Cria uma ordem no Cielo Checkout via API e redireciona para a
     * checkoutUrl retornada. Se falhar, volta para o checkout com erro.
     *
     * @param int $pedidoId ID do pedido no banco legado
     * @param int $lojaId   ID da loja (para merchant_id)
     */
    public function redirectToCielo(int $pedidoId, int $lojaId): RedirectResponse
    {
        $pedido = Pedido::find($pedidoId);

        if (!$pedido) {
            Log::error('[PAYMENT] redirectToCielo: pedido não encontrado', ['pedido_id' => $pedidoId]);
            return redirect('/checkout')->with('error', 'Pedido não encontrado.');
        }

        // Verifica se o cliente logado é dono do pedido
        if (!$this->verifyOwnership($pedido)) {
            abort(403);
        }

        // Cria ordem na API Cielo e obtém URL de checkout
        $checkoutUrl = $this->cieloService->createCheckoutOrder($pedido, $lojaId);

        if (!$checkoutUrl) {
            Log::error('[PAYMENT] redirectToCielo: falha ao criar ordem Cielo', [
                'pedido_id' => $pedidoId,
                'loja_id'   => $lojaId,
            ]);
            return redirect('/checkout')->with('error', 'Erro ao conectar com o gateway de pagamento. Tente novamente.');
        }

        Log::info('[PAYMENT] redirectToCielo: redirecionando para Cielo', [
            'pedido_id'    => $pedidoId,
            'checkout_url' => $checkoutUrl,
        ]);

        return redirect()->away($checkoutUrl);
    }

    /**
     * Página de aguardar confirmação do pagamento Cielo (polling).
     *
     * Após pagar na Cielo, o cliente retorna aqui. O sistema verifica
     * pagamentos_cielo via AJAX (statusCielo) até obter resultado.
     *
     * Comportamento:
     * - Se já pago (status=2): redireciona para confirmação
     * - Se negado/expirado/cancelado (3, 4, 5): redireciona para checkout com erro
     * - Caso contrário: mostra view de polling (AJAX consulta statusCielo)
     *
     * @param int $pedidoId  ID do pedido
     * @param int $tentativa Contador de tentativas de polling (para timeout no frontend)
     */
    public function aguardarCielo(int $pedidoId, int $tentativa = 0): RedirectResponse|\Illuminate\View\View
    {
        $pedido = Pedido::find($pedidoId);

        if (!$pedido) {
            Log::error('[PAYMENT] aguardarCielo: pedido não encontrado', ['pedido_id' => $pedidoId]);
            return redirect('/checkout')->with('error', 'Pedido não encontrado.');
        }

        // Verifica status atual do pagamento em pagamentos_cielo
        $pagamento = PagamentoCielo::where('pedido_id', $pedidoId)
            ->orderBy('id', 'desc')
            ->first();

        if ($pagamento) {
            $status = $pagamento->status_pagamento;

            // Pagamento confirmado — redireciona para confirmação
            if ($status === PagamentoCielo::STATUS_PAGO) {
                return redirect()->route('checkout.confirmation', ['orderNumber' => $pedido->sessao])
                    ->with('success', 'Pagamento confirmado!');
            }

            // Pagamento negado, expirado ou cancelado — volta para checkout com erro
            if (in_array($status, [
                PagamentoCielo::STATUS_NEGADO,
                PagamentoCielo::STATUS_EXPIRADO,
                PagamentoCielo::STATUS_CANCELADO,
            ])) {
                $mensagem = match ($status) {
                    PagamentoCielo::STATUS_NEGADO    => 'Pagamento negado pela operadora.',
                    PagamentoCielo::STATUS_EXPIRADO  => 'Pagamento expirado. Tente novamente.',
                    PagamentoCielo::STATUS_CANCELADO => 'Pagamento cancelado.',
                    default                          => 'Erro no pagamento.',
                };

                return redirect('/checkout')->with('error', $mensagem);
            }
        }

        // Ainda sem resultado — mostra view de polling
        // O frontend faz AJAX para statusCielo a cada N segundos
        return view('storefront.payment.aguardar-cielo', [
            'pedido'    => $pedido,
            'pedidoId'  => $pedidoId,
            'tentativa' => (int) $tentativa,
        ]);
    }

    /**
     * Retorna status do pagamento Cielo via JSON (para polling AJAX).
     *
     * Consultado periodicamente pelo frontend enquanto o cliente
     * aguarda confirmação na página de polling.
     *
     * @param int $pedidoId ID do pedido
     * @return JsonResponse {paid: bool, status: int, sessao: string, redirect_url: string|null}
     */
    public function statusCielo(int $pedidoId): JsonResponse
    {
        $pedido = Pedido::find($pedidoId);

        if (!$pedido) {
            return response()->json([
                'paid'         => false,
                'status'       => 0,
                'sessao'       => '',
                'redirect_url' => null,
            ]);
        }

        // Busca o pagamento mais recente do pedido
        $pagamento = PagamentoCielo::where('pedido_id', $pedidoId)
            ->orderBy('id', 'desc')
            ->first();

        $status = $pagamento ? $pagamento->status_pagamento : 0;
        $paid = $status === PagamentoCielo::STATUS_PAGO;

        // Se pago, retorna URL de confirmação para redirect no frontend
        $redirectUrl = $paid
            ? route('checkout.confirmation', ['orderNumber' => $pedido->sessao])
            : null;

        return response()->json([
            'paid'         => $paid,
            'status'       => $status,
            'sessao'       => $pedido->sessao ?? '',
            'redirect_url' => $redirectUrl,
        ]);
    }

    // ==================== B) REDE e-REDE ====================

    /**
     * Exibe formulário de cartão para pagamento via Rede.
     *
     * Determina o tipo (CREDITO ou DEBITO) pelo formaPagamentoId:
     * - ID 63 = Rede Débito → tipo DEBITO
     * - Outros = Rede Crédito → tipo CREDITO
     *
     * @param int $pedidoId          ID do pedido
     * @param int $lojaId            ID da loja
     * @param int $formaPagamentoId  ID da forma de pagamento (62=crédito, 63=débito)
     */
    public function redeCartao(int $pedidoId, int $lojaId, int $formaPagamentoId): \Illuminate\View\View|RedirectResponse
    {
        $pedido = Pedido::find($pedidoId);

        if (!$pedido) {
            return redirect('/checkout')->with('error', 'Pedido não encontrado.');
        }

        // Verifica se o cliente logado é dono do pedido
        if (!$this->verifyOwnership($pedido)) {
            abort(403);
        }

        // Determina tipo pelo ID da forma: 63 = débito, demais = crédito
        $tipo = ($formaPagamentoId === PaymentService::FORMA_REDE_DEBITO) ? 'DEBITO' : 'CREDITO';

        return view('storefront.payment.rede-cartao', [
            'pedido'            => $pedido,
            'lojaId'            => $lojaId,
            'formaPagamentoId'  => $formaPagamentoId,
            'tipo'              => $tipo,
            'valorAPagar'       => (float) $pedido->valor_total,
            'erroRede'          => request('erro_rede'),
        ]);
    }

    /**
     * Processa pagamento com cartão via Rede (POST do formulário).
     *
     * Extrai dados do cartão do request e chama o RedePaymentService.
     * Três possíveis resultados:
     * - success → redireciona para confirmação do pedido
     * - redirect_3ds → redireciona para autenticação 3D Secure (só débito)
     * - error → volta para o formulário de cartão com mensagem de erro
     *
     * @param Request $request          Dados do formulário (card_number, holder_name, etc.)
     * @param int     $pedidoId         ID do pedido
     * @param int     $lojaId           ID da loja
     * @param int     $formaPagamentoId ID da forma de pagamento
     */
    public function redeProcessar(Request $request, int $pedidoId, int $lojaId, int $formaPagamentoId): RedirectResponse
    {
        $pedido = Pedido::find($pedidoId);

        if (!$pedido) {
            return redirect('/checkout')->with('error', 'Pedido não encontrado.');
        }

        // Verifica se o cliente logado é dono do pedido
        if (!$this->verifyOwnership($pedido)) {
            abort(403);
        }

        // Extrai dados do cartão do request
        $cardData = [
            'card_number'           => $request->input('card_number'),
            'holder_name'           => $request->input('holder_name'),
            'card_expiration_month' => $request->input('card_expiration_month'),
            'card_expiration_year'  => $request->input('card_expiration_year'),
            'card_cvv'              => $request->input('card_cvv'),
        ];

        // Determina se é débito (63) ou crédito e chama o service apropriado
        $isDebit = ($formaPagamentoId === PaymentService::FORMA_REDE_DEBITO);

        Log::info('[PAYMENT] redeProcessar: processando cartão', [
            'pedido_id' => $pedidoId,
            'loja_id'   => $lojaId,
            'tipo'      => $isDebit ? 'DEBITO' : 'CREDITO',
        ]);

        $result = $isDebit
            ? $this->redeService->processDebit($pedido, $lojaId, $cardData)
            : $this->redeService->processCredit($pedido, $lojaId, $cardData);

        // Sucesso — redireciona para confirmação
        if ($result['success']) {
            Log::info('[PAYMENT] redeProcessar: pagamento aprovado', [
                'pedido_id' => $pedidoId,
            ]);

            return redirect()->route('checkout.confirmation', ['orderNumber' => $pedido->sessao])
                ->with('success', 'Pagamento aprovado!');
        }

        // Redirect 3D Secure — redireciona para autenticação do banco (apenas débito)
        if (!empty($result['redirect_3ds'])) {
            Log::info('[PAYMENT] redeProcessar: redirecionando para 3D Secure', [
                'pedido_id'    => $pedidoId,
                'redirect_url' => $result['redirect_3ds'],
            ]);

            return redirect()->away($result['redirect_3ds']);
        }

        // Erro — volta para o formulário de cartão com mensagem
        $errorMessage = $result['error'] ?? 'Erro ao processar pagamento. Tente novamente.';

        Log::warning('[PAYMENT] redeProcessar: erro no pagamento', [
            'pedido_id' => $pedidoId,
            'error'     => $errorMessage,
        ]);

        return redirect()->route('payment.rede.cartao', [
            'pedidoId'          => $pedidoId,
            'lojaId'            => $lojaId,
            'formaPagamentoId'  => $formaPagamentoId,
        ])->with('error', $errorMessage);
    }

    /**
     * Callback do 3D Secure — consulta resultado após autenticação.
     *
     * Chamado automaticamente pelo banco emissor após o portador
     * completar (ou falhar) a autenticação 3D Secure.
     *
     * @param int $pedidoId ID do pedido
     * @param int $lojaId   ID da loja
     */
    public function redeConsultarTid(int $pedidoId, int $lojaId): RedirectResponse
    {
        Log::info('[PAYMENT] redeConsultarTid: retorno do 3D Secure', [
            'pedido_id' => $pedidoId,
            'loja_id'   => $lojaId,
        ]);

        $result = $this->redeService->consultAfter3ds($pedidoId, $lojaId);

        if ($result['success']) {
            // Pagamento aprovado após 3DS — busca pedido para obter sessão
            $pedido = Pedido::find($pedidoId);

            if ($pedido) {
                return redirect()->route('checkout.confirmation', ['orderNumber' => $pedido->sessao])
                    ->with('success', 'Pagamento aprovado!');
            }

            // Fallback improvável: pedido não encontrado após pagamento aprovado
            return redirect('/')->with('success', 'Pagamento aprovado!');
        }

        // Erro na autenticação 3DS — volta para checkout
        $errorMessage = $result['error'] ?? 'Falha na autenticação do cartão.';

        Log::warning('[PAYMENT] redeConsultarTid: falha no 3D Secure', [
            'pedido_id' => $pedidoId,
            'error'     => $errorMessage,
        ]);

        return redirect('/checkout')->with('error', $errorMessage);
    }

    // ==================== C) CALLBACK GENÉRICO ====================

    /**
     * Callback do gateway de pagamento (POST - webhook).
     *
     * Recebe notificação de pagamento aprovado/negado.
     * Preparado para futuros gateways que enviam notificação via POST.
     *
     * Nota: O webhook da Cielo é recebido pelo SISTEMA LEGADO
     * (CieloController::notificacao). Este endpoint é reservado
     * para integrações futuras.
     */
    public function callback(Request $request): JsonResponse
    {
        Log::info('[PAYMENT-CALLBACK] Recebido', [
            'ip'      => $request->ip(),
            'payload' => $request->all(),
        ]);

        // TODO: Implementar parsing do payload conforme gateway
        return response()->json(['received' => true]);
    }

    /**
     * Retorno do cliente após pagamento no gateway (GET).
     *
     * Mantido para compatibilidade — a rota foi removida mas o método
     * pode ser reutilizado por futuras integrações.
     */
    public function returnFromGateway(Request $request): RedirectResponse
    {
        $pedidoId = $request->input('pedido_id');
        $sessao = $request->input('sessao');

        if (!$pedidoId && !$sessao) {
            return redirect('/')->with('error', 'Dados de pagamento inválidos.');
        }

        // Busca pedido por ID ou sessão
        $pedido = $pedidoId
            ? Pedido::find($pedidoId)
            : Pedido::where('sessao', $sessao)->first();

        if (!$pedido) {
            return redirect('/')->with('error', 'Pedido não encontrado.');
        }

        // Verifica se pagamento foi confirmado
        if ($this->paymentService->isPaid($pedido->id)) {
            return redirect()->route('checkout.confirmation', ['orderNumber' => $pedido->sessao])
                ->with('success', 'Pagamento confirmado!');
        }

        // Pagamento ainda pendente
        return redirect()->route('checkout.confirmation', ['orderNumber' => $pedido->sessao])
            ->with('info', 'Pagamento sendo processado. Você receberá uma confirmação por e-mail.');
    }
}
