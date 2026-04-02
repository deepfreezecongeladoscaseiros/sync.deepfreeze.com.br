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

        {{-- Informações do produto --}}
        <div class="box-descricao">
            {{-- Nome do produto --}}
            <div class="box-nome-produto">
                <a href="{{ $product->url }}">
                    <h5 class="nome-produto">{{ $product->name }}</h5>
                </a>
            </div>

            {{-- Linha de metadata: estrelas + peso --}}
            @if($showMeta)
                <div class="product-meta">
                    {{-- Estrelas de avaliação --}}
                    @if($starCount > 0)
                        <span class="product-rating">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fa fa-star{{ $i <= $starCount ? '' : '-o' }}"></i>
                            @endfor
                            <span class="rating-count">({{ $reviewCount }})</span>
                        </span>
                    @endif

                    {{-- Separador visual (só se tiver estrelas E peso) --}}
                    @if($starCount > 0 && $showWeight)
                        <span class="meta-sep">&middot;</span>
                    @endif

                    {{-- Peso do produto --}}
                    @if($showWeight)
                        <span class="product-weight">{{ $weightDisplay }}</span>
                    @endif
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
