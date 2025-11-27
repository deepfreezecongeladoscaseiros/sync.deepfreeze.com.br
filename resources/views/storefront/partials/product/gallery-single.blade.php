{{--
    Partial: Galeria de Produtos (Individual)
    Renderiza UMA galeria específica

    Variáveis:
    - $gallery: ProductGallery - Galeria a ser renderizada

    Usada pelo sistema de blocos flexíveis (HomeBlock)
    para renderizar galerias individuais intercaladas com outros blocos.
--}}

@php
    // Busca produtos filtrados para esta galeria
    $products = $gallery->getProducts();
@endphp

{{-- Se não há produtos, não renderiza nada --}}
@if($products->isEmpty())
    {{-- Retorna vazio --}}
@else
    @php
        // Classes de coluna baseadas na configuração
        $mobileClass = $gallery->getMobileColumnClass();
        $desktopClass = $gallery->getDesktopColumnClass();
        $columnClass = "{$mobileClass} col-sm-4 {$desktopClass}";

        // Estilos de fundo
        $backgroundStyle = $gallery->getBackgroundStyle();

        // ID único para o carousel (usa o ID da galeria)
        $carouselId = 'gallery-' . $gallery->id;
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
                         data-gallery-id="{{ $gallery->id }}">

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
@endif
