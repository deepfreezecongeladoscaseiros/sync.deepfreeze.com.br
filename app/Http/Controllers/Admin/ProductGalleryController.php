<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ProductGallery;
use Illuminate\Http\Request;

/**
 * Controller para gerenciar as galerias de produtos da home
 *
 * Permite criar até 4 galerias configuráveis com filtros dinâmicos,
 * customização de layout e cores.
 */
class ProductGalleryController extends Controller
{
    /**
     * Lista todas as galerias de produtos
     */
    public function index()
    {
        $galleries = ProductGallery::ordered()->get();

        return view('admin.product-galleries.index', compact('galleries'));
    }

    /**
     * Exibe formulário para criar nova galeria
     */
    public function create()
    {
        // Verifica se já existem 4 galerias (limite máximo)
        $galleryCount = ProductGallery::count();

        if ($galleryCount >= 4) {
            return redirect()->route('admin.product-galleries.index')
                ->with('error', 'Limite máximo de 4 galerias atingido. Edite ou desative uma galeria existente.');
        }

        // Busca próxima ordem disponível (1-4)
        $usedOrders = ProductGallery::pluck('order')->toArray();
        $nextOrder = 1;
        for ($i = 1; $i <= 4; $i++) {
            if (!in_array($i, $usedOrders)) {
                $nextOrder = $i;
                break;
            }
        }

        // Lista de categorias para o filtro
        $categories = Category::orderBy('name')->get();

        return view('admin.product-galleries.create', compact('categories', 'nextOrder'));
    }

    /**
     * Armazena nova galeria no banco de dados
     */
    public function store(Request $request)
    {
        // Verifica limite de 4 galerias
        if (ProductGallery::count() >= 4) {
            return redirect()->route('admin.product-galleries.index')
                ->with('error', 'Limite máximo de 4 galerias atingido.');
        }

        $validated = $request->validate([
            'order' => 'required|integer|min:1|max:4|unique:product_galleries,order',
            'title' => 'required|string|max:100',
            'subtitle' => 'nullable|string|max:255',
            'mobile_columns' => 'required|integer|min:1|max:4',
            'desktop_columns' => 'required|integer|min:1|max:6',
            'products_limit' => 'required|integer|min:1|max:50',
            'filter_type' => 'required|in:category,best_sellers,on_sale,low_stock',
            'filter_value' => 'nullable|exists:categories,id',
            'background_color' => 'nullable|string|max:20',
            'background_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'title_color' => 'required|string|max:20',
            'subtitle_color' => 'required|string|max:20',
            'show_view_all_button' => 'boolean',
            'view_all_url' => 'nullable|string|max:255',
            'button_bg_color' => 'required|string|max:20',
            'button_hover_color' => 'required|string|max:20',
            'button_text_color' => 'required|string|max:20',
            'active' => 'boolean',
        ]);

        // Upload da imagem de fundo se enviada
        if ($request->hasFile('background_image')) {
            $backgroundPath = $request->file('background_image')->store('product-galleries', 'public');
            $validated['background_image_path'] = $backgroundPath;
        }

        ProductGallery::create($validated);

        return redirect()->route('admin.product-galleries.index')
            ->with('success', 'Galeria criada com sucesso!');
    }

    /**
     * Exibe formulário de edição da galeria
     */
    public function edit(ProductGallery $productGallery)
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.product-galleries.edit', compact('productGallery', 'categories'));
    }

    /**
     * Atualiza galeria no banco de dados
     */
    public function update(Request $request, ProductGallery $productGallery)
    {
        $validated = $request->validate([
            'order' => 'required|integer|min:1|max:4|unique:product_galleries,order,' . $productGallery->id,
            'title' => 'required|string|max:100',
            'subtitle' => 'nullable|string|max:255',
            'mobile_columns' => 'required|integer|min:1|max:4',
            'desktop_columns' => 'required|integer|min:1|max:6',
            'products_limit' => 'required|integer|min:1|max:50',
            'filter_type' => 'required|in:category,best_sellers,on_sale,low_stock',
            'filter_value' => 'nullable|exists:categories,id',
            'background_color' => 'nullable|string|max:20',
            'background_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'title_color' => 'required|string|max:20',
            'subtitle_color' => 'required|string|max:20',
            'show_view_all_button' => 'boolean',
            'view_all_url' => 'nullable|string|max:255',
            'button_bg_color' => 'required|string|max:20',
            'button_hover_color' => 'required|string|max:20',
            'button_text_color' => 'required|string|max:20',
            'active' => 'boolean',
        ]);

        // Upload da nova imagem de fundo se enviada
        if ($request->hasFile('background_image')) {
            // Remove imagem antiga
            if ($productGallery->background_image_path) {
                \Storage::disk('public')->delete($productGallery->background_image_path);
            }

            $backgroundPath = $request->file('background_image')->store('product-galleries', 'public');
            $validated['background_image_path'] = $backgroundPath;
        }

        $productGallery->update($validated);

        return redirect()->route('admin.product-galleries.index')
            ->with('success', 'Galeria atualizada com sucesso!');
    }

    /**
     * Remove galeria do banco de dados
     */
    public function destroy(ProductGallery $productGallery)
    {
        // Remove imagem de fundo se existir
        if ($productGallery->background_image_path) {
            \Storage::disk('public')->delete($productGallery->background_image_path);
        }

        $productGallery->delete();

        return redirect()->route('admin.product-galleries.index')
            ->with('success', 'Galeria removida com sucesso!');
    }
}
