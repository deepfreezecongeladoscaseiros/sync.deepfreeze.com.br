<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Altera o campo 'icon' para 'icon_path' para armazenar o caminho da imagem
     * em vez de classe CSS do ícone.
     */
    public function up(): void
    {
        Schema::table('feature_blocks', function (Blueprint $table) {
            $table->renameColumn('icon', 'icon_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feature_blocks', function (Blueprint $table) {
            $table->renameColumn('icon_path', 'icon');
        });
    }
};
