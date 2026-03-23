<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Informações de Frete do Pedido (tabela 'pedidos_informacoes_frete')
 *
 * Registra o cálculo de frete de cada pedido: distância, valor, motivo do desconto.
 * O SIV usa esta tabela para relatórios de logística e conciliação.
 *
 * Tabela: novo.pedidos_informacoes_frete
 * Engine: MyISAM | ~844k registros
 */
class PedidoInformacaoFrete extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'pedidos_informacoes_frete';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    protected $fillable = [
        'pedido_id',
        'pessoa_id',
        'loja_id',
        'valor_frete_original',
        'valor_frete',
        'distancia_km',
        'distancia_ida_e_volta',
        'falta_para_frete_gratis',
        'motivo',
        'valor_total_sem_frete',
    ];

    protected $casts = [
        'valor_frete_original' => 'decimal:2',
        'valor_frete' => 'decimal:2',
        'distancia_km' => 'float',
        'distancia_ida_e_volta' => 'float',
        'falta_para_frete_gratis' => 'decimal:2',
        'valor_total_sem_frete' => 'decimal:2',
    ];

    // ==================== RELATIONSHIPS ====================

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }
}
