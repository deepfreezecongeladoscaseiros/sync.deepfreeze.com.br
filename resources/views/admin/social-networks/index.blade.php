@extends('adminlte::page')

@section('title', 'Redes Sociais')

@section('content_header')
    <h1>Redes Sociais</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Gerenciar Redes Sociais</h3>
            <div class="card-tools">
                <a href="{{ route('admin.social-networks.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus"></i> Nova Rede Social
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

            @if($socialNetworks->isEmpty())
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Nenhuma rede social cadastrada ainda.
                    <a href="{{ route('admin.social-networks.create') }}">Criar a primeira</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="60">Ordem</th>
                                <th width="80">Ícone</th>
                                <th>Nome</th>
                                <th>URL</th>
                                <th width="100" class="text-center">Status</th>
                                <th width="180" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($socialNetworks as $social)
                                <tr>
                                    <td class="text-center">{{ $social->order }}</td>
                                    <td class="text-center">
                                        <img src="{{ $social->getIconUrl() }}"
                                             alt="{{ $social->name }}"
                                             style="max-width: 40px; max-height: 40px;">
                                    </td>
                                    <td>{{ $social->name }}</td>
                                    <td>
                                        <a href="{{ $social->url }}" target="_blank" class="text-primary">
                                            {{ Str::limit($social->url, 50) }}
                                            <i class="fas fa-external-link-alt fa-xs"></i>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        @if($social->active)
                                            <span class="badge badge-success">Ativo</span>
                                        @else
                                            <span class="badge badge-secondary">Inativo</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.social-networks.edit', $social) }}"
                                           class="btn btn-primary btn-sm"
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <form action="{{ route('admin.social-networks.destroy', $social) }}"
                                              method="POST"
                                              style="display: inline-block;"
                                              onsubmit="return confirm('Tem certeza que deseja remover esta rede social?');">
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
                </div>

                <div class="mt-3">
                    <p class="text-muted">
                        <i class="fas fa-info-circle"></i>
                        <strong>Dica:</strong> As redes sociais ativas são exibidas automaticamente no topo e rodapé do site,
                        ordenadas pelo campo "Ordem" (menor número aparece primeiro).
                    </p>
                </div>
            @endif
        </div>
    </div>
@stop
