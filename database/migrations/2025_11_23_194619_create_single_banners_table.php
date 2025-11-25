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
        Schema::create('single_banners', function (Blueprint $table) {
            $table->id();
            $table->integer('order')->default(1)->comment('Ordem de exibição (menor = primeiro)');

            // Imagem Desktop
            $table->string('desktop_image_path')->comment('1400x300px recomendado - Imagem para desktop');

            // Imagem Mobile
            $table->string('mobile_image_path')->comment('766x400px recomendado - Imagem para mobile');

            // Dados do banner
            $table->string('link')->nullable()->comment('URL de destino ao clicar no banner');
            $table->string('alt_text')->nullable()->comment('Texto alternativo (SEO/Acessibilidade)');

            // Período de exibição
            $table->date('start_date')->nullable()->comment('Data de início (null = sem limite)');
            $table->date('end_date')->nullable()->comment('Data de fim (null = sem limite)');

            // Status
            $table->boolean('active')->default(true)->comment('Banner ativo/inativo');

            $table->timestamps();

            // Índices para melhorar performance
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
        Schema::dropIfExists('single_banners');
    }
};
