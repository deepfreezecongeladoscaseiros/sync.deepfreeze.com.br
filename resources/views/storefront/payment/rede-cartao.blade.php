{{--
    Formulário de Pagamento com Cartão via Rede e-Rede

    Variáveis recebidas do PaymentController@redeCartao:
    - $pedido          : Model Legacy\Pedido — dados do pedido
    - $lojaId          : int  — ID da loja para roteamento das credenciais Rede
    - $formaPagamentoId: int  — ID da forma de pagamento escolhida no checkout
    - $tipo            : string — 'CREDITO' ou 'DEBITO'
    - $valorAPagar     : float — valor final a ser cobrado no cartão
    - $erroRede        : string|null — mensagem de erro retornada pela Rede (query param erro)

    Fluxo:
    1. Cliente preenche dados do cartão
    2. POST para payment.rede.processar
    3. RedePaymentService processa via SDK e-Rede
    4. Redireciona para confirmação (sucesso) ou volta aqui com ?erro=... (falha)
--}}
@extends('layouts.storefront')

@section('title', 'Pagamento com Cartão - ' . config('app.name'))
@section('body_class', 'pg-interna pg-pagamento-cartao')

@push('styles')
<style>
    /* -------------------------------------------------------
       Banner interno — padrão das páginas internas da loja
    ------------------------------------------------------- */
    .banner-interna {
        background-color: var(--color-primary, #013E3B);
        background-size: cover;
        background-position: center;
        min-height: 150px;
        display: flex;
        align-items: center;
    }
    .banner-interna .pg-titulo h1 {
        color: #fff;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        margin: 0;
        font-size: 2.2em;
    }

    /* -------------------------------------------------------
       Área de conteúdo principal
    ------------------------------------------------------- */
    .pg-pagamento-cartao .area-conteudo {
        padding: 40px 0 60px;
    }

    /* -------------------------------------------------------
       Box que exibe o resumo do pedido e valor a pagar
    ------------------------------------------------------- */
    .box-valor-pedido {
        background: #f5f5f5;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 20px 25px;
        text-align: center;
        margin-bottom: 30px;
    }
    .box-valor-pedido .label-pedido {
        font-size: 13px;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 5px;
    }
    .box-valor-pedido .numero-pedido {
        font-size: 16px;
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
    }
    .box-valor-pedido .label-tipo {
        display: inline-block;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 3px 10px;
        border-radius: 20px;
        background: var(--color-primary, #013E3B);
        color: #fff;
        margin-bottom: 12px;
    }
    .box-valor-pedido .valor-total {
        font-size: 2.2em;
        font-weight: 700;
        color: var(--color-primary, #013E3B);
        line-height: 1;
    }

    /* -------------------------------------------------------
       Formulário do cartão — campos com borda arredondada
    ------------------------------------------------------- */
    .form-cartao .form-control {
        border-radius: 30px;
        height: 45px;
        padding: 10px 18px;
        border: 1px solid #d0d0d0;
        font-size: 14px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .form-cartao .form-control:focus {
        border-color: var(--color-primary, #013E3B);
        box-shadow: 0 0 0 3px rgba(1, 62, 59, 0.12);
        outline: none;
    }
    .form-cartao select.form-control {
        /* Selects precisam de padding menor para não cortar o texto */
        padding: 8px 14px;
        -webkit-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%23666' d='M6 8L0 0h12z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 15px center;
        padding-right: 35px;
    }

    /* Campo número do cartão — fonte maior e espaçamento de dígitos */
    .form-cartao #card_number {
        font-size: 18px;
        letter-spacing: 2px;
        font-family: 'Courier New', Courier, monospace;
    }

    /* Campo CVV — centralizado e fonte maior */
    .form-cartao #card_cvv {
        text-align: center;
        font-size: 18px;
        letter-spacing: 3px;
        font-family: 'Courier New', Courier, monospace;
    }

    /* Nome do titular — sempre em maiúsculas visualmente */
    .form-cartao #holder_name {
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* -------------------------------------------------------
       Detecção de bandeira abaixo do campo número do cartão
    ------------------------------------------------------- */
    .brand-detector {
        margin-top: 6px;
        min-height: 22px;
        padding-left: 18px;
    }
    .brand-detector .brand-badge {
        display: inline-block;
        font-size: 12px;
        font-weight: 600;
        padding: 2px 10px;
        border-radius: 20px;
        background: #e8f5e9;
        color: #2e7d32;
        border: 1px solid #c8e6c9;
    }
    .brand-detector .brand-desconhecida {
        font-size: 12px;
        color: #aaa;
    }

    /* -------------------------------------------------------
       Labels do formulário
    ------------------------------------------------------- */
    .form-cartao label {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #555;
        margin-bottom: 6px;
    }

    /* -------------------------------------------------------
       Mensagem de segurança
    ------------------------------------------------------- */
    .msg-seguranca {
        text-align: center;
        font-size: 12px;
        color: #888;
        margin-top: 20px;
    }
    .msg-seguranca .fa {
        color: #4caf50;
        margin-right: 4px;
    }

    /* -------------------------------------------------------
       Botões de ação
    ------------------------------------------------------- */
    .botoes-pagamento {
        margin-top: 25px;
        display: flex;
        gap: 10px;
        justify-content: center;
        flex-wrap: wrap;
    }
    .btn-pagar {
        background-color: var(--color-primary, #013E3B);
        border-color: var(--color-primary, #013E3B);
        color: #fff;
        border-radius: 30px;
        height: 50px;
        padding: 0 35px;
        font-size: 16px;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: background-color 0.2s ease, opacity 0.2s ease;
    }
    .btn-pagar:hover,
    .btn-pagar:focus {
        background-color: var(--color-primary, #013E3B);
        color: #fff;
        opacity: 0.88;
    }
    .btn-pagar:disabled {
        opacity: 0.65;
        cursor: not-allowed;
    }
    .btn-voltar {
        border-radius: 30px;
        height: 50px;
        padding: 0 28px;
        font-size: 15px;
        color: #555;
        border-color: #ccc;
        background: #fff;
        transition: background-color 0.2s ease;
    }
    .btn-voltar:hover {
        background: #f5f5f5;
        color: #333;
    }

    /* -------------------------------------------------------
       Alerta de erro retornado pela Rede
    ------------------------------------------------------- */
    .alerta-erro-rede {
        border-radius: 10px;
        padding: 15px 20px;
        margin-bottom: 25px;
        border: 1px solid #f5c6cb;
        background: #fff5f5;
        color: #721c24;
    }
    .alerta-erro-rede .fa {
        margin-right: 8px;
    }
</style>
@endpush

@section('content')

{{-- ============================================================
     BANNER INTERNO — padrão das páginas internas da loja
     ============================================================ --}}
<section class="banner-interna">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <div class="pg-titulo text-center">
                    <h1>
                        <i class="fa fa-credit-card" style="margin-right:10px; font-size:0.85em;"></i>
                        Pagamento com Cartão
                    </h1>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     CONTEÚDO PRINCIPAL
     ============================================================ --}}
<div class="area-conteudo">
    <div class="container">
        <div class="row">

            {{-- Coluna centralizada — formulário --}}
            <div class="col-xs-12 col-md-8 col-md-offset-2">

                {{-- --------------------------------------------------
                     ALERTA DE ERRO (exibido quando a Rede retorna erro)
                     Populado via query param ?erro=... pelo PaymentController
                     -------------------------------------------------- --}}
                @if($erroRede)
                <div class="alerta-erro-rede" role="alert">
                    <i class="fa fa-exclamation-triangle"></i>
                    <strong>Falha no pagamento:</strong> {{ $erroRede }}
                </div>
                @endif

                {{-- --------------------------------------------------
                     BOX DE RESUMO — número do pedido e valor a cobrar
                     -------------------------------------------------- --}}
                <div class="box-valor-pedido">
                    <div class="label-pedido">Pedido</div>
                    <div class="numero-pedido">#{{ $pedido->id }}</div>

                    {{-- Badge indicando se é débito ou crédito --}}
                    <div>
                        <span class="label-tipo">
                            @if($tipo === 'DEBITO')
                                <i class="fa fa-university" style="margin-right:4px;"></i> Cartão de Débito
                            @else
                                <i class="fa fa-credit-card" style="margin-right:4px;"></i> Cartão de Crédito
                            @endif
                        </span>
                    </div>

                    {{-- Valor formatado em reais com vírgula decimal (padrão pt-BR) --}}
                    <div class="valor-total">
                        R$ {{ number_format($valorAPagar, 2, ',', '.') }}
                    </div>
                </div>

                {{-- --------------------------------------------------
                     FORMULÁRIO DO CARTÃO
                     POST → PaymentController@redeProcessar
                     -------------------------------------------------- --}}
                <form
                    id="form-cartao-rede"
                    class="form-cartao"
                    method="POST"
                    action="{{ route('payment.rede.processar', [
                        'pedidoId'          => $pedido->id,
                        'lojaId'            => $lojaId,
                        'formaPagamentoId'  => $formaPagamentoId,
                    ]) }}"
                    autocomplete="off"
                >
                    @csrf

                    {{-- Campo oculto com o tipo de pagamento (CREDITO/DEBITO)
                         para o controller processar corretamente --}}
                    <input type="hidden" name="tipo" value="{{ $tipo }}">

                    {{-- ---- Número do Cartão ---- --}}
                    <div class="form-group">
                        <label for="card_number">
                            <i class="fa fa-credit-card" style="margin-right:5px;"></i>
                            Número do Cartão
                        </label>
                        <input
                            type="tel"
                            id="card_number"
                            name="card_number"
                            class="form-control"
                            maxlength="19"
                            placeholder="0000 0000 0000 0000"
                            required
                            autocomplete="cc-number"
                        >
                        {{-- Área de detecção de bandeira (preenchida via JS) --}}
                        <div class="brand-detector" id="brand-detector">
                            <span class="brand-desconhecida">Digite o número para identificar a bandeira</span>
                        </div>
                    </div>

                    {{-- ---- Nome do Titular ---- --}}
                    <div class="form-group">
                        <label for="holder_name">
                            <i class="fa fa-user" style="margin-right:5px;"></i>
                            Nome do Titular
                        </label>
                        <input
                            type="text"
                            id="holder_name"
                            name="holder_name"
                            class="form-control"
                            maxlength="50"
                            placeholder="Como impresso no cartão"
                            required
                            autocomplete="cc-name"
                        >
                    </div>

                    {{-- ---- Validade e CVV (linha com 3 colunas) ---- --}}
                    <div class="row">

                        {{-- Mês de validade --}}
                        <div class="col-xs-4 col-sm-3">
                            <div class="form-group">
                                <label for="card_expiration_month">Mês</label>
                                <select
                                    id="card_expiration_month"
                                    name="card_expiration_month"
                                    class="form-control"
                                    required
                                    autocomplete="cc-exp-month"
                                >
                                    <option value="">MM</option>
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">
                                            {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        {{-- Ano de validade: ano atual até +15 anos --}}
                        <div class="col-xs-4 col-sm-3">
                            <div class="form-group">
                                <label for="card_expiration_year">Ano</label>
                                <select
                                    id="card_expiration_year"
                                    name="card_expiration_year"
                                    class="form-control"
                                    required
                                    autocomplete="cc-exp-year"
                                >
                                    <option value="">AAAA</option>
                                    @for($y = date('Y'); $y <= date('Y') + 15; $y++)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        {{-- CVV (3 ou 4 dígitos dependendo da bandeira) --}}
                        <div class="col-xs-4 col-sm-4 col-sm-offset-2">
                            <div class="form-group">
                                <label for="card_cvv">
                                    CVV
                                    <i class="fa fa-question-circle text-muted" title="Código de segurança impresso no verso do cartão"></i>
                                </label>
                                <input
                                    type="tel"
                                    id="card_cvv"
                                    name="card_cvv"
                                    class="form-control"
                                    maxlength="4"
                                    placeholder="000"
                                    required
                                    autocomplete="cc-csc"
                                >
                            </div>
                        </div>

                    </div>{{-- /row validade + cvv --}}

                    {{-- ---- Mensagem de segurança ---- --}}
                    <div class="msg-seguranca">
                        <i class="fa fa-lock"></i>
                        Seus dados são criptografados e processados com segurança pela Rede e-Rede.
                    </div>

                    {{-- ---- Botões de ação ---- --}}
                    <div class="botoes-pagamento">
                        {{-- Voltar ao checkout sem perder o pedido --}}
                        <a href="/checkout" class="btn btn-default btn-voltar">
                            <i class="fa fa-arrow-left" style="margin-right:6px;"></i>
                            Voltar
                        </a>

                        {{-- Botão principal — desabilitado após primeiro clique
                             para evitar dupla submissão e cobrança duplicada --}}
                        <button
                            type="submit"
                            id="btn-pagar"
                            class="btn btn-pagar"
                        >
                            <i class="fa fa-lock" style="margin-right:6px;"></i>
                            Pagar R$ {{ number_format($valorAPagar, 2, ',', '.') }}
                        </button>
                    </div>

                </form>{{-- /form-cartao-rede --}}

            </div>{{-- /col --}}

        </div>{{-- /row --}}
    </div>{{-- /container --}}
</div>{{-- /area-conteudo --}}

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    /* ============================================================
       1. PREVENÇÃO DE DUPLA SUBMISSÃO
       Desabilita o botão e altera o texto no momento do submit,
       evitando que o cliente clique duas vezes e gere duas cobranças.
    ============================================================ */
    var form   = document.getElementById('form-cartao-rede');
    var btnPagar = document.getElementById('btn-pagar');

    form.addEventListener('submit', function (e) {
        // Pequena pausa para garantir que a validação nativa do browser passou
        setTimeout(function () {
            if (form.checkValidity()) {
                btnPagar.disabled = true;
                btnPagar.innerHTML = '<i class="fa fa-spinner fa-spin" style="margin-right:6px;"></i> Processando...';
            }
        }, 10);
    });

    /* ============================================================
       2. FORMATAÇÃO DO NÚMERO DO CARTÃO
       Insere um espaço a cada 4 dígitos conforme o cliente digita
       (ex.: "4111 1111 1111 1111").
       Remove qualquer caractere não numérico antes de formatar.
    ============================================================ */
    var inputNumero = document.getElementById('card_number');

    inputNumero.addEventListener('input', function () {
        // Remove tudo que não for dígito
        var digits = this.value.replace(/\D/g, '');

        // Limita a 16 dígitos (sem espaços)
        digits = digits.substring(0, 16);

        // Agrupa em blocos de 4 separados por espaço
        var formatted = digits.replace(/(\d{4})(?=\d)/g, '$1 ');

        this.value = formatted;

        // Aciona detecção de bandeira sempre que o número mudar
        detectarBandeira(digits);
    });

    /* ============================================================
       3. DETECÇÃO DE BANDEIRA PELO BIN (primeiros dígitos)
       Exibe o nome da bandeira abaixo do campo de número.
       Lógica baseada nos prefixos reconhecidos pelo gateway Rede.
    ============================================================ */
    function detectarBandeira(numeros) {
        var n      = numeros;
        var brand  = '';
        var detector = document.getElementById('brand-detector');

        if (!n) {
            detector.innerHTML = '<span class="brand-desconhecida">Digite o número para identificar a bandeira</span>';
            return;
        }

        // Ordem importa: prefixos mais específicos devem vir antes dos genéricos
        if (/^(636368|438935|504175|451416|636297|5067|4576|4011|506699)/.test(n)) {
            brand = 'Elo';
        } else if (/^(606282|3841)/.test(n)) {
            brand = 'Hipercard';
        } else if (/^(301|305|36|38)/.test(n)) {
            brand = 'Diners';
        } else if (/^(34|37)/.test(n)) {
            brand = 'Amex';
        } else if (/^(6011|622|64|65)/.test(n)) {
            brand = 'Discover';
        } else if (/^35/.test(n)) {
            brand = 'JCB';
        } else if (/^4/.test(n)) {
            brand = 'Visa';
        } else if (/^5/.test(n)) {
            brand = 'Mastercard';
        }

        if (brand) {
            detector.innerHTML = '<span class="brand-badge"><i class="fa fa-check-circle" style="margin-right:4px;"></i>' + brand + '</span>';
        } else {
            detector.innerHTML = '<span class="brand-desconhecida">Bandeira não identificada</span>';
        }
    }

    /* ============================================================
       4. NOME DO TITULAR EM MAIÚSCULAS
       Força o texto para uppercase em tempo real.
       O campo já tem `text-transform: uppercase` no CSS para exibição,
       mas aqui garantimos que o valor enviado ao servidor também seja
       maiúsculo (text-transform é só visual).
    ============================================================ */
    var inputNome = document.getElementById('holder_name');

    inputNome.addEventListener('input', function () {
        var pos = this.selectionStart; // Preserva posição do cursor
        this.value = this.value.toUpperCase();
        this.setSelectionRange(pos, pos);
    });

})();
</script>
@endpush
