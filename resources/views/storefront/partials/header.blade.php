{{--
    Partial: Cabeçalho Completo da Loja

    Inclui:
    - Busca Full Screen
    - Carrinho Lateral
    - Menu Mobile
    - Top Bar
    - Header com Logo e Navegação
--}}

<section id="buscaFull" class="box-busca-full">
    <div class="group">
        <a href="javascript:" class="btn-fechar js-fechar-busca">
            <i class="fa fa-times"></i><span class="sr-only">Fechar busca</span>
        </a>
        <div class="busca mod-full flex-center">
            <form action="{{ url('/busca') }}" method="get">
                <span class="item-titulo flex-center"><h2>Digite o nome do produto</h2></span>
                <input type="text" class="form-control autocompletar" name="palavra" id="busca">
                <h6 class="hidden-xs">Digite o nome do produto e aperte o <span>Enter</span> ou selecione um produto da lista.</h6>
                <h6 class="visible-xs">Digite o nome do produto e toque em <span>Ir</span> ou selecione um produto da lista.</h6>
            </form>
        </div>
    </div>
</section>

<div id="meuCarrinho" class="overlay-menu overlay-right">
    <a href="javascript:" onclick="closeCarrinhoRight()" class="btn-close"><i class="fa fa-times"></i></a>
    <div class="overlay-content">
        <div id="cesta-topo1">

<!-- alterado variavel do valor da cesta topo, estava uma variavel errado estava a sub_total -->
<div class="icon-topo">
    <a href="{{ url('/carrinho') }}" class="dropdown-toggle">R$ 0,00</a>
    <h4 style="display: none;">Meu Carrinho</h4>
</div>

<form id="form-minha-compra" action="" method="post" autocomplete="off" class="clearfix" name="frm_cesta_topo">
    <ul class="dropdown-menu">

            <li>
                <div class="box-subtotal">
                    <p class="mens">Seu carrinho está vazio.</p>
                </div>
            </li>


    </ul>
</form>        </div>
    </div>
</div>

<div id="menuTopo" class="collapse navbar-collapse">
    <div class="css-mobile">
        {{-- Menu Mobile Dinâmico --}}
        @include('storefront.partials.menu.mobile-menu')
    </div>
</div>

{!! top_bar() !!}

<header class="header">
    <nav class="navbar navbar-default">
        <div class="box-top css-desktop">
            <div class="container">
                <div class="row">
                    <div class="flex">
                        <div class="col-xs-4 col-lg-5">
                            <div class="flex">
                                <div class="rede-social-topo">
                                    {{-- Redes sociais gerenciadas via admin --}}
                                    {!! social_networks() !!}
                                </div>
                                                                <div class="contato-topo">
                                    <p>Delivery de congelados artesanais</p>                                </div>
                                                            </div>
                        </div>
                        <div class="col-xs-8 col-lg-7">
                            <div class="flex-end">
                                <a href="javascript:" onclick="verificaEntregaCep()" class="btn-consultar-entrega js-entrega-open">
                                    <div class="flex">
                                        <i class="fa fa-map-marker"></i>
                                        <div class="js-entrega-msg" style="display:block">
                                            <h5>Entrega na minha região?</h5>
                                        </div>
                                        <div class="js-entrega-msg-ok" style="display:none">
                                            <h6>Entregar em</h6>
                                            <h6><span class="js-entrega-msg-endereco"></span></h6>
                                        </div>
                                    </div>
                                </a>
                                <div class="busca mod-full">
                                    <a href="javascript:" class="btn-abrir js-abrir-busca"><span class="sr-only">Abrir busca</span></a>
                                </div>
                                <div class="carrinho-topo hidden-xs">
                                    <ul class="nav nav-pills">

    <li id="cesta-topo1" class="dropdown valor-compra hidden-xs">
        <div class="box-carrinho-mobile">
            <a href="javascript:" onclick="openCarrinhoRight()" class="btn-open">
                <span class="badge js-cesta-total-produtos-notext">
                    0                </span>
            </a>
        </div>
    </li>

            <li class="cadastre-se"><a href="{{ route('register') }}"><i class="icon-perfil fa fa-user-o"></i>Cadastre-se</a></li>
        <li class="entrar"><a href="{{ route('login') }}"><i class="icon-entrar fa fa-share"></i>Login</a></li>

</ul>                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="js-fixed">
            <div class="container">
                <div class="row">
                    <div class="flex no-flex-sm header-center">
                        <div class="col-xs-12 col-md-3">
                            <div class="navbar-header">
                                <div class="flex-space">
                                    <div class="logo">
                                        <a href="{{ url("/") }}">
                                            <img class="img-responsive" src="{{ theme_logo() }}" alt="{{ theme_logo_alt() }}" title="{{ theme_logo_alt() }}">
                                        </a>
                                    </div>
                                    <div class="box-right css-mobile">
                                        <a href="javascript:" onclick="verificaEntregaCep()" class="btn-consultar-entrega js-entrega-open" >
                                            <div class="flex">
                                                <i class="fa fa-map-marker"></i>
                                                <div class="js-entrega-msg" style="display:block">
                                                    <h5>Entrega na minha região?</h5>
                                                </div>
                                                <div class="js-entrega-msg-ok" style="display:none">
                                                    <h6>Entregar em</h6>
                                                    <h6><span class="js-entrega-msg-endereco"></span></h6>
                                                </div>
                                            </div>
                                        </a>
                                        <div class="flex-end">
                                            <a href="javascript:" class="item-busca btn-abrir js-abrir-busca"><span class="sr-only">Busca</span></a>
                                                                                            <a href="{{ route('login') }}" class="item-login"><span class="sr-only">Entrar</span></a>
                                                                                        <div class="box-carrinho-mobile">
                                                <a href="javascript:" onclick="openCarrinhoRight()" class="btn-open">
                                                    <span class="badge js-cesta-total-produtos-notext">
                                                        0                                                    </span>
                                                </a>
                                            </div>
                                            <button type="button" class="navbar-toggle" data-toggle="no-collapse" data-target="#menu-topo">
                                                <span class="icon-bar"></span>
                                                <span class="icon-bar"></span>
                                                <span class="icon-bar"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-9 col-menu-full">
                            {{-- Menu Principal Dinâmico --}}
                            @include('storefront.partials.menu.desktop-menu')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>
