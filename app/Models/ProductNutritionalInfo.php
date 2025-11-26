<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model: Informações Nutricionais do Produto
 *
 * Armazena dados nutricionais detalhados seguindo padrão ANVISA.
 * Relacionamento 1:1 com Product.
 */
class ProductNutritionalInfo extends Model
{
    use HasFactory;

    protected $table = 'product_nutritional_info';

    protected $fillable = [
        'product_id',

        // Informações da porção
        'portion_size',
        'portion_unit',
        'portion_description',
        'servings_per_container',

        // Valores energéticos
        'energy_kcal',
        'energy_kj',

        // Macronutrientes
        'carbohydrates',
        'total_sugars',
        'added_sugars',
        'proteins',

        // Gorduras
        'total_fat',
        'saturated_fat',
        'trans_fat',
        'monounsaturated_fat',
        'polyunsaturated_fat',
        'cholesterol',

        // Fibras e sódio
        'dietary_fiber',
        'sodium',

        // Minerais
        'calcium',
        'iron',
        'potassium',

        // Vitaminas
        'vitamin_a',
        'vitamin_c',
        'vitamin_d',

        // Percentuais de Valor Diário (%VD)
        'dv_energy',
        'dv_carbohydrates',
        'dv_proteins',
        'dv_total_fat',
        'dv_saturated_fat',
        'dv_trans_fat',
        'dv_dietary_fiber',
        'dv_sodium',
    ];

    protected $casts = [
        'portion_size' => 'decimal:2',
        'energy_kcal' => 'decimal:2',
        'energy_kj' => 'decimal:2',
        'carbohydrates' => 'decimal:2',
        'total_sugars' => 'decimal:2',
        'added_sugars' => 'decimal:2',
        'proteins' => 'decimal:2',
        'total_fat' => 'decimal:2',
        'saturated_fat' => 'decimal:2',
        'trans_fat' => 'decimal:2',
        'monounsaturated_fat' => 'decimal:2',
        'polyunsaturated_fat' => 'decimal:2',
        'cholesterol' => 'decimal:2',
        'dietary_fiber' => 'decimal:2',
        'sodium' => 'decimal:2',
        'calcium' => 'decimal:2',
        'iron' => 'decimal:2',
        'potassium' => 'decimal:2',
        'vitamin_a' => 'decimal:2',
        'vitamin_c' => 'decimal:2',
        'vitamin_d' => 'decimal:2',
        'servings_per_container' => 'integer',
        'dv_energy' => 'integer',
        'dv_carbohydrates' => 'integer',
        'dv_proteins' => 'integer',
        'dv_total_fat' => 'integer',
        'dv_saturated_fat' => 'integer',
        'dv_trans_fat' => 'integer',
        'dv_dietary_fiber' => 'integer',
        'dv_sodium' => 'integer',
    ];

    // ==================== RELACIONAMENTOS ====================

    /**
     * Produto relacionado
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // ==================== HELPERS ====================

    /**
     * Verifica se possui dados nutricionais preenchidos
     */
    public function hasData(): bool
    {
        return $this->energy_kcal !== null || $this->proteins !== null;
    }

    /**
     * Retorna a descrição da porção formatada
     */
    public function getPortionLabelAttribute(): string
    {
        if ($this->portion_description) {
            return $this->portion_description;
        }

        if ($this->portion_size) {
            return $this->portion_size . $this->portion_unit;
        }

        return 'Porção não informada';
    }

    /**
     * Formata valor nutricional com unidade
     */
    public function formatValue($value, string $unit = 'g', int $decimals = 1): string
    {
        if ($value === null) {
            return '-';
        }

        return number_format($value, $decimals, ',', '.') . $unit;
    }

    /**
     * Formata percentual de valor diário
     */
    public function formatDV($value): string
    {
        if ($value === null) {
            return '-';
        }

        return $value . '%';
    }
}
