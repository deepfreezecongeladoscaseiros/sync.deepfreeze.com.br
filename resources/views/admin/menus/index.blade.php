@extends('adminlte::page')

@section('title', 'Gerenciar Menus')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-bars mr-2"></i> Menus de Navegação</h1>
        <a href="{{ route('admin.menus.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Novo Menu
        </a>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Menus Cadastrados</h3>
        </div>
        <div class="card-body p-0">
            @if($menus->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-bars fa-4x text-muted mb-3"></i>
                    <p class="text-muted">Nenhum menu cadastrado.</p>
                    <a href="{{ route('admin.menus.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus mr-1"></i> Criar Primeiro Menu
                    </a>
                </div>
            @else
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th>Nome</th>
                            <th>Identificador</th>
                            <th>Localização</th>
                            <th class="text-center">Itens</th>
                            <th class="text-center">Status</th>
                            <th style="width: 200px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($menus as $menu)
                            <tr>
                                <td>{{ $menu->id }}</td>
                                <td>
                                    <strong>{{ $menu->name }}</strong>
                                    @if($menu->description)
                                        <br><small class="text-muted">{{ $menu->description }}</small>
                                    @endif
                                </td>
                                <td><code>{{ $menu->slug }}</code></td>
                                <td>
                                    <span class="badge badge-info">{{ $menu->getLocationLabel() }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-secondary">{{ $menu->items_count }}</span>
                                </td>
                                <td class="text-center">
                                    @if($menu->active)
                                        <span class="badge badge-success">Ativo</span>
                                    @else
                                        <span class="badge badge-danger">Inativo</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.menus.items', $menu) }}"
                                           class="btn btn-primary" title="Gerenciar Itens">
                                            <i class="fas fa-list"></i> Itens
                                        </a>
                                        <a href="{{ route('admin.menus.edit', $menu) }}"
                                           class="btn btn-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.menus.destroy', $menu) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Excluir este menu e todos os itens?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" title="Excluir">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- Card de Ajuda --}}
    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i> Como Funciona</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="fas fa-desktop text-primary mr-2"></i> Cabeçalho (Header)</h5>
                    <p class="text-muted">Menu principal exibido no topo da loja. Suporta mega menus com imagens promocionais.</p>
                </div>
                <div class="col-md-4">
                    <h5><i class="fas fa-mobile-alt text-success mr-2"></i> Menu Mobile</h5>
                    <p class="text-muted">Menu lateral exibido em dispositivos móveis. Pode ter ícones personalizados.</p>
                </div>
                <div class="col-md-4">
                    <h5><i class="fas fa-shoe-prints text-warning mr-2"></i> Rodapé (Footer)</h5>
                    <p class="text-muted">Links exibidos no rodapé da loja. Ideal para páginas institucionais.</p>
                </div>
            </div>
        </div>
    </div>
@stop
