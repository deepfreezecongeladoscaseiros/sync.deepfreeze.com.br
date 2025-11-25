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
            // Campo para armazenar o caminho da logo (360x82px recomendado)
            $table->string('logo_path')->nullable()->after('name');

            // Campo para armazenar o alt text da logo (acessibilidade)
            $table->string('logo_alt')->default('Logo')->after('logo_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('theme_settings', function (Blueprint $table) {
            $table->dropColumn(['logo_path', 'logo_alt']);
        });
    }
};
