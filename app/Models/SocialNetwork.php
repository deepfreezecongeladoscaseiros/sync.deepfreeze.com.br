<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Model para Redes Sociais
 *
 * Gerencia as redes sociais exibidas no topo e rodapé do site.
 * Cada rede social tem um ícone, link e ordem de exibição.
 */
class SocialNetwork extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'icon_path',
        'url',
        'order',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Scope: apenas redes sociais ativas
     *
     * Exemplo: SocialNetwork::active()->get()
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope: ordenadas pelo campo 'order'
     *
     * Exemplo: SocialNetwork::ordered()->get()
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    /**
     * Scope: redes sociais visíveis (ativas e ordenadas)
     *
     * Exemplo: SocialNetwork::visible()->get()
     */
    public function scopeVisible($query)
    {
        return $query->active()->ordered();
    }

    /**
     * Retorna a URL completa do ícone da rede social
     *
     * @return string URL do ícone
     */
    public function getIconUrl(): string
    {
        return Storage::url($this->icon_path);
    }

    /**
     * Remove o ícone do storage quando a rede social é deletada
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($socialNetwork) {
            // Remove o ícone do storage
            if ($socialNetwork->icon_path && Storage::disk('public')->exists($socialNetwork->icon_path)) {
                Storage::disk('public')->delete($socialNetwork->icon_path);
            }
        });
    }
}
