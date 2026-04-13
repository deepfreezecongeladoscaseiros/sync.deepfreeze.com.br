{{--
    Componente: Preco do Produto

    Exibe preco normal ou promocional (com preco original riscado).
    Reutilizado em: card de produto (listagens e galerias).

    Nota: A pagina de detalhes do produto NAO usa este componente porque
    possui classes JS (js-valor, js-desconto) para atualizacao dinamica de preco.

    Variaveis:
    - $product: Product model (obrigatorio)
    - $size: string (opcional) -- 'sm' (card) ou 'lg' (detalhes). Default: 'sm'
--}}
@php
    $sizeClass = ($size ?? 'sm') === 'lg' ? 'df-price--lg' : 'df-price--sm';
    $isOnPromotion = $product->isOnPromotion();
@endphp

<div class="df-price {{ $sizeClass }}">
    @if($isOnPromotion)
        {{-- Preco original riscado --}}
        <div class="df-price__original">
            de <span>{{ $product->formatted_original_price }}</span>
        </div>
        {{-- Preco promocional --}}
        <div class="df-price__current df-price__current--promo">
            {{ $product->formatted_price }}
        </div>
    @else
        {{-- Preco normal --}}
        <div class="df-price__current">
            {{ $product->formatted_price }}
        </div>
    @endif
</div>
