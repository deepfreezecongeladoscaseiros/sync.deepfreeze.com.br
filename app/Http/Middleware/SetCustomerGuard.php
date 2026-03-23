<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware que define o guard 'customer' como padrão para rotas da storefront.
 *
 * Isso faz com que @auth, @guest, auth()->user(), auth()->check()
 * funcionem automaticamente com a tabela 'pessoas' do banco legado,
 * sem precisar especificar auth('customer') em cada lugar.
 */
class SetCustomerGuard
{
    public function handle(Request $request, Closure $next): Response
    {
        // Define 'customer' como guard padrão para esta requisição
        Auth::shouldUse('customer');

        return $next($request);
    }
}
