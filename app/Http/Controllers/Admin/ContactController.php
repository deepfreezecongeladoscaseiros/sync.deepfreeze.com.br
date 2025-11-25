<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactSetting;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Controller para gerenciamento da página de Contato no Admin.
 *
 * Gerencia configurações da página e visualização de mensagens recebidas.
 */
class ContactController extends Controller
{
    /**
     * Exibe formulário de configurações da página de contato.
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        // Obtém configurações (cria se não existir)
        $settings = ContactSetting::getSettings();

        // Conta mensagens não lidas para exibir badge
        $unreadCount = ContactMessage::unreadCount();

        return view('admin.contact.edit', compact('settings', 'unreadCount'));
    }

    /**
     * Atualiza configurações da página de contato.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        // Validação dos dados
        $data = $request->validate([
            'page_title' => 'required|string|max:255',
            'intro_text' => 'nullable|string',
            'whatsapp' => 'nullable|string|max:20',
            'whatsapp_display' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'business_hours' => 'nullable|string',
            'form_recipient_email' => 'nullable|email|max:255',
            'form_subject' => 'nullable|string|max:255',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'active' => 'boolean',
        ]);

        // Obtém configurações existentes
        $settings = ContactSetting::getSettings();

        // Processa upload do banner se enviado
        if ($request->hasFile('banner_image')) {
            // Remove banner antigo se existir
            if ($settings->banner_image) {
                Storage::delete($settings->banner_image);
            }

            // Salva novo banner
            $data['banner_image'] = $request->file('banner_image')->store('contact', 'public');
        }

        // Trata checkbox de active
        $data['active'] = $request->has('active');

        // Atualiza configurações
        $settings->update($data);

        return redirect()->route('admin.contact.edit')
            ->with('success', 'Configurações de contato atualizadas com sucesso!');
    }

    /**
     * Lista mensagens de contato recebidas.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function messages(Request $request)
    {
        // Query base
        $query = ContactMessage::latest();

        // Filtro por status de leitura
        if ($request->has('filter')) {
            if ($request->filter === 'unread') {
                $query->unread();
            } elseif ($request->filter === 'read') {
                $query->read();
            }
        }

        // Paginação
        $messages = $query->paginate(20);

        // Contadores para tabs
        $totalCount = ContactMessage::count();
        $unreadCount = ContactMessage::unreadCount();
        $readCount = $totalCount - $unreadCount;

        return view('admin.contact.messages', compact(
            'messages',
            'totalCount',
            'unreadCount',
            'readCount'
        ));
    }

    /**
     * Exibe detalhes de uma mensagem.
     *
     * @param ContactMessage $message
     * @return \Illuminate\View\View
     */
    public function showMessage(ContactMessage $message)
    {
        // Marca como lida ao visualizar
        $message->markAsRead();

        return view('admin.contact.show-message', compact('message'));
    }

    /**
     * Exclui uma mensagem.
     *
     * @param ContactMessage $message
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyMessage(ContactMessage $message)
    {
        $message->delete();

        return redirect()->route('admin.contact.messages')
            ->with('success', 'Mensagem excluída com sucesso!');
    }

    /**
     * Marca mensagem como lida/não lida (toggle).
     *
     * @param ContactMessage $message
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleRead(ContactMessage $message)
    {
        if ($message->read) {
            $message->markAsUnread();
            $status = 'não lida';
        } else {
            $message->markAsRead();
            $status = 'lida';
        }

        return redirect()->back()
            ->with('success', "Mensagem marcada como {$status}!");
    }

    /**
     * Marca todas as mensagens como lidas.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAllRead()
    {
        ContactMessage::unread()->update([
            'read' => true,
            'read_at' => now(),
        ]);

        return redirect()->route('admin.contact.messages')
            ->with('success', 'Todas as mensagens foram marcadas como lidas!');
    }

    /**
     * Exclui mensagens antigas (mais de 90 dias).
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clearOld()
    {
        $count = ContactMessage::where('created_at', '<', now()->subDays(90))->count();

        ContactMessage::where('created_at', '<', now()->subDays(90))->delete();

        return redirect()->route('admin.contact.messages')
            ->with('success', "{$count} mensagens antigas foram excluídas!");
    }
}
