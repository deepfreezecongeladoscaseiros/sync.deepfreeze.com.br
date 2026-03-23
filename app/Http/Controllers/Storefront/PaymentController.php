<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Legacy\Pedido;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controller de pagamento da loja.
 *
 * Gerencia callbacks de gateway de pagamento e confirmação manual.
 * Grava resultados em 'pagamentos_cielo' do banco legado para
 * compatibilidade com o SIV.
 *
 * Fluxo:
 * 1. Cliente confirma pedido no checkout (status = pendente)
 * 2. Para pagamento online: redireciona para gateway
 * 3. Gateway processa e retorna via callback
 * 4. Callback confirma pagamento e finaliza pedido
 *
 * Nota: A integração com o gateway (Cielo/Rede/outro) será implementada
 * quando o gateway for definido. Por enquanto, aceita confirmação manual
 * (PIX, dinheiro) e preparação para callback.
 */
class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Callback do gateway de pagamento (POST).
     *
     * Recebe notificação de pagamento aprovado/negado.
     * Grava em pagamentos_cielo e atualiza status do pedido.
     *
     * TODO: Implementar parsing do payload específico do gateway escolhido
     * (Cielo Checkout, Stripe, PagSeguro, etc.)
     */
    public function callback(Request $request): JsonResponse
    {
        Log::info('[PAYMENT-CALLBACK] Recebido', [
            'ip' => $request->ip(),
            'payload' => $request->all(),
        ]);

        // TODO: Implementar parsing do payload do gateway
        // Exemplo para Cielo:
        // $pedidoId = $request->input('MerchantOrderId');
        // $status = $request->input('Payment.Status'); // 2=pago
        // $tid = $request->input('Payment.Tid');

        return response()->json(['received' => true]);
    }

    /**
     * Retorno do cliente após pagamento no gateway (GET).
     *
     * O cliente é redirecionado aqui após completar o pagamento.
     * Verifica status e redireciona para confirmação ou erro.
     */
    public function returnFromGateway(Request $request): RedirectResponse
    {
        $pedidoId = $request->input('pedido_id');
        $sessao = $request->input('sessao');

        if (!$pedidoId && !$sessao) {
            return redirect('/')->with('error', 'Dados de pagamento inválidos.');
        }

        // Busca pedido
        $pedido = $pedidoId
            ? Pedido::find($pedidoId)
            : Pedido::where('sessao', $sessao)->first();

        if (!$pedido) {
            return redirect('/')->with('error', 'Pedido não encontrado.');
        }

        // Verifica se pagamento foi confirmado
        if ($this->paymentService->isPaid($pedido->id)) {
            // Pagamento confirmado — redireciona para confirmação
            return redirect()->route('checkout.confirmation', ['orderNumber' => $pedido->sessao])
                ->with('success', 'Pagamento confirmado!');
        }

        // Pagamento ainda pendente — redireciona para aguardar
        return redirect()->route('checkout.confirmation', ['orderNumber' => $pedido->sessao])
            ->with('info', 'Pagamento sendo processado. Você receberá uma confirmação por e-mail.');
    }
}
