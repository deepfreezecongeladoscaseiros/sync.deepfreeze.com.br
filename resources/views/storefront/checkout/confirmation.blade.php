{{--
    Página de Confirmação do Pedido

    Exibida após o checkout ser concluído com sucesso.
    Mostra número do pedido, resumo dos itens, dados do cliente e endereço.
    Acesso protegido por user_id (logado) ou session (convidado).
--}}
@extends('layouts.storefront')

@section('title', 'Pedido Confirmado - ' . config('app.name'))
@section('body_class', 'pg-interna pg-checkout')

@push('styles')
<style>
    /* Banner interno */
    .banner-interna {
        background-size: cover;
        background-position: center;
        min-height: 200px;
        display: flex;
        align-items: center;
    }
    .banner-interna.small {
        min-height: 150px;
    }
    .banner-interna .pg-titulo h1 {
        color: #fff;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        margin: 0;
        font-size: 2.5em;
    }

    /* Box de confirmação (sucesso) */
    .box-confirmacao {
        text-align: center;
        padding: 40px 0 30px;
    }
    .box-confirmacao .icon-check {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: #28a745;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
    }
    .box-confirmacao .icon-check .fa {
        color: #fff;
        font-size: 40px;
    }
    .box-confirmacao h2 {
        margin: 0 0 10px;
        font-size: 24px;
        color: #333;
    }
    .box-confirmacao .numero-pedido {
        display: inline-block;
        background: #f0f7f0;
        border: 2px solid var(--color-primary, #013E3B);
        color: var(--color-primary, #013E3B);
        padding: 10px 25px;
        border-radius: 30px;
        font-size: 18px;
        font-weight: 700;
        margin: 15px 0;
    }
    .box-confirmacao p {
        color: #666;
        font-size: 14px;
        margin: 5px 0;
    }

    /* Seções de detalhes */
    .pg-confirmacao {
        padding: 0 0 40px;
    }
    .pg-confirmacao .secao-detalhe {
        margin-bottom: 25px;
    }
    .pg-confirmacao .secao-detalhe h3 {
        font-size: 16px;
        margin: 0 0 15px;
        padding-bottom: 8px;
        border-bottom: 2px solid var(--color-primary, #013E3B);
        color: var(--color-primary, #013E3B);
    }

    /* Tabela de itens no resumo */
    .pg-checkout .tabela-padrao th {
        color: #fff !important;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 13px;
        padding: 12px 15px;
        vertical-align: middle;
    }
    .pg-checkout .tabela-padrao td {
        vertical-align: middle;
        padding: 12px 15px;
    }
    .pg-checkout .tabela-padrao .img-produto {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
    }

    /* Totais */
    .box-totais-confirmacao {
        text-align: right;
        margin-top: 15px;
    }
    .box-totais-confirmacao .total-linha {
        display: flex;
        justify-content: flex-end;
        gap: 20px;
        padding: 4px 0;
        font-size: 14px;
    }
    .box-totais-confirmacao .total-linha.destaque {
        font-size: 18px;
        font-weight: 700;
        color: var(--color-primary, #013E3B);
        padding-top: 10px;
        border-top: 2px solid var(--color-primary, #013E3B);
        margin-top: 5px;
    }

    /* Dados do cliente e endereço */
    .dados-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    .dados-grid .dado-item p {
        margin: 4px 0;
        font-size: 14px;
        color: #555;
    }
    .dados-grid .dado-item p strong {
        color: #333;
    }

    /* Box de pagamento */
    .box-pagamento-info {
        background: #FFF3CD;
        border-left: 4px solid #FFC107;
        padding: 15px 20px;
        border-radius: 4px;
        margin-bottom: 25px;
    }
    .box-pagamento-info p {
        margin: 0;
        font-size: 14px;
    }

    /* Botão continuar comprando */
    .box-btn-confirmacao {
        text-align: center;
        padding: 20px 0 40px;
    }
    .box-btn-confirmacao .btn {
        padding: 14px 40px;
        font-size: 15px;
        font-weight: 600;
    }

    @media (max-width: 767px) {
        .dados-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')

{{-- Banner interno --}}
<section class="banner-interna small" style="background-image: url('{{ asset('storefront/img/ban-interna-1.jpg') }}');">
    <div class="pg-titulo">
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <h1 class="animated fadeIn">Pedido Confirmado!</h1>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Conteúdo principal --}}
<main class="pg-internas pg-confirmacao">
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-lg-8 col-lg-offset-2">

                {{-- Box de confirmação com ícone e número do pedido --}}
                <div class="box-confirmacao">
                    <div class="icon-check">
                        <i class="fa fa-check"></i>
                    </div>
                    <h2>Seu pedido foi realizado com sucesso!</h2>
                    <div class="numero-pedido">#{{ $order->order_number }}</div>
                    <p>Enviamos a confirmação para <strong>{{ $order->customer_email }}</strong></p>
                    <p>Data: {{ $order->created_at->format('d/m/Y \à\s H:i') }}</p>
                </div>

                {{-- Informação sobre pagamento --}}
                <div class="box-pagamento-info">
                    <p>
                        <strong><i class="fa fa-info-circle"></i> Sobre o pagamento:</strong><br>
                        O pagamento será combinado diretamente com a Deep Freeze.
                        Entraremos em contato em breve para confirmar a forma de pagamento e o envio do seu pedido.
                    </p>
                </div>

                {{-- Itens do pedido --}}
                <div class="secao-detalhe">
                    <h3>Itens do Pedido</h3>
                    <table class="table tabela-padrao">
                        <thead>
                            <tr>
                                <th colspan="2">Produto</th>
                                <th>Preço unit.</th>
                                <th>Qtd</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td style="width: 60px;">
                                        @if($item->product_image)
                                            <img src="{{ $item->product_image }}" alt="{{ $item->product_name }}" class="img-produto img-responsive">
                                        @endif
                                    </td>
                                    <td>
                                        {{ $item->product_name }}
                                        @if($item->product_sku)
                                            <br><small style="color: #999;">SKU: {{ $item->product_sku }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $item->formatted_unit_price }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ $item->formatted_total }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- Totais --}}
                    <div class="box-totais-confirmacao">
                        <div class="total-linha">
                            <span>Subtotal:</span>
                            <span>{{ $order->formatted_subtotal }}</span>
                        </div>
                        <div class="total-linha">
                            <span>Frete:</span>
                            <span>{{ (float) $order->shipping_cost > 0 ? $order->formatted_shipping_cost : 'A combinar' }}</span>
                        </div>
                        @if((float) $order->discount > 0)
                            <div class="total-linha" style="color: #28a745;">
                                <span>Desconto:</span>
                                <span>- {{ $order->formatted_discount }}</span>
                            </div>
                        @endif
                        <div class="total-linha destaque">
                            <span>Total:</span>
                            <span>{{ $order->formatted_total }}</span>
                        </div>
                    </div>
                </div>

                {{-- Dados do cliente e endereço --}}
                <div class="secao-detalhe">
                    <h3>Informações</h3>
                    <div class="dados-grid">
                        <div class="dado-item">
                            <p><strong>Cliente</strong></p>
                            <p>{{ $order->customer_name }}</p>
                            <p>{{ $order->customer_email }}</p>
                            <p>{{ $order->customer_phone }}</p>
                            <p>{{ $order->customer_person_type === 'juridica' ? 'CNPJ: ' . $order->customer_cnpj : 'CPF: ' . $order->customer_cpf }}</p>
                        </div>
                        <div class="dado-item">
                            <p><strong>Endereço de Entrega</strong></p>
                            <p>{{ $order->shipping_address }}, {{ $order->shipping_number }}
                                @if($order->shipping_complement) - {{ $order->shipping_complement }}@endif
                            </p>
                            <p>{{ $order->shipping_neighborhood }}</p>
                            <p>{{ $order->shipping_city }}/{{ $order->shipping_state }}</p>
                            <p>CEP: {{ $order->shipping_zip_code }}</p>
                        </div>
                    </div>
                </div>

                {{-- Observações (se houver) --}}
                @if($order->notes)
                    <div class="secao-detalhe">
                        <h3>Observações</h3>
                        <p style="background: #f8f8f8; padding: 12px 16px; border-radius: 4px;">{{ $order->notes }}</p>
                    </div>
                @endif

                {{-- Botão continuar comprando --}}
                <div class="box-btn-confirmacao">
                    <a href="{{ url('/') }}" class="btn">Continuar Comprando</a>
                </div>

            </div>
        </div>
    </div>
</main>

@endsection
