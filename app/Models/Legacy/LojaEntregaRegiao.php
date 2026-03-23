<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Associação Loja × Região de Entrega (tabela 'lojas_entregas_regioes')
 *
 * Define qual loja atende qual região de entrega.
 *
 * Tabela: novo.lojas_entregas_regioes
 * Engine: MyISAM
 */
class LojaEntregaRegiao extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'lojas_entregas_regioes';

    public $timestamps = false;

    // ==================== RELATIONSHIPS ====================

    public function loja()
    {
        return $this->belongsTo(Loja::class, 'loja_id');
    }

    public function regiao()
    {
        return $this->belongsTo(EntregaRegiao::class, 'entregas_regiao_id');
    }
}
