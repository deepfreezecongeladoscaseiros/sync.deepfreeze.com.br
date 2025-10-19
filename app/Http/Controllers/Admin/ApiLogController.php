<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiRequestLog;
use Illuminate\Http\Request;

class ApiLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ApiRequestLog::query()->orderBy('created_at', 'desc');

        if ($request->filled('status_code')) {
            $query->where('status_code', $request->input('status_code'));
        }

        if ($request->filled('endpoint')) {
            $query->where('endpoint', 'like', '%' . $request->input('endpoint') . '%');
        }

        if ($request->filled('ip_address')) {
            $query->where('ip_address', $request->input('ip_address'));
        }

        if ($request->filled('method')) {
            $query->where('method', $request->input('method'));
        }

        if ($request->filled('errors_only') && $request->input('errors_only') === '1') {
            $query->errors();
        }

        if ($request->filled('slow_only') && $request->input('slow_only') === '1') {
            $query->slowRequests(1000);
        }

        if ($request->filled('hours')) {
            $query->recent($request->input('hours'));
        }

        $logs = $query->paginate(50)->withQueryString();

        $stats = [
            'total_requests' => ApiRequestLog::count(),
            'errors_today' => ApiRequestLog::errors()->where('created_at', '>=', now()->startOfDay())->count(),
            'slow_requests_today' => ApiRequestLog::slowRequests(1000)->where('created_at', '>=', now()->startOfDay())->count(),
            'avg_response_time' => round(ApiRequestLog::where('created_at', '>=', now()->subHours(24))->avg('response_time_ms')),
        ];

        return view('admin.api_logs.index', compact('logs', 'stats'));
    }

    public function show(ApiRequestLog $apiLog)
    {
        return view('admin.api_logs.show', compact('apiLog'));
    }

    public function destroy(ApiRequestLog $apiLog)
    {
        $apiLog->delete();
        return redirect()->route('admin.api_logs.index')->with('success', 'Log deletado com sucesso');
    }

    public function clearOld(Request $request)
    {
        $days = $request->input('days', 30);
        $deleted = ApiRequestLog::where('created_at', '<', now()->subDays($days))->delete();
        
        return redirect()->route('admin.api_logs.index')->with('success', "Deletados {$deleted} logs com mais de {$days} dias");
    }
}
