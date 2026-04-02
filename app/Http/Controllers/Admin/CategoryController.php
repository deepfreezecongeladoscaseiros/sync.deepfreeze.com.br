<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

/**
 * Controller: Listagem de Categorias (Admin)
 *
 * Exibe categorias do banco legado em modo somente leitura.
 * O CRUD de categorias é feito no SIV (sistema legado).
 * Futuramente, este painel permitirá configurar imagens de
 * apresentação e outras personalizações de marketing.
 */
class CategoryController extends Controller
{
    /**
     * Lista todas as categorias do banco legado.
     */
    public function index(Request $request)
    {
        $query = Category::withCount('products');

        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }

        $categories = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }
}
