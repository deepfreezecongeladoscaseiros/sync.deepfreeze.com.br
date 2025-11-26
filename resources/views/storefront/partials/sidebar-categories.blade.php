{{--
    Partial: Sidebar de Categorias

    Exibe lista de categorias na sidebar lateral (desktop).
    Usa o mesmo estilo do menu mobile, mas sem o container #menuTopo.

    Renderizado dinamicamente usando helper get_menu('main').
--}}

@php
    $categories = \App\Models\Category::orderBy('name')->get();
@endphp

<aside class="aside-left">
    <div class="box-nav-submenu">

        <div class="group-title menu-categorias">
            <h4>Categorias</h4>
        </div>

        <ul class="nav menu-categorias">
            @foreach($categories as $cat)
                @php
                    $isActive = isset($category) && $category->id === $cat->id;
                @endphp
                <li class="{{ $isActive ? 'active' : '' }}">
                    <a href="{{ route('category.show', $cat->slug) }}"
                       class="flex-icon">
                        {{ $cat->name }}
                    </a>
                </li>
            @endforeach
        </ul>

    </div>

    {{-- Espaço para banner lateral opcional --}}
    <div class="box-sidebar css-desktop">
        <div class="box-banner">
            {{-- Banner lateral pode ser adicionado aqui --}}
        </div>
    </div>

</aside>
