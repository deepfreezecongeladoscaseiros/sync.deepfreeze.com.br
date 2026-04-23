<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Cupom Promocional (tabela 'promocionais' do banco legado)
 *
 * Cupons de desconto globais que podem ser aplicados a pedidos.
 * Um cupom está DISPONÍVEL quando: pedido_id IS NULL, data_uso IS NULL, validade >= hoje.
 * Ao usar, grava pedido_id + data_uso para marcar como utilizado.
 *
 * Tabela: novo.promocionais
 * Engine: MyISAM | ~965k registros
 *
 * Tipos de desconto (via motivo_id → tabela motivos):
 * - Cupom manual (cliente digita código)
 * - Gift card (vale presente)
 * - Desconto automático (concedido por parceria)
 * - Desconto de aniversário, ocorrência, etc.
 */
class Promocional extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'promocionais';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'modified';

    protected $fillable = [
        'valor',
        'percentual',
        'codigo',
        'pedido_id',
        'motivo_id',
        'gift_card_id',
        'inicio_validade',
        'validade',
        'data_uso',
        'pessoa_id',
        'pode_ser_usado_site',
        'pode_ser_usado_siv',
        'apenas_dinheiro_debito',
    ];

    protected $casts = [
        'validade' => 'date',
        'inicio_validade' => 'date',
        'data_uso' => 'datetime',
        'pode_ser_usado_site' => 'boolean',
        'apenas_dinheiro_debito' => 'boolean',
    ];

    // ==================== SCOPES ====================

    /**
     * Scope: Cupons disponíveis (não usados, dentro da validade, permitidos no site)
     */
    public function scopeAvailable($query)
    {
        return $query->whereNull('pedido_id')
            ->whereNull('data_uso')
            ->where('pode_ser_usado_site', 1)
            ->where(function ($q) {
                $q->whereNull('validade')
                  ->orWhere('validade', '>=', now()->toDateString());
            })
            ->where(function ($q) {
                $q->whereNull('inicio_validade')
                  ->orWhere('inicio_validade', '<=', now()->toDateString());
            });
    }

    /**
     * Scope: Busca por código
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('codigo', trim($code));
    }

    // ==================== RELATIONSHIPS ====================

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function giftCard()
    {
        return $this->belongsTo(GiftCard::class, 'gift_card_id');
    }

    // ==================== HELPERS ====================

    /**
     * Verifica se o cupom ainda está disponível para uso
     */
    public function isAvailable(): bool
    {
        if ($this->pedido_id || $this->data_uso) {
            return false;
        }

        if (!$this->pode_ser_usado_site) {
            return false;
        }

        if ($this->validade && $this->validade->lt(now()->startOfDay())) {
            return false;
        }

        if ($this->inicio_validade && $this->inicio_validade->gt(now()->startOfDay())) {
            return false;
        }

        return true;
    }

    /**
     * Calcula o valor do desconto para um subtotal
     *
     * @param float $subtotal Valor do pedido
     * @return float Valor do desconto em R$
     */
    public function calculateDiscount(float $subtotal): float
    {
        // Desconto por valor fixo
        if ($this->valor && $this->valor > 0) {
            return min((float) $this->valor, $subtotal);
        }

        // Desconto por percentual
        if ($this->percentual && $this->percentual > 0) {
            return round($subtotal * (float) $this->percentual / 100, 2);
        }

        return 0;
    }

    /**
     * Marca o cupom como utilizado por um pedido
     */
    public function markAsUsed(int $pedidoId, ?int $pessoaId = null): void
    {
        $this->pedido_id = $pedidoId;
        $this->data_uso = now();
        if ($pessoaId) {
            $this->pessoa_id = $pessoaId;
        }
        $this->save();
    }

    /**
     * Libera o cupom para reuso (ex: pedido cancelado)
     */
    public function release(): void
    {
        $this->pedido_id = null;
        $this->data_uso = null;
        $this->save();
    }
}
