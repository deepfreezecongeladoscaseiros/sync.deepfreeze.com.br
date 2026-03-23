<?php

namespace App\Models\Legacy;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

/**
 * Model: Item do Pedido (tabela 'pedidos_produtos' do banco legado)
 *
 * Cada registro representa um produto dentro de um pedido.
 * O campo 'produto' armazena o CÓDIGO do produto (ex: "ALM01"), não o nome.
 * O campo 'preco' é o preço no momento da compra (pode ser promocional).
 *
 * Tabela: novo.pedidos_produtos
 * Engine: MyISAM
 * Charset: utf8mb3
 */
class PedidoProduto extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'pedidos_produtos';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'modified';

    protected $fillable = [
        'pedido_id',
        'produto_id',
        'produto',              // Código do produto (ex: "ALM01")
        'quantidade',
        'preco',                // Preço unitário no momento da compra
        'desconto_preco_unitario',
        'subtotal',
        'preco_original',       // Preço original (antes de promoção)
        'gift',                 // Flag: 1=brinde/presente
        'log_desconto',
    ];

    protected $casts = [
        'preco' => 'decimal:2',
        'desconto_preco_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'preco_original' => 'decimal:2',
        'quantidade' => 'integer',
    ];

    /**
     * Mapeamento: nome inglês → coluna legado
     */
    protected $columnMap = [
        'order_id'           => 'pedido_id',
        'product_id'         => 'produto_id',
        'product_code'       => 'produto',
        'quantity'           => 'quantidade',
        'price'              => 'preco',
        'discount_per_unit'  => 'desconto_preco_unitario',
        'original_price'     => 'preco_original',
        'is_gift'            => 'gift',
    ];

    public function getAttribute($key)
    {
        if (isset($this->columnMap[$key])) {
            return parent::getAttribute($this->columnMap[$key]);
        }

        return parent::getAttribute($key);
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Pedido a que este item pertence
     */
    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    /**
     * Produto do catálogo (pode não existir mais se foi removido)
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'produto_id');
    }

    // ==================== HELPERS ====================

    /**
     * Preço efetivo (preço - desconto por unidade)
     */
    public function getEffectivePriceAttribute(): float
    {
        return (float) $this->preco - (float) $this->desconto_preco_unitario;
    }

    /**
     * Total formatado em R$
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return 'R$ ' . number_format((float) $this->subtotal, 2, ',', '.');
    }

    /**
     * Preço unitário formatado em R$
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'R$ ' . number_format((float) $this->preco, 2, ',', '.');
    }

    /**
     * Preço unitário formatado (alias para compatibilidade com view de e-mail)
     * View usa $item->formatted_unit_price
     */
    public function getFormattedUnitPriceAttribute(): string
    {
        return $this->formatted_price;
    }

    /**
     * Total do item formatado (alias para compatibilidade com view de e-mail)
     * View usa $item->formatted_total
     */
    public function getFormattedTotalAttribute(): string
    {
        return $this->formatted_subtotal;
    }

    /**
     * Nome do produto para exibição no e-mail
     * Busca descrição do produto no catálogo, fallback para código
     * View usa $item->product_name
     */
    public function getProductNameAttribute(): string
    {
        // Tenta buscar descrição do produto se relacionamento carregado
        if ($this->relationLoaded('product') && $this->product) {
            return $this->product->getOriginal('descricao') ?? $this->produto;
        }

        return $this->produto ?? '';
    }

    /**
     * SKU do produto (código legado)
     * View usa $item->product_sku
     */
    public function getProductSkuAttribute(): string
    {
        return $this->produto ?? '';
    }
}
