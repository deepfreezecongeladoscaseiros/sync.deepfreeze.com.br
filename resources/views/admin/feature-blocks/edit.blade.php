@extends('adminlte::page')

@section('title', 'Editar Bloco')

@section('content_header')
    <h1>Editar Bloco de Informação #{{ $featureBlock->order }}</h1>
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

<form action="{{ route('admin.feature-blocks.update', $featureBlock) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row">
        <!-- Formulário -->
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Dados do Bloco</h3>
                </div>
                <div class="box-body">
                    <div class="form-group @error('icon') has-error @enderror">
                        <label>Ícone do Bloco</label>
                        <div class="mb-2">
                            <img src="{{ $featureBlock->getIconUrl() }}" alt="Ícone Atual" style="max-width: 80px; height: auto; border: 1px solid #ddd; padding: 5px; background: white;">
                        </div>
                        <input type="file" name="icon" class="form-control" accept=".svg,.png,.jpg,.jpeg">
                        <p class="help-block">
                            Formatos aceitos: SVG, PNG, JPG (máx. 2MB). Deixe em branco para manter o ícone atual.
                        </p>
                        @error('icon')<span class="help-block">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group @error('title') has-error @enderror">
                        <label>Título *</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $featureBlock->title) }}" required maxlength="100">
                        <p class="help-block">Texto em negrito (ex: "frete expresso")</p>
                        @error('title')<span class="help-block">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group @error('description') has-error @enderror">
                        <label>Descrição *</label>
                        <textarea name="description" class="form-control" rows="3" required maxlength="255">{{ old('description', $featureBlock->description) }}</textarea>
                        <p class="help-block">Texto descritivo menor</p>
                        @error('description')<span class="help-block">{{ $message }}</span>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group @error('bg_color') has-error @enderror">
                                <label>Cor de Fundo *</label>
                                <input type="color" name="bg_color" class="form-control" value="{{ old('bg_color', $featureBlock->bg_color) }}" required>
                                @error('bg_color')<span class="help-block">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group @error('text_color') has-error @enderror">
                                <label>Cor do Texto *</label>
                                <input type="color" name="text_color" class="form-control" value="{{ old('text_color', $featureBlock->text_color) }}" required>
                                @error('text_color')<span class="help-block">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group @error('icon_color') has-error @enderror">
                                <label>Cor do Ícone *</label>
                                <input type="color" name="icon_color" class="form-control" value="{{ old('icon_color', $featureBlock->icon_color) }}" required>
                                @error('icon_color')<span class="help-block">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="active" value="1" {{ old('active', $featureBlock->active) ? 'checked' : '' }}> Ativo
                        </label>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Atualizar Bloco</button>
                    <a href="{{ route('admin.feature-blocks.index') }}" class="btn btn-default">Cancelar</a>
                </div>
            </div>
        </div>

        <!-- Preview em tempo real -->
        <div class="col-md-6">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Preview ao Vivo</h3>
                </div>
                <div class="box-body">
                    <div id="preview-block" style="padding: 30px; text-align: center; border-radius: 8px; background-color: {{ $featureBlock->bg_color }}; color: {{ $featureBlock->text_color }};">
                        <i id="preview-icon" class="{{ $featureBlock->icon }}" style="font-size: 50px; color: {{ $featureBlock->icon_color }}; margin-bottom: 15px;"></i>
                        <h3 id="preview-title" style="margin: 15px 0; font-weight: bold; text-transform: lowercase;">{{ $featureBlock->title }}</h3>
                        <p id="preview-description" style="margin: 0; font-size: 14px;">{{ $featureBlock->description }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('js')
<script>
$(document).ready(function() {
    // Preview em tempo real
    const previewBlock = $('#preview-block');
    const previewIcon = $('#preview-icon');
    const previewTitle = $('#preview-title');
    const previewDescription = $('#preview-description');

    // Atualiza ícone
    $('input[name="icon"]').on('input', function() {
        previewIcon.attr('class', $(this).val());
    });

    // Atualiza título
    $('input[name="title"]').on('input', function() {
        previewTitle.text($(this).val());
    });

    // Atualiza descrição
    $('textarea[name="description"]').on('input', function() {
        previewDescription.text($(this).val());
    });

    // Atualiza cor de fundo
    $('input[name="bg_color"]').on('input', function() {
        previewBlock.css('background-color', $(this).val());
    });

    // Atualiza cor do texto
    $('input[name="text_color"]').on('input', function() {
        previewBlock.css('color', $(this).val());
        previewTitle.css('color', $(this).val());
        previewDescription.css('color', $(this).val());
    });

    // Atualiza cor do ícone
    $('input[name="icon_color"]').on('input', function() {
        previewIcon.css('color', $(this).val());
    });
});
</script>
@endpush
@stop
