<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration para criação das tabelas de pedidos (orders) e itens de pedido (order_items).
 *
 * Os dados do cliente e dos produtos são desnormalizados intencionalmente:
 * isso preserva o histórico do pedido mesmo que o cadastro do cliente
 * ou os dados do produto sejam alterados posteriormente.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Tabela de pedidos
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Número do pedido no formato DF-YYYYMMDD-NNN (ex: DF-20260228-001)
            $table->string('order_number', 20)->unique();

            // Usuário logado (nullable para permitir compras como convidado)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Status do pedido
            // pending_payment = Aguardando Pagamento (padrão ao criar)
            // paid = Pago
            // processing = Em Preparação
            // shipped = Enviado
            // delivered = Entregue
            // cancelled = Cancelado
            $table->enum('status', [
                'pending_payment',
                'paid',
                'processing',
                'shipped',
                'delivered',
                'cancelled',
            ])->default('pending_payment');

            // --- Dados do cliente (desnormalizados) ---
            $table->string('customer_name', 200);          // Nome completo (nome + sobrenome)
            $table->string('customer_email', 255);          // E-mail do cliente
            $table->string('customer_phone', 20);           // Telefone
            $table->enum('customer_person_type', ['fisica', 'juridica']); // Tipo de pessoa
            $table->string('customer_cpf', 14)->nullable(); // CPF (apenas PF)
            $table->string('customer_cnpj', 18)->nullable(); // CNPJ (apenas PJ)

            // --- Endereço de entrega (desnormalizado) ---
            $table->string('shipping_zip_code', 10);        // CEP
            $table->string('shipping_address', 255);         // Logradouro
            $table->string('shipping_number', 20);           // Número
            $table->string('shipping_complement', 100)->nullable(); // Complemento
            $table->string('shipping_neighborhood', 100);    // Bairro
            $table->string('shipping_city', 100);            // Cidade
            $table->string('shipping_state', 2);             // UF (sigla)

            // --- Valores ---
            $table->decimal('subtotal', 10, 2);              // Subtotal dos itens
            $table->decimal('shipping_cost', 10, 2)->default(0); // Frete (0 por enquanto)
            $table->decimal('discount', 10, 2)->default(0);  // Desconto (0 por enquanto)
            $table->decimal('total', 10, 2);                 // Total final

            // --- Observações e metadados ---
            $table->text('notes')->nullable();               // Observações do cliente
            $table->string('ip_address', 45)->nullable();    // IP do cliente (IPv4/IPv6)
            $table->text('user_agent')->nullable();           // User-Agent do navegador

            $table->timestamps();

            // Índices para consultas frequentes
            $table->index('status');
            $table->index('customer_email');
            $table->index('created_at');
        });

        // Tabela de itens do pedido
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            // Referência ao pedido (cascade: ao excluir pedido, exclui itens)
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            // Referência ao produto (nullable: produto pode ser excluído depois)
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();

            // --- Dados do produto (desnormalizados) ---
            $table->string('product_name', 255);             // Nome do produto no momento da compra
            $table->string('product_sku', 100)->nullable();  // SKU do produto
            $table->string('product_image', 500)->nullable(); // URL da imagem principal

            // --- Valores ---
            $table->decimal('unit_price', 10, 2);            // Preço unitário no momento da compra
            $table->unsignedInteger('quantity');              // Quantidade comprada
            $table->decimal('total', 10, 2);                 // Total do item (unit_price * quantity)

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
