<?php

namespace App\Models\Legacy;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

/**
 * Model: Pedido (tabela 'pedidos' do banco legado)
 *
 * Tabela central do sistema de pedidos do SIV.
 * O sync grava nesta tabela para que o ecossistema legado
 * (operações, NF-e, entregas, relatórios) funcione normalmente.
 *
 * Campos de endereço são DESNORMALIZADOS (copiados direto no pedido).
 *
 * Tabela: novo.pedidos
 * Engine: MyISAM
 * Charset: utf8mb3
 *
 * Valores-chave:
 * - origem = 'INTERNET' (pedidos do site)
 * - finalizado: 0=pendente/carrinho, 1=finalizado, 3=cancelado
 * - sessao: inteiro aleatório único (pode ser negativo)
 */
class Pedido extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'pedidos';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'modified';

    // Status do pedido (campo 'finalizado')
    const STATUS_PENDENTE = 0;
    const STATUS_FINALIZADO = 1;
    const STATUS_CANCELADO = 3;

    // Origem do pedido
    const ORIGEM_INTERNET = 'INTERNET';

    protected $fillable = [
        'sessao',
        'origem',
        'pessoa_id',
        'tipo_pedido_id',
        'meio_contato_id',
        'cep_entrega',
        'logradouro_entrega',
        'logradouro_complemento_entrega',
        'logradouro_complemento_numero_entrega',
        'bairro_entrega',
        'uf_entrega',
        'cidade_entrega',
        'referencia_entrega',
        'endereco_id',
        'formas_pagamento_id',
        'desconto',
        'desconto_adicional',
        'desconto_adicional_porcento',
        'desconto_adicional_dinheiro',
        'tipo_desconto',
        'valor_total_produtos',
        'valor_total_venda',
        'valor_frete_original',
        'valor_frete',
        'valor_total',
        'troco',
        'troco_para',
        'observacao',
        'veiculos_periodo_id',
        'finalizado',
        'data_finalizado',
        'entrega',
        'peso',
        'loja_retirada_id',
        'data_retirada',
        'distancia_km',
        'receber_cardapio_impresso',
        'log_desconto',
    ];

    protected $casts = [
        'data_finalizado' => 'datetime',
        'entrega' => 'date',
        'data_retirada' => 'date',
        'valor_total_produtos' => 'decimal:2',
        'valor_total_venda' => 'decimal:2',
        'valor_frete_original' => 'decimal:2',
        'valor_frete' => 'decimal:2',
        'valor_total' => 'decimal:2',
        'troco' => 'decimal:2',
        'distancia_km' => 'decimal:2',
    ];

    /**
     * Mapeamento: nome inglês → coluna legado
     */
    protected $columnMap = [
        // Identificação
        'session'              => 'sessao',
        'origin'               => 'origem',
        'person_id'            => 'pessoa_id',

        // Endereço de entrega (desnormalizado)
        'shipping_zip'         => 'cep_entrega',
        'shipping_street'      => 'logradouro_entrega',
        'shipping_complement'  => 'logradouro_complemento_entrega',
        'shipping_number'      => 'logradouro_complemento_numero_entrega',
        'shipping_neighborhood' => 'bairro_entrega',
        'shipping_state'       => 'uf_entrega',
        'shipping_city'        => 'cidade_entrega',
        'shipping_reference'   => 'referencia_entrega',
        'address_id'           => 'endereco_id',

        // Pagamento
        'payment_method_id'    => 'formas_pagamento_id',

        // Valores
        'products_total'       => 'valor_total_produtos',
        'sale_total'           => 'valor_total_venda',
        'shipping_original'    => 'valor_frete_original',
        'shipping_cost'        => 'valor_frete',
        'total'                => 'valor_total',
        'notes'                => 'observacao',

        // Status
        'finalized'            => 'finalizado',
        'finalized_at'         => 'data_finalizado',

        // Entrega
        'delivery_date'        => 'entrega',
        'delivery_period_id'   => 'veiculos_periodo_id',
        'pickup_store_id'      => 'loja_retirada_id',
        'pickup_date'          => 'data_retirada',
        'weight'               => 'peso',
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
     * Cliente do pedido
     */
    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }

    /**
     * Itens do pedido
     */
    public function items()
    {
        return $this->hasMany(PedidoProduto::class, 'pedido_id');
    }

    /**
     * Histórico de status
     */
    public function statuses()
    {
        return $this->hasMany(StatusPedido::class, 'pedido_id')
            ->orderByDesc('created');
    }

    /**
     * Forma de pagamento
     */
    public function formaPagamento()
    {
        return $this->belongsTo(FormaPagamento::class, 'formas_pagamento_id');
    }

    /**
     * Loja de retirada (se retirada em loja)
     */
    public function lojaRetirada()
    {
        return $this->belongsTo(Loja::class, 'loja_retirada_id');
    }

    // ==================== HELPERS ====================

    /**
     * Verifica se o pedido é entrega (não retirada em loja)
     */
    public function isDelivery(): bool
    {
        return empty($this->loja_retirada_id);
    }

    /**
     * Verifica se o pedido é retirada em loja
     */
    public function isPickup(): bool
    {
        return !empty($this->loja_retirada_id);
    }

    /**
     * Verifica se o pedido está finalizado
     */
    public function isFinalized(): bool
    {
        return (int) $this->finalizado === self::STATUS_FINALIZADO;
    }

    /**
     * Verifica se o pedido está cancelado
     */
    public function isCancelled(): bool
    {
        return (int) $this->finalizado === self::STATUS_CANCELADO;
    }

    /**
     * Verifica se está aguardando pagamento
     */
    public function isPending(): bool
    {
        return (int) $this->finalizado === self::STATUS_PENDENTE;
    }

    /**
     * Label do status em português
     */
    public function getStatusLabelAttribute(): string
    {
        return match ((int) $this->finalizado) {
            self::STATUS_PENDENTE   => 'Aguardando Pagamento',
            self::STATUS_FINALIZADO => 'Confirmado',
            self::STATUS_CANCELADO  => 'Cancelado',
            default                 => 'Desconhecido',
        };
    }

    /**
     * Endereço de entrega formatado (uma linha)
     */
    public function getShippingFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->logradouro_entrega,
            $this->logradouro_complemento_numero_entrega ? 'nº ' . $this->logradouro_complemento_numero_entrega : null,
            $this->logradouro_complemento_entrega,
            $this->bairro_entrega,
            $this->cidade_entrega,
            $this->uf_entrega,
            $this->cep_entrega,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Valor total formatado em R$
     */
    public function getFormattedTotalAttribute(): string
    {
        return 'R$ ' . number_format((float) $this->valor_total, 2, ',', '.');
    }

    /**
     * Subtotal formatado (produtos sem frete)
     * Compatibilidade com view de e-mail
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return 'R$ ' . number_format((float) $this->valor_total_produtos, 2, ',', '.');
    }

    /**
     * Frete formatado
     * Compatibilidade com view de e-mail
     */
    public function getFormattedShippingCostAttribute(): string
    {
        return 'R$ ' . number_format((float) $this->valor_frete, 2, ',', '.');
    }

    /**
     * Desconto formatado
     * Compatibilidade com view de e-mail
     */
    public function getFormattedDiscountAttribute(): string
    {
        return 'R$ ' . number_format((float) $this->desconto, 2, ',', '.');
    }

    /**
     * Número do pedido para exibição (usa o ID do legado)
     * Compatibilidade com view de e-mail que usa $order->order_number
     */
    public function getOrderNumberAttribute(): string
    {
        return (string) $this->id;
    }

    /**
     * Nome do cliente (via relacionamento com pessoa)
     * Compatibilidade com view de e-mail
     */
    public function getCustomerNameAttribute(): string
    {
        return $this->pessoa?->nome ?? '';
    }

    /**
     * E-mail do cliente
     * Compatibilidade com view de e-mail
     */
    public function getCustomerEmailAttribute(): string
    {
        return $this->pessoa?->email_primario ?? '';
    }

    /**
     * Telefone do cliente
     * Compatibilidade com view de e-mail
     */
    public function getCustomerPhoneAttribute(): string
    {
        return $this->pessoa?->telefone_celular ?? $this->pessoa?->telefone_residencial ?? '';
    }

    /**
     * Tipo de pessoa (fisica/juridica)
     * Compatibilidade com view de e-mail
     */
    public function getCustomerPersonTypeAttribute(): string
    {
        return ($this->pessoa?->isPessoaJuridica()) ? 'juridica' : 'fisica';
    }

    /**
     * CPF do cliente
     * Compatibilidade com view de e-mail
     */
    public function getCustomerCpfAttribute(): string
    {
        return $this->pessoa?->cpf ?? '';
    }

    /**
     * CNPJ do cliente
     * Compatibilidade com view de e-mail
     */
    public function getCustomerCnpjAttribute(): string
    {
        return $this->pessoa?->cnpj ?? '';
    }

    /**
     * Endereço completo (alias para shipping_full_address)
     * Compatibilidade com view de e-mail que usa $order->full_address
     */
    public function getFullAddressAttribute(): string
    {
        return $this->shipping_full_address;
    }

    /**
     * Acesso ao campo created como Carbon (para ->format())
     * Compatibilidade com view que usa $order->created_at
     */
    public function getCreatedAtAttribute()
    {
        $value = $this->attributes['created'] ?? null;
        return $value ? \Illuminate\Support\Carbon::parse($value) : null;
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Pedidos finalizados
     */
    public function scopeFinalized($query)
    {
        return $query->where('finalizado', self::STATUS_FINALIZADO);
    }

    /**
     * Scope: Pedidos pendentes
     */
    public function scopePending($query)
    {
        return $query->where('finalizado', self::STATUS_PENDENTE);
    }

    /**
     * Scope: Pedidos vindos do site (nova loja)
     */
    public function scopeFromInternet($query)
    {
        return $query->where('origem', self::ORIGEM_INTERNET);
    }

    /**
     * Scope: Pedidos de uma pessoa
     */
    public function scopeOfPerson($query, int $pessoaId)
    {
        return $query->where('pessoa_id', $pessoaId);
    }
}
