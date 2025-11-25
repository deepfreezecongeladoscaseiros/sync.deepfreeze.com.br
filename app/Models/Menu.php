<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

/**
 * Model: Menu (Container de Menu)
 *
 * Representa um menu da loja (principal, rodapé, mobile, etc.)
 * Cada menu contém múltiplos itens organizados hierarquicamente.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $location (header, footer, mobile_sidebar, custom)
 * @property string|null $description
 * @property bool $active
 */
class Menu extends Model
{
    use HasFactory;

    /**
     * Tempo de cache em segundos (1 hora)
     */
    const CACHE_TTL = 3600;

    /**
     * Localizações disponíveis para menus
     */
    const LOCATIONS = [
        'header' => 'Cabeçalho (Menu Principal)',
        'footer' => 'Rodapé',
        'mobile_sidebar' => 'Menu Lateral Mobile',
        'custom' => 'Posição Customizada',
    ];

    protected $fillable = [
        'name',
        'slug',
        'location',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    // =========================================================================
    // RELACIONAMENTOS
    // =========================================================================

    /**
     * Todos os itens do menu (flat)
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('position');
    }

    /**
     * Apenas itens raiz (sem parent_id) - para construir árvore
     */
    public function rootItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)
            ->whereNull('parent_id')
            ->orderBy('position');
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Scope: apenas menus ativos
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope: busca por localização
     */
    public function scopeByLocation($query, string $location)
    {
        return $query->where('location', $location);
    }

    /**
     * Scope: busca por slug
     */
    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    // =========================================================================
    // MÉTODOS ESTÁTICOS (Obter menus com cache)
    // =========================================================================

    /**
     * Obtém menu pelo slug (com cache)
     *
     * @param string $slug
     * @return Menu|null
     */
    public static function getBySlug(string $slug): ?Menu
    {
        return Cache::remember(
            "menu:{$slug}",
            self::CACHE_TTL,
            fn() => self::active()
                ->bySlug($slug)
                ->with(['rootItems' => function ($query) {
                    $query->active()
                        ->with('activeChildren')
                        ->orderBy('position');
                }])
                ->first()
        );
    }

    /**
     * Obtém menu principal do header (com cache)
     */
    public static function getMainMenu(): ?Menu
    {
        return Cache::remember(
            'menu:main',
            self::CACHE_TTL,
            fn() => self::active()
                ->byLocation('header')
                ->with(['rootItems' => function ($query) {
                    $query->active()
                        ->with('activeChildren')
                        ->orderBy('position');
                }])
                ->first()
        );
    }

    /**
     * Obtém menu do rodapé (com cache)
     */
    public static function getFooterMenu(): ?Menu
    {
        return Cache::remember(
            'menu:footer',
            self::CACHE_TTL,
            fn() => self::active()
                ->byLocation('footer')
                ->with(['rootItems' => function ($query) {
                    $query->active()
                        ->with('activeChildren')
                        ->orderBy('position');
                }])
                ->first()
        );
    }

    /**
     * Obtém menu mobile (com cache)
     */
    public static function getMobileMenu(): ?Menu
    {
        return Cache::remember(
            'menu:mobile',
            self::CACHE_TTL,
            fn() => self::active()
                ->byLocation('mobile_sidebar')
                ->with(['rootItems' => function ($query) {
                    $query->active()
                        ->with('activeChildren')
                        ->orderBy('position');
                }])
                ->first()
        );
    }

    // =========================================================================
    // MÉTODOS DE INSTÂNCIA
    // =========================================================================

    /**
     * Retorna label amigável da localização
     */
    public function getLocationLabel(): string
    {
        return self::LOCATIONS[$this->location] ?? $this->location;
    }

    /**
     * Retorna árvore de itens organizados hierarquicamente
     * Útil para renderização no frontend
     *
     * @param string|null $showOn Filtrar por dispositivo (all, desktop, mobile)
     * @return \Illuminate\Support\Collection
     */
    public function getItemsTree(?string $showOn = null): \Illuminate\Support\Collection
    {
        $query = $this->rootItems()->active()->with('activeChildren');

        if ($showOn && $showOn !== 'all') {
            $query->where(function ($q) use ($showOn) {
                $q->where('show_on', 'all')
                    ->orWhere('show_on', $showOn);
            });
        }

        return $query->orderBy('position')->get();
    }

    // =========================================================================
    // CACHE
    // =========================================================================

    /**
     * Limpa o cache deste menu
     */
    public function clearCache(): void
    {
        Cache::forget("menu:{$this->slug}");
        Cache::forget('menu:main');
        Cache::forget('menu:footer');
        Cache::forget('menu:mobile');
    }

    /**
     * Limpa cache de todos os menus
     */
    public static function clearAllCache(): void
    {
        // Limpa caches conhecidos
        Cache::forget('menu:main');
        Cache::forget('menu:footer');
        Cache::forget('menu:mobile');

        // Limpa cache de todos os menus por slug
        self::all()->each(function ($menu) {
            Cache::forget("menu:{$menu->slug}");
        });
    }

    // =========================================================================
    // EVENTOS
    // =========================================================================

    protected static function boot()
    {
        parent::boot();

        // Limpa cache ao salvar
        static::saved(function ($menu) {
            $menu->clearCache();
        });

        // Limpa cache ao deletar
        static::deleted(function ($menu) {
            $menu->clearCache();
        });
    }
}
