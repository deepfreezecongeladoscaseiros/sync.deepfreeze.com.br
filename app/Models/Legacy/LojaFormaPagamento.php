<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Associação Loja × Forma de Pagamento (tabela 'lojas_formas_pagamentos' do banco legado)
 *
 * Define quais formas de pagamento estão ativas para cada loja,
 * separando por canal (site vs televendas).
 *
 * Tabela: novo.lojas_formas_pagamentos
 * Engine: MyISAM
 * Charset: utf8mb3
 */
class LojaFormaPagamento extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'lojas_formas_pagamentos';

    public $timestamps = false;

    protected $fillable = [
        'loja_id',
        'formas_pagamento_id',
        'taxas',
        'taxa_fixa',
        'dias_deposito',
        'dados_bancarios',
        'ativa_site',
        'ativa_televendas',
    ];

    protected $casts = [
        'ativa_site' => 'boolean',
        'ativa_televendas' => 'boolean',
        'taxas' => 'decimal:2',
        'taxa_fixa' => 'decimal:2',
    ];

    // ==================== RELATIONSHIPS ====================

    public function loja()
    {
        return $this->belongsTo(Loja::class, 'loja_id');
    }

    public function formaPagamento()
    {
        return $this->belongsTo(FormaPagamento::class, 'formas_pagamento_id');
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Formas ativas no site para uma loja específica
     */
    public function scopeActiveSiteForStore($query, int $lojaId)
    {
        return $query->where('loja_id', $lojaId)
            ->where('ativa_site', 1)
            ->whereNull('deleted');
    }
}
