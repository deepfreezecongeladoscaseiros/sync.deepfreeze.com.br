<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Datas Indisponíveis por Veículo (tabela 'nao_disponiveis_datas')
 *
 * Blacklist: se existe registro para (veiculo_periodo_id, data),
 * aquele slot de entrega é removido naquela data específica.
 *
 * Tabela: novo.nao_disponiveis_datas
 * Engine: MyISAM | ~61k registros
 */
class NaoDisponivelData extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'nao_disponiveis_datas';

    public $timestamps = false;

    protected $casts = [
        'data_nao_disponivel' => 'date',
    ];

    // ==================== SCOPES ====================

    /**
     * Filtra registros futuros (a partir de hoje)
     */
    public function scopeFuture($query)
    {
        return $query->where('data_nao_disponivel', '>=', now()->toDateString());
    }

    /**
     * Filtra por lista de veiculo_periodo_ids
     */
    public function scopeForVeiculoPeriodos($query, array $ids)
    {
        return $query->whereIn('veiculo_periodo_id', $ids);
    }
}
