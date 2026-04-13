{{--
    Partial: Card de Produto Unificado
    Usado em listagens de categoria e galerias de produtos na home

    Variáveis:
    - $product: App\Models\Product (obrigatório)
    - $columnClass: Classes de coluna Bootstrap (opcional, padrão: 'col-xs-6 col-sm-4 col-lg-3')
    - $showFavorite: Exibir botão de favorito (opcional, padrão: true)
    - $starsMap: Array de estrelas por produto (opcional, carregado pelo controller/galeria)
--}}
@php
    // Configurações do card
    $colClass = $columnClass ?? 'col-xs-6 col-sm-4 col-lg-3';
    $showFav = $showFavorite ?? true;

    // Dados do produto
    $isOnPromotion = $product->isOnPromotion();
    $discountPercentage = $product->getDiscountPercentage();
    $isKit = $product->isKit();
    $isAvailable = $product->isAvailable();

    // Peso: exibe apenas para produtos simples (não pacote/combo) com peso > 0
    $showWeight = !$isKit && $product->weight > 0;
    $weightDisplay = '';
    if ($showWeight) {
        $unit = $product->weight_unit ?: 'g';
        $weightDisplay = $product->weight . $unit;
    }

    // Estrelas: usa mapa pré-carregado (evita N+1) ou busca individual
    $stars = isset($starsMap[$product->id]) ? $starsMap[$product->id] : null;
    $starCount = $stars ? (int) $stars['estrelas'] : 0;
    $reviewCount = $stars ? (int) $stars['total'] : 0;

    // Exibe linha de metadata se tiver peso OU estrelas
    $showMeta = $showWeight || $starCount > 0;
@endphp

<div class="item {{ $colClass }}">
    <div class="box-produto js-box-produto {{ $isOnPromotion ? 'has-promotion' : '' }}" id="prod-{{ $product->id }}">

        {{-- Tag de destaque (promoção, lançamento, etc) --}}
        @if($product->release || $product->hot || ($isOnPromotion && $discountPercentage > 0))
            <div class="img-destaque">
                @if($product->release)
                    <span class="tag-lancamento">Lançamento</span>
                @elseif($product->hot)
                    <span class="tag-destaque">Destaque</span>
                @elseif($isOnPromotion && $discountPercentage > 0)
                    <span class="tag-promo">{{ $discountPercentage }}% OFF</span>
                @endif
            </div>
        @endif

        {{-- Botão favorito (opcional) --}}
        @if($showFav)
            @auth
                <a href="javascript:" class="box-favorito js-toggle-favorite" data-product-id="{{ $product->id }}">
                    <i id="fav-{{ $product->id }}" class="fa fa-heart-o"></i>
                </a>
            @else
                <a href="{{ route('login') }}" class="box-favorito">
                    <i class="fa fa-heart-o"></i>
                </a>
            @endauth
        @endif

        {{-- Imagem do produto (quadrada) --}}
        <a class="img-hover-vitrine img-quadrada" href="{{ $product->url }}">
            <picture>
                <img class="img-responsive img-capa"
                     src="{{ $product->getMainImageUrl() }}"
                     alt="{{ $product->name }}"
                     title="{{ $product->name }}"
                     loading="lazy" />
            </picture>
        </a>

        {{-- Estrelas de avaliação --}}
        @include('storefront.components.product-rating', [
            'starCount' => $starCount,
            'reviewCount' => $reviewCount,
            'size' => 'sm',
        ])

        {{-- Informações do produto --}}
        <div class="box-descricao">
            {{-- Nome do produto --}}
            <div class="box-nome-produto">
                <a href="{{ $product->url }}">
                    <h5 class="nome-produto">{{ $product->name }}</h5>
                </a>
            </div>

            {{-- Peso do produto (entre nome e preço) --}}
            @if($showWeight)
                <div class="product-weight-row">
                    <span class="product-weight">{{ $weightDisplay }}</span>
                </div>
            @endif

            {{-- Preço --}}
            @include('storefront.components.product-price', ['product' => $product, 'size' => 'sm'])
        </div>

        {{-- Comprar --}}
        @include('storefront.components.buy-button', [
            'product' => $product,
            'isKit' => $isKit,
            'isAvailable' => $isAvailable,
            'size' => 'sm',
        ])

    </div>
</div>
