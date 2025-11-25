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
        Schema::table('theme_settings', function (Blueprint $table) {
            // Top Bar (Barra de Anúncios no topo do site)
            $table->boolean('top_bar_enabled')->default(false)->after('logo_alt'); // Ativa/desativa a top bar
            $table->text('top_bar_text')->nullable()->after('top_bar_enabled'); // Texto com suporte a HTML
            $table->string('top_bar_bg_color')->default('#013E3B')->after('top_bar_text'); // Cor de fundo (verde escuro padrão)
            $table->string('top_bar_text_color')->default('#FFFFFF')->after('top_bar_bg_color'); // Cor do texto (branco padrão)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('theme_settings', function (Blueprint $table) {
            $table->dropColumn(['top_bar_enabled', 'top_bar_text', 'top_bar_bg_color', 'top_bar_text_color']);
        });
    }
};
