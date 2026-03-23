{{--
    Página de Login de Clientes da Loja
    Usa o layout storefront para herdar header, footer, CSS e JS base.
    Estrutura baseada no template de referência (Naturallis):
    - Coluna esquerda: formulário de login
    - Coluna direita: convite para cadastro
    Classes do tema: box-entrar, box-call-login, box-call-cadastro, bg-loja
--}}
@extends('layouts.storefront')

@section('title', 'Identificação - ' . config('app.name'))
@section('body_class', 'pg-interna')

@push('styles')
<style>
    /*
     * Estilos mínimos — a maioria vem do tema (style.css):
     * - .box-entrar: display:inline-block, width:100%, padding:10px 0
     * - .box-call-login: background-color:#F8FCF5, max-width:450px, padding:30px, border-radius:20px
     * - .box-call-cadastro: padding:30px, margin-top:5%
     * - .box-entrar h1: color:#013E3B, font-size:2.143em, font-weight:700
     * - .form-control: border-radius:30px, height:45px
     * Aqui definimos APENAS o banner-interna (mesmo padrão da página de contato)
     */
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

                            <form id="form-login" method="POST" action="{{ route('login') }}" class="form-loja clearfix">
                                @csrf

                                <div class="boxaltura clearfix">
                                    {{-- E-mail --}}
                                    <div class="col-xs-12 col-md-8">
                                        <div class="form-group email">
                                            <label class="sr-only" for="email">E-mail <span>*</span></label>
                                            <input type="email" class="form-control @error('email') invalid @enderror"
                                                   id="email" name="email" value="{{ old('email') }}"
                                                   placeholder="Digite seu e-mail" required autofocus>
                                            @error('email')
                                                <span style="color: #e74c3c; font-size: 12px; margin-top: 5px; display: block;">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- Senha --}}
                                    <div class="col-xs-12 col-md-8">
                                        <div class="form-group senha">
                                            <label for="password" class="sr-only">Senha <span>*</span></label>
                                            <input type="password" class="form-control @error('password') invalid @enderror"
                                                   id="password" name="password"
                                                   placeholder="Digite sua senha" required>
                                        </div>
                                    </div>

                                    {{-- Botões e links --}}
                                    <div class="col-xs-12">
                                        <div class="form-group">
                                            <div class="flex">
                                                {{-- Botão Entrar --}}
                                                <button type="submit" class="btn btn-large w-100 icon-left" id="btn-entrar">
                                                    <i class="fa fa-sign-in js-icon-loading"></i> Entrar
                                                </button>

                                                {{-- Link mobile para cadastro --}}
                                                <div class="visible-xs">
                                                    <a class="btn btn-2 btn-large icon-left w-100" href="{{ route('register') }}" role="button">
                                                        <i class="fa fa-user"></i> Ou faça seu cadastro
                                                    </a>
                                                </div>

                                                {{-- Link "Esqueci minha senha" --}}
                                                @if (Route::has('password.request'))
                                                    <a href="{{ route('password.request') }}" class="btn-link text-nowrap">
                                                        Esqueci minha senha &raquo;
                                                    </a>
                                                @endif
                                            </div>
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
    // Feedback visual ao submeter o formulário de login
    $('#form-login').on('submit', function() {
        var $btn = $('#btn-entrar');
        $btn.prop('disabled', true);
        $btn.find('.js-icon-loading').removeClass('fa-sign-in').addClass('fa-spinner fa-spin');
    });
});
</script>
@endpush
