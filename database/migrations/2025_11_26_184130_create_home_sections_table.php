<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Criar tabela home_sections
 *
 * Esta tabela armazena as seções da página inicial (home) e permite
 * controlar a ordem de exibição e ativação de cada seção via painel admin.
 *
 * Seções disponíveis:
 * - hero_banners: Banner principal do topo (carrossel)
 * - feature_blocks: Blocos de funcionalidades/ícones (4 itens)
 * - product_galleries: Galerias de produtos (até 4 galerias)
 * - dual_banners: Banners duplos (lado a lado)
 * - info_blocks: Blocos de informação (ex: Refeições Saudáveis)
 * - step_blocks: Blocos de passos (4 itens com ícone)
 * - single_banners: Banners únicos (desktop + mobile)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('home_sections', function (Blueprint $table) {
            $table->id();

            // Nome amigável da seção (ex: "Banner Principal", "Blocos de Funcionalidades")
            $table->string('name', 100)->comment('Nome amigável da seção para exibição no admin');

            // Slug único para identificação (ex: "hero_banners", "feature_blocks")
            $table->string('slug', 50)->unique()->comment('Identificador único da seção (snake_case)');

            // Nome da função helper que renderiza a seção (ex: "hero_banners", "feature_blocks")
            // Corresponde às funções em app/Helpers/ThemeHelper.php
            $table->string('helper_function', 50)->comment('Nome da função helper que renderiza a seção');

            // Descrição da seção para ajudar o admin a entender o que é
            $table->string('description', 255)->nullable()->comment('Descrição da seção para o admin');

            // Ícone para exibição no admin (Bootstrap Icons)
            $table->string('icon', 50)->nullable()->comment('Classe do ícone Bootstrap Icons (ex: bi-image)');

            // Se a seção está ativa (visível na home)
            $table->boolean('is_active')->default(true)->comment('Se a seção está visível na home');

            // Ordem de exibição (menor = primeiro)
            $table->unsignedSmallInteger('order')->default(0)->comment('Ordem de exibição (menor primeiro)');

            // Rota do admin para editar os itens desta seção (se aplicável)
            $table->string('admin_route', 100)->nullable()->comment('Nome da rota do admin para editar itens');

            $table->timestamps();

            // Índice para ordenação
            $table->index(['is_active', 'order'], 'idx_home_sections_active_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_sections');
    }
};
