<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::withCount('products');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $perPage = min($request->input('per_page', 50), 100);
        $categories = $query->orderBy('name', 'asc')->paginate($perPage);
        
        return CategoryResource::collection($categories);
    }

    public function show($id)
    {
        $category = Category::withCount('products')->findOrFail($id);
        return new CategoryResource($category);
    }

    public function products(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        
        $query = $category->products()
            ->with(['category', 'brand', 'manufacturer', 'images'])
            ->where('active', true);

        $sortBy = $request->input('sort_by', 'name');
        $sortOrder = $request->input('sort_order', 'asc');
        
        $allowedSorts = ['name', 'price', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $perPage = min($request->input('per_page', 15), 100);
        $products = $query->paginate($perPage);
        
        return ProductResource::collection($products);
    }
}
