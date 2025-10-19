<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_request_logs', function (Blueprint $table) {
            $table->foreignId('integration_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index('integration_id');
        });
    }

    public function down(): void
    {
        Schema::table('api_request_logs', function (Blueprint $table) {
            $table->dropForeign(['integration_id']);
            $table->dropIndex(['integration_id']);
            $table->dropColumn('integration_id');
        });
    }
};
