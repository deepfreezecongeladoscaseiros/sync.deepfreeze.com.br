<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Cria tabela para gerenciar banners duplos exibidos na home.
     * Cada registro contém DOIS banners (esquerdo e direito) que são exibidos lado a lado.
     *
     * Características:
     * - Múltiplos registros permitidos (ordenados por 'order')
     * - Cada banner tem: imagem (670x380px), link, alt text, período de exibição
     * - Período de exibição opcional (start_date e end_date podem ser null)
     * - Banner ativo/inativo por registro
     */
    public function up(): void
    {
        Schema::create('dual_banners', function (Blueprint $table) {
            $table->id();

            // Ordem de exibição (menor = primeiro)
            $table->integer('order')->default(1)->comment('Ordem de exibição do par de banners');

            // Banner Esquerdo
            $table->string('left_image_path')->comment('Caminho da imagem do banner esquerdo (recomendado: 670x380px)');
            $table->string('left_link')->nullable()->comment('URL de destino ao clicar no banner esquerdo');
            $table->string('left_alt_text')->nullable()->comment('Texto alternativo para SEO e acessibilidade');
            $table->date('left_start_date')->nullable()->comment('Data de início da exibição do banner esquerdo (null = sempre ativo)');
            $table->date('left_end_date')->nullable()->comment('Data de fim da exibição do banner esquerdo (null = sem data de término)');

            // Banner Direito
            $table->string('right_image_path')->comment('Caminho da imagem do banner direito (recomendado: 670x380px)');
            $table->string('right_link')->nullable()->comment('URL de destino ao clicar no banner direito');
            $table->string('right_alt_text')->nullable()->comment('Texto alternativo para SEO e acessibilidade');
            $table->date('right_start_date')->nullable()->comment('Data de início da exibição do banner direito (null = sempre ativo)');
            $table->date('right_end_date')->nullable()->comment('Data de fim da exibição do banner direito (null = sem data de término)');

            // Status
            $table->boolean('active')->default(true)->comment('Define se este par de banners está ativo');

            $table->timestamps();

            // Índices para melhorar performance
            $table->index(['active', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dual_banners');
    }
};
