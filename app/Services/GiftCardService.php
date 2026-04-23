<?php

namespace App\Services;

use App\Models\Legacy\GiftCard;
use App\Models\Legacy\PedidoDesconto;
use App\Models\Legacy\Promocional;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service de Gift Card (Vale Presente).
 *
 * Valida e aplica gift cards como forma de desconto no checkout.
 * Replica a lógica do legado (VerificarPromocionalPodeSerUsadoController + PedidosController).
 *
 * Fluxo:
 * 1. Cliente digita código no campo de cupom do checkout
 * 2. CouponController tenta como cupom — se não encontrado, chama GiftCardService::validate()
 * 3. validate() verifica: código existe, pedido de compra finalizado, dentro da validade, não usado
 * 4. apply() grava em promocionais (com gift_card_id) + pedidos_descontos (tipo GIFT_CARD)
 *
 * Regras do legado:
 * - Gift card NÃO é cumulativo (cumulativo = 0) — não acumula com cupom
 * - Gift card é one-shot: valor integral é descontado, sem saldo remanescente
 * - Validade de 1 mês a partir da compra
 * - Valor entre R$35 e R$850
 */
class GiftCardService
{
    /**
     * Valida um código de gift card.
     *
     * Replica a lógica de VerificarPromocionalPodeSerUsadoController::index() (linhas 599-690):
     * 1. Coleta códigos de gift cards já usados em pedidos finalizados
     * 2. Busca gift_cards pelo numero_identificacao com validações
     *
     * @param string $code Código do gift card (numero_identificacao)
     * @param float $orderTotal Valor total do pedido
     * @return array ['valid' => bool, 'message' => string, 'gift_card' => ?GiftCard, 'discount' => float]
     */
    public function validate(string $code, float $orderTotal): array
    {
        $code = trim($code);

        if (empty($code)) {
            return $this->error('Informe o código.');
        }

        // Busca gift card válido replicando a query do legado:
        // - numero_identificacao = código informado
        // - pedido de compra finalizado (finalizado = 1)
        // - pedidos_produtos.quantidade = 1
        // - valor_presenteado > 0
        // - data_finalizado <= agora (pedido já foi finalizado)
        // - validade >= hoje (dentro da validade)
        // - código não está na lista de gift cards já usados
        $giftCard = GiftCard::byCode($code)
            ->join('pedidos_produtos', 'pedidos_produtos.id', '=', 'gift_cards.pedido_produto_id')
            ->join('pedidos', 'pedidos.id', '=', 'pedidos_produtos.pedido_id')
            ->where('pedidos.finalizado', 1)
            ->where('pedidos_produtos.quantidade', 1)
            ->where('gift_cards.valor_presenteado', '>', 0)
            ->whereRaw('DATE(pedidos.data_finalizado) <= CURDATE()')
            ->where('gift_cards.validade', '>=', now()->toDateString())
            ->select('gift_cards.*')
            ->first();

        if (!$giftCard) {
            return $this->error('Código não encontrado ou gift card expirado.');
        }

        // Verifica se o gift card já foi usado (aplicado a qualquer pedido)
        // O legado verifica apenas pedidos finalizados, mas no sync o pedido é criado
        // com finalizado=0 e o gift card já fica vinculado — sem essa proteção,
        // o mesmo código poderia ser aplicado em dois pedidos pendentes simultaneamente.
        // O método remove() anula pedido_id ao cancelar, liberando o gift card.
        $alreadyUsed = Promocional::where('codigo', $code)
            ->whereNotNull('gift_card_id')
            ->whereNotNull('pedido_id')
            ->exists();

        if ($alreadyUsed) {
            return $this->error('Este vale presente já foi utilizado.');
        }

        // Gift card válido
        $discount = min((float) $giftCard->valor_presenteado, $orderTotal);

        return [
            'valid'       => true,
            'message'     => 'Vale presente aplicado! Desconto de R$ ' . number_format($discount, 2, ',', '.'),
            'gift_card'   => $giftCard,
            'discount'    => $discount,
            'description' => 'R$ ' . number_format((float) $giftCard->valor_presenteado, 2, ',', '.') . ' de vale presente',
            'type'        => 'GIFT_CARD',
        ];
    }

    /**
     * Aplica um gift card a um pedido.
     *
     * Replica a lógica de PedidosController::usarDescontosPromocionais_v3() (linhas 270-302):
     * 1. Verifica se já não foi registrado para o mesmo pedido
     * 2. Insere em promocionais com gift_card_id, codigo, valor, pedido_id, data_uso, pessoa_id
     * 3. Insere em pedidos_descontos com tipo GIFT_CARD
     *
     * @param string $code Código do gift card
     * @param int $pedidoId ID do pedido
     * @param int $pessoaId ID da pessoa
     * @param float $orderTotal Valor total do pedido
     * @return array ['success' => bool, 'discount' => float, 'message' => string]
     */
    public function apply(string $code, int $pedidoId, int $pessoaId, float $orderTotal): array
    {
        // Re-valida (pode ter sido usado entre a validação AJAX e a aplicação)
        $validation = $this->validate($code, $orderTotal);

        if (!$validation['valid']) {
            return ['success' => false, 'discount' => 0, 'message' => $validation['message']];
        }

        $giftCard = $validation['gift_card'];
        $discount = $validation['discount'];

        try {
            // Verifica se já existe desconto não-cumulativo no pedido (cupom)
            $hasNonCumulative = PedidoDesconto::validForOrder($pedidoId)
                ->nonCumulative()
                ->exists();

            if ($hasNonCumulative) {
                return [
                    'success' => false,
                    'discount' => 0,
                    'message' => 'Já existe um cupom aplicado que não permite acumular com vale presente.',
                ];
            }

            // Verifica se não foi registrado para o mesmo pedido (idempotência — replica legado)
            $alreadyApplied = Promocional::where('codigo', $code)
                ->where('pessoa_id', $pessoaId)
                ->where('pedido_id', $pedidoId)
                ->where('gift_card_id', $giftCard->id)
                ->exists();

            if ($alreadyApplied) {
                return [
                    'success' => true,
                    'discount' => $discount,
                    'message' => 'Vale presente já aplicado a este pedido.',
                ];
            }

            // Grava em promocionais (replica legado: usarDescontosPromocionais_v3 linhas 288-301)
            Promocional::create([
                'codigo'       => $code,
                'pessoa_id'    => $pessoaId,
                'gift_card_id' => $giftCard->id,
                'valor'        => (float) $giftCard->valor_presenteado,
                'percentual'   => null,
                'pedido_id'    => $pedidoId,
                'data_uso'     => now(),
                'validade'     => now()->toDateString(),
            ]);

            // Grava em pedidos_descontos (para cálculo de total e exibição)
            PedidoDesconto::create([
                'pedido_id'          => $pedidoId,
                'tipo'               => 'GIFT_CARD',
                'codigo_promocional' => $code,
                'valor'              => (float) $giftCard->valor_presenteado,
                'percentual'         => null,
                'valido'             => 1,
                'cumulativo'         => 0,
            ]);

            Log::info('[GIFT_CARD] Vale presente aplicado', [
                'codigo' => $code,
                'gift_card_id' => $giftCard->id,
                'pedido_id' => $pedidoId,
                'pessoa_id' => $pessoaId,
                'desconto' => $discount,
            ]);

            return [
                'success'  => true,
                'discount' => $discount,
                'message'  => $validation['message'],
            ];

        } catch (\Exception $e) {
            Log::error('[GIFT_CARD] Erro ao aplicar vale presente', [
                'codigo' => $code,
                'pedido_id' => $pedidoId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'discount' => 0,
                'message' => 'Erro ao aplicar vale presente. Tente novamente.',
            ];
        }
    }

    /**
     * Remove um gift card de um pedido (libera para reuso).
     *
     * @param string $code Código do gift card
     * @param int $pedidoId ID do pedido
     */
    public function remove(string $code, int $pedidoId): void
    {
        // Desativa em pedidos_descontos
        PedidoDesconto::where('pedido_id', $pedidoId)
            ->where('codigo_promocional', trim($code))
            ->where('tipo', 'GIFT_CARD')
            ->update(['valido' => 0]);

        // Remove de promocionais (seta pedido_id e data_uso para null)
        Promocional::where('codigo', trim($code))
            ->where('pedido_id', $pedidoId)
            ->whereNotNull('gift_card_id')
            ->update(['pedido_id' => null, 'data_uso' => null]);
    }

    /**
     * Verifica se um código é de gift card (sem validar disponibilidade completa).
     * Usado para detectar o tipo do código antes da validação.
     *
     * @param string $code Código a verificar
     * @return bool true se o código existe em gift_cards.numero_identificacao
     */
    public function isGiftCardCode(string $code): bool
    {
        return GiftCard::byCode($code)->exists();
    }

    /**
     * Helper para retornar erro padronizado
     */
    protected function error(string $message): array
    {
        return [
            'valid'     => false,
            'message'   => $message,
            'gift_card' => null,
            'discount'  => 0,
        ];
    }
}
