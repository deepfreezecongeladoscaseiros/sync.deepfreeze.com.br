<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Storefront\Auth\LoginController;
use App\Http\Controllers\Storefront\Auth\CustomerRegisterController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas de autenticação de CLIENTES da loja (guard: customer)
|--------------------------------------------------------------------------
|
| Login e registro de clientes usam a tabela 'pessoas' do banco legado
| com senhas MD5 para manter compatibilidade com o SIV.
|
| O guard 'customer' é separado do guard 'web' (usado pelo admin).
*/

Route::middleware('guest:customer')->group(function () {
    // Registro de novo cliente — grava em 'pessoas' + 'enderecos' do banco legado
    Route::get('register', [CustomerRegisterController::class, 'create'])
                ->name('register');
    Route::post('register', [CustomerRegisterController::class, 'store']);

    // Login de clientes — autentica contra 'pessoas' (MD5)
    Route::get('login', [LoginController::class, 'create'])
                ->name('login');
    Route::post('login', [LoginController::class, 'store']);

    // Login alternativo por CPF + Data de Nascimento
    Route::post('login/cpf', [LoginController::class, 'storeByCpf'])
                ->name('login.cpf');

    // Recuperação de senha (usa rotas padrão do Breeze — tabela users/password_reset_tokens)
    // TODO: Adaptar para tabela 'pessoas' quando necessário
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
                ->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
                ->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
                ->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])
                ->name('password.store');
});

// Logout de clientes — guard 'customer'
Route::post('logout', [LoginController::class, 'destroy'])
            ->middleware('auth:customer')
            ->name('logout');

/*
|--------------------------------------------------------------------------
| Rotas de autenticação do ADMIN (guard: web) — inalteradas
|--------------------------------------------------------------------------
|
| Estas rotas continuam usando a tabela 'users' com bcrypt.
| Acessíveis em /admin/login e /admin/logout (definidas em web.php).
*/

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
                ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
                ->middleware(['signed', 'throttle:6,1'])
                ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                ->middleware('throttle:6,1')
                ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
                ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
});
