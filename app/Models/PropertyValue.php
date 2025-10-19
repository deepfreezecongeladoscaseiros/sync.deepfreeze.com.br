<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'name',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_property_value');
    }
}
