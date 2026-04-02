<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CepQueryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Controller: Estatísticas de Consultas de CEP (Admin)
 *
 * Exibe dados de consultas de "Entrega na minha região" para
 * a equipe de marketing analisar demanda por região.
 */
class CepStatsController extends Controller
{
    public function index(Request $request)
    {
        // Filtros
        $dateFrom  = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo    = $request->input('date_to', now()->format('Y-m-d'));
        $estado    = $request->input('estado');
        $cidade    = $request->input('cidade');
        $bairro    = $request->input('bairro');
        $atendido  = $request->input('atendido'); // '', '1', '0'

        // Query base com filtro de período
        $query = CepQueryLog::whereBetween('created_at', [
            $dateFrom . ' 00:00:00',
            $dateTo . ' 23:59:59',
        ]);

        if ($estado) {
            $query->where('estado', $estado);
        }
        if ($cidade) {
            $query->where('cidade', 'like', "%{$cidade}%");
        }
        if ($bairro) {
            $query->where('bairro', 'like', "%{$bairro}%");
        }
        if ($atendido !== null && $atendido !== '') {
            $query->where('atendido', (bool) $atendido);
        }

        // Totalizadores (sobre a query filtrada)
        $statsQuery = clone $query;
        $totalConsultas   = $statsQuery->count();
        $totalAtendidas   = (clone $statsQuery)->where('atendido', true)->count();
        $totalNaoAtendidas = $totalConsultas - $totalAtendidas;
        $percentAtendidas = $totalConsultas > 0 ? round(($totalAtendidas / $totalConsultas) * 100, 1) : 0;

        // Top 5 cidades não atendidas (para decisão de expansão)
        $topCidadesNaoAtendidas = CepQueryLog::whereBetween('created_at', [
                $dateFrom . ' 00:00:00',
                $dateTo . ' 23:59:59',
            ])
            ->where('atendido', false)
            ->whereNotNull('cidade')
            ->select('cidade', 'estado', DB::raw('COUNT(*) as total'))
            ->groupBy('cidade', 'estado')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Top 5 bairros não atendidos
        $topBairrosNaoAtendidos = CepQueryLog::whereBetween('created_at', [
                $dateFrom . ' 00:00:00',
                $dateTo . ' 23:59:59',
            ])
            ->where('atendido', false)
            ->whereNotNull('bairro')
            ->where('bairro', '!=', '')
            ->select('bairro', 'cidade', 'estado', DB::raw('COUNT(*) as total'))
            ->groupBy('bairro', 'cidade', 'estado')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Lista paginada
        $logs = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        // Estados disponíveis para o filtro select
        $estados = CepQueryLog::whereNotNull('estado')
            ->distinct()
            ->orderBy('estado')
            ->pluck('estado');

        return view('admin.cep-stats.index', compact(
            'logs',
            'totalConsultas',
            'totalAtendidas',
            'totalNaoAtendidas',
            'percentAtendidas',
            'topCidadesNaoAtendidas',
            'topBairrosNaoAtendidos',
            'estados',
            'dateFrom',
            'dateTo',
            'estado',
            'cidade',
            'bairro',
            'atendido'
        ));
    }
}
