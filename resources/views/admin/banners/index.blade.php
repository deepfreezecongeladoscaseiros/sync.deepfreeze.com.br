@extends('adminlte::page')

@section('title', 'Banners Hero')

@section('content_header')
    <h1><i class="fa fa-picture-o"></i> Banners Hero (Principal)</h1>
@stop

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-check"></i> Sucesso!</h4>
        {{ session('success') }}
    </div>
@endif

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Gerenciar Banners</h3>
        <div class="box-tools">
            <a href="{{ route('admin.banners.create') }}" class="btn btn-success btn-sm">
                <i class="fa fa-plus"></i> Novo Banner
            </a>
        </div>
    </div>
    <div class="box-body">
        @if($banners->isEmpty())
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i>
                Nenhum banner cadastrado. Clique em "Novo Banner" para adicionar.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th width="50">Ordem</th>
                            <th>Preview</th>
                            <th>Alt / Link</th>
                            <th width="150">Período</th>
                            <th width="100">Status</th>
                            <th width="150">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($banners as $banner)
                        <tr>
                            <td class="text-center"><strong>{{ $banner->order }}</strong></td>
                            <td>
                                <img src="{{ $banner->getDesktopImageUrl() }}" alt="{{ $banner->alt_text }}" style="max-width: 200px; height: auto;">
                            </td>
                            <td>
                                <strong>{{ $banner->alt_text }}</strong><br>
                                @if($banner->link)
                                    <small class="text-muted">{{ Str::limit($banner->link, 50) }}</small>
                                @else
                                    <small class="text-muted">Sem link</small>
                                @endif
                            </td>
                            <td>
                                <small>
                                    @if($banner->start_date)
                                        <strong>Início:</strong> {{ $banner->start_date->format('d/m/Y') }}<br>
                                    @endif
                                    @if($banner->end_date)
                                        <strong>Fim:</strong> {{ $banner->end_date->format('d/m/Y') }}
                                    @else
                                        <strong>Fim:</strong> Eterno
                                    @endif
                                </small>
                            </td>
                            <td>
                                @php
                                    $status = $banner->getStatusLabel();
                                    $class = match($status) {
                                        'Ativo' => 'success',
                                        'Agendado' => 'info',
                                        'Expirado' => 'warning',
                                        default => 'default'
                                    };
                                @endphp
                                <span class="label label-{{ $class }}">{{ $status }}</span>
                            </td>
                            <td>
                                <a href="{{ route('admin.banners.edit', $banner) }}" class="btn btn-sm btn-primary" title="Editar">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST" style="display:inline" onsubmit="return confirm('Tem certeza que deseja remover este banner?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Remover">
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
