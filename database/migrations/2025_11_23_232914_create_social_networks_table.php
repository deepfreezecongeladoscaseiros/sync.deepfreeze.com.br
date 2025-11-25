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
        Schema::create('social_networks', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->comment('Nome da rede social (ex: Facebook, Instagram)');
            $table->string('icon_path')->comment('Caminho do ícone/imagem da rede social');
            $table->string('url')->comment('URL completa da rede social (ex: https://facebook.com/username)');
            $table->integer('order')->default(1)->comment('Ordem de exibição (menor número = aparece primeiro)');
            $table->boolean('active')->default(true)->comment('Rede social ativa/visível no site');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_networks');
    }
};
