@extends('adminlte::page')
@section('title', 'Editar Banner')
@section('content_header')
    <h1>Editar Banner Hero</h1>
@stop
@section('content')
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-ban"></i> Erro!</h4>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('admin.banners.update', $banner) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="box box-primary">
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group @error('image_desktop') has-error @enderror">
                        <label>Imagem Desktop (1400x385px)</label>
                        <div class="mb-2">
                            <img src="{{ $banner->getDesktopImageUrl() }}" alt="Preview Desktop" style="max-width: 300px; height: auto; border: 1px solid #ddd; padding: 5px;">
                        </div>
                        <input type="file" name="image_desktop" class="form-control" accept="image/*">
                        <small class="text-muted">Deixe em branco para manter a imagem atual</small>
                        @error('image_desktop')<span class="help-block">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group @error('image_mobile') has-error @enderror">
                        <label>Imagem Mobile (766x981px)</label>
                        <div class="mb-2">
                            <img src="{{ $banner->getMobileImageUrl() }}" alt="Preview Mobile" style="max-width: 200px; height: auto; border: 1px solid #ddd; padding: 5px;">
                        </div>
                        <input type="file" name="image_mobile" class="form-control" accept="image/*">
                        <small class="text-muted">Deixe em branco para manter a imagem atual</small>
                        @error('image_mobile')<span class="help-block">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
            <div class="form-group @error('link') has-error @enderror">
                <label>Link (URL)</label>
                <input type="url" name="link" class="form-control" value="{{ old('link', $banner->link) }}" placeholder="https://example.com/produtos">
                @error('link')<span class="help-block">{{ $message }}</span>@enderror
            </div>
            <div class="form-group @error('alt_text') has-error @enderror">
                <label>Texto Alternativo *</label>
                <input type="text" name="alt_text" class="form-control" value="{{ old('alt_text', $banner->alt_text) }}" required>
                @error('alt_text')<span class="help-block">{{ $message }}</span>@enderror
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group @error('start_date') has-error @enderror">
                        <label>Data Início</label>
                        <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $banner->start_date?->format('Y-m-d')) }}">
                        @error('start_date')<span class="help-block">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group @error('end_date') has-error @enderror">
                        <label>Data Fim (deixe vazio para eterno)</label>
                        <input type="date" name="end_date" class="form-control" value="{{ old('end_date', $banner->end_date?->format('Y-m-d')) }}">
                        @error('end_date')<span class="help-block">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group @error('order') has-error @enderror">
                        <label>Ordem (menor = primeiro) *</label>
                        <input type="number" name="order" class="form-control" value="{{ old('order', $banner->order) }}" required min="0">
                        @error('order')<span class="help-block">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
            <div class="checkbox">
                <label><input type="checkbox" name="active" value="1" {{ old('active', $banner->active) ? 'checked' : '' }}> Ativo</label>
            </div>
        </div>
        <div class="box-footer">
            <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Atualizar Banner</button>
            <a href="{{ route('admin.banners.index') }}" class="btn btn-default">Cancelar</a>
        </div>
    </div>
</form>
@stop
