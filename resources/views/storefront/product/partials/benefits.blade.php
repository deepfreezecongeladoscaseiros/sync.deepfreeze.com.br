{{--
    Partial: Benefícios do Produto

    Exibe os benefícios nutricionais e de saúde do produto.

    Variáveis:
    - $product: Product model
--}}

@if($product->benefits)
<div class="group-box box-beneficios">
    <h2 class="titulo-desc">
        <i class="bi bi-heart-pulse"></i> Benefícios
    </h2>

    <div class="beneficios-conteudo">
        <div class="beneficio-icon">
            <i class="bi bi-check2-circle"></i>
        </div>
        <div class="beneficio-texto">
            <p>{!! nl2br(e($product->benefits)) !!}</p>
        </div>
    </div>
</div>
@endif

@if($product->properties)
<div class="group-box box-propriedades">
    <h2 class="titulo-desc">
        <i class="bi bi-clipboard2-pulse"></i> Propriedades Nutricionais
    </h2>

    <div class="propriedades-conteudo">
        <div class="propriedade-icon">
            <i class="bi bi-shield-check"></i>
        </div>
        <div class="propriedade-texto">
            <p>{!! nl2br(e($product->properties)) !!}</p>
        </div>
    </div>
</div>
@endif
