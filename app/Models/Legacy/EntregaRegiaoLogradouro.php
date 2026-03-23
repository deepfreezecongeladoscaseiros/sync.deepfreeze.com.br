<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Associação Região × Logradouro (tabela 'entregas_regioes_logradouros')
 *
 * Mapeia quais logradouros (CEPs) pertencem a qual região de entrega.
 * Tabela pivô entre entregas_regioes e logradouros.
 *
 * Tabela: novo.entregas_regioes_logradouros
 * Engine: MyISAM | ~20M registros
 */
class EntregaRegiaoLogradouro extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'entregas_regioes_logradouros';

    public $timestamps = false;

    // ==================== RELATIONSHIPS ====================

    public function regiao()
    {
        return $this->belongsTo(EntregaRegiao::class, 'entregas_regioes_id');
    }

    public function logradouro()
    {
        return $this->belongsTo(Logradouro::class, 'logradouros_id');
    }
}
