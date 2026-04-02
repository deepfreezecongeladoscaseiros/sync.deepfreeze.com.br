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

        // Busca o produto pelo slug (accessor: Str::slug(codigo + '-' + descricao))
        // O slug combina código + descrição (ex: "ice01-arroz-a-grega").
        // Estratégia: tenta extrair o código do início do slug e buscar por ele.
        // Se não encontrar, faz fallback com LIKE na descrição.
        $product = null;

        // Tenta extrair o código do produto do slug (parte antes do primeiro '-' que não é alfanumérico puro)
        // Ex: "lh38-salada-oriental-congelada" → código "LH38"
        if (preg_match('/^([a-z0-9]+)-/', $productSlug, $matches)) {
            $possibleCode = strtoupper($matches[1]);
            $product = Product::with(['category', 'images', 'brand'])
                ->where('categoria_id', $category->id)
                ->where('codigo', $possibleCode)
                ->active()
                ->first();

            // Confirma match exato pelo slug (código pode bater mas descrição ser diferente)
            if ($product && $product->slug !== $productSlug) {
                $product = null;
            }
        }

        // Fallback: busca por LIKE na descrição (para slugs sem código no início)
        if (!$product) {
            $nameLike = '%' . str_replace('-', '%', $productSlug) . '%';
            $product = Product::with(['category', 'images', 'brand'])
                ->where('categoria_id', $category->id)
                ->where('descricao', 'LIKE', $nameLike)
                ->active()
                ->get()
                ->first(fn($p) => $p->slug === $productSlug);
        }

        if (!$product) {
            abort(404);
        }

        // Busca produtos relacionados (mesma categoria, exceto o atual)
        $relatedProducts = Product::with(['category', 'images'])
            ->where('categoria_id', $category->id)
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
        $product = Product::with(['category', 'images', 'brand'])
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
