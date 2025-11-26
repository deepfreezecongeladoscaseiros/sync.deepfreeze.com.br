<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

/**
 * Controller: Página de Detalhes do Produto (Storefront)
 *
 * Exibe a página de detalhes de um produto individual na loja virtual.
 */
class ProductController extends Controller
{
    /**
     * Exibe a página de detalhes do produto
     *
     * Rota: /{category_slug}/{product_slug}
     * Exemplo: /kit-refeicao/roupa-velha-arroz-branco-e-feijao
     *
     * @param string $categorySlug Slug da categoria do produto
     * @param string $productSlug Slug do produto (gerado a partir do nome)
     */
    public function show(string $categorySlug, string $productSlug)
    {
        // Busca a categoria pelo slug
        $category = Category::where('slug', $categorySlug)->firstOrFail();

        // Busca o produto pelo slug (gerado a partir do nome) e categoria
        // O slug é gerado dinamicamente no model Product via getSlugAttribute()
        $product = Product::with(['category', 'images', 'brand', 'nutritionalInfo'])
            ->where('category_id', $category->id)
            ->active()
            ->get()
            ->first(function ($p) use ($productSlug) {
                return $p->slug === $productSlug;
            });

        if (!$product) {
            abort(404);
        }

        // Busca produtos relacionados (mesma categoria, exceto o atual)
        $relatedProducts = Product::with(['category', 'images'])
            ->where('category_id', $category->id)
            ->where('id', '!=', $product->id)
            ->active()
            ->withImage()
            ->limit(4)
            ->get();

        // Prepara dados para breadcrumb
        $breadcrumb = [
            ['title' => 'Home', 'url' => url('/')],
            ['title' => $category->name, 'url' => route('category.show', $category->slug)],
            ['title' => $product->name, 'url' => null],
        ];

        return view('storefront.product.show', compact(
            'product',
            'category',
            'relatedProducts',
            'breadcrumb'
        ));
    }

    /**
     * Rota alternativa: /produto/{sku}
     *
     * Permite acessar produto diretamente pelo SKU (código)
     * Exemplo: /produto/KR57
     */
    public function showBySku(string $sku)
    {
        $product = Product::with(['category', 'images', 'brand', 'nutritionalInfo'])
            ->where('sku', $sku)
            ->active()
            ->firstOrFail();

        // Redireciona para URL amigável se tiver categoria
        if ($product->category) {
            return redirect()->route('product.show', [
                'categorySlug' => $product->category->slug,
                'productSlug' => $product->slug,
            ], 301);
        }

        // Se não tiver categoria, exibe direto
        $breadcrumb = [
            ['title' => 'Home', 'url' => url('/')],
            ['title' => $product->name, 'url' => null],
        ];

        $relatedProducts = collect();

        return view('storefront.product.show', compact(
            'product',
            'relatedProducts',
            'breadcrumb'
        ));
    }
}
