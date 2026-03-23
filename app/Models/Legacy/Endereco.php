<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Endereço de entrega (tabela 'enderecos' do banco legado)
 *
 * Cada pessoa pode ter múltiplos endereços.
 * O campo 'end_principal' indica o endereço padrão.
 * O campo 'ultimo_endereco_usado' indica o último endereço usado em pedido.
 *
 * Tabela: novo.enderecos
 * Engine: MyISAM
 * Charset: utf8mb3
 */
class Endereco extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'enderecos';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    protected $fillable = [
        'pessoa_id',
        'logradouro_id',
        'cep',
        'logradouro',
        'logradouro_complemento',
        'logradouro_complemento_numero',
        'bairro',
        'cidade',
        'uf',
        'end_principal',
        'latitude',
        'longitude',
        'observacao',
        'ativo',
        'ultimo_endereco_usado',
        'regiao_administrativa',
    ];

    protected $casts = [
        'end_principal' => 'boolean',
        'ativo' => 'boolean',
    ];

    /**
     * Mapeamento: nome inglês → coluna legado
     */
    protected $columnMap = [
        'zip_code'      => 'cep',
        'street'        => 'logradouro',
        'number'        => 'logradouro_complemento_numero',
        'complement'    => 'logradouro_complemento',
        'neighborhood'  => 'bairro',
        'city'          => 'cidade',
        'state'         => 'uf',
        'is_primary'    => 'end_principal',
        'notes'         => 'observacao',
        'active'        => 'ativo',
    ];

    public function getAttribute($key)
    {
        if (isset($this->columnMap[$key])) {
            return parent::getAttribute($this->columnMap[$key]);
        }

        return parent::getAttribute($key);
    }

    // ==================== RELATIONSHIPS ====================

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }

    // ==================== HELPERS ====================

    /**
     * Endereço completo em uma linha (para exibição)
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->logradouro,
            $this->logradouro_complemento_numero ? 'nº ' . $this->logradouro_complemento_numero : null,
            $this->logradouro_complemento,
            $this->bairro,
            $this->cidade,
            $this->uf,
            $this->cep,
        ]);

        return implode(', ', $parts);
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Apenas endereços ativos
     */
    public function scopeActive($query)
    {
        return $query->where('ativo', 1);
    }
}
