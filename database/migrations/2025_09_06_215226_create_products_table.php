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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('ean', 120)->nullable()->unique();
            $table->string('name', 200);
            $table->string('ncm', 8)->nullable();
            $table->text('description')->nullable();
            $table->string('description_small', 500)->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->decimal('promotional_price', 10, 2)->nullable();
            $table->date('start_promotion')->nullable();
            $table->date('end_promotion')->nullable();
            $table->decimal('ipi_value', 10, 2)->nullable();
            $table->foreignId('brand_id')->nullable()->constrained()->onDelete('set null');
            $table->string('model', 150)->nullable();
            $table->decimal('weight', 10, 3)->nullable(); // Peso em kg
            $table->decimal('length', 10, 2)->nullable(); // Comprimento em cm
            $table->decimal('width', 10, 2)->nullable(); // Largura em cm
            $table->decimal('height', 10, 2)->nullable(); // Altura em cm
            $table->integer('stock')->default(0);
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->boolean('available')->default(true);
            $table->boolean('available_in_store')->default(true);
            $table->string('availability')->nullable();
            $table->integer('availability_days')->nullable();
            $table->string('reference', 120)->nullable();
            $table->boolean('hot')->default(false);
            $table->boolean('release')->default(false);
            $table->boolean('additional_button')->default(false);
            $table->text('related_categories')->nullable()->comment('Comma-separated category IDs');
            $table->date('release_date')->nullable();
            $table->boolean('virtual_product')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
