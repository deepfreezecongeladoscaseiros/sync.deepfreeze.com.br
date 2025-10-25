@extends('adminlte::page')

@section('title', 'Editar Produto')

@section('content_header')
    <h1>Editar Produto: {{ $product->name }}</h1>
@stop

@section('content')
    {{-- Product Edit Form --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Detalhes do Produto</h3>
            <div class="card-tools">
                <form action="{{ route('admin.products.sync_to_tray', $product) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-success">Sincronizar com Tray</button>
                </form>
                <form action="{{ route('admin.products.sync_image', $product) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-info">Sincronizar Imagem</button>
                </form>
                <form action="{{ route('admin.products.sync_properties', $product) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-warning">Sincronizar Propriedades</button>
                </form>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="tray_id">ID Tray</label>
                    <input type="text" id="tray_id" class="form-control" value="{{ $product->tray_id }}" readonly>
                </div>

                @include('admin.products._form')
            </form>
        </div>
    </div>

    {{-- Variants Management --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Variações</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#variantModal">
                    Nova Variação
                </button>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID Tray</th>
                        <th>Tipo</th>
                        <th>Valor</th>
                        <th>Preço</th>
                        <th>Estoque</th>
                        <th style="width: 200px">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($product->variations as $variant)
                        <tr>
                            <td>{{ $variant->tray_id }}</td>
                            <td>{{ $variant->type }}</td>
                            <td>{{ $variant->value }}</td>
                            <td>R$ {{ number_format($variant->price ?? $product->price, 2, ',', '.') }}</td>
                            <td>{{ $variant->stock }}</td>
                            <td>
                                <a href="{{ route('admin.variants.edit', $variant->id) }}" class="btn btn-sm btn-warning">Editar</a>
                                <form action="{{ route('admin.variants.destroy', $variant->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                                </form>
                                <form action="{{ route('admin.variants.sync_to_tray', $variant->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">Sincronizar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">Nenhuma variação encontrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

{{-- New Variant Modal --}}
<div class="modal fade" id="variantModal" tabindex="-1" role="dialog" aria-labelledby="variantModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="variantModalLabel">Nova Variação</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="variantForm" action="{{ route('admin.products.variants.store', $product) }}" method="POST">
                <div class="modal-body">
                    @csrf
                    <div class="form-group">
                        <label for="type">Tipo (ex: Cor)</label>
                        <input type="text" name="type" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="value">Valor (ex: Azul)</label>
                        <input type="text" name="value" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="price">Preço (deixe em branco para usar o preço do produto principal)</label>
                        <input type="number" step="0.01" name="price" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="stock">Estoque</label>
                        <input type="number" name="stock" class="form-control" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-primary">Salvar Variação</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
