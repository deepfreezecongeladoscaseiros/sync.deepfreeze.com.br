@extends('adminlte::page')

@section('title', 'Editar Cores do Tema')

@section('content_header')
    <h1>
        <i class="fa fa-paint-brush"></i> Editar Cores do Tema
        <small>{{ $theme->name }}</small>
    </h1>
@stop

@section('content')
<form action="{{ route('admin.layout.colors.update') }}" method="POST">
    @csrf
    @method('PUT')

    {{-- Cores da Marca --}}
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-bookmark"></i> Cores da Marca</h3>
            <p class="help-block">Cores principais da identidade visual</p>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Primária</label>
                        <input type="color" name="colors[brand.primary]" value="{{ $theme->colors['brand']['primary'] }}" class="form-control">
                        <span class="help-block">{{ $theme->colors['brand']['primary'] }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Secundária</label>
                        <input type="color" name="colors[brand.secondary]" value="{{ $theme->colors['brand']['secondary'] }}" class="form-control">
                        <span class="help-block">{{ $theme->colors['brand']['secondary'] }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Destaque</label>
                        <input type="color" name="colors[brand.accent]" value="{{ $theme->colors['brand']['accent'] }}" class="form-control">
                        <span class="help-block">{{ $theme->colors['brand']['accent'] }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Clara</label>
                        <input type="color" name="colors[brand.light]" value="{{ $theme->colors['brand']['light'] }}" class="form-control">
                        <span class="help-block">{{ $theme->colors['brand']['light'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cores de Texto --}}
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-font"></i> Cores de Texto</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Texto Principal</label>
                        <input type="color" name="colors[text.primary]" value="{{ $theme->colors['text']['primary'] }}" class="form-control">
                        <span class="help-block">{{ $theme->colors['text']['primary'] }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Texto Secundário</label>
                        <input type="color" name="colors[text.secondary]" value="{{ $theme->colors['text']['secondary'] }}" class="form-control">
                        <span class="help-block">{{ $theme->colors['text']['secondary'] }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Texto Desativado</label>
                        <input type="color" name="colors[text.muted]" value="{{ $theme->colors['text']['muted'] }}" class="form-control">
                        <span class="help-block">{{ $theme->colors['text']['muted'] }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Texto Branco</label>
                        <input type="color" name="colors[text.white]" value="{{ $theme->colors['text']['white'] }}" class="form-control">
                        <span class="help-block">{{ $theme->colors['text']['white'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cores de Fundo --}}
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-square"></i> Cores de Fundo</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Fundo Principal</label>
                        <input type="color" name="colors[background.main]" value="{{ $theme->colors['background']['main'] }}" class="form-control">
                        <span class="help-block">{{ $theme->colors['background']['main'] }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Fundo Claro</label>
                        <input type="color" name="colors[background.light]" value="{{ $theme->colors['background']['light'] }}" class="form-control">
                        <span class="help-block">{{ $theme->colors['background']['light'] }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Fundo Cinza</label>
                        <input type="color" name="colors[background.gray]" value="{{ $theme->colors['background']['gray'] }}" class="form-control">
                        <span class="help-block">{{ $theme->colors['background']['gray'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cores de Botões --}}
    <div class="box box-warning">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-hand-pointer-o"></i> Cores de Botões</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <h4>Botão Primário</h4>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Fundo</label>
                                <input type="color" name="colors[button.primary_bg]" value="{{ $theme->colors['button']['primary_bg'] }}" class="form-control">
                                <span class="help-block">{{ $theme->colors['button']['primary_bg'] }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Texto</label>
                                <input type="color" name="colors[button.primary_text]" value="{{ $theme->colors['button']['primary_text'] }}" class="form-control">
                                <span class="help-block">{{ $theme->colors['button']['primary_text'] }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Hover</label>
                                <input type="color" name="colors[button.primary_hover]" value="{{ $theme->colors['button']['primary_hover'] }}" class="form-control">
                                <span class="help-block">{{ $theme->colors['button']['primary_hover'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <h4>Botão Secundário</h4>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Fundo</label>
                                <input type="color" name="colors[button.secondary_bg]" value="{{ $theme->colors['button']['secondary_bg'] }}" class="form-control">
                                <span class="help-block">{{ $theme->colors['button']['secondary_bg'] }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Texto</label>
                                <input type="color" name="colors[button.secondary_text]" value="{{ $theme->colors['button']['secondary_text'] }}" class="form-control">
                                <span class="help-block">{{ $theme->colors['button']['secondary_text'] }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Hover</label>
                                <input type="color" name="colors[button.secondary_hover]" value="{{ $theme->colors['button']['secondary_hover'] }}" class="form-control">
                                <span class="help-block">{{ $theme->colors['button']['secondary_hover'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cores do Botão Comprar --}}
    <div class="box box-success">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-shopping-cart"></i> Botão Comprar (Produtos)</h3>
            <div class="box-tools pull-right">
                <span class="badge bg-green">Novo</span>
            </div>
        </div>
        <div class="box-body">
            <p class="text-muted">
                <i class="fa fa-info-circle"></i>
                Cores específicas do botão "Comprar" exibido nos cards de produtos.
            </p>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Fundo</label>
                        <input type="color" name="colors[buy_button.bg]" value="{{ $theme->colors['buy_button']['bg'] ?? '#FFA733' }}" class="form-control">
                        <span class="help-block">{{ $theme->colors['buy_button']['bg'] ?? '#FFA733' }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Texto</label>
                        <input type="color" name="colors[buy_button.text]" value="{{ $theme->colors['buy_button']['text'] ?? '#FFFFFF' }}" class="form-control">
                        <span class="help-block">{{ $theme->colors['buy_button']['text'] ?? '#FFFFFF' }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Fundo (Hover)</label>
                        <input type="color" name="colors[buy_button.hover_bg]" value="{{ $theme->colors['buy_button']['hover_bg'] ?? '#013E3B' }}" class="form-control">
                        <span class="help-block">{{ $theme->colors['buy_button']['hover_bg'] ?? '#013E3B' }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Texto (Hover)</label>
                        <input type="color" name="colors[buy_button.hover_text]" value="{{ $theme->colors['buy_button']['hover_text'] ?? '#FFFFFF' }}" class="form-control">
                        <span class="help-block">{{ $theme->colors['buy_button']['hover_text'] ?? '#FFFFFF' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cores de Links --}}
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-link"></i> Cores de Links</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Link Padrão</label>
                        <input type="color" name="colors[link.default]" value="{{ $theme->colors['link']['default'] }}" class="form-control">
                        <span class="help-block">{{ $theme->colors['link']['default'] }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Link Hover</label>
                        <input type="color" name="colors[link.hover]" value="{{ $theme->colors['link']['hover'] }}" class="form-control">
                        <span class="help-block">{{ $theme->colors['link']['hover'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cores de Status --}}
    <div class="box box-success">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-check-circle"></i> Cores de Status/Feedback</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Sucesso</label>
                        <input type="color" name="colors[status.success]" value="{{ $theme->colors['status']['success'] }}" class="form-control">
                        <span class="help-block">{{ $theme->colors['status']['success'] }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Erro</label>
                        <input type="color" name="colors[status.error]" value="{{ $theme->colors['status']['error'] }}" class="form-control">
                        <span class="help-block">{{ $theme->colors['status']['error'] }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Aviso</label>
                        <input type="color" name="colors[status.warning]" value="{{ $theme->colors['status']['warning'] }}" class="form-control">
                        <span class="help-block">{{ $theme->colors['status']['warning'] }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Informação</label>
                        <input type="color" name="colors[status.info]" value="{{ $theme->colors['status']['info'] }}" class="form-control">
                        <span class="help-block">{{ $theme->colors['status']['info'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Botões de Ação --}}
    <div class="box box-solid">
        <div class="box-footer">
            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fa fa-save"></i> Salvar Alterações
                    </button>
                    <a href="{{ route('admin.layout.index') }}" class="btn btn-default btn-lg">
                        <i class="fa fa-arrow-left"></i> Voltar
                    </a>
                    <div class="pull-right">
                        <button type="button" class="btn btn-info btn-lg" onclick="window.open('{{ url('/') }}', '_blank')">
                            <i class="fa fa-eye"></i> Pré-visualizar Loja
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@stop

@section('css')
<style>
input[type="color"] {
    height: 45px;
    cursor: pointer;
    border: 2px solid #ddd;
    border-radius: 4px;
}
input[type="color"]:hover {
    border-color: #3c8dbc;
}
.help-block {
    font-family: monospace;
    font-size: 12px;
    color: #999;
}
.box-header .help-block {
    margin-top: 5px;
    margin-bottom: 0;
}
</style>
@stop

@section('js')
<script>
// Atualiza o help-block com o valor selecionado
document.querySelectorAll('input[type="color"]').forEach(input => {
    input.addEventListener('input', function() {
        const helpBlock = this.nextElementSibling;
        if (helpBlock && helpBlock.classList.contains('help-block')) {
            helpBlock.textContent = this.value.toUpperCase();
        }
    });
});
</script>
@stop
