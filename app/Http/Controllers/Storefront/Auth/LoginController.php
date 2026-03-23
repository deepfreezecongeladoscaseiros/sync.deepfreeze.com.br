<?php

namespace App\Http\Controllers\Storefront\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Controller de login para clientes da loja.
 *
 * Autentica contra a tabela 'pessoas' do banco legado via guard 'customer'.
 * Suporta dois modos de login:
 * 1. Email + Senha (padrão)
 * 2. CPF + Data de Nascimento (alternativo, sem senha)
 */
class LoginController extends Controller
{
    /**
     * Exibe o formulário de login da loja.
     */
    public function create(): View
    {
        return view('storefront.auth.login');
    }

    /**
     * Processa a autenticação do cliente via email + senha.
     * Usa guard 'customer' que valida MD5 contra tabela 'pessoas'.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'password.required' => 'A senha é obrigatória.',
        ]);

        // Rate limiting para proteção contra brute force
        $this->ensureIsNotRateLimited($request);

        // Tenta autenticar com o guard 'customer' (tabela pessoas, MD5)
        if (!Auth::guard('customer')->attempt(
            $request->only('email', 'password'),
            $request->boolean('remember')
        )) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'email' => 'E-mail ou senha incorretos.',
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));

        $request->session()->regenerate();

        // Redireciona para a página que o cliente tentava acessar, ou para a home
        return redirect()->intended('/');
    }

    /**
     * Processa a autenticação do cliente via CPF + Data de Nascimento.
     * Modo alternativo para clientes que esqueceram a senha.
     */
    public function storeByCpf(Request $request): RedirectResponse
    {
        $request->validate([
            'cpf' => ['required', 'string', 'min:11'],
            'birth_date_login' => ['required', 'string', 'min:10'],
        ], [
            'cpf.required' => 'O CPF é obrigatório.',
            'birth_date_login.required' => 'A data de nascimento é obrigatória.',
        ]);

        $this->ensureIsNotRateLimited($request);

        // Converte data de DD/MM/YYYY para YYYY-MM-DD
        $birthDateParts = explode('/', $request->input('birth_date_login'));
        $birthDate = null;
        if (count($birthDateParts) === 3) {
            $birthDate = "{$birthDateParts[2]}-{$birthDateParts[1]}-{$birthDateParts[0]}";
        }

        // Tenta autenticar por CPF + nascimento
        if (!Auth::guard('customer')->attempt([
            'cpf' => $request->input('cpf'),
            'birth_date' => $birthDate,
        ])) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'cpf' => 'CPF ou data de nascimento incorretos.',
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));

        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    /**
     * Processa o logout do cliente.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Proteção contra rate limiting (brute force)
     */
    protected function ensureIsNotRateLimited(Request $request): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => "Muitas tentativas. Tente novamente em {$seconds} segundos.",
        ]);
    }

    /**
     * Chave de throttling por IP + email/CPF
     */
    protected function throttleKey(Request $request): string
    {
        $identifier = $request->input('email', $request->input('cpf', ''));
        return Str::transliterate(Str::lower($identifier) . '|' . $request->ip());
    }
}
