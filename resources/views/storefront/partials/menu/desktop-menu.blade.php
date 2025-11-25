{{--
    Partial: Menu Principal Desktop

    Renderiza o menu de navegação principal no header.
    Mantém estrutura HTML compatível com CSS existente.

    Uso:
    @include('storefront.partials.menu.desktop-menu')
--}}

@php
    $menuItems = main_menu('desktop');
@endphp

<div class="menu-principal css-desktop">
    <ul class="nav navbar-nav">
        @forelse($menuItems as $item)
            @if($item->is_mega_menu && $item->hasChildren())
                {{-- Mega Menu (dropdown com colunas e imagem) --}}
                @include('storefront.partials.menu.mega-menu', ['item' => $item])
            @elseif($item->hasChildren())
                {{-- Dropdown simples --}}
                @include('storefront.partials.menu.dropdown', ['item' => $item])
            @else
                {{-- Item simples --}}
                @include('storefront.partials.menu.item', ['item' => $item])
            @endif
        @empty
            {{-- Fallback: se não houver menu cadastrado, exibe itens padrão --}}
            <li class="menu-home active">
                <a href="{{ url('/') }}">Home</a>
            </li>
        @endforelse
    </ul>
</div>
