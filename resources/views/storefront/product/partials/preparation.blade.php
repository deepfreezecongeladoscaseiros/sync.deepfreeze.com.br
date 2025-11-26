{{--
    Partial: Modo de Preparo / Instruções de Consumo

    Exibe as instruções de como aquecer/preparar o produto.

    Variáveis:
    - $product: Product model
--}}

@if($product->consumption_instructions)
<div class="group-box box-preparo">
    <h2 class="titulo-desc">
        <i class="bi bi-fire"></i> Modo de Preparo
    </h2>

    <div class="preparo-conteudo">
        <div class="preparo-icon">
            <i class="bi bi-thermometer-half"></i>
        </div>
        <div class="preparo-texto">
            {!! nl2br(e($product->consumption_instructions)) !!}
        </div>
    </div>
</div>
@endif
