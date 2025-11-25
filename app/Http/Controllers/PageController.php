<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Page;
use App\Http\Controllers\Storefront\CategoryController;
use Illuminate\Http\Request;

/**
 * Controller público para exibição de Páginas Internas e Categorias
 *
 * Responsável por:
 * - Exibir páginas institucionais dinâmicas
 * - Exibir categorias de produtos (quando slug corresponde a uma categoria)
 *
 * Este controller é chamado pela wildcard route quando uma URL não é encontrada.
 * A ordem de verificação é: Categoria -> Página -> 404
 */
class PageController extends Controller
{
    /**
     * Exibe uma página interna ou categoria pelo slug
     *
     * @param Request $request
     * @param string $slug
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function show(Request $request, string $slug)
    {
        // 1. Primeiro verifica se é uma categoria
        $category = Category::where('slug', $slug)->first();

        if ($category) {
            // Delega para o CategoryController
            $categoryController = app(CategoryController::class);
            return $categoryController->show($request, $slug);
        }

        // 2. Se não for categoria, busca página ativa com o slug fornecido
        $page = Page::active()->bySlug($slug)->firstOrFail();

        // 3. Retorna view com a página
        return view('storefront.pages.show', compact('page'));
    }
}
