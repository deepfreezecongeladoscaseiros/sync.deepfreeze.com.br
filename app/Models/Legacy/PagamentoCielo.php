<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Pagamento Cielo (tabela 'pagamentos_cielo' do banco legado)
 *
 * Registra transações de cartão processadas pela Cielo.
 * O SIV lê esta tabela para conciliação financeira.
 *
 * Tabela: novo.pagamentos_cielo
 * Engine: MyISAM
 * Charset: utf8mb3
 *
 * Status de pagamento:
 *   2 = Pago/Autorizado
 *   3 = Negado
 *   4 = Expirado
 *   5 = Cancelado
 *   8 = Chargeback
 */
class PagamentoCielo extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'pagamentos_cielo';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    // Status de pagamento (campo status_pagamento)
    const STATUS_PAGO = 2;
    const STATUS_NEGADO = 3;
    const STATUS_EXPIRADO = 4;
    const STATUS_CANCELADO = 5;
    const STATUS_CHARGEBACK = 8;

    protected $fillable = [
        'pedido_id',
        'reais_pago',               // Valor em centavos
        'cielo_id',                  // ID único da transação na Cielo
        'metodo',                    // Método (crédito/débito)
        'bandeira',                  // Bandeira do cartão (código numérico)
        'tid',                       // Transaction ID
        'checkout_cielo_order_number', // Número do pedido no checkout Cielo
        'status_pagamento',          // 2=pago, 3=negado, 4=expirado, 5=cancelado, 8=chargeback
        'json',                      // JSON completo da resposta da Cielo
    ];

    protected $casts = [
        'status_pagamento' => 'integer',
        'reais_pago' => 'integer',
    ];

    protected $columnMap = [
        'order_id'     => 'pedido_id',
        'amount_cents' => 'reais_pago',
        'method'       => 'metodo',
        'brand'        => 'bandeira',
        'status'       => 'status_pagamento',
        'response_json' => 'json',
    ];

    public function getAttribute($key)
    {
        if (isset($this->columnMap[$key])) {
            return parent::getAttribute($this->columnMap[$key]);
        }

        return parent::getAttribute($key);
    }

    // ==================== RELATIONSHIPS ====================

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    // ==================== HELPERS ====================

    /**
     * Verifica se o pagamento foi aprovado
     */
    public function isPaid(): bool
    {
        return $this->status_pagamento === self::STATUS_PAGO;
    }

    /**
     * Valor pago formatado em R$ (converte centavos para reais)
     */
    public function getFormattedAmountAttribute(): string
    {
        $reais = ($this->reais_pago ?? 0) / 100;
        return 'R$ ' . number_format($reais, 2, ',', '.');
    }

    /**
     * Label do status em português
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status_pagamento) {
            self::STATUS_PAGO       => 'Pago',
            self::STATUS_NEGADO     => 'Negado',
            self::STATUS_EXPIRADO   => 'Expirado',
            self::STATUS_CANCELADO  => 'Cancelado',
            self::STATUS_CHARGEBACK => 'Chargeback',
            default                 => 'Pendente',
        };
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Pagamentos aprovados de um pedido
     */
    public function scopePaidForOrder($query, int $pedidoId)
    {
        return $query->where('pedido_id', $pedidoId)
            ->where('status_pagamento', self::STATUS_PAGO);
    }
}
