<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Model: Produto (lê da tabela 'produtos' do banco legado)
 *
 * O cadastro de produtos é feito no SIV (sistema legado CakePHP).
 * Este Model apenas LÊ os dados para exibição na nova loja virtual.
 *
 * Usa $columnMap para mapear nomes em inglês (usados no Blade)
 * para nomes em português (colunas reais do banco legado).
 *
 * Tabela: novo.produtos
 * Engine: MyISAM
 * Charset: utf8mb3
 */
class Product extends Model
{
    /**
     * Conexão com o banco de dados legado
     */
    protected $connection = 'mysql_legacy';
    protected $table = 'produtos';

    /**
     * Timestamps do legado usam 'created' e 'updated' (não created_at/updated_at)
     */
    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    /**
     * Mapeamento: nome inglês (Blade) → coluna real (banco legado)
     *
     * Permite usar $product->name no template enquanto
     * a coluna real no banco é 'descricao'.
     *
     * Campos que precisam de transformação (ex: preço varchar→float)
     * são tratados por accessors explícitos, não pelo mapa.
     */
    protected $columnMap = [
        // Identificação
        'name'              => 'descricao',
        'sku'               => 'codigo',
        'ean'               => 'codigo_de_barras',
        'ncm'               => 'ncm',

        // Descrições
        'description'              => 'apresentacao',
        'description_small'        => 'descricao_etiqueta',
        'description_english'      => 'descricao_ingles',
        'properties'               => 'propriedades',
        'benefits'                 => 'beneficios',
        'chef_tips'                => 'dica_do_chef',
        'dish_history'             => 'historia_do_prato',
        'ingredients'              => 'ingredientes',
        'consumption_instructions' => 'instrucoes_para_consumo',

        // Peso e medidas
        'weight'      => 'peso_liquido',
        'gross_weight' => 'peso_bruto',
        'weight_unit' => 'unidade_medida_peso',

        // Visual do card
        'background_color' => 'cor_fundo',
        'text_color'       => 'cor_texto',

        // Flags
        'made_to_order' => 'produzido_por_encomenda',
        'active'        => 'ativo',

        // Ordenação
        'display_order' => 'ordem_exibicao_site',

        // Relacionamentos (FKs)
        'category_id'     => 'categoria_id',
        'manufacturer_id' => 'fabricante_id',
        'brand_id'        => 'marca_id',

        // Alérgenos e restrições alimentares
        'contains_gluten'    => 'in_contem_gluten',
        'lactose_free'       => 'in_sem_lactose',
        'low_lactose'        => 'in_baixo_lactose',
        'contains_lactose'   => 'in_contem_lactose',
        'alcoholic_beverage' => 'bebida_alcoolica',
        'allergens'          => 'alergenicos_manual',

        // Produção e validade
        'shelf_life_days' => 'validade_dias',
        'freezing_time'   => 'tempo_congelamento',
        'portion_size'    => 'porcao',
        'home_measure'    => 'medida_caseira',
    ];

    /**
     * Resolve atributos mapeados: inglês → coluna legado
     *
     * Quando o Blade acessa $product->name, o Laravel chama getAttribute('name').
     * Este override verifica se 'name' está no mapa e redireciona para 'descricao'.
     */
    public function getAttribute($key)
    {
        // Se o atributo tem mapeamento, busca pela coluna real do legado
        if (isset($this->columnMap[$key])) {
            return parent::getAttribute($this->columnMap[$key]);
        }

        return parent::getAttribute($key);
    }

    // ==================== ACCESSORS (campos com transformação de tipo) ====================

    /**
     * Preço - converte varchar do legado para float
     *
     * No legado, 'preco' é varchar(10) e pode conter vírgula como separador decimal.
     * Ex: "12,50" ou "12.50" ou "0.00"
     */
    public function getPriceAttribute(): float
    {
        $raw = $this->attributes['preco'] ?? '0';
        return (float) str_replace(',', '.', $raw);
    }

    /**
     * Preço promocional - converte varchar do legado para float ou null
     *
     * Retorna null quando não há promoção (vazio, "0.00" ou "0").
     * O site antigo usa: strcmp($preco_promocional, "0.00") && $preco_promocional != ""
     */
    public function getPromotionalPriceAttribute(): ?float
    {
        $raw = $this->attributes['preco_promocional'] ?? null;

        if (empty($raw) || $raw === '0.00' || $raw === '0') {
            return null;
        }

        return (float) str_replace(',', '.', $raw);
    }

    /**
     * is_combo - legado armazena como tinyint 'combo'
     */
    public function getIsComboAttribute(): bool
    {
        return !empty($this->attributes['combo']);
    }

    /**
     * is_gift_card - legado armazena como tinyint 'gift_card'
     */
    public function getIsGiftCardAttribute(): bool
    {
        return !empty($this->attributes['gift_card']);
    }

    /**
     * is_package - no legado, 'pacote' é int (pode ser FK ou flag)
     * Valor > 0 indica que o produto é um pacote/kit
     */
    public function getIsPackageAttribute(): bool
    {
        return !empty($this->attributes['pacote']) && $this->attributes['pacote'] > 0;
    }

    /**
     * Estoque - calculado a partir de otm_estoques_lojas
     *
     * No legado, estoque NÃO é um campo na tabela produtos.
     * É calculado: SUM(estoque_atual_calculado - giro_balcao) por produto_id.
     *
     * Quando o scope withStockQuantity() é usado, o valor vem no atributo '_stock'
     * evitando N+1 queries. Sem o scope, faz query individual (fallback).
     */
    public function getStockAttribute(): int
    {
        // Prioriza valor pré-calculado via scope withStockQuantity()
        if (array_key_exists('_stock', $this->attributes)) {
            return (int) ($this->attributes['_stock'] ?? 0);
        }

        // Fallback: query individual (evitar em listagens - usar scope)
        return (int) (OtmEstoqueLoja::where('produto_id', $this->id)
            ->selectRaw('SUM(estoque_atual_calculado - giro_balcao) as total')
            ->value('total') ?? 0);
    }

    /**
     * hot (Destaque) - não existe como coluna no legado
     *
     * No legado, "Destaque" é uma tag na tabela tags/produtos_tags.
     * TODO: Implementar verificação via tags quando necessário.
     */
    public function getHotAttribute(): bool
    {
        return false;
    }

    /**
     * release (Lançamento) - não existe como coluna no legado
     *
     * No legado, "Lançamento" seria uma tag na tabela tags/produtos_tags.
     * TODO: Implementar verificação via tags quando necessário.
     */
    public function getReleaseAttribute(): bool
    {
        return false;
    }

    /**
     * available - no legado não existe campo separado de disponibilidade
     * Usa 'ativo' como referência principal
     */
    public function getAvailableAttribute(): bool
    {
        return !empty($this->attributes['ativo']);
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Categoria do produto (tabela: categorias)
     * FK legado: categoria_id
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'categoria_id');
    }

    /**
     * Imagens do produto (tabela: produtos_imagens)
     * Apenas imagens ativas, ordenadas por posição
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class, 'produto_id')
            ->where('ativa', 1)
            ->orderBy('posicao');
    }

    /**
     * Tags do produto (tabela: tags via produtos_tags)
     *
     * Tags são selos visuais como "Sem Glúten", "Sem Lactose", "Novidade"
     * com ícones, cores e tipos (IMGSP, IMG, TXT).
     * produtos_tags tem data_inicial/data_final para tags temporárias.
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'produtos_tags', 'produto_id', 'tag_id')
            ->withPivot('data_inicial', 'data_final');
    }

    /**
     * Tags ativas (filtra por data e status da tag)
     */
    public function activeTags()
    {
        return $this->tags()
            ->where('ativa', 1)
            ->where(function ($q) {
                $q->whereNull('produtos_tags.data_final')
                  ->orWhere('produtos_tags.data_final', '>=', now());
            });
    }

    /**
     * Marca do produto
     * FK legado: marca_id
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'marca_id');
    }

    /**
     * Fabricante do produto
     * FK legado: fabricante_id
     */
    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class, 'fabricante_id');
    }

    // ==================== HELPERS ====================

    /**
     * Gera slug do produto
     *
     * No legado, a URL usa: {codigo}-{descricao_slugified}
     * Ex: produto código "CH01" descricao "Frango Grelhado" → "ch01-frango-grelhado"
     */
    public function getSlugAttribute(): string
    {
        $codigo = $this->attributes['codigo'] ?? '';
        $descricao = $this->attributes['descricao'] ?? '';

        return Str::slug($codigo . '-' . $descricao);
    }

    /**
     * URL do produto na loja
     */
    public function getUrlAttribute(): string
    {
        if ($this->category) {
            return url("/{$this->category->slug}/{$this->slug}");
        }

        return url("/produto/{$this->slug}");
    }

    /**
     * Retorna a imagem principal do produto
     *
     * Legado: primeira imagem ativa ordenada por posição.
     * Não existe conceito de "is_main" no legado - é sempre a primeira por posição.
     */
    public function getMainImage(): ?ProductImage
    {
        // Se imagens já carregadas via eager loading, filtra na collection (0 queries extras)
        if ($this->relationLoaded('images')) {
            return $this->images->first();
        }

        // Fallback: query individual
        return $this->images()->first();
    }

    /**
     * URL da imagem principal
     *
     * Usa subdomínio img.deepfreeze.com.br para servir imagens do legado.
     * Path no legado: /app/webroot/img/pratos/big/{imagem_src}
     * URL final: https://img.deepfreeze.com.br/pratos/big/{imagem_src}
     */
    public function getMainImageUrl(string $size = 'medium'): string
    {
        $image = $this->getMainImage();

        if ($image && $image->imagem_src) {
            $baseUrl = rtrim(config('legacy.image_base_url'), '/');
            $imagePath = rtrim(config('legacy.image_path'), '/');

            return $baseUrl . $imagePath . '/' . $image->imagem_src;
        }

        // Placeholder quando produto não tem imagem
        return asset('storefront/img/no-image.jpg');
    }

    /**
     * Verifica se o produto está em promoção
     *
     * No legado NÃO existem datas de promoção (start_promotion/end_promotion).
     * A promoção é ativa quando preco_promocional é preenchido e menor que preco.
     *
     * O site antigo usa: strcmp($preco_promocional, "0.00") && $preco_promocional != ""
     */
    public function isOnPromotion(): bool
    {
        $promoPrice = $this->promotional_price;

        if (!$promoPrice || $promoPrice <= 0) {
            return false;
        }

        return $promoPrice < $this->price;
    }

    /**
     * Preço atual (promocional se ativo, senão normal)
     */
    public function getCurrentPrice(): float
    {
        if ($this->isOnPromotion()) {
            return $this->promotional_price;
        }

        return $this->price;
    }

    /**
     * Preço original (sem promoção)
     */
    public function getOriginalPrice(): float
    {
        return $this->price;
    }

    /**
     * Percentual de desconto (inteiro arredondado)
     */
    public function getDiscountPercentage(): int
    {
        if (!$this->isOnPromotion() || $this->price <= 0) {
            return 0;
        }

        $discount = (($this->price - $this->promotional_price) / $this->price) * 100;
        return (int) round($discount);
    }

    /**
     * Formata preço para exibição (ex: "R$ 12,50")
     */
    public static function formatPrice(float $value): string
    {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }

    /**
     * Preço atual formatado para exibição no Blade
     */
    public function getFormattedPriceAttribute(): string
    {
        return self::formatPrice($this->getCurrentPrice());
    }

    /**
     * Preço original formatado para exibição no Blade
     */
    public function getFormattedOriginalPriceAttribute(): string
    {
        return self::formatPrice($this->getOriginalPrice());
    }

    /**
     * Verifica se o produto está disponível para venda
     *
     * No legado: ativo + tem estoque > 0
     * (não existe campo 'available' separado)
     */
    public function isAvailable(): bool
    {
        return $this->active && $this->stock > 0;
    }

    /**
     * Verifica se é um kit/combo/pacote
     */
    public function isKit(): bool
    {
        return $this->is_package || $this->is_combo;
    }

    // ==================== SCOPES ====================
    // Scopes usam nomes REAIS das colunas do banco legado

    /**
     * Scope: Apenas produtos ativos
     * Coluna legado: ativo (tinyint)
     */
    public function scopeActive($query)
    {
        return $query->where('ativo', 1);
    }

    /**
     * Scope: Produtos com pelo menos uma imagem ativa
     */
    public function scopeWithImage($query)
    {
        return $query->whereHas('images');
    }

    /**
     * Scope: Produtos disponíveis no canal de vendas Internet (canais_venda_id = 1).
     *
     * No legado, a tabela canais_vendas_produtos controla quais produtos
     * aparecem em cada canal. O site usa canal_venda_id = 1 (Internet).
     * Filtra por data_inicial <= agora E (data_final >= agora OU data_final IS NULL).
     */
    public function scopeAvailableOnline($query)
    {
        return $query->whereIn('produtos.id', function ($sub) {
            $sub->select('produto_id')
                ->from('canais_vendas_produtos')
                ->where('canais_venda_id', 1)
                ->where('data_inicial', '<=', now())
                ->where(function ($q) {
                    $q->whereNull('data_final')
                      ->orWhere('data_final', '>=', now());
                });
        });
    }

    /**
     * Scope: Produtos visíveis na loja (ativos + com imagem + canal Internet)
     *
     * Replica o filtro completo do legado:
     * - ativo = 1
     * - Possui imagem ativa
     * - Cadastrado no canal de vendas Internet (canais_venda_id = 1) com datas válidas
     */
    public function scopeVisibleInStore($query)
    {
        return $query->active()->withImage()->availableOnline();
    }

    /**
     * Scope: Adiciona quantidade de estoque como subquery
     *
     * Evita N+1 queries ao calcular estoque na listagem.
     * O accessor getStockAttribute() lê o valor de '_stock'.
     *
     * Uso: Product::withStockQuantity()->visibleInStore()->get()
     */
    public function scopeWithStockQuantity($query)
    {
        return $query->select('produtos.*')
            ->selectRaw(
                '(SELECT COALESCE(SUM(estoque_atual_calculado - giro_balcao), 0)
                  FROM otm_estoques_lojas
                  WHERE otm_estoques_lojas.produto_id = produtos.id) as _stock'
            );
    }

    /**
     * Scope: Produtos em promoção
     * No legado, preco e preco_promocional são varchar - precisa CAST
     */
    public function scopeOnPromotion($query)
    {
        return $query->whereNotNull('preco_promocional')
                     ->where('preco_promocional', '!=', '')
                     ->where('preco_promocional', '!=', '0.00')
                     ->whereRaw('CAST(preco_promocional AS DECIMAL(10,2)) < CAST(preco AS DECIMAL(10,2))');
    }

    /**
     * Scope: Ordenação por preço (com tratamento de varchar→decimal)
     *
     * Como preco/preco_promocional são varchar no legado,
     * a ordenação precisa de CAST para funcionar corretamente.
     */
    public function scopeOrderByPrice($query, string $direction = 'asc')
    {
        // Sanitiza direction para evitar SQL injection
        $direction = strtolower($direction) === 'desc' ? 'DESC' : 'ASC';

        return $query->orderByRaw("
            CASE
                WHEN preco_promocional IS NOT NULL
                     AND preco_promocional != ''
                     AND preco_promocional != '0.00'
                     AND CAST(preco_promocional AS DECIMAL(10,2)) > 0
                     AND CAST(preco_promocional AS DECIMAL(10,2)) < CAST(preco AS DECIMAL(10,2))
                THEN CAST(preco_promocional AS DECIMAL(10,2))
                ELSE CAST(preco AS DECIMAL(10,2))
            END {$direction}
        ");
    }
}
