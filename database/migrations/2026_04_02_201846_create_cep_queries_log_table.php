<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Log de consultas de CEP
 *
 * Registra cada consulta de "Entrega na minha região" feita no storefront.
 * Banco sync — dados de marketing/estatísticas.
 * Enriquecido com estado, cidade e bairro via ViaCEP.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cep_queries_log', function (Blueprint $table) {
            $table->id();
            $table->string('cep', 9)->comment('CEP consultado');
            $table->boolean('atendido')->comment('true = CEP está em região de entrega');
            $table->string('estado', 2)->nullable()->comment('UF obtida via ViaCEP');
            $table->string('cidade', 100)->nullable()->comment('Cidade obtida via ViaCEP');
            $table->string('bairro', 100)->nullable()->comment('Bairro obtido via ViaCEP');
            $table->unsignedInteger('regiao_id')->nullable()->comment('ID da entregas_regioes (se atendido)');
            $table->unsignedInteger('loja_id')->nullable()->comment('ID da loja que atende (se atendido)');
            $table->timestamp('created_at')->useCurrent()->comment('Data/hora da consulta');

            $table->index('created_at');
            $table->index('atendido');
            $table->index('estado');
            $table->index('cidade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cep_queries_log');
    }
};
