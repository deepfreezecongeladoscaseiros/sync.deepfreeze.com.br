<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brand_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('integration_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('legacy_id')->nullable();
            $table->enum('event_type', ['create', 'update'])->default('update');
            $table->json('payload');
            $table->json('headers')->nullable();
            $table->enum('status', ['pending', 'processing', 'success', 'failed'])->default('pending');
            $table->timestamp('processed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('legacy_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brand_webhook_logs');
    }
};
