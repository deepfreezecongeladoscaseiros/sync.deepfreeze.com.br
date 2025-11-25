<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();

            // Imagens (obrigatórias)
            $table->string('image_desktop'); // Caminho da imagem desktop (1400x385px recomendado)
            $table->string('image_mobile'); // Caminho da imagem mobile (766x981px recomendado)

            // Link e acessibilidade
            $table->string('link')->nullable(); // URL de destino ao clicar no banner
            $table->string('alt_text')->default('Banner'); // Texto alternativo para acessibilidade

            // Período de exibição
            $table->dateTime('start_date')->nullable(); // Data/hora de início da exibição
            $table->dateTime('end_date')->nullable(); // Data/hora de fim (null = eterno)

            // Ordenação e controle
            $table->integer('order')->default(0); // Ordem de exibição (menor = primeiro)
            $table->boolean('active')->default(true); // Ativo/inativo

            $table->timestamps();

            // Índices para performance
            $table->index('active');
            $table->index('order');
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
