@extends('adminlte::page')
@section('title', 'Banners Únicos')
@section('content_header')
<h1>Banners Únicos</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <a href="{{ route('admin.single-banners.create') }}" class="btn btn-success float-right">
            <i class="fas fa-plus"></i> Novo Banner
        </a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($banners->isEmpty())
            <p>Nenhum banner cadastrado.</p>
        @else
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Ordem</th>
                        <th>Preview Desktop</th>
                        <th>Preview Mobile</th>
                        <th>Link</th>
                        <th>Período</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($banners as $banner)
                    <tr>
                        <td>{{ $banner->order }}</td>
                        <td>
                            <img src="{{ $banner->getDesktopImageUrl() }}" width="200" class="img-thumbnail">
                        </td>
                        <td>
                            <img src="{{ $banner->getMobileImageUrl() }}" width="100" class="img-thumbnail">
                        </td>
                        <td>
                            @if($banner->link)
                                <a href="{{ $banner->link }}" target="_blank" class="btn btn-sm btn-info">
                                    <i class="fas fa-external-link-alt"></i> Ver
                                </a>
                            @else
                                <span class="text-muted">Sem link</span>
                            @endif
                        </td>
                        <td>
                            @if($banner->start_date)
                                {{ $banner->start_date->format('d/m/Y') }}
                            @else
                                <span class="text-muted">Sem início</span>
                            @endif
                            <br>
                            @if($banner->end_date)
                                {{ $banner->end_date->format('d/m/Y') }}
                            @else
                                <span class="text-muted">Sem fim</span>
                            @endif
                        </td>
                        <td>
                            @if($banner->active)
                                <span class="badge badge-success">Ativo</span>
                            @else
                                <span class="badge badge-secondary">Inativo</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.single-banners.edit', $banner) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.single-banners.destroy', $banner) }}" method="POST" style="display:inline" onsubmit="return confirm('Excluir este banner?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">
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
