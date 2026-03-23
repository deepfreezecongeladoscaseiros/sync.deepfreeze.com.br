<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Logradouro/CEP (tabela 'logradouros' do banco legado)
 *
 * Base de CEPs do Brasil. CEPs armazenados SEM hífen (ex: '20551030').
 * Contém coordenadas GPS (latitude/longitude) para cálculo de distância.
 *
 * Tabela: novo.logradouros
 * Engine: MyISAM | ~282k registros
 */
class Logradouro extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'logradouros';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    public $timestamps = false;

    // ==================== SCOPES ====================

    /**
     * Busca por CEP (remove hífen automaticamente)
     */
    public function scopeByCep($query, string $cep)
    {
        $cepLimpo = preg_replace('/\D/', '', $cep);
        return $query->where('CEP', $cepLimpo);
    }
}
