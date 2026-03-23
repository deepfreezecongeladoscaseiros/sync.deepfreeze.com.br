<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Session;

/**
 * Serviço de Carrinho de Compras (Session-based)
 *
 * Gerencia o carrinho de compras usando a sessão do Laravel.
 * Cada item é indexado pelo product_id e contém:
 * product_id, name, price, image, url, quantity
 */
class CartService
{
    /** Chave usada na sessão para armazenar o carrinho */
    const SESSION_KEY = 'cart';

    /** Quantidade mínima permitida por item */
    const MIN_QTY = 1;

    /** Quantidade máxima permitida por item */
    const MAX_QTY = 99;

    /**
     * Adiciona um produto ao carrinho ou incrementa a quantidade se já existir.
     *
     * Busca o produto no banco para garantir dados atualizados (preço, imagem, disponibilidade).
     * Se o produto já está no carrinho, soma a quantidade informada à existente.
     *
     * @param int $productId ID do produto
     * @param int $quantity Quantidade a adicionar (padrão 1)
     * @return array Dados do item adicionado/atualizado
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Se produto não existir
     * @throws \Exception Se produto não estiver disponível
     */
    public function add(int $productId, int $quantity = 1): array
    {
        // Valida produto no banco — findOrFail lança 404 se não existir
        $product = Product::with('images', 'category')->findOrFail($productId);

        // Verifica disponibilidade (ativo + disponível + estoque > 0)
        if (!$product->isAvailable()) {
            throw new \Exception('Este produto não está disponível no momento.');
        }

        $cart = $this->getCart();

        // Se já existe no carrinho, soma a quantidade
        if (isset($cart[$productId])) {
            $newQty = $cart[$productId]['quantity'] + $quantity;
            $cart[$productId]['quantity'] = min($newQty, self::MAX_QTY);
            // Atualiza preço (pode ter mudado desde a última adição)
            $cart[$productId]['price'] = $product->getCurrentPrice();
        } else {
            // Novo item no carrinho
            $cart[$productId] = [
                'product_id' => $product->id,
                'name'       => $product->name,
                'price'      => $product->getCurrentPrice(),
                'image'      => $product->getMainImageUrl('thumb'),
                'url'        => $product->url,
                'quantity'   => min(max($quantity, self::MIN_QTY), self::MAX_QTY),
            ];
        }

        Session::put(self::SESSION_KEY, $cart);

        return $cart[$productId];
    }

    /**
     * Atualiza a quantidade de um item no carrinho.
     *
     * Se a quantidade for menor que 1, remove o item.
     * Limita a quantidade entre MIN_QTY e MAX_QTY.
     *
     * @param int $productId ID do produto
     * @param int $quantity Nova quantidade
     * @return bool true se atualizado, false se item não encontrado
     */
    public function update(int $productId, int $quantity): bool
    {
        $cart = $this->getCart();

        if (!isset($cart[$productId])) {
            return false;
        }

        // Quantidade menor que mínimo = remover o item
        if ($quantity < self::MIN_QTY) {
            return $this->remove($productId);
        }

        $cart[$productId]['quantity'] = min($quantity, self::MAX_QTY);
        Session::put(self::SESSION_KEY, $cart);

        return true;
    }

    /**
     * Remove um item do carrinho.
     *
     * @param int $productId ID do produto a remover
     * @return bool true se removido, false se não encontrado
     */
    public function remove(int $productId): bool
    {
        $cart = $this->getCart();

        if (!isset($cart[$productId])) {
            return false;
        }

        unset($cart[$productId]);
        Session::put(self::SESSION_KEY, $cart);

        return true;
    }

    /**
     * Retorna todos os itens do carrinho.
     *
     * @return array Array associativo indexado por product_id
     */
    public function getCart(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    /**
     * Retorna a quantidade total de itens no carrinho.
     * Soma as quantidades individuais de cada produto.
     *
     * @return int
     */
    public function getCount(): int
    {
        $cart = $this->getCart();

        return array_sum(array_column($cart, 'quantity'));
    }

    /**
     * Calcula o subtotal do carrinho (preço * quantidade de cada item).
     *
     * @return float
     */
    public function getSubtotal(): float
    {
        $cart = $this->getCart();
        $subtotal = 0;

        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        return $subtotal;
    }

    /**
     * Retorna um resumo do carrinho para respostas JSON.
     * Inclui contagem, subtotal formatado e lista de itens.
     *
     * @return array
     */
    public function getSummary(): array
    {
        return [
            'cart_count'         => $this->getCount(),
            'subtotal'           => $this->getSubtotal(),
            'subtotal_formatted' => Product::formatPrice($this->getSubtotal()),
            'items'              => array_values($this->getCart()),
        ];
    }

    /**
     * Limpa todo o carrinho.
     *
     * @return void
     */
    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }
}
