@extends('adminlte::page')
@section('title', 'Editar Página Interna')
@section('content_header')
    <h1>Editar Página Interna</h1>
@stop
@section('content')
    <form action="{{ route('admin.pages.update', $page) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Dados Gerais</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Nome da Página <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                                   value="{{ old('title', $page->title) }}" required>
                            @error('title')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <div class="custom-control custom-switch mt-4">
                                <input type="checkbox" class="custom-control-input" id="active" name="active" value="1" {{ old('active', $page->active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="active"><strong>Ativa</strong></label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>URL da Página <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">{{ url('/') }}/</span>
                        </div>
                        <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" 
                               value="{{ old('slug', $page->slug) }}" required>
                        @error('slug')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <small class="text-muted">Use apenas letras minúsculas, números e hífens</small>
                </div>

                <div class="form-group">
                    <label>Conteúdo da Página <span class="text-danger">*</span></label>
                    <textarea name="content" id="content" class="form-control @error('content') is-invalid @enderror">{{ old('content', $page->content) }}</textarea>
                    @error('content')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Otimização para busca (SEO) - Opcional</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Título da página (meta title)</label>
                    <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $page->meta_title) }}" maxlength="60">
                    <small class="text-muted">Máximo 60 caracteres</small>
                </div>
                
                <div class="form-group">
                    <label>Descrição completa (meta description)</label>
                    <textarea name="meta_description" class="form-control" rows="3" maxlength="160">{{ old('meta_description', $page->meta_description) }}</textarea>
                    <small class="text-muted">Máximo 160 caracteres</small>
                </div>
                
                <div class="form-group">
                    <label>Palavras chaves (meta keywords)</label>
                    <input type="text" name="meta_keywords" class="form-control" value="{{ old('meta_keywords', $page->meta_keywords) }}">
                    <small class="text-muted">Separe as palavras-chave por vírgula</small>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-footer">
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Atualizar</button>
                <a href="{{ route('admin.pages.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
                <a href="{{ url($page->slug) }}" target="_blank" class="btn btn-info float-right"><i class="fas fa-eye"></i> Visualizar Página</a>
            </div>
        </div>
    </form>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#content').summernote({
            height: 400,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    });
    </script>
@stop
