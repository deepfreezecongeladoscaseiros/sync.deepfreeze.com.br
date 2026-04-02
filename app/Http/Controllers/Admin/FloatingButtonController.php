<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FloatingButtonConfig;
use Illuminate\Http\Request;

/**
 * Controller: Configuração dos Ícones Flutuantes (Admin)
 *
 * Formulário singleton edit/update (padrão CookieConsentController).
 * Permite configurar WhatsApp, Instagram e posição dos ícones.
 */
class FloatingButtonController extends Controller
{
    public function edit()
    {
        $config = FloatingButtonConfig::getConfig();
        return view('admin.floating-buttons.edit', compact('config'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'position'         => 'required|in:left,right',
            'whatsapp_number'  => 'nullable|string|max:20',
            'whatsapp_message' => 'nullable|string|max:255',
            'instagram_url'    => 'nullable|url|max:255',
        ]);

        $config = FloatingButtonConfig::getConfig();
        $config->update($data);

        return redirect()->route('admin.floating-buttons.edit')
            ->with('success', 'Configurações dos ícones flutuantes atualizadas com sucesso!');
    }
}
