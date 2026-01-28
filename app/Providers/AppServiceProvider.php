<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFour();

        // Query logging: registra total de queries e tempo por request (apenas em ambiente local)
        if (app()->environment('local')) {
            DB::enableQueryLog();

            // Ao final de cada request, loga o resumo de queries executadas
            app()->terminating(function () {
                $queries = DB::getQueryLog();
                $totalQueries = count($queries);
                $totalTime = array_sum(array_column($queries, 'time'));

                Log::channel('stderr')->info("[QUERY LOG] {$totalQueries} queries | {$totalTime}ms | " . request()->method() . ' ' . request()->fullUrl());

                // Se houver mais de 20 queries, loga como alerta com detalhes
                if ($totalQueries > 20) {
                    Log::channel('stderr')->warning("[QUERY LOG] EXCESSO DE QUERIES ({$totalQueries}). Detalhes:");
                    foreach ($queries as $i => $query) {
                        Log::channel('stderr')->warning("[QUERY #{$i}] {$query['time']}ms | {$query['query']}");
                    }
                }
            });
        }
    }
}
