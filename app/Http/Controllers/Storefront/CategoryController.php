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
 *
 * IMPORTANTE: Queries usam nomes reais das colunas do banco legado (português).
 * O mapeamento inglês→português acontece apenas no Model (via $columnMap),
 * para uso nos templates Blade.
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
        // Busca a categoria pelo slug (coluna 'slug' existe no legado)
        $category = Category::where('slug', $slug)->firstOrFail();

        // Query base: produtos da categoria, ativos, com imagem, com estoque calculado
        // categoria_id = coluna real no legado (mesmo nome)
        $query = Product::where('categoria_id', $category->id)
            ->visibleInStore()
            ->withStockQuantity()
            ->with(['images', 'category']);

        // Aplicar filtros (preço, selos, busca)
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
     * Colunas legado usadas:
     * - in_sem_lactose (tinyint) = lactose_free
     * - in_contem_gluten (tinyint) = contains_gluten
     * - preco (varchar) = price
     * - preco_promocional (varchar) = promotional_price
     * - descricao (varchar) = name
     */
    protected function applyFilters($query, Request $request)
    {
        // Filtro por selo (característica do produto)
        if ($request->filled('selo')) {
            $selo = $request->input('selo');

            switch ($selo) {
                case '1': // Sem Lactose
                    $query->where('in_sem_lactose', 1);
                    break;
                case '2': // Sem Glúten
                    // in_contem_gluten: NULL ou 0 = sem glúten
                    $query->where(function ($q) {
                        $q->whereNull('in_contem_gluten')
                          ->orWhere('in_contem_gluten', 0);
                    });
                    break;
            }
        }

        // Filtro de preço mínimo (preco é varchar no legado - precisa CAST)
        if ($request->filled('preco_min')) {
            $min = (float) $request->input('preco_min');
            $query->where(function ($q) use ($min) {
                $q->whereRaw('CAST(preco AS DECIMAL(10,2)) >= ?', [$min])
                  ->orWhereRaw(
                      "preco_promocional IS NOT NULL AND preco_promocional != '' AND preco_promocional != '0.00'
                       AND CAST(preco_promocional AS DECIMAL(10,2)) >= ?",
                      [$min]
                  );
            });
        }

        // Filtro de preço máximo
        if ($request->filled('preco_max')) {
            $max = (float) $request->input('preco_max');
            $query->whereRaw('CAST(preco AS DECIMAL(10,2)) <= ?', [$max]);
        }

        // Filtro de busca por nome (coluna legado: descricao)
        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where('descricao', 'like', "%{$search}%");
        }

        return $query;
    }

    /**
     * Aplica ordenação à query de produtos
     *
     * Colunas legado usadas:
     * - ordem_exibicao_site (int) = display_order
     * - descricao (varchar) = name
     * - preco/preco_promocional (varchar) = price (via scope orderByPrice)
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
                $query->orderBy('descricao', 'asc');
                break;

            case '6': // Z - A
                $query->orderBy('descricao', 'desc');
                break;

            default:
                // Ordenação padrão: por posição de exibição, depois alfabético
                $query->orderBy('ordem_exibicao_site', 'asc')
                      ->orderBy('descricao', 'asc');
        }

        return $query;
    }
}
