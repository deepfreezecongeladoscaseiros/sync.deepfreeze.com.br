{{--
    Layout: Área do Cliente (Minha Conta)

    Estrutura duas colunas: sidebar (menu) + conteúdo.
    Todas as páginas da área do cliente estendem este layout.

    Variáveis:
    - $activeMenu: string indicando o item ativo ('dashboard', 'profile', 'password', 'addresses', 'orders')
--}}
@extends('layouts.storefront')

@section('body_class', 'pg-interna')

@section('content')

{{-- Banner Interno --}}
@include('storefront.components.banner-interno', ['title' => 'Minha Conta'])

<main class="pg-internas bg-loja">
    <div class="container">
        <div class="customer-area">
            <div class="row">

                {{-- Sidebar --}}
                <div class="col-xs-12 col-md-3">
                    @php
                        $customer = auth('customer')->user();
                        $nameParts = explode(' ', trim($customer->nome ?? ''));
                        $initials = mb_strtoupper(mb_substr($nameParts[0], 0, 1));
                        if (count($nameParts) > 1) {
                            $initials .= mb_strtoupper(mb_substr(end($nameParts), 0, 1));
                        }
                    @endphp

                    <div class="customer-sidebar">
                        <div class="customer-sidebar-header">
                            <div class="customer-avatar">{{ $initials }}</div>
                            <h4>{{ ucfirst(mb_strtolower($nameParts[0])) }}</h4>
                            <small>{{ $customer->email_primario }}</small>
                        </div>

                        <ul class="customer-nav">
                            <li>
                                <a href="{{ route('customer.dashboard') }}" class="{{ ($activeMenu ?? '') === 'dashboard' ? 'active' : '' }}">
                                    <i class="fa fa-th-large"></i> Dashboard
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('customer.profile') }}" class="{{ ($activeMenu ?? '') === 'profile' ? 'active' : '' }}">
                                    <i class="fa fa-user"></i> Meus Dados
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('customer.password') }}" class="{{ ($activeMenu ?? '') === 'password' ? 'active' : '' }}">
                                    <i class="fa fa-lock"></i> Alterar Senha
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('customer.addresses') }}" class="{{ ($activeMenu ?? '') === 'addresses' ? 'active' : '' }}">
                                    <i class="fa fa-map-marker"></i> Meus Endereços
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('customer.orders') }}" class="{{ ($activeMenu ?? '') === 'orders' ? 'active' : '' }}">
                                    <i class="fa fa-shopping-bag"></i> Meus Pedidos
                                </a>
                            </li>
                            <li class="customer-nav-divider"></li>
                            <li>
                                <a href="{{ route('logout') }}" class="nav-logout"
                                   onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();">
                                    <i class="fa fa-sign-out"></i> Sair
                                </a>
                                <form id="logout-form-sidebar" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Conteúdo --}}
                <div class="col-xs-12 col-md-9">
                    <div class="customer-content">
                        @yield('customer-content')
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>

@endsection
