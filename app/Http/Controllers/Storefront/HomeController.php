<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Exibe a página inicial da loja virtual
     *
     * Por enquanto apenas renderiza a view estática baixada do site Naturallis.
     * Futuramente será integrada com os produtos do banco de dados.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('storefront.home.index');
    }
}
