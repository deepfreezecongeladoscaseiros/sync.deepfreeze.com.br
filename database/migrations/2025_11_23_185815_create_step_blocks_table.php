<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Cria tabela para blocos de passos/processo exibidos na home.
     * Cada registro contém 4 itens com ícone + título + descrição.
     *
     * Exemplo: "Entrega agendada", "Descontos", "Nutrição", "Facilite sua rotina"
     *
     * Layout: 4 colunas (col-md-3 cada) abaixo do bloco de informação
     */
    public function up(): void
    {
        Schema::create('step_blocks', function (Blueprint $table) {
            $table->id();

            // Ordem de exibição
            $table->integer('order')->default(1)->comment('Ordem de exibição (menor = primeiro)');

            // Item 1
            $table->string('item_1_icon_path')->comment('Caminho do ícone do item 1 (recomendado: 100x100px)');
            $table->string('item_1_title', 100)->comment('Título do item 1');
            $table->text('item_1_description')->comment('Descrição do item 1');
            $table->string('item_1_alt')->nullable()->comment('Alt text do ícone 1');

            // Item 2
            $table->string('item_2_icon_path')->comment('Caminho do ícone do item 2');
            $table->string('item_2_title', 100)->comment('Título do item 2');
            $table->text('item_2_description')->comment('Descrição do item 2');
            $table->string('item_2_alt')->nullable()->comment('Alt text do ícone 2');

            // Item 3
            $table->string('item_3_icon_path')->comment('Caminho do ícone do item 3');
            $table->string('item_3_title', 100)->comment('Título do item 3');
            $table->text('item_3_description')->comment('Descrição do item 3');
            $table->string('item_3_alt')->nullable()->comment('Alt text do ícone 3');

            // Item 4
            $table->string('item_4_icon_path')->comment('Caminho do ícone do item 4');
            $table->string('item_4_title', 100)->comment('Título do item 4');
            $table->text('item_4_description')->comment('Descrição do item 4');
            $table->string('item_4_alt')->nullable()->comment('Alt text do ícone 4');

            // Status
            $table->boolean('active')->default(true)->comment('Define se o bloco está ativo');

            $table->timestamps();

            // Índices
            $table->index(['active', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('step_blocks');
    }
};
