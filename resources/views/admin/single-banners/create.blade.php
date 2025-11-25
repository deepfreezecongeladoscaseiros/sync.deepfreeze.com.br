@extends('adminlte::page')
@section('title', 'Novo Banner Único')
@section('content')

<form action="{{ route('admin.single-banners.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="card">
        <div class="card-body">
            {{-- Ordem --}}
            <div class="form-group">
                <label>Ordem <span class="text-danger">*</span></label>
                <input type="number" name="order" class="form-control" value="{{ $nextOrder }}" required>
                <small class="form-text text-muted">Ordem de exibição (menor número aparece primeiro)</small>
            </div>

            {{-- Status Ativo --}}
            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="active" name="active" value="1" checked>
                <label class="custom-control-label" for="active">Ativo</label>
            </div>

            <hr>

            <h5>Imagens</h5>

            {{-- Imagem Desktop --}}
            <div class="form-group">
                <label>Imagem Desktop <span class="text-danger">*</span></label>
                <input type="file" name="desktop_image" class="form-control" accept="image/*" required>
                <small class="form-text text-muted">Tamanho recomendado: 1400x300px (máx 2MB)</small>
            </div>

            {{-- Imagem Mobile --}}
            <div class="form-group">
                <label>Imagem Mobile <span class="text-danger">*</span></label>
                <input type="file" name="mobile_image" class="form-control" accept="image/*" required>
                <small class="form-text text-muted">Tamanho recomendado: 766x400px (máx 2MB)</small>
            </div>

            <hr>

            <h5>Informações</h5>

            {{-- Link --}}
            <div class="form-group">
                <label>Link (URL)</label>
                <input type="url" name="link" class="form-control" placeholder="https://exemplo.com">
                <small class="form-text text-muted">URL de destino ao clicar no banner</small>
            </div>

            {{-- Alt Text --}}
            <div class="form-group">
                <label>Texto Alternativo (Alt)</label>
                <input type="text" name="alt_text" class="form-control" maxlength="255">
                <small class="form-text text-muted">Importante para SEO e acessibilidade</small>
            </div>

            <hr>

            <h5>Período de Exibição</h5>

            {{-- Data Início --}}
            <div class="form-group">
                <label>Data de Início</label>
                <input type="date" name="start_date" class="form-control">
                <small class="form-text text-muted">Deixe vazio para sem limite inicial</small>
            </div>

            {{-- Data Fim --}}
            <div class="form-group">
                <label>Data de Fim</label>
                <input type="date" name="end_date" class="form-control">
                <small class="form-text text-muted">Deixe vazio para sem limite final</small>
            </div>

        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-success">Salvar</button>
            <a href="{{ route('admin.single-banners.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </div>
</form>

@stop
