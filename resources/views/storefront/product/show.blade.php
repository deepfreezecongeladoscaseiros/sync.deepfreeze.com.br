{{--
    View: Página de Detalhes do Produto

    Exibe todas as informações do produto:
    - Galeria de imagens
    - Nome, código, descrição
    - Preço e botão comprar
    - Informações nutricionais
    - Ingredientes e alérgenos
    - Modo de preparo
    - Produtos relacionados

    Variáveis:
    - $product: Product model
    - $category: Category model
    - $relatedProducts: Collection de produtos relacionados
    - $breadcrumb: Array com navegação
--}}

@extends('layouts.storefront')

@section('title', $product->name . ' | ' . config('app.name'))
@section('meta_title', $product->name)
@section('meta_description', Str::limit(strip_tags($product->presentation ?? $product->description), 160))

@section('og_title', $product->name)
@section('og_description', Str::limit(strip_tags($product->presentation ?? $product->description), 160))
@section('og_image', $product->getMainImageUrl('large'))

@section('body_class', 'pg-produto-interno')

@section('content')

    {{-- Banner internas - ESTRUTURA EXATA DO ORIGINAL --}}
    <section class="banner-interna" style="background-image: url('{{ asset('storefront/img/ban-interna-1.jpg') }}');">
        <div class="pg-titulo">
            <div class="container">
                <div class="row">
                    <div class="col-xs-12">
                        <h2 class="animated fadeIn">{{ $category->name ?? 'Produto' }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Breadcrumbs - ESTRUTURA EXATA DO ORIGINAL --}}
    <section class="box-breadcrumb">
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            @foreach($breadcrumb as $item)
                                @if($item['url'])
                                    <li class="breadcrumb-item">
                                        <a href="{{ $item['url'] }}">{{ $item['title'] }}</a>
                                    </li>
                                @else
                                    <li class="breadcrumb-item active" aria-current="page">
                                        {{ $item['title'] }}
                                    </li>
                                @endif
                            @endforeach
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    {{-- Content - ESTRUTURA EXATA DO ORIGINAL --}}
    <main class="pg-internas">
        <div class="container">
            <div class="row">

                {{-- Sidebar Left --}}
                <div class="col-xs-12 col-md-3 css-desktop">
                    @include('storefront.partials.sidebar-categories')
                </div>

                {{-- Detalhe Produto --}}
                <div class="col-xs-12 col-md-9 animated fadeIn">
                    <div class="row">

                        {{-- Voltar --}}
                        <div class="col-xs-12">
                            <a href="{{ $category ? route('category.show', $category->slug) : url('/') }}" class="btn-voltar icon-left">
                                <i class="fa fa-arrow-left"></i>Voltar
                            </a>
                        </div>

                        <div class="box-detalhe-produto">

                            {{-- Galeria de Fotos --}}
                            @include('storefront.product.partials.gallery')

                            {{-- Informações do Produto --}}
                            @include('storefront.product.partials.info')

                            {{-- Descrição do Produto --}}
                            <div class="col-xs-12">
                                <article class="conteudo">

                                    {{-- Tabela Nutricional --}}
                                    @include('storefront.product.partials.nutritional-info')

                                    {{-- Ingredientes e Alérgenos --}}
                                    @include('storefront.product.partials.ingredients')

                                    {{-- Modo de Preparo --}}
                                    @include('storefront.product.partials.preparation')

                                    {{-- Dicas do Chef --}}
                                    @if($product->chef_tips)
                                        @include('storefront.product.partials.chef-tips')
                                    @endif

                                    {{-- Benefícios --}}
                                    @if($product->benefits)
                                        @include('storefront.product.partials.benefits')
                                    @endif

                                </article>
                            </div>

                        </div>
                    </div>

                    {{-- Produtos Relacionados --}}
                    @if($relatedProducts->count() > 0)
                        @include('storefront.product.partials.related-products')
                    @endif

                </div>

            </div>
        </div>
    </main>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Galeria de imagens - troca imagem principal ao clicar na miniatura
    $('.thumb-item').on('click', function(e) {
        e.preventDefault();
        var newSrc = $(this).data('image');
        var newZoom = $(this).data('zoom');

        $('#galeriaProdutos').attr('src', newSrc);
        if (newZoom) {
            $('#galeriaProdutos').attr('data-zoom-image', newZoom);
        }

        $('.thumb-item').removeClass('active');
        $(this).addClass('active');
    });

    // Seletor de quantidade
    $('.js-btn-minus-detail').on('click', function(e) {
        e.preventDefault();
        var $input = $(this).siblings('input.qtd');
        var currentQty = parseInt($input.val()) || 1;
        if (currentQty > 1) {
            $input.val(currentQty - 1);
        }
    });

    $('.js-btn-plus-detail').on('click', function(e) {
        e.preventDefault();
        var $input = $(this).siblings('input.qtd');
        var currentQty = parseInt($input.val()) || 1;
        if (currentQty < 99) {
            $input.val(currentQty + 1);
        }
    });
});
</script>
@endpush
