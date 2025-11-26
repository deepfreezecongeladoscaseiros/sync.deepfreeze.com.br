{{--
    Partial: Galeria de Fotos do Produto

    Exibe a imagem principal e miniaturas (thumbnails) do produto.

    Variáveis:
    - $product: Product model com relacionamento images carregado
--}}

<div class="col-xs-12 col-sm-6 col-md-6 col-lg-7 galeria-fotos-produtos">
    <div class="row">

        {{-- Imagem Principal --}}
        <div class="item-capa">
            <div class="col-xs-12">
                @php
                    $mainImage = $product->getMainImage();
                    $mainImageUrl = $product->getMainImageUrl('large');
                @endphp
                <img id="galeriaProdutos"
                     class="img-responsive"
                     src="{{ $mainImageUrl }}"
                     data-zoom-image="{{ $mainImageUrl }}"
                     title="{{ $product->name }}"
                     alt="{{ $product->name }}" />
            </div>
        </div>

        {{-- Miniaturas (Thumbnails) --}}
        @if($product->images && $product->images->count() > 1)
            <div class="item-thumbs">
                <div class="col-xs-12">
                    <div class="row">
                        @foreach($product->images as $index => $image)
                            @php
                                $imageUrl = $image->path;
                                if (!str_starts_with($imageUrl, 'http')) {
                                    $imageUrl = asset('storage/' . $image->path);
                                }
                            @endphp
                            <div class="col-xs-3">
                                <a href="javascript:"
                                   class="thumb-item {{ $index === 0 ? 'active' : '' }}"
                                   data-image="{{ $imageUrl }}"
                                   data-zoom="{{ $imageUrl }}">
                                    <img class="img-responsive"
                                         src="{{ $imageUrl }}"
                                         alt="{{ $product->name }} - Imagem {{ $index + 1 }}" />
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
