<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model para os blocos de features/informações exibidos abaixo do banner hero
 *
 * São sempre 4 blocos fixos que exibem informações como:
 * - Frete expresso
 * - Entrega expressa
 * - Praticidade
 * - Pagamento
 *
 * Cada bloco pode ter ícone, título, descrição e cores personalizáveis.
 *
 * @property int $id
 * @property int $order - Ordem de exibição (1-4)
 * @property string $icon - Classe do ícone (ex: 'bi bi-truck')
 * @property string $title - Título em negrito
 * @property string $description - Texto descritivo
 * @property string $bg_color - Cor de fundo (hex)
 * @property string $text_color - Cor do texto (hex)
 * @property string $icon_color - Cor do ícone (hex)
 * @property bool $active - Ativo/inativo
 */
class FeatureBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'order',
        'icon_path',
        'title',
        'description',
        'bg_color',
        'text_color',
        'icon_color',
        'active',
    ];

    /**
     * Casts para boolean
     */
    protected $casts = [
        'active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Scope para buscar apenas blocos ativos
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope para ordenar por ordem de exibição
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    /**
     * Retorna o estilo inline para o bloco
     *
     * @return string
     */
    public function getInlineStyle(): string
    {
        return "background-color: {$this->bg_color}; color: {$this->text_color};";
    }

    /**
     * Retorna o estilo inline para o ícone
     *
     * @return string
     */
    public function getIconStyle(): string
    {
        return "color: {$this->icon_color};";
    }

    /**
     * Retorna a URL completa do ícone
     *
     * @return string
     */
    public function getIconUrl(): string
    {
        return asset('storage/' . $this->icon_path);
    }
}
