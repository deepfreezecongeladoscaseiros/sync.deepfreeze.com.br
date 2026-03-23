<?php

namespace App\Services;

use App\Models\Legacy\Pedido;
use App\Models\Legacy\PedidoInformacaoFrete;
use App\Models\Legacy\PedidoProduto;
use App\Models\Legacy\Pessoa;
use App\Models\Legacy\SessaoPedido;
use App\Models\Legacy\StatusPedido;
use App\Services\CouponService;
use App\Models\Product;
use App\Mail\OrderConfirmation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Service para criação de pedidos no banco legado.
 *
 * Grava diretamente nas tabelas 'pedidos', 'pedidos_produtos',
 * 'sessoes_pedidos' e 'status_pedidos' do banco legado (mysql_legacy),
 * para que o SIV processe os pedidos normalmente (operações, NF-e, entrega).
 *
 * Replica o comportamento do PedidosController.php do CakePHP legado,
 * campo por campo, para manter compatibilidade total.
 */
class LegacyOrderService
{
    // Status ID para "Criado na internet" (confirmado via Tinker)
    const STATUS_CRIADO_INTERNET = 1;

    // E-mail do admin para notificação
    const ADMIN_EMAIL = 'contato@deepfreeze.com.br';

    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Cria um pedido completo no banco legado.
     *
     * Fluxo:
     * 1. Gera sessão única (mt_rand) → INSERT sessoes_pedidos
     * 2. Valida itens do carrinho (preço atual, disponibilidade)
     * 3. Calcula peso total dos produtos
     * 4. INSERT pedidos (origem='INTERNET', finalizado=0)
     * 5. INSERT pedidos_produtos para cada item
     * 6. INSERT status_pedidos (statu_id=1 — "Criado na internet")
     * 7. Envia e-mails de confirmação
     * 8. Limpa carrinho
     *
     * @param Pessoa $customer Cliente autenticado (tabela pessoas)
     * @param array $addressData Dados do endereço de entrega
     * @param string|null $notes Observações do cliente
     * @return Pedido Pedido criado com itens carregados
     * @throws \Exception Se carrinho vazio ou erro na criação
     */
    /**
     * @param array $deliveryData Dados de entrega: veiculo_periodo_id (delivery) OU loja_retirada_id + data_retirada (pickup)
     * @param array $shippingCalc Cálculo de frete: valor, valor_original, distancia_km, motivo
     */
    public function createOrder(Pessoa $customer, array $addressData, ?string $notes = null, ?int $formaPagamentoId = null, array $deliveryData = [], array $shippingCalc = [], ?string $couponCode = null): Pedido
    {
        // Valida e atualiza itens do carrinho
        $validatedItems = $this->validateCartItems();

        if (empty($validatedItems)) {
            throw new \Exception('Seu carrinho está vazio ou os produtos não estão mais disponíveis.');
        }

        // Calcula totais
        $subtotal = $this->calculateSubtotal($validatedItems);
        $totalWeight = $this->calculateWeight($validatedItems);

        // Frete (do cálculo recebido ou 0 se não calculado)
        $shippingCost = (float) ($shippingCalc['valor'] ?? 0);
        $shippingOriginal = (float) ($shippingCalc['valor_original'] ?? 0);

        // Total final
        $total = $subtotal + $shippingCost;

        // Gera sessão única (replica getSessao() do legado)
        $sessaoId = SessaoPedido::generate();

        $connection = DB::connection('mysql_legacy');

        try {
            // ====== INSERT PEDIDO ======
            $pedido = new Pedido();
            $pedido->sessao = $sessaoId;
            $pedido->origem = Pedido::ORIGEM_INTERNET;              // 'INTERNET' — identificação do canal
            $pedido->pessoa_id = $customer->id;
            $pedido->tipo_pedido_id = 1;                            // Tipo padrão

            // Endereço de entrega desnormalizado (copiado no pedido)
            $pedido->cep_entrega = $addressData['shipping_zip_code'] ?? null;
            $pedido->logradouro_entrega = $addressData['shipping_address'] ?? null;
            $pedido->logradouro_complemento_numero_entrega = $addressData['shipping_number'] ?? null;
            $pedido->logradouro_complemento_entrega = $addressData['shipping_complement'] ?? null;
            $pedido->bairro_entrega = $addressData['shipping_neighborhood'] ?? null;
            $pedido->cidade_entrega = $addressData['shipping_city'] ?? null;
            $pedido->uf_entrega = $addressData['shipping_state'] ?? null;
            $pedido->referencia_entrega = null;
            $pedido->endereco_id = $addressData['endereco_id'] ?? null;

            // Entrega: delivery (veiculos_periodo_id) OU retirada em loja (loja_retirada_id)
            if (!empty($deliveryData['loja_retirada_id'])) {
                // Retirada em loja
                $pedido->loja_retirada_id = $deliveryData['loja_retirada_id'];
                $pedido->data_retirada = $deliveryData['data_retirada'] ?? null;
                $pedido->veiculos_periodo_id = null;
                $pedido->entrega = null;
            } elseif (!empty($deliveryData['veiculo_periodo_id'])) {
                // Entrega via veículo
                $pedido->veiculos_periodo_id = $deliveryData['veiculo_periodo_id'];
                $pedido->entrega = $deliveryData['data_entrega'] ?? null;
                $pedido->loja_retirada_id = null;
                $pedido->data_retirada = null;
            }

            $pedido->distancia_km = $shippingCalc['distancia_km'] ?? null;

            // Forma de pagamento (selecionada no checkout)
            $pedido->formas_pagamento_id = $formaPagamentoId;

            // Valores
            $pedido->valor_total_produtos = $subtotal;              // Soma dos produtos (sem frete/desconto)
            $pedido->valor_total_venda = $subtotal;                 // Total original antes de descontos
            $pedido->valor_frete_original = $shippingOriginal;
            $pedido->valor_frete = $shippingCost;
            $pedido->valor_total = $total;                          // Total final (produtos + frete - descontos)
            $pedido->desconto = 0;
            $pedido->tipo_desconto = 1;

            // Status
            $pedido->finalizado = Pedido::STATUS_PENDENTE;          // 0 = aguardando pagamento
            $pedido->data_finalizado = now();                       // Legado seta data mesmo antes de pagar

            // Metadata
            $pedido->peso = $totalWeight;
            $pedido->observacao = $notes;
            $pedido->receber_cardapio_impresso = 0;

            $pedido->save();

            $pedidoId = $pedido->id;

            // ====== INSERT ITENS DO PEDIDO ======
            foreach ($validatedItems as $item) {
                $pedidoProduto = new PedidoProduto();
                $pedidoProduto->pedido_id = $pedidoId;
                $pedidoProduto->produto_id = $item['product_id'];
                $pedidoProduto->produto = $item['product_code'];    // Código do produto (ex: "ALM01")
                $pedidoProduto->quantidade = $item['quantity'];
                $pedidoProduto->preco = $item['price'];             // Preço atual (pode ser promocional)
                $pedidoProduto->preco_original = $item['original_price'];
                $pedidoProduto->desconto_preco_unitario = 0;
                $pedidoProduto->subtotal = $item['price'] * $item['quantity'];
                $pedidoProduto->gift = 0;
                $pedidoProduto->save();
            }

            // ====== INSERT STATUS DO PEDIDO ======
            // statu_id=1 = "Criado na internet" (auditoria)
            $statusPedido = new StatusPedido();
            $statusPedido->pessoa_id = $customer->id;
            $statusPedido->pedido_id = $pedidoId;
            $statusPedido->statu_id = self::STATUS_CRIADO_INTERNET;
            $statusPedido->observacao = 'Pedido criado pela nova loja virtual (sync)';
            $statusPedido->save();

            // ====== APLICAR CUPOM DE DESCONTO ======
            if ($couponCode) {
                $couponService = app(CouponService::class);
                $couponResult = $couponService->apply($couponCode, $pedidoId, $customer->id, $subtotal);

                if ($couponResult['success'] && $couponResult['discount'] > 0) {
                    $discount = $couponResult['discount'];
                    $total = max(0, $subtotal + $shippingCost - $discount);

                    // Atualiza valores do pedido com desconto
                    $pedido->desconto = $discount;
                    $pedido->valor_total = $total;
                    $pedido->log_desconto = json_encode([
                        'cupom' => $couponCode,
                        'desconto' => $discount,
                        'aplicado_em' => now()->toDateTimeString(),
                    ]);
                    $pedido->save();
                }
            }

            // ====== SALVAR INFORMAÇÕES DE FRETE ======
            if (!empty($shippingCalc) && ($shippingCalc['valor'] ?? 0) > 0) {
                $lojaId = $deliveryData['loja_id'] ?? ($deliveryData['loja_retirada_id'] ?? 0);

                PedidoInformacaoFrete::create([
                    'pedido_id'              => $pedidoId,
                    'pessoa_id'              => $customer->id,
                    'loja_id'                => $lojaId,
                    'valor_frete_original'   => $shippingCalc['valor_original'] ?? 0,
                    'valor_frete'            => $shippingCalc['valor'] ?? 0,
                    'distancia_km'           => $shippingCalc['distancia_km'] ?? 0,
                    'distancia_ida_e_volta'  => ($shippingCalc['distancia_km'] ?? 0) * 2,
                    'falta_para_frete_gratis' => 0,
                    'motivo'                 => $shippingCalc['motivo'] ?? 'sync',
                    'valor_total_sem_frete'  => $subtotal,
                ]);
            }

            // ====== PÓS-CRIAÇÃO ======

            // Carrega relacionamentos para e-mail (pessoa + items com products)
            $pedido->load(['items.product', 'pessoa']);
            $this->sendConfirmationEmails($pedido, $customer);

            // Limpa carrinho
            $this->cartService->clear();

            // Salva referência na session para acesso à confirmação
            session()->put('last_order_session', $sessaoId);

            Log::info('[PEDIDO-LEGADO] Pedido criado com sucesso', [
                'pedido_id' => $pedidoId,
                'sessao' => $sessaoId,
                'pessoa_id' => $customer->id,
                'total' => $total,
                'itens' => count($validatedItems),
            ]);

            return $pedido;

        } catch (\Exception $e) {
            Log::error('[PEDIDO-LEGADO] Erro ao criar pedido', [
                'sessao' => $sessaoId,
                'pessoa_id' => $customer->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Valida itens do carrinho: verifica se produtos existem, estão ativos,
     * e atualiza preços para o valor atual.
     *
     * @return array Itens validados com dados atualizados do banco
     * @throws \Exception Se nenhum item válido
     */
    public function validateCartItems(): array
    {
        $cart = $this->cartService->getCart();
        $validatedItems = [];

        foreach ($cart as $item) {
            $product = Product::find($item['product_id']);

            // Produto não existe ou está inativo — ignora silenciosamente
            if (!$product || !$product->active) {
                Log::warning('[PEDIDO-LEGADO] Produto removido do carrinho (inativo/inexistente)', [
                    'product_id' => $item['product_id'],
                ]);
                continue;
            }

            // Preço atual do produto (pode ser promocional)
            $currentPrice = $product->getCurrentPrice();
            $originalPrice = $product->price;

            $validatedItems[] = [
                'product_id'     => $product->id,
                'product_code'   => $product->getOriginal('codigo') ?? '',   // Código do produto legado (ex: "ALM01")
                'product_name'   => $product->getOriginal('descricao') ?? '',
                'price'          => $currentPrice,
                'original_price' => $originalPrice,
                'quantity'       => (int) $item['quantity'],
                'weight'         => (float) ($product->getOriginal('peso_liquido') ?? 0),
            ];
        }

        return $validatedItems;
    }

    /**
     * Calcula subtotal dos itens validados.
     */
    protected function calculateSubtotal(array $items): float
    {
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        return round($subtotal, 2);
    }

    /**
     * Calcula peso total dos itens (usado pelo SIV para logística).
     */
    protected function calculateWeight(array $items): float
    {
        $weight = 0;
        foreach ($items as $item) {
            $weight += $item['weight'] * $item['quantity'];
        }
        return $weight;
    }

    /**
     * Envia e-mails de confirmação para cliente e admin.
     * Encapsulado em try/catch para não falhar o pedido se e-mail falhar.
     */
    protected function sendConfirmationEmails(Pedido $pedido, Pessoa $customer): void
    {
        try {
            // E-mail para o cliente
            if (!empty($customer->email_primario)) {
                Mail::to($customer->email_primario)
                    ->send(new OrderConfirmation($pedido, false));
            }

            // E-mail para o admin
            Mail::to(self::ADMIN_EMAIL)
                ->send(new OrderConfirmation($pedido, true));

        } catch (\Exception $e) {
            // Não falha o pedido — apenas loga o erro
            Log::warning('[PEDIDO-LEGADO] Falha ao enviar e-mail de confirmação', [
                'pedido_id' => $pedido->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
