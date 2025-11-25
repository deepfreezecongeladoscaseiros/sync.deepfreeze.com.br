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
        Schema::create('cookie_consent', function (Blueprint $table) {
            $table->id();

            // Status
            $table->boolean('active')->default(true)->comment('Disclaimer ativo/inativo');

            // Texto do disclaimer
            $table->text('message_text')->comment('Texto exibido no disclaimer (pode conter HTML/links)');

            // Estilos do botão
            $table->string('button_label', 50)->default('Aceito')->comment('Texto do botão de aceite');
            $table->string('button_bg_color', 20)->default('#FFA733')->comment('Cor de fundo do botão');
            $table->string('button_text_color', 20)->default('#FFFFFF')->comment('Cor do texto do botão');
            $table->string('button_hover_bg_color', 20)->default('#013E3B')->comment('Cor de fundo do botão ao passar o mouse');

            $table->timestamps();
        });

        // Insere registro padrão
        DB::table('cookie_consent')->insert([
            'active' => true,
            'message_text' => 'Nós usamos cookies e outras tecnologias semelhantes para melhorar a sua experiência em nossos serviços. Ao utilizar nossos serviços, você concorda com nossas <a href="/politica-de-privacidade">Políticas de Privacidade</a> e <a href="/politica-de-cookies">Cookies.</a>',
            'button_label' => 'Aceito',
            'button_bg_color' => '#FFA733',
            'button_text_color' => '#FFFFFF',
            'button_hover_bg_color' => '#013E3B',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cookie_consent');
    }
};
