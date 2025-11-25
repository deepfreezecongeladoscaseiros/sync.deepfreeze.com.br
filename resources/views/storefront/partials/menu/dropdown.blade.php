{{--
    Partial: Dropdown Simples

    Renderiza um item de menu com subitens em dropdown.

    Variáveis:
    - $item: MenuItem model com children
--}}

@php
    $url = $item->getResolvedUrl();
    $isActive = $item->isActiveOrHasActiveChild();
    $cssClass = 'dropdown menu-' . Str::slug($item->title);
    if ($isActive) $cssClass .= ' active';
    if ($item->css_class) $cssClass .= ' ' . $item->css_class;
@endphp

<li class="{{ $cssClass }}">
    <a class="dropdown-toggle"
       href="{{ $url ?: 'javascript:void(0)' }}"
       @if(!$url) data-toggle="dropdown" @endif>
        {{ $item->title }}
        <i class="fa fa-caret-down"></i>
    </a>

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
</li>
