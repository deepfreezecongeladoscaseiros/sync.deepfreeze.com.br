<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration para criar tabela de blocos flexíveis da home page
 *
 * Permite montar a home page com blocos intercalados de diferentes tipos,
 * onde cada bloco pode referenciar um item específico (galeria X, banner Y, etc.)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('home_blocks', function (Blueprint $table) {
            $table->id();

            // Tipo do bloco (determina qual partial/helper será usado)
            // Valores: hero_banners, feature_blocks, product_gallery, dual_banner,
            //          single_banner, info_block, step_blocks
            $table->string('type', 50);

            // ID do item específico (ex: qual galeria, qual banner)
            // NULL para tipos que exibem todos os itens (hero_banners, feature_blocks, step_blocks)
            $table->unsignedBigInteger('reference_id')->nullable();

            // Título customizado para o bloco (opcional, sobrescreve o título do item)
            $table->string('custom_title')->nullable();

            // Ordem de exibição na home (0, 1, 2, 3...)
            $table->unsignedInteger('order')->default(0);

            // Bloco ativo/inativo
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Índices para performance
            $table->index('type'); // Busca por tipo
            $table->index('order'); // Ordenação
            $table->index(['is_active', 'order']); // Query principal da home
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_blocks');
    }
};
