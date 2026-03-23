<?php

namespace App\Auth;

use App\Models\Legacy\Pessoa;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

/**
 * Provider de autenticação contra a tabela 'pessoas' do banco legado.
 *
 * O SIV armazena senhas em MD5 puro (sem salt).
 * Este provider valida credenciais compatíveis com o legado:
 * - Login por email + senha (MD5)
 * - Login por CPF + data de nascimento (sem senha)
 *
 * Não altera o hash — novos cadastros também gravam MD5 para manter
 * compatibilidade com o SIV.
 */
class LegacyCustomerProvider implements UserProvider
{
    /**
     * Busca pessoa pelo ID (usado para restaurar sessão)
     */
    public function retrieveById($identifier): ?Authenticatable
    {
        return Pessoa::where('ativo', 1)->find($identifier);
    }

    /**
     * Busca pessoa pelo ID + remember token
     * Tabela pessoas não suporta remember_token — retorna null
     */
    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        return null;
    }

    /**
     * Atualiza remember token — não suportado pela tabela pessoas
     */
    public function updateRememberToken(Authenticatable $user, $token): void
    {
        // Tabela pessoas não tem coluna remember_token — ignorar
    }

    /**
     * Busca pessoa pelas credenciais (email ou CPF)
     *
     * Suporta dois modos de busca:
     * 1. Por email: ['email' => 'x@x.com', 'password' => '...']
     * 2. Por CPF: ['cpf' => '12345678901', 'birth_date' => '1990-01-15']
     */
    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        // Ignora campo password na busca (validado em validateCredentials)
        if (isset($credentials['email'])) {
            return Pessoa::where('ativo', 1)
                ->where('email_primario', strtolower(trim($credentials['email'])))
                ->first();
        }

        // Login alternativo por CPF + nascimento
        if (isset($credentials['cpf'])) {
            $cpf = preg_replace('/\D/', '', $credentials['cpf']);

            return Pessoa::where('ativo', 1)
                ->where('cpf', $cpf)
                ->first();
        }

        return null;
    }

    /**
     * Valida as credenciais da pessoa
     *
     * Modo email: compara md5(senha_digitada) com pessoas.senha
     * Modo CPF: compara data de nascimento
     */
    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        // Modo email + senha (MD5)
        if (isset($credentials['password'])) {
            $senhaDigitada = md5($credentials['password']);
            $senhaArmazenada = $user->getAuthPassword();

            // Senha no banco pode ser vazia ou '0' — rejeitar
            if (empty($senhaArmazenada) || strlen($senhaArmazenada) < 5) {
                return false;
            }

            return $senhaDigitada === $senhaArmazenada;
        }

        // Modo CPF + data de nascimento
        if (isset($credentials['birth_date']) && $user instanceof Pessoa) {
            $nascimentoInformado = $credentials['birth_date'];
            $nascimentoBanco = $user->nascimento?->format('Y-m-d');

            if (empty($nascimentoBanco)) {
                return false;
            }

            return $nascimentoInformado === $nascimentoBanco;
        }

        return false;
    }

    /**
     * Reabilitar autenticação (Laravel 10+)
     */
    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        // MD5 não deve ser rehashed — manter compatibilidade com SIV
    }
}
