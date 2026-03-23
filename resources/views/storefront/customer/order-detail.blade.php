{{--
    Página: Detalhe do Pedido
    Exibe informações completas de um pedido específico:
    itens, valores, endereço, status, forma de pagamento.
--}}
@extends('layouts.storefront')

@section('title', 'Pedido #' . $pedido->id . ' - ' . config('app.name'))
@section('body_class', 'pg-interna')

@push('styles')
<style>
    .banner-interna {
        background-size: cover;
        background-position: center;
        min-height: 200px;
        display: flex;
        align-items: center;
    }
    .banner-interna .pg-titulo h1 {
        color: #fff;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        margin: 0;
        font-size: 2.5em;
    }
    .box-detalhe-pedido { padding: 40px 0; }
    .detalhe-section {
        background: #fff;
        border: 1px solid #eee;
        border-radius: 10px;
        padding: 25px;
        margin-bottom: 20px;
    }
    .detalhe-section h3 {
        margin: 0 0 15px;
        font-size: 16px;
        color: var(--color-primary, #013E3B);
        border-bottom: 2px solid var(--color-primary, #013E3B);
        padding-bottom: 8px;
    }
    .detalhe-info p { margin: 4px 0; font-size: 14px; }
    .detalhe-info p strong { color: #333; }
    .badge-status {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        color: #fff;
    }
    .badge-pendente { background: #ffc107; color: #333; }
    .badge-finalizado { background: #28a745; }
    .badge-cancelado { background: #dc3545; }
    .timeline { margin: 0; padding: 0; list-style: none; }
    .timeline li {
        padding: 8px 0 8px 20px;
        border-left: 2px solid #ddd;
        position: relative;
        font-size: 13px;
    }
    .timeline li::before {
        content: '';
        width: 10px;
        height: 10px;
        background: var(--color-primary, #013E3B);
        border-radius: 50%;
        position: absolute;
        left: -6px;
        top: 12px;
    }
    .timeline li:first-child { border-color: var(--color-primary, #013E3B); }
    .timeline li .timeline-date { color: #888; font-size: 12px; }
</style>
@endpush

@section('content')

{{-- Banner --}}
<section class="banner-interna" style="background-image: url('{{ asset('storefront/img/ban-interna-1.jpg') }}');">
    <div class="pg-titulo">
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <h1 class="animated fadeIn">Pedido #{{ $pedido->id }}</h1>
                </div>
            </div>
        </div>
    </div>
</section>

<main class="pg-internas bg-loja">
    <div class="container">
        <div class="box-detalhe-pedido animated fadeIn">

            {{-- Voltar --}}
            <p style="margin-bottom: 20px;">
                <a href="{{ route('customer.orders') }}" class="btn-link">
                    <i class="fa fa-angle-left"></i> Voltar para Meus Pedidos
                </a>
            </p>

            {{-- Resumo --}}
            <div class="detalhe-section">
                <h3>Resumo</h3>
                <div class="detalhe-info">
                    <div class="row">
                        <div class="col-xs-12 col-sm-6">
                            <p><strong>Pedido:</strong> #{{ $pedido->id }}</p>
                            <p><strong>Data:</strong> {{ $pedido->created_at?->format('d/m/Y \à\s H:i') }}</p>
                            <p>
                                <strong>Status:</strong>
                                @php
                                    $badgeClass = match((int) $pedido->finalizado) {
                                        1 => 'badge-finalizado',
                                        3 => 'badge-cancelado',
                                        default => 'badge-pendente',
                                    };
                                @endphp
                                <span class="badge-status {{ $badgeClass }}">{{ $pedido->status_label }}</span>
                            </p>
                        </div>
                        <div class="col-xs-12 col-sm-6">
                            @if($pedido->formaPagamento)
                                <p><strong>Pagamento:</strong> {{ $pedido->formaPagamento->nome }}</p>
                            @endif
                            @if($pedido->isPickup() && $pedido->lojaRetirada)
                                <p><strong>Retirada:</strong> {{ $pedido->lojaRetirada->nome }}</p>
                                @if($pedido->data_retirada)
                                    <p><strong>Data retirada:</strong> {{ \Carbon\Carbon::parse($pedido->data_retirada)->format('d/m/Y') }}</p>
                                @endif
                            @elseif($pedido->isDelivery())
                                <p><strong>Entrega:</strong> {{ $pedido->shipping_full_address }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Itens --}}
            <div class="detalhe-section">
                <h3>Itens do Pedido</h3>
                <table class="table tabela-padrao">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Produto</th>
                            <th>Qtd</th>
                            <th>Preço unit.</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pedido->items as $item)
                            <tr>
                                <td>{{ $item->produto }}</td>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ $item->quantidade }}</td>
                                <td>{{ $item->formatted_price }}</td>
                                <td>{{ $item->formatted_subtotal }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Totais --}}
                <div style="text-align: right; margin-top: 15px;">
                    <p><strong>Subtotal:</strong> {{ $pedido->formatted_subtotal }}</p>
                    @if((float) $pedido->valor_frete > 0)
                        <p><strong>Frete:</strong> {{ $pedido->formatted_shipping_cost }}</p>
                    @else
                        <p><strong>Frete:</strong> <span style="color: #28a745;">A combinar</span></p>
                    @endif
                    @if((float) $pedido->desconto > 0)
                        <p style="color: #28a745;"><strong>Desconto:</strong> - {{ $pedido->formatted_discount }}</p>
                    @endif
                    <p style="font-size: 18px; color: var(--color-primary, #013E3B);">
                        <strong>Total: {{ $pedido->formatted_total }}</strong>
                    </p>
                </div>
            </div>

            {{-- Observações --}}
            @if($pedido->observacao)
                <div class="detalhe-section">
                    <h3>Observações</h3>
                    <p style="background: #f8f8f8; padding: 12px; border-radius: 6px;">{{ $pedido->observacao }}</p>
                </div>
            @endif

            {{-- Histórico de Status (timeline) --}}
            @if($pedido->statuses->count() > 0)
                <div class="detalhe-section">
                    <h3>Histórico</h3>
                    <ul class="timeline">
                        @foreach($pedido->statuses as $status)
                            <li>
                                <strong>{{ $status->status?->nome ?? 'Status #' . $status->statu_id }}</strong>
                                @if($status->observacao)
                                    <br><span style="color: #666;">{{ $status->observacao }}</span>
                                @endif
                                <br><span class="timeline-date">{{ $status->created?->format('d/m/Y H:i') }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Botão continuar comprando --}}
            <div style="text-align: center; margin-top: 20px;">
                <a href="{{ url('/') }}" class="btn btn-confirmar">
                    <i class="fa fa-cutlery"></i> Continuar Comprando
                </a>
            </div>

        </div>
    </div>
</main>

@endsection
