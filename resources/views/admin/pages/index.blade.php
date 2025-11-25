@extends('adminlte::page')
@section('title', 'Páginas Internas')
@section('content_header')
    <h1>Páginas Internas</h1>
@stop
@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Gerenciar Páginas Internas</h3>
            <div class="card-tools">
                <a href="{{ route('admin.pages.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus"></i> Nova Página Interna
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
            @if($pages->isEmpty())
                <div class="alert alert-info">
                    Nenhuma página criada ainda. <a href="{{ route('admin.pages.create') }}">Criar a primeira</a>
                </div>
            @else
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Nome da Página</th>
                            <th>URL</th>
                            <th width="100" class="text-center">Status</th>
                            <th width="220" class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pages as $page)
                            <tr>
                                <td>{{ $page->title }}</td>
                                <td><code>/{{ $page->slug }}</code></td>
                                <td class="text-center">
                                    @if($page->active)
                                        <span class="badge badge-success">Ativa</span>
                                    @else
                                        <span class="badge badge-secondary">Inativa</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ url($page->slug) }}" target="_blank" class="btn btn-info btn-sm" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-primary btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.pages.destroy', $page) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Tem certeza?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </button>
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
