<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabela para armazenar configurações de tema (cores, fontes, layout).
     * Permite múltiplos temas, mas apenas um ativo por vez.
     */
    public function up(): void
    {
        Schema::create('theme_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('default'); // Nome do tema (ex: "Naturallis Original", "Dark Mode")
            $table->boolean('is_active')->default(false); // Apenas um tema pode estar ativo
            $table->json('colors'); // Todas as cores do tema em formato JSON
            $table->json('fonts')->nullable(); // Configurações de fontes (futuro)
            $table->json('layout')->nullable(); // Configurações de layout (futuro)
            $table->timestamps();

            // Índice para buscar rapidamente o tema ativo
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('theme_settings');
    }
};
