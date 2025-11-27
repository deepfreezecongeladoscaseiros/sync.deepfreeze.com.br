<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeSection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Controller para gerenciar a ordenação das seções da página inicial
 *
 * Permite reordenar e ativar/desativar as seções da home page.
 * As seções são fixas (não podem ser criadas ou deletadas),
 * apenas reordenadas e ativadas/desativadas.
 */
class HomeSectionController extends Controller
{
    /**
     * Lista todas as seções da home para gerenciamento
     *
     * Exibe uma interface drag-and-drop para reordenar as seções
     * e toggles para ativar/desativar cada uma.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Busca todas as seções ordenadas pela ordem atual
        $sections = HomeSection::ordered()->get();

        return view('admin.home-sections.index', compact('sections'));
    }

    /**
     * Atualiza a ordem das seções via AJAX (drag-and-drop)
     *
     * Recebe um array de IDs na nova ordem e atualiza o campo 'order'
     * de cada seção correspondente.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateOrder(Request $request): JsonResponse
    {
        // Valida que recebemos um array de IDs
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:home_sections,id',
        ]);

        // Atualiza a ordem de cada seção
        // O índice do array representa a nova posição (0, 1, 2, ...)
        foreach ($validated['order'] as $position => $sectionId) {
            HomeSection::where('id', $sectionId)->update(['order' => $position]);
        }

        // Limpa o cache das seções da home
        clear_home_sections_cache();

        return response()->json([
            'success' => true,
            'message' => 'Ordem atualizada com sucesso!',
        ]);
    }

    /**
     * Toggle de ativação/desativação de uma seção via AJAX
     *
     * Inverte o estado is_active da seção e retorna o novo estado.
     *
     * @param HomeSection $homeSection
     * @return JsonResponse
     */
    public function toggleActive(HomeSection $homeSection): JsonResponse
    {
        // Inverte o estado atual
        $homeSection->is_active = !$homeSection->is_active;
        $homeSection->save();

        // Limpa o cache das seções da home
        clear_home_sections_cache();

        return response()->json([
            'success' => true,
            'is_active' => $homeSection->is_active,
            'message' => $homeSection->is_active
                ? "Seção '{$homeSection->name}' ativada!"
                : "Seção '{$homeSection->name}' desativada!",
        ]);
    }

    /**
     * Exibe preview da home com a ordem atual das seções
     *
     * Útil para visualizar como ficará a home antes de publicar.
     *
     * @return \Illuminate\View\View
     */
    public function preview()
    {
        $sections = HomeSection::active()->ordered()->get();

        return view('admin.home-sections.preview', compact('sections'));
    }
}
