<?php

namespace App\Services;

use App\Models\Legacy\PedidoDesconto;
use App\Models\Legacy\Promocional;
use Illuminate\Support\Facades\Log;

/**
 * Service de Cupons de Desconto.
 *
 * Valida e aplica cupons promocionais da tabela 'promocionais' do banco legado.
 * Grava descontos aplicados em 'pedidos_descontos'.
 *
 * Fluxo:
 * 1. Cliente digita código no checkout
 * 2. validate() verifica: existe, disponível, dentro da validade, pode ser usado no site
 * 3. apply() grava em pedidos_descontos + marca promocionais.pedido_id
 * 4. Se pedido cancelado, release() libera o cupom para reuso
 *
 * Regras de cumulatividade:
 * - Se cupom.cumulativo = 0: não pode usar junto com outro não-cumulativo
 * - Se cupom.apenas_dinheiro_debito = 1: só aceita pagamento em dinheiro/débito
 */
class CouponService
{
    /**
     * Valida um código de cupom.
     *
     * @param string $code Código do cupom
     * @param float $orderTotal Valor total do pedido
     * @param int|null $formaPagamentoId Forma de pagamento selecionada
     * @return array ['valid' => bool, 'message' => string, 'coupon' => ?Promocional, 'discount' => float]
     */
    public function validate(string $code, float $orderTotal, ?int $formaPagamentoId = null): array
    {
        $code = trim($code);

        if (empty($code)) {
            return $this->error('Informe o código do cupom.');
        }

        // Busca cupom pelo código
        $coupon = Promocional::byCode($code)->first();

        if (!$coupon) {
            return $this->error('Cupom não encontrado.');
        }

        // Verifica se está disponível (não usado, dentro da validade, permitido no site)
        if (!$coupon->isAvailable()) {
            // Determina mensagem específica
            if ($coupon->pedido_id || $coupon->data_uso) {
                return $this->error('Este cupom já foi utilizado.');
            }
            if ($coupon->validade && $coupon->validade->lt(now())) {
                return $this->error('Este cupom está expirado.');
            }
            if (!$coupon->pode_ser_usado_site) {
                return $this->error('Este cupom não é válido para compras no site.');
            }
            return $this->error('Este cupom não está disponível.');
        }

        // Verifica restrição de forma de pagamento (apenas dinheiro/débito)
        if ($coupon->apenas_dinheiro_debito && $formaPagamentoId) {
            $formasPermitidas = [
                PaymentService::FORMA_DINHEIRO,    // Dinheiro
                PaymentService::FORMA_REDE_DEBITO, // Débito Rede
            ];
            if (!in_array($formaPagamentoId, $formasPermitidas)) {
                return $this->error('Este cupom é válido apenas para pagamento em dinheiro ou débito.');
            }
        }

        // Calcula valor do desconto
        $discount = $coupon->calculateDiscount($orderTotal);

        if ($discount <= 0) {
            return $this->error('Este cupom não oferece desconto para o valor atual.');
        }

        // Monta descrição do desconto
        $description = '';
        if ($coupon->valor && $coupon->valor > 0) {
            $description = 'R$ ' . number_format($coupon->valor, 2, ',', '.') . ' de desconto';
        } elseif ($coupon->percentual && $coupon->percentual > 0) {
            $description = $coupon->percentual . '% de desconto';
        }

        return [
            'valid'       => true,
            'message'     => 'Cupom aplicado! ' . $description,
            'coupon'      => $coupon,
            'discount'    => $discount,
            'description' => $description,
            'type'        => $coupon->valor ? 'valor' : 'percentual',
        ];
    }

    /**
     * Aplica um cupom a um pedido.
     *
     * Grava em pedidos_descontos e marca o cupom como usado em promocionais.
     *
     * @param string $code Código do cupom
     * @param int $pedidoId ID do pedido
     * @param int $pessoaId ID da pessoa
     * @param float $orderTotal Valor total do pedido
     * @return array ['success' => bool, 'discount' => float, 'message' => string]
     */
    public function apply(string $code, int $pedidoId, int $pessoaId, float $orderTotal): array
    {
        // Valida novamente (pode ter sido usado entre a validação e a aplicação)
        $validation = $this->validate($code, $orderTotal);

        if (!$validation['valid']) {
            return ['success' => false, 'discount' => 0, 'message' => $validation['message']];
        }

        $coupon = $validation['coupon'];
        $discount = $validation['discount'];

        try {
            // Verifica se já existe desconto não-cumulativo no pedido
            $hasNonCumulative = PedidoDesconto::validForOrder($pedidoId)
                ->nonCumulative()
                ->exists();

            // Se o pedido já tem desconto não-cumulativo, verifica se o novo cupom é cumulativo
            // (cupons da tabela promocionais não têm campo cumulativo direto — usa-se a regra de negócio)
            if ($hasNonCumulative) {
                return [
                    'success' => false,
                    'discount' => 0,
                    'message' => 'Já existe um cupom aplicado que não permite acumular com outros.',
                ];
            }

            // Grava desconto no pedido
            PedidoDesconto::create([
                'pedido_id'          => $pedidoId,
                'tipo'               => 'CUPOM',
                'codigo_promocional' => $coupon->codigo,
                'percentual'         => $coupon->percentual > 0 ? $coupon->percentual : null,
                'valor'              => $coupon->valor > 0 ? $coupon->valor : null,
                'valido'             => 1,
                'cumulativo'         => 0,
            ]);

            // Marca cupom como usado
            $coupon->markAsUsed($pedidoId, $pessoaId);

            Log::info('[CUPOM] Cupom aplicado', [
                'codigo' => $code,
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
            Log::error('[CUPOM] Erro ao aplicar cupom', [
                'codigo' => $code,
                'pedido_id' => $pedidoId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'discount' => 0,
                'message' => 'Erro ao aplicar cupom. Tente novamente.',
            ];
        }
    }

    /**
     * Remove um cupom de um pedido (libera para reuso).
     *
     * @param string $code Código do cupom
     * @param int $pedidoId ID do pedido
     */
    public function remove(string $code, int $pedidoId): void
    {
        // Desativa em pedidos_descontos
        PedidoDesconto::where('pedido_id', $pedidoId)
            ->where('codigo_promocional', trim($code))
            ->update(['valido' => 0]);

        // Libera em promocionais
        $coupon = Promocional::byCode($code)->where('pedido_id', $pedidoId)->first();
        if ($coupon) {
            $coupon->release();
        }
    }

    /**
     * Calcula o desconto total de um pedido (soma dos descontos válidos).
     *
     * @param int $pedidoId ID do pedido
     * @param float $subtotal Subtotal do pedido
     * @return float Desconto total em R$
     */
    public function getTotalDiscount(int $pedidoId, float $subtotal): float
    {
        $descontos = PedidoDesconto::validForOrder($pedidoId)->get();
        $total = 0;

        foreach ($descontos as $d) {
            if ($d->valor && $d->valor > 0) {
                $total += (float) $d->valor;
            } elseif ($d->percentual && $d->percentual > 0) {
                $total += round($subtotal * (float) $d->percentual / 100, 2);
            }
        }

        // Desconto não pode exceder o subtotal
        return min($total, $subtotal);
    }

    /**
     * Helper para retornar erro padronizado
     */
    protected function error(string $message): array
    {
        return [
            'valid'    => false,
            'message'  => $message,
            'coupon'   => null,
            'discount' => 0,
        ];
    }
}
