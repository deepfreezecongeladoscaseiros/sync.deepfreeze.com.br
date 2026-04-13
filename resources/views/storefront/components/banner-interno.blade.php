{{--
    Componente: Banner Interno (topo das páginas internas)

    Faixa com imagem de fundo e título da página.
    Reutilizado em todas as páginas internas do storefront.

    Variáveis:
    - $title: string (obrigatório) — título exibido no banner
    - $image: string (opcional) — URL da imagem de fundo. Default: ban-interna-1.jpg
    - $tag: string (opcional) — tag HTML do título (h1, h2). Default: h1
    - $animation: string (opcional) — classe de animação. Default: fadeIn
--}}
<section class="banner-interna" style="background-image: url('{{ $image ?? asset('storefront/img/ban-interna-1.jpg') }}');">
    <div class="pg-titulo">
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <{{ $tag ?? 'h1' }} class="animated {{ $animation ?? 'fadeIn' }}">{{ $title }}</{{ $tag ?? 'h1' }}>
                </div>
            </div>
        </div>
    </div>
</section>
