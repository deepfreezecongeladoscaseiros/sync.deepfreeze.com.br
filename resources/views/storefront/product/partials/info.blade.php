{{--
    Partial: Informações Principais do Produto

    Exibe nome, código, descrição, preço e botão comprar.

    Variáveis:
    - $product: Product model
--}}

<div class="col-xs-12 col-sm-6 col-md-6 col-lg-5 box-desc box-produto-interno">

    {{-- Nome do Produto --}}
    <h1 class="nome-produto">{{ $product->name }}</h1>

    {{-- Código e Calorias --}}
    <p class="info">
        Cód:
        <span class="produto-codigo">{{ $product->sku }}</span>
    </p>
    @if($product->nutritionalInfo && $product->nutritionalInfo->energy_kcal)
        <p class="info">|</p>
        <p class="info">
            <span class="kcal">{{ number_format($product->nutritionalInfo->energy_kcal, 0, ',', '.') }}</span> calorias
        </p>
    @endif

    {{-- Peso --}}
    @if($product->weight)
        <p class="info">|</p>
        <p class="info">
            <span class="peso">{{ number_format($product->weight * 1000, 0, ',', '.') }}g</span>
        </p>
    @endif

    {{-- Descrição / Apresentação --}}
    @if($product->presentation)
        <div class="desc">
            {!! nl2br(e($product->presentation)) !!}
        </div>
    @elseif($product->description)
        <div class="desc">
            {!! $product->description !!}
        </div>
    @endif

    {{-- Seletor de Quantidade e Preço --}}
    <div class="escolher">
        <div class="quantidade">
            <div class="box-number">
                <div class="add-qtd js-btn-minus-detail">
                    <i class="fa fa-minus"><span>-</span></i>
                </div>
                <input name="quantidade"
                       type="text"
                       class="qtd"
                       value="1"
                       id="qtd-{{ $product->id }}"
                       readonly />
                <div class="add-qtd js-btn-plus-detail">
                    <i class="fa fa-plus"><span>+</span></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Preço --}}
    <div class="box-preco">
        @if($product->isOnPromotion())
            <div class="js-valor valor valor-original old-price">
                {{ $product->formatted_original_price }}
            </div>
            <div class="js-valor js-desconto valor valor-desconto new-price">
                {{ $product->formatted_price }}
            </div>
            <span class="desconto-badge">-{{ $product->getDiscountPercentage() }}%</span>
        @else
            <div class="js-valor js-desconto valor valor-desconto new-price">
                {{ $product->formatted_price }}
            </div>
        @endif
    </div>

    {{-- Botão Comprar --}}
    @if($product->isAvailable())
        <a href="javascript:" class="js-add-to-cart" data-product-id="{{ $product->id }}">
            <div class="box-adicionar adicionar">
                <span>Comprar</span>
            </div>
        </a>
    @else
        <div class="box-adicionar indisponivel">
            <span>Indisponível</span>
        </div>
    @endif

    {{-- Indicador de Estoque --}}
    @if($product->stock > 0 && $product->stock <= 10)
        <p class="estoque-baixo">
            <i class="fa fa-exclamation-triangle"></i>
            Últimas {{ $product->stock }} unidades!
        </p>
    @endif

    {{-- Tags/Selos --}}
    <div class="product-tags">
        @if($product->contains_gluten)
            <span class="tag tag-warning">Contém Glúten</span>
        @endif
        @if($product->lactose_free)
            <span class="tag tag-success">Sem Lactose</span>
        @elseif($product->low_lactose)
            <span class="tag tag-info">Baixa Lactose</span>
        @elseif($product->contains_lactose)
            <span class="tag tag-warning">Contém Lactose</span>
        @endif
        @if($product->is_package || $product->is_combo)
            <span class="tag tag-primary">Kit</span>
        @endif
    </div>

    {{-- Validade --}}
    @if($product->shelf_life_days)
        <p class="validade-info">
            <i class="fa fa-clock-o"></i>
            Validade: {{ $product->shelf_life_days }} dias após fabricação
        </p>
    @endif

    <input type="hidden" value="{{ $product->id }}" name="cod_produto">

</div>
