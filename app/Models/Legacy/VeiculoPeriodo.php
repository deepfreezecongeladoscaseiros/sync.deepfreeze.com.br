<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Veículo × Período (tabela 'veiculos_periodos' do banco legado)
 *
 * Alocação de veículos em períodos de entrega com condutor específico.
 * Vincula um veículo + condutor + período + loja.
 *
 * Tabela: novo.veiculos_periodos
 * Engine: MyISAM | ~29k registros
 */
class VeiculoPeriodo extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'veiculos_periodos';

    public $timestamps = false;

    // ==================== RELATIONSHIPS ====================

    public function entregaPeriodo()
    {
        return $this->belongsTo(EntregaPeriodo::class, 'entregas_periodo_id');
    }

    public function loja()
    {
        return $this->belongsTo(Loja::class, 'loja_id');
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('ativo', 1);
    }
}
