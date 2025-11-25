@extends('adminlte::page')

@section('title', 'Blocos de Informações')

@section('content_header')
    <h1><i class="fa fa-th-large"></i> Blocos de Informações (Régua)</h1>
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
        <h3 class="box-title">Gerenciar Blocos de Features</h3>
        <p class="help-block" style="margin-top: 10px;">
            <i class="fa fa-info-circle"></i>
            São 4 blocos fixos exibidos abaixo do banner hero na página inicial.
        </p>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="50">Ordem</th>
                        <th width="100">Ícone</th>
                        <th>Título</th>
                        <th>Descrição</th>
                        <th width="80">Cores</th>
                        <th width="80">Status</th>
                        <th width="100">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($blocks as $block)
                    <tr>
                        <td class="text-center"><strong>{{ $block->order }}</strong></td>
                        <td class="text-center">
                            <img src="{{ $block->getIconUrl() }}" alt="{{ $block->title }}" style="max-width: 50px; height: auto;">
                        </td>
                        <td><strong>{{ $block->title }}</strong></td>
                        <td>{{ $block->description }}</td>
                        <td>
                            <div style="display: flex; gap: 5px;">
                                <div style="width: 20px; height: 20px; background-color: {{ $block->bg_color }}; border: 1px solid #ddd;" title="Fundo"></div>
                                <div style="width: 20px; height: 20px; background-color: {{ $block->text_color }}; border: 1px solid #ddd;" title="Texto"></div>
                                <div style="width: 20px; height: 20px; background-color: {{ $block->icon_color }}; border: 1px solid #ddd;" title="Ícone"></div>
                            </div>
                        </td>
                        <td class="text-center">
                            @if($block->active)
                                <span class="label label-success">Ativo</span>
                            @else
                                <span class="label label-default">Inativo</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.feature-blocks.edit', $block) }}" class="btn btn-sm btn-primary" title="Editar">
                                <i class="fa fa-edit"></i> Editar
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Preview dos blocos -->
<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title">Preview</h3>
    </div>
    <div class="box-body">
        <div class="row">
            @foreach($blocks as $block)
                @if($block->active)
                <div class="col-xs-12 col-sm-6 col-md-3">
                    <div style="{{ $block->getInlineStyle() }} padding: 20px; text-align: center; border-radius: 8px; margin-bottom: 15px;">
                        <img src="{{ $block->getIconUrl() }}" alt="{{ $block->title }}" class="img-responsive" style="display: block; margin: 0 auto 15px; max-width: 60px;">
                        <h4 style="margin: 10px 0; font-weight: bold; text-transform: lowercase;">{{ $block->title }}</h4>
                        <p style="margin: 0; font-size: 13px;">{{ $block->description }}</p>
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
@stop
