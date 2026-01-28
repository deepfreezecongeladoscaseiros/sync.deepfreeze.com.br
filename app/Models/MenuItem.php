<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

/**
 * Model: MenuItem (Item de Menu)
 *
 * Representa um item individual de um menu com suporte a:
 * - Hierarquia (submenus aninhados)
 * - Tipos variados (categoria, página, link externo, etc.)
 * - Relacionamento polimórfico com entidades (Category, Page)
 * - Ícones (classe CSS ou imagem)
 * - Mega menu com banner/imagem promocional
 * - Controle de exibição por dispositivo
 *
 * @property int $id
 * @property int $menu_id
 * @property int|null $parent_id
 * @property string $type (category, page, url, contact, home, submenu_title)
 * @property string|null $linkable_type
 * @property int|null $linkable_id
 * @property string $title
 * @property string|null $url
 * @property string $target (_self, _blank)
 * @property string|null $icon_class
 * @property string|null $icon_image
 * @property string|null $css_class
 * @property int $position
 * @property string $show_on (all, desktop, mobile)
 * @property bool $is_mega_menu
 * @property string|null $mega_menu_image
 * @property string|null $mega_menu_image_url
 * @property string|null $mega_menu_image_alt
 * @property string $mega_menu_image_position (right, left, bottom)
 * @property int $mega_menu_columns
 * @property bool $active
 */
class MenuItem extends Model
{
    use HasFactory;

    /**
     * Tipos de itens de menu disponíveis
     */
    const TYPES = [
        'home' => 'Home',
        'category' => 'Categoria',
        'page' => 'Página Institucional',
        'url' => 'Link Externo/Customizado',
        'contact' => 'Página de Contato',
        'submenu_title' => 'Título de Grupo (sem link)',
    ];

    /**
     * Posições do banner no mega menu
     */
    const MEGA_MENU_POSITIONS = [
        'right' => 'Direita',
        'left' => 'Esquerda',
        'bottom' => 'Abaixo',
    ];

    /**
     * Opções de exibição por dispositivo
     */
    const SHOW_ON_OPTIONS = [
        'all' => 'Todos (Desktop e Mobile)',
        'desktop' => 'Apenas Desktop',
        'mobile' => 'Apenas Mobile',
    ];

    protected $fillable = [
        'menu_id',
        'parent_id',
        'type',
        'linkable_type',
        'linkable_id',
        'title',
        'url',
        'target',
        'icon_class',
        'icon_image',
        'css_class',
        'position',
        'show_on',
        'is_mega_menu',
        'mega_menu_image',
        'mega_menu_image_url',
        'mega_menu_image_alt',
        'mega_menu_image_position',
        'mega_menu_columns',
        'active',
    ];

    protected $casts = [
        'is_mega_menu' => 'boolean',
        'active' => 'boolean',
        'position' => 'integer',
        'mega_menu_columns' => 'integer',
    ];

    // =========================================================================
    // RELACIONAMENTOS
    // =========================================================================

    /**
     * Menu ao qual pertence
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Item pai (para hierarquia)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * Itens filhos (subitens)
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('position');
    }

    /**
     * Apenas filhos ativos
     */
    public function activeChildren(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')
            ->where('active', true)
            ->orderBy('position');
    }

    /**
     * Filhos ativos com recursão (para árvore completa)
     */
    public function activeChildrenRecursive(): HasMany
    {
        return $this->activeChildren()->with('activeChildrenRecursive');
    }

    /**
     * Relacionamento polimórfico com entidade linkada
     * (Category, Page, etc.)
     */
    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Scope: apenas itens ativos
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope: itens raiz (sem pai)
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope: filtrar por dispositivo
     */
    public function scopeForDevice($query, string $device)
    {
        return $query->where(function ($q) use ($device) {
            $q->where('show_on', 'all')
                ->orWhere('show_on', $device);
        });
    }

    /**
     * Scope: ordenar por posição
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }

    // =========================================================================
    // MÉTODOS DE URL
    // =========================================================================

    /**
     * Retorna a URL resolvida do item
     * Resolve automaticamente baseado no tipo
     */
    public function getResolvedUrl(): ?string
    {
        switch ($this->type) {
            case 'home':
                return url('/');

            case 'contact':
                return route('contact');

            case 'category':
                // Se tem linkable (Category), usa o slug dela
                if ($this->linkable) {
                    return url('/categoria/' . $this->linkable->slug);
                }
                return $this->url;

            case 'page':
                // Se tem linkable (Page), usa o slug dela
                if ($this->linkable) {
                    return url('/' . $this->linkable->slug);
                }
                return $this->url;

            case 'url':
                return $this->url;

            case 'submenu_title':
                // Títulos de grupo não têm link
                return null;

            default:
                return $this->url;
        }
    }

    /**
     * Verifica se o item tem link válido
     */
    public function hasLink(): bool
    {
        return $this->type !== 'submenu_title' && $this->getResolvedUrl() !== null;
    }

    // =========================================================================
    // MÉTODOS DE ÍCONE
    // =========================================================================

    /**
     * Retorna URL da imagem do ícone (se houver)
     */
    public function getIconImageUrl(): ?string
    {
        if (!$this->icon_image) {
            return null;
        }

        // Se for URL externa, retorna direto
        if (str_starts_with($this->icon_image, 'http')) {
            return $this->icon_image;
        }

        // Se for path de storage, gera URL
        return Storage::url($this->icon_image);
    }

    /**
     * Verifica se tem ícone (classe ou imagem)
     */
    public function hasIcon(): bool
    {
        return !empty($this->icon_class) || !empty($this->icon_image);
    }

    // =========================================================================
    // MÉTODOS DE MEGA MENU
    // =========================================================================

    /**
     * Retorna URL da imagem do mega menu (se houver)
     */
    public function getMegaMenuImageUrl(): ?string
    {
        if (!$this->mega_menu_image) {
            return null;
        }

        // Se for URL externa, retorna direto
        if (str_starts_with($this->mega_menu_image, 'http')) {
            return $this->mega_menu_image;
        }

        // Se for path de storage, gera URL
        return Storage::url($this->mega_menu_image);
    }

    /**
     * Verifica se tem imagem no mega menu
     */
    public function hasMegaMenuImage(): bool
    {
        return $this->is_mega_menu && !empty($this->mega_menu_image);
    }

    // =========================================================================
    // MÉTODOS DE HIERARQUIA
    // =========================================================================

    /**
     * Verifica se tem filhos ativos
     *
     * Otimização: se a relação 'activeChildren' já foi carregada via eager loading,
     * usa a collection em memória ao invés de executar COUNT no banco.
     * Antes: 1 query COUNT por item de menu (N+1 no menu inteiro)
     * Depois: 0 queries extras quando eager loaded
     */
    public function hasChildren(): bool
    {
        // Se já foi carregado via eager loading, usa a collection em memória
        if ($this->relationLoaded('activeChildren')) {
            return $this->activeChildren->isNotEmpty();
        }

        // Fallback: query COUNT no banco
        return $this->activeChildren()->count() > 0;
    }

    /**
     * Retorna a profundidade do item na árvore (0 = raiz)
     */
    public function getDepth(): int
    {
        $depth = 0;
        $parent = $this->parent;

        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }

        return $depth;
    }

    /**
     * Verifica se é item raiz
     */
    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    // =========================================================================
    // MÉTODOS DE STATUS/ESTADO
    // =========================================================================

    /**
     * Verifica se este item é o atualmente ativo (página atual)
     */
    public function isActive(): bool
    {
        $currentUrl = request()->url();
        $itemUrl = $this->getResolvedUrl();

        if (!$itemUrl) {
            return false;
        }

        // Verifica URL exata ou se começa com a URL do item (para subpáginas)
        return $currentUrl === $itemUrl ||
            str_starts_with($currentUrl, $itemUrl . '/');
    }

    /**
     * Verifica se este item ou algum filho está ativo (recursivo)
     *
     * Otimização: usa relationLoaded para evitar N+1 na relação activeChildren.
     * Se não estiver carregada, busca apenas uma vez e reutiliza.
     */
    public function isActiveOrHasActiveChild(): bool
    {
        if ($this->isActive()) {
            return true;
        }

        // Usa a relação já carregada via eager loading quando disponível
        $children = $this->relationLoaded('activeChildren')
            ? $this->activeChildren
            : $this->activeChildren()->get();

        foreach ($children as $child) {
            if ($child->isActiveOrHasActiveChild()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retorna label do tipo
     */
    public function getTypeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * Retorna label de show_on
     */
    public function getShowOnLabel(): string
    {
        return self::SHOW_ON_OPTIONS[$this->show_on] ?? $this->show_on;
    }

    // =========================================================================
    // MÉTODOS DE CLASSE CSS
    // =========================================================================

    /**
     * Retorna classes CSS para renderização
     */
    public function getCssClasses(): string
    {
        $classes = [];

        // Classe customizada
        if ($this->css_class) {
            $classes[] = $this->css_class;
        }

        // Mega menu
        if ($this->is_mega_menu) {
            $classes[] = 'submenu-full';
        }

        // Item ativo
        if ($this->isActive()) {
            $classes[] = 'active';
        }

        // Tem filhos
        if ($this->hasChildren()) {
            $classes[] = 'dropdown';
        }

        // Classe baseada no tipo
        $classes[] = 'menu-' . str_replace('_', '-', $this->type);

        return implode(' ', $classes);
    }

    // =========================================================================
    // EVENTOS
    // =========================================================================

    protected static function boot()
    {
        parent::boot();

        // Limpa cache do menu ao salvar item
        static::saved(function ($item) {
            if ($item->menu) {
                $item->menu->clearCache();
            }
        });

        // Limpa cache do menu ao deletar item
        static::deleted(function ($item) {
            if ($item->menu) {
                $item->menu->clearCache();
            }
        });
    }
}
