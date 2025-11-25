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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('Nome/título da página interna');
            $table->string('slug')->unique()->comment('URL amigável da página (ex: quem-somos, politica-privacidade)');
            $table->longText('content')->comment('Conteúdo HTML da página (editor WYSIWYG)');
            $table->string('meta_title')->nullable()->comment('Título SEO para <title> tag (opcional - usa title se vazio)');
            $table->text('meta_description')->nullable()->comment('Meta description para SEO (até 160 caracteres recomendado)');
            $table->string('meta_keywords')->nullable()->comment('Palavras-chave separadas por vírgula para SEO');
            $table->boolean('active')->default(true)->comment('Página ativa/visível no site');
            $table->timestamps();

            // Índice para busca rápida por slug
            $table->index('slug');
            $table->index('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
