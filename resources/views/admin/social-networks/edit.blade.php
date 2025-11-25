@extends('adminlte::page')

@section('title', 'Editar Rede Social')

@section('content_header')
    <h1>Editar Rede Social</h1>
@stop

@section('content')
    <form action="{{ route('admin.social-networks.update', $socialNetwork) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informações da Rede Social</h3>
            </div>

            <div class="card-body">
                {{-- Nome --}}
                <div class="form-group">
                    <label for="name">Nome da Rede Social <span class="text-danger">*</span></label>
                    <input type="text"
                           name="name"
                           id="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $socialNetwork->name) }}"
                           placeholder="Ex: Facebook, Instagram, WhatsApp"
                           maxlength="50"
                           required>
                    @error('name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">Nome descritivo da rede social</small>
                </div>

                {{-- Ícone Atual --}}
                <div class="form-group">
                    <label>Ícone Atual</label>
                    <div>
                        <img src="{{ $socialNetwork->getIconUrl() }}"
                             alt="{{ $socialNetwork->name }}"
                             style="max-width: 60px; max-height: 60px; border: 1px solid #ddd; padding: 5px;">
                    </div>
                </div>

                {{-- Novo Ícone (opcional) --}}
                <div class="form-group">
                    <label for="icon">Novo Ícone (opcional)</label>
                    <div class="custom-file">
                        <input type="file"
                               name="icon"
                               id="icon"
                               class="custom-file-input @error('icon') is-invalid @enderror"
                               accept="image/png,image/jpeg,image/jpg,image/svg+xml">
                        <label class="custom-file-label" for="icon">Escolher arquivo...</label>
                        @error('icon')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <small class="form-text text-muted">
                        Deixe em branco para manter o ícone atual. Formatos: PNG, JPG, JPEG, SVG. Tamanho: 40x40px. Máx: 2MB
                    </small>

                    {{-- Preview do novo ícone --}}
                    <div id="icon-preview" class="mt-2" style="display: none;">
                        <img id="preview-image" src="" alt="Preview" style="max-width: 60px; max-height: 60px; border: 1px solid #ddd; padding: 5px;">
                    </div>
                </div>

                {{-- URL --}}
                <div class="form-group">
                    <label for="url">URL da Rede Social <span class="text-danger">*</span></label>
                    <input type="url"
                           name="url"
                           id="url"
                           class="form-control @error('url') is-invalid @enderror"
                           value="{{ old('url', $socialNetwork->url) }}"
                           placeholder="https://facebook.com/suapagina"
                           maxlength="255"
                           required>
                    @error('url')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">URL completa incluindo https://</small>
                </div>

                {{-- Ordem --}}
                <div class="form-group">
                    <label for="order">Ordem de Exibição <span class="text-danger">*</span></label>
                    <input type="number"
                           name="order"
                           id="order"
                           class="form-control @error('order') is-invalid @enderror"
                           value="{{ old('order', $socialNetwork->order) }}"
                           min="1"
                           required>
                    @error('order')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">
                        Número que define a ordem de exibição (menor número aparece primeiro). Deve ser único.
                    </small>
                </div>

                {{-- Status Ativo --}}
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox"
                               class="custom-control-input"
                               id="active"
                               name="active"
                               value="1"
                               {{ old('active', $socialNetwork->active) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="active">
                            <strong>Rede Social Ativa</strong>
                        </label>
                    </div>
                    <small class="form-text text-muted">
                        Apenas redes sociais ativas são exibidas no site
                    </small>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Atualizar
                </button>
                <a href="{{ route('admin.social-networks.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </div>
    </form>
@stop

@section('js')
<script>
// Preview do ícone ao selecionar arquivo
document.getElementById('icon').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-image').src = e.target.result;
            document.getElementById('icon-preview').style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
});

// Atualiza label do custom-file-input com o nome do arquivo
$('.custom-file-input').on('change', function() {
    let fileName = $(this).val().split('\\').pop();
    $(this).next('.custom-file-label').html(fileName);
});
</script>
@stop
