<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Category;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Controller Admin para Gerenciamento de Menus
 *
 * Gerencia menus e itens de menu com suporte a:
 * - CRUD de menus (principal, rodapé, mobile)
 * - CRUD de itens de menu (categorias, páginas, links)
 * - Drag-and-drop para reordenação
 * - Mega menus com imagem/banner
 * - Hierarquia ilimitada de submenus
 */
class MenuController extends Controller
{
    /**
     * Lista todos os menus
     */
    public function index()
    {
        $menus = Menu::withCount('items')->orderBy('location')->get();

        return view('admin.menus.index', compact('menus'));
    }

    /**
     * Formulário de criação de menu
     */
    public function create()
    {
        $locations = Menu::LOCATIONS;

        return view('admin.menus.create', compact('locations'));
    }

    /**
     * Salva novo menu
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:50|unique:menus,slug',
            'location' => 'required|in:header,footer,mobile_sidebar,custom',
            'description' => 'nullable|string|max:255',
            'active' => 'boolean',
        ], [
            'name.required' => 'O nome do menu é obrigatório.',
            'slug.unique' => 'Este identificador já está em uso.',
        ]);

        // Gera slug se não fornecido
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['active'] = $request->has('active');

        $menu = Menu::create($validated);

        return redirect()
            ->route('admin.menus.items', $menu)
            ->with('success', 'Menu criado com sucesso! Agora adicione os itens.');
    }

    /**
     * Formulário de edição de menu
     */
    public function edit(Menu $menu)
    {
        $locations = Menu::LOCATIONS;

        return view('admin.menus.edit', compact('menu', 'locations'));
    }

    /**
     * Atualiza menu
     */
    public function update(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:50|unique:menus,slug,' . $menu->id,
            'location' => 'required|in:header,footer,mobile_sidebar,custom',
            'description' => 'nullable|string|max:255',
            'active' => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['active'] = $request->has('active');

        $menu->update($validated);

        return redirect()
            ->route('admin.menus.index')
            ->with('success', 'Menu atualizado com sucesso!');
    }

    /**
     * Remove menu e todos os itens
     */
    public function destroy(Menu $menu)
    {
        $menu->delete(); // Cascade deleta itens

        Menu::clearAllCache();

        return redirect()
            ->route('admin.menus.index')
            ->with('success', 'Menu excluído com sucesso!');
    }

    // =========================================================================
    // GERENCIAMENTO DE ITENS DO MENU
    // =========================================================================

    /**
     * Exibe tela de gerenciamento de itens (drag-and-drop)
     */
    public function items(Menu $menu)
    {
        // Carrega itens em árvore hierárquica
        $items = $menu->rootItems()
            ->with('activeChildrenRecursive')
            ->orderBy('position')
            ->get();

        // Dados para selects no modal de adição
        $categories = Category::orderBy('name')->get();
        $pages = Page::active()->orderBy('title')->get();
        $itemTypes = MenuItem::TYPES;
        $showOnOptions = MenuItem::SHOW_ON_OPTIONS;
        $megaMenuPositions = MenuItem::MEGA_MENU_POSITIONS;

        return view('admin.menus.items', compact(
            'menu',
            'items',
            'categories',
            'pages',
            'itemTypes',
            'showOnOptions',
            'megaMenuPositions'
        ));
    }

    /**
     * Adiciona item ao menu (via AJAX)
     *
     * Processa os campos category_id e page_id separadamente
     * para evitar conflitos de nome no FormData
     */
    public function addItem(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:menu_items,id',
            'type' => 'required|in:home,category,page,url,contact,submenu_title',
            'title' => 'required|string|max:100',
            'category_id' => 'nullable|integer|exists:categories,id',
            'page_id' => 'nullable|integer|exists:pages,id',
            'url' => 'nullable|string|max:500',
            'target' => 'in:_self,_blank',
            'icon_class' => 'nullable|string|max:100',
            'icon_image' => 'nullable|image|mimes:png,jpg,jpeg,gif,svg,webp|max:1024',
            'css_class' => 'nullable|string|max:100',
            'show_on' => 'in:all,desktop,mobile',
            'is_mega_menu' => 'boolean',
            'mega_menu_image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'mega_menu_image_url' => 'nullable|string|max:500',
            'mega_menu_image_alt' => 'nullable|string|max:150',
            'mega_menu_image_position' => 'in:right,left,bottom',
            'mega_menu_columns' => 'integer|min:1|max:4',
        ]);

        // Processa upload de ícone
        if ($request->hasFile('icon_image')) {
            $validated['icon_image'] = $request->file('icon_image')
                ->store('menus/icons', 'public');
        }

        // Processa upload de imagem mega menu
        if ($request->hasFile('mega_menu_image')) {
            $validated['mega_menu_image'] = $request->file('mega_menu_image')
                ->store('menus/mega', 'public');
        }

        // Define linkable baseado no tipo
        // Usa category_id ou page_id conforme o tipo selecionado
        $validated['linkable_type'] = null;
        $validated['linkable_id'] = null;

        if ($validated['type'] === 'category' && !empty($request->category_id)) {
            $validated['linkable_type'] = Category::class;
            $validated['linkable_id'] = $request->category_id;
        } elseif ($validated['type'] === 'page' && !empty($request->page_id)) {
            $validated['linkable_type'] = Page::class;
            $validated['linkable_id'] = $request->page_id;
        }

        // Remove campos temporários que não existem na tabela
        unset($validated['category_id'], $validated['page_id']);

        // Calcula posição (último item do nível)
        $position = MenuItem::where('menu_id', $menu->id)
            ->where('parent_id', $validated['parent_id'] ?? null)
            ->max('position') ?? -1;
        $validated['position'] = $position + 1;

        // Cria o item
        $validated['menu_id'] = $menu->id;
        $validated['is_mega_menu'] = $request->has('is_mega_menu');
        $validated['active'] = true;

        $item = MenuItem::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Item adicionado com sucesso!',
                'item' => $item->load('linkable'),
            ]);
        }

        return redirect()
            ->route('admin.menus.items', $menu)
            ->with('success', 'Item adicionado com sucesso!');
    }

    /**
     * Atualiza item do menu (via AJAX)
     *
     * Processa os campos category_id e page_id separadamente
     * para evitar conflitos de nome no FormData
     */
    public function updateItem(Request $request, Menu $menu, MenuItem $item)
    {
        // Verifica se item pertence ao menu
        if ($item->menu_id !== $menu->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'type' => 'required|in:home,category,page,url,contact,submenu_title',
            'category_id' => 'nullable|integer|exists:categories,id',
            'page_id' => 'nullable|integer|exists:pages,id',
            'url' => 'nullable|string|max:500',
            'target' => 'in:_self,_blank',
            'icon_class' => 'nullable|string|max:100',
            'icon_image' => 'nullable|image|mimes:png,jpg,jpeg,gif,svg,webp|max:1024',
            'css_class' => 'nullable|string|max:100',
            'show_on' => 'in:all,desktop,mobile',
            'is_mega_menu' => 'boolean',
            'mega_menu_image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'mega_menu_image_url' => 'nullable|string|max:500',
            'mega_menu_image_alt' => 'nullable|string|max:150',
            'mega_menu_image_position' => 'in:right,left,bottom',
            'mega_menu_columns' => 'integer|min:1|max:4',
            'active' => 'boolean',
        ]);

        // Processa upload de ícone
        if ($request->hasFile('icon_image')) {
            // Remove ícone antigo
            if ($item->icon_image) {
                Storage::disk('public')->delete($item->icon_image);
            }
            $validated['icon_image'] = $request->file('icon_image')
                ->store('menus/icons', 'public');
        }

        // Processa upload de imagem mega menu
        if ($request->hasFile('mega_menu_image')) {
            // Remove imagem antiga
            if ($item->mega_menu_image) {
                Storage::disk('public')->delete($item->mega_menu_image);
            }
            $validated['mega_menu_image'] = $request->file('mega_menu_image')
                ->store('menus/mega', 'public');
        }

        // Atualiza linkable baseado no tipo
        // Usa category_id ou page_id conforme o tipo selecionado
        if ($validated['type'] === 'category' && !empty($request->category_id)) {
            $validated['linkable_type'] = Category::class;
            $validated['linkable_id'] = $request->category_id;
        } elseif ($validated['type'] === 'page' && !empty($request->page_id)) {
            $validated['linkable_type'] = Page::class;
            $validated['linkable_id'] = $request->page_id;
        } else {
            $validated['linkable_type'] = null;
            $validated['linkable_id'] = null;
        }

        // Remove campos temporários que não existem na tabela
        unset($validated['category_id'], $validated['page_id']);

        $validated['is_mega_menu'] = $request->has('is_mega_menu');
        $validated['active'] = $request->has('active');

        $item->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Item atualizado com sucesso!',
                'item' => $item->fresh()->load('linkable'),
            ]);
        }

        return redirect()
            ->route('admin.menus.items', $menu)
            ->with('success', 'Item atualizado com sucesso!');
    }

    /**
     * Remove item do menu (via AJAX)
     */
    public function destroyItem(Menu $menu, MenuItem $item)
    {
        // Verifica se item pertence ao menu
        if ($item->menu_id !== $menu->id) {
            abort(404);
        }

        // Remove imagens
        if ($item->icon_image) {
            Storage::disk('public')->delete($item->icon_image);
        }
        if ($item->mega_menu_image) {
            Storage::disk('public')->delete($item->mega_menu_image);
        }

        $item->delete(); // Cascade deleta filhos

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Item removido com sucesso!',
            ]);
        }

        return redirect()
            ->route('admin.menus.items', $menu)
            ->with('success', 'Item removido com sucesso!');
    }

    /**
     * Reordena itens via drag-and-drop (AJAX)
     *
     * Recebe array de itens com nova estrutura hierárquica
     */
    public function reorderItems(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:menu_items,id',
            'items.*.parent_id' => 'nullable|exists:menu_items,id',
            'items.*.position' => 'required|integer|min:0',
        ]);

        foreach ($validated['items'] as $itemData) {
            MenuItem::where('id', $itemData['id'])
                ->where('menu_id', $menu->id)
                ->update([
                    'parent_id' => $itemData['parent_id'],
                    'position' => $itemData['position'],
                ]);
        }

        // Limpa cache
        $menu->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Ordem atualizada com sucesso!',
        ]);
    }

    /**
     * Toggle status do item (ativo/inativo)
     */
    public function toggleItemStatus(Menu $menu, MenuItem $item)
    {
        if ($item->menu_id !== $menu->id) {
            abort(404);
        }

        $item->update(['active' => !$item->active]);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'active' => $item->active,
                'message' => $item->active ? 'Item ativado!' : 'Item desativado!',
            ]);
        }

        return back()->with('success', $item->active ? 'Item ativado!' : 'Item desativado!');
    }

    /**
     * Remove imagem do ícone do item
     */
    public function removeItemIcon(Menu $menu, MenuItem $item)
    {
        if ($item->menu_id !== $menu->id) {
            abort(404);
        }

        if ($item->icon_image) {
            Storage::disk('public')->delete($item->icon_image);
            $item->update(['icon_image' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ícone removido com sucesso!',
        ]);
    }

    /**
     * Remove imagem do mega menu
     */
    public function removeMegaMenuImage(Menu $menu, MenuItem $item)
    {
        if ($item->menu_id !== $menu->id) {
            abort(404);
        }

        if ($item->mega_menu_image) {
            Storage::disk('public')->delete($item->mega_menu_image);
            $item->update(['mega_menu_image' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Imagem do mega menu removida com sucesso!',
        ]);
    }

    /**
     * Duplica um item de menu
     */
    public function duplicateItem(Menu $menu, MenuItem $item)
    {
        if ($item->menu_id !== $menu->id) {
            abort(404);
        }

        // Cria cópia do item
        $newItem = $item->replicate();
        $newItem->title = $item->title . ' (cópia)';
        $newItem->position = MenuItem::where('menu_id', $menu->id)
            ->where('parent_id', $item->parent_id)
            ->max('position') + 1;
        $newItem->save();

        // Duplica filhos recursivamente
        $this->duplicateChildren($item, $newItem);

        return response()->json([
            'success' => true,
            'message' => 'Item duplicado com sucesso!',
            'item' => $newItem,
        ]);
    }

    /**
     * Duplica filhos de um item recursivamente
     */
    protected function duplicateChildren(MenuItem $original, MenuItem $copy): void
    {
        foreach ($original->children as $child) {
            $newChild = $child->replicate();
            $newChild->parent_id = $copy->id;
            $newChild->save();

            $this->duplicateChildren($child, $newChild);
        }
    }
}
