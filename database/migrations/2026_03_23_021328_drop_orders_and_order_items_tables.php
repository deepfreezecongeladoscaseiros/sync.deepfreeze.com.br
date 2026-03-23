<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Remove as tabelas 'orders' e 'order_items' do banco do sync.
 *
 * Motivo: Os pedidos agora são gravados diretamente nas tabelas
 * 'pedidos' e 'pedidos_produtos' do banco legado (mysql_legacy)
 * via LegacyOrderService. Essas tabelas do sync continham apenas
 * dados de teste e não são mais utilizadas.
 *
 * Models removidos: App\Models\Order, App\Models\OrderItem
 * Service removido: App\Services\OrderService
 */
return new class extends Migration
{
    public function up(): void
    {
        // order_items tem FK para orders — dropar primeiro
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }

    public function down(): void
    {
        // Recriação não implementada — tabelas eram de uso temporário
        // Se necessário, consultar migration original:
        // database/migrations/2026_02_28_222946_create_orders_table.php
    }
};
