<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tray_id',
    ];

    public function values()
    {
        return $this->hasMany(PropertyValue::class);
    }
}
