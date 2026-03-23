<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller do Carrinho de Compras (Storefront)
 *
 * Gerencia as operações do carrinho via AJAX (adicionar, atualizar, remover)
 * e renderiza as views do carrinho (sidebar e página completa).
 */
class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Adiciona um produto ao carrinho.
     * Chamado via AJAX pelo botão .js-add-to-cart nos cards de produto.
     *
     * @param Request $request Espera: product_id (int), quantity (int, opcional)
     * @return JsonResponse
     */
    public function add(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity'   => 'nullable|integer|min:1|max:99',
        ]);

        try {
            $item = $this->cartService->add(
                $request->input('product_id'),
                $request->input('quantity', 1)
            );

            $summary = $this->cartService->getSummary();

            return response()->json([
                'success'    => true,
                'message'    => 'Produto adicionado ao carrinho!',
                'item'       => $item,
                'cart_count'  => $summary['cart_count'],
                'subtotal'   => $summary['subtotal_formatted'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Atualiza a quantidade de um item no carrinho.
     * Chamado via AJAX quando o usuário altera a quantidade na sidebar.
     *
     * @param Request $request Espera: product_id (int), quantity (int)
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer',
            'quantity'   => 'required|integer|min:0|max:99',
        ]);

        $updated = $this->cartService->update(
            $request->input('product_id'),
            $request->input('quantity')
        );

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Produto não encontrado no carrinho.',
            ], 404);
        }

        return response()->json(array_merge(
            ['success' => true],
            $this->cartService->getSummary()
        ));
    }

    /**
     * Remove um item do carrinho.
     * Chamado via AJAX pelo botão .js-cart-remove na sidebar.
     *
     * @param Request $request Espera: product_id (int)
     * @return JsonResponse
     */
    public function remove(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer',
        ]);

        $removed = $this->cartService->remove(
            $request->input('product_id')
        );

        if (!$removed) {
            return response()->json([
                'success' => false,
                'message' => 'Produto não encontrado no carrinho.',
            ], 404);
        }

        return response()->json(array_merge(
            ['success' => true],
            $this->cartService->getSummary()
        ));
    }

    /**
     * Retorna o HTML da sidebar do carrinho.
     * Chamado via AJAX para atualizar o conteúdo do #cesta-topo1.
     *
     * @return \Illuminate\View\View
     */
    public function sidebar()
    {
        $cart = $this->cartService->getCart();
        $count = $this->cartService->getCount();
        $subtotal = $this->cartService->getSubtotal();

        return view('storefront.partials.cart-sidebar', compact('cart', 'count', 'subtotal'));
    }

    /**
     * Página completa do carrinho (placeholder).
     * Exibe todos os itens com opção de alterar quantidade e remover.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $cart = $this->cartService->getCart();
        $count = $this->cartService->getCount();
        $subtotal = $this->cartService->getSubtotal();

        return view('storefront.cart.index', compact('cart', 'count', 'subtotal'));
    }
}
