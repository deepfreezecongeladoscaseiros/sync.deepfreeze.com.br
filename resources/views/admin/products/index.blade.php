@extends('adminlte::page')

@section('title', 'Produtos')

@section('content_header')
    <h1>Produtos</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted">
                    <i class="fas fa-info-circle"></i>
                    Dados do sistema legado (somente leitura). Para editar, acesse o SIV.
                </span>

                <form action="{{ route('admin.products.index') }}" method="GET" class="form-inline">
                    <div class="input-group">
                        <input type="text"
                               name="search"
                               class="form-control"
                               placeholder="Buscar por nome, código ou marca..."
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

            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th style="width: 60px">ID</th>
                        <th style="width: 80px">Código</th>
                        <th style="width: 70px">Imagem</th>
                        <th>Nome</th>
                        <th>Categoria</th>
                        <th>Marca</th>
                        <th style="width: 100px">Preço</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr>
                            <td>{{ $product->id }}</td>
                            <td><small class="text-muted">{{ $product->sku ?? '-' }}</small></td>
                            <td>
                                {{-- Usa getMainImageUrl() que busca do img.deepfreeze.com.br --}}
                                <img src="{{ $product->getMainImageUrl('small') }}"
                                     alt="{{ $product->name }}"
                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;"
                                     loading="lazy">
                            </td>
                            <td>{{ $product->name }}</td>
                            <td><small>{{ $product->category->name ?? '-' }}</small></td>
                            <td><small>{{ $product->brand->brand ?? '-' }}</small></td>
                            <td>{{ $product->formatted_price }}</td>
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
