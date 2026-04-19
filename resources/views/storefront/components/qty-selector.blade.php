{{--
    Componente: Seletor de Quantidade (+/- input)

    Componente reutilizável para seleção de quantidade.
    Usado em: card de produto, detalhes do produto, carrinho.

    Variáveis:
    - $productId: int (obrigatório) — ID do produto
    - $size: string (opcional) — 'sm' (card) ou 'lg' (detalhes). Default: 'sm'
    - $minusClass: string (opcional) — classe JS extra para o botão minus. Default: 'js-btn-minus'
    - $plusClass: string (opcional) — classe JS extra para o botão plus. Default: 'js-btn-plus'
    - $inputClass: string (opcional) — classe CSS extra para o input. Default: ''
    - $qtyValue: int (opcional) — valor inicial. Default: 1
      ATENÇÃO: não usar $value — conflita com @foreach do Blade
--}}
@php
    $sizeClass = ($size ?? 'sm') === 'lg' ? 'df-qty--lg' : 'df-qty--sm';
    $btnMinus = $minusClass ?? 'js-btn-minus';
    $btnPlus = $plusClass ?? 'js-btn-plus';
    $inputExtraClass = $inputClass ?? '';
    $initialValue = $qtyValue ?? 1;
@endphp

<div class="df-qty {{ $sizeClass }}">
    <button type="button"
            class="df-qty__btn df-qty__btn--minus {{ $btnMinus }}"
            data-product-id="{{ $productId }}">
        <span>&minus;</span>
    </button>
    <input type="text"
           class="df-qty__input js-qtd-input {{ $inputExtraClass }}"
           id="qtd-{{ $productId }}"
           name="quantidade"
           value="{{ $initialValue }}"
           readonly
           data-product-id="{{ $productId }}">
    <button type="button"
            class="df-qty__btn df-qty__btn--plus {{ $btnPlus }}"
            data-product-id="{{ $productId }}">
        <span>&plus;</span>
    </button>
</div>
