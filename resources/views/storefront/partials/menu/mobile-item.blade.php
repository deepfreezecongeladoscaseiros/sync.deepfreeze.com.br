{{--
    Partial: Item de Menu Mobile

    Renderiza um item do menu mobile com suporte a:
    - Ícones de imagem
    - Dropdowns expansíveis

    Variáveis:
    - $item: MenuItem model
--}}

@php
    $url = $item->getResolvedUrl();
    $hasChildren = $item->hasChildren();
    $isActive = $item->isActiveOrHasActiveChild();

    $cssClass = 'dropdown';
    if ($isActive) $cssClass .= ' open';
    if (!$hasChildren) $cssClass .= ' link';
    if ($item->css_class) $cssClass .= ' ' . $item->css_class;
@endphp

<li class="{{ $cssClass }}">
    <a href="{{ $url ?: 'javascript:void(0)' }}"
       class="dropdown-toggle flex-icon"
       data-toggle="{{ $hasChildren ? 'dropdown' : 'linkdropdown' }}"
       @if($item->target === '_blank') target="_blank" @endif>

        {{-- Ícone de imagem (se houver) --}}
        @if($item->icon_image && $item->getIconImageUrl())
            <img class="icon-menu"
                 src="{{ $item->getIconImageUrl() }}"
                 alt="{{ $item->title }}">
        @elseif($item->icon_class)
            <i class="{{ $item->icon_class }} icon-menu"></i>
        @endif

        {{ $item->title }}

        @if($hasChildren)
            <i class="fa fa-caret-right"></i>
        @endif
    </a>

    @if($hasChildren)
        <ul class="dropdown-menu" role="menu">
            @foreach($item->activeChildren as $child)
                @php
                    $childUrl = $child->getResolvedUrl();
                @endphp
                <li>
                    <a href="{{ $childUrl ?: '#' }}"
                       @if($child->target === '_blank') target="_blank" @endif>
                        {{ $child->title }}
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</li>
