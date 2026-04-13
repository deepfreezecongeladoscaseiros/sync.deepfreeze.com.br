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

        {{-- Estrelas de avaliação (entre imagem e nome) --}}
        @if($starCount > 0)
            <div class="product-rating-row">
                @for($i = 1; $i <= 5; $i++)
                    <svg class="rating-star {{ $i <= $starCount ? 'filled' : 'empty' }}" viewBox="0 0 24 24" width="18" height="18">
                        <path d="M12 2.5c.4 0 .7.2.9.5l2.5 5 5.5.8c.5.1.8.5.8 1 0 .2-.1.5-.3.7l-4 3.9.9 5.5c.1.5-.1.9-.5 1.1-.2.1-.4.2-.6.2-.2 0-.3 0-.5-.1L12 18.3l-4.9 2.6c-.4.2-.9.2-1.3-.1-.3-.2-.5-.6-.4-1.1l.9-5.5-4-3.9c-.3-.3-.4-.8-.2-1.2.2-.4.5-.6.9-.7l5.5-.8 2.5-5c.2-.3.6-.5 1-.5z"/>
                    </svg>
                @endfor
                <span class="rating-count">({{ $reviewCount }})</span>
            </div>
        @endif

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

            {{-- Preços --}}
            <div class="box-precos">
                @if($isOnPromotion)
                    {{-- Preço original riscado --}}
                    <div class="preco-original">
                        de <span>{{ $product->formatted_original_price }}</span>
                    </div>
                    {{-- Preço promocional --}}
                    <div class="preco-atual preco-promo">
                        {{ $product->formatted_price }}
                    </div>
                @else
                    {{-- Preço normal --}}
                    <div class="preco-atual">
                        {{ $product->formatted_price }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Área de compra: Quantidade + Botão --}}
        @if($isAvailable)
            @if($isKit)
                {{-- Para kits: apenas link para ver detalhes --}}
                <a href="{{ $product->url }}" class="adicionar btn-store txt-adicionar">
                    <div class="box-adicionar">
                        <span>Ver Kit</span>
                    </div>
                </a>
            @else
                {{-- Para produtos normais: quantidade + comprar --}}
                <div class="box-comprar-inline">
                    {{-- Seletor de quantidade --}}
                    @include('storefront.components.qty-selector', [
                        'productId' => $product->id,
                        'size' => 'sm',
                    ])

                    {{-- Botão comprar --}}
                    <a href="javascript:"
                       class="btn-comprar-inline js-add-to-cart"
                       data-product-id="{{ $product->id }}">
                        <span>Comprar</span>
                    </a>
                </div>
            @endif
        @else
            <a href="{{ $product->url }}" class="adicionar btn-store txt-adicionar indisponivel">
                <div class="box-adicionar">
                    <span>Indisponível</span>
                </div>
            </a>
        @endif

    </div>
</div>
