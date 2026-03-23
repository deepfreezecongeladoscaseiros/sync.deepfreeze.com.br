<?php

namespace App\Models\Legacy;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;

/**
 * Model: Pessoa/Cliente (tabela 'pessoas' do banco legado)
 *
 * Tabela central de clientes, colaboradores e fornecedores do SIV.
 * Para o e-commerce, usamos apenas clientes (colaborador_deep = NULL ou 0).
 *
 * Tabela: novo.pessoas
 * Engine: MyISAM
 * Charset: utf8mb3
 *
 * Autenticação: senha em MD5 (sem salt) — compatibilidade com SIV legado.
 */
class Pessoa extends Model implements AuthenticatableContract
{
    protected $connection = 'mysql_legacy';
    protected $table = 'pessoas';

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    protected $fillable = [
        'nome',
        'apelido',
        'nascimento',
        'cpf',
        'rg',
        'razao_social',
        'cnpj',
        'inscricao_estadual',
        'inscricao_municipal',
        'email_primario',
        'senha',
        'sexo',
        'telefone_residencial',
        'telefone_celular',
        'telefone_empresarial',
        'autoriza_newsletter',
        'aceita_ligacao',
        'aceita_sms',
        'aceita_whats_app',
        'data_cadastro',
        'nome_fantasia',
        'ativo',
    ];

    protected $hidden = [
        'senha',
    ];

    protected $casts = [
        'nascimento' => 'date',
        'data_cadastro' => 'datetime',
        'ativo' => 'boolean',
        'autoriza_newsletter' => 'boolean',
        'colaborador_deep' => 'boolean',
    ];

    /**
     * Mapeamento: nome inglês (Blade) → coluna real (banco legado)
     */
    protected $columnMap = [
        'name'                 => 'nome',
        'nickname'             => 'apelido',
        'email'                => 'email_primario',
        'password'             => 'senha',
        'birth_date'           => 'nascimento',
        'gender'               => 'sexo',
        'phone'                => 'telefone_celular',
        'phone_home'           => 'telefone_residencial',
        'phone_business'       => 'telefone_empresarial',
        'active'               => 'ativo',
        'company_name'         => 'razao_social',
        'trade_name'           => 'nome_fantasia',
        'state_registration'   => 'inscricao_estadual',
        'city_registration'    => 'inscricao_municipal',
        'accepts_newsletter'   => 'autoriza_newsletter',
        'registered_at'        => 'data_cadastro',
        'is_employee'          => 'colaborador_deep',
    ];

    /**
     * Resolve atributos mapeados: inglês → coluna legado
     */
    public function getAttribute($key)
    {
        if (isset($this->columnMap[$key])) {
            return parent::getAttribute($this->columnMap[$key]);
        }

        return parent::getAttribute($key);
    }

    // ==================== AUTHENTICATABLE ====================

    /**
     * Identificador único para autenticação (PK da tabela)
     */
    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return $this->id;
    }

    /**
     * Retorna a senha (MD5 hash) para comparação
     */
    public function getAuthPassword(): string
    {
        return $this->senha ?? '';
    }

    public function getRememberToken(): ?string
    {
        // Tabela pessoas não tem remember_token
        return null;
    }

    public function setRememberToken($value): void
    {
        // Tabela pessoas não suporta remember_token — ignorar silenciosamente
    }

    public function getRememberTokenName(): string
    {
        return '';
    }

    // ==================== HELPERS ====================

    /**
     * Verifica se a pessoa é pessoa jurídica (tem CNPJ preenchido)
     */
    public function isPessoaJuridica(): bool
    {
        return !empty($this->cnpj) && strlen(preg_replace('/\D/', '', $this->cnpj)) === 14;
    }

    /**
     * Retorna CPF sem formatação (apenas dígitos)
     */
    public function getCpfDigitsAttribute(): string
    {
        return preg_replace('/\D/', '', $this->cpf ?? '');
    }

    /**
     * Retorna CNPJ sem formatação (apenas dígitos)
     */
    public function getCnpjDigitsAttribute(): string
    {
        return preg_replace('/\D/', '', $this->cnpj ?? '');
    }

    /**
     * Nome de exibição: apelido se existir, senão nome
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->apelido ?: $this->nome ?? '';
    }

    /**
     * Verifica se o cadastro está completo para realizar pedido
     * Replica a lógica de Pessoa::cadastro_esta_completo() do legado
     */
    public function isProfileComplete(): bool
    {
        return !empty($this->nome)
            && !empty($this->nascimento)
            && !empty($this->email_primario)
            && !empty($this->sexo)
            && strlen($this->cpf_digits) === 11
            && (!empty($this->telefone_residencial) || !empty($this->telefone_celular));
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Endereços da pessoa
     */
    public function enderecos()
    {
        return $this->hasMany(Endereco::class, 'pessoa_id')
            ->where('ativo', 1)
            ->orderByDesc('end_principal')
            ->orderByDesc('ultimo_endereco_usado');
    }

    /**
     * Pedidos da pessoa
     */
    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'pessoa_id');
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Apenas clientes ativos
     */
    public function scopeActive($query)
    {
        return $query->where('ativo', 1);
    }

    /**
     * Scope: Buscar por email
     */
    public function scopeByEmail($query, string $email)
    {
        return $query->where('email_primario', strtolower(trim($email)));
    }

    /**
     * Scope: Buscar por CPF (apenas dígitos)
     */
    public function scopeByCpf($query, string $cpf)
    {
        $digits = preg_replace('/\D/', '', $cpf);
        return $query->where('cpf', $digits);
    }
}
