@extends('adminlte::page')

@section('title', 'Novo Par de Banners')

@section('content_header')
    <h1>Novo Par de Banners Duplos</h1>
@stop

@section('content')
    <form action="{{ route('admin.dual-banners.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Configurações Gerais</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="order">Ordem de Exibição <span class="text-danger">*</span></label>
                                    <input type="number"
                                           class="form-control @error('order') is-invalid @enderror"
                                           id="order"
                                           name="order"
                                           value="{{ old('order', $nextOrder) }}"
                                           min="1"
                                           required>
                                    @error('order')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">Ordem em que este par será exibido (menor = primeiro)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch mt-4">
                                        <input type="checkbox"
                                               class="custom-control-input"
                                               id="active"
                                               name="active"
                                               value="1"
                                               {{ old('active', true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="active">Ativo</label>
                                    </div>
                                    <small class="form-text text-muted">Apenas pares ativos são exibidos na home</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Banner Esquerdo -->
            <div class="col-md-6">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-arrow-left"></i> Banner Esquerdo</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="left_image">Imagem <span class="text-danger">*</span></label>
                            <div class="custom-file">
                                <input type="file"
                                       class="custom-file-input @error('left_image') is-invalid @enderror"
                                       id="left_image"
                                       name="left_image"
                                       accept="image/jpeg,image/png,image/webp"
                                       required>
                                <label class="custom-file-label" for="left_image">Escolher arquivo</label>
                            </div>
                            @error('left_image')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">
                                <strong>Tamanho recomendado:</strong> 670 x 380 pixels<br>
                                Formatos: JPG, PNG, WEBP (máx. 5MB)
                            </small>
                            <div class="mt-2" id="left_preview"></div>
                        </div>

                        <div class="form-group">
                            <label for="left_link">Link (URL)</label>
                            <input type="url"
                                   class="form-control @error('left_link') is-invalid @enderror"
                                   id="left_link"
                                   name="left_link"
                                   value="{{ old('left_link') }}"
                                   placeholder="https://exemplo.com/pagina">
                            @error('left_link')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">URL de destino ao clicar no banner</small>
                        </div>

                        <div class="form-group">
                            <label for="left_alt_text">Texto Alternativo (Alt Text)</label>
                            <input type="text"
                                   class="form-control @error('left_alt_text') is-invalid @enderror"
                                   id="left_alt_text"
                                   name="left_alt_text"
                                   value="{{ old('left_alt_text') }}"
                                   placeholder="Descrição da imagem para SEO">
                            @error('left_alt_text')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Importante para acessibilidade e SEO</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="left_start_date">Data de Início</label>
                                    <input type="date"
                                           class="form-control @error('left_start_date') is-invalid @enderror"
                                           id="left_start_date"
                                           name="left_start_date"
                                           value="{{ old('left_start_date') }}">
                                    @error('left_start_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">Deixe vazio para sempre ativo</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="left_end_date">Data de Fim</label>
                                    <input type="date"
                                           class="form-control @error('left_end_date') is-invalid @enderror"
                                           id="left_end_date"
                                           name="left_end_date"
                                           value="{{ old('left_end_date') }}">
                                    @error('left_end_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">Deixe vazio para sem data de término</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Banner Direito -->
            <div class="col-md-6">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-arrow-right"></i> Banner Direito</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="right_image">Imagem <span class="text-danger">*</span></label>
                            <div class="custom-file">
                                <input type="file"
                                       class="custom-file-input @error('right_image') is-invalid @enderror"
                                       id="right_image"
                                       name="right_image"
                                       accept="image/jpeg,image/png,image/webp"
                                       required>
                                <label class="custom-file-label" for="right_image">Escolher arquivo</label>
                            </div>
                            @error('right_image')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">
                                <strong>Tamanho recomendado:</strong> 670 x 380 pixels<br>
                                Formatos: JPG, PNG, WEBP (máx. 5MB)
                            </small>
                            <div class="mt-2" id="right_preview"></div>
                        </div>

                        <div class="form-group">
                            <label for="right_link">Link (URL)</label>
                            <input type="url"
                                   class="form-control @error('right_link') is-invalid @enderror"
                                   id="right_link"
                                   name="right_link"
                                   value="{{ old('right_link') }}"
                                   placeholder="https://exemplo.com/pagina">
                            @error('right_link')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">URL de destino ao clicar no banner</small>
                        </div>

                        <div class="form-group">
                            <label for="right_alt_text">Texto Alternativo (Alt Text)</label>
                            <input type="text"
                                   class="form-control @error('right_alt_text') is-invalid @enderror"
                                   id="right_alt_text"
                                   name="right_alt_text"
                                   value="{{ old('right_alt_text') }}"
                                   placeholder="Descrição da imagem para SEO">
                            @error('right_alt_text')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Importante para acessibilidade e SEO</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="right_start_date">Data de Início</label>
                                    <input type="date"
                                           class="form-control @error('right_start_date') is-invalid @enderror"
                                           id="right_start_date"
                                           name="right_start_date"
                                           value="{{ old('right_start_date') }}">
                                    @error('right_start_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">Deixe vazio para sempre ativo</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="right_end_date">Data de Fim</label>
                                    <input type="date"
                                           class="form-control @error('right_end_date') is-invalid @enderror"
                                           id="right_end_date"
                                           name="right_end_date"
                                           value="{{ old('right_end_date') }}">
                                    @error('right_end_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">Deixe vazio para sem data de término</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Salvar Par de Banners
                        </button>
                        <a href="{{ route('admin.dual-banners.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop

@section('js')
<script>
    // Preview de imagem ao selecionar arquivo
    function setupImagePreview(inputId, previewId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);

        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" class="img-thumbnail" style="max-width: 300px;">`;
                };
                reader.readAsDataURL(file);

                // Atualiza o label do input
                const label = document.querySelector(`label[for="${inputId}"]`);
                if (label) {
                    label.textContent = file.name;
                }
            }
        });
    }

    setupImagePreview('left_image', 'left_preview');
    setupImagePreview('right_image', 'right_preview');

    // Atualiza label do custom-file-input
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName);
    });
</script>
@stop
