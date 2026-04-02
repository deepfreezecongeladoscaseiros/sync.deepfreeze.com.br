<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

/**
 * Controller: Listagem de Marcas (Admin)
 *
 * Exibe marcas do banco legado em modo somente leitura.
 * O CRUD de marcas é feito no SIV (sistema legado).
 */
class BrandController extends Controller
{
    /**
     * Lista todas as marcas do banco legado.
     */
    public function index(Request $request)
    {
        $query = Brand::withCount('products');

        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function($q) use ($search) {
                $q->where('nome_marca', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }

        $brands = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('admin.brands.index', compact('brands'));
    }
}
