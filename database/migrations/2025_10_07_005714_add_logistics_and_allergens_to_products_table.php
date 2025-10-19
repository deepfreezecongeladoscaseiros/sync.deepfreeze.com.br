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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('gross_weight', 10, 3)->nullable()->after('weight');
            $table->string('weight_unit', 10)->default('g')->after('gross_weight');
            
            $table->integer('shelf_life_days')->nullable()->after('stock');
            
            $table->integer('portion_size')->nullable()->after('shelf_life_days');
            $table->string('home_measure', 100)->nullable()->after('portion_size');
            
            $table->boolean('contains_gluten')->default(false)->after('home_measure');
            $table->boolean('lactose_free')->default(false)->after('contains_gluten');
            $table->boolean('low_lactose')->default(false)->after('lactose_free');
            $table->boolean('contains_lactose')->default(false)->after('low_lactose');
            $table->text('allergens')->nullable()->after('contains_lactose');
            
            $table->boolean('alcoholic_beverage')->default(false)->after('allergens');
            $table->string('label_description', 120)->nullable()->after('alcoholic_beverage');
            $table->text('description_english')->nullable()->after('label_description');
            $table->time('freezing_time')->nullable()->after('description_english');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'gross_weight',
                'weight_unit',
                'shelf_life_days',
                'portion_size',
                'home_measure',
                'contains_gluten',
                'lactose_free',
                'low_lactose',
                'contains_lactose',
                'allergens',
                'alcoholic_beverage',
                'label_description',
                'description_english',
                'freezing_time',
            ]);
        });
    }
};
