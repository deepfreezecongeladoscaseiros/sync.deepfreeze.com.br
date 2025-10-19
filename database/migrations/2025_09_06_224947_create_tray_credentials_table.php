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
        Schema::create('tray_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('store_id')->unique();
            $table->string('api_host');
            $table->string('consumer_key');
            $table->string('consumer_secret');
            $table->string('code');
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('date_expiration_access_token')->nullable();
            $table->timestamp('date_expiration_refresh_token')->nullable();
            $table->timestamp('date_activated')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tray_credentials');
    }
};
