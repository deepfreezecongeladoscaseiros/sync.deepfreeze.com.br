<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->getSeoTitle() }}</title>

    @if($page->meta_description)
        <meta name="description" content="{{ $page->meta_description }}">
    @endif

    @if($page->meta_keywords)
        <meta name="keywords" content="{{ $page->meta_keywords }}">
    @endif

    {{-- CSS --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.7.2/animate.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.7/css/jquery.fancybox.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-sweetalert/0.4.5/sweet-alert.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" rel="stylesheet">
    <link href="https://naturallisas.com.br/assets/css/icon-fonts-octoshop.min.css" rel="stylesheet">
    <link href="https://naturallisas.com.br/assets/css/style-extend.min.css?v=6921ea036ab4e" rel="stylesheet">
    <link href="https://naturallisas.com.br/lojas/naturallis/theme/assets/css/style.min.css" rel="stylesheet">

    {{-- Bootstrap Icons (Linear style) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    {{-- Mapeamento de ícones FA para Bootstrap Icons --}}
    <link href="{{ asset('storefront/css/icons-mapping.css') }}" rel="stylesheet">
    {{-- CSS Dinâmico do Tema (variáveis de cores) --}}
    <link href="{{ theme_css_url() }}" rel="stylesheet">
    {{-- Override de estilos com as cores do tema --}}
    <link href="{{ asset('storefront/css/theme-override.css') }}" rel="stylesheet">
    {{-- CSS customizado para logos quadradas --}}
    <link href="{{ asset('storefront/css/logo-custom.css') }}" rel="stylesheet">
    {{-- CSS customizado para Top Bar (Barra de Anúncios) --}}
    <link href="{{ asset('storefront/css/top-bar-custom.css') }}" rel="stylesheet">
    {{-- CSS customizado para Feature Blocks (Blocos de Informações) --}}
    <link href="{{ asset('storefront/css/feature-blocks-custom.css') }}" rel="stylesheet">

    {{-- Fontes --}}
    <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="pg-institucional">

{{-- Cabeçalho Completo (inclui top bar, menu mobile, busca, carrinho) --}}
@include('storefront.partials.header')

{{-- Breadcrumb --}}
<div class="box-breadcrumb">
    <div class="container">
        <ol class="breadcrumb">
            <li><a href="/">Home</a></li>
            <li class="active">{{ $page->title }}</li>
        </ol>
    </div>
</div>

{{-- Conteúdo da Página --}}
<section class="page-content" style="padding: 40px 0; min-height: 400px;">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 style="margin-bottom: 30px; color: var(--color-primary);">{{ $page->title }}</h1>

                <div class="page-body" style="line-height: 1.8; font-size: 15px;">
                    {!! $page->content !!}
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Rodapé Completo (inclui logo, formas de pagamento, menu institucional, contato, cookie consent) --}}
@include('storefront.partials.footer')

{{-- JavaScript --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.2.1/owl.carousel.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.imagesloaded/4.1.4/imagesloaded.pkgd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.matchHeight/0.7.2/jquery.matchHeight-min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/3.3.4/jquery.inputmask.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/elevatezoom/3.0.8/jquery.elevatezoom.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.7/js/jquery.fancybox.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-sweetalert/0.4.5/sweet-alert.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.2/bootstrap3-typeahead.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/wow/1.1.2/wow.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.quicksearch/2.4.0/jquery.quicksearch.min.js"></script>
<script src="https://naturallisas.com.br/assets/js/vendor/ui.datepicker-pt-BR.min.js"></script>
<script src="https://naturallisas.com.br/assets/js/loja-validations.min.js?v=6921ea0372482"></script>
<script src="https://naturallisas.com.br/assets/js/loja-function.min.js?v=6921ea0372484"></script>
<script src="https://naturallisas.com.br/assets/js/loja-script.min.js?v=6921ea0372485"></script>
<script src="https://naturallisas.com.br/assets/js/facebook-login.php?id="></script>
<script src="https://naturallisas.com.br/lojas/naturallis/theme/assets/js/script-js.php?v=6921ea0372487"></script>
<script src="https://naturallisas.com.br/assets/js/google-login.php?id=&ggconnect=" ></script>
<script src="https://accounts.google.com/gsi/client"></script>
<script src="https://unpkg.com/jwt-decode/build/jwt-decode.js"></script>
<script src="https://naturallisas.com.br/lojas/naturallis/conteudo/estatico/tags-textos.min.js?v=6921ea0372494"></script>

</body>
</html>
