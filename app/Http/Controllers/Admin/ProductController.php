<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * Controller: Listagem de Produtos (Admin)
 *
 * Exibe produtos do banco legado em modo somente leitura.
 * Aplica o mesmo filtro visibleInStore() do storefront para mostrar
 * apenas produtos ativos, com imagem e cadastrados no canal Internet.
 * O CRUD de produtos é feito no SIV (sistema legado).
 */
class ProductController extends Controller
{
    /**
     * Lista produtos do banco legado (somente leitura).
     * Mesma regra de visibilidade do storefront.
     */
    public function index(Request $request)
    {
        // visibleInStore() = active() + withImage() + availableOnline()
        $query = Product::visibleInStore()->with(['category', 'brand', 'images']);

        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function($q) use ($search) {
                $q->where('descricao', 'like', "%{$search}%")
                  ->orWhere('codigo', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%")
                  ->orWhereHas('brand', function($q) use ($search) {
                      $q->where('nome_marca', 'like', "%{$search}%");
                  });
            });
        }

        $products = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('admin.products.index', compact('products'));
    }
}
