<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * Controller: Exibição de Categorias no Storefront
 *
 * Responsável por:
 * - Listar produtos de uma categoria
 * - Aplicar filtros e ordenação
 * - Paginação de resultados
 */
class CategoryController extends Controller
{
    /**
     * Exibe a página de uma categoria com seus produtos
     *
     * @param Request $request
     * @param string $slug Slug da categoria
     * @return \Illuminate\View\View
     */
    public function show(Request $request, string $slug)
    {
        // Busca a categoria pelo slug
        $category = Category::where('slug', $slug)->firstOrFail();

        // Query base de produtos da categoria
        // Apenas produtos visíveis (ativos + com imagem)
        $query = Product::where('category_id', $category->id)
            ->visibleInStore()
            ->with(['images', 'category']);

        // Aplicar filtros
        $query = $this->applyFilters($query, $request);

        // Aplicar ordenação
        $query = $this->applySort($query, $request);

        // Paginar resultados (12 por página)
        $products = $query->paginate(12)->withQueryString();

        // Total de produtos (para exibição)
        $totalProducts = $products->total();

        // Opções de ordenação para o select
        $sortOptions = [
            ''  => '...',
            '3' => 'Menor Preço',
            '4' => 'Maior Preço',
            '5' => 'A - Z',
            '6' => 'Z - A',
        ];

        return view('storefront.categories.show', compact(
            'category',
            'products',
            'totalProducts',
            'sortOptions'
        ));
    }

    /**
     * Aplica filtros à query de produtos
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyFilters($query, Request $request)
    {
        // Filtro por selo (característica do produto)
        if ($request->filled('selo')) {
            $selo = $request->input('selo');

            // Mapeia selos para campos do produto
            switch ($selo) {
                case '1': // Sem Lactose
                    $query->where('lactose_free', true);
                    break;
                case '2': // Sem Glúten
                    $query->where('contains_gluten', false);
                    break;
                case '3': // Vegetariano
                    // Implementar conforme necessidade
                    break;
            }
        }

        // Filtro de preço mínimo
        if ($request->filled('preco_min')) {
            $query->where(function ($q) use ($request) {
                $min = (float) $request->input('preco_min');
                $q->where('price', '>=', $min)
                  ->orWhere('promotional_price', '>=', $min);
            });
        }

        // Filtro de preço máximo
        if ($request->filled('preco_max')) {
            $query->where(function ($q) use ($request) {
                $max = (float) $request->input('preco_max');
                $q->where('price', '<=', $max);
            });
        }

        // Filtro de busca por nome
        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where('name', 'like', "%{$search}%");
        }

        return $query;
    }

    /**
     * Aplica ordenação à query de produtos
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applySort($query, Request $request)
    {
        $sort = $request->input('filtro', '');

        switch ($sort) {
            case '3': // Menor Preço
                $query->orderByPrice('asc');
                break;

            case '4': // Maior Preço
                $query->orderByPrice('desc');
                break;

            case '5': // A - Z
                $query->orderBy('name', 'asc');
                break;

            case '6': // Z - A
                $query->orderBy('name', 'desc');
                break;

            default:
                // Ordenação padrão: por posição/destaque
                $query->orderBy('display_order', 'asc')
                      ->orderBy('hot', 'desc')
                      ->orderBy('release', 'desc')
                      ->orderBy('name', 'asc');
        }

        return $query;
    }
}
