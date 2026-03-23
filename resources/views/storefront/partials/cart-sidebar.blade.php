{{--
    Partial: Conteúdo da Sidebar do Carrinho (#cesta-topo1)

    Renderizado server-side na primeira carga e atualizado via AJAX (GET /carrinho/sidebar).
    Usa as classes CSS do tema herdado da Naturallis.

    Variáveis esperadas:
    - $cart (array) — itens do carrinho indexados por product_id
    - $count (int) — quantidade total de itens
    - $subtotal (float) — valor subtotal
--}}

<div class="icon-topo">
    <a href="{{ url('/carrinho') }}" class="dropdown-toggle">
        {{ \App\Models\Product::formatPrice($subtotal) }}
    </a>
    <h4 style="display: none;">Meu Carrinho</h4>
</div>

<form id="form-minha-compra" action="" method="post" autocomplete="off" class="clearfix" name="frm_cesta_topo">
    <ul class="dropdown-menu">
        @if(count($cart) === 0)
            {{-- Carrinho vazio --}}
            <li>
                <div class="box-subtotal">
                    <p class="mens">Seu carrinho está vazio.</p>
                </div>
            </li>
        @else
            {{-- Lista de itens do carrinho --}}
            @foreach($cart as $item)
                <li class="box-item-carrinho" data-product-id="{{ $item['product_id'] }}">
                    <div class="img-produto">
                        <a href="{{ $item['url'] }}">
                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="img-responsive">
                        </a>
                    </div>
                    <div class="box-desc">
                        <a href="{{ $item['url'] }}" class="nome-produto">{{ $item['name'] }}</a>
                        <span class="qtd">Qtd: {{ $item['quantity'] }}</span>
                        <span class="preco">{{ \App\Models\Product::formatPrice($item['price'] * $item['quantity']) }}</span>
                    </div>
                    <a href="javascript:" class="btn-remove js-cart-remove" data-product-id="{{ $item['product_id'] }}" title="Remover">
                        <i class="fa fa-trash-o"></i>
                    </a>
                </li>
            @endforeach

            {{-- Subtotal e botão de checkout --}}
            <li>
                <div class="box-subtotal">
                    <p>Subtotal: <strong>{{ \App\Models\Product::formatPrice($subtotal) }}</strong></p>
                </div>
            </li>
            <li>
                <div class="box-checkout">
                    <a href="{{ url('/carrinho') }}" class="btn btn-primary btn-block">Ver Carrinho</a>
                </div>
            </li>
        @endif
    </ul>
</form>
