<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeatureBlock;
use Illuminate\Http\Request;

/**
 * Controller para gerenciar os 4 blocos de features/informações
 *
 * São sempre 4 blocos fixos exibidos abaixo do banner hero.
 * Apenas edição é permitida, sem criar ou deletar.
 */
class FeatureBlockController extends Controller
{
    /**
     * Lista os 4 blocos de features
     */
    public function index()
    {
        $blocks = FeatureBlock::ordered()->get();

        return view('admin.feature-blocks.index', compact('blocks'));
    }

    /**
     * Exibe formulário de edição de um bloco
     */
    public function edit(FeatureBlock $featureBlock)
    {
        return view('admin.feature-blocks.edit', compact('featureBlock'));
    }

    /**
     * Atualiza um bloco de feature
     */
    public function update(Request $request, FeatureBlock $featureBlock)
    {
        $validated = $request->validate([
            'icon' => 'nullable|image|mimes:svg,png,jpg,jpeg|max:2048',
            'title' => 'required|string|max:100',
            'description' => 'required|string|max:255',
            'bg_color' => 'required|string|max:20',
            'text_color' => 'required|string|max:20',
            'icon_color' => 'required|string|max:20',
            'active' => 'boolean',
        ]);

        // Atualiza ícone se enviado
        if ($request->hasFile('icon')) {
            // Remove ícone antigo
            \Storage::disk('public')->delete($featureBlock->icon_path);

            // Upload do novo ícone
            $iconPath = $request->file('icon')->store('feature-blocks', 'public');
            $featureBlock->icon_path = $iconPath;
        }

        $featureBlock->update([
            'title' => $request->title,
            'description' => $request->description,
            'bg_color' => $request->bg_color,
            'text_color' => $request->text_color,
            'icon_color' => $request->icon_color,
            'active' => $request->boolean('active', true),
        ]);

        return redirect()->route('admin.feature-blocks.index')->with('success', 'Bloco atualizado com sucesso!');
    }
}
