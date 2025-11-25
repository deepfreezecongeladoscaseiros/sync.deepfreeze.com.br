<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Cria tabela de galerias de produtos para exibição na home.
     * Permite até 4 galerias configuráveis com filtros dinâmicos,
     * customização de layout e cores.
     */
    public function up(): void
    {
        Schema::create('product_galleries', function (Blueprint $table) {
            $table->id();

            // Ordenação e identificação
            $table->integer('order')->unique(); // Ordem de exibição (1-4)

            // Conteúdo textual
            $table->string('title', 100); // Título da galeria (ex: "Os mais vendidos")
            $table->string('subtitle', 255)->nullable(); // Subtítulo (ex: "Confira os produtos mais pedidos")

            // Configuração de layout
            $table->integer('mobile_columns')->default(2); // Produtos por linha no mobile (1-4)
            $table->integer('desktop_columns')->default(4); // Produtos por linha no desktop (1-6)
            $table->integer('products_limit')->default(12); // Quantidade total de produtos

            // Filtros de produtos
            $table->enum('filter_type', ['category', 'best_sellers', 'on_sale', 'low_stock'])->default('category'); // Tipo de filtro
            $table->unsignedBigInteger('filter_value')->nullable(); // ID da categoria (quando filter_type = 'category')

            // Estilização - Fundo
            $table->string('background_color', 20)->nullable(); // Cor de fundo do bloco (hex)
            $table->string('background_image_path')->nullable(); // Caminho da imagem de fundo

            // Estilização - Textos
            $table->string('title_color', 20)->default('#013E3B'); // Cor do título
            $table->string('subtitle_color', 20)->default('#666666'); // Cor do subtítulo

            // Estilização - Botão "Ver Todos"
            $table->boolean('show_view_all_button')->default(true); // Exibir botão "Ver Todos"
            $table->string('view_all_url')->nullable(); // URL do botão "Ver Todos"
            $table->string('button_bg_color', 20)->default('#FFA733'); // Cor de fundo do botão
            $table->string('button_hover_color', 20)->default('#013E3B'); // Cor de fundo do botão ao hover
            $table->string('button_text_color', 20)->default('#FFFFFF'); // Cor do texto do botão

            // Status
            $table->boolean('active')->default(true); // Galeria ativa/inativa

            $table->timestamps();

            // Índices para performance
            $table->index('order'); // Consulta por ordem
            $table->index('active'); // Filtro por status ativo
            $table->index(['filter_type', 'filter_value']); // Consulta por filtro

            // Foreign key para categoria (quando filter_type = 'category')
            $table->foreign('filter_value')
                ->references('id')
                ->on('categories')
                ->onDelete('set null'); // Se categoria for deletada, filter_value vira null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_galleries');
    }
};
