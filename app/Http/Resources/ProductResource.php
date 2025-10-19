<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'legacy_id' => $this->legacy_id,
            'sku' => $this->sku,
            'name' => $this->name,
            'slug' => \Illuminate\Support\Str::slug($this->name),
            
            'category' => [
                'id' => $this->category?->id,
                'legacy_id' => $this->category?->legacy_id,
                'name' => $this->category?->name,
                'slug' => $this->category?->slug,
            ],
            
            'brand' => [
                'id' => $this->brand?->id,
                'legacy_id' => $this->brand?->legacy_id,
                'name' => $this->brand?->brand,
                'slug' => $this->brand?->slug,
            ],
            
            'manufacturer' => [
                'id' => $this->manufacturer?->id,
                'legacy_id' => $this->manufacturer?->legacy_id,
                'trade_name' => $this->manufacturer?->trade_name,
            ],
            
            'description' => [
                'presentation' => $this->presentation,
                'properties' => $this->properties,
                'benefits' => $this->benefits,
                'chef_tips' => $this->chef_tips,
                'dish_history' => $this->dish_history,
                'ingredients' => $this->ingredients,
                'consumption_instructions' => $this->consumption_instructions,
                'label_description' => $this->label_description,
                'description_english' => $this->description_english,
            ],
            
            'pricing' => [
                'price' => floatval($this->price),
                'promotional_price' => $this->promotional_price ? floatval($this->promotional_price) : null,
                'has_promotion' => !empty($this->promotional_price),
                'discount_percentage' => $this->promotional_price 
                    ? round((($this->price - $this->promotional_price) / $this->price) * 100, 2)
                    : 0,
                'ifood_percentage' => $this->ifood_percentage ? floatval($this->ifood_percentage) : null,
                'ifood_promotion_percentage' => $this->ifood_promotion_percentage ? floatval($this->ifood_promotion_percentage) : null,
            ],
            
            'stock' => [
                'quantity' => $this->stock ?? 0,
                'available' => ($this->stock ?? 0) > 0,
            ],
            
            'weight' => [
                'net' => $this->weight ? floatval($this->weight) : null,
                'gross' => $this->gross_weight ? floatval($this->gross_weight) : null,
                'unit' => $this->weight_unit ?? 'kg',
                'net_grams' => $this->weight ? floatval($this->weight) * 1000 : null,
            ],
            
            'nutritional_info' => [
                'shelf_life_days' => $this->shelf_life_days,
                'portion_size' => $this->portion_size,
                'home_measure' => $this->home_measure,
            ],
            
            'allergens' => [
                'contains_gluten' => (bool) $this->contains_gluten,
                'lactose_free' => (bool) $this->lactose_free,
                'low_lactose' => (bool) $this->low_lactose,
                'contains_lactose' => (bool) $this->contains_lactose,
                'allergens_text' => $this->allergens,
                'is_alcoholic' => (bool) $this->alcoholic_beverage,
            ],
            
            'product_type' => [
                'is_package' => (bool) $this->is_package,
                'is_combo' => (bool) $this->is_combo,
                'is_gift_card' => (bool) $this->is_gift_card,
                'made_to_order' => (bool) $this->made_to_order,
                'order_deadline' => $this->order_deadline,
            ],
            
            'display' => [
                'background_color' => $this->background_color,
                'text_color' => $this->text_color,
                'display_order' => $this->display_order,
            ],
            
            'images' => $this->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'url' => url('storage/' . $image->path),
                    'path' => $image->path,
                    'position' => $image->position,
                    'is_main' => (bool) $image->is_main,
                ];
            })->sortBy('position')->values(),
            
            'main_image' => $this->images->where('is_main', 1)->first() 
                ? url('storage/' . $this->images->where('is_main', 1)->first()->path)
                : null,
            
            'status' => [
                'active' => (bool) $this->active,
                'freezing_time' => $this->freezing_time,
            ],
            
            'timestamps' => [
                'created_at' => $this->created_at?->toIso8601String(),
                'updated_at' => $this->updated_at?->toIso8601String(),
            ],
        ];
    }
}
