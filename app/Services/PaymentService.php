<?php

namespace App\Services;

use App\Models\Legacy\FormaPagamento;
use App\Models\Legacy\LojaFormaPagamento;
use App\Models\Legacy\PagamentoCielo;
use App\Models\Legacy\Pedido;
use Illuminate\Support\Facades\Log;

/**
 * Service de Pagamento.
 *
 * Gerencia formas de pagamento disponíveis e processa pagamentos.
 * Grava resultados na tabela 'pagamentos_cielo' do banco legado
 * para que o SIV processe conciliação financeira.
 *
 * Formas de pagamento disponíveis para a nova loja (site):
 * - Online (gateway): Cielo (ID 46), Rede Crédito (62), Rede Débito (63)
 * - PIX: ID 75 (processado manualmente — chave enviada por e-mail)
 * - Dinheiro: ID 1 (apenas retirada em loja)
 *
 * A integração com gateway de pagamento será implementada progressivamente:
 * 1. Primeiro: PIX e "a combinar" (sem gateway)
 * 2. Depois: Cielo Checkout ou outro gateway moderno
 */
class PaymentService
{
    // IDs das formas de pagamento conhecidas do legado
    const FORMA_DINHEIRO = 1;
    const FORMA_CHEQUE = 4;
    const FORMA_CIELO = 46;
    const FORMA_REDE_CREDITO = 62;
    const FORMA_REDE_DEBITO = 63;
    const FORMA_PIX = 75;

    // Formas que podem ser oferecidas na nova loja virtual
    // (filtradas por loja + ativa_site + relevantes para e-commerce)
    const FORMAS_SITE = [
        self::FORMA_CIELO,
        self::FORMA_REDE_CREDITO,
        self::FORMA_REDE_DEBITO,
        self::FORMA_PIX,
        self::FORMA_DINHEIRO,
    ];

    /**
     * Retorna formas de pagamento disponíveis para uma loja no site.
     *
     * Cruza: formas_pagamentos (ativas) × lojas_formas_pagamentos (ativa_site=1)
     * Filtra apenas as formas relevantes para o e-commerce (FORMAS_SITE).
     *
     * @param int $lojaId ID da loja
     * @return \Illuminate\Support\Collection Formas de pagamento disponíveis
     */
    public function getAvailableMethods(int $lojaId): \Illuminate\Support\Collection
    {
        // Busca IDs das formas ativas no site para a loja
        $formasLojaIds = LojaFormaPagamento::activeSiteForStore($lojaId)
            ->pluck('formas_pagamento_id')
            ->toArray();

        // Filtra apenas formas relevantes para o e-commerce e ativas
        return FormaPagamento::where('ativo', 1)
            ->whereIn('id', $formasLojaIds)
            ->whereIn('id', self::FORMAS_SITE)
            ->orderByRaw("FIELD(id, " . implode(',', self::FORMAS_SITE) . ")")
            ->get();
    }

    /**
     * Retorna formas de pagamento disponíveis para TODAS as lojas ativas.
     * Útil quando a loja de entrega ainda não foi determinada.
     *
     * Retorna a UNIÃO de formas disponíveis em qualquer loja ativa.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAvailableMethodsAllStores(): \Illuminate\Support\Collection
    {
        // Busca IDs de formas ativas no site em qualquer loja
        $formasIds = LojaFormaPagamento::where('ativa_site', 1)
            ->whereNull('deleted')
            ->distinct()
            ->pluck('formas_pagamento_id')
            ->toArray();

        return FormaPagamento::where('ativo', 1)
            ->whereIn('id', $formasIds)
            ->whereIn('id', self::FORMAS_SITE)
            ->orderByRaw("FIELD(id, " . implode(',', self::FORMAS_SITE) . ")")
            ->get();
    }

    /**
     * Valida se uma forma de pagamento é permitida para a loja.
     *
     * @param int $formaPagamentoId ID da forma de pagamento
     * @param int|null $lojaId ID da loja (null = aceita qualquer loja)
     * @return bool
     */
    public function isMethodAllowed(int $formaPagamentoId, ?int $lojaId = null): bool
    {
        // Verifica se está na lista de formas do site
        if (!in_array($formaPagamentoId, self::FORMAS_SITE)) {
            return false;
        }

        // Verifica se forma está ativa globalmente
        $forma = FormaPagamento::where('id', $formaPagamentoId)->where('ativo', 1)->first();
        if (!$forma) {
            return false;
        }

        // Se loja especificada, verifica se está ativa na loja
        if ($lojaId) {
            return LojaFormaPagamento::activeSiteForStore($lojaId)
                ->where('formas_pagamento_id', $formaPagamentoId)
                ->exists();
        }

        return true;
    }

    /**
     * Registra um pagamento na tabela pagamentos_cielo.
     *
     * Usado tanto para pagamentos reais (callback do gateway)
     * quanto para registros manuais (PIX, dinheiro).
     *
     * @param int $pedidoId ID do pedido
     * @param int $status Status do pagamento (2=pago, 3=negado, etc.)
     * @param int $valorCentavos Valor em centavos
     * @param array $extraData Dados adicionais (tid, cielo_id, json, etc.)
     * @return PagamentoCielo Registro criado
     */
    public function registerPayment(int $pedidoId, int $status, int $valorCentavos, array $extraData = []): PagamentoCielo
    {
        $pagamento = PagamentoCielo::create([
            'pedido_id'                    => $pedidoId,
            'status_pagamento'             => $status,
            'reais_pago'                   => $valorCentavos,
            'cielo_id'                     => $extraData['cielo_id'] ?? null,
            'tid'                          => $extraData['tid'] ?? null,
            'checkout_cielo_order_number'  => $extraData['order_number'] ?? null,
            'metodo'                       => $extraData['metodo'] ?? null,
            'bandeira'                     => $extraData['bandeira'] ?? null,
            'json'                         => $extraData['json'] ?? null,
        ]);

        Log::info('[PAGAMENTO] Pagamento registrado', [
            'pedido_id' => $pedidoId,
            'status' => $status,
            'valor_centavos' => $valorCentavos,
            'pagamento_id' => $pagamento->id,
        ]);

        return $pagamento;
    }

    /**
     * Confirma pagamento e finaliza o pedido.
     *
     * Chamado pelo callback do gateway ou confirmação manual (PIX).
     * Registra pagamento em pagamentos_cielo e marca pedido como finalizado.
     *
     * @param int $pedidoId ID do pedido
     * @param int $valorCentavos Valor pago em centavos
     * @param array $gatewayData Dados do gateway (cielo_id, tid, etc.)
     * @return bool true se finalizado com sucesso
     */
    public function confirmPayment(int $pedidoId, int $valorCentavos, array $gatewayData = []): bool
    {
        $pedido = Pedido::find($pedidoId);

        if (!$pedido) {
            Log::error('[PAGAMENTO] Pedido não encontrado para confirmação', ['pedido_id' => $pedidoId]);
            return false;
        }

        // Verifica se já foi pago (evita duplicidade)
        $jaPago = PagamentoCielo::paidForOrder($pedidoId)->exists();
        if ($jaPago) {
            Log::warning('[PAGAMENTO] Pedido já possui pagamento aprovado', ['pedido_id' => $pedidoId]);
            return true;
        }

        // Registra pagamento como aprovado
        $this->registerPayment($pedidoId, PagamentoCielo::STATUS_PAGO, $valorCentavos, $gatewayData);

        // Finaliza o pedido
        $pedido->finalizado = Pedido::STATUS_FINALIZADO;
        $pedido->data_finalizado = now();
        $pedido->save();

        Log::info('[PAGAMENTO] Pedido finalizado após confirmação de pagamento', [
            'pedido_id' => $pedidoId,
            'valor_centavos' => $valorCentavos,
        ]);

        return true;
    }

    /**
     * Verifica se um pedido já foi pago.
     *
     * @param int $pedidoId ID do pedido
     * @return bool
     */
    public function isPaid(int $pedidoId): bool
    {
        return PagamentoCielo::paidForOrder($pedidoId)->exists();
    }

    /**
     * Retorna o ícone CSS (FontAwesome) para uma forma de pagamento.
     * Usado na view do checkout para exibir ícones ao lado das opções.
     */
    public static function getIcon(int $formaId): string
    {
        return match ($formaId) {
            self::FORMA_CIELO, self::FORMA_REDE_CREDITO => 'fa-credit-card',
            self::FORMA_REDE_DEBITO                      => 'fa-credit-card-alt',
            self::FORMA_PIX                               => 'fa-qrcode',
            self::FORMA_DINHEIRO                          => 'fa-money',
            default                                       => 'fa-credit-card',
        };
    }

    /**
     * Retorna descrição curta para exibição no checkout.
     */
    public static function getDescription(int $formaId): string
    {
        return match ($formaId) {
            self::FORMA_CIELO         => 'Pague com cartão de crédito ou débito de forma segura.',
            self::FORMA_REDE_CREDITO  => 'Cartão de crédito via Rede.',
            self::FORMA_REDE_DEBITO   => 'Cartão de débito via Rede.',
            self::FORMA_PIX           => 'Enviaremos a chave PIX por e-mail para pagamento.',
            self::FORMA_DINHEIRO      => 'Pagamento em dinheiro na retirada do pedido.',
            default                   => '',
        };
    }
}
