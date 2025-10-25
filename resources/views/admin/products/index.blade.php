@extends('adminlte::page')

@section('title', 'Produtos')

@section('content_header')
    <h1>Produtos</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary">Novo Produto</a>
                
                <form action="{{ route('admin.products.index') }}" method="GET" class="form-inline">
                    <div class="input-group">
                        <input type="text" 
                               name="search" 
                               class="form-control" 
                               placeholder="Buscar por nome, código, legacy_id ou marca..." 
                               value="{{ request('search') }}"
                               style="min-width: 350px;">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                            @if(request('search'))
                                <a href="{{ route('admin.products.index') }}" class="btn btn-default">
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
                    <a href="{{ route('admin.products.index') }}" class="float-right">Limpar busca</a>
                </div>
            @endif
            
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th style="width: 10px">#</th>
                        <th style="width: 80px">ID Legado</th>
                        <th style="width: 100px">Código</th>
                        <th style="width: 80px">Imagem</th>
                        <th>Nome</th>
                        <th>Categoria</th>
                        <th>Marca</th>
                        <th>Preço</th>
                        <th style="width: 150px">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr>
                            <td>{{ $product->id }}</td>
                            <td>
                                <span class="badge badge-info">{{ $product->legacy_id ?? '-' }}</span>
                            </td>
                            <td>
                                <small class="text-muted">{{ $product->sku ?? '-' }}</small>
                            </td>
                            <td>
                                @php
                                    $mainImage = $product->images->where('is_main', true)->first() ?? $product->images->first();
                                @endphp
                                @if ($mainImage)
                                    <img src="{{ asset('storage/' . $mainImage->path) }}" alt="{{ $product->name }}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                                @else
                                    <div style="width: 60px; height: 60px; background-color: #f0f0f0; display: flex; align-items: center; justify-content: center; border-radius: 4px; font-size: 9px; color: #999;">
                                        Sem Imagem
                                    </div>
                                @endif
                            </td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->category->name ?? 'N/A' }}</td>
                            <td>{{ $product->brand->brand ?? 'N/A' }}</td>
                            <td>R$ {{ number_format($product->price, 2, ',', '.') }}</td>
                            <td>
                                <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-warning">Editar</a>
                                <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" style="display:inline-block;">
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
            {{ $products->links() }}
        </div>
    </div>
@stop
