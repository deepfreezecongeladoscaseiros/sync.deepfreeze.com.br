<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model para blocos informativos exibidos na home
 *
 * Bloco com imagem grande + título + subtítulo
 * Exemplo: seção "Refeições Saudáveis"
 */
class InfoBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'order',
        'image_path',
        'image_alt',
        'title',
        'subtitle',
        'background_color',
        'active',
    ];

    protected $casts = [
        'order' => 'integer',
        'active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    public function getImageUrl(): string
    {
        return asset('storage/' . $this->image_path);
    }
}
