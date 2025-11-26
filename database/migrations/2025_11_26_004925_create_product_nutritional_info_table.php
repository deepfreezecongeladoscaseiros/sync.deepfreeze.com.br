<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Tabela de Informações Nutricionais dos Produtos
 *
 * Armazena dados nutricionais detalhados de cada produto, seguindo
 * o padrão da ANVISA para rotulagem nutricional.
 *
 * Relacionamento: 1 produto -> 1 informação nutricional (hasOne)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_nutritional_info', function (Blueprint $table) {
            $table->id();

            // Relacionamento com produto (1:1)
            $table->foreignId('product_id')
                  ->constrained()
                  ->onDelete('cascade')
                  ->comment('FK para tabela products');

            // ========================================
            // INFORMAÇÕES DA PORÇÃO
            // ========================================
            $table->decimal('portion_size', 10, 2)
                  ->nullable()
                  ->comment('Tamanho da porção (ex: 350)');

            $table->string('portion_unit', 10)
                  ->default('g')
                  ->comment('Unidade: g (gramas) ou ml (mililitros)');

            $table->string('portion_description')
                  ->nullable()
                  ->comment('Descrição da porção (ex: "1 unidade (350g)")');

            $table->integer('servings_per_container')
                  ->nullable()
                  ->comment('Porções por embalagem');

            // ========================================
            // VALORES ENERGÉTICOS
            // ========================================
            $table->decimal('energy_kcal', 10, 2)
                  ->nullable()
                  ->comment('Valor energético em kcal');

            $table->decimal('energy_kj', 10, 2)
                  ->nullable()
                  ->comment('Valor energético em kJ');

            // ========================================
            // MACRONUTRIENTES
            // ========================================
            $table->decimal('carbohydrates', 10, 2)
                  ->nullable()
                  ->comment('Carboidratos totais (g)');

            $table->decimal('total_sugars', 10, 2)
                  ->nullable()
                  ->comment('Açúcares totais (g)');

            $table->decimal('added_sugars', 10, 2)
                  ->nullable()
                  ->comment('Açúcares adicionados (g)');

            $table->decimal('proteins', 10, 2)
                  ->nullable()
                  ->comment('Proteínas (g)');

            // ========================================
            // GORDURAS
            // ========================================
            $table->decimal('total_fat', 10, 2)
                  ->nullable()
                  ->comment('Gorduras totais (g)');

            $table->decimal('saturated_fat', 10, 2)
                  ->nullable()
                  ->comment('Gorduras saturadas (g)');

            $table->decimal('trans_fat', 10, 2)
                  ->nullable()
                  ->comment('Gorduras trans (g)');

            $table->decimal('monounsaturated_fat', 10, 2)
                  ->nullable()
                  ->comment('Gorduras monoinsaturadas (g)');

            $table->decimal('polyunsaturated_fat', 10, 2)
                  ->nullable()
                  ->comment('Gorduras poliinsaturadas (g)');

            $table->decimal('cholesterol', 10, 2)
                  ->nullable()
                  ->comment('Colesterol (mg)');

            // ========================================
            // FIBRAS E SÓDIO
            // ========================================
            $table->decimal('dietary_fiber', 10, 2)
                  ->nullable()
                  ->comment('Fibra alimentar (g)');

            $table->decimal('sodium', 10, 2)
                  ->nullable()
                  ->comment('Sódio (mg)');

            // ========================================
            // MINERAIS
            // ========================================
            $table->decimal('calcium', 10, 2)
                  ->nullable()
                  ->comment('Cálcio (mg)');

            $table->decimal('iron', 10, 2)
                  ->nullable()
                  ->comment('Ferro (mg)');

            $table->decimal('potassium', 10, 2)
                  ->nullable()
                  ->comment('Potássio (mg)');

            // ========================================
            // VITAMINAS
            // ========================================
            $table->decimal('vitamin_a', 10, 2)
                  ->nullable()
                  ->comment('Vitamina A (mcg)');

            $table->decimal('vitamin_c', 10, 2)
                  ->nullable()
                  ->comment('Vitamina C (mg)');

            $table->decimal('vitamin_d', 10, 2)
                  ->nullable()
                  ->comment('Vitamina D (mcg)');

            // ========================================
            // PERCENTUAIS DE VALOR DIÁRIO (%VD)
            // Baseado em dieta de 2000 kcal
            // ========================================
            $table->integer('dv_energy')
                  ->nullable()
                  ->comment('%VD de valor energético');

            $table->integer('dv_carbohydrates')
                  ->nullable()
                  ->comment('%VD de carboidratos');

            $table->integer('dv_proteins')
                  ->nullable()
                  ->comment('%VD de proteínas');

            $table->integer('dv_total_fat')
                  ->nullable()
                  ->comment('%VD de gorduras totais');

            $table->integer('dv_saturated_fat')
                  ->nullable()
                  ->comment('%VD de gorduras saturadas');

            $table->integer('dv_trans_fat')
                  ->nullable()
                  ->comment('%VD de gorduras trans');

            $table->integer('dv_dietary_fiber')
                  ->nullable()
                  ->comment('%VD de fibras');

            $table->integer('dv_sodium')
                  ->nullable()
                  ->comment('%VD de sódio');

            $table->timestamps();

            // Índice único: 1 produto = 1 info nutricional
            $table->unique('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_nutritional_info');
    }
};
