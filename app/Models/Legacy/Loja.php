<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Loja/Filial (tabela 'lojas' do banco legado)
 *
 * Configuração de múltiplas lojas/filiais do sistema DeepFreeze.
 * Cada loja tem próprio CNPJ, IE, certificado digital.
 * Apenas leitura nesta etapa — gerenciado pelo SIV.
 *
 * Tabela: novo.lojas
 * Engine: MyISAM
 * Charset: utf8mb3
 *
 * Lojas ativas conhecidas: códigos 2, 9, 23, 25
 */
class Loja extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'lojas';

    public $timestamps = false;

    protected $casts = [
        'loja_ativa' => 'boolean',
        'retirar_na_loja' => 'boolean',
        'franquia' => 'boolean',
        'filial' => 'boolean',
        'latitude' => 'double',
        'longitude' => 'double',
    ];

    protected $columnMap = [
        'name'               => 'nome',
        'code'               => 'codigo',
        'active'             => 'loja_ativa',
        'allow_pickup'       => 'retirar_na_loja',
        'delivery_region_id' => 'entregas_regiao_id',
        'is_franchise'       => 'franquia',
        'is_branch'          => 'filial',
        'manager'            => 'gerente',
        'phone_number'       => 'telefone',
        'image'              => 'imagem_loja',
        'maps_link'          => 'link_google_maps',
        'email_address'      => 'email_loja',
        'cielo_merchant_id'  => 'merchant_id',     // MerchantId para Cielo Checkout API
        'rede_pv'            => 'pv',              // Ponto de Venda para Rede e-Rede
        'rede_token'         => 'token',           // Token de autenticação Rede e-Rede
    ];

    public function getAttribute($key)
    {
        if (isset($this->columnMap[$key])) {
            return parent::getAttribute($this->columnMap[$key]);
        }

        return parent::getAttribute($key);
    }

    // ==================== HELPERS ====================

    /**
     * Endereço completo formatado
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->logradouro,
            $this->numero ? 'nº ' . $this->numero : null,
            $this->complemento,
            $this->bairro,
            $this->municipio,
            $this->uf,
            $this->cep,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Horário de funcionamento formatado para exibição
     */
    public function getBusinessHoursAttribute(): array
    {
        return [
            'weekdays' => $this->horario_funcionamento_semana,
            'saturday' => $this->horario_funcionamento_sabado,
            'sunday'   => $this->horario_funcionamento_domingo,
        ];
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Lojas ativas
     */
    public function scopeActive($query)
    {
        return $query->where('loja_ativa', 1);
    }

    /**
     * Scope: Lojas que permitem retirada
     */
    public function scopeAllowPickup($query)
    {
        return $query->where('retirar_na_loja', 1);
    }
}
