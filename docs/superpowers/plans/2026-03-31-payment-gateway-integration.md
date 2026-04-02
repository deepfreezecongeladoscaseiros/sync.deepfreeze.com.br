# Integração de Gateways de Pagamento (Cielo + Rede) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implementar Cielo Checkout e Rede e-Rede no sync.deepfreeze.com.br, replicando exatamente o comportamento do legado, usando o mesmo banco e webhook endpoint, com design moderno.

**Architecture:** O CheckoutController já cria pedidos no banco legado com `finalizado=0`. Este plano adiciona o fluxo pós-criação: redirect para gateway, webhook/callback, polling de status, e finalização. Os dois gateways (Cielo redirect + Rede direto) coexistem. O webhook da Cielo (`/siv_v2/cielo/notificacao`) continua no legado — o sync apenas redireciona e faz polling. A Rede processa diretamente no sync com o SDK PHP.

**Tech Stack:** Laravel 10, PHP 8.1+, Rede e-Rede SDK (PHP), Cielo Checkout API v1 (cURL), Bootstrap 3, jQuery, banco legado MySQL (MyISAM)

---

## Premissas e Decisões Arquiteturais

1. **Webhook Cielo = mantido no legado** — O endpoint `POST /siv_v2/cielo/notificacao` no servidor legado já está cadastrado na Cielo. Ele grava em `pagamentos_cielo` e seta `finalizado=1`. O sync NÃO precisa receber o webhook — apenas redirecionar o cliente para o Cielo e depois fazer polling até o webhook do legado confirmar.

2. **ReturnUrl do Cielo** — Quando pedidos vêm do sync, o `ReturnUrl` deve apontar para o sync: `https://sync.deepfreeze.com.br/pagamento/aguardar-cielo/{pedido_id}`. O checkout Cielo redireciona o cliente de volta para essa URL.

3. **Rede SDK** — Copiar o SDK Rede do legado (`html/app/Vendor/Rede/`) para o sync como package em `app/Libraries/Rede/`. Não usar Composer pois não é um pacote registrado.

4. **Credenciais por loja** — Lidas de `lojas.merchant_id` (Cielo) e `lojas.pv` + `lojas.token` (Rede) do banco legado.

5. **Pedidos offline** — Dinheiro/Cheque/PIX já funcionam (criam pedido com `finalizado=0` e direcionam para confirmação). Ajustar para que dinheiro em retirada na loja faça `finalizado=1` direto (como o legado).

6. **Model PagamentoRede** — Criar no sync (não existe ainda) para gravar na tabela `pagamentos_rede` do banco legado.

---

## File Structure

### Criar:
| Arquivo | Responsabilidade |
|---------|-----------------|
| `app/Services/CieloCheckoutService.php` | Monta order JSON, chama API Cielo, retorna checkoutUrl |
| `app/Services/RedePaymentService.php` | Processa transação Rede (crédito/débito), grava `pagamentos_rede` |
| `app/Models/Legacy/PagamentoRede.php` | Model da tabela `pagamentos_rede` do banco legado |
| `app/Libraries/Rede/` (diretório) | SDK Rede copiado do legado |
| `resources/views/storefront/payment/aguardar-cielo.blade.php` | Polling de confirmação Cielo (auto-refresh 15s) |
| `resources/views/storefront/payment/rede-cartao.blade.php` | Form de cartão para Rede (crédito/débito) |
| `public/storefront/css/payment.css` | CSS específico do fluxo de pagamento |

### Modificar:
| Arquivo | Mudança |
|---------|---------|
| `app/Http/Controllers/Storefront/CheckoutController.php` | `store()`: após criar pedido, redirecionar para gateway se online |
| `app/Http/Controllers/Storefront/PaymentController.php` | Adicionar: `aguardarCielo()`, `statusCielo()`, `redeCredito()`, `redeConsultarTid()` |
| `app/Services/PaymentService.php` | Adicionar: `isOnlinePayment()`, `getPaymentGateway()`, `getLojaCredentials()` |
| `app/Models/Legacy/Loja.php` | Adicionar campos de credenciais no `$columnMap` |
| `routes/web.php` | Adicionar rotas de gateway (aguardar-cielo, rede/credito, rede/consultar-tid) |
| `resources/views/storefront/checkout/index.blade.php` | Mostrar ícones/bandeiras das formas de pagamento, separar online vs offline |

---

## Tasks

### Task 1: Model PagamentoRede + Loja credentials

**Files:**
- Create: `app/Models/Legacy/PagamentoRede.php`
- Modify: `app/Models/Legacy/Loja.php`

- [ ] **Step 1: Criar o Model PagamentoRede**

```php
<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Pagamento Rede (tabela 'pagamentos_rede' do banco legado)
 *
 * Registra transações de cartão processadas pela Rede e-Rede.
 * Compatível com o SIV que lê esta tabela para conciliação financeira.
 *
 * Tabela: novo.pagamentos_rede
 * Engine: MyISAM
 * Charset: utf8mb4
 *
 * Return codes:
 *   '00'  = Autorizado/Aprovado
 *   '220' = 3D Secure pendente (redirecionar para autenticação)
 *   Outros = Negado
 */
class PagamentoRede extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'pagamentos_rede';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    // Return codes conhecidos
    const CODE_APPROVED = '00';
    const CODE_3DS_PENDING = '220';

    protected $fillable = [
        'pedido_id',
        'amount',                       // Valor da transação (decimal)
        'transaction_id',               // TID da Rede
        'reference',                    // Referência (pedido_id + timestamp)
        'authorization_code',           // Código de autorização
        'nsu',                          // NSU
        'bin',                          // 6 primeiros dígitos do cartão
        'last4',                        // 4 últimos dígitos do cartão
        'return_code_authorization',    // '00' = sucesso, '220' = 3DS
        'return_message_authorization', // Mensagem de retorno
        'kind',                         // 'Credit' ou 'Debit'
        'bandeira_rede',                // Bandeira detectada (visa, mastercard, elo, etc.)
        'authorization_datetime',       // Data/hora da autorização
        'capture_datetime',             // Data/hora da captura
        'transaction_json',             // JSON completo da transação (card data mascarado)
        'authorization_json',           // JSON da autorização
        'capture_json',                 // JSON da captura
        'return_authorization_status',  // Status da autorização
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // ==================== RELATIONSHIPS ====================

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    // ==================== HELPERS ====================

    /**
     * Verifica se a transação foi aprovada
     */
    public function isApproved(): bool
    {
        return $this->return_code_authorization === self::CODE_APPROVED;
    }

    /**
     * Verifica se está aguardando 3D Secure
     */
    public function isPending3ds(): bool
    {
        return $this->return_code_authorization === self::CODE_3DS_PENDING;
    }

    /**
     * Cria registro de pagamento a partir da transação Rede.
     * Mascara dados sensíveis do cartão antes de gravar.
     *
     * @param int $pedidoId ID do pedido
     * @param object $transaction Objeto Transaction do SDK Rede
     * @return self
     */
    public static function createFromTransaction(int $pedidoId, $transaction): self
    {
        // Serializa a transação para JSON e mascara dados sensíveis
        $transactionData = json_decode(json_encode($transaction), true);
        if (isset($transactionData['cardNumber'])) {
            $transactionData['cardNumber'] = '*';
        }
        if (isset($transactionData['securityCode'])) {
            $transactionData['securityCode'] = '*';
        }

        return self::create([
            'pedido_id'                    => $pedidoId,
            'amount'                       => $transaction->getAmount(),
            'transaction_id'               => $transaction->getTid(),
            'reference'                    => $transaction->getReference(),
            'authorization_code'           => $transaction->getAuthorizationCode(),
            'nsu'                          => $transaction->getNsu(),
            'bin'                          => $transaction->getCardBin(),
            'last4'                        => $transaction->getLast4(),
            'return_code_authorization'    => $transaction->getReturnCode(),
            'return_message_authorization' => $transaction->getReturnMessage(),
            'kind'                         => null, // Será setado depois
            'bandeira_rede'                => null, // Será setado depois
            'transaction_json'             => json_encode($transactionData),
        ]);
    }

    /**
     * Salva dados de autorização e captura após consulta.
     */
    public function saveAuthorizationAndCapture(array $authorization, array $capture): self
    {
        $this->update([
            'authorization_json'    => json_encode($authorization),
            'capture_json'          => json_encode($capture),
            'authorization_datetime' => $authorization['dateTime'] ?? null,
            'capture_datetime'      => $capture['dateTime'] ?? null,
        ]);

        return $this;
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Transação aprovada de um pedido
     */
    public function scopeApprovedForOrder($query, int $pedidoId)
    {
        return $query->where('pedido_id', $pedidoId)
            ->where('return_code_authorization', self::CODE_APPROVED);
    }

    /**
     * Scope: Transação pendente de 3D Secure
     */
    public function scopePending3dsForOrder($query, int $pedidoId)
    {
        return $query->where('pedido_id', $pedidoId)
            ->where('return_code_authorization', self::CODE_3DS_PENDING);
    }
}
```

- [ ] **Step 2: Adicionar campos de credenciais no Model Loja**

Em `app/Models/Legacy/Loja.php`, adicionar ao `$columnMap`:

```php
    protected $columnMap = [
        'name'               => 'nome',
        'code'               => 'codigo',
        'active'             => 'loja_ativa',
        'allow_pickup'       => 'retirar_na_loja',
        'delivery_region_id' => 'entregas_regiao_id',
        'is_franchise'       => 'franquia',
        'is_branch'          => 'filial',
        'manager'            => 'gerente',
        'phone_number'       => 'telefone',
        'image'              => 'imagem_loja',
        'maps_link'          => 'link_google_maps',
        'email_address'      => 'email_loja',
        // Credenciais de gateway de pagamento
        'cielo_merchant_id'  => 'merchant_id',     // MerchantId para Cielo Checkout API
        'rede_pv'            => 'pv',              // Ponto de Venda para Rede e-Rede
        'rede_token'         => 'token',           // Token de autenticação Rede e-Rede
    ];
```

- [ ] **Step 3: Commit**

```bash
git add app/Models/Legacy/PagamentoRede.php app/Models/Legacy/Loja.php
git commit -m "feat: adicionar Model PagamentoRede e credenciais de gateway no Model Loja

Model PagamentoRede para tabela pagamentos_rede do banco legado.
Campos de credenciais (merchant_id, pv, token) mapeados no Model Loja."
```

---

### Task 2: Copiar SDK Rede do legado

**Files:**
- Create: `app/Libraries/Rede/` (copiar todos os arquivos de `siv_deepfreeze/html/app/Vendor/Rede/`)

- [ ] **Step 1: Copiar SDK e adaptar namespace**

```bash
# Copiar o SDK Rede do legado para o sync
cp -r /Users/eduardomacedo/source/deepfreeze/siv_deepfreeze/html/app/Vendor/Rede/ /Users/eduardomacedo/source/deepfreeze/sync.deepfreeze.com.br/app/Libraries/Rede/
```

Verificar que os arquivos principais existem:
- `eRede.php`
- `Transaction.php`
- `Store.php`
- `Environment.php`
- `ThreeDSecure.php`
- `Url.php`
- `Service/` (diretório com classes de serviço)
- `Exception/` (diretório com exceções)

- [ ] **Step 2: Registrar autoload no composer.json**

No `composer.json`, na seção `autoload.classmap`, adicionar:

```json
"classmap": [
    "app/Libraries/Rede"
]
```

Ou alternativamente, se os arquivos já usam namespace `\Rede\`:

```json
"autoload": {
    "psr-4": {
        "App\\": "app/",
        "Database\\Factories\\": "database/factories/",
        "Database\\Seeders\\": "database/seeders/"
    },
    "classmap": [
        "app/Libraries/Rede"
    ],
    "files": [
        "app/Helpers/ThemeHelper.php"
    ]
}
```

- [ ] **Step 3: Rodar composer dump-autoload**

```bash
cd /Users/eduardomacedo/source/deepfreeze/sync.deepfreeze.com.br
composer dump-autoload
```

Expected: Sem erros.

- [ ] **Step 4: Verificar que o SDK carrega**

```bash
cd /Users/eduardomacedo/source/deepfreeze/sync.deepfreeze.com.br
php artisan tinker --execute="echo class_exists('\Rede\eRede') ? 'OK' : 'FALHOU';"
```

Expected: `OK`

- [ ] **Step 5: Commit**

```bash
git add app/Libraries/Rede/ composer.json
git commit -m "feat: copiar SDK Rede e-Rede do legado para app/Libraries/Rede

SDK copiado integralmente do legado (html/app/Vendor/Rede/).
Registrado no classmap do composer.json para autoload."
```

---

### Task 3: CieloCheckoutService

**Files:**
- Create: `app/Services/CieloCheckoutService.php`

- [ ] **Step 1: Criar o service**

```php
<?php

namespace App\Services;

use App\Models\Legacy\Loja;
use App\Models\Legacy\Pedido;
use App\Models\Legacy\PedidoProduto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service de integração com Cielo Checkout API.
 *
 * Replica exatamente o comportamento do legado (siv_v2/CieloController::ir_para_cielo),
 * montando o JSON no formato Cielo Checkout v1 e enviando via cURL.
 *
 * Fluxo:
 * 1. Monta o objeto order (items, shipping, customer, options)
 * 2. POST para https://cieloecommerce.cielo.com.br/api/public/v1/orders
 * 3. Recebe checkoutUrl → redireciona o cliente
 * 4. Cielo processa pagamento e envia webhook para /siv_v2/cielo/notificacao (legado)
 * 5. Sync faz polling em /pagamento/aguardar-cielo/{id} até confirmar
 *
 * API: https://cieloecommerce.cielo.com.br/api/public/v1/orders
 * Header: MerchantId (de lojas.merchant_id)
 */
class CieloCheckoutService
{
    // URL da API Cielo Checkout (produção)
    const API_URL = 'https://cieloecommerce.cielo.com.br/api/public/v1/orders';

    /**
     * Envia pedido para Cielo Checkout e retorna a URL de redirect.
     *
     * @param Pedido $pedido Pedido já criado no banco legado
     * @param int $lojaId ID da loja (para buscar merchant_id)
     * @return string|null URL do checkout Cielo, ou null se falhou
     */
    public function createCheckoutOrder(Pedido $pedido, int $lojaId): ?string
    {
        $loja = Loja::find($lojaId);

        if (!$loja || !$loja->merchant_id) {
            Log::error('[CIELO] Loja sem merchant_id', ['loja_id' => $lojaId]);
            return null;
        }

        // Garante formas_pagamento_id = 46 (Cielo) e finalizado = 0
        $pedido->update([
            'formas_pagamento_id' => PaymentService::FORMA_CIELO,
            'finalizado' => 0,
        ]);

        $order = $this->buildOrder($pedido);

        Log::info('[CIELO] Enviando pedido para Cielo Checkout', [
            'pedido_id' => $pedido->id,
            'loja_id'   => $lojaId,
        ]);

        $response = $this->callCieloApi($order, $loja->merchant_id);

        if (isset($response['settings']['checkoutUrl'])) {
            Log::info('[CIELO] checkoutUrl recebido', [
                'pedido_id'    => $pedido->id,
                'checkout_url' => $response['settings']['checkoutUrl'],
            ]);
            return $response['settings']['checkoutUrl'];
        }

        Log::error('[CIELO] Resposta sem checkoutUrl', [
            'pedido_id' => $pedido->id,
            'response'  => $response,
        ]);

        return null;
    }

    /**
     * Monta o objeto Order no formato Cielo Checkout v1.
     * Replica exatamente o formato do legado (siv_v2/CieloController::get_order).
     */
    private function buildOrder(Pedido $pedido): array
    {
        $pessoa = $pedido->pessoa;
        $items = $pedido->items()->with('product')->get();

        // --- Cart ---
        $cartItems = [];
        $valorProdutos = 0;

        foreach ($items as $item) {
            // Nome do produto: usar descrição do produto legado ou código
            $productName = $item->product
                ? substr(trim($item->product->descricao), 0, 128)
                : substr(trim($item->produto), 0, 128);

            $cartItems[] = [
                'Name'      => $productName,
                'UnitPrice' => number_format($item->preco, 2, '', ''), // Centavos sem separador
                'Quantity'  => (int) $item->quantidade,
                'Sku'       => trim($item->produto), // Código do produto
                'Type'      => 'Asset',
            ];

            $valorProdutos += ($item->quantidade * $item->preco);
        }

        $cart = ['Items' => $cartItems];

        // --- Desconto ---
        // Calcula desconto como diferença entre soma dos itens e (valor_total - frete)
        $valorCobrado = $pedido->valor_total - $pedido->valor_frete;
        $desconto = $valorProdutos - $valorCobrado;

        if ($desconto > 0) {
            $cart['Discount'] = [
                'Type'  => 'Amount',
                'Value' => number_format($desconto, 2, '', ''),
            ];
        }

        // --- Shipping ---
        $shipping = [];

        if ($pedido->loja_retirada_id > 0) {
            // Retirada na loja
            $shipping['Type'] = 'WithoutShippingPickUp';
        } elseif ($pedido->valor_frete > 0) {
            // Entrega com frete
            $shipping['Type'] = 'FixedAmount';
            $shipping['Services'] = [[
                'Name'  => 'Frete',
                'Price' => number_format($pedido->valor_frete, 2, '', ''),
            ]];
        } else {
            // Frete grátis
            $shipping['Type'] = 'Free';
        }

        // Endereço de entrega (se não for retirada)
        if ($pedido->loja_retirada_id <= 0 && $pedido->cep_entrega) {
            $cep = preg_replace('/\D/', '', $pedido->cep_entrega);
            $shipping['TargetZipCode'] = $cep;

            $address = [];
            if ($pedido->logradouro_complemento_numero_entrega) {
                $address['Number'] = trim($pedido->logradouro_complemento_numero_entrega);
            }
            if ($pedido->logradouro_complemento_entrega) {
                $address['Complement'] = trim($pedido->logradouro_complemento_entrega);
            }
            if ($pedido->logradouro_entrega) {
                $address['Street'] = substr(trim($pedido->logradouro_entrega), 0, 256);
            }
            if ($pedido->bairro_entrega) {
                $address['District'] = trim($pedido->bairro_entrega);
            }
            if ($pedido->cidade_entrega) {
                $address['City'] = trim($pedido->cidade_entrega);
            }
            if ($pedido->uf_entrega) {
                $address['State'] = trim($pedido->uf_entrega);
            }

            if (!empty($address)) {
                $shipping['Address'] = $address;
            }
        }

        // --- Customer ---
        $customer = [];
        if ($pessoa) {
            $customer['FullName'] = $pessoa->nome;

            $cpf = preg_replace('/\D/', '', $pessoa->cpf ?? '');
            if (strlen($cpf) === 11) {
                $customer['Identity'] = $cpf;
            }

            if (filter_var($pessoa->email_primario, FILTER_VALIDATE_EMAIL)) {
                $customer['Email'] = $pessoa->email_primario;
            }

            $telefone = preg_replace('/\D/', '', $pessoa->telefone_celular ?? $pessoa->telefone_residencial ?? '');
            if (strlen($telefone) >= 10 && strlen($telefone) <= 11) {
                $customer['Phone'] = $telefone;
            }
        }

        // --- Options ---
        // ReturnUrl: após pagamento, Cielo redireciona o cliente para esta URL
        $returnUrl = url('/pagamento/aguardar-cielo/' . $pedido->id);

        $options = [
            'AntifraudEnabled' => false,
            'ReturnUrl'        => $returnUrl,
        ];

        // --- Order completo ---
        return [
            'OrderNumber' => $pedido->id,
            'Cart'        => $cart,
            'Shipping'    => $shipping,
            'Customer'    => $customer,
            'Options'     => $options,
        ];
    }

    /**
     * Chama a API Cielo via cURL.
     * Replica exatamente o cURL do legado.
     */
    private function callCieloApi(array $order, string $merchantId): ?array
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => self::API_URL,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($order),
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER     => [
                'MerchantId: ' . $merchantId,
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if (curl_errno($curl)) {
            Log::error('[CIELO] cURL error', [
                'error'     => curl_error($curl),
                'http_code' => $httpCode,
            ]);
            curl_close($curl);
            return null;
        }

        curl_close($curl);

        $json = json_decode($response, true);

        Log::info('[CIELO] API response', [
            'http_code' => $httpCode,
            'has_url'   => isset($json['settings']['checkoutUrl']),
        ]);

        return $json;
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Services/CieloCheckoutService.php
git commit -m "feat: criar CieloCheckoutService para integração com Cielo Checkout API

Monta order JSON no formato Cielo Checkout v1, envia via cURL e retorna checkoutUrl.
Replica exatamente o comportamento do legado (siv_v2/CieloController::ir_para_cielo).
ReturnUrl aponta para /pagamento/aguardar-cielo/{id} do sync."
```

---

### Task 4: RedePaymentService

**Files:**
- Create: `app/Services/RedePaymentService.php`

- [ ] **Step 1: Criar o service**

```php
<?php

namespace App\Services;

use App\Models\Legacy\FormaPagamento;
use App\Models\Legacy\Loja;
use App\Models\Legacy\PagamentoRede;
use App\Models\Legacy\Pedido;
use Illuminate\Support\Facades\Log;

/**
 * Service de integração com Rede e-Rede.
 *
 * Replica o comportamento do legado (RedeController::credito).
 * Processa pagamentos de crédito e débito com o SDK Rede.
 * Débito requer 3D Secure (DECLINE_ON_FAILURE).
 *
 * Fluxo crédito:
 * 1. Valida dados do cartão
 * 2. Cria Transaction com creditCard()->capture(TRUE)
 * 3. Se returnCode='00': grava pagamentos_rede, finaliza pedido
 * 4. Se erro: retorna mensagem de erro
 *
 * Fluxo débito:
 * 1. Valida dados do cartão
 * 2. Cria Transaction com debitCard()->capture(TRUE)->threeDSecure(DECLINE_ON_FAILURE)
 * 3. Se returnCode='220': redireciona para 3D Secure URL
 * 4. Após 3D Secure: consulta por reference e finaliza se aprovado
 */
class RedePaymentService
{
    /**
     * Processa pagamento com cartão de crédito.
     *
     * @return array ['success' => bool, 'error' => string|null, 'pagamento' => PagamentoRede|null]
     */
    public function processCredit(
        Pedido $pedido,
        int $lojaId,
        array $cardData
    ): array {
        return $this->processCard($pedido, $lojaId, $cardData, 'CREDITO');
    }

    /**
     * Processa pagamento com cartão de débito.
     * Retorna redirect URL para 3D Secure se returnCode='220'.
     *
     * @return array ['success' => bool, 'error' => string|null, 'redirect_3ds' => string|null, 'pagamento' => PagamentoRede|null]
     */
    public function processDebit(
        Pedido $pedido,
        int $lojaId,
        array $cardData
    ): array {
        return $this->processCard($pedido, $lojaId, $cardData, 'DEBITO');
    }

    /**
     * Consulta transação pendente de 3D Secure.
     * Chamado após callback do 3D Secure.
     *
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function consultAfter3ds(int $pedidoId, int $lojaId): array
    {
        $loja = Loja::find($lojaId);
        if (!$loja || !$loja->pv || !$loja->token) {
            return ['success' => false, 'error' => 'Loja sem credenciais Rede'];
        }

        // Busca transação pendente de consulta (returnCode='220')
        $pagamento = PagamentoRede::pending3dsForOrder($pedidoId)
            ->orderBy('id', 'desc')
            ->first();

        if (!$pagamento) {
            return ['success' => false, 'error' => 'Transação pendente não encontrada'];
        }

        try {
            $store = new \Rede\Store($loja->pv, $loja->token, \Rede\Environment::production());
            $transaction = (new \Rede\eRede($store))->getByReference($pagamento->reference);

            if ($transaction->getAuthorization()->getReturnCode() === '00') {
                // Autorizado — salva dados de auth/capture
                $authData = json_decode($transaction->getAuthorization()->getJSONEncode(), true);
                $captureData = json_decode($transaction->getCapture()->getJSONEncode(), true);
                $pagamento->saveAuthorizationAndCapture($authData, $captureData);

                // Detecta bandeira e atualiza forma de pagamento
                $this->updatePaymentMethodFromBrand($pedidoId, $pagamento);

                // Finaliza o pedido
                $this->finalizePedido($pedidoId);

                return ['success' => true, 'error' => null];
            }

            return [
                'success' => false,
                'error'   => $transaction->getAuthorization()->getReturnMessage() ?? 'Pagamento não autorizado',
            ];
        } catch (\Exception $e) {
            Log::error('[REDE] Erro ao consultar 3D Secure', [
                'pedido_id' => $pedidoId,
                'error'     => $e->getMessage(),
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Processa cartão (crédito ou débito).
     */
    private function processCard(
        Pedido $pedido,
        int $lojaId,
        array $cardData,
        string $tipo
    ): array {
        $loja = Loja::find($lojaId);
        if (!$loja || !$loja->pv || !$loja->token) {
            return ['success' => false, 'error' => 'Loja sem credenciais Rede'];
        }

        // Valida dados do cartão
        $validation = $this->validateCardData($cardData);
        if (!empty($validation)) {
            return ['success' => false, 'error' => implode(', ', $validation)];
        }

        try {
            $store = new \Rede\Store($loja->pv, $loja->token, \Rede\Environment::production());

            // Referência única: pedido_id + timestamp (replica legado)
            $reference = $pedido->id . time();

            if ($tipo === 'CREDITO') {
                $transaction = (new \Rede\Transaction($pedido->valor_total, $reference))
                    ->creditCard(
                        $cardData['card_number'],
                        $cardData['card_cvv'],
                        $cardData['card_expiration_month'],
                        $cardData['card_expiration_year'],
                        $cardData['holder_name']
                    )
                    ->capture(true);
            } else {
                // Débito com 3D Secure obrigatório
                $transaction = (new \Rede\Transaction($pedido->valor_total, $reference))
                    ->debitCard(
                        $cardData['card_number'],
                        $cardData['card_cvv'],
                        $cardData['card_expiration_month'],
                        $cardData['card_expiration_year'],
                        $cardData['holder_name']
                    )
                    ->capture(true);

                $transaction->threeDSecure(\Rede\ThreeDSecure::DECLINE_ON_FAILURE);

                // URLs de retorno do 3D Secure
                $successUrl = url("/pagamento/rede/consultar-tid/{$pedido->id}/{$lojaId}");
                $failureUrl = url("/checkout?erro_rede=FALHA");

                $transaction->addUrl($successUrl, \Rede\Url::THREE_D_SECURE_SUCCESS);
                $transaction->addUrl($failureUrl, \Rede\Url::THREE_D_SECURE_FAILURE);
            }

            // Executa transação
            $transaction = (new \Rede\eRede($store))->create($transaction);

            // Grava registro na tabela pagamentos_rede
            $pagamento = PagamentoRede::createFromTransaction($pedido->id, $transaction);

            // Detecta bandeira pelo BIN
            $bandeira = $this->detectBrand($cardData['card_number']);
            $pagamento->update([
                'kind'          => $tipo === 'CREDITO' ? 'Credit' : 'Debit',
                'bandeira_rede' => $bandeira,
            ]);

            Log::info('[REDE] Transação processada', [
                'pedido_id'   => $pedido->id,
                'return_code' => $transaction->getReturnCode(),
                'tipo'        => $tipo,
                'bandeira'    => $bandeira,
            ]);

            if ($transaction->getReturnCode() === '00') {
                // Aprovado — busca detalhes completos
                $fullTransaction = (new \Rede\eRede($store))->get($transaction->getTid());
                $authData = json_decode($fullTransaction->getAuthorization()->getJSONEncode(), true);
                $captureData = json_decode($fullTransaction->getCapture()->getJSONEncode(), true);
                $pagamento->saveAuthorizationAndCapture($authData, $captureData);

                // Atualiza forma de pagamento real (pela bandeira detectada)
                $this->updatePaymentMethodFromBrand($pedido->id, $pagamento);

                // Finaliza pedido
                $this->finalizePedido($pedido->id);

                return ['success' => true, 'error' => null, 'pagamento' => $pagamento];
            } elseif ($transaction->getReturnCode() === '220') {
                // 3D Secure necessário — retorna URL de redirect
                return [
                    'success'      => false,
                    'error'        => null,
                    'redirect_3ds' => $transaction->getThreeDSecure()->getUrl(),
                    'pagamento'    => $pagamento,
                ];
            } else {
                // Negado
                return [
                    'success' => false,
                    'error'   => $transaction->getReturnMessage() ?? 'Transação negada (código: ' . $transaction->getReturnCode() . ')',
                ];
            }
        } catch (\Exception $e) {
            Log::error('[REDE] Exceção ao processar pagamento', [
                'pedido_id' => $pedido->id,
                'tipo'      => $tipo,
                'error'     => $e->getMessage(),
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Valida dados do cartão.
     * Replica PagamentoRede::validar() do legado.
     */
    private function validateCardData(array $data): array
    {
        $errors = [];

        if (empty($data['card_number'])) {
            $errors[] = 'Número do cartão é obrigatório';
        } elseif (!$this->luhnCheck($data['card_number'])) {
            $errors[] = 'Número do cartão inválido';
        }

        if (empty($data['holder_name'])) {
            $errors[] = 'Nome do titular é obrigatório';
        }

        if (empty($data['card_expiration_month']) || empty($data['card_expiration_year'])) {
            $errors[] = 'Data de validade é obrigatória';
        }

        if (empty($data['card_cvv'])) {
            $errors[] = 'CVV é obrigatório';
        }

        return $errors;
    }

    /**
     * Algoritmo de Luhn para validação de número de cartão.
     * Replica PagamentoRede::validateCcNum() do legado.
     */
    private function luhnCheck(string $number): bool
    {
        $number = preg_replace('/\D/', '', $number);

        if (strlen($number) < 13 || strlen($number) > 19) {
            return false;
        }

        $sum = 0;
        $length = strlen($number);
        $parity = $length % 2;

        for ($i = 0; $i < $length; $i++) {
            $digit = (int) $number[$i];

            if ($i % 2 === $parity) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
        }

        return ($sum % 10) === 0;
    }

    /**
     * Detecta bandeira do cartão pelo BIN (primeiros dígitos).
     * Replica PagamentoRede::obterBandeira() do legado.
     */
    private function detectBrand(string $number): ?string
    {
        $number = preg_replace('/\D/', '', $number);

        $patterns = [
            'elo'        => '/^(636368|438935|504175|451416|636297|5067|4576|4011|506699)/',
            'discover'   => '/^(6011|622|64|65)/',
            'diners'     => '/^(301|305|36|38)/',
            'amex'       => '/^(34|37)/',
            'hipercard'  => '/^(606282|3841)/',
            'aura'       => '/^50/',
            'jcb'        => '/^35/',
            'mastercard' => '/^5/',
            'visa'       => '/^4/',
        ];

        foreach ($patterns as $brand => $pattern) {
            if (preg_match($pattern, $number)) {
                return $brand;
            }
        }

        return null;
    }

    /**
     * Atualiza a forma_pagamento_id do pedido com base na bandeira detectada.
     * Replica get_forma_pagamento_id_bandeira() do legado.
     */
    private function updatePaymentMethodFromBrand(int $pedidoId, PagamentoRede $pagamento): void
    {
        $kind = $pagamento->kind; // 'Credit' ou 'Debit'
        $bandeira = $pagamento->bandeira_rede;

        if (!$bandeira || !$kind) {
            return;
        }

        // Busca forma de pagamento real pela bandeira e tipo
        $column = $kind === 'Debit' ? 'rede_debito' : 'rede_credito';

        $forma = FormaPagamento::where('ativo', 1)
            ->where($column, 1)
            ->whereRaw("LOWER(bandeira) LIKE ?", ['%' . strtolower($bandeira) . '%'])
            ->first();

        if ($forma) {
            Pedido::where('id', $pedidoId)->update(['formas_pagamento_id' => $forma->id]);

            Log::info('[REDE] Forma de pagamento atualizada pela bandeira', [
                'pedido_id' => $pedidoId,
                'forma_id'  => $forma->id,
                'bandeira'  => $bandeira,
                'kind'      => $kind,
            ]);
        }
    }

    /**
     * Finaliza o pedido (finalizado=1 + data_finalizado).
     */
    private function finalizePedido(int $pedidoId): void
    {
        Pedido::where('id', $pedidoId)->update([
            'finalizado'     => 1,
            'data_finalizado' => now(),
        ]);

        Log::info('[REDE] Pedido finalizado', ['pedido_id' => $pedidoId]);
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Services/RedePaymentService.php
git commit -m "feat: criar RedePaymentService para integração com Rede e-Rede

Processa crédito e débito com SDK Rede. Débito com 3D Secure obrigatório.
Valida cartão (Luhn), detecta bandeira pelo BIN, grava em pagamentos_rede.
Replica comportamento do legado (RedeController::credito)."
```

---

### Task 5: PaymentService — adicionar helpers de gateway

**Files:**
- Modify: `app/Services/PaymentService.php`

- [ ] **Step 1: Adicionar métodos helper**

Adicionar os seguintes métodos ao final da classe `PaymentService`:

```php
    /**
     * Verifica se uma forma de pagamento é online (requer gateway).
     */
    public function isOnlinePayment(int $formaPagamentoId): bool
    {
        $forma = FormaPagamento::where('id', $formaPagamentoId)->where('ativo', 1)->first();
        return $forma && $forma->online == 1;
    }

    /**
     * Determina qual gateway usar para a forma de pagamento.
     *
     * @return string 'cielo', 'rede_credito', 'rede_debito', 'offline'
     */
    public static function getPaymentGateway(int $formaPagamentoId): string
    {
        return match ($formaPagamentoId) {
            self::FORMA_CIELO        => 'cielo',
            self::FORMA_REDE_CREDITO => 'rede_credito',
            self::FORMA_REDE_DEBITO  => 'rede_debito',
            default                  => 'offline',
        };
    }

    /**
     * Busca a loja que vai processar o pagamento.
     *
     * Para entrega: usa a loja de entrega (calculada pelo ShippingService via CEP→região→loja).
     * Para retirada: usa a loja de retirada.
     * Fallback: loja 2 (Tijuca, sede principal).
     */
    public function getPaymentLojaId(?int $lojaRetiradaId, ?int $lojaEntregaId): int
    {
        if ($lojaRetiradaId && $lojaRetiradaId > 0) {
            return $lojaRetiradaId;
        }

        if ($lojaEntregaId && $lojaEntregaId > 0) {
            return $lojaEntregaId;
        }

        // Fallback: loja 2 (sede principal)
        return 2;
    }

    /**
     * Verifica se o pedido foi pago via Rede (tabela pagamentos_rede).
     */
    public function isPaidRede(int $pedidoId): bool
    {
        return \App\Models\Legacy\PagamentoRede::approvedForOrder($pedidoId)->exists();
    }
```

- [ ] **Step 2: Commit**

```bash
git add app/Services/PaymentService.php
git commit -m "feat: adicionar helpers de gateway no PaymentService

isOnlinePayment(), getPaymentGateway(), getPaymentLojaId(), isPaidRede().
Determina qual gateway usar com base no ID da forma de pagamento."
```

---

### Task 6: Rotas e PaymentController — fluxo Cielo + Rede

**Files:**
- Modify: `app/Http/Controllers/Storefront/PaymentController.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Reescrever PaymentController com actions de gateway**

```php
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
use Illuminate\View\View;

/**
 * Controller de pagamento da loja.
 *
 * Gerencia 3 fluxos de pagamento:
 * 1. Cielo Checkout (redirect): redireciona para Cielo, faz polling até webhook confirmar
 * 2. Rede e-Rede (direto): form de cartão no site, processa via SDK
 * 3. Offline (dinheiro/PIX): pedido criado com finalizado=0, confirmação manual
 *
 * O webhook da Cielo continua no legado (/siv_v2/cielo/notificacao).
 * O sync apenas redireciona e faz polling na tabela pagamentos_cielo.
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

    // ==================== CIELO CHECKOUT ====================

    /**
     * Redireciona para Cielo Checkout.
     * Chamado após criação do pedido no CheckoutController.
     */
    public function redirectToCielo(int $pedidoId, int $lojaId): RedirectResponse
    {
        $pedido = Pedido::find($pedidoId);

        if (!$pedido) {
            return redirect('/checkout')->with('error', 'Pedido não encontrado.');
        }

        $checkoutUrl = $this->cieloService->createCheckoutOrder($pedido, $lojaId);

        if (!$checkoutUrl) {
            Log::error('[PAYMENT] Falha ao criar checkout Cielo', ['pedido_id' => $pedidoId]);
            return redirect('/checkout')
                ->with('error', 'Erro ao conectar com o gateway de pagamento. Tente novamente.');
        }

        return redirect()->away($checkoutUrl);
    }

    /**
     * Aguarda confirmação de pagamento Cielo (polling).
     * Cielo redireciona o cliente para cá após pagar.
     * A view faz auto-refresh a cada 15 segundos.
     *
     * Replica: legado CieloController::aguardar_confirmacao_pagamento
     */
    public function aguardarCielo(int $pedidoId, int $tentativa = 0): View|RedirectResponse
    {
        $pedido = Pedido::find($pedidoId);

        if (!$pedido) {
            return redirect('/')->with('error', 'Pedido não encontrado.');
        }

        // Verifica se já está pago (webhook da Cielo já processou)
        if ($this->paymentService->isPaid($pedidoId)) {
            return redirect()
                ->route('checkout.confirmation', ['orderNumber' => $pedido->sessao])
                ->with('success', 'Pagamento confirmado!');
        }

        // Verifica status de erro
        $ultimoStatus = PagamentoCielo::where('pedido_id', $pedidoId)
            ->orderBy('created', 'desc')
            ->value('status_pagamento');

        if ($ultimoStatus == PagamentoCielo::STATUS_NEGADO) {
            return redirect('/checkout')
                ->with('error', 'Pagamento não autorizado. Tente outra forma de pagamento.');
        }

        if ($ultimoStatus == PagamentoCielo::STATUS_EXPIRADO) {
            return redirect('/checkout')
                ->with('error', 'O tempo para pagamento expirou. Tente novamente.');
        }

        if ($ultimoStatus == PagamentoCielo::STATUS_CANCELADO) {
            return redirect('/checkout')
                ->with('error', 'Pagamento cancelado.');
        }

        // Ainda pendente — exibe tela de polling
        return view('storefront.payment.aguardar-cielo', [
            'pedidoId'  => $pedidoId,
            'tentativa' => $tentativa,
            'pedido'    => $pedido,
        ]);
    }

    /**
     * AJAX: Verifica status do pagamento Cielo (para polling via JS).
     */
    public function statusCielo(int $pedidoId): JsonResponse
    {
        $isPaid = $this->paymentService->isPaid($pedidoId);

        $ultimoStatus = PagamentoCielo::where('pedido_id', $pedidoId)
            ->orderBy('created', 'desc')
            ->value('status_pagamento');

        $pedido = Pedido::find($pedidoId);
        $sessao = $pedido ? $pedido->sessao : null;

        return response()->json([
            'paid'          => $isPaid,
            'status'        => $ultimoStatus,
            'sessao'        => $sessao,
            'redirect_url'  => $isPaid
                ? route('checkout.confirmation', ['orderNumber' => $sessao])
                : null,
        ]);
    }

    // ==================== REDE e-Rede ====================

    /**
     * Exibe formulário de cartão para Rede (crédito ou débito).
     * Replica: legado RedeController::credito (GET)
     */
    public function redeCartao(int $pedidoId, int $lojaId, int $formaPagamentoId): View|RedirectResponse
    {
        $pedido = Pedido::find($pedidoId);

        if (!$pedido) {
            return redirect('/checkout')->with('error', 'Pedido não encontrado.');
        }

        // Determina tipo (crédito ou débito)
        $tipo = $formaPagamentoId == PaymentService::FORMA_REDE_DEBITO ? 'DEBITO' : 'CREDITO';

        return view('storefront.payment.rede-cartao', [
            'pedido'            => $pedido,
            'lojaId'            => $lojaId,
            'formaPagamentoId'  => $formaPagamentoId,
            'tipo'              => $tipo,
            'valorAPagar'       => $pedido->valor_total,
            'erroRede'          => request('erro_rede'),
        ]);
    }

    /**
     * Processa pagamento Rede (POST do form de cartão).
     * Replica: legado RedeController::credito (POST)
     */
    public function redeProcessar(Request $request, int $pedidoId, int $lojaId, int $formaPagamentoId): RedirectResponse
    {
        $pedido = Pedido::find($pedidoId);

        if (!$pedido) {
            return redirect('/checkout')->with('error', 'Pedido não encontrado.');
        }

        $cardData = [
            'card_number'           => preg_replace('/\D/', '', $request->input('card_number', '')),
            'holder_name'           => trim($request->input('holder_name', '')),
            'card_expiration_month' => $request->input('card_expiration_month', ''),
            'card_expiration_year'  => $request->input('card_expiration_year', ''),
            'card_cvv'              => $request->input('card_cvv', ''),
        ];

        $tipo = $formaPagamentoId == PaymentService::FORMA_REDE_DEBITO ? 'DEBITO' : 'CREDITO';

        if ($tipo === 'CREDITO') {
            $result = $this->redeService->processCredit($pedido, $lojaId, $cardData);
        } else {
            $result = $this->redeService->processDebit($pedido, $lojaId, $cardData);
        }

        if ($result['success']) {
            // Pagamento aprovado
            return redirect()
                ->route('checkout.confirmation', ['orderNumber' => $pedido->sessao])
                ->with('success', 'Pagamento aprovado!');
        }

        // Redirect para 3D Secure (débito)
        if (isset($result['redirect_3ds']) && $result['redirect_3ds']) {
            return redirect()->away($result['redirect_3ds']);
        }

        // Erro — volta para form de cartão com mensagem
        $errorMsg = $result['error'] ?? 'Erro ao processar pagamento';

        return redirect("/pagamento/rede/cartao/{$pedidoId}/{$lojaId}/{$formaPagamentoId}?erro_rede=" . urlencode($errorMsg));
    }

    /**
     * Callback do 3D Secure — consulta resultado da autenticação.
     * Replica: legado RedeController::consultar_tid
     */
    public function redeConsultarTid(int $pedidoId, int $lojaId): RedirectResponse
    {
        $result = $this->redeService->consultAfter3ds($pedidoId, $lojaId);

        $pedido = Pedido::find($pedidoId);

        if ($result['success'] && $pedido) {
            return redirect()
                ->route('checkout.confirmation', ['orderNumber' => $pedido->sessao])
                ->with('success', 'Pagamento aprovado!');
        }

        // Erro — volta para checkout
        $errorMsg = $result['error'] ?? 'Erro na autenticação do cartão';

        return redirect('/checkout')
            ->with('error', 'Pagamento não autorizado: ' . $errorMsg);
    }

    // ==================== CALLBACK LEGADO (mantido) ====================

    /**
     * Callback genérico do gateway (mantido para compatibilidade futura).
     * O webhook da Cielo continua no legado (/siv_v2/cielo/notificacao).
     */
    public function callback(Request $request): JsonResponse
    {
        Log::info('[PAYMENT-CALLBACK] Recebido', [
            'ip'      => $request->ip(),
            'payload' => $request->all(),
        ]);

        return response()->json(['received' => true]);
    }
}
```

- [ ] **Step 2: Adicionar rotas no web.php**

No `routes/web.php`, dentro do grupo `customer.guard`, substituir o bloco de pagamento:

```php
    // Rotas de pagamento (gateway, polling, callback)
    Route::prefix('pagamento')->name('payment.')->group(function () {
        // Callback genérico (POST - webhook)
        Route::post('/callback', [App\Http\Controllers\Storefront\PaymentController::class, 'callback'])->name('callback');

        // Cielo Checkout
        Route::get('/cielo/{pedidoId}/{lojaId}', [App\Http\Controllers\Storefront\PaymentController::class, 'redirectToCielo'])->name('cielo.redirect');
        Route::get('/aguardar-cielo/{pedidoId}/{tentativa?}', [App\Http\Controllers\Storefront\PaymentController::class, 'aguardarCielo'])->name('cielo.aguardar');
        Route::get('/status-cielo/{pedidoId}', [App\Http\Controllers\Storefront\PaymentController::class, 'statusCielo'])->name('cielo.status');

        // Rede e-Rede
        Route::get('/rede/cartao/{pedidoId}/{lojaId}/{formaPagamentoId}', [App\Http\Controllers\Storefront\PaymentController::class, 'redeCartao'])->name('rede.cartao');
        Route::post('/rede/cartao/{pedidoId}/{lojaId}/{formaPagamentoId}', [App\Http\Controllers\Storefront\PaymentController::class, 'redeProcessar'])->name('rede.processar');
        Route::get('/rede/consultar-tid/{pedidoId}/{lojaId}', [App\Http\Controllers\Storefront\PaymentController::class, 'redeConsultarTid'])->name('rede.consultar_tid');
    });
```

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/Storefront/PaymentController.php routes/web.php
git commit -m "feat: implementar PaymentController com Cielo Checkout e Rede e-Rede

Cielo: redirect para checkout, polling de status, AJAX para verificação.
Rede: form de cartão (crédito/débito), processamento via SDK, 3D Secure callback.
Offline: mantido callback genérico para compatibilidade."
```

---

### Task 7: CheckoutController — redirect para gateway após criar pedido

**Files:**
- Modify: `app/Http/Controllers/Storefront/CheckoutController.php`

- [ ] **Step 1: Alterar método store() para redirecionar ao gateway**

No método `store()`, após a criação do pedido com sucesso, substituir o redirect direto para confirmação por lógica de gateway:

Substituir o bloco `try { ... }` (linhas 171-197) por:

```php
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

            $formaPagamentoId = (int) $validated['formas_pagamento_id'];
            $gateway = PaymentService::getPaymentGateway($formaPagamentoId);

            // Determina loja para processamento do pagamento
            $lojaId = $this->paymentService->getPaymentLojaId(
                $pedido->loja_retirada_id,
                $shippingCalc['loja_id'] ?? null
            );

            // Redireciona de acordo com o gateway
            switch ($gateway) {
                case 'cielo':
                    // Redirect para Cielo Checkout (pagamento no domínio Cielo)
                    return redirect()->route('payment.cielo.redirect', [
                        'pedidoId' => $pedido->id,
                        'lojaId'   => $lojaId,
                    ]);

                case 'rede_credito':
                case 'rede_debito':
                    // Redirect para form de cartão Rede (pagamento no domínio sync)
                    return redirect()->route('payment.rede.cartao', [
                        'pedidoId'          => $pedido->id,
                        'lojaId'            => $lojaId,
                        'formaPagamentoId'  => $formaPagamentoId,
                    ]);

                default:
                    // Pagamento offline (dinheiro, PIX, cheque)
                    // Se for retirada na loja com dinheiro, finaliza direto
                    if ($formaPagamentoId == PaymentService::FORMA_DINHEIRO && $pedido->loja_retirada_id > 0) {
                        $pedido->update([
                            'finalizado'     => 1,
                            'data_finalizado' => now(),
                        ]);
                    }

                    return redirect()
                        ->route('checkout.confirmation', ['orderNumber' => $pedido->sessao])
                        ->with('success', 'Pedido realizado com sucesso!');
            }

        } catch (\Exception $e) {
            Log::error('[CHECKOUT] Erro ao criar pedido', [
                'error'     => $e->getMessage(),
                'pessoa_id' => $customer->id,
                'ip'        => $request->ip(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao processar seu pedido. Por favor, tente novamente.');
        }
```

- [ ] **Step 2: Commit**

```bash
git add app/Http/Controllers/Storefront/CheckoutController.php
git commit -m "feat: redirecionar para gateway após criação do pedido

Cielo → redirect para Cielo Checkout.
Rede → redirect para form de cartão.
Offline → confirmação direta (dinheiro retirada = finalizado=1)."
```

---

### Task 8: View — Aguardar pagamento Cielo (polling)

**Files:**
- Create: `resources/views/storefront/payment/aguardar-cielo.blade.php`

- [ ] **Step 1: Criar a view de polling**

```blade
@extends('layouts.storefront')

@section('title', 'Processando pagamento')

@section('content')
<section class="banner-interna small" style="background-image: url('{{ asset('storefront/img/ban-interna-1.jpg') }}');">
    <div class="pg-titulo">
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <h1 class="animated fadeIn">Processando Pagamento</h1>
                </div>
            </div>
        </div>
    </div>
</section>

<main class="pg-internas form-cadastro-new">
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
                <div class="box-aguardar-pagamento text-center" style="padding: 60px 20px;">
                    {{-- Ícone animado de loading --}}
                    <div style="margin-bottom: 30px;">
                        <i class="fa fa-spinner fa-spin fa-3x" style="color: var(--color-primary, #013E3B);"></i>
                    </div>

                    <h3 style="color: var(--color-primary, #013E3B); margin-bottom: 15px;">
                        Aguardando confirmação de pagamento
                    </h3>

                    <p style="color: #666; font-size: 16px; margin-bottom: 10px;">
                        Estamos verificando seu pagamento junto à operadora.
                    </p>
                    <p style="color: #999; font-size: 14px;">
                        Esta página atualiza automaticamente. Não feche o navegador.
                    </p>

                    {{-- Pedido info --}}
                    <div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                        <p style="margin: 0; font-size: 14px; color: #666;">
                            Pedido <strong>#{{ $pedidoId }}</strong>
                            @if($pedido && $pedido->valor_total)
                                &mdash; {{ 'R$ ' . number_format($pedido->valor_total, 2, ',', '.') }}
                            @endif
                        </p>
                    </div>

                    {{-- Status de erro (exibido via JS se ocorrer) --}}
                    <div id="status-erro" style="display: none; margin-top: 20px;" class="alert alert-danger">
                        <i class="fa fa-exclamation-circle"></i>
                        <span id="status-erro-msg"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
    // Polling de status do pagamento — replica o comportamento do legado
    // (aguardar_confirmacao_pagamento.ctp com setTimeout de 15s)
    var pedidoId = {{ $pedidoId }};
    var tentativa = {{ $tentativa }};
    var maxTentativas = 40; // 40 × 15s = 10 minutos
    var pollingInterval = 15000; // 15 segundos

    function verificarPagamento() {
        tentativa++;

        if (tentativa > maxTentativas) {
            // Timeout — redireciona para erro
            $('#status-erro-msg').text('O tempo para confirmação do pagamento expirou. Tente novamente.');
            $('#status-erro').show();
            return;
        }

        $.ajax({
            url: '{{ url("/pagamento/status-cielo") }}/' + pedidoId,
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.paid && data.redirect_url) {
                    // Pagamento confirmado — redireciona para confirmação
                    window.location.href = data.redirect_url;
                    return;
                }

                // Verifica status de erro
                if (data.status == 3) {
                    // Negado
                    window.location.href = '/checkout?error=nao_autorizado';
                    return;
                }
                if (data.status == 4) {
                    // Expirado
                    window.location.href = '/checkout?error=expirado';
                    return;
                }
                if (data.status == 5) {
                    // Cancelado
                    window.location.href = '/checkout?error=cancelado';
                    return;
                }

                // Ainda pendente — continua polling
                setTimeout(verificarPagamento, pollingInterval);
            },
            error: function() {
                // Erro de rede — tenta novamente
                setTimeout(verificarPagamento, pollingInterval);
            }
        });
    }

    // Inicia polling após 15 segundos (dá tempo do webhook chegar)
    setTimeout(verificarPagamento, pollingInterval);
</script>
@endpush
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/storefront/payment/aguardar-cielo.blade.php
git commit -m "feat: criar view de polling de pagamento Cielo

Auto-refresh a cada 15s via AJAX. Redireciona para confirmação quando pago.
Timeout de 10 minutos. Trata status de erro (negado, expirado, cancelado)."
```

---

### Task 9: View — Formulário de cartão Rede

**Files:**
- Create: `resources/views/storefront/payment/rede-cartao.blade.php`

- [ ] **Step 1: Criar a view do form de cartão**

```blade
@extends('layouts.storefront')

@section('title', 'Pagamento com Cartão')

@section('content')
<section class="banner-interna small" style="background-image: url('{{ asset('storefront/img/ban-interna-1.jpg') }}');">
    <div class="pg-titulo">
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <h1 class="animated fadeIn">Pagamento com Cartão</h1>
                </div>
            </div>
        </div>
    </div>
</section>

<main class="pg-internas form-cadastro-new">
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">

                {{-- Mensagem de erro --}}
                @if($erroRede)
                    <div class="alert alert-danger" style="margin-top: 20px;">
                        <i class="fa fa-exclamation-circle"></i>
                        Erro no pagamento: {{ $erroRede }}
                    </div>
                @endif

                {{-- Box de valor --}}
                <div style="background: #f8f9fa; border-radius: 8px; padding: 20px; margin: 20px 0; text-align: center;">
                    <p style="margin: 0 0 5px; color: #666; font-size: 14px;">
                        Valor a pagar — Pedido #{{ $pedido->id }}
                    </p>
                    <p style="margin: 0; font-size: 28px; font-weight: 700; color: var(--color-primary, #013E3B);">
                        R$ {{ number_format($valorAPagar, 2, ',', '.') }}
                    </p>
                    <p style="margin: 5px 0 0; font-size: 12px; color: #999;">
                        {{ $tipo === 'DEBITO' ? 'Cartão de Débito' : 'Cartão de Crédito' }}
                    </p>
                </div>

                {{-- Formulário de cartão --}}
                <form method="POST"
                      action="{{ route('payment.rede.processar', ['pedidoId' => $pedido->id, 'lojaId' => $lojaId, 'formaPagamentoId' => $formaPagamentoId]) }}"
                      id="form-cartao"
                      autocomplete="off">
                    @csrf

                    {{-- Número do Cartão --}}
                    <div class="form-group">
                        <label for="card_number">Número do Cartão <span style="color:#e74c3c">*</span></label>
                        <input type="tel"
                               name="card_number"
                               id="card_number"
                               class="form-control"
                               placeholder="0000 0000 0000 0000"
                               maxlength="19"
                               required
                               autocomplete="cc-number"
                               style="border-radius: 30px; height: 45px; font-size: 18px; letter-spacing: 2px;">
                        <div id="card-brand" style="margin-top: 5px; font-size: 12px; color: #999;"></div>
                    </div>

                    {{-- Nome do Titular --}}
                    <div class="form-group">
                        <label for="holder_name">Nome do Titular <span style="color:#e74c3c">*</span></label>
                        <input type="text"
                               name="holder_name"
                               id="holder_name"
                               class="form-control"
                               placeholder="Como impresso no cartão"
                               maxlength="50"
                               required
                               autocomplete="cc-name"
                               style="border-radius: 30px; height: 45px; text-transform: uppercase;">
                    </div>

                    <div class="row">
                        {{-- Validade --}}
                        <div class="col-xs-4">
                            <div class="form-group">
                                <label for="card_expiration_month">Mês <span style="color:#e74c3c">*</span></label>
                                <select name="card_expiration_month" id="card_expiration_month" class="form-control" required style="border-radius: 30px; height: 45px;">
                                    <option value="">Mês</option>
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-xs-4">
                            <div class="form-group">
                                <label for="card_expiration_year">Ano <span style="color:#e74c3c">*</span></label>
                                <select name="card_expiration_year" id="card_expiration_year" class="form-control" required style="border-radius: 30px; height: 45px;">
                                    <option value="">Ano</option>
                                    @for($y = date('Y'); $y <= date('Y') + 15; $y++)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        {{-- CVV --}}
                        <div class="col-xs-4">
                            <div class="form-group">
                                <label for="card_cvv">CVV <span style="color:#e74c3c">*</span></label>
                                <input type="tel"
                                       name="card_cvv"
                                       id="card_cvv"
                                       class="form-control"
                                       placeholder="000"
                                       maxlength="4"
                                       required
                                       autocomplete="cc-csc"
                                       style="border-radius: 30px; height: 45px; text-align: center; font-size: 18px;">
                            </div>
                        </div>
                    </div>

                    {{-- Segurança --}}
                    <div style="text-align: center; padding: 10px 0; color: #999; font-size: 12px;">
                        <i class="fa fa-lock"></i> Pagamento processado com segurança via Rede e-Rede
                    </div>

                    {{-- Botões --}}
                    <div class="box-btn-checkout" style="display: flex; justify-content: space-between; padding: 20px 0 40px;">
                        <a href="/checkout" class="btn-voltar" style="color: var(--color-primary, #013E3B); font-size: 14px; padding: 14px 0;">
                            <i class="fa fa-arrow-left"></i> Voltar
                        </a>
                        <button type="submit" id="btn-pagar" class="btn btn-primary" style="padding: 14px 40px; font-size: 15px; font-weight: 600; text-transform: uppercase;">
                            Pagar R$ {{ number_format($valorAPagar, 2, ',', '.') }}
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
    // Previne duplo envio
    $('#form-cartao').on('submit', function() {
        var $btn = $('#btn-pagar');
        $btn.prop('disabled', true).text('Processando...');
    });

    // Formata número do cartão com espaços (visual)
    $('#card_number').on('input', function() {
        var val = $(this).val().replace(/\D/g, '');
        var formatted = val.replace(/(\d{4})(?=\d)/g, '$1 ');
        $(this).val(formatted);

        // Detecta bandeira
        detectBrand(val);
    });

    // Detecção de bandeira para feedback visual
    function detectBrand(number) {
        var brand = '';
        if (/^4/.test(number)) brand = 'Visa';
        else if (/^5/.test(number)) brand = 'Mastercard';
        else if (/^(636368|438935|504175|451416|636297|5067|4576|4011|506699)/.test(number)) brand = 'Elo';
        else if (/^(34|37)/.test(number)) brand = 'Amex';
        else if (/^(606282|3841)/.test(number)) brand = 'Hipercard';
        else if (/^(6011|622|64|65)/.test(number)) brand = 'Discover';
        else if (/^(301|305|36|38)/.test(number)) brand = 'Diners';
        else if (/^35/.test(number)) brand = 'JCB';

        $('#card-brand').text(brand ? 'Bandeira: ' + brand : '');
    }

    // Força uppercase no nome do titular
    $('#holder_name').on('input', function() {
        $(this).val($(this).val().toUpperCase());
    });
</script>
@endpush
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/storefront/payment/rede-cartao.blade.php
git commit -m "feat: criar view de formulário de cartão para Rede e-Rede

Form de crédito/débito com validação visual.
Detecção de bandeira pelo BIN, formatação do número, prevenção de duplo envio.
Design seguindo padrão do storefront (Bootstrap 3 + CSS vars do tema)."
```

---

### Task 10: Testes e verificação

**Files:**
- Test manual via browser e tinker

- [ ] **Step 1: Verificar que a estrutura compila sem erros**

```bash
cd /Users/eduardomacedo/source/deepfreeze/sync.deepfreeze.com.br
php artisan route:list --path=pagamento
```

Expected: Lista as 7 novas rotas de pagamento (callback, cielo.redirect, cielo.aguardar, cielo.status, rede.cartao, rede.processar, rede.consultar_tid).

- [ ] **Step 2: Verificar Models carregam do banco legado**

```bash
php artisan tinker --execute="
use App\Models\Legacy\Loja;
\$l = Loja::find(2);
echo 'PV: ' . \$l->pv . ' | MerchantId: ' . \$l->merchant_id;
"
```

Expected: Mostra PV e MerchantId da loja 2 (credenciais reais do legado).

- [ ] **Step 3: Verificar PagamentoRede lê do banco legado**

```bash
php artisan tinker --execute="
use App\Models\Legacy\PagamentoRede;
echo 'Count: ' . PagamentoRede::count();
echo ' | Último: ' . PagamentoRede::orderBy('id', 'desc')->first()?->reference;
"
```

Expected: Mostra contagem de registros e referência do último pagamento.

- [ ] **Step 4: Verificar PaymentService::getPaymentGateway**

```bash
php artisan tinker --execute="
use App\Services\PaymentService;
echo '46 => ' . PaymentService::getPaymentGateway(46);
echo ' | 62 => ' . PaymentService::getPaymentGateway(62);
echo ' | 63 => ' . PaymentService::getPaymentGateway(63);
echo ' | 1 => ' . PaymentService::getPaymentGateway(1);
echo ' | 75 => ' . PaymentService::getPaymentGateway(75);
"
```

Expected: `46 => cielo | 62 => rede_credito | 63 => rede_debito | 1 => offline | 75 => offline`

- [ ] **Step 5: Verificar SDK Rede carrega**

```bash
php artisan tinker --execute="
echo class_exists('\Rede\eRede') ? 'SDK OK' : 'SDK FALHOU';
echo ' | ' . (class_exists('\Rede\Transaction') ? 'Transaction OK' : 'Transaction FALHOU');
echo ' | ' . (class_exists('\Rede\ThreeDSecure') ? '3DS OK' : '3DS FALHOU');
"
```

Expected: `SDK OK | Transaction OK | 3DS OK`

- [ ] **Step 6: Commit final**

```bash
git add -A
git commit -m "feat: integração completa de pagamento Cielo Checkout + Rede e-Rede

Implementação dos gateways de pagamento replicando comportamento do legado:
- Cielo Checkout: redirect para API, polling de status, webhook no legado
- Rede e-Rede: form de cartão direto, SDK PHP, 3D Secure para débito
- Pagamentos offline: dinheiro retirada = finalizado direto
- Credenciais por loja (merchant_id, pv, token)
- Model PagamentoRede para tabela do banco legado
- Views: polling Cielo + form de cartão Rede"
```

---

## Resumo do que muda vs. o que permanece

| Aspecto | Legado | Sync (novo) |
|---------|--------|-------------|
| **Webhook Cielo** | `/siv_v2/cielo/notificacao` (MANTIDO) | Não recebe — apenas faz polling |
| **ReturnUrl Cielo** | `deepfreeze.com.br/cielo/aguardar_...` | `sync.deepfreeze.com.br/pagamento/aguardar-cielo/{id}` |
| **Form cartão Rede** | `credito.ctp` (CakePHP) | `rede-cartao.blade.php` (Laravel/Blade) |
| **SDK Rede** | `Vendor/Rede/` | `Libraries/Rede/` (cópia idêntica) |
| **Credenciais** | `lojas.pv`, `lojas.token`, `lojas.merchant_id` | Mesmas colunas, mesmo banco |
| **Tabela pagamentos** | `pagamentos_cielo` + `pagamentos_rede` | Mesmas tabelas, mesmo banco |
| **Pedido offline** | `finalizado=1` direto | Igual |
| **Pedido online** | `finalizado=0` → webhook → `finalizado=1` | Igual |

## Pontos de atenção para deploy

1. **ReturnUrl na Cielo:** Quando o sync estiver em produção, o `ReturnUrl` vai apontar para `sync.deepfreeze.com.br`. O webhook da Cielo (`notificacao`) continua no legado.
2. **SSL obrigatório:** As URLs de ReturnUrl e 3D Secure devem ser HTTPS.
3. **Rota `aguardar-cielo` sem auth:** Precisa estar acessível sem login (cliente volta do Cielo sem session).
4. **CSRF:** O form Rede usa `@csrf`. O polling Cielo usa GET (sem CSRF).
