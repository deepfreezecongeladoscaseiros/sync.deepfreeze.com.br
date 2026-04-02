<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Log de consultas de CEP
 *
 * Registra consultas de "Entrega na minha região" no storefront.
 * Banco: sync (tabela cep_queries_log).
 * Sem updated_at — registros são imutáveis (apenas INSERT).
 */
class CepQueryLog extends Model
{
    public $timestamps = false;

    protected $table = 'cep_queries_log';

    protected $fillable = [
        'cep',
        'atendido',
        'estado',
        'cidade',
        'bairro',
        'regiao_id',
        'loja_id',
        'created_at',
    ];

    protected $casts = [
        'atendido'   => 'boolean',
        'created_at' => 'datetime',
    ];
}
