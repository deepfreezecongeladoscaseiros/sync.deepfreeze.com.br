@extends('adminlte::page')
@section('title', 'Editar Bloco de Informação')
@section('content_header')
    <h1>Editar Bloco de Informação</h1>
@stop
@section('content')
    <form action="{{ route('admin.info-blocks.update', $infoBlock) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Ordem *</label>
                            <input type="number" name="order" class="form-control @error('order') is-invalid @enderror" value="{{ old('order', $infoBlock->order) }}" required>
                            @error('order')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="custom-control custom-switch mt-4">
                                <input type="checkbox" class="custom-control-input" id="active" name="active" value="1" {{ old('active', $infoBlock->active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="active">Ativo</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Imagem (Atual)</label><br>
                    <img src="{{ $infoBlock->getImageUrl() }}" class="img-thumbnail mb-2" style="max-width: 300px;">
                    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror">
                    @error('image')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label>Alt da Imagem</label>
                    <input type="text" name="image_alt" class="form-control" value="{{ old('image_alt', $infoBlock->image_alt) }}">
                </div>

                <div class="form-group">
                    <label>Título *</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $infoBlock->title) }}" required>
                    @error('title')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label>Subtítulo</label>
                    <input type="text" name="subtitle" class="form-control" value="{{ old('subtitle', $infoBlock->subtitle) }}">
                </div>

                <div class="form-group">
                    <label>Cor de Fundo (Hex)</label>
                    <input type="color" name="background_color" class="form-control" value="{{ old('background_color', $infoBlock->background_color ?? '#ffffff') }}">
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Atualizar</button>
                <a href="{{ route('admin.info-blocks.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </div>
    </form>
@stop
