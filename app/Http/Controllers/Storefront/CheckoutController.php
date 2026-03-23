<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Legacy\Pedido;
use App\Models\Legacy\Pessoa;
use App\Services\CartService;
use App\Services\LegacyOrderService;
use App\Services\PaymentService;
use App\Services\ShippingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

/**
 * Controller do Checkout da Storefront.
 *
 * Fluxo de finalização de compra:
 * 1. Exige login de cliente (guard 'customer' → tabela 'pessoas')
 * 2. Exibe formulário de endereço de entrega
 * 3. Processa pedido via LegacyOrderService (grava no banco legado)
 * 4. Exibe página de confirmação
 *
 * O pedido é gravado nas tabelas 'pedidos' e 'pedidos_produtos' do banco legado,
 * para que o SIV processe normalmente (operações, NF-e, entrega).
 */
class CheckoutController extends Controller
{
    protected CartService $cartService;
    protected LegacyOrderService $orderService;
    protected PaymentService $paymentService;
    protected ShippingService $shippingService;

    public function __construct(CartService $cartService, LegacyOrderService $orderService, PaymentService $paymentService, ShippingService $shippingService)
    {
        $this->cartService = $cartService;
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
        $this->shippingService = $shippingService;
    }

    /**
     * Exibe o formulário de checkout.
     *
     * Login obrigatório (redireciona para /login se não autenticado).
     * Pré-preenche endereço com dados do cadastro do cliente.
     */
    public function index(): View|RedirectResponse
    {
        // Verifica se cliente está logado (guard 'customer')
        $customer = auth()->user();
        if (!$customer) {
            return redirect()->route('login')
                ->with('info', 'Faça login para finalizar sua compra.');
        }

        $cart = $this->cartService->getCart();

        // Carrinho vazio → redireciona
        if (empty($cart)) {
            return redirect('/carrinho')
                ->with('info', 'Seu carrinho está vazio. Adicione produtos antes de finalizar.');
        }

        $cartItems = array_values($cart);
        $subtotal = $this->cartService->getSubtotal();

        // Busca endereços salvos do cliente para pré-preenchimento
        $enderecos = $customer->enderecos()->get();

        // Busca formas de pagamento disponíveis
        $paymentMethods = $this->paymentService->getAvailableMethodsAllStores();

        // Passa o customer como $user para manter compatibilidade com a view
        $user = $customer;

        return view('storefront.checkout.index', compact('cartItems', 'subtotal', 'user', 'enderecos', 'paymentMethods'));
    }

    /**
     * Processa o pedido.
     *
     * Valida endereço de entrega, cria pedido no banco legado via LegacyOrderService,
     * e redireciona para a confirmação.
     */
    public function store(Request $request): RedirectResponse
    {
        // Verifica login
        $customer = auth()->user();
        if (!$customer || !($customer instanceof Pessoa)) {
            return redirect()->route('login')
                ->with('info', 'Faça login para finalizar sua compra.');
        }

        // Carrinho vazio → redireciona
        if (empty($this->cartService->getCart())) {
            return redirect('/carrinho')
                ->with('info', 'Seu carrinho está vazio.');
        }

        // Valida dados de endereço de entrega e forma de pagamento
        $validated = $request->validate([
            'shipping_zip_code'     => ['required', 'string', 'max:10'],
            'shipping_address'      => ['required', 'string', 'max:255'],
            'shipping_number'       => ['required', 'string', 'max:30'],
            'shipping_complement'   => ['nullable', 'string', 'max:40'],
            'shipping_neighborhood' => ['required', 'string', 'max:50'],
            'shipping_city'         => ['required', 'string', 'max:45'],
            'shipping_state'        => ['required', 'string', 'size:2'],
            'formas_pagamento_id'   => ['required', 'integer'],
            'tipo_entrega'          => ['required', 'in:delivery,pickup'],
            'veiculo_periodo_id'    => ['nullable', 'integer'],
            'data_entrega'          => ['nullable', 'string'],
            'loja_retirada_id'      => ['nullable', 'integer'],
            'data_retirada'         => ['nullable', 'string'],
            'notes'                 => ['nullable', 'string', 'max:1000'],
        ], [
            'shipping_zip_code.required'     => 'O CEP é obrigatório.',
            'shipping_address.required'      => 'O endereço é obrigatório.',
            'shipping_number.required'       => 'O número é obrigatório.',
            'shipping_neighborhood.required' => 'O bairro é obrigatório.',
            'shipping_city.required'         => 'A cidade é obrigatória.',
            'shipping_state.required'        => 'O estado é obrigatório.',
            'formas_pagamento_id.required'   => 'Selecione uma forma de pagamento.',
            'tipo_entrega.required'          => 'Selecione o tipo de entrega.',
        ]);

        // Valida se a forma de pagamento é permitida
        if (!$this->paymentService->isMethodAllowed((int) $validated['formas_pagamento_id'])) {
            return redirect()->back()->withInput()
                ->with('error', 'Forma de pagamento inválida.');
        }

        // Monta dados de endereço (inclui endereco_id se selecionou endereço salvo)
        $addressData = [
            'shipping_zip_code'     => $validated['shipping_zip_code'],
            'shipping_address'      => $validated['shipping_address'],
            'shipping_number'       => $validated['shipping_number'],
            'shipping_complement'   => $validated['shipping_complement'] ?? null,
            'shipping_neighborhood' => $validated['shipping_neighborhood'],
            'shipping_city'         => $validated['shipping_city'],
            'shipping_state'        => strtoupper($validated['shipping_state']),
            'endereco_id'           => $request->input('endereco_id'),
        ];

        // Monta dados de entrega (delivery ou pickup)
        $deliveryData = [];
        if (($validated['tipo_entrega'] ?? 'delivery') === 'pickup') {
            $deliveryData['loja_retirada_id'] = $validated['loja_retirada_id'] ?? null;
            $deliveryData['data_retirada'] = $validated['data_retirada'] ?? null;
        } else {
            $deliveryData['veiculo_periodo_id'] = $validated['veiculo_periodo_id'] ?? null;
            $deliveryData['data_entrega'] = $validated['data_entrega'] ?? null;
        }

        // Calcula frete (se for delivery)
        $shippingCalc = [];
        if (($validated['tipo_entrega'] ?? 'delivery') === 'delivery') {
            $subtotal = $this->cartService->getSubtotal();
            $shippingCalc = $this->shippingService->calculateShipping(
                $validated['shipping_zip_code'],
                $subtotal,
                (int) $validated['formas_pagamento_id']
            );
            $shippingCalc['valor_pedido'] = $subtotal;
        }

        try {
            $pedido = $this->orderService->createOrder(
                $customer,
                $addressData,
                $validated['notes'] ?? null,
                (int) $validated['formas_pagamento_id'],
                $deliveryData,
                $shippingCalc,
                $request->input('coupon_code')
            );

            return redirect()
                ->route('checkout.confirmation', ['orderNumber' => $pedido->sessao])
                ->with('success', 'Pedido realizado com sucesso!');

        } catch (\Exception $e) {
            Log::error('[CHECKOUT] Erro ao criar pedido', [
                'error' => $e->getMessage(),
                'pessoa_id' => $customer->id,
                'ip' => $request->ip(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao processar seu pedido. Por favor, tente novamente.');
        }
    }

    /**
     * Exibe a página de confirmação do pedido.
     *
     * O parâmetro $orderNumber é na verdade o campo 'sessao' do pedido legado
     * (inteiro único que identifica o pedido no fluxo web).
     *
     * Proteção de acesso:
     * - Cliente logado: verifica se o pedido pertence a ele (pessoa_id)
     * - Session: verifica se a sessão do pedido está salva na session do Laravel
     */
    public function confirmation(string $orderNumber): View|RedirectResponse
    {
        // Busca pedido pela sessão
        $pedido = Pedido::with('items', 'pessoa')
            ->where('sessao', (int) $orderNumber)
            ->first();

        if (!$pedido) {
            abort(404);
        }

        // Proteção de acesso
        $customer = auth()->user();
        $lastOrderSession = Session::get('last_order_session');

        $canAccess = false;

        if ($customer && $pedido->pessoa_id === $customer->id) {
            // Cliente logado e é o dono do pedido
            $canAccess = true;
        } elseif ($lastOrderSession !== null && (int) $lastOrderSession === (int) $pedido->sessao) {
            // Session do Laravel tem a referência do pedido (acabou de criar)
            $canAccess = true;
        }

        if (!$canAccess) {
            abort(403);
        }

        // Passa como $order para manter compatibilidade com a view
        $order = $pedido;

        return view('storefront.checkout.confirmation', compact('order'));
    }
}
