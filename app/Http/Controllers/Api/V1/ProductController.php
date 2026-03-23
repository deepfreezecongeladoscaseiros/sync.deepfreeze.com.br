<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * Controller: API de Produtos (V1)
 *
 * IMPORTANTE: Queries usam nomes reais das colunas do banco legado (português).
 * O mapeamento inglês→português é feito no Model Product via $columnMap.
 *
 * Colunas legado mais usadas:
 * - ativo (active), descricao (name), codigo (sku)
 * - preco (price), preco_promocional (promotional_price)
 * - categoria_id (category_id), marca_id (brand_id), fabricante_id (manufacturer_id)
 * - ordem_exibicao_site (display_order)
 * - ingredientes (ingredients), alergenicos_manual (allergens)
 * - in_contem_gluten, in_sem_lactose, in_baixo_lactose, bebida_alcoolica
 * - combo (is_combo), pacote (is_package), produzido_por_encomenda (made_to_order)
 */
class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'manufacturer', 'images'])
            ->where('ativo', 1)
            ->withStockQuantity();

        $query = $this->applyFilters($query, $request);

        $perPage = min($request->input('per_page', 15), 100);
        $products = $query->paginate($perPage);

        return ProductResource::collection($products);
    }

    public function show($id)
    {
        $product = Product::with(['category', 'brand', 'manufacturer', 'images'])
            ->where('ativo', 1)
            ->withStockQuantity()
            ->findOrFail($id);

        return new ProductResource($product);
    }

    public function search(Request $request)
    {
        $searchTerm = $request->input('q');

        if (empty($searchTerm)) {
            return response()->json([
                'message' => 'Search term is required',
                'data' => []
            ], 400);
        }

        $query = Product::with(['category', 'brand', 'manufacturer', 'images'])
            ->where('ativo', 1)
            ->withStockQuantity();

        // Busca em múltiplos campos (colunas legado)
        $query->where(function ($q) use ($searchTerm) {
            $q->where('descricao', 'like', "%{$searchTerm}%")       // name
              ->orWhere('codigo', 'like', "%{$searchTerm}%")        // sku
              ->orWhere('apresentacao', 'like', "%{$searchTerm}%")  // presentation
              ->orWhere('ingredientes', 'like', "%{$searchTerm}%")  // ingredients
              ->orWhere('propriedades', 'like', "%{$searchTerm}%")  // properties
              ->orWhereHas('category', function ($q) use ($searchTerm) {
                  $q->where('nome', 'like', "%{$searchTerm}%");     // category name
              })
              ->orWhereHas('brand', function ($q) use ($searchTerm) {
                  $q->where('nome', 'like', "%{$searchTerm}%");     // brand name
              });
        });

        $query = $this->applyFilters($query, $request);

        $perPage = min($request->input('per_page', 15), 100);
        $products = $query->paginate($perPage);

        return ProductResource::collection($products);
    }

    public function featured(Request $request)
    {
        $query = Product::with(['category', 'brand', 'manufacturer', 'images'])
            ->where('ativo', 1)
            ->where('ordem_exibicao_site', '>', 0)
            ->orderBy('ordem_exibicao_site', 'asc')
            ->withStockQuantity();

        $query = $this->applyFilters($query, $request);

        $perPage = min($request->input('per_page', 15), 100);
        $products = $query->paginate($perPage);

        return ProductResource::collection($products);
    }

    public function onSale(Request $request)
    {
        $query = Product::with(['category', 'brand', 'manufacturer', 'images'])
            ->where('ativo', 1)
            ->onPromotion()  // Scope que trata varchar com CAST
            ->withStockQuantity()
            ->orderByPrice('asc');

        $query = $this->applyFilters($query, $request);

        $perPage = min($request->input('per_page', 15), 100);
        $products = $query->paginate($perPage);

        return ProductResource::collection($products);
    }

    /**
     * Aplica filtros usando colunas reais do banco legado
     */
    private function applyFilters($query, Request $request)
    {
        // Filtro por categoria (coluna legado: categoria_id)
        if ($request->filled('category_id')) {
            $query->where('categoria_id', $request->input('category_id'));
        }

        // Filtro por marca (coluna legado: marca_id)
        if ($request->filled('brand_id')) {
            $query->where('marca_id', $request->input('brand_id'));
        }

        // Filtro por fabricante (coluna legado: fabricante_id)
        if ($request->filled('manufacturer_id')) {
            $query->where('fabricante_id', $request->input('manufacturer_id'));
        }

        // Filtro de preço mínimo (preco é varchar no legado - precisa CAST)
        if ($request->filled('min_price')) {
            $min = (float) $request->input('min_price');
            $query->whereRaw('CAST(preco AS DECIMAL(10,2)) >= ?', [$min]);
        }

        // Filtro de preço máximo
        if ($request->filled('max_price')) {
            $max = (float) $request->input('max_price');
            $query->whereRaw('CAST(preco AS DECIMAL(10,2)) <= ?', [$max]);
        }

        // Filtro de estoque (via subquery - coluna _stock do scope withStockQuantity)
        if ($request->filled('in_stock')) {
            if ($request->input('in_stock') === 'true' || $request->input('in_stock') === '1') {
                $query->whereRaw(
                    '(SELECT COALESCE(SUM(estoque_atual_calculado - giro_balcao), 0)
                      FROM otm_estoques_lojas
                      WHERE otm_estoques_lojas.produto_id = produtos.id) > 0'
                );
            }
        }

        // Filtro sem glúten (coluna legado: in_contem_gluten)
        if ($request->filled('gluten_free')) {
            if ($request->input('gluten_free') === 'true' || $request->input('gluten_free') === '1') {
                $query->where(function ($q) {
                    $q->whereNull('in_contem_gluten')
                      ->orWhere('in_contem_gluten', 0);
                });
            }
        }

        // Filtro sem lactose (coluna legado: in_sem_lactose)
        if ($request->filled('lactose_free')) {
            if ($request->input('lactose_free') === 'true' || $request->input('lactose_free') === '1') {
                $query->where('in_sem_lactose', 1);
            }
        }

        // Filtro baixa lactose (coluna legado: in_baixo_lactose)
        if ($request->filled('low_lactose')) {
            if ($request->input('low_lactose') === 'true' || $request->input('low_lactose') === '1') {
                $query->where('in_baixo_lactose', 1);
            }
        }

        // Filtro bebida alcoólica (coluna legado: bebida_alcoolica)
        if ($request->filled('alcoholic')) {
            if ($request->input('alcoholic') === 'true' || $request->input('alcoholic') === '1') {
                $query->where('bebida_alcoolica', 1);
            } else {
                $query->where(function ($q) {
                    $q->whereNull('bebida_alcoolica')
                      ->orWhere('bebida_alcoolica', 0);
                });
            }
        }

        // Filtro por ingrediente (coluna legado: ingredientes)
        if ($request->filled('has_ingredient')) {
            $ingredient = $request->input('has_ingredient');
            $query->where('ingredientes', 'like', "%{$ingredient}%");
        }

        // Filtro sem ingrediente
        if ($request->filled('without_ingredient')) {
            $ingredient = $request->input('without_ingredient');
            $query->where(function ($q) use ($ingredient) {
                $q->where('ingredientes', 'not like', "%{$ingredient}%")
                  ->orWhereNull('ingredientes');
            });
        }

        // Filtro por alérgeno (coluna legado: alergenicos_manual)
        if ($request->filled('allergen')) {
            $allergen = $request->input('allergen');
            $query->where('alergenicos_manual', 'like', "%{$allergen}%");
        }

        // Filtro sem alérgeno
        if ($request->filled('without_allergen')) {
            $allergen = $request->input('without_allergen');
            $query->where(function ($q) use ($allergen) {
                $q->where('alergenicos_manual', 'not like', "%{$allergen}%")
                  ->orWhereNull('alergenicos_manual');
            });
        }

        // Filtro combo (coluna legado: combo)
        if ($request->filled('is_combo')) {
            if ($request->input('is_combo') === 'true' || $request->input('is_combo') === '1') {
                $query->where('combo', 1);
            }
        }

        // Filtro pacote (coluna legado: pacote)
        if ($request->filled('is_package')) {
            if ($request->input('is_package') === 'true' || $request->input('is_package') === '1') {
                $query->whereNotNull('pacote')->where('pacote', '>', 0);
            }
        }

        // Filtro sob encomenda (coluna legado: produzido_por_encomenda)
        if ($request->filled('made_to_order')) {
            if ($request->input('made_to_order') === 'true' || $request->input('made_to_order') === '1') {
                $query->where('produzido_por_encomenda', 1);
            }
        }

        // Ordenação
        $sortBy = $request->input('sort_by', 'created');
        $sortOrder = strtolower($request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';

        // Mapeamento de nomes da API para colunas legado
        $sortMap = [
            'name'              => 'descricao',
            'price'             => 'preco',
            'promotional_price' => 'preco_promocional',
            'created_at'        => 'created',
            'updated_at'        => 'updated',
            'display_order'     => 'ordem_exibicao_site',
            // 'stock' não pode ser ordenado diretamente (é subquery)
        ];

        $realColumn = $sortMap[$sortBy] ?? $sortBy;

        // Valida que a coluna existe para evitar SQL injection
        $allowedColumns = ['descricao', 'preco', 'preco_promocional', 'created', 'updated', 'ordem_exibicao_site', 'codigo'];
        if (in_array($realColumn, $allowedColumns)) {
            $query->orderBy($realColumn, $sortOrder);
        }

        return $query;
    }
}
