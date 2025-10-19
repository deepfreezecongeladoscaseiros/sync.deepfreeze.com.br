@extends('adminlte::page')

@section('title', 'Manufacturers')

@section('content_header')
    <h1>Manufacturers</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('admin.manufacturers.create') }}" class="btn btn-primary">New Manufacturer</a>
                
                <form action="{{ route('admin.manufacturers.index') }}" method="GET" class="form-inline">
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
            
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th style="width: 10px">#</th>
                        <th style="width: 100px">Legacy ID</th>
                        <th>Trade Name</th>
                        <th>Legal Name</th>
                        <th>City/State</th>
                        <th style="width: 80px">Status</th>
                        <th style="width: 150px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($manufacturers as $manufacturer)
                        <tr>
                            <td>{{ $manufacturer->id }}</td>
                            <td>
                                <span class="badge badge-info">{{ $manufacturer->legacy_id ?? '-' }}</span>
                            </td>
                            <td>{{ $manufacturer->trade_name }}</td>
                            <td><small class="text-muted">{{ $manufacturer->legal_name ?? '-' }}</small></td>
                            <td><small class="text-muted">{{ $manufacturer->city ?? '-' }}/{{ $manufacturer->state ?? '-' }}</small></td>
                            <td>
                                @if($manufacturer->active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.manufacturers.edit', $manufacturer->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('admin.manufacturers.destroy', $manufacturer->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
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
