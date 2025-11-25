<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Model para gerenciar banners hero (principais) da home
 *
 * Banners podem ter período de exibição definido e são ordenados.
 * Suporta imagens diferentes para desktop e mobile.
 *
 * @property int $id
 * @property string $image_desktop - Caminho da imagem desktop (1400x385px)
 * @property string $image_mobile - Caminho da imagem mobile (766x981px)
 * @property string|null $link - URL de destino
 * @property string $alt_text - Texto alternativo
 * @property Carbon|null $start_date - Data de início
 * @property Carbon|null $end_date - Data de fim (null = eterno)
 * @property int $order - Ordem de exibição (menor primeiro)
 * @property bool $active - Ativo/inativo
 */
class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'image_desktop',
        'image_mobile',
        'link',
        'alt_text',
        'start_date',
        'end_date',
        'order',
        'active',
    ];

    /**
     * Casts para datas e boolean
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Scope para buscar apenas banners ativos
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope para buscar banners que devem ser exibidos agora
     *
     * Considera:
     * - Banner ativo
     * - Data de início já passou (ou null)
     * - Data de fim não passou ainda (ou null)
     */
    public function scopeVisible($query)
    {
        $now = Carbon::now();

        return $query->where('active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('start_date')
                  ->orWhere('start_date', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', $now);
            });
    }

    /**
     * Scope para ordenar por ordem de exibição
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc')->orderBy('id', 'asc');
    }

    /**
     * Verifica se o banner está visível no momento atual
     *
     * @return bool
     */
    public function isVisible(): bool
    {
        if (!$this->active) {
            return false;
        }

        $now = Carbon::now();

        // Verifica data de início
        if ($this->start_date && $this->start_date->gt($now)) {
            return false;
        }

        // Verifica data de fim
        if ($this->end_date && $this->end_date->lt($now)) {
            return false;
        }

        return true;
    }

    /**
     * Retorna URL completa da imagem desktop
     *
     * @return string
     */
    public function getDesktopImageUrl(): string
    {
        return asset('storage/' . $this->image_desktop);
    }

    /**
     * Retorna URL completa da imagem mobile
     *
     * @return string
     */
    public function getMobileImageUrl(): string
    {
        return asset('storage/' . $this->image_mobile);
    }

    /**
     * Retorna status de exibição (ativo, agendado, expirado)
     *
     * @return string
     */
    public function getStatusLabel(): string
    {
        if (!$this->active) {
            return 'Inativo';
        }

        $now = Carbon::now();

        if ($this->start_date && $this->start_date->gt($now)) {
            return 'Agendado';
        }

        if ($this->end_date && $this->end_date->lt($now)) {
            return 'Expirado';
        }

        return 'Ativo';
    }
}
