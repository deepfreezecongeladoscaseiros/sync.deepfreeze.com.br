<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Configuração dos ícones flutuantes (WhatsApp + Instagram)
 *
 * Tabela singleton (1 registro) — armazena configurações de exibição
 * dos botões flutuantes no storefront. Banco sync (configuração visual).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('floating_button_config', function (Blueprint $table) {
            $table->id();

            // Posição dos ícones na tela: 'left' ou 'right'
            $table->enum('position', ['left', 'right'])
                  ->default('right')
                  ->comment('Posição dos ícones: left = canto esquerdo, right = canto direito');

            // WhatsApp — se vazio, ícone não aparece
            $table->string('whatsapp_number', 20)
                  ->nullable()
                  ->comment('Número do WhatsApp com DDI+DDD (ex: 5521934783000). Vazio = ícone oculto');

            $table->string('whatsapp_message', 255)
                  ->nullable()
                  ->comment('Texto pré-configurado enviado ao abrir o WhatsApp');

            // Instagram — se vazio, ícone não aparece
            $table->string('instagram_url', 255)
                  ->nullable()
                  ->comment('URL do perfil do Instagram. Vazio = ícone oculto');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('floating_button_config');
    }
};
