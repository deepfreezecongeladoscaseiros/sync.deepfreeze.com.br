<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Pagamento Rede (tabela 'pagamentos_rede' do banco legado)
 *
 * Registra transações de cartão processadas pela Rede e-Rede.
 * O SIV lê esta tabela para conciliação financeira.
 *
 * Tabela: novo.pagamentos_rede
 * Engine: MyISAM
 * Charset: utf8mb3
 *
 * Códigos de retorno relevantes:
 *   '00'  = Transação aprovada
 *   '220' = Pendente de autenticação 3DS (3D Secure)
 */
class PagamentoRede extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'pagamentos_rede';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    // Códigos de retorno da Rede (campo return_code_authorization)
    const CODE_APPROVED = '00';       // Transação autorizada com sucesso
    const CODE_3DS_PENDING = '220';   // Pendente de autenticação 3D Secure

    protected $fillable = [
        'pedido_id',                    // ID do pedido no sistema legado (FK pedidos.id)
        'amount',                       // Valor da transação em reais (decimal 10,2)
        'transaction_id',               // ID único da transação na Rede
        'reference',                    // Referência do pedido enviada à Rede (geralmente o pedido_id)
        'authorization_code',           // Código de autorização retornado pela Rede
        'nsu',                          // Número Sequencial Único (comprovante)
        'bin',                          // Primeiros 6 dígitos do cartão (BIN - Bank Identification Number)
        'last4',                        // Últimos 4 dígitos do cartão
        'return_code_authorization',    // Código de retorno da autorização ('00' = aprovado, '220' = 3DS pendente)
        'return_message_authorization', // Mensagem descritiva do retorno da autorização
        'kind',                         // Tipo da transação (credit, debit)
        'bandeira_rede',                // Bandeira do cartão retornada pela Rede (Visa, Master, etc.)
        'authorization_datetime',       // Data/hora da autorização
        'capture_datetime',             // Data/hora da captura
        'transaction_json',             // JSON completo da transação (dados sensíveis mascarados)
        'authorization_json',           // JSON da resposta de autorização
        'capture_json',                 // JSON da resposta de captura
        'return_authorization_status',  // Status retornado na autorização
    ];

    protected $casts = [
        'amount' => 'decimal:2',   // Valor em reais com 2 casas decimais
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Pedido associado a este pagamento
     */
    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    // ==================== HELPERS ====================

    /**
     * Verifica se a transação foi aprovada pela Rede
     * Código '00' indica autorização bem-sucedida
     */
    public function isApproved(): bool
    {
        return $this->return_code_authorization === self::CODE_APPROVED;
    }

    /**
     * Verifica se a transação está pendente de autenticação 3D Secure
     * Código '220' indica que o portador precisa autenticar no banco emissor
     */
    public function isPending3ds(): bool
    {
        return $this->return_code_authorization === self::CODE_3DS_PENDING;
    }

    // ==================== STATIC METHODS ====================

    /**
     * Cria registro de pagamento a partir de um objeto Transaction do SDK da Rede
     *
     * IMPORTANTE: Mascara dados sensíveis (cardNumber e securityCode) no JSON
     * antes de persistir, por compliance PCI-DSS.
     *
     * @param int $pedidoId ID do pedido no banco legado
     * @param mixed $transaction Objeto Transaction retornado pelo SDK da Rede
     * @return static
     */
    public static function createFromTransaction(int $pedidoId, $transaction): static
    {
        // Converte o objeto da transação para array para manipulação segura
        $transactionData = json_decode(json_encode($transaction), true);

        // Mascara dados sensíveis do cartão por compliance PCI-DSS
        // cardNumber e securityCode NUNCA devem ser armazenados em texto plano
        $maskedData = self::maskSensitiveData($transactionData);

        return static::create([
            'pedido_id'                    => $pedidoId,
            'amount'                       => ($transaction->getAmount() ?? 0) / 100, // SDK retorna em centavos
            'transaction_id'               => $transaction->getTid(),
            'reference'                    => $transaction->getReference(),
            'authorization_code'           => $transaction->getAuthorizationCode(),
            'nsu'                          => $transaction->getNsu(),
            'bin'                          => $transaction->getCardBin(),
            'last4'                        => $transaction->getLast4(),
            'return_code_authorization'    => $transaction->getReturnCode(),
            'return_message_authorization' => $transaction->getReturnMessage(),
            'kind'                         => $transaction->getKind(),
            'bandeira_rede'                => $transaction->getBrand(),
            'authorization_datetime'       => $transaction->getDateTime(),
            'return_authorization_status'  => $transaction->getStatus(),
            'transaction_json'             => json_encode($maskedData),
        ]);
    }

    /**
     * Atualiza os JSONs de autorização e captura após processamento
     *
     * Usado quando a autorização e captura são feitas em etapas separadas
     * (ex: pré-autorização seguida de captura posterior)
     *
     * @param array $authorization Dados da resposta de autorização
     * @param array $capture Dados da resposta de captura
     */
    public function saveAuthorizationAndCapture(array $authorization, array $capture): void
    {
        $this->update([
            'authorization_json'    => json_encode(self::maskSensitiveData($authorization)),
            'authorization_datetime' => $authorization['dateTime'] ?? $this->authorization_datetime,
            'capture_json'          => json_encode(self::maskSensitiveData($capture)),
            'capture_datetime'      => $capture['dateTime'] ?? null,
        ]);
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Pagamentos aprovados de um pedido
     * Filtra pela combinação pedido_id + return_code '00'
     */
    public function scopeApprovedForOrder($query, int $pedidoId)
    {
        return $query->where('pedido_id', $pedidoId)
            ->where('return_code_authorization', self::CODE_APPROVED);
    }

    /**
     * Scope: Pagamentos pendentes de 3DS de um pedido
     * Filtra pela combinação pedido_id + return_code '220'
     */
    public function scopePending3dsForOrder($query, int $pedidoId)
    {
        return $query->where('pedido_id', $pedidoId)
            ->where('return_code_authorization', self::CODE_3DS_PENDING);
    }

    // ==================== PRIVATE METHODS ====================

    /**
     * Mascara dados sensíveis do cartão no array de dados da transação
     *
     * Substitui cardNumber e securityCode por asteriscos para compliance PCI-DSS.
     * Percorre recursivamente o array para garantir que dados sensíveis
     * não vazem em nenhum nível da estrutura JSON.
     *
     * @param array $data Array com dados da transação
     * @return array Array com dados sensíveis mascarados
     */
    private static function maskSensitiveData(array $data): array
    {
        // Campos que devem ser mascarados por conterem dados sensíveis do cartão
        $sensitiveFields = ['cardNumber', 'securityCode', 'card_number', 'security_code'];

        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                // Percorre recursivamente sub-arrays (ex: dados do cartão dentro de 'payment')
                $value = self::maskSensitiveData($value);
            } elseif (in_array($key, $sensitiveFields, true) && is_string($value)) {
                // Substitui o valor inteiro por asteriscos de mesmo tamanho
                $value = str_repeat('*', strlen($value));
            }
        }

        return $data;
    }
}
