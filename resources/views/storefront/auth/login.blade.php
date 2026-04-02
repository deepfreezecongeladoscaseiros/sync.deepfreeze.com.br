{{--
    Página de Login de Clientes da Loja

    Dois modos de login alternáveis via jQuery:
    1. Email + Senha (padrão)
    2. CPF + Data de Nascimento (alternativo, sem senha)

    Coluna esquerda: formulário de login com toggle
    Coluna direita: convite para cadastro (novos clientes)
--}}
@extends('layouts.storefront')

@section('title', 'Identificação - ' . config('app.name'))
@section('body_class', 'pg-interna')

@push('styles')
<style>
    .banner-interna {
        background-size: cover;
        background-position: center;
        min-height: 200px;
        display: flex;
        align-items: center;
    }
    .banner-interna .pg-titulo h1 {
        color: #fff;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        margin: 0;
        font-size: 2.5em;
    }

    /* Toggle entre modos de login */
    .login-toggle-link {
        display: inline-block;
        margin-top: 12px;
        font-size: 13px;
        color: var(--primary-color, #013E3B);
        cursor: pointer;
        text-decoration: underline;
        transition: color 0.2s;
    }
    .login-toggle-link:hover {
        color: var(--secondary-color, #FFA733);
    }
    .login-toggle-link i {
        margin-right: 4px;
    }

    /* Esconde modo CPF por padrão */
    .login-mode-cpf { display: none; }
</style>
@endpush

@section('content')

{{-- Banner Interno --}}
<section class="banner-interna" style="background-image: url('{{ asset('storefront/img/ban-interna-1.jpg') }}');">
    <div class="pg-titulo">
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <h1 class="animated fadeIn">Identificação</h1>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Conteúdo Principal --}}
<main class="pg-internas bg-loja">
    <div class="container">
        <div class="box-entrar animated fadeIn">

            <div class="flex no-flex-xs">

                {{-- ========================================== --}}
                {{-- COLUNA ESQUERDA: Formulário de Login --}}
                {{-- ========================================== --}}
                <div class="col-xs-12 col-sm-6">
                    <div class="row">
                        <div class="box-call-login">
                            <div class="col-xs-12">
                                <h1 id="entrar">Entrar</h1>
                            </div>

                            {{-- ====== MODO 1: Email + Senha (padrão) ====== --}}
                            <form id="form-login-email" method="POST" action="{{ route('login') }}" class="form-loja clearfix login-mode-email">
                                @csrf
                                <div class="boxaltura clearfix">
                                    <div class="col-xs-12 col-md-8">
                                        <div class="form-group email">
                                            <label class="sr-only" for="email">E-mail</label>
                                            <input type="email" class="form-control @error('email') invalid @enderror"
                                                   id="email" name="email" value="{{ old('email') }}"
                                                   placeholder="Digite seu e-mail" required autofocus>
                                            @error('email')
                                                <span style="color: #e74c3c; font-size: 12px; margin-top: 5px; display: block;">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-md-8">
                                        <div class="form-group senha">
                                            <label for="password" class="sr-only">Senha</label>
                                            <input type="password" class="form-control @error('password') invalid @enderror"
                                                   id="password" name="password"
                                                   placeholder="Digite sua senha" required>
                                        </div>
                                    </div>
                                    <div class="col-xs-12">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-large w-100 icon-left" id="btn-entrar-email">
                                                <i class="fa fa-sign-in js-icon-loading"></i> Entrar
                                            </button>

                                            {{-- Link para alternar para modo CPF --}}
                                            <span class="login-toggle-link" id="toggle-to-cpf">
                                                <i class="fa fa-id-card"></i> Entrar com CPF + Nascimento
                                            </span>

                                            @if (Route::has('password.request'))
                                                <div style="margin-top: 10px;">
                                                    <a href="{{ route('password.request') }}" class="btn-link text-nowrap" style="font-size: 13px;">
                                                        Esqueci minha senha &raquo;
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </form>

                            {{-- ====== MODO 2: CPF + Data de Nascimento ====== --}}
                            <form id="form-login-cpf" method="POST" action="{{ route('login.cpf') }}" class="form-loja clearfix login-mode-cpf">
                                @csrf
                                <div class="boxaltura clearfix">
                                    <div class="col-xs-12 col-md-8">
                                        <div class="form-group">
                                            <label class="sr-only" for="cpf">CPF</label>
                                            <input type="text" class="form-control @error('cpf') invalid @enderror"
                                                   id="cpf" name="cpf" value="{{ old('cpf') }}"
                                                   placeholder="Digite seu CPF" required>
                                            @error('cpf')
                                                <span style="color: #e74c3c; font-size: 12px; margin-top: 5px; display: block;">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-md-8">
                                        <div class="form-group">
                                            <label for="birth_date_login" class="sr-only">Data de Nascimento</label>
                                            <input type="text" class="form-control @error('birth_date_login') invalid @enderror"
                                                   id="birth_date_login" name="birth_date_login"
                                                   placeholder="Data de nascimento (DD/MM/AAAA)" required>
                                            @error('birth_date_login')
                                                <span style="color: #e74c3c; font-size: 12px; margin-top: 5px; display: block;">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-xs-12">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-large w-100 icon-left" id="btn-entrar-cpf">
                                                <i class="fa fa-id-card js-icon-loading-cpf"></i> Entrar com CPF
                                            </button>

                                            {{-- Link para voltar ao modo email --}}
                                            <span class="login-toggle-link" id="toggle-to-email">
                                                <i class="fa fa-envelope"></i> Voltar para login com E-mail
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            {{-- Mensagem de status (ex: senha resetada com sucesso) --}}
                            @if (session('status'))
                                <div class="col-xs-12">
                                    <div class="alert alert-success" style="margin-top: 15px;">
                                        <i class="fa fa-check-circle"></i> {{ session('status') }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ========================================== --}}
                {{-- COLUNA DIREITA: Convite para Cadastro --}}
                {{-- ========================================== --}}
                <div class="col-xs-12 col-sm-6">
                    <div class="row">
                        <div class="box-call-cadastro form-loja">
                            <div class="col-xs-12">
                                <h1>Ainda não sou cadastrado</h1>
                            </div>
                            <div class="col-xs-12">
                                <p>Já colocou alguns ítens em sua sacola? Não tem problema! Eles continuarão lá. Crie rapidamente sua conta para finalizar seu pedido.</p>
                                <a class="btn btn-large icon-left" href="{{ route('register') }}" role="button">
                                    <i class="fa fa-user"></i> Cadastre-se
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</main>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // === Máscaras de CPF e Data de Nascimento (jQuery Inputmask já carregado) ===
    $('#cpf').inputmask('999.999.999-99');
    $('#birth_date_login').inputmask('99/99/9999');

    // === Toggle entre modo Email e modo CPF ===
    $('#toggle-to-cpf').on('click', function() {
        $('.login-mode-email').fadeOut(200, function() {
            $('.login-mode-cpf').fadeIn(200);
            $('#cpf').focus();
        });
    });

    $('#toggle-to-email').on('click', function() {
        $('.login-mode-cpf').fadeOut(200, function() {
            $('.login-mode-email').fadeIn(200);
            $('#email').focus();
        });
    });

    // === Se veio erro de CPF (validação server-side), mostra modo CPF automaticamente ===
    @if($errors->has('cpf') || $errors->has('birth_date_login'))
        $('.login-mode-email').hide();
        $('.login-mode-cpf').show();
    @endif

    // === Feedback visual ao submeter ===
    $('#form-login-email').on('submit', function() {
        var $btn = $('#btn-entrar-email');
        $btn.prop('disabled', true);
        $btn.find('.js-icon-loading').removeClass('fa-sign-in').addClass('fa-spinner fa-spin');
    });

    $('#form-login-cpf').on('submit', function() {
        var $btn = $('#btn-entrar-cpf');
        $btn.prop('disabled', true);
        $btn.find('.js-icon-loading-cpf').removeClass('fa-id-card').addClass('fa-spinner fa-spin');
    });
});
</script>
@endpush
