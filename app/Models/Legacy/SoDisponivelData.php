<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Datas Exclusivas por Veículo (tabela 'so_disponiveis_datas')
 *
 * Whitelist: se um veiculo_periodo_id tem registros nesta tabela,
 * ele SÓ opera nas datas listadas. Qualquer outra data é removida.
 * Datas desta tabela são tratadas como feriado (horário de sábado na retirada).
 *
 * Tabela: novo.so_disponiveis_datas
 * Engine: MyISAM | ~5k registros
 */
class SoDisponivelData extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'so_disponiveis_datas';

    public $timestamps = false;

    protected $casts = [
        'data_disponivel' => 'date',
    ];

    // ==================== SCOPES ====================

    /**
     * Filtra registros futuros (a partir de hoje)
     */
    public function scopeFuture($query)
    {
        return $query->where('data_disponivel', '>=', now()->toDateString());
    }

    /**
     * Filtra por lista de veiculo_periodo_ids
     */
    public function scopeForVeiculoPeriodos($query, array $ids)
    {
        return $query->whereIn('veiculo_periodo_id', $ids);
    }
}
