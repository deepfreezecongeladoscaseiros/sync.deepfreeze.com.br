@extends('adminlte::page')

@section('title', 'Marcas')

@section('content_header')
    <h1>Marcas</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('admin.brands.create') }}" class="btn btn-primary">Nova Marca</a>
                
                <form action="{{ route('admin.brands.index') }}" method="GET" class="form-inline">
                    <div class="input-group">
                        <input type="text" 
                               name="search" 
                               class="form-control" 
                               placeholder="Buscar por nome ou legacy_id..." 
                               value="{{ request('search') }}"
                               style="min-width: 300px;">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                            @if(request('search'))
                                <a href="{{ route('admin.brands.index') }}" class="btn btn-default">
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
                    <a href="{{ route('admin.brands.index') }}" class="float-right">Limpar busca</a>
                </div>
            @endif
            
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th style="width: 10px">#</th>
                        <th style="width: 100px">ID Legado</th>
                        <th>Nome</th>
                        <th>Slug</th>
                        <th style="width: 150px">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($brands as $brand)
                        <tr>
                            <td>{{ $brand->id }}</td>
                            <td>
                                <span class="badge badge-info">{{ $brand->legacy_id ?? '-' }}</span>
                            </td>
                            <td>{{ $brand->brand }}</td>
                            <td><small class="text-muted">{{ $brand->slug }}</small></td>
                            <td>
                                <a href="{{ route('admin.brands.edit', $brand->id) }}" class="btn btn-sm btn-warning">Editar</a>
                                <form action="{{ route('admin.brands.destroy', $brand->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $brands->links() }}
        </div>
    </div>
@stop
