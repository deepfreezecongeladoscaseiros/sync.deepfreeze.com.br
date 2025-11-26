{{--
    Partial: Produtos Relacionados

    Exibe uma galeria de produtos relacionados (mesma categoria).
    Utiliza o componente unificado de card de produto para manter consistência visual.

    Variáveis:
    - $relatedProducts: Collection de Product models
--}}

@if($relatedProducts && $relatedProducts->count() > 0)
<section class="produtos-relacionados">
    <div class="row">
        <div class="col-xs-12">
            <h2 class="titulo-secao">Você também pode gostar</h2>
        </div>
    </div>

    {{--
        Usa o componente unificado de card de produto (storefront/partials/product/card.blade.php)
        para manter consistência visual em todo o site.

        Parâmetros:
        - product: Objeto Product (obrigatório)
        - columnClass: Classes de grid (opcional, padrão: col-xs-6 col-sm-4 col-lg-3)
        - showFavorite: Exibir botão de favorito (opcional, padrão: true)
    --}}
    <div class="row listagem-produtos">
        @foreach($relatedProducts as $relatedProduct)
            @include('storefront.partials.product.card', [
                'product' => $relatedProduct,
                'columnClass' => 'col-xs-6 col-sm-4 col-md-3',
                'showFavorite' => true
            ])
        @endforeach
    </div>
</section>
@endif
