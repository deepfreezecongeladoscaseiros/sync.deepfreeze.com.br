<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model para blocos flexíveis da home page
 *
 * Permite montar a home com blocos intercalados de diferentes tipos,
 * onde cada bloco pode referenciar um item específico (galeria X, banner Y, etc.)
 *
 * @property int $id
 * @property string $type - Tipo do bloco (hero_banners, product_gallery, dual_banner, etc.)
 * @property int|null $reference_id - ID do item específico (null para tipos que exibem todos)
 * @property string|null $custom_title - Título customizado (sobrescreve o do item)
 * @property int $order - Ordem de exibição na home
 * @property bool $is_active - Bloco ativo/inativo
 */
class HomeBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'reference_id',
        'custom_title',
        'order',
        'is_active',
    ];

    protected $casts = [
        'reference_id' => 'integer',
        'order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Tipos de blocos disponíveis
     * - requires_reference: se precisa escolher um item específico
     * - model: Model relacionado (para buscar itens disponíveis)
     * - label: Nome amigável para exibição
     * - icon: Ícone Bootstrap Icons
     * - helper: Função helper que renderiza o bloco
     * - admin_route: Rota para editar os itens desse tipo
     */
    public const BLOCK_TYPES = [
        'hero_banners' => [
            'requires_reference' => false,
            'model' => null,
            'label' => 'Banner Principal (Hero)',
            'icon' => 'bi bi-image',
            'helper' => 'hero_banners',
            'admin_route' => 'admin.banners.index',
            'description' => 'Carrossel de banners no topo da página',
        ],
        'feature_blocks' => [
            'requires_reference' => false,
            'model' => null,
            'label' => 'Blocos de Informações',
            'icon' => 'bi bi-grid-3x3-gap',
            'helper' => 'feature_blocks',
            'admin_route' => 'admin.feature-blocks.index',
            'description' => '4 blocos com ícones e texto (frete, entrega, etc.)',
        ],
        'product_gallery' => [
            'requires_reference' => true,
            'model' => ProductGallery::class,
            'label' => 'Galeria de Produtos',
            'icon' => 'bi bi-collection',
            'helper' => 'product_gallery',
            'admin_route' => 'admin.product-galleries.index',
            'description' => 'Carrossel de produtos (escolha qual galeria)',
        ],
        'dual_banner' => [
            'requires_reference' => true,
            'model' => DualBanner::class,
            'label' => 'Banner Duplo',
            'icon' => 'bi bi-layout-split',
            'helper' => 'dual_banner',
            'admin_route' => 'admin.dual-banners.index',
            'description' => 'Dois banners lado a lado (escolha qual par)',
        ],
        'info_block' => [
            'requires_reference' => true,
            'model' => InfoBlock::class,
            'label' => 'Bloco de Informação',
            'icon' => 'bi bi-info-circle',
            'helper' => 'info_block',
            'admin_route' => 'admin.info-blocks.index',
            'description' => 'Seção com imagem e texto (escolha qual bloco)',
        ],
        'step_blocks' => [
            'requires_reference' => false,
            'model' => null,
            'label' => 'Blocos de Passos',
            'icon' => 'bi bi-list-ol',
            'helper' => 'step_blocks',
            'admin_route' => 'admin.step-blocks.index',
            'description' => '4 passos com ícone, título e descrição',
        ],
        'single_banner' => [
            'requires_reference' => true,
            'model' => SingleBanner::class,
            'label' => 'Banner Único',
            'icon' => 'bi bi-card-image',
            'helper' => 'single_banner',
            'admin_route' => 'admin.single-banners.index',
            'description' => 'Banner em largura total (escolha qual banner)',
        ],
    ];

    /**
     * Scope para buscar apenas blocos ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para ordenar por ordem de exibição
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    /**
     * Retorna a configuração do tipo deste bloco
     */
    public function getTypeConfig(): ?array
    {
        return self::BLOCK_TYPES[$this->type] ?? null;
    }

    /**
     * Retorna o label amigável do tipo
     */
    public function getTypeLabelAttribute(): string
    {
        return self::BLOCK_TYPES[$this->type]['label'] ?? $this->type;
    }

    /**
     * Retorna o ícone do tipo
     */
    public function getTypeIconAttribute(): string
    {
        return self::BLOCK_TYPES[$this->type]['icon'] ?? 'bi bi-box';
    }

    /**
     * Verifica se este tipo requer seleção de item específico
     */
    public function requiresReference(): bool
    {
        return self::BLOCK_TYPES[$this->type]['requires_reference'] ?? false;
    }

    /**
     * Retorna o item referenciado (galeria, banner, etc.)
     * Retorna null se o tipo não requer referência ou se o item não existe
     *
     * Otimização: se o item foi pré-carregado via home_blocks() (setRelation),
     * usa o item em memória ao invés de fazer query individual no banco.
     * Cada query ao banco remoto leva ~550ms de latência de rede.
     */
    public function getReferencedItem(): ?Model
    {
        // Usa item pré-carregado se disponível (injetado por home_blocks())
        if ($this->relationLoaded('_preloadedItem')) {
            return $this->getRelation('_preloadedItem');
        }

        $config = $this->getTypeConfig();

        if (!$config || !$config['requires_reference'] || !$this->reference_id) {
            return null;
        }

        $modelClass = $config['model'];
        return $modelClass::find($this->reference_id);
    }

    /**
     * Retorna o título a ser exibido (custom_title ou título do item)
     */
    public function getDisplayTitleAttribute(): string
    {
        // Se tem título customizado, usa ele
        if ($this->custom_title) {
            return $this->custom_title;
        }

        // Se tem item referenciado, tenta pegar o título dele
        $item = $this->getReferencedItem();
        if ($item && isset($item->title)) {
            return $item->title;
        }

        // Fallback para o label do tipo
        return $this->type_label;
    }

    /**
     * Retorna a URL para editar os itens deste tipo no admin
     */
    public function getAdminUrl(): ?string
    {
        $config = $this->getTypeConfig();

        if (!$config || !isset($config['admin_route'])) {
            return null;
        }

        return route($config['admin_route']);
    }

    /**
     * Renderiza o HTML deste bloco chamando a função helper apropriada
     *
     * Para tipos que requerem referência (product_gallery, dual_banner, etc.),
     * passa o item específico para o helper.
     *
     * Para tipos globais (hero_banners, feature_blocks, step_blocks),
     * chama o helper sem parâmetros.
     */
    public function render(): string
    {
        $config = $this->getTypeConfig();

        if (!$config) {
            return "<!-- Bloco tipo '{$this->type}' não reconhecido -->";
        }

        $helperFunction = $config['helper'];

        // Verifica se a função helper existe
        if (!function_exists($helperFunction)) {
            return "<!-- Helper '{$helperFunction}' não encontrado -->";
        }

        // Se requer referência, passa o item específico
        if ($config['requires_reference']) {
            $item = $this->getReferencedItem();

            if (!$item) {
                return "<!-- Item #{$this->reference_id} não encontrado para {$this->type} -->";
            }

            // Verifica se o item está ativo (se tiver esse atributo)
            if (isset($item->active) && !$item->active) {
                return "<!-- Item #{$this->reference_id} está inativo -->";
            }

            return $helperFunction($item);
        }

        // Para tipos globais, chama o helper sem parâmetros
        return $helperFunction();
    }

    /**
     * Retorna itens disponíveis para seleção (para tipos que requerem referência)
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public static function getAvailableItems(string $type)
    {
        $config = self::BLOCK_TYPES[$type] ?? null;

        if (!$config || !$config['requires_reference'] || !$config['model']) {
            return null;
        }

        $modelClass = $config['model'];

        // Tenta ordenar por 'order' se existir, senão por 'id'
        $query = $modelClass::query();

        // Verifica se o model tem o campo 'title' para exibição
        return $query->orderBy('id')->get();
    }

    /**
     * Retorna lista de tipos disponíveis para criar novos blocos
     */
    public static function getAvailableTypes(): array
    {
        return self::BLOCK_TYPES;
    }
}
