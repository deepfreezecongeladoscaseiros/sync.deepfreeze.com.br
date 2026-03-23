<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Tabela de Preços de Frete (tabela 'precos_frete' do banco legado)
 *
 * Define preço base (0-4km) e preço por km adicional (>4.1km).
 * Apenas 2 registros: Lalago e Lalapro (tipos de veículo LalaMove).
 *
 * Tabela: novo.precos_frete
 * Engine: MyISAM | 2-3 registros
 */
class PrecoFrete extends Model
{
    protected $connection = 'mysql_legacy';
    protected $table = 'precos_frete';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    protected $casts = [
        'preco_base' => 'decimal:2',
        'preco_km_adicional' => 'decimal:2',
        'preco_ponto_adicional' => 'decimal:2',
    ];

    /**
     * Calcula valor do frete por distância
     *
     * @param float $distanciaKm Distância em km
     * @return float Valor do frete
     */
    public function calcularPorDistancia(float $distanciaKm): float
    {
        if ($distanciaKm <= 4.0) {
            return (float) $this->preco_base;
        }

        $kmAdicional = $distanciaKm - 4.0;
        return (float) $this->preco_base + ($kmAdicional * (float) $this->preco_km_adicional);
    }
}
