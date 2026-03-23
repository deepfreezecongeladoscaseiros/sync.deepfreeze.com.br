{{--
    Página Completa do Carrinho

    Segue a estrutura HTML/CSS do tema Octofood (Naturallis):
    - Banner interno com título "MEU CARRINHO"
    - Tabela .tabela-padrao com colunas: Produto, Preço unitário, Quant, Total, Excluir
    - Valor total fora da tabela, alinhado à direita
    - Botões "Continuar comprando" e "FINALIZAR COMPRA »"
--}}
@extends('layouts.storefront')

@section('title', 'Meu Carrinho - ' . config('app.name'))
@section('body_class', 'pg-interna pg-carrinho')

@push('styles')
<style>
    /* Banner interno — mesmo padrão da página de contato e da Naturallis */
    .pg-carrinho .banner-interna {
        background-size: cover;
        background-position: center;
        min-height: 200px;
        display: flex;
        align-items: center;
    }
    .pg-carrinho .banner-interna .pg-titulo h1 {
        color: #fff;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        margin: 0;
        font-size: 2.5em;
    }
    .pg-carrinho .pg-internas {
        padding: 40px 0;
    }

    /* Tabela do carrinho — usa .tabela-padrao do tema, ajustes específicos */
    .pg-carrinho .tabela-padrao th {
        color: #fff !important;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 13px;
        padding: 12px 15px;
        vertical-align: middle;
    }
    .pg-carrinho .tabela-padrao td {
        vertical-align: middle;
        padding: 15px;
    }
    /* Imagem do produto na tabela */
    .pg-carrinho .tabela-padrao .img-produto {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 4px;
    }
    /* Nome do produto na tabela — link com cor do tema */
    .pg-carrinho .tabela-padrao td .nome-produto-link {
        font-weight: 500;
    }

    /* Controles de quantidade — mesma estrutura .box-number do tema */
    .pg-carrinho .box-number {
        display: inline-flex;
        align-items: center;
    }
    .pg-carrinho .box-number .add-qtd2 {
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        background: #333;
        color: #fff;
        font-size: 12px;
        border-radius: 3px;
        user-select: none;
        transition: background 0.2s;
    }
    .pg-carrinho .box-number .add-qtd2:hover {
        background: #555;
    }
    .pg-carrinho .box-number .qtd {
        width: 40px;
        height: 30px;
        text-align: center;
        border: 1px solid #ddd;
        border-left: none;
        border-right: none;
        font-size: 14px;
        font-weight: 500;
        -moz-appearance: textfield;
        appearance: textfield;
    }
    .pg-carrinho .box-number .qtd::-webkit-outer-spin-button,
    .pg-carrinho .box-number .qtd::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* Botão excluir — ícone circular com cor do tema */
    .pg-carrinho .tabela-padrao .btn-excluir {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
    }
    .pg-carrinho .tabela-padrao .btn-excluir .fa {
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: #fff;
        font-size: 14px;
        transition: background 0.2s;
    }

    /* Valor total — fora da tabela, alinhado à direita */
    .pg-carrinho .box-valor-total {
        text-align: right;
        padding: 20px 0;
    }
    .pg-carrinho .box-valor-total h5 {
        font-size: 14px;
        color: #666;
        margin: 0 0 5px 0;
        font-weight: 400;
    }
    .pg-carrinho .box-valor-total .valor-total {
        font-size: 28px;
        font-weight: 700;
        color: var(--color-primary);
        margin: 0;
    }

    /* Botões de ação — "Continuar comprando" à esquerda, "Finalizar" à direita */
    .pg-carrinho .box-btn-bottom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 0 40px;
    }
    .pg-carrinho .box-btn-bottom .btn-continuar {
        color: var(--color-primary);
        font-weight: 500;
        text-decoration: none;
        font-size: 14px;
    }
    .pg-carrinho .box-btn-bottom .btn-continuar:hover {
        text-decoration: underline;
    }
    .pg-carrinho .box-btn-bottom .btn-continuar .fa {
        margin-right: 5px;
    }
    .pg-carrinho .box-btn-bottom .btn-finalizar {
        padding: 14px 40px;
        font-size: 15px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Carrinho vazio */
    .pg-carrinho .box-carrinho-vazio {
        text-align: center;
        padding: 60px 0;
    }
    .pg-carrinho .box-carrinho-vazio .fa {
        font-size: 64px;
        color: #ccc;
        display: block;
        margin-bottom: 20px;
    }
    .pg-carrinho .box-carrinho-vazio h3 {
        margin-bottom: 10px;
    }
    .pg-carrinho .box-carrinho-vazio .btn {
        margin-top: 20px;
    }
</style>
@endpush

@section('content')
{{-- Banner interno — mesma estrutura da Naturallis --}}
<section class="banner-interna" style="background-image: url('{{ asset('storefront/img/ban-interna-1.jpg') }}');">
    <div class="pg-titulo">
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <h1 class="animated fadeIn">Meu Carrinho</h1>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Conteúdo principal --}}
<main class="pg-internas">
    <div class="container">
        <div class="row">
            <div class="col-xs-12 animated fadeIn">

                @if(count($cart) === 0)
                    {{-- Carrinho vazio --}}
                    <div class="box-carrinho-vazio">
                        <i class="fa fa-shopping-cart"></i>
                        <h3>Seu carrinho está vazio.</h3>
                        <p>Navegue pela loja e adicione produtos ao seu carrinho.</p>
                        <a href="{{ url('/') }}" class="btn">Continuar Comprando</a>
                    </div>
                @else
                    {{-- Tabela de produtos — usa .tabela-padrao do tema --}}
                    <table class="table tabela-padrao">
                        <thead>
                            <tr>
                                <th colspan="2">Produto</th>
                                <th>Preço unitário</th>
                                <th>Quant</th>
                                <th>Total</th>
                                <th>Excluir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cart as $item)
                            <tr data-product-id="{{ $item['product_id'] }}">
                                <td style="width: 80px;">
                                    <a href="{{ $item['url'] }}">
                                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="img-produto img-responsive">
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ $item['url'] }}" class="nome-produto-link">{{ $item['name'] }}</a>
                                </td>
                                <td class="js-preco-unitario">{{ \App\Models\Product::formatPrice($item['price']) }}</td>
                                <td>
                                    <div class="quantidade">
                                        <div class="box-number">
                                            <div class="add-qtd2 js-cart-qty-minus" data-product-id="{{ $item['product_id'] }}">
                                                <i class="fa fa-minus"></i>
                                            </div>
                                            <input type="number" class="qtd js-cart-qty-input" value="{{ $item['quantity'] }}" min="1" max="99" data-product-id="{{ $item['product_id'] }}">
                                            <div class="add-qtd2 js-cart-qty-plus" data-product-id="{{ $item['product_id'] }}">
                                                <i class="fa fa-plus"></i>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="js-linha-total">{{ \App\Models\Product::formatPrice($item['price'] * $item['quantity']) }}</td>
                                <td class="text-center">
                                    <a href="javascript:" class="btn-excluir js-cart-remove" data-product-id="{{ $item['product_id'] }}" title="Remover">
                                        <i class="fa fa-times"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- Valor total — fora da tabela, alinhado à direita (padrão Naturallis) --}}
                    <div class="box-valor-total">
                        <h5>Valor total</h5>
                        <h3 class="valor-total js-cart-subtotal">{{ \App\Models\Product::formatPrice($subtotal) }}</h3>
                    </div>

                    {{-- Botões de ação --}}
                    <div class="box-btn-bottom">
                        <a href="{{ url('/') }}" class="btn-continuar">
                            <i class="fa fa-caret-right" style="transform: rotate(180deg);"></i>
                            Continuar comprando
                        </a>
                        <a href="{{ route('checkout.index') }}" class="btn btn-finalizar">
                            Finalizar Compra &raquo;
                        </a>
                    </div>
                @endif

            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var csrfToken = '{{ csrf_token() }}';

    /**
     * Formata valor para padrão brasileiro (R$ 99,90)
     */
    function formatPrice(value) {
        return 'R$ ' + value.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    /**
     * Atualiza a quantidade de um item no carrinho via AJAX.
     * Atualiza a linha da tabela e o subtotal sem recarregar a página.
     */
    function updateCartQty(productId, quantity) {
        $.ajax({
            url: '{{ url("/carrinho/atualizar") }}',
            method: 'POST',
            data: {
                _token: csrfToken,
                product_id: productId,
                quantity: quantity
            },
            success: function(response) {
                var $row = $('tr[data-product-id="' + productId + '"]');
                var $input = $row.find('.js-cart-qty-input');

                // Atualiza o input com a nova quantidade
                $input.val(quantity);

                // Recalcula o total da linha (preço unitário * quantidade)
                var priceText = $row.find('.js-preco-unitario').text();
                var price = parseFloat(priceText.replace('R$ ', '').replace('.', '').replace(',', '.'));
                var lineTotal = price * quantity;
                $row.find('.js-linha-total').text(formatPrice(lineTotal));

                // Atualiza valor total
                if (response.subtotal_formatted) {
                    $('.js-cart-subtotal').text(response.subtotal_formatted);
                }

                // Atualiza badges do header
                if (response.cart_count !== undefined) {
                    $('.js-cesta-total-produtos-notext').text(response.cart_count);
                }

                // Atualiza sidebar do carrinho
                if (typeof refreshCartSidebar === 'function') {
                    refreshCartSidebar();
                }
            },
            error: function(xhr) {
                var msg = 'Erro ao atualizar carrinho.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alert(msg);
            }
        });
    }

    // Botão diminuir quantidade (-) — usa .add-qtd2 do tema
    $(document).on('click', '.pg-carrinho .js-cart-qty-minus', function() {
        var productId = $(this).data('product-id');
        var $input = $(this).closest('.box-number').find('.js-cart-qty-input');
        var qty = parseInt($input.val()) - 1;
        if (qty >= 1) {
            updateCartQty(productId, qty);
        }
    });

    // Botão aumentar quantidade (+)
    $(document).on('click', '.pg-carrinho .js-cart-qty-plus', function() {
        var productId = $(this).data('product-id');
        var $input = $(this).closest('.box-number').find('.js-cart-qty-input');
        var qty = parseInt($input.val()) + 1;
        if (qty <= 99) {
            updateCartQty(productId, qty);
        }
    });

    // Input de quantidade alterado diretamente
    $(document).on('change', '.pg-carrinho .js-cart-qty-input', function() {
        var productId = $(this).data('product-id');
        var qty = parseInt($(this).val());
        if (qty >= 1 && qty <= 99) {
            updateCartQty(productId, qty);
        }
    });

    // Remover item — remove a linha da tabela sem recarregar
    $(document).on('click', '.pg-carrinho .js-cart-remove', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var $btn = $(this);
        var productId = $btn.data('product-id');

        $.ajax({
            url: '{{ url("/carrinho/remover") }}',
            method: 'POST',
            data: {
                _token: csrfToken,
                product_id: productId
            },
            success: function(response) {
                // Remove a linha da tabela com animação
                $('tr[data-product-id="' + productId + '"]').fadeOut(300, function() {
                    $(this).remove();

                    // Se não sobrou nenhum item, recarrega para mostrar estado vazio
                    if ($('.tabela-padrao tbody tr').length === 0) {
                        location.reload();
                        return;
                    }

                    // Atualiza valor total
                    if (response.subtotal_formatted) {
                        $('.js-cart-subtotal').text(response.subtotal_formatted);
                    }
                });

                // Atualiza badges do header
                if (response.cart_count !== undefined) {
                    $('.js-cesta-total-produtos-notext').text(response.cart_count);
                }

                // Atualiza sidebar
                if (typeof refreshCartSidebar === 'function') {
                    refreshCartSidebar();
                }
            }
        });
    });
});
</script>
@endpush
