<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\CouponService;
use App\Services\GiftCardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller de Cupons e Vale Presente (AJAX).
 *
 * Valida códigos de desconto no checkout antes de finalizar o pedido.
 * Aceita tanto cupons promocionais quanto gift cards (vale presente)
 * no mesmo campo de input — a detecção é automática.
 *
 * Fluxo:
 * 1. Tenta validar como cupom (tabela promocionais)
 * 2. Se não encontrado, tenta validar como gift card (tabela gift_cards)
 * 3. Retorna resultado com tipo 'CUPOM' ou 'GIFT_CARD'
 *
 * A aplicação efetiva ocorre no checkout store() via LegacyOrderService.
 */
class CouponController extends Controller
{
    protected CouponService $couponService;
    protected GiftCardService $giftCardService;

    public function __construct(CouponService $couponService, GiftCardService $giftCardService)
    {
        $this->couponService = $couponService;
        $this->giftCardService = $giftCardService;
    }

    /**
     * Valida um código de cupom ou gift card (AJAX).
     * POST /cupom/validar
     *
     * Não aplica ao pedido — apenas valida e retorna o desconto.
     * A aplicação efetiva ocorre no checkout store().
     */
    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'code'     => 'required|string|max:50',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $code = $request->input('code');
        $subtotal = (float) $request->input('subtotal');
        $formaPagamentoId = $request->input('formas_pagamento_id') ? (int) $request->input('formas_pagamento_id') : null;

        // Tenta como cupom primeiro
        $result = $this->couponService->validate($code, $subtotal, $formaPagamentoId);

        // Se cupom não encontrado, tenta como gift card (replica lógica do legado)
        if (!$result['valid'] && $result['message'] === 'Cupom não encontrado.') {
            $giftResult = $this->giftCardService->validate($code, $subtotal);

            if ($giftResult['valid']) {
                return response()->json([
                    'valid'              => true,
                    'message'            => $giftResult['message'],
                    'discount'           => $giftResult['discount'],
                    'discount_formatted' => 'R$ ' . number_format($giftResult['discount'], 2, ',', '.'),
                    'description'        => $giftResult['description'],
                    'type'               => 'GIFT_CARD',
                ]);
            }

            // Se também não é gift card válido, retorna a mensagem do gift card
            // (mais específica que "cupom não encontrado")
            if ($this->giftCardService->isGiftCardCode($code)) {
                $result = [
                    'valid'    => false,
                    'message'  => $giftResult['message'],
                    'discount' => 0,
                ];
            }
        }

        return response()->json([
            'valid'              => $result['valid'],
            'message'            => $result['message'],
            'discount'           => $result['discount'] ?? 0,
            'discount_formatted' => $result['discount'] ? 'R$ ' . number_format($result['discount'], 2, ',', '.') : null,
            'description'        => $result['description'] ?? null,
            'type'               => $result['type'] ?? null,
        ]);
    }
}
