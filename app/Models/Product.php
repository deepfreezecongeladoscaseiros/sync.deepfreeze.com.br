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
}
