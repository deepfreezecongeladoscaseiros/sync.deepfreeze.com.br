{{--
    Partial: Ingredientes e Alérgenos

    Exibe a lista de ingredientes e informações sobre alérgenos.

    Variáveis:
    - $product: Product model
--}}

@if($product->ingredients || $product->allergens || $product->contains_gluten || $product->contains_lactose)
<div class="group-box box-ingredientes">
    <h2 class="titulo-desc">
        <i class="bi bi-basket3"></i> Ingredientes
    </h2>

    @if($product->ingredients)
        <div class="ingredientes-lista">
            <p>{!! nl2br(e($product->ingredients)) !!}</p>
        </div>
    @endif

    {{-- Alérgenos --}}
    @if($product->allergens || $product->contains_gluten || $product->contains_lactose)
        <div class="alergenicos">
            <h5><i class="fa fa-exclamation-triangle"></i> Informações sobre Alérgenos</h5>

            @if($product->allergens)
                <p class="alergenicos-texto">{{ $product->allergens }}</p>
            @else
                <p class="alergenicos-texto">
                    @if($product->contains_gluten)
                        <strong>CONTÉM GLÚTEN.</strong>
                    @endif
                    @if($product->contains_lactose)
                        <strong>CONTÉM LACTOSE.</strong>
                    @elseif($product->low_lactose)
                        <strong>BAIXO TEOR DE LACTOSE.</strong>
                    @elseif($product->lactose_free)
                        <strong>SEM LACTOSE.</strong>
                    @endif
                </p>
            @endif
        </div>
    @endif
</div>
@endif
