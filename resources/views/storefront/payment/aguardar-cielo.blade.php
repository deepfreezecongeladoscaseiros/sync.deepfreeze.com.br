{{--
    Página de Aguardar Pagamento Cielo — Polling

    Exibe uma tela de espera enquanto consulta o status do pagamento Cielo
    via polling AJAX a cada 15 segundos, replicando o comportamento do legado.

    Variáveis esperadas do controller:
      - $pedidoId  (int)   — ID do pedido no banco legado
      - $tentativa (int)   — Tentativa atual de polling (para uso futuro)
      - $pedido    (Pedido) — Model Pedido com valor_total disponível

    Rota de polling: GET /pagamento/status-cielo/{pedidoId}
    Resposta JSON esperada: { paid: bool, status: int, sessao: string, redirect_url: string|null }

    Fluxo de status:
      - paid == true && redirect_url  → redireciona para redirect_url (confirmação)
      - status == 3                   → pagamento negado, volta ao checkout com erro
      - status == 4                   → pagamento expirado, volta ao checkout com erro
      - status == 5                   → pagamento cancelado, volta ao checkout com erro
      - demais                        → continua polling
--}}
@extends('layouts.storefront')

@section('title', 'Processando pagamento')
@section('body_class', 'pg-interna pg-aguardar-pagamento')

@push('styles')
<style>
    /* =============================================
       Banner interno — mesma classe usada em checkout
       ============================================= */
    .banner-interna {
        background-size: cover;
        background-position: center;
        min-height: 200px;
        display: flex;
        align-items: center;
    }
    .banner-interna.small {
        min-height: 150px;
    }
    .banner-interna .pg-titulo h1 {
        color: #fff;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        margin: 0;
        font-size: 2.5em;
    }

    /* =============================================
       Container central de aguardo
       ============================================= */
    .box-aguardar-pagamento {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        margin-top: 40px;
        margin-bottom: 40px;
    }

    /* Spinner com cor primária do tema */
    .box-aguardar-pagamento .spinner-icone {
        color: var(--color-primary, #013E3B);
        margin-bottom: 30px;
    }

    /* Título de aguardo */
    .box-aguardar-pagamento h3 {
        color: var(--color-primary, #013E3B);
        margin-bottom: 15px;
        font-weight: 600;
    }

    /* Textos auxiliares */
    .box-aguardar-pagamento .texto-principal {
        color: #666;
        font-size: 16px;
        margin-bottom: 10px;
    }
    .box-aguardar-pagamento .texto-aviso {
        color: #999;
        font-size: 14px;
    }

    /* Caixa de resumo do pedido */
    .box-info-pedido {
        margin-top: 30px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
    }
    .box-info-pedido p {
        margin: 0;
        font-size: 14px;
        color: #666;
    }
</style>
@endpush

@section('content')

{{-- Banner interno — mesmo padrão das demais páginas internas (checkout, cadastro, etc.) --}}
<section class="banner-interna small" style="background-image: url('{{ asset('storefront/img/ban-interna-1.jpg') }}');">
    <div class="pg-titulo">
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <h1 class="animated fadeIn">Processando Pagamento</h1>
                </div>
            </div>
        </div>
    </div>
</section>

<main class="pg-internas form-cadastro-new">
    <div class="container">
        <div class="row">
            {{-- Coluna centralizada: 8/12 em md, 6/12 em lg --}}
            <div class="col-xs-12 col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">

                <div class="box-aguardar-pagamento text-center" style="padding: 60px 20px;">

                    {{-- Ícone animado de carregamento --}}
                    <div class="spinner-icone">
                        <i class="fa fa-spinner fa-spin fa-3x"></i>
                    </div>

                    {{-- Título principal --}}
                    <h3>Aguardando confirmação de pagamento</h3>

                    {{-- Explicação para o cliente --}}
                    <p class="texto-principal">
                        Estamos verificando seu pagamento junto à operadora.
                    </p>
                    <p class="texto-aviso">
                        Esta página atualiza automaticamente. Não feche o navegador.
                    </p>

                    {{-- Resumo do pedido --}}
                    <div class="box-info-pedido">
                        <p>
                            Pedido <strong>#{{ $pedidoId }}</strong>
                            @if($pedido && $pedido->valor_total)
                                &mdash; {{ 'R$ ' . number_format($pedido->valor_total, 2, ',', '.') }}
                            @endif
                        </p>
                    </div>

                    {{-- Bloco de erro: oculto por padrão, exibido via JS quando o pagamento é recusado/expirado --}}
                    <div id="status-erro" style="display: none; margin-top: 20px;" class="alert alert-danger">
                        <i class="fa fa-exclamation-circle"></i>
                        <span id="status-erro-msg"></span>
                    </div>

                </div>{{-- /.box-aguardar-pagamento --}}

            </div>{{-- /.col --}}
        </div>{{-- /.row --}}
    </div>{{-- /.container --}}
</main>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    // =========================================================
    // Configurações do polling — replicam comportamento do legado
    // =========================================================
    var PEDIDO_ID      = {{ (int) $pedidoId }};  // ID do pedido no banco legado
    var INTERVALO_MS   = 15000;                   // 15 segundos entre cada consulta (igual ao legado)
    var MAX_TENTATIVAS = 40;                      // 40 × 15s = 10 minutos de espera máxima
    var tentativa      = 0;                       // Contador de tentativas realizadas
    var timeoutId      = null;                    // Referência ao setTimeout ativo (permite cancelar)

    // URL da rota de polling (definida no routes/web.php)
    var urlStatus = '/pagamento/status-cielo/' + PEDIDO_ID;

    // =========================================================
    // Exibe mensagem de erro abaixo do card de aguardo
    // =========================================================
    function mostrarErro(mensagem) {
        var divErro  = document.getElementById('status-erro');
        var spanMsg  = document.getElementById('status-erro-msg');

        if (divErro && spanMsg) {
            spanMsg.textContent = mensagem;
            divErro.style.display = 'block';
        }
    }

    // =========================================================
    // Redireciona com parâmetro de erro na URL do checkout
    // O checkout lê o parâmetro ?erro= e exibe alerta ao cliente
    // =========================================================
    function redirecionarCheckoutComErro(motivo) {
        window.location.href = '/checkout?erro=' + encodeURIComponent(motivo);
    }

    // =========================================================
    // Consulta o status do pagamento via AJAX
    // =========================================================
    function consultarStatus() {
        tentativa++;

        // Esgotou o limite de tentativas: exibe erro genérico
        if (tentativa > MAX_TENTATIVAS) {
            mostrarErro('O tempo limite de verificação foi atingido. Por favor, entre em contato com o suporte informando o pedido #' + PEDIDO_ID + '.');
            return;
        }

        // Realiza a requisição GET à rota de polling
        var xhr = new XMLHttpRequest();
        xhr.open('GET', urlStatus, true);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.onreadystatechange = function () {
            if (xhr.readyState !== 4) {
                return; // Aguarda a resposta completa
            }

            // ------------------------------------------------
            // Erro de rede ou servidor (5xx, timeout, etc.)
            // Reexecuta o polling após o intervalo normal
            // ------------------------------------------------
            if (xhr.status === 0 || xhr.status >= 500) {
                agendarProximaConsulta();
                return;
            }

            // ------------------------------------------------
            // Resposta HTTP inesperada (4xx, etc.)
            // Exibe erro ao cliente e encerra o polling
            // ------------------------------------------------
            if (xhr.status !== 200) {
                mostrarErro('Erro ao verificar o pagamento (HTTP ' + xhr.status + '). Tente recarregar a página.');
                return;
            }

            // ------------------------------------------------
            // Parse do JSON de resposta
            // Estrutura esperada:
            //   { paid: bool, status: int, sessao: string, redirect_url: string|null }
            // ------------------------------------------------
            var dados;
            try {
                dados = JSON.parse(xhr.responseText);
            } catch (e) {
                // JSON inválido: agenda nova tentativa sem expor erro ao cliente
                agendarProximaConsulta();
                return;
            }

            // ------------------------------------------------
            // Pagamento aprovado — redireciona para a confirmação
            // ------------------------------------------------
            if (dados.paid && dados.redirect_url) {
                window.location.href = dados.redirect_url;
                return;
            }

            // ------------------------------------------------
            // Tratamento dos status negativos da Cielo:
            //   3 = Negado   → volta ao checkout com motivo
            //   4 = Expirado → volta ao checkout com motivo
            //   5 = Cancelado→ volta ao checkout com motivo
            // ------------------------------------------------
            var status = parseInt(dados.status, 10);

            if (status === 3) {
                redirecionarCheckoutComErro('Pagamento negado pela operadora. Por favor, verifique os dados do cartão ou tente outra forma de pagamento.');
                return;
            }

            if (status === 4) {
                redirecionarCheckoutComErro('O tempo para pagamento expirou. Por favor, realize o pedido novamente.');
                return;
            }

            if (status === 5) {
                redirecionarCheckoutComErro('Pagamento cancelado. Por favor, realize o pedido novamente ou entre em contato com o suporte.');
                return;
            }

            // ------------------------------------------------
            // Demais status (pendente, em processamento, etc.)
            // Continua o polling normalmente
            // ------------------------------------------------
            agendarProximaConsulta();
        };

        xhr.send();
    }

    // =========================================================
    // Agenda a próxima consulta após INTERVALO_MS
    // Usa setTimeout (não setInterval) para evitar sobreposição
    // de requisições caso o servidor demore a responder —
    // mesma abordagem do código legado
    // =========================================================
    function agendarProximaConsulta() {
        timeoutId = setTimeout(consultarStatus, INTERVALO_MS);
    }

    // =========================================================
    // Inicialização: primeira consulta imediata após 3 segundos
    // para dar tempo da transação ser registrada no gateway
    // =========================================================
    timeoutId = setTimeout(consultarStatus, 3000);

})();
</script>
@endpush
