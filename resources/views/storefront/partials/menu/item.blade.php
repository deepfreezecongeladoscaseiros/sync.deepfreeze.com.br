{{--
    Partial: Item de Menu Simples

    Renderiza um item de menu sem filhos (link direto).

    Variáveis:
    - $item: MenuItem model
--}}

@php
    $url = $item->getResolvedUrl();
    $isActive = $item->isActive();
    $cssClass = 'menu-' . Str::slug($item->title);
    if ($isActive) $cssClass .= ' active';
    if ($item->css_class) $cssClass .= ' ' . $item->css_class;
@endphp

<li class="{{ $cssClass }}">
    <a href="{{ $url ?: 'javascript:void(0)' }}"
       @if($item->target === '_blank') target="_blank" rel="noopener noreferrer" @endif>
        {{-- Ícone (se houver) --}}
        @if($item->icon_class)
            <i class="{{ $item->icon_class }}"></i>
        @endif
        {{ $item->title }}
    </a>
</li>
