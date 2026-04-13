{{--
    Componente: Breadcrumb

    Navegação hierárquica. Reutilizado em categoria, produto, páginas internas.

    Variáveis:
    - $items: array de ['title' => string, 'url' => string|null]
      O último item (url = null) é exibido como ativo.
--}}
<section class="box-breadcrumb">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        @foreach($items as $item)
                            @if($item['url'])
                                <li class="breadcrumb-item">
                                    <a href="{{ $item['url'] }}">{{ $item['title'] }}</a>
                                </li>
                            @else
                                <li class="breadcrumb-item active" aria-current="page">
                                    {{ $item['title'] }}
                                </li>
                            @endif
                        @endforeach
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>
