<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Endereço de Regra de Entrega (tabela 'enderecos_regras_entregas' do banco legado)
 *
 * Ponto de origem alternativo para cálculo de distância.
 * Usado quando a entrega não parte de uma loja cadastrada
 * (ex: Centro de Distribuição, pedágio).
 *
 * Tabela: novo.enderecos_regras_entregas
 * Engine: MyISAM | 1 registro (Pedágio Ponte Rio-Niterói)
 */
class EnderecoRegraEntrega extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'enderecos_regras_entregas';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    protected $casts = [
        'latitude'  => 'double',
        'longitude' => 'double',
    ];
}
