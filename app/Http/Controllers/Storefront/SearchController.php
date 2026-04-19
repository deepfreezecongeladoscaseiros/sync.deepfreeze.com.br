<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * Controller: Busca de Produtos no Storefront
 *
 * Busca simples por texto nos campos: codigo (SKU), descricao (nome), apresentacao.
 * Multi-word com AND: "frango empanado" encontra produtos que contenham AMBAS as palavras.
 * Ordenação padrão: produtos com estoque primeiro, depois por código ASC (igual ao legado).
 */
class SearchController extends Controller
{
    /**
     * Exibe a página de resultados da busca
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $termo = trim($request->input('palavra', ''));

        // Query base: produtos ativos, com imagem, canal internet, com estoque calculado
        $query = Product::visibleInStore()
            ->withStockQuantity()
            ->with(['images', 'category']);

        // Busca multi-word com AND: cada palavra deve aparecer em pelo menos um dos campos
        if ($termo !== '') {
            $palavras = array_filter(explode(' ', $termo), fn($p) => $p !== '');

            foreach ($palavras as $palavra) {
                $query->where(function ($q) use ($palavra) {
                    $q->where('codigo', 'like', "%{$palavra}%")
                      ->orWhere('descricao', 'like', "%{$palavra}%")
                      ->orWhere('apresentacao', 'like', "%{$palavra}%");
                });
            }
        }

        // Filtros adicionais (preço, selos)
        $query = $this->applyFilters($query, $request);

        // Ordenação: stock-first como padrão (igual legado), ou dropdown quando selecionado
        $query = $this->applySort($query, $request);

        // Paginar resultados (12 por página, como na categoria)
        $products = $query->paginate(12)->withQueryString();
        $totalProducts = $products->total();

        // Opções de ordenação para o select (mesmo padrão da categoria)
        $sortOptions = [
            ''  => '...',
            '3' => 'Menor Preço',
            '4' => 'Maior Preço',
            '5' => 'A - Z',
            '6' => 'Z - A',
        ];

        return view('storefront.search.index', compact(
            'products',
            'totalProducts',
            'sortOptions',
            'termo'
        ));
    }

    /**
     * Aplica filtros à query de produtos
     *
     * Filtros disponíveis: selo (sem lactose, sem glúten), preço mínimo/máximo.
     * Reutiliza a mesma lógica do CategoryController.
     *
     * Colunas legado:
     * - in_sem_lactose (tinyint)
     * - in_contem_gluten (tinyint)
     * - preco (varchar)
     * - preco_promocional (varchar)
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

        return $query;
    }

    /**
     * Aplica ordenação à query de produtos
     *
     * Padrão da busca: produtos com estoque primeiro, depois por código ASC (igual legado).
     * Quando o usuário seleciona filtro: preço ou alfabético.
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
                // Ordenação padrão da busca: estoque primeiro, depois código ASC
                // _stock é alias da subquery adicionada por withStockQuantity()
                $query->orderByRaw('_stock > 0 DESC')
                      ->orderBy('codigo', 'asc');
        }

        return $query;
    }
}
