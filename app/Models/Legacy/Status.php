<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Catálogo de Status (tabela 'status' do banco legado)
 *
 * Tabela de lookup com os possíveis status de um pedido.
 * Apenas leitura — os valores são gerenciados pelo SIV.
 *
 * Tabela: novo.status
 * Engine: MyISAM
 * Charset: utf8mb3
 */
class Status extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'status';

    public $timestamps = false;

    protected $columnMap = [
        'name'   => 'nome',
        'active' => 'ativo',
    ];

    public function getAttribute($key)
    {
        if (isset($this->columnMap[$key])) {
            return parent::getAttribute($this->columnMap[$key]);
        }

        return parent::getAttribute($key);
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('ativo', 1);
    }
}
