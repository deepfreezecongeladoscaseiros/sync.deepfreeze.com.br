<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CookieConsent;
use Illuminate\Http\Request;

class CookieConsentController extends Controller
{
    /**
     * Exibe formulário de edição do cookie consent
     * Como só existe 1 registro, vai direto para edição
     */
    public function edit()
    {
        $config = CookieConsent::getConfig();
        return view('admin.cookie-consent.edit', compact('config'));
    }

    /**
     * Atualiza configurações do cookie consent
     */
    public function update(Request $request)
    {
        // Validação
        $data = $request->validate([
            'active' => 'boolean',
            'message_text' => 'required',
            'button_label' => 'required|max:50',
            'button_bg_color' => 'required|max:20',
            'button_text_color' => 'required|max:20',
            'button_hover_bg_color' => 'required|max:20',
        ]);

        // Define active como false se não foi enviado (checkbox desmarcado)
        $data['active'] = $request->has('active') ? true : false;

        // Atualiza configuração
        $config = CookieConsent::getConfig();
        $config->update($data);

        return redirect()->route('admin.cookie-consent.edit')
            ->with('success', 'Configurações do Cookie Consent atualizadas com sucesso!');
    }
}
