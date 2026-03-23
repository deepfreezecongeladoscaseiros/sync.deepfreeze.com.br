<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    /**
     * Redireciona usuários autenticados para o destino correto.
     * Se está acessando rota /admin/*, vai para o dashboard admin.
     * Caso contrário, vai para a home da loja.
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Se está tentando acessar área admin, redireciona para dashboard
                if ($request->is('admin/*') || $request->is('admin')) {
                    return redirect()->route('admin.dashboard');
                }

                // Caso contrário, redireciona para a home da loja
                return redirect('/');
            }
        }

        return $next($request);
    }
}
