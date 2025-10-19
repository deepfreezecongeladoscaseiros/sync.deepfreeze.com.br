<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'manufacturer', 'images'])
            ->where('active', true);

        $query = $this->applyFilters($query, $request);
        
        $perPage = min($request->input('per_page', 15), 100);
        $products = $query->paginate($perPage);
        
        return ProductResource::collection($products);
    }

    public function show($id)
    {
        $product = Product::with(['category', 'brand', 'manufacturer', 'images'])
            ->where('active', true)
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
            ->where('active', true);

        $query->where(function($q) use ($searchTerm) {
            $q->where('name', 'like', "%{$searchTerm}%")
              ->orWhere('sku', 'like', "%{$searchTerm}%")
              ->orWhere('presentation', 'like', "%{$searchTerm}%")
              ->orWhere('ingredients', 'like', "%{$searchTerm}%")
              ->orWhere('properties', 'like', "%{$searchTerm}%")
              ->orWhereHas('category', function($q) use ($searchTerm) {
                  $q->where('name', 'like', "%{$searchTerm}%");
              })
              ->orWhereHas('brand', function($q) use ($searchTerm) {
                  $q->where('brand', 'like', "%{$searchTerm}%");
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
            ->where('active', true)
            ->where('display_order', '>', 0)
            ->orderBy('display_order', 'asc');

        $query = $this->applyFilters($query, $request);

        $perPage = min($request->input('per_page', 15), 100);
        $products = $query->paginate($perPage);
        
        return ProductResource::collection($products);
    }

    public function onSale(Request $request)
    {
        $query = Product::with(['category', 'brand', 'manufacturer', 'images'])
            ->where('active', true)
            ->whereNotNull('promotional_price')
            ->where('promotional_price', '>', 0)
            ->orderBy('promotional_price', 'asc');

        $query = $this->applyFilters($query, $request);

        $perPage = min($request->input('per_page', 15), 100);
        $products = $query->paginate($perPage);
        
        return ProductResource::collection($products);
    }

    private function applyFilters($query, Request $request)
    {
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->input('brand_id'));
        }

        if ($request->filled('manufacturer_id')) {
            $query->where('manufacturer_id', $request->input('manufacturer_id'));
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        if ($request->filled('in_stock')) {
            if ($request->input('in_stock') === 'true' || $request->input('in_stock') === '1') {
                $query->where('stock', '>', 0);
            }
        }

        if ($request->filled('gluten_free')) {
            if ($request->input('gluten_free') === 'true' || $request->input('gluten_free') === '1') {
                $query->where('contains_gluten', false);
            }
        }

        if ($request->filled('lactose_free')) {
            if ($request->input('lactose_free') === 'true' || $request->input('lactose_free') === '1') {
                $query->where('lactose_free', true);
            }
        }

        if ($request->filled('low_lactose')) {
            if ($request->input('low_lactose') === 'true' || $request->input('low_lactose') === '1') {
                $query->where('low_lactose', true);
            }
        }

        if ($request->filled('alcoholic')) {
            if ($request->input('alcoholic') === 'true' || $request->input('alcoholic') === '1') {
                $query->where('alcoholic_beverage', true);
            } else {
                $query->where('alcoholic_beverage', false);
            }
        }

        if ($request->filled('has_ingredient')) {
            $ingredient = $request->input('has_ingredient');
            $query->where('ingredients', 'like', "%{$ingredient}%");
        }

        if ($request->filled('without_ingredient')) {
            $ingredient = $request->input('without_ingredient');
            $query->where(function($q) use ($ingredient) {
                $q->where('ingredients', 'not like', "%{$ingredient}%")
                  ->orWhereNull('ingredients');
            });
        }

        if ($request->filled('allergen')) {
            $allergen = $request->input('allergen');
            $query->where('allergens', 'like', "%{$allergen}%");
        }

        if ($request->filled('without_allergen')) {
            $allergen = $request->input('without_allergen');
            $query->where(function($q) use ($allergen) {
                $q->where('allergens', 'not like', "%{$allergen}%")
                  ->orWhereNull('allergens');
            });
        }

        if ($request->filled('is_combo')) {
            if ($request->input('is_combo') === 'true' || $request->input('is_combo') === '1') {
                $query->where('is_combo', true);
            }
        }

        if ($request->filled('is_package')) {
            if ($request->input('is_package') === 'true' || $request->input('is_package') === '1') {
                $query->where('is_package', true);
            }
        }

        if ($request->filled('made_to_order')) {
            if ($request->input('made_to_order') === 'true' || $request->input('made_to_order') === '1') {
                $query->where('made_to_order', true);
            }
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        
        $allowedSorts = ['name', 'price', 'promotional_price', 'created_at', 'updated_at', 'stock', 'display_order'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        return $query;
    }
}
