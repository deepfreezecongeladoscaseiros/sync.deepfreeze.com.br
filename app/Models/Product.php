<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'legacy_id',
        'tray_id',
        'sku',
        'ean',
        'name',
        'ncm',
        'description',
        'description_small',
        'presentation',
        'properties',
        'benefits',
        'chef_tips',
        'dish_history',
        'ingredients',
        'consumption_instructions',
        'price',
        'cost_price',
        'promotional_price',
        'start_promotion',
        'end_promotion',
        'ipi_value',
        'brand_id',
        'manufacturer_id',
        'model',
        'weight',
        'gross_weight',
        'weight_unit',
        'length',
        'width',
        'height',
        'stock',
        'shelf_life_days',
        'portion_size',
        'home_measure',
        'contains_gluten',
        'lactose_free',
        'low_lactose',
        'contains_lactose',
        'allergens',
        'alcoholic_beverage',
        'label_description',
        'description_english',
        'freezing_time',
        'category_id',
        'available',
        'available_in_store',
        'availability',
        'availability_days',
        'reference',
        'hot',
        'release',
        'additional_button',
        'related_categories',
        'release_date',
        'virtual_product',
        'active',
        'is_package',
        'is_combo',
        'is_gift_card',
        'made_to_order',
        'order_deadline',
        'background_color',
        'text_color',
        'display_order',
        'ifood_percentage',
        'ifood_promotion_percentage',
    ];

    protected $casts = [
        'contains_gluten' => 'boolean',
        'lactose_free' => 'boolean',
        'low_lactose' => 'boolean',
        'contains_lactose' => 'boolean',
        'alcoholic_beverage' => 'boolean',
        'available' => 'boolean',
        'available_in_store' => 'boolean',
        'hot' => 'boolean',
        'release' => 'boolean',
        'additional_button' => 'boolean',
        'virtual_product' => 'boolean',
        'active' => 'boolean',
        'is_package' => 'boolean',
        'is_combo' => 'boolean',
        'is_gift_card' => 'boolean',
        'made_to_order' => 'boolean',
        'start_promotion' => 'date',
        'end_promotion' => 'date',
        'release_date' => 'date',
        'order_deadline' => 'date',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variations()
    {
        return $this->hasMany(Variant::class, 'product_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }

    public function metatags()
    {
        return $this->morphMany(Metatag::class, 'metatagable');
    }

    public function propertyValues()
    {
        return $this->belongsToMany(PropertyValue::class, 'product_property_value');
    }

    public function nutritionalInfo()
    {
        return $this->hasOne(ProductNutritionalInfo::class);
    }

    // ==================== HELPERS ====================

    /**
     * Gera o slug do produto baseado no nome
     * @return string
     */
    public function getSlugAttribute(): string
    {
        return \Illuminate\Support\Str::slug($this->name);
    }

    /**
     * Retorna a URL do produto
     * @return string
     */
    public function getUrlAttribute(): string
    {
        // Se pertence a uma categoria, inclui o slug da categoria
        if ($this->category) {
            return url("/{$this->category->slug}/{$this->slug}");
        }
        return url("/produto/{$this->slug}");
    }

    /**
     * Retorna a imagem principal do produto
     *
     * Otimização: se a relação 'images' já foi carregada via eager loading,
     * usa a collection em memória ao invés de fazer novas queries.
     * Antes: 2 queries por produto (busca main + fallback)
     * Depois: 0 queries extras quando eager loaded, ou 1 query quando não
     *
     * @return ProductImage|null
     */
    public function getMainImage(): ?ProductImage
    {
        // Se as imagens já foram carregadas via eager loading, filtra na collection
        if ($this->relationLoaded('images')) {
            $images = $this->images;
            return $images->firstWhere('is_main', true) ?? $images->first();
        }

        // Fallback: query única ordenando por is_main DESC para priorizar a principal
        return $this->images()
            ->orderByDesc('is_main')
            ->first();
    }

    /**
     * Retorna a URL da imagem principal
     * @param string $size Tamanho da imagem (thumb, medium, large)
     * @return string
     */
    public function getMainImageUrl(string $size = 'medium'): string
    {
        $image = $this->getMainImage();

        if ($image && $image->path) {
            // Se é URL externa (CDN), retorna direto
            if (str_starts_with($image->path, 'http')) {
                return $image->path;
            }
            // Se é path local
            return asset('storage/' . $image->path);
        }

        // Placeholder caso não tenha imagem
        return asset('storefront/img/no-image.jpg');
    }

    /**
     * Verifica se o produto está em promoção
     * @return bool
     */
    public function isOnPromotion(): bool
    {
        if (!$this->promotional_price || $this->promotional_price <= 0) {
            return false;
        }

        $today = now()->startOfDay();

        // Verifica período da promoção
        if ($this->start_promotion && $today->lt($this->start_promotion)) {
            return false;
        }

        if ($this->end_promotion && $today->gt($this->end_promotion)) {
            return false;
        }

        return $this->promotional_price < $this->price;
    }

    /**
     * Retorna o preço atual (considerando promoção)
     * @return float
     */
    public function getCurrentPrice(): float
    {
        if ($this->isOnPromotion()) {
            return (float) $this->promotional_price;
        }
        return (float) $this->price;
    }

    /**
     * Retorna o preço original (sem promoção)
     * @return float
     */
    public function getOriginalPrice(): float
    {
        return (float) $this->price;
    }

    /**
     * Calcula o percentual de desconto
     * @return int
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
     * Formata preço para exibição
     * @param float $value
     * @return string
     */
    public static function formatPrice(float $value): string
    {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }

    /**
     * Retorna o preço atual formatado
     * @return string
     */
    public function getFormattedPriceAttribute(): string
    {
        return self::formatPrice($this->getCurrentPrice());
    }

    /**
     * Retorna o preço original formatado
     * @return string
     */
    public function getFormattedOriginalPriceAttribute(): string
    {
        return self::formatPrice($this->getOriginalPrice());
    }

    /**
     * Verifica se o produto está disponível para venda
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->active && $this->available && $this->stock > 0;
    }

    /**
     * Verifica se é um kit/combo
     * @return bool
     */
    public function isKit(): bool
    {
        return $this->is_package || $this->is_combo;
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Apenas produtos ativos
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope: Apenas produtos disponíveis
     */
    public function scopeAvailable($query)
    {
        return $query->where('active', true)
                     ->where('available', true)
                     ->where('stock', '>', 0);
    }

    /**
     * Scope: Apenas produtos com imagem
     */
    public function scopeWithImage($query)
    {
        return $query->whereHas('images');
    }

    /**
     * Scope: Produtos visíveis na loja (ativos + com imagem)
     */
    public function scopeVisibleInStore($query)
    {
        return $query->active()->withImage();
    }

    /**
     * Scope: Produtos em promoção
     */
    public function scopeOnPromotion($query)
    {
        $today = now()->startOfDay();

        return $query->whereNotNull('promotional_price')
                     ->where('promotional_price', '>', 0)
                     ->where('promotional_price', '<', \DB::raw('price'))
                     ->where(function ($q) use ($today) {
                         $q->whereNull('start_promotion')
                           ->orWhere('start_promotion', '<=', $today);
                     })
                     ->where(function ($q) use ($today) {
                         $q->whereNull('end_promotion')
                           ->orWhere('end_promotion', '>=', $today);
                     });
    }

    /**
     * Scope: Ordenação por preço
     */
    public function scopeOrderByPrice($query, string $direction = 'asc')
    {
        return $query->orderByRaw("
            CASE
                WHEN promotional_price IS NOT NULL
                     AND promotional_price > 0
                     AND promotional_price < price
                     AND (start_promotion IS NULL OR start_promotion <= NOW())
                     AND (end_promotion IS NULL OR end_promotion >= NOW())
                THEN promotional_price
                ELSE price
            END {$direction}
        ");
    }
}
