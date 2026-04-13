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
    @if($nutritionalData && isset($nutritionalData['nutri'][1]))
        <p class="info">|</p>
        <p class="info">
            <span class="kcal">{{ number_format($nutritionalData['nutri'][1], 0, ',', '.') }}</span> calorias
        </p>
    @endif

    {{-- Peso --}}
    @if($product->weight)
        <p class="info">|</p>
        <p class="info">
            <span class="peso">{{ $product->weight }}{{ $product->weight_unit ?: 'g' }}</span>
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
            @include('storefront.components.qty-selector', [
                'productId' => $product->id,
                'size' => 'lg',
                'minusClass' => 'js-btn-minus-detail',
                'plusClass' => 'js-btn-plus-detail',
                'inputClass' => 'qtd',
            ])
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

    {{-- Compartilhar --}}
    <div class="product-share">
        <span class="share-label">Compartilhe:</span>
        <a href="https://wa.me/?text={{ urlencode($product->name . ' — ' . $product->url) }}"
           target="_blank" rel="noopener" class="share-btn share-whatsapp" title="WhatsApp">
            <i class="fa fa-whatsapp"></i>
        </a>
        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($product->url) }}"
           target="_blank" rel="noopener" class="share-btn share-facebook" title="Facebook">
            <i class="fa fa-facebook"></i>
        </a>
        <a href="javascript:" class="share-btn share-copy js-copy-link"
           data-url="{{ $product->url }}" title="Copiar link">
            <i class="fa fa-link"></i>
        </a>
    </div>

    {{-- Informações de Alérgenos --}}
    <div class="product-allergens">
        {{-- Glúten --}}
        @if($product->contains_gluten)
            <span class="allergen-badge allergen-warning">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4m0 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>
                Contém Glúten
            </span>
        @else
            <span class="allergen-badge allergen-safe">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
                Não Contém Glúten
            </span>
        @endif

        {{-- Lactose --}}
        @if($product->lactose_free)
            <span class="allergen-badge allergen-safe">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
                Sem Lactose
            </span>
        @elseif($product->low_lactose)
            <span class="allergen-badge allergen-info">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4m0 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>
                Baixa Lactose
            </span>
        @elseif($product->contains_lactose)
            <span class="allergen-badge allergen-warning">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4m0 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>
                Contém Lactose
            </span>
        @endif

        {{-- Bebida alcoólica --}}
        @if($product->alcoholic_beverage)
            <span class="allergen-badge allergen-danger">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                Bebida Alcoólica
            </span>
        @endif

        {{-- Kit/Pacote --}}
        @if($product->is_package || $product->is_combo)
            <span class="allergen-badge allergen-info">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v3"/></svg>
                Kit
            </span>
        @endif
    </div>

    {{-- Alérgenos manuais (texto completo do cadastro) --}}
    @if($product->allergens)
        <div class="product-allergens-text">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4m0 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>
            <strong>Alérgenos:</strong> {{ $product->allergens }}
        </div>
    @endif

    {{-- Validade --}}
    @if($product->shelf_life_days)
        <p class="validade-info">
            <i class="fa fa-clock-o"></i>
            Validade: {{ $product->shelf_life_days }} dias após fabricação
        </p>
    @endif

    <input type="hidden" value="{{ $product->id }}" name="cod_produto">

</div>
