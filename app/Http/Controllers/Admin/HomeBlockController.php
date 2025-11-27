<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeBlock;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Controller para gerenciar os blocos flexíveis da home page
 *
 * Permite adicionar, remover, reordenar e ativar/desativar blocos.
 * Cada bloco pode ser de um tipo diferente (galeria, banner, etc.)
 * e pode referenciar um item específico.
 */
class HomeBlockController extends Controller
{
    /**
     * Lista todos os blocos da home para gerenciamento
     *
     * Exibe interface drag-and-drop para reordenar e gerenciar blocos.
     */
    public function index()
    {
        // Busca todos os blocos ordenados
        $blocks = HomeBlock::ordered()->get();

        // Tipos disponíveis para adicionar novos blocos
        $blockTypes = HomeBlock::getAvailableTypes();

        return view('admin.home-blocks.index', compact('blocks', 'blockTypes'));
    }

    /**
     * Retorna os itens disponíveis para um tipo específico (AJAX)
     *
     * Usado para popular o select de itens quando o usuário
     * seleciona um tipo que requer referência.
     */
    public function getItems(string $type): JsonResponse
    {
        $config = HomeBlock::BLOCK_TYPES[$type] ?? null;

        if (!$config) {
            return response()->json(['error' => 'Tipo inválido'], 400);
        }

        // Se não requer referência, retorna vazio
        if (!$config['requires_reference']) {
            return response()->json([
                'requires_reference' => false,
                'items' => [],
            ]);
        }

        // Busca itens disponíveis
        $items = HomeBlock::getAvailableItems($type);

        // Formata para o select
        $formattedItems = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title ?? $item->name ?? "Item #{$item->id}",
                'active' => $item->active ?? true,
            ];
        });

        return response()->json([
            'requires_reference' => true,
            'items' => $formattedItems,
        ]);
    }

    /**
     * Adiciona um novo bloco à home
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|in:' . implode(',', array_keys(HomeBlock::BLOCK_TYPES)),
            'reference_id' => 'nullable|integer',
            'custom_title' => 'nullable|string|max:255',
        ]);

        // Verifica se o tipo requer referência
        $config = HomeBlock::BLOCK_TYPES[$validated['type']];

        if ($config['requires_reference'] && empty($validated['reference_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Este tipo de bloco requer a seleção de um item.',
            ], 422);
        }

        // Calcula a próxima ordem (última posição)
        $maxOrder = HomeBlock::max('order') ?? -1;

        // Cria o bloco
        $block = HomeBlock::create([
            'type' => $validated['type'],
            'reference_id' => $validated['reference_id'] ?? null,
            'custom_title' => $validated['custom_title'] ?? null,
            'order' => $maxOrder + 1,
            'is_active' => true,
        ]);

        // Limpa o cache
        clear_home_blocks_cache();

        return response()->json([
            'success' => true,
            'message' => 'Bloco adicionado com sucesso!',
            'block' => [
                'id' => $block->id,
                'type' => $block->type,
                'type_label' => $block->type_label,
                'type_icon' => $block->type_icon,
                'display_title' => $block->display_title,
                'is_active' => $block->is_active,
                'admin_url' => $block->getAdminUrl(),
            ],
        ]);
    }

    /**
     * Remove um bloco da home
     */
    public function destroy(HomeBlock $homeBlock): JsonResponse
    {
        $homeBlock->delete();

        // Reordena os blocos restantes
        $this->reorderBlocks();

        // Limpa o cache
        clear_home_blocks_cache();

        return response()->json([
            'success' => true,
            'message' => 'Bloco removido com sucesso!',
        ]);
    }

    /**
     * Atualiza a ordem dos blocos via AJAX (drag-and-drop)
     */
    public function updateOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:home_blocks,id',
        ]);

        // Atualiza a ordem de cada bloco
        foreach ($validated['order'] as $position => $blockId) {
            HomeBlock::where('id', $blockId)->update(['order' => $position]);
        }

        // Limpa o cache
        clear_home_blocks_cache();

        return response()->json([
            'success' => true,
            'message' => 'Ordem atualizada com sucesso!',
        ]);
    }

    /**
     * Toggle de ativação/desativação de um bloco via AJAX
     */
    public function toggleActive(HomeBlock $homeBlock): JsonResponse
    {
        $homeBlock->is_active = !$homeBlock->is_active;
        $homeBlock->save();

        // Limpa o cache
        clear_home_blocks_cache();

        return response()->json([
            'success' => true,
            'is_active' => $homeBlock->is_active,
            'message' => $homeBlock->is_active
                ? "Bloco '{$homeBlock->display_title}' ativado!"
                : "Bloco '{$homeBlock->display_title}' desativado!",
        ]);
    }

    /**
     * Atualiza o título customizado de um bloco
     */
    public function updateTitle(Request $request, HomeBlock $homeBlock): JsonResponse
    {
        $validated = $request->validate([
            'custom_title' => 'nullable|string|max:255',
        ]);

        $homeBlock->custom_title = $validated['custom_title'] ?: null;
        $homeBlock->save();

        // Limpa o cache
        clear_home_blocks_cache();

        return response()->json([
            'success' => true,
            'display_title' => $homeBlock->display_title,
            'message' => 'Título atualizado com sucesso!',
        ]);
    }

    /**
     * Reordena os blocos para garantir sequência contínua (0, 1, 2, 3...)
     */
    private function reorderBlocks(): void
    {
        $blocks = HomeBlock::orderBy('order')->get();

        foreach ($blocks as $index => $block) {
            if ($block->order !== $index) {
                $block->update(['order' => $index]);
            }
        }
    }
}
