@extends('adminlte::page')
@section('title', 'Cookie Consent LGPD')
@section('content')

<form action="{{ route('admin.cookie-consent.update') }}" method="POST">
    @csrf
    @method('PUT')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Configurações do Disclaimer de Cookies (LGPD)</h3>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            {{-- Status Ativo --}}
            <div class="form-group">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="active" name="active" value="1" {{ $config->active ? 'checked' : '' }}>
                    <label class="custom-control-label" for="active">
                        <strong>Disclaimer Ativo</strong>
                    </label>
                </div>
                <small class="form-text text-muted">Quando ativo, o disclaimer será exibido para usuários que ainda não aceitaram os cookies</small>
            </div>

            <hr>

            {{-- Texto do Disclaimer --}}
            <div class="form-group">
                <label>Texto do Disclaimer <span class="text-danger">*</span></label>
                <textarea name="message_text" class="form-control" rows="4" required>{{ $config->message_text }}</textarea>
                <small class="form-text text-muted">
                    Você pode usar HTML neste campo (tags &lt;a&gt;, &lt;strong&gt;, &lt;em&gt;, etc.)
                </small>
            </div>

            <hr>

            <h5>Configurações do Botão de Aceite</h5>

            <div class="row">
                {{-- Label do Botão --}}
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Texto do Botão <span class="text-danger">*</span></label>
                        <input type="text" name="button_label" class="form-control" value="{{ $config->button_label }}" maxlength="50" required>
                        <small class="form-text text-muted">Exemplo: "Aceito", "Concordo", "OK"</small>
                    </div>
                </div>

                {{-- Cor de Fundo --}}
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Cor de Fundo do Botão <span class="text-danger">*</span></label>
                        <input type="color" name="button_bg_color" class="form-control" value="{{ $config->button_bg_color }}" required>
                        <small class="form-text text-muted">Cor padrão: #FFA733</small>
                    </div>
                </div>

                {{-- Cor do Texto --}}
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Cor do Texto do Botão <span class="text-danger">*</span></label>
                        <input type="color" name="button_text_color" class="form-control" value="{{ $config->button_text_color }}" required>
                        <small class="form-text text-muted">Cor padrão: #FFFFFF</small>
                    </div>
                </div>

                {{-- Cor de Fundo Hover --}}
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Cor ao Passar o Mouse <span class="text-danger">*</span></label>
                        <input type="color" name="button_hover_bg_color" class="form-control" value="{{ $config->button_hover_bg_color }}" required>
                        <small class="form-text text-muted">Cor padrão: #013E3B</small>
                    </div>
                </div>
            </div>

            <hr>

            {{-- Preview do Botão --}}
            <div class="form-group">
                <label>Preview do Botão:</label>
                <div>
                    <button type="button" id="preview-button" class="btn" style="
                        background-color: {{ $config->button_bg_color }};
                        color: {{ $config->button_text_color }};
                        border: none;
                        padding: 10px 25px;
                        border-radius: 30px;
                        cursor: pointer;
                    ">{{ $config->button_label }}</button>
                </div>
                <small class="form-text text-muted">Este é um preview aproximado. As cores mudam ao preencher os campos acima.</small>
            </div>

        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Salvar Configurações
            </button>
        </div>
    </div>
</form>

@stop

@section('js')
<script>
// Atualiza preview do botão em tempo real
document.addEventListener('DOMContentLoaded', function() {
    const previewBtn = document.getElementById('preview-button');
    const labelInput = document.querySelector('input[name="button_label"]');
    const bgColorInput = document.querySelector('input[name="button_bg_color"]');
    const textColorInput = document.querySelector('input[name="button_text_color"]');
    const hoverColorInput = document.querySelector('input[name="button_hover_bg_color"]');

    // Atualiza texto
    labelInput.addEventListener('input', function() {
        previewBtn.textContent = this.value;
    });

    // Atualiza cor de fundo
    bgColorInput.addEventListener('input', function() {
        previewBtn.style.backgroundColor = this.value;
    });

    // Atualiza cor do texto
    textColorInput.addEventListener('input', function() {
        previewBtn.style.color = this.value;
    });

    // Evento de hover
    previewBtn.addEventListener('mouseenter', function() {
        this.style.backgroundColor = hoverColorInput.value;
    });

    previewBtn.addEventListener('mouseleave', function() {
        this.style.backgroundColor = bgColorInput.value;
    });
});
</script>
@stop
