{{--
    Partial: Renderiza um item de menu recursivamente na árvore
    Usado no drag-and-drop do Nestable
--}}
@php
    $badgeClass = match($item->type) {
        'category' => 'badge-primary',
        'page' => 'badge-success',
        'url' => 'badge-info',
        'home' => 'badge-warning',
        'contact' => 'badge-secondary',
        'submenu_title' => 'badge-dark',
        default => 'badge-light'
    };
@endphp

<li class="dd-item {{ !$item->active ? 'item-inactive' : '' }} {{ $item->is_mega_menu ? 'item-mega-menu' : '' }}"
    data-id="{{ $item->id }}"
    data-title="{{ $item->title }}"
    data-type="{{ $item->type }}"
    data-linkable-id="{{ $item->linkable_id }}"
    data-url="{{ $item->url }}"
    data-target="{{ $item->target }}"
    data-icon-class="{{ $item->icon_class }}"
    data-css-class="{{ $item->css_class }}"
    data-show-on="{{ $item->show_on }}"
    data-is-mega-menu="{{ $item->is_mega_menu ? 1 : 0 }}"
    data-mega-menu-image-url="{{ $item->mega_menu_image_url }}"
    data-mega-menu-image-alt="{{ $item->mega_menu_image_alt }}"
    data-mega-menu-image-position="{{ $item->mega_menu_image_position }}"
    data-mega-menu-columns="{{ $item->mega_menu_columns }}"
    data-active="{{ $item->active ? 1 : 0 }}">

    <div class="dd3-handle"><i class="fas fa-grip-vertical"></i></div>

    <div class="dd3-content">
        {{-- Ícone do item --}}
        @if($item->icon_image && $item->getIconImageUrl())
            <img src="{{ $item->getIconImageUrl() }}" alt="" class="item-icon">
        @elseif($item->icon_class)
            <i class="{{ $item->icon_class }} mr-1"></i>
        @endif

        {{-- Título --}}
        <strong class="item-title">{{ $item->title }}</strong>

        {{-- Badge do tipo --}}
        <span class="badge {{ $badgeClass }} ml-2">{{ $item->getTypeLabel() }}</span>

        {{-- Indicador de mega menu --}}
        @if($item->is_mega_menu)
            <i class="fas fa-th-large text-info ml-1" title="Mega Menu"></i>
        @endif

        {{-- Indicador de dispositivo --}}
        @if($item->show_on === 'desktop')
            <i class="fas fa-desktop text-muted ml-1" title="Apenas Desktop"></i>
        @elseif($item->show_on === 'mobile')
            <i class="fas fa-mobile-alt text-muted ml-1" title="Apenas Mobile"></i>
        @endif

        {{-- URL/Link --}}
        @if($item->getResolvedUrl())
            <small class="text-muted ml-2">
                <i class="fas fa-link"></i>
                {{ Str::limit($item->getResolvedUrl(), 40) }}
            </small>
        @endif

        {{-- Ações --}}
        <div class="item-actions">
            <button type="button" class="btn btn-sm btn-outline-primary add-subitem-btn"
                    data-parent-id="{{ $item->id }}" title="Adicionar Subitem">
                <i class="fas fa-plus"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-warning edit-item-btn"
                    data-id="{{ $item->id }}" title="Editar">
                <i class="fas fa-edit"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-{{ $item->active ? 'secondary' : 'success' }} toggle-status-btn"
                    data-id="{{ $item->id }}" title="{{ $item->active ? 'Desativar' : 'Ativar' }}">
                <i class="fas fa-{{ $item->active ? 'eye-slash' : 'eye' }}"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger delete-item-btn"
                    data-id="{{ $item->id }}" title="Excluir">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </div>

    {{-- Filhos (recursivo) --}}
    @if($item->activeChildrenRecursive && $item->activeChildrenRecursive->count() > 0)
        <ol class="dd-list">
            @foreach($item->activeChildrenRecursive as $child)
                @include('admin.menus.partials.item-tree', ['item' => $child])
            @endforeach
        </ol>
    @endif
</li>
