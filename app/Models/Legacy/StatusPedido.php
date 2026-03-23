<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Histórico de Status do Pedido (tabela 'status_pedidos' do banco legado)
 *
 * Cada registro representa uma mudança de status de um pedido (auditoria).
 * O SIV usa esta tabela para rastrear o ciclo de vida completo do pedido:
 * criação → separação → entrega → conclusão.
 *
 * Tabela: novo.status_pedidos
 * Engine: MyISAM
 * Charset: utf8mb3
 *
 * Nota: O campo FK é 'statu_id' (sem o 's' — typo do legado).
 */
class StatusPedido extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'status_pedidos';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    protected $fillable = [
        'pessoa_id',
        'pedido_id',
        'statu_id',             // FK para status.id (typo do legado: 'statu' sem 's')
        'observacao',
        'data_entrega',
        'formas_pagamento_id',
        'entregador_id',
        'valor',
        'numero_de_sacolas',
        'pessoa_id_separou_pedido',
        'pessoa_id_conferiu_pedido',
        'cookie_utm_source',
        'cookie_utm_campaign',
        'cookie_newsletter_id',
    ];

    protected $casts = [
        'data_entrega' => 'datetime',
        'valor' => 'decimal:2',
    ];

    protected $columnMap = [
        'person_id'     => 'pessoa_id',
        'order_id'      => 'pedido_id',
        'status_id'     => 'statu_id',
        'notes'         => 'observacao',
        'delivery_date' => 'data_entrega',
        'deliverer_id'  => 'entregador_id',
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

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'statu_id');
    }
}
