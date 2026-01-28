{{--
    Página: Home da Loja Virtual

    Exibe a página inicial com:
    - Banners rotativos
    - Galerias de produtos
    - Blocos de recursos (feature blocks)

    Layout: storefront (header, footer, scripts)
--}}
@extends('layouts.storefront')

@section('title', config('app.name') . ' - Delivery de congelados artesanais')

@section('meta_description', 'Delivery de congelados artesanais. Praticidade e sabor na sua rotina.')

@section('body_class', 'pg-home')

@section('content')
    {{--
        Sistema de Blocos Flexíveis da Home Page
        ----------------------------------------
        Permite montar a home com blocos intercalados (galerias, banners, etc.)
        em qualquer ordem desejada.

        Gerenciamento: /admin/home-blocks

        Tipos disponíveis:
        - hero_banners: Banner principal (carrossel)
        - feature_blocks: Blocos de informações (régua)
        - product_gallery: Galeria de produtos (individual)
        - dual_banner: Banner duplo (individual)
        - info_block: Bloco de informação (individual)
        - step_blocks: Blocos de passos
        - single_banner: Banner único (individual)
    --}}
    {!! home_blocks() !!}

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
