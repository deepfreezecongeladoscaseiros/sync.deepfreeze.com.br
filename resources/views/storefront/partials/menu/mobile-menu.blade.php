{{--
    Partial: Menu Mobile (Sidebar)

    Renderiza o menu lateral para dispositivos móveis.
    Suporta ícones de imagem e dropdowns.

    Uso:
    @include('storefront.partials.menu.mobile-menu')
--}}

@php
    $menuItems = mobile_menu();
    // Se não há menu mobile específico, usa o menu principal
    if ($menuItems->isEmpty()) {
        $menuItems = main_menu('mobile');
    }
@endphp

<aside class="aside-left">
    <div class="box-nav-submenu">
        <a href="javascript:" class="css-mobile navbar-toggle-close">
            <i class="fa fa-times"></i>
            <span class="sr-only">Fechar</span>
        </a>

        @if($menuItems->isNotEmpty())
            <div class="group-title menu-categorias">
                <h4>Categorias</h4>
            </div>

            <ul class="nav menu-categorias">
                @foreach($menuItems as $item)
                    @include('storefront.partials.menu.mobile-item', ['item' => $item])
                @endforeach
            </ul>
        @else
            {{-- Fallback: itens padrão se não houver menu --}}
            <div class="group-title menu-categorias">
                <h4>Categorias</h4>
            </div>
            <ul class="nav menu-categorias">
                <li>
                    <a href="{{ url('/') }}">Home</a>
                </li>
            </ul>
        @endif
    </div>

    {{-- Banner lateral (opcional) --}}
    <div class="box-sidebar css-desktop">
        <div class="box-banner">
            {{-- Espaço para banner lateral se necessário --}}
        </div>
    </div>
</aside>
