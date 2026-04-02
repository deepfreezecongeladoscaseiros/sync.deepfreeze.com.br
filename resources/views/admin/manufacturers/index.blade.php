@extends('adminlte::page')

@section('title', 'Fabricantes')

@section('content_header')
    <h1>Fabricantes</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted">
                    <i class="fas fa-info-circle"></i>
                    Dados do sistema legado (somente leitura). Para editar, acesse o SIV.
                </span>

                <form action="{{ route('admin.manufacturers.index') }}" method="GET" class="form-inline">
                    <div class="input-group">
                        <input type="text"
                               name="search"
                               class="form-control"
                               placeholder="Buscar por nome ou ID..."
                               value="{{ request('search') }}"
                               style="min-width: 300px;">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                            @if(request('search'))
                                <a href="{{ route('admin.manufacturers.index') }}" class="btn btn-default">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card-body">
            @if(request('search'))
                <div class="alert alert-info">
                    Mostrando resultados para: <strong>{{ request('search') }}</strong>
                    <a href="{{ route('admin.manufacturers.index') }}" class="float-right">Limpar busca</a>
                </div>
            @endif

            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th style="width: 60px">ID</th>
                        <th>Nome Fantasia</th>
                        <th>Razão Social</th>
                        <th>Cidade/UF</th>
                        <th style="width: 120px">Produtos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($manufacturers as $manufacturer)
                        <tr>
                            <td>{{ $manufacturer->id }}</td>
                            <td>{{ $manufacturer->trade_name }}</td>
                            <td><small class="text-muted">{{ $manufacturer->legal_name ?? '-' }}</small></td>
                            <td><small class="text-muted">{{ $manufacturer->city ?? '-' }}/{{ $manufacturer->state ?? '-' }}</small></td>
                            <td class="text-center">
                                <span class="badge badge-secondary">{{ $manufacturer->products_count ?? '-' }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $manufacturers->links() }}
        </div>
    </div>
@stop
