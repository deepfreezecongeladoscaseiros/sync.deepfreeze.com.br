@extends('adminlte::page')

@section('title', 'Barra de Anúncios (Top Bar)')

@section('content_header')
    <h1>
        <i class="fa fa-bullhorn"></i> Barra de Anúncios (Top Bar)
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.layout.index') }}"><i class="fa fa-paint-brush"></i> Layout</a></li>
        <li class="active">Barra de Anúncios</li>
    </ol>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">Configurar Barra de Anúncios</h3>
            </div>

            <form action="{{ route('admin.layout.topbar.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="box-body">
                    {{-- Ativar/Desativar --}}
                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input type="hidden" name="top_bar_enabled" value="0">
                                <input type="checkbox"
                                       name="top_bar_enabled"
                                       id="top_bar_enabled"
                                       value="1"
                                       {{ old('top_bar_enabled', $theme->top_bar_enabled) ? 'checked' : '' }}>
                                <strong>Ativar Barra de Anúncios</strong>
                            </label>
                        </div>
                        <p class="help-block">Exibe uma barra no topo de todas as páginas do site.</p>
                    </div>

                    <hr>

                    {{-- Texto/HTML --}}
                    <div class="form-group @error('top_bar_text') has-error @enderror">
                        <label for="top_bar_text">Texto da Barra de Anúncios</label>
                        <textarea name="top_bar_text"
                                  id="top_bar_text"
                                  class="form-control"
                                  rows="4"
                                  maxlength="500"
                                  placeholder="Ex: entrega expressa: receba em até 24h úteis">{{ old('top_bar_text', $theme->top_bar_text) }}</textarea>
                        @error('top_bar_text')
                            <span class="help-block">{{ $message }}</span>
                        @enderror
                        <p class="help-block">
                            <i class="fa fa-info-circle"></i>
                            Suporta HTML básico. Use tags como <code>&lt;strong&gt;</code>, <code>&lt;a href=""&gt;</code>, <code>&lt;br&gt;</code>, etc.
                            <br>
                            <strong>Exemplo com link:</strong>
                            <code>Frete grátis acima de R$ 100! &lt;a href="/produtos" style="text-decoration:underline"&gt;Ver produtos&lt;/a&gt;</code>
                        </p>
                        <small class="text-muted">
                            <span id="char-count">0</span>/500 caracteres
                        </small>
                    </div>

                    {{-- Cores --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group @error('top_bar_bg_color') has-error @enderror">
                                <label for="top_bar_bg_color">Cor de Fundo</label>
                                <div class="input-group">
                                    <input type="color"
                                           name="top_bar_bg_color"
                                           id="top_bar_bg_color"
                                           value="{{ old('top_bar_bg_color', $theme->top_bar_bg_color ?? '#013E3B') }}"
                                           class="form-control">
                                    <span class="input-group-addon">
                                        <span id="bg-color-hex">{{ $theme->top_bar_bg_color ?? '#013E3B' }}</span>
                                    </span>
                                </div>
                                @error('top_bar_bg_color')
                                    <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group @error('top_bar_text_color') has-error @enderror">
                                <label for="top_bar_text_color">Cor do Texto</label>
                                <div class="input-group">
                                    <input type="color"
                                           name="top_bar_text_color"
                                           id="top_bar_text_color"
                                           value="{{ old('top_bar_text_color', $theme->top_bar_text_color ?? '#FFFFFF') }}"
                                           class="form-control">
                                    <span class="input-group-addon">
                                        <span id="text-color-hex">{{ $theme->top_bar_text_color ?? '#FFFFFF' }}</span>
                                    </span>
                                </div>
                                @error('top_bar_text_color')
                                    <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Preview --}}
                    <div class="form-group">
                        <label>Preview em Tempo Real</label>
                        <div id="preview-container" style="min-height: 50px; transition: all 0.3s;">
                            <div id="top-bar-preview"
                                 style="padding: 12px 0; text-align: center; font-size: 14px; display: none;">
                                <div class="container">
                                    <span id="preview-text">entrega expressa: receba em até 24h úteis</span>
                                </div>
                            </div>
                        </div>
                        <p class="help-block">
                            <i class="fa fa-eye"></i>
                            Este é um preview aproximado de como a barra aparecerá no site.
                        </p>
                    </div>
                </div>

                <div class="box-footer">
                    <button type="submit" class="btn btn-warning">
                        <i class="fa fa-save"></i> Salvar Configurações
                    </button>
                    <a href="{{ route('admin.layout.index') }}" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Voltar
                    </a>
                    <a href="{{ url('/') }}" target="_blank" class="btn btn-info pull-right">
                        <i class="fa fa-external-link"></i> Pré-visualizar Loja
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        {{-- Status Atual --}}
        <div class="box box-{{ $theme->top_bar_enabled ? 'success' : 'default' }}">
            <div class="box-header with-border">
                <h3 class="box-title">Status Atual</h3>
            </div>
            <div class="box-body">
                <p>
                    <strong>Status:</strong>
                    @if($theme->top_bar_enabled)
                        <span class="label label-success">Ativa</span>
                    @else
                        <span class="label label-default">Desativada</span>
                    @endif
                </p>
                @if($theme->top_bar_text)
                    <p>
                        <strong>Texto atual:</strong><br>
                        <small class="text-muted">{{ Str::limit(strip_tags($theme->top_bar_text), 100) }}</small>
                    </p>
                @endif
                <p>
                    <strong>Última atualização:</strong><br>
                    {{ $theme->updated_at->format('d/m/Y H:i') }}
                </p>
            </div>
        </div>

        {{-- Dicas --}}
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Dicas de Uso</h3>
            </div>
            <div class="box-body">
                <ul style="padding-left: 20px; font-size: 13px;">
                    <li>Mantenha o texto curto e objetivo</li>
                    <li>Use para promoções, frete grátis, avisos</li>
                    <li>Pode incluir links e formatação HTML</li>
                    <li>Teste o contraste entre texto e fundo</li>
                    <li>A barra aparece em todas as páginas</li>
                    <li>Máximo de 500 caracteres</li>
                </ul>
            </div>
        </div>

        {{-- Exemplos --}}
        <div class="box box-info collapsed-box">
            <div class="box-header with-border">
                <h3 class="box-title">Exemplos de Texto</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body" style="display: none;">
                <small>
                    <strong>Exemplo 1:</strong><br>
                    <code>Frete grátis acima de R$ 100!</code>
                    <hr>
                    <strong>Exemplo 2:</strong><br>
                    <code>Black Friday: até 50% OFF em todos os produtos</code>
                    <hr>
                    <strong>Exemplo 3 (com link):</strong><br>
                    <code>&lt;strong&gt;Novidade!&lt;/strong&gt; Confira nossos &lt;a href="/kits"&gt;kits especiais&lt;/a&gt;</code>
                </small>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Elementos
    const enabledCheckbox = $('#top_bar_enabled');
    const textArea = $('#top_bar_text');
    const bgColorInput = $('#top_bar_bg_color');
    const textColorInput = $('#top_bar_text_color');
    const previewContainer = $('#top-bar-preview');
    const previewText = $('#preview-text');
    const charCount = $('#char-count');
    const bgColorHex = $('#bg-color-hex');
    const textColorHex = $('#text-color-hex');

    // Atualiza preview
    function updatePreview() {
        const isEnabled = enabledCheckbox.is(':checked');
        const text = textArea.val() || 'entrega expressa: receba em até 24h úteis';
        const bgColor = bgColorInput.val();
        const textColor = textColorInput.val();

        if (isEnabled) {
            previewContainer.show();
            previewText.html(text);
            previewContainer.css({
                'background-color': bgColor,
                'color': textColor
            });
        } else {
            previewContainer.hide();
        }

        // Atualiza contador de caracteres
        charCount.text(text.length);

        // Atualiza hex display
        bgColorHex.text(bgColor.toUpperCase());
        textColorHex.text(textColor.toUpperCase());
    }

    // Event listeners
    enabledCheckbox.on('change', updatePreview);
    textArea.on('input', updatePreview);
    bgColorInput.on('input', updatePreview);
    textColorInput.on('input', updatePreview);

    // Preview inicial
    updatePreview();
});
</script>
@stop

@section('css')
<style>
#top-bar-preview {
    transition: all 0.3s ease;
}

#top-bar-preview a {
    color: inherit;
    text-decoration: underline;
}

#char-count {
    font-weight: bold;
}

.input-group-addon {
    min-width: 80px;
}
</style>
@stop
