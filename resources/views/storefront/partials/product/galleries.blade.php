{{--
    Partial: Galerias de Produtos
    Renderiza múltiplas galerias configuráveis na home

    Variáveis:
    - $galleries: Collection de ProductGallery
--}}

@foreach($galleries as $index => $gallery)
    @php
        // Busca produtos filtrados para esta galeria
        $products = $gallery->getProducts();
    @endphp

    {{-- Se não há produtos, pula esta galeria --}}
    @if($products->isEmpty())
        @continue
    @endif

    @php
        // Classes de coluna baseadas na configuração
        $mobileClass = $gallery->getMobileColumnClass();
        $desktopClass = $gallery->getDesktopColumnClass();
        $columnClass = "{$mobileClass} col-sm-4 {$desktopClass}";

        // Estilos de fundo
        $backgroundStyle = $gallery->getBackgroundStyle();
    @endphp

    <section class="vitrine-home order" @if($backgroundStyle) style="{{ $backgroundStyle }}" @endif>
        <div class="container">
            <div class="row">
                <div class="col-xs-12">

                    {{-- Título e subtítulo --}}
                    <div class="titulo-vitrine">
                        <div class="group-title">
                            <h3 class="titulo-box animated fadeInDown" style="color: {{ $gallery->title_color }}">
                                {{ $gallery->title }}
                            </h3>

                            @if($gallery->subtitle)
                                <p style="color: {{ $gallery->subtitle_color }}">{{ $gallery->subtitle }}</p>
                            @endif
                        </div>

                        {{-- Botão "Ver Todos" --}}
                        @if($gallery->show_view_all_button && $gallery->view_all_url)
                            <div class="group-btn">
                                <a href="{{ $gallery->view_all_url }}"
                                   class="btn gallery-btn"
                                   style="background-color: {{ $gallery->button_bg_color }} !important;
                                          color: {{ $gallery->button_text_color }} !important;
                                          border-color: {{ $gallery->button_bg_color }} !important;"
                                   onmouseover="this.style.backgroundColor='{{ $gallery->button_hover_color }}'; this.style.borderColor='{{ $gallery->button_hover_color }}'"
                                   onmouseout="this.style.backgroundColor='{{ $gallery->button_bg_color }}'; this.style.borderColor='{{ $gallery->button_bg_color }}'">
                                    Ver Todos
                                </a>
                            </div>
                        @endif
                    </div>

                    {{-- Carousel de produtos --}}
                    <div class="js-carousel js-carousel-new owl-carousel owl-theme carrossel-produtos"
                         data-number="{{ $index + 1 }}">

                        @foreach($products as $product)
                            @include('storefront.partials.product.card', [
                                'product' => $product,
                                'columnClass' => $columnClass,
                                'showFavorite' => false
                            ])
                        @endforeach

                    </div>

                </div>
            </div>
        </div>
    </section>
@endforeach
