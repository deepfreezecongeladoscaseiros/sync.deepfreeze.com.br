@extends('adminlte::page')

@section('title', 'Layout da Loja')

@section('content_header')
    <h1>Configurações de Layout</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Módulos de Customização</h3>
                <p class="help-block">Personalize a aparência da loja virtual sem precisar editar código</p>
            </div>
            <div class="box-body">
                <div class="row">
                    {{-- Card: Logo --}}
                    <div class="col-md-3">
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-image"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Logomarca</span>
                                <span class="info-box-number">{{ $theme && $theme->logo_path ? 'Configurada' : 'Não configurada' }}</span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: {{ $theme && $theme->logo_path ? '100' : '0' }}%"></div>
                                </div>
                                <span class="progress-description">
                                    <a href="{{ route('admin.layout.logo') }}" class="text-white">
                                        <i class="fa fa-upload"></i> {{ $theme && $theme->logo_path ? 'Alterar' : 'Enviar' }} Logo
                                    </a>
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Card: Top Bar --}}
                    <div class="col-md-3">
                        <div class="info-box bg-yellow">
                            <span class="info-box-icon"><i class="fa fa-bullhorn"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Barra de Anúncios</span>
                                <span class="info-box-number">{{ $theme && $theme->top_bar_enabled ? 'Ativa' : 'Desativada' }}</span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: {{ $theme && $theme->top_bar_enabled ? '100' : '0' }}%"></div>
                                </div>
                                <span class="progress-description">
                                    <a href="{{ route('admin.layout.topbar') }}" class="text-white">
                                        <i class="fa fa-cog"></i> Configurar
                                    </a>
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Card: Cores --}}
                    <div class="col-md-3">
                        <div class="info-box bg-aqua">
                            <span class="info-box-icon"><i class="fa fa-paint-brush"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Cores</span>
                                <span class="info-box-number">{{ $theme ? count($theme->colors) : 0 }} categorias</span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                                <span class="progress-description">
                                    <a href="{{ route('admin.layout.colors') }}" class="text-white">
                                        <i class="fa fa-edit"></i> Editar Cores
                                    </a>
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Card: Fontes (futuro) --}}
                    <div class="col-md-3">
                        <div class="info-box bg-gray">
                            <span class="info-box-icon"><i class="fa fa-font"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Fontes</span>
                                <span class="info-box-number">Em breve</span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 0%"></div>
                                </div>
                                <span class="progress-description">
                                    <i class="fa fa-clock-o"></i> Funcionalidade futura
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($theme)
                <div class="row">
                    <div class="col-md-12">
                        <div class="callout callout-info">
                            <h4><i class="icon fa fa-info-circle"></i> Tema Ativo:</h4>
                            <p>
                                <strong>{{ $theme->name }}</strong><br>
                                Última atualização: {{ $theme->updated_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.info-box-content a.text-white:hover {
    text-decoration: underline;
}
</style>
@stop
