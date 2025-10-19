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
        Schema::create('metatags', function (Blueprint $table) {
            $table->id();
            $table->morphs('metatagable'); // Isso criará metatagable_id e metatagable_type
            $table->string('type')->nullable()->comment('Ex: keywords, description');
            $table->text('content')->nullable();
            $table->integer('local')->nullable()->comment('Local da metatag, conforme especificação do produto');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metatags');
    }
};
