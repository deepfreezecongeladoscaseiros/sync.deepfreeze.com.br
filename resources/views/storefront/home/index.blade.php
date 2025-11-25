{{--
    Página: Home da Loja Virtual

    Exibe a página inicial com:
    - Banners rotativos
    - Galerias de produtos
    - Blocos de recursos (feature blocks)

    Layout: storefront (header, footer, scripts)
--}}
@extends('layouts.storefront')

@section('title', config('app.name') . ' - Delivery de marmitas saudáveis ultracongeladas')

@section('meta_description', 'Delivery de marmitas saudáveis ultracongeladas. Praticidade e saúde na sua rotina.')

@section('body_class', 'pg-home')

@section('content')
    {{-- Banners Hero (Principal) --}}
    {!! hero_banners() !!}

    {{-- Galerias de Produtos --}}
    {!! product_galleries() !!}

    {{-- Blocos de Recursos (Feature Blocks) --}}
    {!! feature_blocks() !!}

    {{-- Banners Duplos --}}
    {!! dual_banners() !!}

    {{-- Blocos de Passos --}}
    {!! step_blocks() !!}

    {{-- Banners Simples --}}
    {!! single_banners() !!}

    {{-- Blocos de Informação --}}
    {!! info_blocks() !!}

    {{-- Consentimento de Cookies (LGPD) --}}
    {!! cookie_consent() !!}
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Inicializa carousel dos banners hero
    $('.js-banner-principal').owlCarousel({
        loop: true,
        items: 1,
        margin: 0,
        nav: true,
        navText: ['<i class="fa fa-chevron-left"></i>', '<i class="fa fa-chevron-right"></i>'],
        dots: true,
        autoplay: true,
        autoplayTimeout: 5000,
        autoplayHoverPause: true
    });

    // Inicializa carousels de produtos nas galerias
    $('.js-carousel, .carrossel-produtos').owlCarousel({
        loop: false,
        margin: 15,
        nav: true,
        navText: ['<i class="fa fa-chevron-left"></i>', '<i class="fa fa-chevron-right"></i>'],
        dots: false,
        responsive: {
            0: { items: 2 },
            576: { items: 2 },
            768: { items: 3 },
            992: { items: 4 },
            1200: { items: 4 }
        }
    });

    // Iguala altura dos cards de produto
    $('.js-h-card').matchHeight();
});
</script>
@endpush
