<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Model para banners duplos exibidos na home
 *
 * Cada registro representa um PAR de banners (esquerdo e direito)
 * exibidos lado a lado na página inicial.
 *
 * @property int $id
 * @property int $order - Ordem de exibição
 * @property string $left_image_path - Caminho da imagem esquerda (670x380px)
 * @property string|null $left_link - URL do banner esquerdo
 * @property string|null $left_alt_text - Alt text do banner esquerdo
 * @property \Carbon\Carbon|null $left_start_date - Início da exibição do banner esquerdo
 * @property \Carbon\Carbon|null $left_end_date - Fim da exibição do banner esquerdo
 * @property string $right_image_path - Caminho da imagem direita (670x380px)
 * @property string|null $right_link - URL do banner direito
 * @property string|null $right_alt_text - Alt text do banner direito
 * @property \Carbon\Carbon|null $right_start_date - Início da exibição do banner direito
 * @property \Carbon\Carbon|null $right_end_date - Fim da exibição do banner direito
 * @property bool $active - Status ativo/inativo
 */
class DualBanner extends Model
{
    use HasFactory;

    protected $fillable = [
        'order',
        'left_image_path',
        'left_link',
        'left_alt_text',
        'left_start_date',
        'left_end_date',
        'right_image_path',
        'right_link',
        'right_alt_text',
        'right_start_date',
        'right_end_date',
        'active',
    ];

    protected $casts = [
        'order' => 'integer',
        'left_start_date' => 'date',
        'left_end_date' => 'date',
        'right_start_date' => 'date',
        'right_end_date' => 'date',
        'active' => 'boolean',
    ];

    /**
     * Scope para buscar apenas banners ativos
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope para ordenar por ordem de exibição
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    /**
     * Scope para buscar banners visíveis (ativos e dentro do período)
     */
    public function scopeVisible($query)
    {
        $today = Carbon::today();

        return $query->active()
            ->where(function ($q) use ($today) {
                // Banner esquerdo visível
                $q->where(function ($subQ) use ($today) {
                    $subQ->where(function ($dateQ) use ($today) {
                        $dateQ->whereNull('left_start_date')
                            ->orWhere('left_start_date', '<=', $today);
                    })
                    ->where(function ($dateQ) use ($today) {
                        $dateQ->whereNull('left_end_date')
                            ->orWhere('left_end_date', '>=', $today);
                    });
                })
                // OU Banner direito visível (pelo menos um deve estar visível)
                ->orWhere(function ($subQ) use ($today) {
                    $subQ->where(function ($dateQ) use ($today) {
                        $dateQ->whereNull('right_start_date')
                            ->orWhere('right_start_date', '<=', $today);
                    })
                    ->where(function ($dateQ) use ($today) {
                        $dateQ->whereNull('right_end_date')
                            ->orWhere('right_end_date', '>=', $today);
                    });
                });
            });
    }

    /**
     * Verifica se o banner esquerdo está visível no momento
     *
     * @return bool
     */
    public function isLeftVisible(): bool
    {
        $today = Carbon::today();

        $startOk = is_null($this->left_start_date) || $this->left_start_date->lte($today);
        $endOk = is_null($this->left_end_date) || $this->left_end_date->gte($today);

        return $startOk && $endOk;
    }

    /**
     * Verifica se o banner direito está visível no momento
     *
     * @return bool
     */
    public function isRightVisible(): bool
    {
        $today = Carbon::today();

        $startOk = is_null($this->right_start_date) || $this->right_start_date->lte($today);
        $endOk = is_null($this->right_end_date) || $this->right_end_date->gte($today);

        return $startOk && $endOk;
    }

    /**
     * Retorna a URL completa da imagem esquerda
     *
     * @return string
     */
    public function getLeftImageUrl(): string
    {
        return asset('storage/' . $this->left_image_path);
    }

    /**
     * Retorna a URL completa da imagem direita
     *
     * @return string
     */
    public function getRightImageUrl(): string
    {
        return asset('storage/' . $this->right_image_path);
    }
}
