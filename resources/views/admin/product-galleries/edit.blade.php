@extends('adminlte::page')

@section('title', 'Editar Galeria de Produtos')

@section('content_header')
    <h1>Editar Galeria #{{ $productGallery->order }}</h1>
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

<form action="{{ route('admin.product-galleries.update', $productGallery) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row">
        <!-- Configurações Básicas -->
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Configurações Básicas</h3>
                </div>
                <div class="box-body">
                    <div class="form-group @error('order') has-error @enderror">
                        <label>Ordem de Exibição *</label>
                        <input type="number" name="order" class="form-control" value="{{ old('order', $productGallery->order) }}" min="1" max="4" required>
                        <p class="help-block">Posição da galeria na home (1-4)</p>
                        @error('order')<span class="help-block">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group @error('title') has-error @enderror">
                        <label>Título *</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $productGallery->title) }}" required maxlength="100">
                        @error('title')<span class="help-block">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group @error('subtitle') has-error @enderror">
                        <label>Subtítulo</label>
                        <input type="text" name="subtitle" class="form-control" value="{{ old('subtitle', $productGallery->subtitle) }}" maxlength="255">
                        @error('subtitle')<span class="help-block">{{ $message }}</span>@enderror
                    </div>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="active" value="1" {{ old('active', $productGallery->active) ? 'checked' : '' }}> Ativo
                        </label>
                    </div>
                </div>
            </div>

            <!-- Configurações de Layout -->
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Layout e Produtos</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group @error('mobile_columns') has-error @enderror">
                                <label>Colunas Mobile *</label>
                                <select name="mobile_columns" class="form-control" required>
                                    <option value="1" {{ old('mobile_columns', $productGallery->mobile_columns) == 1 ? 'selected' : '' }}>1 por linha</option>
                                    <option value="2" {{ old('mobile_columns', $productGallery->mobile_columns) == 2 ? 'selected' : '' }}>2 por linha</option>
                                    <option value="3" {{ old('mobile_columns', $productGallery->mobile_columns) == 3 ? 'selected' : '' }}>3 por linha</option>
                                    <option value="4" {{ old('mobile_columns', $productGallery->mobile_columns) == 4 ? 'selected' : '' }}>4 por linha</option>
                                </select>
                                @error('mobile_columns')<span class="help-block">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group @error('desktop_columns') has-error @enderror">
                                <label>Colunas Desktop *</label>
                                <select name="desktop_columns" class="form-control" required>
                                    <option value="2" {{ old('desktop_columns', $productGallery->desktop_columns) == 2 ? 'selected' : '' }}>2 por linha</option>
                                    <option value="3" {{ old('desktop_columns', $productGallery->desktop_columns) == 3 ? 'selected' : '' }}>3 por linha</option>
                                    <option value="4" {{ old('desktop_columns', $productGallery->desktop_columns) == 4 ? 'selected' : '' }}>4 por linha</option>
                                    <option value="6" {{ old('desktop_columns', $productGallery->desktop_columns) == 6 ? 'selected' : '' }}>6 por linha</option>
                                </select>
                                @error('desktop_columns')<span class="help-block">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group @error('products_limit') has-error @enderror">
                        <label>Quantidade de Produtos *</label>
                        <input type="number" name="products_limit" class="form-control" value="{{ old('products_limit', $productGallery->products_limit) }}" min="1" max="50" required>
                        <p class="help-block">Número total de produtos a exibir</p>
                        @error('products_limit')<span class="help-block">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros e Estilização -->
        <div class="col-md-6">
            <!-- Filtro de Produtos -->
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title">Filtro de Produtos</h3>
                </div>
                <div class="box-body">
                    <div class="form-group @error('filter_type') has-error @enderror">
                        <label>Tipo de Filtro *</label>
                        <select name="filter_type" id="filter_type" class="form-control" required>
                            <option value="category" {{ old('filter_type', $productGallery->filter_type) == 'category' ? 'selected' : '' }}>Por Categoria</option>
                            <option value="best_sellers" {{ old('filter_type', $productGallery->filter_type) == 'best_sellers' ? 'selected' : '' }}>Mais Vendidos</option>
                            <option value="on_sale" {{ old('filter_type', $productGallery->filter_type) == 'on_sale' ? 'selected' : '' }}>Em Promoção</option>
                            <option value="low_stock" {{ old('filter_type', $productGallery->filter_type) == 'low_stock' ? 'selected' : '' }}>Estoque Baixo</option>
                        </select>
                        @error('filter_type')<span class="help-block">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group @error('filter_value') has-error @enderror" id="category_selector">
                        <label>Categoria</label>
                        <select name="filter_value" class="form-control">
                            <option value="">Selecione uma categoria</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('filter_value', $productGallery->filter_value) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('filter_value')<span class="help-block">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <!-- Cores -->
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">Cores</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group @error('title_color') has-error @enderror">
                                <label>Cor do Título *</label>
                                <input type="color" name="title_color" class="form-control" value="{{ old('title_color', $productGallery->title_color) }}" required>
                                @error('title_color')<span class="help-block">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group @error('subtitle_color') has-error @enderror">
                                <label>Cor do Subtítulo *</label>
                                <input type="color" name="subtitle_color" class="form-control" value="{{ old('subtitle_color', $productGallery->subtitle_color) }}" required>
                                @error('subtitle_color')<span class="help-block">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group @error('background_color') has-error @enderror">
                        <label>Cor de Fundo</label>
                        <input type="color" name="background_color" class="form-control" value="{{ old('background_color', $productGallery->background_color) }}">
                        <p class="help-block">Deixe em branco para fundo transparente</p>
                        @error('background_color')<span class="help-block">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group @error('background_image') has-error @enderror">
                        <label>Imagem de Fundo</label>
                        @if($productGallery->background_image_path)
                            <div class="mb-2">
                                <img src="{{ $productGallery->getBackgroundImageUrl() }}" alt="Imagem Atual" style="max-width: 200px; height: auto; border: 1px solid #ddd; padding: 5px;">
                            </div>
                        @endif
                        <input type="file" name="background_image" class="form-control" accept="image/*">
                        <p class="help-block">Opcional. JPG, PNG ou WebP (máx. 5MB). Deixe em branco para manter a imagem atual.</p>
                        @error('background_image')<span class="help-block">{{ $message }}</span>@enderror
                    </div>

                    <hr>
                    <h4>Botão "Ver Todos"</h4>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="show_view_all_button" value="1" {{ old('show_view_all_button', $productGallery->show_view_all_button) ? 'checked' : '' }}> Exibir botão
                        </label>
                    </div>

                    <div class="form-group @error('view_all_url') has-error @enderror">
                        <label>URL do Botão</label>
                        <input type="text" name="view_all_url" class="form-control" value="{{ old('view_all_url', $productGallery->view_all_url) }}" placeholder="/categoria-exemplo">
                        @error('view_all_url')<span class="help-block">{{ $message }}</span>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group @error('button_bg_color') has-error @enderror">
                                <label>Cor Fundo *</label>
                                <input type="color" name="button_bg_color" class="form-control" value="{{ old('button_bg_color', $productGallery->button_bg_color) }}" required>
                                @error('button_bg_color')<span class="help-block">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group @error('button_hover_color') has-error @enderror">
                                <label>Cor Hover *</label>
                                <input type="color" name="button_hover_color" class="form-control" value="{{ old('button_hover_color', $productGallery->button_hover_color) }}" required>
                                @error('button_hover_color')<span class="help-block">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group @error('button_text_color') has-error @enderror">
                                <label>Cor Texto *</label>
                                <input type="color" name="button_text_color" class="form-control" value="{{ old('button_text_color', $productGallery->button_text_color) }}" required>
                                @error('button_text_color')<span class="help-block">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Botões de Ação -->
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-footer">
                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Atualizar Galeria</button>
                    <a href="{{ route('admin.product-galleries.index') }}" class="btn btn-default">Cancelar</a>
                </div>
            </div>
        </div>
    </div>
</form>
@stop

@push('js')
<script>
$(document).ready(function() {
    // Mostra/esconde seletor de categoria baseado no tipo de filtro
    function toggleCategorySelector() {
        var filterType = $('#filter_type').val();
        if (filterType === 'category') {
            $('#category_selector').show();
        } else {
            $('#category_selector').hide();
        }
    }

    $('#filter_type').on('change', toggleCategorySelector);
    toggleCategorySelector(); // Executa na carga da página
});
</script>
@endpush
