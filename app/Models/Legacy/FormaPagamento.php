<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Forma de Pagamento (tabela 'formas_pagamentos' do banco legado)
 *
 * Catálogo de formas de pagamento disponíveis no sistema.
 * Apenas leitura — gerenciado pelo SIV.
 *
 * Tabela: novo.formas_pagamentos
 * Engine: MyISAM
 * Charset: utf8mb3
 *
 * Campos de tipo (boolean):
 * - online: processado por gateway (Cielo, Rede)
 * - cheque: pagamento com cheque
 * - dinheiro: pagamento em dinheiro/espécie
 * - debito: cartão de débito
 * - cartao_debito_ou_credito: aceita cartão (débito ou crédito)
 * - rede_credito / rede_debito: processado pela Rede
 */
class FormaPagamento extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'formas_pagamentos';

    public $timestamps = false;

    protected $casts = [
        'ativo' => 'boolean',
        'online' => 'boolean',
        'cheque' => 'boolean',
        'dinheiro' => 'boolean',
        'debito' => 'boolean',
        'cartao_debito_ou_credito' => 'boolean',
        'rede_credito' => 'boolean',
        'rede_debito' => 'boolean',
    ];

    protected $columnMap = [
        'name'       => 'nome',
        'code'       => 'codigo',
        'active'     => 'ativo',
        'is_online'  => 'online',
        'brand'      => 'bandeira',
        'icon'       => 'icone',
        'is_cash'    => 'dinheiro',
        'is_check'   => 'cheque',
        'is_debit'   => 'debito',
        'is_card'    => 'cartao_debito_ou_credito',
    ];

    public function getAttribute($key)
    {
        if (isset($this->columnMap[$key])) {
            return parent::getAttribute($this->columnMap[$key]);
        }

        return parent::getAttribute($key);
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Formas ativas
     */
    public function scopeActive($query)
    {
        return $query->where('ativo', 1);
    }

    /**
     * Scope: Formas disponíveis online (site)
     */
    public function scopeOnline($query)
    {
        return $query->where('online', 1);
    }
}
