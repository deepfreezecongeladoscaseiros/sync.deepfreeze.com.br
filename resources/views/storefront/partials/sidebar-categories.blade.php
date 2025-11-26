{{--
    Partial: Sidebar de Categorias

    Exibe menu lateral de navegação (desktop).
    Usa o módulo de Menu do sistema para exibir itens configuráveis
    com suporte a ícones, dropdowns e submenus.

    Estrutura idêntica ao site original Naturallis.
--}}

@php
    // Busca o menu específico do sidebar (independente do menu mobile)
    $menuItems = sidebar_menu();

    // Função auxiliar para renderizar item de menu com dropdown
    function renderSidebarMenuItem($item, $currentUrl = null) {
        $url = $item->getResolvedUrl();
        $hasChildren = $item->hasChildren();
        $isActive = $currentUrl && $url && $currentUrl === $url;

        // Classe do item (dropdown se tiver filhos)
        $liClass = [];
        if ($hasChildren) {
            $liClass[] = 'dropdown';
        }
        if ($isActive) {
            $liClass[] = 'active';
        }
        $liClassStr = implode(' ', $liClass);

        $html = '<li class="' . $liClassStr . '">';

        // Link principal
        if ($hasChildren) {
            // Com dropdown
            $html .= '<a href="' . e($url ?: 'javascript:void(0)') . '" class="dropdown-toggle flex-icon" data-toggle="dropdown">';
        } else {
            // Sem dropdown
            $html .= '<a href="' . e($url ?: '#') . '" class="flex-icon">';
        }

        // Ícone (imagem)
        if ($item->icon_image && $item->getIconImageUrl()) {
            $html .= '<img class="icon-menu" src="' . e($item->getIconImageUrl()) . '" alt="' . e($item->title) . '">';
        }

        // Título
        $html .= e($item->title);

        // Seta para dropdown
        if ($hasChildren) {
            $html .= '<i class="fa fa-caret-right"></i>';
        }

        $html .= '</a>';

        // Submenu (filhos)
        if ($hasChildren) {
            $html .= '<ul class="dropdown-menu" role="menu">';

            foreach ($item->activeChildren as $child) {
                $childUrl = $child->getResolvedUrl();
                $html .= '<li><a href="' . e($childUrl) . '">' . e($child->title) . '</a></li>';
            }

            $html .= '</ul>';
        }

        $html .= '</li>';

        return $html;
    }
@endphp

<aside class="aside-left">
    <div class="box-nav-submenu">
        {{-- Botão fechar (mobile) --}}
        <a href="javascript:" class="css-mobile navbar-toggle-close">
            <i class="fa fa-times"></i><span class="sr-only">Fechar</span>
        </a>

        {{-- Título "Categorias" --}}
        <div class="group-title menu-categorias">
            <h4>Categorias</h4>
        </div>

        {{-- Lista de itens do menu --}}
        <ul class="nav menu-categorias">
            @foreach($menuItems as $item)
                {!! renderSidebarMenuItem($item, request()->url()) !!}
            @endforeach
        </ul>

    </div>

    {{-- Banner lateral (opcional) --}}
    <div class="box-sidebar css-desktop">
        <div class="box-banner">
            {{-- Banner lateral pode ser adicionado aqui --}}
        </div>
    </div>

</aside>
