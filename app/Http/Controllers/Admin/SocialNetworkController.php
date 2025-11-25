<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialNetwork;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Controller para gerenciar Redes Sociais no painel Admin
 *
 * CRUD completo para gerenciar as redes sociais exibidas no topo e rodapé do site.
 * Permite upload de ícones, definição de URLs e ordem de exibição.
 */
class SocialNetworkController extends Controller
{
    /**
     * Lista todas as redes sociais
     *
     * Exibe lista ordenada de todas as redes sociais cadastradas
     */
    public function index()
    {
        // Busca todas as redes sociais ordenadas
        $socialNetworks = SocialNetwork::ordered()->get();

        return view('admin.social-networks.index', compact('socialNetworks'));
    }

    /**
     * Formulário para criar nova rede social
     */
    public function create()
    {
        return view('admin.social-networks.create');
    }

    /**
     * Salva nova rede social no banco
     *
     * Valida dados, faz upload do ícone e cria registro
     */
    public function store(Request $request)
    {
        // Validação dos dados
        $data = $request->validate([
            'name' => 'required|string|max:50',
            'icon' => 'required|image|mimes:png,jpg,jpeg,svg|max:2048', // Máximo 2MB
            'url' => 'required|url|max:255',
            'order' => 'required|integer|min:1|unique:social_networks,order',
            'active' => 'boolean',
        ]);

        // Upload do ícone para storage/app/public/social-icons
        $data['icon_path'] = $request->file('icon')->store('social-icons', 'public');

        // Converte checkbox 'active' para boolean
        $data['active'] = $request->has('active') ? true : false;

        // Remove campo 'icon' pois já foi processado
        unset($data['icon']);

        // Cria a rede social
        SocialNetwork::create($data);

        return redirect()
            ->route('admin.social-networks.index')
            ->with('success', 'Rede social criada com sucesso!');
    }

    /**
     * Formulário para editar rede social existente
     */
    public function edit(SocialNetwork $socialNetwork)
    {
        return view('admin.social-networks.edit', compact('socialNetwork'));
    }

    /**
     * Atualiza rede social existente
     *
     * Valida dados, atualiza ícone se novo arquivo enviado
     */
    public function update(Request $request, SocialNetwork $socialNetwork)
    {
        // Validação dos dados
        $data = $request->validate([
            'name' => 'required|string|max:50',
            'icon' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048', // Ícone opcional na edição
            'url' => 'required|url|max:255',
            'order' => 'required|integer|min:1|unique:social_networks,order,' . $socialNetwork->id,
            'active' => 'boolean',
        ]);

        // Se novo ícone foi enviado, substitui o antigo
        if ($request->hasFile('icon')) {
            // Remove ícone antigo do storage
            if (Storage::disk('public')->exists($socialNetwork->icon_path)) {
                Storage::disk('public')->delete($socialNetwork->icon_path);
            }

            // Upload do novo ícone
            $data['icon_path'] = $request->file('icon')->store('social-icons', 'public');
        }

        // Converte checkbox 'active' para boolean
        $data['active'] = $request->has('active') ? true : false;

        // Remove campo 'icon' pois já foi processado
        unset($data['icon']);

        // Atualiza a rede social
        $socialNetwork->update($data);

        return redirect()
            ->route('admin.social-networks.index')
            ->with('success', 'Rede social atualizada com sucesso!');
    }

    /**
     * Remove rede social do banco
     *
     * O método boot() do Model já cuida de remover o ícone do storage
     */
    public function destroy(SocialNetwork $socialNetwork)
    {
        // Deleta a rede social (o boot() do Model remove o ícone automaticamente)
        $socialNetwork->delete();

        return redirect()
            ->route('admin.social-networks.index')
            ->with('success', 'Rede social removida com sucesso!');
    }
}
