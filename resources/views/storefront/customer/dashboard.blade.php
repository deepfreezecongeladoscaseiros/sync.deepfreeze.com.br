{{--
    Página: Dashboard — Minha Conta
    Cards de navegação + resumo do cliente.
--}}
@extends('storefront.customer.layout')

@section('title', 'Minha Conta - ' . config('app.name'))

@section('customer-content')

    <h2>Olá, {{ ucfirst(mb_strtolower(explode(' ', $customer->nome)[0])) }}!</h2>
    <p class="section-subtitle">Gerencie suas informações, endereços e pedidos.</p>

    <div class="dashboard-cards">
        <a href="{{ route('customer.profile') }}" class="dash-card">
            <i class="fa fa-user"></i>
            <span>Meus Dados</span>
            <small>Editar nome, email, telefone</small>
        </a>

        <a href="{{ route('customer.password') }}" class="dash-card">
            <i class="fa fa-lock"></i>
            <span>Alterar Senha</span>
            <small>Trocar sua senha de acesso</small>
        </a>

        <a href="{{ route('customer.addresses') }}" class="dash-card">
            <i class="fa fa-map-marker"></i>
            <span>Meus Endereços</span>
            <small>{{ $addressCount }} {{ $addressCount === 1 ? 'endereço' : 'endereços' }} cadastrados</small>
        </a>

        <a href="{{ route('customer.orders') }}" class="dash-card">
            <i class="fa fa-shopping-bag"></i>
            <span>Meus Pedidos</span>
            <small>{{ $orderCount }} {{ $orderCount === 1 ? 'pedido' : 'pedidos' }}</small>
        </a>
    </div>

@endsection
