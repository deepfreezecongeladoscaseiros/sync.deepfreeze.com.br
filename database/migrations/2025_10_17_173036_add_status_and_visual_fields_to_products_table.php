<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('active')->default(true)->after('virtual_product');
            $table->boolean('is_package')->default(false)->after('active');
            $table->boolean('is_combo')->default(false)->after('is_package');
            $table->boolean('is_gift_card')->default(false)->after('is_combo');
            $table->boolean('made_to_order')->default(false)->after('is_gift_card');
            $table->date('order_deadline')->nullable()->after('made_to_order');
            
            $table->string('background_color', 7)->default('#F0F0F0')->after('order_deadline');
            $table->string('text_color', 7)->default('#000000')->after('background_color');
            $table->integer('display_order')->default(0)->after('text_color');
            
            $table->decimal('ifood_percentage', 10, 2)->nullable()->after('display_order');
            $table->decimal('ifood_promotion_percentage', 10, 2)->nullable()->after('ifood_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'active',
                'is_package',
                'is_combo',
                'is_gift_card',
                'made_to_order',
                'order_deadline',
                'background_color',
                'text_color',
                'display_order',
                'ifood_percentage',
                'ifood_promotion_percentage',
            ]);
        });
    }
};
