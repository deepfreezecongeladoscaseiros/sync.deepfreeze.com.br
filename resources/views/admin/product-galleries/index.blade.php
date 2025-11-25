@extends('adminlte::page')

@section('title', 'Galerias de Produtos')

@section('content_header')
    <h1><i class="fa fa-th-large"></i> Galerias de Produtos (Home)</h1>
@stop

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-check"></i> Sucesso!</h4>
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-ban"></i> Erro!</h4>
        {{ session('error') }}
    </div>
@endif

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Gerenciar Galerias de Produtos</h3>
        <div class="box-tools">
            @if($galleries->count() < 4)
                <a href="{{ route('admin.product-galleries.create') }}" class="btn btn-success btn-sm">
                    <i class="fa fa-plus"></i> Nova Galeria
                </a>
            @else
                <span class="label label-warning">Limite de 4 galerias atingido</span>
            @endif
        </div>
        <p class="help-block" style="margin-top: 10px;">
            <i class="fa fa-info-circle"></i>
            Você pode criar até 4 galerias de produtos para exibir na página inicial. Cada galeria é customizável com filtros, layout e cores próprias.
        </p>
    </div>
    <div class="box-body">
        @if($galleries->isEmpty())
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i> Nenhuma galeria cadastrada ainda. Clique em "Nova Galeria" para começar.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th width="50">Ordem</th>
                            <th>Título</th>
                            <th>Filtro</th>
                            <th width="100">Layout</th>
                            <th width="80">Produtos</th>
                            <th width="80">Status</th>
                            <th width="150">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($galleries as $gallery)
                        <tr>
                            <td class="text-center"><strong>{{ $gallery->order }}</strong></td>
                            <td>
                                <strong>{{ $gallery->title }}</strong>
                                @if($gallery->subtitle)
                                    <br><small class="text-muted">{{ $gallery->subtitle }}</small>
                                @endif
                            </td>
                            <td>
                                @switch($gallery->filter_type)
                                    @case('category')
                                        <span class="label label-info">Categoria</span><br>
                                        <small>{{ $gallery->category ? $gallery->category->name : 'N/A' }}</small>
                                        @break
                                    @case('best_sellers')
                                        <span class="label label-success">Mais Vendidos</span>
                                        @break
                                    @case('on_sale')
                                        <span class="label label-warning">Em Promoção</span>
                                        @break
                                    @case('low_stock')
                                        <span class="label label-danger">Estoque Baixo</span>
                                        @break
                                @endswitch
                            </td>
                            <td class="text-center">
                                <small>
                                    Mobile: {{ $gallery->mobile_columns }}/linha<br>
                                    Desktop: {{ $gallery->desktop_columns }}/linha
                                </small>
                            </td>
                            <td class="text-center">{{ $gallery->products_limit }}</td>
                            <td class="text-center">
                                @if($gallery->active)
                                    <span class="label label-success">Ativo</span>
                                @else
                                    <span class="label label-default">Inativo</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.product-galleries.edit', $gallery) }}" class="btn btn-sm btn-primary" title="Editar">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.product-galleries.destroy', $gallery) }}" method="POST" style="display: inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir esta galeria?')">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@stop
