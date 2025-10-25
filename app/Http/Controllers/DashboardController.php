<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Brand;
use App\Models\Manufacturer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_categories' => Category::count(),
            'total_brands' => Brand::count(),
            'total_manufacturers' => Manufacturer::count(),
            'total_products' => Product::count(),
            'active_products' => Product::where('active', true)->count(),
            'inactive_products' => Product::where('active', false)->count(),
            'products_with_stock' => Product::where('stock', '>', 0)->count(),
            'products_out_of_stock' => Product::where('stock', '<=', 0)->count(),
            'products_on_sale' => Product::whereNotNull('promotional_price')->count(),
            'total_stock_value' => Product::sum(DB::raw('stock * price')),
        ];

        $recentProducts = Product::with(['category', 'brand'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $topCategories = Category::withCount('products')
            ->orderBy('products_count', 'desc')
            ->limit(5)
            ->get();

        $lowStockProducts = Product::with(['category', 'brand'])
            ->where('stock', '>', 0)
            ->where('stock', '<=', 10)
            ->orderBy('stock', 'asc')
            ->limit(10)
            ->get();

        $productsByCategory = Category::withCount('products')
            ->having('products_count', '>', 0)
            ->orderBy('products_count', 'desc')
            ->get();

        return view('dashboard', compact(
            'stats',
            'recentProducts',
            'topCategories',
            'lowStockProducts',
            'productsByCategory'
        ));
    }
}
