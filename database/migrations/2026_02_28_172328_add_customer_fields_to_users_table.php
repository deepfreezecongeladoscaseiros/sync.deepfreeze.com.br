<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration para adicionar campos de cliente à tabela users.
 * Permite o cadastro completo de Pessoa Física e Jurídica,
 * com endereço e preferências de comunicação.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Dados pessoais básicos
            $table->string('surname')->nullable()->after('name'); // Sobrenome do cliente
            $table->enum('person_type', ['fisica', 'juridica'])->default('fisica')->after('surname'); // Tipo de pessoa: fisica = CPF, juridica = CNPJ
            $table->string('cpf', 14)->nullable()->unique()->after('person_type'); // CPF formatado (999.999.999-99) — obrigatório para Pessoa Física
            $table->string('cnpj', 18)->nullable()->unique()->after('cpf'); // CNPJ formatado (99.999.999/9999-99) — obrigatório para Pessoa Jurídica
            $table->string('company_name')->nullable()->after('cnpj'); // Razão social da empresa (apenas Pessoa Jurídica)
            $table->string('state_registration')->nullable()->after('company_name'); // Inscrição estadual (apenas Pessoa Jurídica, pode ser "Isento")
            $table->enum('gender', ['m', 'f'])->nullable()->after('state_registration'); // Gênero: m = Masculino, f = Feminino
            $table->date('birth_date')->nullable()->after('gender'); // Data de nascimento
            $table->string('phone', 20)->nullable()->after('birth_date'); // Telefone/WhatsApp com DDD — formato (99) 99999-9999

            // Endereço
            $table->string('zip_code', 9)->nullable()->after('phone'); // CEP formatado (99999-999)
            $table->string('address')->nullable()->after('zip_code'); // Logradouro (rua, avenida, etc.)
            $table->string('number', 10)->nullable()->after('address'); // Número do endereço
            $table->string('complement')->nullable()->after('number'); // Complemento (apto, bloco, sala, etc.)
            $table->string('neighborhood')->nullable()->after('complement'); // Bairro
            $table->string('city')->nullable()->after('neighborhood'); // Cidade
            $table->string('state', 2)->nullable()->after('city'); // Estado (UF) — sigla de 2 caracteres

            // Preferências
            $table->boolean('newsletter')->default(true)->after('state'); // Aceita receber newsletter e promoções por e-mail
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'surname',
                'person_type',
                'cpf',
                'cnpj',
                'company_name',
                'state_registration',
                'gender',
                'birth_date',
                'phone',
                'zip_code',
                'address',
                'number',
                'complement',
                'neighborhood',
                'city',
                'state',
                'newsletter',
            ]);
        });
    }
};
