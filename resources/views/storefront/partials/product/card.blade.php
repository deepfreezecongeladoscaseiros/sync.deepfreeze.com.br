{{--
    Partial: Card de Produto Unificado
    Usado em listagens de categoria e galerias de produtos na home

    Variáveis:
    - $product: App\Models\Product (obrigatório)
    - $columnClass: Classes de coluna Bootstrap (opcional, padrão: 'col-xs-6 col-sm-4 col-lg-3')
    - $showFavorite: Exibir botão de favorito (opcional, padrão: true)
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

        {{-- Nome do produto --}}
        <div class="box-descricao">
            <div class="box-nome-produto">
                <a href="{{ $product->url }}">
                    <h5 class="nome-produto">{{ $product->name }}</h5>
                </a>
            </div>

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
                    <div class="quantidade-inline">
                        <button type="button" class="btn-qtd js-btn-minus" data-product-id="{{ $product->id }}">
                            <i class="fa fa-minus"></i>
                        </button>
                        <input type="text"
                               class="qtd-input js-qtd-input"
                               id="qtd-{{ $product->id }}"
                               value="1"
                               readonly
                               data-product-id="{{ $product->id }}">
                        <button type="button" class="btn-qtd js-btn-plus" data-product-id="{{ $product->id }}">
                            <i class="fa fa-plus"></i>
                        </button>
                    </div>

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
