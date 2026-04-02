<?php

namespace App\Services;

use App\Models\Legacy\FormaPagamento;
use App\Models\Legacy\Loja;
use App\Models\Legacy\PagamentoRede;
use App\Models\Legacy\Pedido;
use Illuminate\Support\Facades\Log;
use Rede\Environment;
use Rede\eRede;
use Rede\Store;
use Rede\ThreeDSecure;
use Rede\Transaction;
use Rede\Url;

/**
 * Service: Processamento de pagamentos via Rede e-Rede
 *
 * Réplica da lógica do legado (RedeController::credito / RedeController::debito)
 * adaptada para o padrão Laravel com retorno estruturado.
 *
 * Fluxo geral:
 *   1. Busca credenciais da loja (PV + Token)
 *   2. Valida dados do cartão (Luhn + campos obrigatórios)
 *   3. Monta transação via SDK Rede
 *   4. Executa e salva resultado em pagamentos_rede
 *   5. Se aprovado: finaliza pedido e atualiza forma de pagamento
 *   6. Se 3DS: retorna URL de redirect para autenticação do banco
 *   7. Se negado: retorna mensagem de erro
 */
class RedePaymentService
{
    // ==================== PADRÕES DE BANDEIRA ====================

    /**
     * Padrões regex para detecção de bandeira pelo BIN do cartão.
     * Ordem importa: Elo antes de Visa/Master por ter ranges conflitantes.
     * Réplica de PagamentoRede::obterBandeira do legado.
     */
    private const BRAND_PATTERNS = [
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

    // ==================== MÉTODOS PÚBLICOS ====================

    /**
     * Processa pagamento com cartão de CRÉDITO via Rede
     *
     * Fluxo: autorização + captura imediata (capture=true).
     * Não exige 3D Secure.
     *
     * @param Pedido $pedido   Pedido do banco legado (deve ter valor_total)
     * @param int    $lojaId   ID da loja para buscar credenciais PV/Token
     * @param array  $cardData Dados do cartão:
     *   - card_number: string (sem espaços/pontos)
     *   - holder_name: string
     *   - card_expiration_month: string (MM)
     *   - card_expiration_year: string (YYYY)
     *   - card_cvv: string
     *
     * @return array ['success' => bool, 'error' => string|null, 'pagamento' => PagamentoRede|null]
     */
    public function processCredit(Pedido $pedido, int $lojaId, array $cardData): array
    {
        return $this->processCard($pedido, $lojaId, $cardData, 'Credit');
    }

    /**
     * Processa pagamento com cartão de DÉBITO via Rede
     *
     * Fluxo: autorização + captura imediata com 3D Secure obrigatório.
     * O banco emissor pode redirecionar o portador para autenticação (código 220).
     *
     * @param Pedido $pedido   Pedido do banco legado
     * @param int    $lojaId   ID da loja
     * @param array  $cardData Dados do cartão (mesma estrutura do crédito)
     *
     * @return array ['success' => bool, 'error' => string|null, 'redirect_3ds' => string|null, 'pagamento' => PagamentoRede|null]
     */
    public function processDebit(Pedido $pedido, int $lojaId, array $cardData): array
    {
        return $this->processCard($pedido, $lojaId, $cardData, 'Debit');
    }

    /**
     * Consulta resultado da transação após retorno do 3D Secure
     *
     * Chamado quando o banco emissor redireciona de volta após autenticação.
     * Busca o pagamento pendente (código 220) e consulta o status na Rede
     * pela referência da transação.
     *
     * Réplica de RedeController::consultar_tid do legado.
     *
     * @param int $pedidoId ID do pedido no banco legado
     * @param int $lojaId   ID da loja para credenciais
     *
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function consultAfter3ds(int $pedidoId, int $lojaId): array
    {
        try {
            // Busca o pagamento mais recente pendente de 3DS para este pedido
            $pagamento = PagamentoRede::pending3dsForOrder($pedidoId)
                ->orderBy('id', 'desc')
                ->first();

            if (!$pagamento) {
                Log::warning("[REDE] consultAfter3ds: nenhum pagamento pendente 3DS encontrado", [
                    'pedido_id' => $pedidoId,
                    'loja_id'   => $lojaId,
                ]);
                return [
                    'success' => false,
                    'error'   => 'Nenhum pagamento pendente de autenticação encontrado.',
                ];
            }

            // Busca credenciais da loja
            $loja = Loja::find($lojaId);
            if (!$loja || empty($loja->pv) || empty($loja->token)) {
                Log::error("[REDE] consultAfter3ds: credenciais inválidas para loja", [
                    'loja_id' => $lojaId,
                ]);
                return [
                    'success' => false,
                    'error'   => 'Credenciais da loja não configuradas.',
                ];
            }

            // Consulta transação na Rede pela referência
            $store = new Store($loja->pv, $loja->token, Environment::production());
            $transaction = (new eRede($store))->getByReference($pagamento->reference);

            Log::info("[REDE] consultAfter3ds: resultado da consulta", [
                'pedido_id'   => $pedidoId,
                'reference'   => $pagamento->reference,
                'return_code' => $transaction->getReturnCode(),
                'return_msg'  => $transaction->getReturnMessage(),
            ]);

            // Verifica se a autorização foi aprovada após 3DS
            if ($transaction->getReturnCode() === PagamentoRede::CODE_APPROVED) {
                // Salva dados de autorização e captura no registro do pagamento
                $this->saveAuthorizationAndCapture($pagamento, $transaction);

                // Finaliza o pedido (marca como pago)
                $this->finalizePedido($pedidoId);

                // Atualiza forma de pagamento baseado na bandeira
                $this->updateFormaPagamento(
                    $pedidoId,
                    $pagamento->bandeira_rede ?? $this->detectBrand($pagamento->bin ?? ''),
                    'Debit' // 3DS é usado apenas em débito
                );

                Log::info("[REDE] consultAfter3ds: pagamento aprovado após 3DS", [
                    'pedido_id'      => $pedidoId,
                    'pagamento_id'   => $pagamento->id,
                    'authorization'  => $transaction->getAuthorizationCode(),
                ]);

                return [
                    'success' => true,
                    'error'   => null,
                ];
            }

            // Autenticação 3DS falhou ou transação negada
            Log::warning("[REDE] consultAfter3ds: transação não aprovada após 3DS", [
                'pedido_id'   => $pedidoId,
                'return_code' => $transaction->getReturnCode(),
                'return_msg'  => $transaction->getReturnMessage(),
            ]);

            return [
                'success' => false,
                'error'   => $transaction->getReturnMessage() ?: 'Transação não autorizada após autenticação.',
            ];

        } catch (\Exception $e) {
            Log::error("[REDE] consultAfter3ds: exceção ao consultar transação", [
                'pedido_id' => $pedidoId,
                'loja_id'   => $lojaId,
                'exception' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error'   => 'Erro ao consultar status do pagamento. Tente novamente.',
            ];
        }
    }

    // ==================== MÉTODO PRINCIPAL (PRIVATE) ====================

    /**
     * Processa transação de cartão (crédito ou débito)
     *
     * Método centralizado que replica a lógica de RedeController::credito do legado.
     * Diferença entre crédito e débito:
     *   - Crédito: creditCard() sem 3DS
     *   - Débito: debitCard() + threeDSecure(DECLINE_ON_FAILURE) + URLs de callback
     *
     * @param Pedido $pedido   Pedido do banco legado
     * @param int    $lojaId   ID da loja
     * @param array  $cardData Dados do cartão
     * @param string $kind     'Credit' ou 'Debit'
     *
     * @return array Resultado estruturado com success, error, pagamento e redirect_3ds
     */
    private function processCard(Pedido $pedido, int $lojaId, array $cardData, string $kind): array
    {
        // === 1. Busca credenciais da loja ===
        $loja = Loja::find($lojaId);
        if (!$loja || empty($loja->pv) || empty($loja->token)) {
            Log::error("[REDE] processCard: credenciais inválidas", [
                'loja_id' => $lojaId,
                'kind'    => $kind,
            ]);
            return $this->errorResult('Credenciais da loja não configuradas.', $kind);
        }

        // === 2. Valida dados do cartão ===
        $validationError = $this->validateCardData($cardData);
        if ($validationError) {
            Log::warning("[REDE] processCard: validação falhou", [
                'pedido_id' => $pedido->id,
                'error'     => $validationError,
            ]);
            return $this->errorResult($validationError, $kind);
        }

        // === 3. Detecta bandeira pelo número do cartão ===
        $cardNumber = preg_replace('/\D/', '', $cardData['card_number']);
        $bandeira = $this->detectBrand($cardNumber);

        // === 4. Monta objetos do SDK ===
        $store = new Store($loja->pv, $loja->token, Environment::production());

        // Referência única: ID do pedido + timestamp (evita duplicidade na Rede)
        $reference = $pedido->id . time();

        $month = (int) $cardData['card_expiration_month'];
        $year  = (int) $cardData['card_expiration_year'];
        $cvv   = $cardData['card_cvv'];
        $holderName = $cardData['holder_name'];

        try {
            // === 5. Cria transação conforme tipo (crédito ou débito) ===
            $transaction = new Transaction($pedido->valor_total, $reference);

            if ($kind === 'Credit') {
                // Crédito: autorização + captura imediata, sem 3DS
                $transaction->creditCard($cardNumber, $cvv, $month, $year, $holderName)
                    ->capture(true);
            } else {
                // Débito: autorização + captura imediata COM 3D Secure obrigatório
                // DECLINE_ON_FAILURE = recusa se autenticação 3DS falhar
                $transaction->debitCard($cardNumber, $cvv, $month, $year, $holderName)
                    ->capture(true);

                $transaction->threeDSecure(ThreeDSecure::DECLINE_ON_FAILURE);

                // URLs de callback para retorno do 3D Secure
                $successUrl = url("/pagamento/rede/consultar-tid/{$pedido->id}/{$lojaId}");
                $failureUrl = url("/checkout?erro_rede=FALHA");

                $transaction->addUrl($successUrl, Url::THREE_D_SECURE_SUCCESS);
                $transaction->addUrl($failureUrl, Url::THREE_D_SECURE_FAILURE);
            }

            // === 6. Executa transação na Rede ===
            Log::info("[REDE] processCard: enviando transação", [
                'pedido_id' => $pedido->id,
                'kind'      => $kind,
                'reference' => $reference,
                'valor'     => $pedido->valor_total,
                'bandeira'  => $bandeira,
            ]);

            $transaction = (new eRede($store))->create($transaction);

            // === 7. Salva resultado em pagamentos_rede ===
            $pagamento = PagamentoRede::createFromTransaction($pedido->id, $transaction);

            // Atualiza kind e bandeira detectada (o SDK pode não retornar a bandeira)
            $pagamento->update([
                'kind'           => $kind,
                'bandeira_rede'  => $bandeira,
            ]);

            Log::info("[REDE] processCard: transação processada", [
                'pedido_id'      => $pedido->id,
                'pagamento_id'   => $pagamento->id,
                'return_code'    => $transaction->getReturnCode(),
                'return_message' => $transaction->getReturnMessage(),
                'tid'            => $transaction->getTid(),
            ]);

            // === 8. Trata resultado conforme código de retorno ===
            $returnCode = $transaction->getReturnCode();

            // Código '00' = Transação aprovada
            if ($returnCode === PagamentoRede::CODE_APPROVED) {
                // Busca transação completa para obter dados de autorização/captura
                $fullTransaction = (new eRede($store))->getById($transaction->getTid());

                // Salva JSONs de autorização e captura
                $this->saveAuthorizationAndCapture($pagamento, $fullTransaction);

                // Finaliza o pedido no banco legado
                $this->finalizePedido($pedido->id);

                // Atualiza a forma de pagamento pela bandeira detectada
                $this->updateFormaPagamento($pedido->id, $bandeira, $kind);

                Log::info("[REDE] processCard: pagamento APROVADO", [
                    'pedido_id'     => $pedido->id,
                    'pagamento_id'  => $pagamento->id,
                    'authorization' => $transaction->getAuthorizationCode(),
                    'nsu'           => $transaction->getNsu(),
                ]);

                return $this->successResult($pagamento, $kind);
            }

            // Código '220' = Pendente de autenticação 3D Secure (apenas débito)
            if ($returnCode === PagamentoRede::CODE_3DS_PENDING) {
                // Extrai URL de redirect do 3DS da resposta da transação
                $threeDSecure = $transaction->getThreeDSecure();
                $redirectUrl = $threeDSecure ? $threeDSecure->getUrl() : null;

                Log::info("[REDE] processCard: redirecionando para 3D Secure", [
                    'pedido_id'    => $pedido->id,
                    'pagamento_id' => $pagamento->id,
                    'redirect_url' => $redirectUrl,
                ]);

                return [
                    'success'      => false,
                    'error'        => null,
                    'redirect_3ds' => $redirectUrl,
                    'pagamento'    => $pagamento,
                ];
            }

            // Qualquer outro código = Transação negada
            $errorMessage = $transaction->getReturnMessage() ?: 'Transação não autorizada.';

            Log::warning("[REDE] processCard: transação NEGADA", [
                'pedido_id'   => $pedido->id,
                'return_code' => $returnCode,
                'return_msg'  => $errorMessage,
            ]);

            return $this->errorResult($errorMessage, $kind, $pagamento);

        } catch (\Exception $e) {
            Log::error("[REDE] processCard: exceção ao processar transação", [
                'pedido_id' => $pedido->id,
                'kind'      => $kind,
                'exception' => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);

            return $this->errorResult(
                'Erro ao processar pagamento. Tente novamente.',
                $kind
            );
        }
    }

    // ==================== VALIDAÇÃO ====================

    /**
     * Valida dados do cartão antes de enviar à Rede
     *
     * Réplica de PagamentoRede::validar do legado.
     * Verifica campos obrigatórios e algoritmo de Luhn para o número do cartão.
     *
     * @param array $cardData Dados do cartão
     * @return string|null Mensagem de erro ou null se válido
     */
    private function validateCardData(array $cardData): ?string
    {
        // Campos obrigatórios
        if (empty($cardData['card_number'])) {
            return 'Número do cartão é obrigatório.';
        }

        if (empty($cardData['holder_name'])) {
            return 'Nome do titular é obrigatório.';
        }

        if (empty($cardData['card_expiration_month'])) {
            return 'Mês de validade é obrigatório.';
        }

        if (empty($cardData['card_expiration_year'])) {
            return 'Ano de validade é obrigatório.';
        }

        if (empty($cardData['card_cvv'])) {
            return 'CVV é obrigatório.';
        }

        // Validação de Luhn (verifica se o número do cartão é matematicamente válido)
        $cardNumber = preg_replace('/\D/', '', $cardData['card_number']);
        if (!$this->luhnCheck($cardNumber)) {
            return 'Número do cartão inválido.';
        }

        return null;
    }

    /**
     * Algoritmo de Luhn (Mod 10) para validação de número de cartão
     *
     * Verifica se o número do cartão é matematicamente válido
     * conforme padrão ISO/IEC 7812-1. Não garante que o cartão exista,
     * apenas que o número segue a fórmula de checksum.
     *
     * @param string $number Número do cartão (apenas dígitos)
     * @return bool true se o número passa na validação de Luhn
     */
    private function luhnCheck(string $number): bool
    {
        if (strlen($number) < 13 || strlen($number) > 19) {
            return false;
        }

        $sum = 0;
        $alternate = false;

        // Percorre os dígitos da direita para a esquerda
        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $digit = (int) $number[$i];

            // Duplica a cada segundo dígito (da direita para a esquerda)
            if ($alternate) {
                $digit *= 2;
                // Se o resultado for > 9, subtrai 9 (equivale a somar os dígitos)
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
            $alternate = !$alternate;
        }

        // Número válido se a soma é divisível por 10
        return ($sum % 10) === 0;
    }

    // ==================== DETECÇÃO DE BANDEIRA ====================

    /**
     * Detecta a bandeira do cartão pelo BIN (primeiros dígitos)
     *
     * Réplica de PagamentoRede::obterBandeira do legado.
     * A ordem dos padrões é importante: Elo é verificado antes de
     * Visa/Mastercard porque possui ranges que conflitam (ex: 4011, 4576).
     *
     * @param string $cardNumber Número do cartão (apenas dígitos)
     * @return string Nome da bandeira em minúsculas ou 'desconhecida'
     */
    private function detectBrand(string $cardNumber): string
    {
        $number = preg_replace('/\D/', '', $cardNumber);

        foreach (self::BRAND_PATTERNS as $brand => $pattern) {
            if (preg_match($pattern, $number)) {
                return $brand;
            }
        }

        return 'desconhecida';
    }

    // ==================== FINALIZAÇÃO DO PEDIDO ====================

    /**
     * Finaliza o pedido no banco legado após pagamento aprovado
     *
     * Marca o pedido como finalizado (finalizado=1) com a data/hora atual.
     * O SIV passa a tratar este pedido como "pago" para operações,
     * conferência, NF-e e logística.
     *
     * @param int $pedidoId ID do pedido no banco legado
     */
    private function finalizePedido(int $pedidoId): void
    {
        Pedido::where('id', $pedidoId)->update([
            'finalizado'     => Pedido::STATUS_FINALIZADO,
            'data_finalizado' => now(),
        ]);

        Log::info("[REDE] finalizePedido: pedido finalizado", [
            'pedido_id' => $pedidoId,
        ]);
    }

    /**
     * Atualiza a forma de pagamento do pedido com base na bandeira do cartão
     *
     * Busca na tabela formas_pagamentos a forma que corresponde à bandeira
     * e ao tipo (crédito/débito). Necessário para que o SIV identifique
     * corretamente o meio de pagamento nos relatórios e conciliação.
     *
     * @param int    $pedidoId ID do pedido
     * @param string $bandeira Nome da bandeira (visa, mastercard, etc.)
     * @param string $kind     'Credit' ou 'Debit'
     */
    private function updateFormaPagamento(int $pedidoId, string $bandeira, string $kind): void
    {
        // Determina a coluna de filtro conforme tipo da transação
        $column = $kind === 'Debit' ? 'rede_debito' : 'rede_credito';

        // Busca forma de pagamento ativa que corresponde à bandeira e tipo
        $forma = FormaPagamento::where('ativo', 1)
            ->where($column, 1)
            ->whereRaw("LOWER(bandeira) LIKE ?", ['%' . strtolower($bandeira) . '%'])
            ->first();

        if ($forma) {
            Pedido::where('id', $pedidoId)->update([
                'formas_pagamento_id' => $forma->id,
            ]);

            Log::info("[REDE] updateFormaPagamento: forma de pagamento atualizada", [
                'pedido_id' => $pedidoId,
                'forma_id'  => $forma->id,
                'bandeira'  => $bandeira,
                'kind'      => $kind,
            ]);
        } else {
            // Bandeira não encontrada na tabela — log para investigação
            // Não é erro crítico: o pedido já foi pago, apenas o relatório pode ficar incompleto
            Log::warning("[REDE] updateFormaPagamento: forma de pagamento não encontrada para bandeira", [
                'pedido_id' => $pedidoId,
                'bandeira'  => $bandeira,
                'kind'      => $kind,
                'column'    => $column,
            ]);
        }
    }

    // ==================== HELPERS ====================

    /**
     * Salva dados de autorização e captura no registro do pagamento
     *
     * Converte os objetos de Authorization e Capture do SDK em arrays
     * para persistência. Os JSONs são mascarados (PCI-DSS) pelo model.
     *
     * @param PagamentoRede $pagamento Registro do pagamento
     * @param Transaction   $transaction Transação completa retornada pelo SDK
     */
    private function saveAuthorizationAndCapture(PagamentoRede $pagamento, Transaction $transaction): void
    {
        // Converte authorization e capture para arrays
        $authorization = $transaction->getAuthorization()
            ? json_decode(json_encode($transaction->getAuthorization()), true)
            : [];

        $capture = $transaction->getCapture()
            ? json_decode(json_encode($transaction->getCapture()), true)
            : [];

        $pagamento->saveAuthorizationAndCapture($authorization, $capture);
    }

    /**
     * Monta array de retorno para erro
     *
     * @param string             $error     Mensagem de erro
     * @param string             $kind      'Credit' ou 'Debit' (determina se inclui redirect_3ds)
     * @param PagamentoRede|null $pagamento Registro do pagamento (se já foi criado)
     * @return array
     */
    private function errorResult(string $error, string $kind, ?PagamentoRede $pagamento = null): array
    {
        $result = [
            'success'   => false,
            'error'     => $error,
            'pagamento' => $pagamento,
        ];

        // Débito inclui campo redirect_3ds no retorno
        if ($kind === 'Debit') {
            $result['redirect_3ds'] = null;
        }

        return $result;
    }

    /**
     * Monta array de retorno para sucesso
     *
     * @param PagamentoRede $pagamento Registro do pagamento aprovado
     * @param string        $kind      'Credit' ou 'Debit'
     * @return array
     */
    private function successResult(PagamentoRede $pagamento, string $kind): array
    {
        $result = [
            'success'   => true,
            'error'     => null,
            'pagamento' => $pagamento,
        ];

        // Débito inclui campo redirect_3ds no retorno
        if ($kind === 'Debit') {
            $result['redirect_3ds'] = null;
        }

        return $result;
    }
}
