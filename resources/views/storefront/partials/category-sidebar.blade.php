{{--
    Partial: Sidebar de Categorias
    Exibe o menu de categorias na lateral da página de listagem
--}}
@php
    // Busca todas as categorias que têm produtos visíveis (ativos + com imagem)
    $sidebarCategories = \App\Models\Category::whereHas('products', function($q) {
        $q->visibleInStore(); // Apenas produtos ativos com imagem
    })->orderBy('name')->get();

    // Categoria atual (se estiver em uma página de categoria)
    $currentCategory = $category ?? null;
@endphp

<aside class="aside-left">
    <div class="box-nav-submenu">
        <a href="javascript:" class="css-mobile navbar-toggle-close">
            <i class="fa fa-times"></i>
            <span class="sr-only">Fechar</span>
        </a>

        <div class="group-title menu-categorias">
            <h4>Categorias</h4>
        </div>

        <ul class="nav menu-categorias">
            @foreach($sidebarCategories as $cat)
                @php
                    $isActive = $currentCategory && $currentCategory->id === $cat->id;
                @endphp
                <li class="dropdown {{ $isActive ? 'open active' : '' }}">
                    <a href="{{ url('/categoria/' . $cat->slug) }}"
                       class="dropdown-toggle flex-icon"
                       data-toggle="linkdropdown">
                        {{ $cat->name }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>

    {{-- Banner lateral (opcional) --}}
    <div class="box-sidebar css-desktop">
        <div class="box-banner">
            {{-- Espaço para banner promocional se necessário --}}
        </div>
    </div>
</aside>
