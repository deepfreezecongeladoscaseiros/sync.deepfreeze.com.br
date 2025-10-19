<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('presentation')->nullable()->after('description');
            $table->text('properties')->nullable()->after('presentation');
            $table->text('benefits')->nullable()->after('properties');
            $table->text('chef_tips')->nullable()->after('benefits');
            $table->text('dish_history')->nullable()->after('chef_tips');
            
            $table->text('ingredients')->nullable()->after('dish_history');
            $table->text('consumption_instructions')->nullable()->after('ingredients');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'presentation',
                'properties',
                'benefits',
                'chef_tips',
                'dish_history',
                'ingredients',
                'consumption_instructions',
            ]);
        });
    }
};
