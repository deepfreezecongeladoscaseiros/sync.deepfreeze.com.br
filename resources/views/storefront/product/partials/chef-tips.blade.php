{{--
    Partial: Dicas do Chef

    Exibe dicas de preparo e sugestões do chef.

    Variáveis:
    - $product: Product model
--}}

@if($product->chef_tips)
<div class="group-box box-dicas-chef">
    <h2 class="titulo-desc">
        <i class="bi bi-mortarboard"></i> Dicas do Chef
    </h2>

    <div class="dicas-conteudo">
        <div class="dica-icon">
            <i class="bi bi-lightbulb"></i>
        </div>
        <div class="dica-texto">
            <p>{!! nl2br(e($product->chef_tips)) !!}</p>
        </div>
    </div>
</div>
@endif
