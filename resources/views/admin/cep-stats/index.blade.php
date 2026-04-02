@extends('adminlte::page')

@section('title', 'Estatísticas de CEP')

@section('content_header')
    <h1>Consultas de CEP — Entrega na minha região</h1>
@stop

@section('content')

    {{-- Cards totalizadores --}}
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($totalConsultas, 0, ',', '.') }}</h3>
                    <p>Total de Consultas</p>
                </div>
                <div class="icon"><i class="fas fa-search"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($totalAtendidas, 0, ',', '.') }}</h3>
                    <p>Atendidas ({{ $percentAtendidas }}%)</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($totalNaoAtendidas, 0, ',', '.') }}</h3>
                    <p>Não Atendidas</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-purple">
                <div class="inner">
                    <h3>{{ $topCidadesNaoAtendidas->first()->cidade ?? '-' }}</h3>
                    <p>Cidade mais demandada (não atendida)</p>
                </div>
                <div class="icon"><i class="fas fa-map-marker-alt"></i></div>
            </div>
        </div>
    </div>

    {{-- Top 5 rankings lado a lado --}}
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-city"></i> Top 5 Cidades NÃO Atendidas</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped">
                        <thead><tr><th>Cidade</th><th>UF</th><th class="text-right">Consultas</th></tr></thead>
                        <tbody>
                            @forelse($topCidadesNaoAtendidas as $item)
                                <tr>
                                    <td>{{ $item->cidade }}</td>
                                    <td>{{ $item->estado }}</td>
                                    <td class="text-right"><span class="badge badge-warning">{{ $item->total }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">Nenhum dado no período</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-map-pin"></i> Top 5 Bairros NÃO Atendidos</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped">
                        <thead><tr><th>Bairro</th><th>Cidade/UF</th><th class="text-right">Consultas</th></tr></thead>
                        <tbody>
                            @forelse($topBairrosNaoAtendidos as $item)
                                <tr>
                                    <td>{{ $item->bairro }}</td>
                                    <td>{{ $item->cidade }}/{{ $item->estado }}</td>
                                    <td class="text-right"><span class="badge badge-warning">{{ $item->total }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">Nenhum dado no período</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros + Tabela detalhada --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Consultas Detalhadas</h3>
        </div>
        <div class="card-body">
            {{-- Filtros --}}
            <form action="{{ route('admin.cep-stats.index') }}" method="GET" class="form-inline mb-3" style="gap: 8px; flex-wrap: wrap;">
                <div class="form-group">
                    <label class="mr-1">De:</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
                </div>
                <div class="form-group">
                    <label class="mr-1">Até:</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
                </div>
                <div class="form-group">
                    <select name="estado" class="form-control form-control-sm">
                        <option value="">Todos os estados</option>
                        @foreach($estados as $uf)
                            <option value="{{ $uf }}" {{ $estado === $uf ? 'selected' : '' }}>{{ $uf }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <input type="text" name="cidade" class="form-control form-control-sm" placeholder="Cidade" value="{{ $cidade }}">
                </div>
                <div class="form-group">
                    <input type="text" name="bairro" class="form-control form-control-sm" placeholder="Bairro" value="{{ $bairro }}">
                </div>
                <div class="form-group">
                    <select name="atendido" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        <option value="1" {{ $atendido === '1' ? 'selected' : '' }}>Atendidos</option>
                        <option value="0" {{ $atendido === '0' ? 'selected' : '' }}>Não atendidos</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter"></i> Filtrar</button>
                <a href="{{ route('admin.cep-stats.index') }}" class="btn btn-sm btn-default">Limpar</a>
            </form>

            {{-- Tabela --}}
            <table class="table table-bordered table-hover table-sm">
                <thead>
                    <tr>
                        <th style="width: 140px">Data/Hora</th>
                        <th style="width: 100px">CEP</th>
                        <th>Bairro</th>
                        <th>Cidade</th>
                        <th style="width: 50px">UF</th>
                        <th style="width: 100px">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td><small>{{ $log->created_at->format('d/m/Y H:i') }}</small></td>
                            <td><code>{{ $log->cep }}</code></td>
                            <td>{{ $log->bairro ?? '-' }}</td>
                            <td>{{ $log->cidade ?? '-' }}</td>
                            <td>{{ $log->estado ?? '-' }}</td>
                            <td>
                                @if($log->atendido)
                                    <span class="badge badge-success">Atendido</span>
                                @else
                                    <span class="badge badge-warning">Não atendido</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Nenhuma consulta encontrada no período.</td>
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
