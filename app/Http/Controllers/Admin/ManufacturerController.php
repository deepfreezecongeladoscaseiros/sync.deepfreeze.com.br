<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Manufacturer;
use Illuminate\Http\Request;

/**
 * Controller: Listagem de Fabricantes (Admin)
 *
 * Exibe fabricantes do banco legado em modo somente leitura.
 * O CRUD de fabricantes é feito no SIV (sistema legado).
 */
class ManufacturerController extends Controller
{
    /**
     * Lista todos os fabricantes do banco legado.
     */
    public function index(Request $request)
    {
        $query = Manufacturer::withCount('products');

        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function($q) use ($search) {
                $q->where('trade_name', 'like', "%{$search}%")
                  ->orWhere('legal_name', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }

        $manufacturers = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('admin.manufacturers.index', compact('manufacturers'));
    }
}
