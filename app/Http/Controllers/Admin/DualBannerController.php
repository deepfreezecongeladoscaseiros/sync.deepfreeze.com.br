<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DualBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Controller para gerenciar banners duplos no admin
 *
 * Cada registro gerencia UM PAR de banners (esquerdo e direito)
 * exibidos lado a lado na home.
 */
class DualBannerController extends Controller
{
    /**
     * Lista todos os pares de banners duplos
     */
    public function index()
    {
        $dualBanners = DualBanner::ordered()->get();

        return view('admin.dual-banners.index', compact('dualBanners'));
    }

    /**
     * Exibe formulário de criação
     */
    public function create()
    {
        // Busca o próximo número de ordem disponível
        $nextOrder = DualBanner::max('order') + 1;

        return view('admin.dual-banners.create', compact('nextOrder'));
    }

    /**
     * Armazena novo par de banners duplos
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order' => 'required|integer|min:1|unique:dual_banners,order',

            // Banner Esquerdo
            'left_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120', // 5MB
            'left_link' => 'nullable|url|max:500',
            'left_alt_text' => 'nullable|string|max:255',
            'left_start_date' => 'nullable|date',
            'left_end_date' => 'nullable|date|after_or_equal:left_start_date',

            // Banner Direito
            'right_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120', // 5MB
            'right_link' => 'nullable|url|max:500',
            'right_alt_text' => 'nullable|string|max:255',
            'right_start_date' => 'nullable|date',
            'right_end_date' => 'nullable|date|after_or_equal:right_start_date',

            'active' => 'boolean',
        ], [
            'left_image.required' => 'A imagem do banner esquerdo é obrigatória',
            'left_image.image' => 'O arquivo do banner esquerdo deve ser uma imagem',
            'left_image.max' => 'A imagem do banner esquerdo não pode ser maior que 5MB',
            'left_end_date.after_or_equal' => 'A data de fim do banner esquerdo deve ser posterior ou igual à data de início',

            'right_image.required' => 'A imagem do banner direito é obrigatória',
            'right_image.image' => 'O arquivo do banner direito deve ser uma imagem',
            'right_image.max' => 'A imagem do banner direito não pode ser maior que 5MB',
            'right_end_date.after_or_equal' => 'A data de fim do banner direito deve ser posterior ou igual à data de início',
        ]);

        // Upload da imagem esquerda
        $leftImagePath = $request->file('left_image')->store('dual-banners/left', 'public');
        $validated['left_image_path'] = $leftImagePath;

        // Upload da imagem direita
        $rightImagePath = $request->file('right_image')->store('dual-banners/right', 'public');
        $validated['right_image_path'] = $rightImagePath;

        // Remove campos temporários do validated
        unset($validated['left_image'], $validated['right_image']);

        DualBanner::create($validated);

        return redirect()->route('admin.dual-banners.index')
            ->with('success', 'Par de banners criado com sucesso!');
    }

    /**
     * Exibe formulário de edição
     */
    public function edit(DualBanner $dualBanner)
    {
        return view('admin.dual-banners.edit', compact('dualBanner'));
    }

    /**
     * Atualiza um par de banners existente
     */
    public function update(Request $request, DualBanner $dualBanner)
    {
        $validated = $request->validate([
            'order' => 'required|integer|min:1|unique:dual_banners,order,' . $dualBanner->id,

            // Banner Esquerdo
            'left_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'left_link' => 'nullable|url|max:500',
            'left_alt_text' => 'nullable|string|max:255',
            'left_start_date' => 'nullable|date',
            'left_end_date' => 'nullable|date|after_or_equal:left_start_date',

            // Banner Direito
            'right_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'right_link' => 'nullable|url|max:500',
            'right_alt_text' => 'nullable|string|max:255',
            'right_start_date' => 'nullable|date',
            'right_end_date' => 'nullable|date|after_or_equal:right_start_date',

            'active' => 'boolean',
        ], [
            'left_end_date.after_or_equal' => 'A data de fim do banner esquerdo deve ser posterior ou igual à data de início',
            'right_end_date.after_or_equal' => 'A data de fim do banner direito deve ser posterior ou igual à data de início',
        ]);

        // Atualiza imagem esquerda se enviada
        if ($request->hasFile('left_image')) {
            // Remove imagem antiga
            Storage::disk('public')->delete($dualBanner->left_image_path);

            // Upload nova imagem
            $leftImagePath = $request->file('left_image')->store('dual-banners/left', 'public');
            $validated['left_image_path'] = $leftImagePath;
        }

        // Atualiza imagem direita se enviada
        if ($request->hasFile('right_image')) {
            // Remove imagem antiga
            Storage::disk('public')->delete($dualBanner->right_image_path);

            // Upload nova imagem
            $rightImagePath = $request->file('right_image')->store('dual-banners/right', 'public');
            $validated['right_image_path'] = $rightImagePath;
        }

        // Remove campos temporários do validated
        unset($validated['left_image'], $validated['right_image']);

        $dualBanner->update($validated);

        return redirect()->route('admin.dual-banners.index')
            ->with('success', 'Par de banners atualizado com sucesso!');
    }

    /**
     * Remove um par de banners
     */
    public function destroy(DualBanner $dualBanner)
    {
        // Remove as imagens do storage
        Storage::disk('public')->delete([
            $dualBanner->left_image_path,
            $dualBanner->right_image_path
        ]);

        $dualBanner->delete();

        return redirect()->route('admin.dual-banners.index')
            ->with('success', 'Par de banners excluído com sucesso!');
    }
}
