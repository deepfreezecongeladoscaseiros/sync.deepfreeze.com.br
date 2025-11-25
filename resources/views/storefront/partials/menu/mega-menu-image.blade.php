{{--
    Partial: Imagem do Mega Menu

    Renderiza a imagem/banner promocional do mega menu.

    Variáveis:
    - $item: MenuItem model
    - $cols: Número de colunas Bootstrap (6 ou 12)
--}}

@php
    $imageUrl = $item->getMegaMenuImageUrl();
    $imageAlt = $item->mega_menu_image_alt ?: $item->title;
    $linkUrl = $item->mega_menu_image_url;
@endphp

<div class="col-xs-12 col-md-{{ $cols }} boxHeight hidden-xs">
    <div class="col-right">
        @if($linkUrl)
            <a href="{{ $linkUrl }}">
        @endif

        <img class="img-responsive"
             src="{{ $imageUrl }}"
             alt="{{ $imageAlt }}"
             title="{{ $imageAlt }}">

        @if($linkUrl)
            </a>
        @endif
    </div>
</div>
