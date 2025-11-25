{{--
    Partial: Mega Menu

    Renderiza um mega menu com:
    - Colunas de itens organizados em grupos
    - Imagem/banner promocional (opcional)

    Variáveis:
    - $item: MenuItem model (is_mega_menu = true)
--}}

@php
    $url = $item->getResolvedUrl();
    $isActive = $item->isActiveOrHasActiveChild();
    $cssClass = 'dropdown menu-' . Str::slug($item->title) . ' submenu-full';
    if ($isActive) $cssClass .= ' active';
    if ($item->css_class) $cssClass .= ' ' . $item->css_class;

    $hasImage = $item->hasMegaMenuImage();
    $imagePosition = $item->mega_menu_image_position ?? 'right';
    $columns = $item->mega_menu_columns ?? 2;

    // Calcula tamanho das colunas Bootstrap
    // Mantém compatibilidade com layout original: col-xs-6 col-sm-3 col-md-{n}
    $contentCols = $hasImage ? 6 : 12;
    $imageCols = $hasImage ? 6 : 0;
    $mdColSize = 12 / $columns; // Ex: 2 colunas = 6, 4 colunas = 3
    $smColSize = max(3, $mdColSize); // No mobile (sm), mínimo 3 (4 itens por linha)
    $itemColClass = 'col-xs-6 col-sm-' . $smColSize . ' col-md-' . $mdColSize;
@endphp

<li class="{{ $cssClass }}">
    <a class="dropdown-toggle" href="{{ $url ?: 'javascript:void(0)' }}">
        {{ $item->title }}
        <i class="fa fa-caret-down"></i>
    </a>

    <ul class="dropdown-menu">
        <div class="container">
            <div class="row">
                {{-- Imagem à esquerda --}}
                @if($hasImage && $imagePosition === 'left')
                    @include('storefront.partials.menu.mega-menu-image', [
                        'item' => $item,
                        'cols' => $imageCols
                    ])
                @endif

                {{-- Conteúdo (grupos de itens) --}}
                <div class="col-xs-12 col-md-{{ $contentCols }}">
                    <div class="col-left">
                        <div class="row">
                            @foreach($item->activeChildren as $group)
                                <div class="{{ $itemColClass }}">
                                    <div class="group-menu boxHeight2">
                                        {{-- Título do grupo --}}
                                        @php
                                            $groupUrl = $group->getResolvedUrl();
                                        @endphp
                                        <h5 class="boxHeight3">
                                            @if($groupUrl)
                                                <a href="{{ $groupUrl }}">{{ $group->title }}</a>
                                            @else
                                                {{ $group->title }}
                                            @endif
                                        </h5>

                                        {{-- Subitens do grupo --}}
                                        @if($group->hasChildren())
                                            @foreach($group->activeChildren as $subItem)
                                                @php
                                                    $subUrl = $subItem->getResolvedUrl();
                                                @endphp
                                                @if($subUrl)
                                                    <li>
                                                        <a href="{{ $subUrl }}"
                                                           @if($subItem->target === '_blank') target="_blank" @endif>
                                                            {{ $subItem->title }}
                                                        </a>
                                                    </li>
                                                @endif
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Imagem à direita (padrão) --}}
                @if($hasImage && $imagePosition === 'right')
                    @include('storefront.partials.menu.mega-menu-image', [
                        'item' => $item,
                        'cols' => $imageCols
                    ])
                @endif
            </div>
        </div>

        {{-- Imagem abaixo --}}
        @if($hasImage && $imagePosition === 'bottom')
            <div class="container">
                <div class="row">
                    @include('storefront.partials.menu.mega-menu-image', [
                        'item' => $item,
                        'cols' => 12
                    ])
                </div>
            </div>
        @endif
    </ul>
</li>
