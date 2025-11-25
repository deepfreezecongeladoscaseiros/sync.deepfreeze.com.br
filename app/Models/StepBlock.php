<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StepBlock extends Model
{
    protected $fillable = [
        'order', 'active',
        'item_1_icon_path', 'item_1_title', 'item_1_description', 'item_1_alt',
        'item_2_icon_path', 'item_2_title', 'item_2_description', 'item_2_alt',
        'item_3_icon_path', 'item_3_title', 'item_3_description', 'item_3_alt',
        'item_4_icon_path', 'item_4_title', 'item_4_description', 'item_4_alt',
    ];

    protected $casts = ['order' => 'integer', 'active' => 'boolean'];

    public function scopeActive($query) { return $query->where('active', true); }
    public function scopeOrdered($query) { return $query->orderBy('order'); }
    
    public function getIconUrl($item) { 
        $path = $this->{"item_{$item}_icon_path"};
        return $path ? asset('storage/' . $path) : null; 
    }
}
