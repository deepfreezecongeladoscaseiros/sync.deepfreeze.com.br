{{--
    Componente: Botão Comprar

    Três variações: produto normal (qty + Comprar), kit (Ver Kit), indisponível.
    Usa o componente qty-selector internamente.
    Reutilizado em: card de produto, detalhes do produto.

    Variáveis:
    - $product: Product model (obrigatório)
    - $isKit: bool (obrigatório)
    - $isAvailable: bool (obrigatório)
    - $size: string (opcional) — 'sm' (card) ou 'lg' (detalhes). Default: 'sm'
    - $showQty: bool (opcional) — exibir seletor de quantidade. Default: true
--}}
@php
    $sizeClass = ($size ?? 'sm') === 'lg' ? 'df-buy--lg' : 'df-buy--sm';
    $showQuantity = $showQty ?? true;
@endphp

@if($isAvailable)
    @if($isKit)
        {{-- Kit: link para ver detalhes --}}
        <a href="{{ $product->url }}" class="df-buy {{ $sizeClass }} df-buy--kit">
            <span>Ver Kit</span>
        </a>
    @else
        {{-- Produto normal: quantidade + comprar --}}
        <div class="df-buy {{ $sizeClass }}">
            @if($showQuantity)
                @include('storefront.components.qty-selector', [
                    'productId' => $product->id,
                    'size' => $size ?? 'sm',
                ])
            @endif
            <a href="javascript:"
               class="df-buy__btn js-add-to-cart"
               data-product-id="{{ $product->id }}">
                <span>Comprar</span>
            </a>
        </div>
    @endif
@else
    {{-- Indisponível --}}
    <a href="{{ $product->url }}" class="df-buy {{ $sizeClass }} df-buy--unavailable">
        <span>Indisponível</span>
    </a>
@endif
