<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SingleBanner extends Model
{
    /**
     * Campos permitidos para mass assignment
     * Inclui todos os campos editáveis do banner
     */
    protected $fillable = [
        'order',
        'desktop_image_path',
        'mobile_image_path',
        'link',
        'alt_text',
        'start_date',
        'end_date',
        'active',
    ];

    /**
     * Casting de tipos
     * Garante que datas e boolean sejam tratados corretamente
     */
    protected $casts = [
        'order' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'active' => 'boolean',
    ];

    /**
     * Scope: retorna apenas banners ativos
     * Uso: SingleBanner::active()->get()
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope: retorna banners ordenados por 'order' (crescente)
     * Uso: SingleBanner::ordered()->get()
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Scope: retorna apenas banners visíveis (ativos + dentro do período)
     * Considera: active=true E data atual entre start_date e end_date (se definidas)
     * Uso: SingleBanner::visible()->get()
     */
    public function scopeVisible($query)
    {
        $today = Carbon::today();

        return $query->active()
            ->where(function ($q) use ($today) {
                $q->where(function ($dateQuery) use ($today) {
                    // start_date é null OU está no passado/hoje
                    $dateQuery->whereNull('start_date')
                        ->orWhere('start_date', '<=', $today);
                })
                ->where(function ($dateQuery) use ($today) {
                    // end_date é null OU está no futuro/hoje
                    $dateQuery->whereNull('end_date')
                        ->orWhere('end_date', '>=', $today);
                });
            });
    }

    /**
     * Retorna URL completa da imagem desktop
     * @return string
     */
    public function getDesktopImageUrl(): string
    {
        return $this->desktop_image_path
            ? asset('storage/' . $this->desktop_image_path)
            : asset('images/placeholder-banner.png');
    }

    /**
     * Retorna URL completa da imagem mobile
     * @return string
     */
    public function getMobileImageUrl(): string
    {
        return $this->mobile_image_path
            ? asset('storage/' . $this->mobile_image_path)
            : asset('images/placeholder-banner-mobile.png');
    }

    /**
     * Verifica se o banner está dentro do período de exibição
     * @return bool
     */
    public function isWithinDateRange(): bool
    {
        $today = Carbon::today();

        $afterStart = !$this->start_date || $this->start_date->lte($today);
        $beforeEnd = !$this->end_date || $this->end_date->gte($today);

        return $afterStart && $beforeEnd;
    }
}
