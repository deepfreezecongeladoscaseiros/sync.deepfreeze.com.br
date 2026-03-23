<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Desconto Aplicado ao Pedido (tabela 'pedidos_descontos' do banco legado)
 *
 * Registra descontos/cupons efetivamente aplicados a um pedido.
 * Um pedido pode ter múltiplos descontos (se cumulativos).
 *
 * Tabela: novo.pedidos_descontos
 * Engine: MyISAM | ~76k registros
 *
 * Tipos comuns (campo 'tipo'):
 * - CUPOM: código promocional inserido pelo cliente
 * - COLABORADOR: desconto de funcionário
 * - PERCENTUAL / VALOR: aplicado manualmente
 */
class PedidoDesconto extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'pedidos_descontos';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    protected $fillable = [
        'pedido_id',
        'tipo',
        'codigo_promocional',
        'percentual',
        'valor',
        'valido',
        'cumulativo',
    ];

    protected $casts = [
        'percentual' => 'decimal:2',
        'valor' => 'decimal:2',
        'valido' => 'boolean',
        'cumulativo' => 'boolean',
    ];

    // ==================== RELATIONSHIPS ====================

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Descontos válidos (ativos) de um pedido
     */
    public function scopeValidForOrder($query, int $pedidoId)
    {
        return $query->where('pedido_id', $pedidoId)
            ->where('valido', 1);
    }

    /**
     * Scope: Descontos não-cumulativos
     */
    public function scopeNonCumulative($query)
    {
        return $query->where('cumulativo', 0);
    }
}
