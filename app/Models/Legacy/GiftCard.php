<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Gift Card / Vale Presente (tabela 'gift_cards' do banco legado)
 *
 * Armazena gift cards comprados no e-commerce.
 * Cada gift card tem um código de 10 caracteres (numero_identificacao)
 * que pode ser usado como forma de pagamento em pedidos futuros.
 *
 * Tabela: novo.gift_cards
 * Engine: MyISAM | ~48 registros
 *
 * Campos principais:
 * - comprador_id: FK para pessoas (quem comprou)
 * - pedido_produto_id: FK para pedidos_produtos (item do pedido de compra)
 * - valor_presenteado: valor em R$ do vale (R$35 a R$850)
 * - numero_identificacao: código único de 10 chars para resgate
 * - validade: data limite para uso (1 mês após compra)
 * - tipo_entrega: 1 = email direto ao presenteado, 2 = email ao comprador + CC presenteado
 * - produto_id: FK para produtos (produto com gift_card=1)
 *
 * O uso do gift card é registrado na tabela 'promocionais' (com gift_card_id preenchido),
 * NÃO em uma tabela separada de usos.
 */
class GiftCard extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'gift_cards';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    protected $fillable = [
        'comprador_id',
        'email',
        'nome',
        'telefone',
        'celular',
        'pedido_produto_id',
        'valor_presenteado',
        'validade',
        'numero_identificacao',
        'produto_id',
        'observacao',
        'tipo_entrega',
    ];

    protected $casts = [
        'valor_presenteado' => 'decimal:2',
        'validade' => 'date',
    ];

    // ==================== RELATIONSHIPS ====================

    public function comprador()
    {
        return $this->belongsTo(Pessoa::class, 'comprador_id');
    }

    public function pedidoProduto()
    {
        return $this->belongsTo(PedidoProduto::class, 'pedido_produto_id');
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Busca gift card pelo código de identificação
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('numero_identificacao', trim($code));
    }
}
