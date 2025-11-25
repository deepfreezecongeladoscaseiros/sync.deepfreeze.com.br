@extends('adminlte::page')

@section('title', 'Logomarca')

@section('content_header')
    <h1>
        <i class="fa fa-image"></i> Logomarca da Loja
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.layout.index') }}"><i class="fa fa-paint-brush"></i> Layout</a></li>
        <li class="active">Logomarca</li>
    </ol>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Upload de Nova Logo</h3>
            </div>

            <form action="{{ route('admin.layout.logo.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="box-body">
                    {{-- Alerta de dimensões --}}
                    <div class="alert alert-info">
                        <i class="icon fa fa-info-circle"></i>
                        <strong>Dimensões:</strong> Altura mínima de 120px (pode ser quadrada ou retangular)<br>
                        <strong>Exemplos:</strong> 120x120px (quadrada), 360x120px (horizontal), 120x200px (vertical)<br>
                        <strong>Formatos aceitos:</strong> PNG, JPG, JPEG, SVG<br>
                        <strong>Tamanho máximo:</strong> 2MB<br>
                        <strong>Recomendação:</strong> PNG com fundo transparente
                    </div>

                    {{-- Upload de arquivo --}}
                    <div class="form-group @error('logo') has-error @enderror">
                        <label for="logo">Selecione a Logo *</label>
                        <input type="file"
                               name="logo"
                               id="logo"
                               class="form-control"
                               accept="image/png,image/jpeg,image/jpg,image/svg+xml"
                               required>
                        @error('logo')
                            <span class="help-block">{{ $message }}</span>
                        @enderror
                        <p class="help-block">A logo será exibida no cabeçalho e rodapé do site.</p>
                    </div>

                    {{-- Preview da imagem --}}
                    <div class="form-group">
                        <label>Preview</label>
                        <div id="preview-container" style="border: 1px solid #ddd; padding: 20px; background: #f5f5f5; min-height: 120px; display: flex; align-items: center; justify-content: center;">
                            <img id="logo-preview" src="#" alt="Preview" style="display:none; max-width: 100%; height: auto;">
                            <span id="preview-text" style="color: #999;">Nenhuma imagem selecionada</span>
                        </div>
                        <p class="help-block" id="dimensions-info" style="display:none; margin-top: 10px;">
                            <strong>Dimensões:</strong> <span id="img-width"></span>x<span id="img-height"></span>px
                        </p>
                    </div>

                    {{-- Texto alternativo (alt) --}}
                    <div class="form-group @error('logo_alt') has-error @enderror">
                        <label for="logo_alt">Texto Alternativo (Alt)</label>
                        <input type="text"
                               name="logo_alt"
                               id="logo_alt"
                               class="form-control"
                               value="{{ old('logo_alt', $theme->logo_alt ?? config('app.name')) }}"
                               placeholder="Ex: Deep Freeze - Alimentos Congelados">
                        @error('logo_alt')
                            <span class="help-block">{{ $message }}</span>
                        @enderror
                        <p class="help-block">Importante para acessibilidade e SEO.</p>
                    </div>
                </div>

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-upload"></i> Fazer Upload
                    </button>
                    <a href="{{ route('admin.layout.index') }}" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        {{-- Logo Atual --}}
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">Logo Atual</h3>
            </div>
            <div class="box-body">
                @if($theme->logo_path)
                    <div style="border: 1px solid #ddd; padding: 20px; background: #fff; text-align: center;">
                        <img src="{{ asset('storage/' . $theme->logo_path) }}"
                             alt="{{ $theme->logo_alt }}"
                             style="max-width: 100%; height: auto;">
                    </div>
                    <p class="help-block" style="margin-top: 10px;">
                        <strong>Alt:</strong> {{ $theme->logo_alt }}<br>
                        <strong>Arquivo:</strong> {{ basename($theme->logo_path) }}<br>
                        <strong>Atualizado:</strong> {{ $theme->updated_at->format('d/m/Y H:i') }}
                    </p>
                @else
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle"></i>
                        Nenhuma logo configurada ainda.
                    </div>
                @endif
            </div>
        </div>

        {{-- Dicas --}}
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Dicas</h3>
            </div>
            <div class="box-body">
                <ul style="padding-left: 20px;">
                    <li><strong>Altura mínima:</strong> 120px (obrigatório)</li>
                    <li><strong>Altura ideal:</strong> 120-150px</li>
                    <li>Pode ser quadrada ou retangular</li>
                    <li>Use PNG com fundo transparente</li>
                    <li>Evite textos muito pequenos</li>
                    <li>Teste em dispositivos móveis</li>
                    <li>A logo sempre aponta para a página inicial</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
// Preview da imagem selecionada
document.getElementById('logo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const img = document.getElementById('logo-preview');
            const text = document.getElementById('preview-text');
            const dimensionsInfo = document.getElementById('dimensions-info');

            img.src = event.target.result;
            img.style.display = 'block';
            text.style.display = 'none';

            // Detecta dimensões
            img.onload = function() {
                const width = this.naturalWidth;
                const height = this.naturalHeight;

                document.getElementById('img-width').textContent = width;
                document.getElementById('img-height').textContent = height;
                dimensionsInfo.style.display = 'block';

                // Valida altura mínima de 120px
                if (height < 120) {
                    dimensionsInfo.innerHTML = `
                        <span class="text-danger">
                            <i class="fa fa-times-circle"></i>
                            <strong>Erro:</strong> A logo deve ter pelo menos 120px de altura.
                            Sua imagem tem ${width}x${height}px.
                        </span>
                    `;
                } else if (height >= 120 && height <= 150) {
                    dimensionsInfo.innerHTML = `
                        <span class="text-success">
                            <i class="fa fa-check"></i>
                            <strong>Perfeito!</strong> Dimensões ideais: ${width}x${height}px
                        </span>
                    `;
                } else {
                    dimensionsInfo.innerHTML = `
                        <span class="text-info">
                            <i class="fa fa-info-circle"></i>
                            <strong>OK!</strong> Dimensões: ${width}x${height}px
                            ${height > 150 ? '<br><small>Altura maior que o ideal (150px), mas funcionará.</small>' : ''}
                        </span>
                    `;
                }
            };
        };
        reader.readAsDataURL(file);
    }
});
</script>
@stop
