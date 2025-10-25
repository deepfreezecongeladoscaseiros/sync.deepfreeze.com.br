@extends('adminlte::page')

@section('title', 'API Request Log Details')

@section('content_header')
    <h1>API Request Log #{{ $apiLog->id }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Request Information</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 150px">Método</th>
                            <td>
                                <span class="badge badge-{{ $apiLog->method === 'GET' ? 'info' : 'primary' }}">
                                    {{ $apiLog->method }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Endpoint</th>
                            <td><code>{{ $apiLog->endpoint }}</code></td>
                        </tr>
                        <tr>
                            <th>Full URL</th>
                            <td><small>{{ $apiLog->full_url }}</small></td>
                        </tr>
                        <tr>
                            <th>Endereço IP</th>
                            <td>{{ $apiLog->ip_address }}</td>
                        </tr>
                        <tr>
                            <th>Navegador</th>
                            <td><small>{{ $apiLog->user_agent }}</small></td>
                        </tr>
                        <tr>
                            <th>Date/Time</th>
                            <td>{{ $apiLog->created_at->format('d/m/Y H:i:s') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Response Information</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 150px">Status Code</th>
                            <td>
                                <span class="badge badge-{{ $apiLog->status_badge }}">
                                    {{ $apiLog->status_code }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Response Time</th>
                            <td>
                                <span class="badge badge-{{ $apiLog->response_time_color }}">
                                    {{ $apiLog->response_time_ms }}ms
                                </span>
                                @if($apiLog->isSlow())
                                    <span class="badge badge-warning">SLOW</span>
                                @endif
                            </td>
                        </tr>
                        @if($apiLog->error_type)
                            <tr>
                                <th>Error Type</th>
                                <td><span class="badge badge-danger">{{ $apiLog->error_type }}</span></td>
                            </tr>
                        @endif
                        @if($apiLog->error_message)
                            <tr>
                                <th>Error Message</th>
                                <td class="text-danger">{{ $apiLog->error_message }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if($apiLog->query_params)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Query Parameters</h3>
            </div>
            <div class="card-body">
                <pre><code>{{ json_encode($apiLog->query_params, JSON_PRETTY_PRINT) }}</code></pre>
            </div>
        </div>
    @endif

    @if($apiLog->request_body)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Request Body</h3>
            </div>
            <div class="card-body">
                <pre><code>{{ json_encode($apiLog->request_body, JSON_PRETTY_PRINT) }}</code></pre>
            </div>
        </div>
    @endif

    @if($apiLog->headers)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Headers</h3>
            </div>
            <div class="card-body">
                <pre><code>{{ json_encode($apiLog->headers, JSON_PRETTY_PRINT) }}</code></pre>
            </div>
        </div>
    @endif

    @if($apiLog->response_body)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Response Body</h3>
            </div>
            <div class="card-body">
                <pre><code>{{ json_encode($apiLog->response_body, JSON_PRETTY_PRINT) }}</code></pre>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-footer">
            <a href="{{ route('admin.api_logs.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <form action="{{ route('admin.api_logs.destroy', $apiLog->id) }}" method="POST" style="display:inline-block;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Deletar este log?')">
                    <i class="fas fa-trash"></i> Deletar
                </button>
            </form>
        </div>
    </div>
@stop
