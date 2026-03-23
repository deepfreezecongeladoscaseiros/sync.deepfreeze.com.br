<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Região de Entrega (tabela 'entregas_regioes' do banco legado)
 *
 * Define regiões geográficas para entrega (ex: "Tijuca", "Copacabana").
 * Cada região é atendida por uma ou mais lojas.
 *
 * Tabela: novo.entregas_regioes
 * Engine: MyISAM | ~71 registros
 */
class EntregaRegiao extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'entregas_regioes';

    public $timestamps = false;

    // ==================== RELATIONSHIPS ====================

    public function periodos()
    {
        return $this->hasMany(EntregaPeriodo::class, 'entregas_regiao_id');
    }
}
