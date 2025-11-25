<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabela para armazenar os 4 blocos de features/informações exibidos abaixo do banner hero.
     * Cada bloco contém: ícone, título, descrição e cores personalizáveis.
     */
    public function up(): void
    {
        Schema::create('feature_blocks', function (Blueprint $table) {
            $table->id();
            $table->integer('order')->unique(); // Ordem de exibição (1, 2, 3, 4) - fixa em 4 blocos
            $table->string('icon', 100); // Classe do ícone (ex: 'fa fa-motorcycle', 'bi bi-truck')
            $table->string('title', 100); // Título do bloco (ex: 'frete expresso')
            $table->string('description', 255); // Descrição detalhada
            $table->string('bg_color', 20)->default('#D4F4DD'); // Cor de fundo (hex ou rgba)
            $table->string('text_color', 20)->default('#013E3B'); // Cor do texto
            $table->string('icon_color', 20)->default('#013E3B'); // Cor do ícone
            $table->boolean('active')->default(true); // Ativo/inativo
            $table->timestamps();

            // Índice para ordem de exibição
            $table->index('order');
            $table->index('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_blocks');
    }
};
