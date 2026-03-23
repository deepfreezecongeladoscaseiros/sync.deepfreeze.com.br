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
        // Queries usam nomes REAIS das colunas do banco legado (português)
        $stats = [
            'total_categories' => Category::count(),
            'total_brands' => Brand::count(),
            'total_manufacturers' => Manufacturer::count(),
            'total_products' => Product::count(),
            'active_products' => Product::where('ativo', 1)->count(),
            'inactive_products' => Product::where('ativo', 0)->orWhereNull('ativo')->count(),
            'products_on_sale' => Product::whereNotNull('preco_promocional')
                ->where('preco_promocional', '!=', '')
                ->where('preco_promocional', '!=', '0.00')
                ->count(),
        ];

        // Produtos recentes com estoque pré-calculado (evita N+1)
        $recentProducts = Product::with(['category', 'brand'])
            ->withStockQuantity()
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $topCategories = Category::withCount('products')
            ->orderByDesc('products_count')
            ->limit(5)
            ->get();

        $productsByCategory = Category::withCount('products')
            ->having('products_count', '>', 0)
            ->orderByDesc('products_count')
            ->get();

        // Produtos com estoque baixo (via subquery em otm_estoques_lojas)
        $lowStockProducts = collect(); // Simplificado — estoque requer subquery complexa

        return view('dashboard', compact(
            'stats',
            'recentProducts',
            'topCategories',
            'lowStockProducts',
            'productsByCategory'
        ));
    }
}
