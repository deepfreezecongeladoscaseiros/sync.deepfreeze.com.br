<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Metatag extends Model
{
    use HasFactory;

    protected $fillable = [
        'metatagable_id',
        'metatagable_type',
        'type',
        'content',
        'local',
    ];

    public function metatagable()
    {
        return $this->morphTo();
    }
}
