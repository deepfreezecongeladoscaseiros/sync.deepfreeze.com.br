@extends('adminlte::page')

@section('title', 'API Request Logs')

@section('content_header')
    <h1>API Request Logs</h1>
@stop

@section('content')
    <div class="row mb-3">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($stats['total_requests']) }}</h3>
                    <p>Total Requests</p>
                </div>
                <div class="icon">
                    <i class="fas fa-globe"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['errors_today'] }}</h3>
                    <p>Errors Today</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['slow_requests_today'] }}</h3>
                    <p>Slow Requests Today</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['avg_response_time'] }}ms</h3>
                    <p>Avg Response (24h)</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tachometer-alt"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">Filtros</h3>
                <div>
                    <form action="{{ route('admin.api_logs.clearOld') }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja deletar logs antigos?')">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="days" value="30">
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i> Limpar logs > 30 dias
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.api_logs.index') }}" method="GET">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Método</label>
                            <select name="method" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                <option value="GET" {{ request('method') === 'GET' ? 'selected' : '' }}>GET</option>
                                <option value="POST" {{ request('method') === 'POST' ? 'selected' : '' }}>POST</option>
                                <option value="PUT" {{ request('method') === 'PUT' ? 'selected' : '' }}>PUT</option>
                                <option value="DELETE" {{ request('method') === 'DELETE' ? 'selected' : '' }}>DELETE</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Endpoint</label>
                            <input type="text" name="endpoint" class="form-control form-control-sm" value="{{ request('endpoint') }}" placeholder="api/v1/products">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Status Code</label>
                            <select name="status_code" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                <option value="200" {{ request('status_code') == '200' ? 'selected' : '' }}>200 OK</option>
                                <option value="400" {{ request('status_code') == '400' ? 'selected' : '' }}>400 Bad Request</option>
                                <option value="404" {{ request('status_code') == '404' ? 'selected' : '' }}>404 Not Found</option>
                                <option value="429" {{ request('status_code') == '429' ? 'selected' : '' }}>429 Rate Limit</option>
                                <option value="500" {{ request('status_code') == '500' ? 'selected' : '' }}>500 Server Error</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>IP Address</label>
                            <input type="text" name="ip_address" class="form-control form-control-sm" value="{{ request('ip_address') }}" placeholder="127.0.0.1">
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label>Horas</label>
                            <input type="number" name="hours" class="form-control form-control-sm" value="{{ request('hours') }}" placeholder="24">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="errors_only" name="errors_only" value="1" {{ request('errors_only') ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="errors_only">Erros</label>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="slow_only" name="slow_only" value="1" {{ request('slow_only') ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="slow_only">Lentas (>1s)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        <a href="{{ route('admin.api_logs.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-times"></i> Limpar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th style="width: 50px">ID</th>
                        <th style="width: 70px">Method</th>
                        <th>Endpoint</th>
                        <th style="width: 100px">Status</th>
                        <th style="width: 100px">Response Time</th>
                        <th style="width: 120px">IP</th>
                        <th style="width: 150px">Date</th>
                        <th style="width: 100px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr class="{{ $log->isError() ? 'table-danger' : '' }}">
                            <td>{{ $log->id }}</td>
                            <td>
                                <span class="badge badge-{{ $log->method === 'GET' ? 'info' : 'primary' }}">
                                    {{ $log->method }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $log->endpoint }}</small>
                                @if($log->query_params)
                                    <br><small class="text-muted">{{ http_build_query($log->query_params) }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $log->status_badge }}">
                                    {{ $log->status_code }}
                                </span>
                                @if($log->error_type)
                                    <br><small class="text-muted">{{ $log->error_type }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $log->response_time_color }}">
                                    {{ $log->response_time_ms }}ms
                                </span>
                            </td>
                            <td><small>{{ $log->ip_address }}</small></td>
                            <td><small>{{ $log->created_at->format('d/m/Y H:i:s') }}</small></td>
                            <td>
                                <a href="{{ route('admin.api_logs.show', $log->id) }}" class="btn btn-xs btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <form action="{{ route('admin.api_logs.destroy', $log->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Deletar este log?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">Nenhum log encontrado</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $logs->links() }}
        </div>
    </div>
@stop
