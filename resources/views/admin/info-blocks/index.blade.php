@extends('adminlte::page')

@section('title', 'Blocos de Informação')

@section('content_header')
    <h1>Blocos de Informação</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista de Blocos</h3>
            <div class="card-tools">
                <a href="{{ route('admin.info-blocks.create') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i> Novo Bloco
                </a>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    {{ session('success') }}
                </div>
            @endif

            @if($infoBlocks->isEmpty())
                <div class="alert alert-info">
                    Nenhum bloco cadastrado. <a href="{{ route('admin.info-blocks.create') }}">Criar primeiro bloco</a>
                </div>
            @else
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th width="60px">Ordem</th>
                            <th>Imagem</th>
                            <th>Título</th>
                            <th>Subtítulo</th>
                            <th width="100px">Status</th>
                            <th width="150px">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($infoBlocks as $block)
                            <tr>
                                <td class="text-center"><span class="badge badge-secondary">{{ $block->order }}</span></td>
                                <td><img src="{{ $block->getImageUrl() }}" class="img-thumbnail" style="max-width: 100px;"></td>
                                <td>{{ $block->title }}</td>
                                <td>{{ $block->subtitle }}</td>
                                <td class="text-center">
                                    @if($block->active)
                                        <span class="badge badge-success">Ativo</span>
                                    @else
                                        <span class="badge badge-secondary">Inativo</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.info-blocks.edit', $block) }}" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('admin.info-blocks.destroy', $block) }}" method="POST" style="display:inline;" onsubmit="return confirm('Excluir este bloco?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@stop
