{{--
    Página: Meus Pedidos
    Lista os pedidos do cliente logado com status, data e valor.
    Usa layout da área do cliente (sidebar + banner "Minha Conta").
--}}
@extends('storefront.customer.layout')

@section('title', 'Meus Pedidos - ' . config('app.name'))

@push('styles')
<style>
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
    .box-meus-pedidos { padding: 40px 0; }
    .pedido-card {
        background: #fff;
        border: 1px solid #eee;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 15px;
        transition: box-shadow 0.2s;
    }
    .pedido-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
    .pedido-card .pedido-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 10px;
    }
    .pedido-card .pedido-info { color: #666; font-size: 13px; }
    .pedido-card .pedido-total { font-size: 18px; font-weight: 700; color: var(--color-primary, #013E3B); }
    .pedido-card .pedido-items { font-size: 13px; color: #888; margin-top: 8px; }
    .empty-state { text-align: center; padding: 60px 0; color: #999; }
    .empty-state i { font-size: 48px; margin-bottom: 15px; display: block; opacity: 0.4; }
</style>
@endpush

@section('customer-content')

        <div class="box-meus-pedidos animated fadeIn">

            {{-- Saudação --}}
            <p style="margin-bottom: 20px; font-size: 15px;">
                Olá, <strong>{{ $customer->display_name }}</strong>!
                Aqui estão seus pedidos.
            </p>

            @if($pedidos->isEmpty())
                <div class="empty-state">
                    <i class="fa fa-shopping-bag"></i>
                    <h3>Nenhum pedido encontrado</h3>
                    <p>Você ainda não realizou nenhum pedido.</p>
                    <a href="{{ url('/') }}" class="btn btn-confirmar" style="margin-top: 15px;">
                        <i class="fa fa-cutlery"></i> Ver Produtos
                    </a>
                </div>
            @else
                @foreach($pedidos as $pedido)
                    <div class="pedido-card">
                        <div class="pedido-header">
                            <div>
                                <strong>Pedido #{{ $pedido->id }}</strong>
                                @php
                                    $badgeClass = match((int) $pedido->finalizado) {
                                        1 => 'badge-finalizado',
                                        3 => 'badge-cancelado',
                                        default => 'badge-pendente',
                                    };
                                @endphp
                                <span class="badge-status {{ $badgeClass }}">{{ $pedido->status_label }}</span>
                            </div>
                            <div class="pedido-total">{{ $pedido->formatted_total }}</div>
                        </div>

                        <div class="pedido-info">
                            <i class="fa fa-calendar"></i> {{ $pedido->created_at?->format('d/m/Y H:i') }}
                            @if($pedido->formas_pagamento_id)
                                &nbsp;&middot;&nbsp;
                                <i class="fa fa-credit-card"></i> {{ $pedido->formaPagamento?->nome ?? '' }}
                            @endif
                            @if($pedido->isPickup() && $pedido->lojaRetirada)
                                &nbsp;&middot;&nbsp;
                                <i class="fa fa-shopping-bag"></i> Retirada: {{ $pedido->lojaRetirada->nome }}
                            @elseif($pedido->isDelivery() && $pedido->cep_entrega)
                                &nbsp;&middot;&nbsp;
                                <i class="fa fa-truck"></i> Entrega: {{ $pedido->cep_entrega }}
                            @endif
                        </div>

                        <div class="pedido-items">
                            {{ $pedido->items->count() }} {{ $pedido->items->count() === 1 ? 'item' : 'itens' }}:
                            {{ $pedido->items->take(3)->pluck('produto')->implode(', ') }}{{ $pedido->items->count() > 3 ? '...' : '' }}
                        </div>

                        <div style="margin-top: 12px;">
                            <a href="{{ route('customer.order.detail', $pedido->id) }}" class="btn-link" style="font-weight: 600;">
                                Ver detalhes <i class="fa fa-angle-right"></i>
                            </a>
                        </div>
                    </div>
                @endforeach

                {{-- Paginação --}}
                <div style="text-align: center; margin-top: 20px;">
                    {{ $pedidos->links() }}
                </div>
            @endif

        </div>

@endsection
