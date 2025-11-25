<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabela de configurações da página de Contato.
     * Armazena informações de contato, textos e configurações de envio de e-mail.
     */
    public function up(): void
    {
        Schema::create('contact_settings', function (Blueprint $table) {
            $table->id();

            // Título da página
            $table->string('page_title')->default('Contato'); // Título exibido no banner e breadcrumb

            // Texto introdutório do formulário
            $table->text('intro_text')->nullable(); // Texto acima do formulário explicando como entrar em contato

            // Informações de contato
            $table->string('whatsapp')->nullable(); // Número do WhatsApp (ex: 5511947446739)
            $table->string('whatsapp_display')->nullable(); // Exibição formatada (ex: (11) 94744-6739)
            $table->string('email')->nullable(); // E-mail de contato exibido na página
            $table->text('business_hours')->nullable(); // Horário de atendimento (pode ter múltiplas linhas)

            // Configurações de envio do formulário
            $table->string('form_recipient_email')->nullable(); // E-mail que receberá as mensagens do formulário
            $table->string('form_subject')->default('Nova mensagem de contato'); // Assunto do e-mail enviado

            // Banner interno (opcional)
            $table->string('banner_image')->nullable(); // Imagem do banner interno

            // SEO
            $table->string('meta_title')->nullable(); // Título para SEO
            $table->text('meta_description')->nullable(); // Descrição para SEO

            // Status
            $table->boolean('active')->default(true); // Se a página está ativa

            $table->timestamps();
        });

        // Também criar tabela para armazenar mensagens recebidas (opcional mas útil)
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();

            $table->string('name'); // Nome do remetente
            $table->string('email'); // E-mail do remetente
            $table->string('phone')->nullable(); // Telefone do remetente
            $table->text('message'); // Mensagem enviada

            $table->string('ip_address')->nullable(); // IP de origem (para segurança)
            $table->string('user_agent')->nullable(); // Navegador usado

            $table->boolean('read')->default(false); // Se a mensagem foi lida
            $table->timestamp('read_at')->nullable(); // Quando foi lida

            $table->timestamps();

            $table->index('read'); // Índice para filtrar mensagens não lidas
            $table->index('created_at'); // Índice para ordenação
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('contact_settings');
    }
};
