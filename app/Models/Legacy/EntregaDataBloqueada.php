<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Datas Bloqueadas para Entrega (tabela 'entregas_datas_bloqueadas')
 *
 * Feriados e datas sem entrega por loja.
 *
 * Tabela: novo.entregas_datas_bloqueadas
 * Engine: MyISAM | ~112 registros
 */
class EntregaDataBloqueada extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'entregas_datas_bloqueadas';

    public $timestamps = false;

    protected $casts = [
        'data' => 'date',
    ];

    // ==================== SCOPES ====================

    /**
     * Datas bloqueadas para uma loja (ou globais).
     * No legado, loja_id = 0 indica bloqueio global (todas as lojas).
     */
    public function scopeForStore($query, int $lojaId)
    {
        return $query->where(function ($q) use ($lojaId) {
            $q->where('loja_id', $lojaId)
              ->orWhere('loja_id', 0)
              ->orWhereNull('loja_id');
        });
    }

    /**
     * Datas bloqueadas futuras
     */
    public function scopeFuture($query)
    {
        return $query->where('data', '>=', now()->toDateString());
    }
}
