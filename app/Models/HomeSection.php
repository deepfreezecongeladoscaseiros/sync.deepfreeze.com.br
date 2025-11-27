<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model para gerenciar as seções da página inicial (home)
 *
 * Permite controlar a ordem de exibição e ativação de cada seção
 * da home page via painel administrativo.
 *
 * Seções disponíveis:
 * - hero_banners: Banner principal do topo (carrossel)
 * - feature_blocks: Blocos de funcionalidades/ícones (4 itens)
 * - product_galleries: Galerias de produtos (até 4 galerias)
 * - dual_banners: Banners duplos (lado a lado)
 * - info_blocks: Blocos de informação (ex: Refeições Saudáveis)
 * - step_blocks: Blocos de passos (4 itens com ícone)
 * - single_banners: Banners únicos (desktop + mobile)
 *
 * @property int $id
 * @property string $name - Nome amigável da seção
 * @property string $slug - Identificador único (snake_case)
 * @property string $helper_function - Nome da função helper que renderiza
 * @property string|null $description - Descrição da seção
 * @property string|null $icon - Classe do ícone Bootstrap Icons
 * @property bool $is_active - Se a seção está visível na home
 * @property int $order - Ordem de exibição (menor primeiro)
 * @property string|null $admin_route - Nome da rota do admin para editar itens
 */
class HomeSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'helper_function',
        'description',
        'icon',
        'is_active',
        'order',
        'admin_route',
    ];

    /**
     * Casts para tipos corretos
     */
    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Scope para buscar apenas seções ativas
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para ordenar por ordem de exibição
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    /**
     * Renderiza o conteúdo HTML da seção chamando a função helper correspondente
     *
     * Cada seção tem uma função helper associada (ex: hero_banners(), feature_blocks())
     * que retorna o HTML renderizado da seção.
     *
     * @return string HTML da seção ou string vazia se a função não existir
     */
    public function render(): string
    {
        // Verifica se a função helper existe
        if (function_exists($this->helper_function)) {
            // Chama a função helper dinamicamente e retorna o resultado
            return call_user_func($this->helper_function);
        }

        // Retorna vazio se a função não existir (evita erros)
        return '';
    }

    /**
     * Retorna a URL do admin para editar os itens desta seção
     *
     * @return string|null URL do admin ou null se não houver rota configurada
     */
    public function getAdminUrl(): ?string
    {
        if ($this->admin_route && \Route::has($this->admin_route)) {
            return route($this->admin_route);
        }

        return null;
    }

    /**
     * Verifica se a seção tem link para o admin
     *
     * @return bool
     */
    public function hasAdminLink(): bool
    {
        return !empty($this->admin_route) && \Route::has($this->admin_route);
    }

    /**
     * Busca uma seção pelo slug
     *
     * @param string $slug
     * @return self|null
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    /**
     * Retorna todas as seções ativas ordenadas para renderização na home
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getOrderedSections()
    {
        return static::active()->ordered()->get();
    }
}
