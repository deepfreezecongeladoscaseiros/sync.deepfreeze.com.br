<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model para galerias de produtos exibidas na home
 *
 * Permite criar até 4 galerias customizáveis com:
 * - Filtros dinâmicos (categoria, mais vendidos, promoção, ponta de estoque)
 * - Configuração de layout (colunas mobile/desktop, limite de produtos)
 * - Customização visual (cores de fundo, título, subtítulo, botão)
 * - Imagem de fundo opcional
 *
 * @property int $id
 * @property int $order - Ordem de exibição (1-4)
 * @property string $title - Título da galeria
 * @property string|null $subtitle - Subtítulo da galeria
 * @property int $mobile_columns - Produtos por linha no mobile (1-4)
 * @property int $desktop_columns - Produtos por linha no desktop (1-6)
 * @property int $products_limit - Quantidade total de produtos
 * @property string $filter_type - Tipo de filtro: category, best_sellers, on_sale, low_stock
 * @property int|null $filter_value - ID da categoria (quando filter_type = 'category')
 * @property string|null $background_color - Cor de fundo (hex)
 * @property string|null $background_image_path - Caminho da imagem de fundo
 * @property string $title_color - Cor do título (hex)
 * @property string $subtitle_color - Cor do subtítulo (hex)
 * @property bool $show_view_all_button - Exibir botão "Ver Todos"
 * @property string|null $view_all_url - URL do botão "Ver Todos"
 * @property string $button_bg_color - Cor de fundo do botão (hex)
 * @property string $button_hover_color - Cor hover do botão (hex)
 * @property string $button_text_color - Cor do texto do botão (hex)
 * @property bool $active - Galeria ativa/inativa
 */
class ProductGallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'order',
        'title',
        'subtitle',
        'mobile_columns',
        'desktop_columns',
        'products_limit',
        'filter_type',
        'filter_value',
        'background_color',
        'background_image_path',
        'title_color',
        'subtitle_color',
        'show_view_all_button',
        'view_all_url',
        'button_bg_color',
        'button_hover_color',
        'button_text_color',
        'active',
    ];

    protected $casts = [
        'order' => 'integer',
        'mobile_columns' => 'integer',
        'desktop_columns' => 'integer',
        'products_limit' => 'integer',
        'filter_value' => 'integer',
        'show_view_all_button' => 'boolean',
        'active' => 'boolean',
    ];

    /**
     * Relacionamento com Category (quando filter_type = 'category')
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'filter_value');
    }

    /**
     * Scope para buscar apenas galerias ativas
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
     * Retorna os produtos filtrados para esta galeria
     *
     * IMPORTANTE: Apenas produtos visíveis na loja são retornados.
     * Utiliza o scope visibleInStore() que garante:
     * - Produtos ativos (active = true)
     * - Produtos COM pelo menos uma imagem
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getProducts()
    {
        $query = Product::query()
            ->visibleInStore(); // Scope: ativos + com imagem

        // Aplica filtro baseado no tipo
        switch ($this->filter_type) {
            case 'category':
                if ($this->filter_value) {
                    $query->where('category_id', $this->filter_value);
                }
                break;

            case 'best_sellers':
                // TODO: Implementar lógica de mais vendidos (requer tabela de pedidos)
                // Por enquanto, ordena por ID decrescente
                $query->orderBy('id', 'desc');
                break;

            case 'on_sale':
                // Produtos com promotional_price preenchido e dentro do período de promoção
                $query->whereNotNull('promotional_price')
                    ->where(function ($q) {
                        $q->whereNull('start_promotion')
                            ->orWhere('start_promotion', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('end_promotion')
                            ->orWhere('end_promotion', '>=', now());
                    });
                break;

            case 'low_stock':
                // Produtos com estoque baixo (< 10 unidades)
                $query->where('stock', '<', 10)->where('stock', '>', 0);
                break;
        }

        return $query->limit($this->products_limit)->get();
    }

    /**
     * Retorna o estilo inline para o fundo da galeria
     *
     * @return string
     */
    public function getBackgroundStyle(): string
    {
        $styles = [];

        if ($this->background_color) {
            $styles[] = "background-color: {$this->background_color}";
        }

        if ($this->background_image_path) {
            $url = asset('storage/' . $this->background_image_path);
            $styles[] = "background-image: url('{$url}')";
            $styles[] = "background-size: cover";
            $styles[] = "background-position: center";
        }

        return implode('; ', $styles);
    }

    /**
     * Retorna a classe de coluna para mobile baseada em mobile_columns
     *
     * @return string
     */
    public function getMobileColumnClass(): string
    {
        // Bootstrap usa 12 colunas. Divide por mobile_columns
        $colSize = 12 / $this->mobile_columns;
        return "col-xs-{$colSize}";
    }

    /**
     * Retorna a classe de coluna para desktop baseada em desktop_columns
     *
     * @return string
     */
    public function getDesktopColumnClass(): string
    {
        // Bootstrap usa 12 colunas. Divide por desktop_columns
        $colSize = 12 / $this->desktop_columns;
        return "col-lg-{$colSize}";
    }

    /**
     * Retorna a URL da imagem de fundo
     *
     * @return string|null
     */
    public function getBackgroundImageUrl(): ?string
    {
        if (!$this->background_image_path) {
            return null;
        }

        return asset('storage/' . $this->background_image_path);
    }
}
