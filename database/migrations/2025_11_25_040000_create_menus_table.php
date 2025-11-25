<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Tabela de Menus (containers)
 *
 * Armazena os diferentes menus da loja (principal, rodapé, mobile, etc.)
 * Cada menu pode ter múltiplos itens organizados hierarquicamente.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();

            // Identificação do menu
            $table->string('name', 100);                    // Nome interno (ex: "Menu Principal")
            $table->string('slug', 50)->unique();           // Identificador único (ex: "main", "footer")

            // Localização onde será exibido
            $table->enum('location', [
                'header',           // Cabeçalho (menu principal desktop)
                'footer',           // Rodapé
                'mobile_sidebar',   // Menu lateral mobile
                'custom'            // Posição customizada
            ])->default('header');

            // Descrição para o admin
            $table->string('description', 255)->nullable(); // Descrição do menu para o admin

            // Status
            $table->boolean('active')->default(true);       // Menu ativo/inativo

            $table->timestamps();

            // Índices para performance
            $table->index(['location', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
