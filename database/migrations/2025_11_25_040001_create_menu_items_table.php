<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Tabela de Itens de Menu
 *
 * Armazena os itens individuais de cada menu com suporte a:
 * - Hierarquia (parent_id para submenus)
 * - Tipos variados (categoria, página, link externo, etc.)
 * - Relacionamento polimórfico (linkable)
 * - Ícones (classe CSS ou imagem)
 * - Mega menu com imagem/banner
 * - Controle de exibição (desktop, mobile, ambos)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();

            // Relacionamento com menu
            $table->foreignId('menu_id')
                ->constrained()
                ->onDelete('cascade');              // Se deletar menu, deleta itens

            // Hierarquia (auto-relacionamento para submenus)
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('menu_items')
                ->onDelete('cascade');              // Se deletar pai, deleta filhos

            // Tipo do item de menu
            $table->enum('type', [
                'category',         // Link para categoria de produtos
                'page',             // Link para página institucional
                'url',              // Link externo ou URL customizada
                'contact',          // Link para página de contato
                'home',             // Link para home
                'submenu_title',    // Apenas título de grupo (sem link)
            ])->default('url');

            // Relacionamento polimórfico (para category, page, etc.)
            $table->string('linkable_type', 100)->nullable();   // Ex: App\Models\Category
            $table->unsignedBigInteger('linkable_id')->nullable();

            // Dados do item
            $table->string('title', 100);           // Texto exibido no menu
            $table->string('url', 500)->nullable(); // URL customizada (quando type = url)
            $table->enum('target', ['_self', '_blank'])->default('_self'); // Onde abrir o link

            // Ícones
            $table->string('icon_class', 100)->nullable();  // Classe CSS do ícone (ex: "fa fa-home")
            $table->string('icon_image', 255)->nullable();  // Caminho da imagem do ícone (menu mobile)

            // Estilo e aparência
            $table->string('css_class', 100)->nullable();   // Classes CSS extras (ex: "submenu-full", "destaque")

            // Controle de ordenação
            $table->unsignedInteger('position')->default(0); // Ordem de exibição

            // Controle de exibição por dispositivo
            $table->enum('show_on', [
                'all',      // Exibir em todos os dispositivos
                'desktop',  // Apenas desktop
                'mobile'    // Apenas mobile
            ])->default('all');

            // === MEGA MENU (banner/imagem promocional) ===
            $table->boolean('is_mega_menu')->default(false);        // É um mega menu?
            $table->string('mega_menu_image', 255)->nullable();     // Imagem/banner do mega menu
            $table->string('mega_menu_image_url', 500)->nullable(); // Link ao clicar na imagem
            $table->string('mega_menu_image_alt', 150)->nullable(); // Texto alternativo da imagem
            $table->enum('mega_menu_image_position', [
                'right',    // Imagem à direita (padrão atual)
                'left',     // Imagem à esquerda
                'bottom'    // Imagem abaixo dos itens
            ])->default('right');
            $table->unsignedTinyInteger('mega_menu_columns')->default(2); // Colunas para os itens (1-4)

            // Status
            $table->boolean('active')->default(true);

            $table->timestamps();

            // Índices para performance
            $table->index(['menu_id', 'parent_id', 'position']);
            $table->index(['menu_id', 'active', 'show_on']);
            $table->index(['linkable_type', 'linkable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
