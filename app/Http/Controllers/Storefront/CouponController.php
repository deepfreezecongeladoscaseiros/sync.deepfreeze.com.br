<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller de Cupons de Desconto (AJAX).
 *
 * Valida cupons promocionais no checkout antes de finalizar o pedido.
 * O cupom é efetivamente aplicado (gravado em pedidos_descontos)
 * apenas no momento da criação do pedido pelo LegacyOrderService.
 */
class CouponController extends Controller
{
    protected CouponService $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    /**
     * Valida um código de cupom (AJAX).
     * POST /cupom/validar
     *
     * Não aplica o cupom ao pedido — apenas valida e retorna o desconto.
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

        $result = $this->couponService->validate($code, $subtotal, $formaPagamentoId);

        return response()->json([
            'valid'             => $result['valid'],
            'message'           => $result['message'],
            'discount'          => $result['discount'] ?? 0,
            'discount_formatted' => $result['discount'] ? 'R$ ' . number_format($result['discount'], 2, ',', '.') : null,
            'description'       => $result['description'] ?? null,
            'type'              => $result['type'] ?? null,
        ]);
    }
}
