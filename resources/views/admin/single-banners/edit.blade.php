@extends('adminlte::page')
@section('title', 'Editar Banner Único')
@section('content')

<form action="{{ route('admin.single-banners.update', $singleBanner) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="card">
        <div class="card-body">
            {{-- Ordem --}}
            <div class="form-group">
                <label>Ordem <span class="text-danger">*</span></label>
                <input type="number" name="order" class="form-control" value="{{ $singleBanner->order }}" required>
                <small class="form-text text-muted">Ordem de exibição (menor número aparece primeiro)</small>
            </div>

            {{-- Status Ativo --}}
            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="active" name="active" value="1" {{ $singleBanner->active ? 'checked' : '' }}>
                <label class="custom-control-label" for="active">Ativo</label>
            </div>

            <hr>

            <h5>Imagens</h5>

            {{-- Imagem Desktop Atual --}}
            <div class="form-group">
                <label>Imagem Desktop Atual</label><br>
                <img src="{{ $singleBanner->getDesktopImageUrl() }}" width="400" class="img-thumbnail mb-2">
            </div>

            {{-- Nova Imagem Desktop --}}
            <div class="form-group">
                <label>Nova Imagem Desktop (opcional)</label>
                <input type="file" name="desktop_image" class="form-control" accept="image/*">
                <small class="form-text text-muted">Tamanho recomendado: 1400x300px (máx 2MB). Deixe vazio para manter a atual.</small>
            </div>

            {{-- Imagem Mobile Atual --}}
            <div class="form-group">
                <label>Imagem Mobile Atual</label><br>
                <img src="{{ $singleBanner->getMobileImageUrl() }}" width="200" class="img-thumbnail mb-2">
            </div>

            {{-- Nova Imagem Mobile --}}
            <div class="form-group">
                <label>Nova Imagem Mobile (opcional)</label>
                <input type="file" name="mobile_image" class="form-control" accept="image/*">
                <small class="form-text text-muted">Tamanho recomendado: 766x400px (máx 2MB). Deixe vazio para manter a atual.</small>
            </div>

            <hr>

            <h5>Informações</h5>

            {{-- Link --}}
            <div class="form-group">
                <label>Link (URL)</label>
                <input type="url" name="link" class="form-control" value="{{ $singleBanner->link }}" placeholder="https://exemplo.com">
                <small class="form-text text-muted">URL de destino ao clicar no banner</small>
            </div>

            {{-- Alt Text --}}
            <div class="form-group">
                <label>Texto Alternativo (Alt)</label>
                <input type="text" name="alt_text" class="form-control" value="{{ $singleBanner->alt_text }}" maxlength="255">
                <small class="form-text text-muted">Importante para SEO e acessibilidade</small>
            </div>

            <hr>

            <h5>Período de Exibição</h5>

            {{-- Data Início --}}
            <div class="form-group">
                <label>Data de Início</label>
                <input type="date" name="start_date" class="form-control" value="{{ $singleBanner->start_date?->format('Y-m-d') }}">
                <small class="form-text text-muted">Deixe vazio para sem limite inicial</small>
            </div>

            {{-- Data Fim --}}
            <div class="form-group">
                <label>Data de Fim</label>
                <input type="date" name="end_date" class="form-control" value="{{ $singleBanner->end_date?->format('Y-m-d') }}">
                <small class="form-text text-muted">Deixe vazio para sem limite final</small>
            </div>

        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-success">Atualizar</button>
            <a href="{{ route('admin.single-banners.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </div>
</form>

@stop
