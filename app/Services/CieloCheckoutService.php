<?php

namespace App\Services;

use App\Models\Legacy\Loja;
use App\Models\Legacy\Logradouro;
use App\Models\Legacy\Pedido;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service: Cielo Checkout (página hospedada).
 *
 * Replica o fluxo do legado (CieloController::ir_para_cielo) para
 * criar uma ordem na API Cielo Checkout e redirecionar o cliente
 * à página de pagamento da Cielo.
 *
 * Fluxo:
 * 1. Marca o pedido com forma_pagamento = Cielo (46) e finalizado = 0
 * 2. Monta o JSON da ordem no formato esperado pela API Cielo
 * 3. Envia via cURL (replica exatamente os headers e opções do legado)
 * 4. Retorna a URL de checkout para redirect, ou null em caso de erro
 *
 * O webhook de retorno da Cielo é recebido pelo SISTEMA LEGADO
 * (CieloController::notificacao), que grava em pagamentos_cielo.
 * O sync apenas faz polling nessa tabela para saber se o pagamento foi confirmado.
 */
class CieloCheckoutService
{
    /** URL da API Cielo Checkout (produção) */
    const API_URL = 'https://cieloecommerce.cielo.com.br/api/public/v1/orders';

    /**
     * Cria uma ordem no Cielo Checkout e retorna a URL de pagamento.
     *
     * Replica fielmente o legado CieloController::ir_para_cielo():
     * - Seta formas_pagamento_id = 46 (Cielo) e finalizado = 0
     * - Monta JSON com Cart, Shipping, Customer e Options
     * - Envia para API Cielo via cURL
     * - Retorna checkoutUrl ou null
     *
     * @param Pedido $pedido  Pedido do banco legado (com relacionamentos pessoa e items)
     * @param int    $lojaId  ID da loja para obter o merchant_id
     * @return string|null    URL de checkout da Cielo, ou null em caso de erro
     */
    public function createCheckoutOrder(Pedido $pedido, int $lojaId): ?string
    {
        // Busca a loja para obter o merchant_id (credencial Cielo)
        $loja = Loja::find($lojaId);

        if (!$loja || empty($loja->merchant_id)) {
            Log::error('[CIELO] Loja não encontrada ou sem merchant_id', [
                'loja_id' => $lojaId,
                'pedido_id' => $pedido->id,
            ]);
            return null;
        }

        $merchantId = $loja->merchant_id;

        // Marca o pedido como Cielo e pendente (replica legado: T670)
        $pedido->formas_pagamento_id = PaymentService::FORMA_CIELO;
        $pedido->finalizado = Pedido::STATUS_PENDENTE;
        $pedido->save();

        // Carrega relacionamentos necessários para montar a ordem
        $pedido->load(['items', 'pessoa']);

        if ($pedido->items->isEmpty()) {
            Log::error('[CIELO] Pedido sem itens', ['pedido_id' => $pedido->id]);
            return null;
        }

        // Calcula desconto e monta o JSON da ordem
        $desconto = $this->calculateDiscount($pedido);
        $order = $this->buildOrder($pedido, $desconto);

        // Envia para a API Cielo (cURL idêntico ao legado)
        $checkoutUrl = $this->sendToApi($merchantId, $order, $pedido->id);

        return $checkoutUrl;
    }

    /**
     * Calcula o desconto da ordem.
     *
     * Replica legado CieloController::get_desconto():
     * desconto = somaItens(qtd * preco) - (valor_total - valor_frete)
     *
     * O desconto representa a diferença entre o valor "cheio" dos produtos
     * e o que realmente será cobrado (excluindo frete). Pode incluir
     * cupons, descontos promocionais, etc.
     *
     * @param Pedido $pedido Pedido com items carregados
     * @return float Valor do desconto (0 se não houver)
     */
    private function calculateDiscount(Pedido $pedido): float
    {
        // Valor cobrado = total do pedido menos frete
        $valorCobrado = number_format(
            (float) $pedido->valor_total - (float) $pedido->valor_frete,
            2, '.', ''
        );

        // Soma dos itens (quantidade × preço unitário)
        $valorProdutos = 0;
        foreach ($pedido->items as $item) {
            $valorProdutos += $item->quantidade * number_format((float) $item->preco, 2, '.', '');
        }

        // Desconto = diferença entre valor dos produtos e valor cobrado
        $desconto = number_format($valorProdutos, 2, '.', '') - number_format($valorCobrado, 2, '.', '');

        return max(0, $desconto);
    }

    /**
     * Monta o JSON da ordem no formato Cielo Checkout.
     *
     * Replica fielmente CieloController::get_order() do legado.
     * Cada seção (Cart, Shipping, Customer, Options) segue
     * exatamente a mesma estrutura e validações do legado.
     *
     * @param Pedido $pedido   Pedido com items e pessoa carregados
     * @param float  $desconto Valor do desconto calculado
     * @return array Ordem no formato esperado pela API Cielo
     */
    private function buildOrder(Pedido $pedido, float $desconto): array
    {
        $order = [
            'OrderNumber' => $pedido->id,
            'Cart' => $this->buildCart($pedido, $desconto),
            'Shipping' => $this->buildShipping($pedido),
            'Customer' => $this->buildCustomer($pedido),
            'Options' => [
                'AntifraudEnabled' => false,
                // URL de retorno: cliente volta aqui após pagar na Cielo
                'ReturnUrl' => url('/pagamento/aguardar-cielo/' . $pedido->id),
            ],
        ];

        return $order;
    }

    /**
     * Monta a seção Cart (itens + desconto).
     *
     * Cada item segue o formato Cielo:
     * - Name: descrição do produto (max 128 chars)
     * - UnitPrice: preço em centavos SEM separador (ex: "1500" = R$15,00)
     * - Quantity: quantidade inteira
     * - Sku: código do produto (campo 'produto' no legado)
     * - Type: sempre "Asset" (produto físico)
     *
     * @param Pedido $pedido   Pedido com items carregados
     * @param float  $desconto Valor do desconto
     * @return array Seção Cart formatada
     */
    private function buildCart(Pedido $pedido, float $desconto): array
    {
        $cart = [];

        // Monta array de itens
        $items = [];
        foreach ($pedido->items as $item) {
            // Busca descrição do produto (via relacionamento ou fallback para código)
            $productName = $item->product_name; // accessor que busca descricao ou fallback

            $items[] = [
                'Name' => substr(trim($productName), 0, 128),
                // Preço em centavos sem separador (replica: number_format($preco, 2, '', ''))
                'UnitPrice' => number_format((float) $item->preco, 2, '', ''),
                'Quantity' => (int) $item->quantidade,
                'Sku' => trim($item->produto),
                'Type' => 'Asset',
            ];
        }

        $cart['Items'] = $items;

        // Desconto só é enviado se > 0 (replica legado)
        if ($desconto > 0) {
            $cart['Discount'] = [
                'Type' => 'Amount',
                // Valor em centavos sem separador
                'Value' => number_format($desconto, 2, '', ''),
            ];
        }

        return $cart;
    }

    /**
     * Monta a seção Shipping (tipo de entrega + endereço).
     *
     * Três cenários (replica legado):
     * 1. Retirada em loja: Type = "WithoutShippingPickUp" (sem endereço)
     * 2. Entrega com frete: Type = "FixedAmount" + Services com valor do frete
     * 3. Entrega frete grátis: Type = "Free"
     *
     * Para entregas, busca dados do logradouro pelo CEP na tabela
     * logradouros do banco legado (mesma consulta do CieloController::get_logradouro).
     *
     * @param Pedido $pedido Pedido com dados de entrega
     * @return array Seção Shipping formatada
     */
    private function buildShipping(Pedido $pedido): array
    {
        $shipping = [];

        if ($pedido->isPickup()) {
            // Retirada em loja — sem informações de frete/endereço
            $shipping['Type'] = 'WithoutShippingPickUp';
        } else {
            // Entrega — define tipo conforme valor do frete
            $valorFrete = (float) $pedido->valor_frete;

            if ($valorFrete > 0) {
                $shipping['Type'] = 'FixedAmount';
                $shipping['Services'] = [
                    [
                        'Name' => 'Frete',
                        // Valor em centavos sem separador
                        'Price' => number_format($valorFrete, 2, '', ''),
                    ],
                ];
            } else {
                $shipping['Type'] = 'Free';
            }

            // Busca dados completos do logradouro pelo CEP (replica legado)
            $cep = preg_replace('/\D/', '', $pedido->cep_entrega ?? '');

            if (!empty($cep)) {
                $logradouro = $this->getLogradouro($cep);

                if ($logradouro) {
                    $shipping['TargetZipCode'] = $cep;

                    $address = [
                        'Number' => trim($pedido->logradouro_complemento_numero_entrega ?? ''),
                    ];

                    // Complemento só é incluído se não vazio (replica legado)
                    $complemento = trim($pedido->logradouro_complemento_entrega ?? '');
                    if (strlen($complemento) > 0) {
                        $address['Complement'] = $complemento;
                    }

                    $address['Street'] = substr(trim($logradouro->EnderecoCompleto ?? ''), 0, 256);
                    $address['District'] = trim($logradouro->Bairro ?? '');
                    $address['City'] = trim($logradouro->CidadeCompleto ?? '');
                    $address['State'] = trim($logradouro->uf ?? '');

                    $shipping['Address'] = $address;
                }
            }
        }

        return $shipping;
    }

    /**
     * Busca dados do logradouro pelo CEP (replica legado get_logradouro).
     *
     * Join entre logradouros, bairros e localidades para obter
     * endereço completo, bairro, cidade e UF.
     *
     * @param string $cep CEP limpo (só dígitos)
     * @return object|null Objeto com EnderecoCompleto, Bairro, CidadeCompleto, uf
     */
    private function getLogradouro(string $cep): ?object
    {
        // Query idêntica ao legado CieloController::get_logradouro()
        return DB::connection('mysql_legacy')
            ->table('logradouros')
            ->join('bairros', 'logradouros.bairro_id', '=', 'bairros.id')
            ->join('localidades', 'logradouros.localidade_id', '=', 'localidades.id')
            ->where('logradouros.CEP', $cep)
            ->select(
                'logradouros.EnderecoCompleto',
                'logradouros.uf',
                'bairros.Bairro',
                'localidades.CidadeCompleto'
            )
            ->first();
    }

    /**
     * Monta a seção Customer (dados do cliente).
     *
     * Replica as validações do legado:
     * - Identity: CPF com exatamente 11 dígitos
     * - Email: validado com filter_var FILTER_VALIDATE_EMAIL
     * - Phone: telefone_celular ou telefone_residencial com 10-11 dígitos
     *
     * @param Pedido $pedido Pedido com pessoa carregada
     * @return array Seção Customer formatada
     */
    private function buildCustomer(Pedido $pedido): array
    {
        $pessoa = $pedido->pessoa;
        $customer = [];

        if (!$pessoa) {
            Log::warning('[CIELO] Pedido sem pessoa vinculada', ['pedido_id' => $pedido->id]);
            return $customer;
        }

        $customer['FullName'] = $pessoa->nome;

        // CPF: só envia se tiver exatamente 11 dígitos (replica legado)
        $cpf = preg_replace('/\D/', '', $pessoa->cpf ?? '');
        if (strlen($cpf) === 11) {
            $customer['Identity'] = $cpf;
        }

        // E-mail: só envia se for válido (replica legado)
        if (filter_var($pessoa->email_primario, FILTER_VALIDATE_EMAIL)) {
            $customer['Email'] = $pessoa->email_primario;
        }

        // Telefone: prioriza celular, fallback para residencial
        // Legado aceita 10 dígitos (fixo) e 11 dígitos (celular)
        $celular = preg_replace('/\D/', '', $pessoa->telefone_celular ?? '');
        $residencial = preg_replace('/\D/', '', $pessoa->telefone_residencial ?? '');

        if (strlen($celular) === 10 || strlen($celular) === 11) {
            $customer['Phone'] = $celular;
        } elseif (strlen($residencial) === 10 || strlen($residencial) === 11) {
            $customer['Phone'] = $residencial;
        }

        return $customer;
    }

    /**
     * Envia a ordem para a API Cielo via cURL.
     *
     * cURL configurado IDENTICAMENTE ao legado para evitar
     * incompatibilidades com a API da Cielo:
     * - SSL_VERIFYPEER = false (legado não verifica certificado)
     * - FOLLOWLOCATION = true (segue redirects)
     * - Header MerchantId para autenticação
     *
     * @param string $merchantId MerchantId da loja na Cielo
     * @param array  $order      Ordem no formato Cielo
     * @param int    $pedidoId   ID do pedido (para logs)
     * @return string|null URL de checkout, ou null em caso de erro
     */
    private function sendToApi(string $merchantId, array $order, int $pedidoId): ?string
    {
        $curl = curl_init();

        // Configuração idêntica ao legado (CieloController::ir_para_cielo)
        curl_setopt($curl, CURLOPT_URL, self::API_URL);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($order));
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'MerchantId: ' . $merchantId,
            'Content-Type: application/json',
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);

        curl_close($curl);

        // Log da requisição para debug (sem expor merchant_id completo)
        Log::info('[CIELO] Requisição enviada', [
            'pedido_id' => $pedidoId,
            'http_code' => $httpCode,
            'order_number' => $order['OrderNumber'] ?? null,
        ]);

        // Erro de cURL (timeout, DNS, etc.)
        if ($response === false) {
            Log::error('[CIELO] Erro cURL', [
                'pedido_id' => $pedidoId,
                'error' => $curlError,
            ]);
            return null;
        }

        $json = json_decode($response, true);

        // Verifica se a resposta contém a URL de checkout
        if (isset($json['settings']['checkoutUrl'])) {
            Log::info('[CIELO] Checkout URL obtida com sucesso', [
                'pedido_id' => $pedidoId,
                'checkout_url' => $json['settings']['checkoutUrl'],
            ]);

            return $json['settings']['checkoutUrl'];
        }

        // Resposta inesperada — loga para investigação
        Log::error('[CIELO] Resposta sem checkoutUrl', [
            'pedido_id' => $pedidoId,
            'http_code' => $httpCode,
            'response' => $response,
        ]);

        return null;
    }
}
