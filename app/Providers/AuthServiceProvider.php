<?php

namespace App\Providers;

use App\Auth\LegacyCustomerProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Registra o provider customizado para autenticação de clientes
        // contra a tabela 'pessoas' do banco legado (senha MD5)
        Auth::provider('legacy_pessoa', function ($app, array $config) {
            return new LegacyCustomerProvider();
        });
    }
}
