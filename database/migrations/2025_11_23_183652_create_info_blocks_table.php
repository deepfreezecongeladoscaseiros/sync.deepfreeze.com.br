<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Cria tabela para blocos informativos exibidos na home.
     * Cada bloco contém uma imagem grande e texto (título + subtítulo).
     *
     * Exemplo de uso: seção "Refeições Saudáveis"
     * - Imagem à esquerda (70% largura)
     * - Título e subtítulo à direita (30% largura)
     *
     * Características:
     * - Múltiplos blocos permitidos (ordenados por 'order')
     * - Background color customizável
     * - Ativo/inativo
     */
    public function up(): void
    {
        Schema::create('info_blocks', function (Blueprint $table) {
            $table->id();

            // Ordem de exibição
            $table->integer('order')->default(1)->comment('Ordem de exibição (menor = primeiro)');

            // Imagem principal
            $table->string('image_path')->comment('Caminho da imagem principal (recomendado: 800x600px)');
            $table->string('image_alt')->nullable()->comment('Texto alternativo da imagem para SEO');

            // Textos
            $table->string('title', 100)->comment('Título principal do bloco');
            $table->string('subtitle', 255)->nullable()->comment('Subtítulo/descrição do bloco');

            // Estilização
            $table->string('background_color', 20)->nullable()->comment('Cor de fundo do bloco (hex)');

            // Status
            $table->boolean('active')->default(true)->comment('Define se o bloco está ativo');

            $table->timestamps();

            // Índices para performance
            $table->index(['active', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('info_blocks');
    }
};
