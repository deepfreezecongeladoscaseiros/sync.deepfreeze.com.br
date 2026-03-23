{{--
    Página de Checkout - Finalizar Pedido

    Dois cenários:
    1. Usuário logado: dados pessoais pré-preenchidos (read-only), endereço editável
    2. Convidado: opção de login/cadastro ou formulário completo (PF/PJ + endereço)

    Reutiliza padrões CSS/JS da página de cadastro e carrinho:
    - Banner .banner-interna, .pg-internas, .form-cadastro-new
    - Tabela .tabela-padrao (resumo read-only)
    - Máscaras de input (CPF, CNPJ, telefone, CEP)
    - Busca ViaCEP
    - Toggle PF/PJ
--}}
@extends('layouts.storefront')

@section('title', 'Finalizar Pedido - ' . config('app.name'))
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

    /* Formulário */
    .form-cadastro-new {
        padding: 40px 0;
    }
    .form-group.has-error .form-control {
        border-color: #e74c3c !important;
    }
    .form-group .help-block {
        color: #e74c3c;
        font-size: 12px;
        margin-top: 4px;
    }

    /* Box de opções para convidado (login/cadastro/continuar como convidado) */
    .box-opcoes-checkout {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 25px;
        margin-bottom: 30px;
        border: 1px solid #eee;
    }
    .box-opcoes-checkout h4 {
        margin: 0 0 15px;
        font-size: 16px;
    }
    .box-opcoes-checkout .opcao-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 0;
        border-bottom: 1px solid #eee;
    }
    .box-opcoes-checkout .opcao-item:last-child {
        border-bottom: none;
    }
    .box-opcoes-checkout .opcao-item .fa {
        width: 20px;
        text-align: center;
        color: var(--color-primary, #013E3B);
    }

    /* Dados do usuário logado (resumo read-only) */
    .box-dados-usuario {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 25px;
        border: 1px solid #eee;
    }
    .box-dados-usuario h4 {
        margin: 0 0 12px;
        font-size: 16px;
        color: var(--color-primary, #013E3B);
    }
    .box-dados-usuario p {
        margin: 4px 0;
        font-size: 14px;
        color: #555;
    }
    .box-dados-usuario p strong {
        color: #333;
    }

    /* Resumo do pedido (read-only) */
    .box-resumo-pedido {
        margin-top: 30px;
    }
    .box-resumo-pedido h3 {
        margin: 0 0 15px;
        font-size: 18px;
    }
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
    .box-total-checkout {
        text-align: right;
        padding: 15px 0;
    }
    .box-total-checkout .total-label {
        font-size: 14px;
        color: #666;
        margin: 0 0 5px 0;
    }
    .box-total-checkout .total-valor {
        font-size: 24px;
        font-weight: 700;
        color: var(--color-primary, #013E3B);
        margin: 0;
    }

    /* Área de botões */
    .box-btn-checkout {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 0 40px;
    }
    .box-btn-checkout .btn-voltar {
        color: var(--color-primary, #013E3B);
        font-weight: 500;
        text-decoration: none;
        font-size: 14px;
    }
    .box-btn-checkout .btn-voltar:hover {
        text-decoration: underline;
    }
    .box-btn-checkout .btn-confirmar {
        padding: 14px 40px;
        font-size: 15px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Alerta de erro */
    .alert-checkout {
        margin-bottom: 20px;
    }

    /* Seção separadora */
    .secao-checkout {
        margin-bottom: 25px;
    }
    .secao-checkout h3 {
        margin-bottom: 15px;
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
                    <h1 class="animated fadeIn">Finalizar Pedido</h1>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Conteúdo principal --}}
<main class="pg-internas form-cadastro-new">
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-lg-8 col-lg-offset-2">

                {{-- Alertas --}}
                @if(session('error'))
                    <div class="alert alert-danger alert-checkout">
                        <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-checkout">
                        <i class="fa fa-exclamation-circle"></i>
                        <strong>Verifique os campos abaixo:</strong>
                        <ul style="margin-top: 5px; margin-bottom: 0;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('checkout.store') }}" id="form-checkout" novalidate>
                    @csrf

                    {{-- ========================================== --}}
                    {{-- CENÁRIO 1: USUÁRIO NÃO LOGADO --}}
                    {{-- ========================================== --}}
                    @guest
                        {{-- Login obrigatório para checkout --}}
                        <div class="box-opcoes-checkout">
                            <h4><i class="fa fa-lock"></i> Identificação necessária</h4>
                            <p style="margin: 10px 0;">Para finalizar sua compra, faça login ou cadastre-se.</p>
                            <div class="opcao-item">
                                <i class="fa fa-sign-in"></i>
                                <span>Já tem conta? <a href="{{ route('login') }}?redirect={{ urlencode(url('/checkout')) }}" style="font-weight: 600;">Faça login</a></span>
                            </div>
                            <div class="opcao-item">
                                <i class="fa fa-user-plus"></i>
                                <span>Ainda não tem conta? <a href="{{ route('register') }}" style="font-weight: 600;">Cadastre-se</a></span>
                            </div>
                        </div>
                    @endguest

                    {{-- ========================================== --}}
                    {{-- CENÁRIO 2: USUÁRIO LOGADO (dados da tabela pessoas) --}}
                    {{-- ========================================== --}}
                    @auth
                        {{-- Resumo dos dados do cliente logado (read-only) --}}
                        <div class="box-dados-usuario">
                            <h4><i class="fa fa-user"></i> Seus dados</h4>
                            <p><strong>Nome:</strong> {{ $user->nome }}</p>
                            <p><strong>E-mail:</strong> {{ $user->email_primario }}</p>
                            <p><strong>Telefone:</strong> {{ $user->telefone_celular ?? $user->telefone_residencial }}</p>
                            @if($user->isPessoaJuridica())
                                <p><strong>CNPJ:</strong> {{ $user->cnpj }}</p>
                                @if($user->razao_social)
                                    <p><strong>Razão Social:</strong> {{ $user->razao_social }}</p>
                                @endif
                            @else
                                <p><strong>CPF:</strong> {{ $user->cpf }}</p>
                            @endif
                        </div>

                    {{-- ========================================== --}}
                    {{-- SEÇÃO: ENDEREÇO DE ENTREGA --}}
                    {{-- ========================================== --}}
                    <div class="secao-checkout">
                        <div class="d-inline">
                            <h3 class="mb-2">Endereço de entrega</h3>

                            {{-- Dropdown de endereços salvos (se o cliente tiver) --}}
                            @if(isset($enderecos) && $enderecos->count() > 0)
                                <div class="row mb-2">
                                    <div class="col-xs-12">
                                        <div class="form-group">
                                            <label for="endereco_salvo">Selecione um endereço salvo</label>
                                            <select id="endereco_salvo" class="form-control" style="border-radius: 30px; height: 45px;">
                                                <option value="">Usar outro endereço (preencher abaixo)</option>
                                                @foreach($enderecos as $endereco)
                                                    <option value="{{ $endereco->id }}"
                                                            data-zip="{{ $endereco->cep }}"
                                                            data-street="{{ $endereco->logradouro }}"
                                                            data-number="{{ $endereco->logradouro_complemento_numero }}"
                                                            data-complement="{{ $endereco->logradouro_complemento }}"
                                                            data-neighborhood="{{ $endereco->bairro }}"
                                                            data-city="{{ $endereco->cidade }}"
                                                            data-state="{{ $endereco->uf }}"
                                                            {{ $endereco->ultimo_endereco_usado ? 'selected' : '' }}>
                                                        {{ $endereco->full_address }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            {{-- Campo hidden para enviar o endereco_id selecionado --}}
                                            <input type="hidden" name="endereco_id" id="endereco_id" value="{{ $enderecos->where('ultimo_endereco_usado', 1)->first()?->id }}">
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="row">
                                {{-- CEP --}}
                                <div class="col-xs-12 col-sm-4">
                                    <div class="form-group @error('shipping_zip_code') has-error @enderror">
                                        <div class="flex-space">
                                            <label for="shipping_zip_code">CEP <span>*</span></label>
                                            <a href="https://buscacepinter.correios.com.br/app/endereco/index.php" target="_blank" class="btn-link">Não sei meu CEP</a>
                                        </div>
                                        <div class="group">
                                            @php
                                                // Pré-preenche com último endereço usado ou primeiro endereço
                                                $defaultEndereco = isset($enderecos) ? ($enderecos->where('ultimo_endereco_usado', 1)->first() ?? $enderecos->first()) : null;
                                            @endphp
                                            <input type="tel" name="shipping_zip_code" id="shipping_zip_code"
                                                   value="{{ old('shipping_zip_code', $defaultEndereco?->cep ?? '') }}"
                                                   class="form-control js-cep" placeholder="_____-___">
                                            <span class="loading"><i class="fa fa-spinner fa-spin" id="cep-loading" style="display: none;"></i></span>
                                        </div>
                                        @error('shipping_zip_code')
                                            <span class="help-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Campos de endereço --}}
                            @php
                                $showAddress = old('shipping_address') || $defaultEndereco;
                            @endphp
                            <div id="box_endereco" class="row mt-2" style="display: {{ $showAddress ? 'flex' : 'none' }};">
                                {{-- Endereço --}}
                                <div class="col-xs-12 col-sm-6">
                                    <div class="form-group @error('shipping_address') has-error @enderror">
                                        <label for="shipping_address">Endereço <span>*</span></label>
                                        <input type="text" name="shipping_address" id="shipping_address"
                                               value="{{ old('shipping_address', $defaultEndereco?->logradouro ?? '') }}"
                                               class="form-control" maxlength="255">
                                        @error('shipping_address')
                                            <span class="help-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Número --}}
                                <div class="col-xs-4 col-sm-2">
                                    <div class="form-group @error('shipping_number') has-error @enderror">
                                        <label for="shipping_number">Número <span>*</span></label>
                                        <input type="tel" name="shipping_number" id="shipping_number"
                                               value="{{ old('shipping_number', $defaultEndereco?->logradouro_complemento_numero ?? '') }}"
                                               class="form-control" maxlength="20">
                                        @error('shipping_number')
                                            <span class="help-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Complemento --}}
                                <div class="col-xs-8 col-sm-4">
                                    <div class="form-group">
                                        <label for="shipping_complement">Complemento</label>
                                        <input type="text" name="shipping_complement" id="shipping_complement"
                                               value="{{ old('shipping_complement', $defaultEndereco?->logradouro_complemento ?? '') }}"
                                               class="form-control" maxlength="100">
                                    </div>
                                </div>

                                {{-- Bairro --}}
                                <div class="col-xs-12 col-sm-4">
                                    <div class="form-group @error('shipping_neighborhood') has-error @enderror">
                                        <label for="shipping_neighborhood">Bairro <span>*</span></label>
                                        <input type="text" name="shipping_neighborhood" id="shipping_neighborhood"
                                               value="{{ old('shipping_neighborhood', $defaultEndereco?->bairro ?? '') }}"
                                               class="form-control" maxlength="100">
                                        @error('shipping_neighborhood')
                                            <span class="help-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Cidade --}}
                                <div class="col-xs-6 col-sm-4">
                                    <div class="form-group @error('shipping_city') has-error @enderror">
                                        <label for="shipping_city">Cidade <span>*</span></label>
                                        <input type="text" name="shipping_city" id="shipping_city"
                                               value="{{ old('shipping_city', $defaultEndereco?->cidade ?? '') }}"
                                               class="form-control" maxlength="100">
                                        @error('shipping_city')
                                            <span class="help-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Estado --}}
                                <div class="col-xs-6 col-sm-4">
                                    <div class="form-group @error('shipping_state') has-error @enderror">
                                        <label for="shipping_state">Estado <span>*</span></label>
                                        @php
                                            $selectedState = old('shipping_state', $defaultEndereco?->uf ?? '');
                                            $estados = [
                                                'AC' => 'Acre', 'AL' => 'Alagoas', 'AP' => 'Amapá', 'AM' => 'Amazonas',
                                                'BA' => 'Bahia', 'CE' => 'Ceará', 'DF' => 'Distrito Federal', 'ES' => 'Espírito Santo',
                                                'GO' => 'Goiás', 'MA' => 'Maranhão', 'MT' => 'Mato Grosso', 'MS' => 'Mato Grosso do Sul',
                                                'MG' => 'Minas Gerais', 'PA' => 'Pará', 'PB' => 'Paraíba', 'PR' => 'Paraná',
                                                'PE' => 'Pernambuco', 'PI' => 'Piauí', 'RJ' => 'Rio de Janeiro', 'RN' => 'Rio Grande do Norte',
                                                'RS' => 'Rio Grande do Sul', 'RO' => 'Rondônia', 'RR' => 'Roraima', 'SC' => 'Santa Catarina',
                                                'SP' => 'São Paulo', 'SE' => 'Sergipe', 'TO' => 'Tocantins',
                                            ];
                                        @endphp
                                        <select name="shipping_state" id="shipping_state" class="form-control">
                                            <option value="">...</option>
                                            @foreach ($estados as $uf => $nome)
                                                <option value="{{ $uf }}" {{ $selectedState == $uf ? 'selected' : '' }}>{{ $nome }}</option>
                                            @endforeach
                                        </select>
                                        @error('shipping_state')
                                            <span class="help-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endauth

                    {{-- ========================================== --}}
                    {{-- SEÇÃO: CUPOM DE DESCONTO --}}
                    {{-- ========================================== --}}
                    @auth
                    <div class="secao-checkout">
                        <div class="d-inline">
                            <h3 class="mb-2">Cupom de desconto</h3>
                            <div class="row">
                                <div class="col-xs-12 col-sm-6">
                                    <div class="form-group" style="display: flex; gap: 10px;">
                                        <input type="text" name="coupon_code" id="coupon_code"
                                               value="{{ old('coupon_code') }}"
                                               class="form-control" placeholder="Digite o código do cupom"
                                               style="text-transform: uppercase;" maxlength="50">
                                        <button type="button" id="btn-aplicar-cupom" class="btn btn-confirmar" style="white-space: nowrap; padding: 0 20px;">
                                            <i class="fa fa-tag"></i> Aplicar
                                        </button>
                                    </div>
                                    {{-- Feedback do cupom --}}
                                    <div id="coupon_feedback" style="display: none; padding: 8px 12px; border-radius: 6px; font-size: 13px; margin-top: -5px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endauth

                    {{-- ========================================== --}}
                    {{-- SEÇÃO: OBSERVAÇÕES --}}
                    {{-- ========================================== --}}
                    <div class="secao-checkout">
                        <div class="d-inline">
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="form-group">
                                        <label for="notes">Observações do pedido</label>
                                        <textarea name="notes" id="notes" class="form-control" rows="3"
                                                  maxlength="1000" placeholder="Informações adicionais sobre o pedido (opcional)"
                                                  style="height: auto; border-radius: 15px; padding: 15px 20px;">{{ old('notes') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ========================================== --}}
                    {{-- SEÇÃO: TIPO DE ENTREGA --}}
                    {{-- ========================================== --}}
                    @auth
                    <div class="secao-checkout">
                        <div class="d-inline">
                            <h3 class="mb-2">Como deseja receber?</h3>
                            @error('tipo_entrega')
                                <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> {{ $message }}</div>
                            @enderror

                            {{-- Toggle Entrega / Retirada --}}
                            <div class="flex-check mb-2" style="gap: 15px;">
                                <div class="check-custom mod-2">
                                    <label class="{{ old('tipo_entrega', 'delivery') == 'delivery' ? 'active' : '' }}" style="padding: 12px 20px; border-radius: 10px; border: 2px solid {{ old('tipo_entrega', 'delivery') == 'delivery' ? 'var(--color-primary, #013E3B)' : '#ddd' }}; cursor: pointer;">
                                        <input type="radio" name="tipo_entrega" value="delivery"
                                               {{ old('tipo_entrega', 'delivery') == 'delivery' ? 'checked' : '' }}>
                                        <i class="fa fa-truck"></i> Receber em casa
                                    </label>
                                </div>
                                <div class="check-custom mod-2">
                                    <label class="{{ old('tipo_entrega') == 'pickup' ? 'active' : '' }}" style="padding: 12px 20px; border-radius: 10px; border: 2px solid {{ old('tipo_entrega') == 'pickup' ? 'var(--color-primary, #013E3B)' : '#ddd' }}; cursor: pointer;">
                                        <input type="radio" name="tipo_entrega" value="pickup"
                                               {{ old('tipo_entrega') == 'pickup' ? 'checked' : '' }}>
                                        <i class="fa fa-shopping-bag"></i> Retirar na loja
                                    </label>
                                </div>
                            </div>

                            {{-- DELIVERY: Períodos de entrega (carregado via AJAX após CEP) --}}
                            <div id="box_delivery" style="display: {{ old('tipo_entrega', 'delivery') == 'delivery' ? 'block' : 'none' }};">
                                <div id="delivery_slots_container">
                                    <p style="color: #888; font-size: 13px;"><i class="fa fa-info-circle"></i> Preencha o CEP acima para ver os horários de entrega disponíveis.</p>
                                </div>
                                <input type="hidden" name="veiculo_periodo_id" id="veiculo_periodo_id" value="{{ old('veiculo_periodo_id') }}">
                                <input type="hidden" name="data_entrega" id="data_entrega" value="{{ old('data_entrega') }}">

                                {{-- Valor do frete calculado --}}
                                <div id="box_frete" style="display: none; margin-top: 15px; padding: 12px 16px; background: #f0f9f0; border-radius: 8px; border: 1px solid #c3e6cb;">
                                    <strong>Frete:</strong> <span id="valor_frete_display">-</span>
                                    <span id="frete_gratis_badge" style="display: none; background: #28a745; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 12px; margin-left: 8px;">GRÁTIS</span>
                                </div>
                            </div>

                            {{-- PICKUP: Seleção de loja --}}
                            <div id="box_pickup" style="display: {{ old('tipo_entrega') == 'pickup' ? 'block' : 'none' }};">
                                <div id="pickup_stores_container">
                                    <p style="color: #888;"><i class="fa fa-spinner fa-spin"></i> Carregando lojas...</p>
                                </div>
                                <input type="hidden" name="loja_retirada_id" id="loja_retirada_id" value="{{ old('loja_retirada_id') }}">
                                <input type="hidden" name="data_retirada" id="data_retirada" value="{{ old('data_retirada') }}">
                            </div>
                        </div>
                    </div>
                    @endauth

                    {{-- ========================================== --}}
                    {{-- SEÇÃO: FORMA DE PAGAMENTO --}}
                    {{-- ========================================== --}}
                    @auth
                    <div class="secao-checkout">
                        <div class="d-inline">
                            <h3 class="mb-2">Forma de pagamento</h3>
                            @error('formas_pagamento_id')
                                <div class="alert alert-danger" style="margin-bottom: 15px;">
                                    <i class="fa fa-exclamation-circle"></i> {{ $message }}
                                </div>
                            @enderror

                            @if(isset($paymentMethods) && $paymentMethods->count() > 0)
                                <div class="row">
                                    @foreach($paymentMethods as $method)
                                        <div class="col-xs-12 col-sm-6" style="margin-bottom: 10px;">
                                            <label class="forma-pagamento-option {{ old('formas_pagamento_id') == $method->id ? 'active' : '' }}"
                                                   style="display: flex; align-items: center; gap: 12px; padding: 15px; border: 2px solid {{ old('formas_pagamento_id') == $method->id ? 'var(--color-primary, #013E3B)' : '#ddd' }}; border-radius: 10px; cursor: pointer; transition: all 0.2s; background: {{ old('formas_pagamento_id') == $method->id ? '#f0f9f0' : '#fff' }};">
                                                <input type="radio" name="formas_pagamento_id" value="{{ $method->id }}"
                                                       {{ old('formas_pagamento_id') == $method->id ? 'checked' : '' }}
                                                       style="margin: 0;">
                                                <i class="fa {{ \App\Services\PaymentService::getIcon($method->id) }}" style="font-size: 20px; color: var(--color-primary, #013E3B); width: 24px; text-align: center;"></i>
                                                <div>
                                                    <strong style="display: block; font-size: 14px;">{{ $method->nome }}</strong>
                                                    @if(\App\Services\PaymentService::getDescription($method->id))
                                                        <small style="color: #888;">{{ \App\Services\PaymentService::getDescription($method->id) }}</small>
                                                    @endif
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p style="color: #999;">Nenhuma forma de pagamento disponível no momento.</p>
                            @endif
                        </div>
                    </div>
                    @endauth

                    {{-- ========================================== --}}
                    {{-- SEÇÃO: RESUMO DO PEDIDO (read-only) --}}
                    {{-- ========================================== --}}
                    <div class="box-resumo-pedido">
                        <h3>Resumo do Pedido</h3>
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
                                @foreach($cartItems as $item)
                                    <tr>
                                        <td style="width: 60px;">
                                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="img-produto img-responsive">
                                        </td>
                                        <td>{{ $item['name'] }}</td>
                                        <td>{{ \App\Models\Product::formatPrice($item['price']) }}</td>
                                        <td>{{ $item['quantity'] }}</td>
                                        <td>{{ \App\Models\Product::formatPrice($item['price'] * $item['quantity']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        {{-- Totais --}}
                        <div class="box-total-checkout">
                            <p class="total-label">Subtotal</p>
                            <p class="total-valor">{{ \App\Models\Product::formatPrice($subtotal) }}</p>
                            <p style="font-size: 12px; color: #999; margin-top: 5px;">Frete a combinar</p>
                        </div>
                    </div>

                    {{-- ========================================== --}}
                    {{-- BOTÕES --}}
                    {{-- ========================================== --}}
                    <div class="box-btn-checkout">
                        <a href="{{ url('/carrinho') }}" class="btn-voltar">
                            <i class="fa fa-caret-right" style="transform: rotate(180deg);"></i>
                            Voltar ao carrinho
                        </a>
                        <button type="submit" class="btn btn-confirmar" id="btn-confirmar-pedido">
                            Confirmar Pedido <i class="fa fa-spinner fa-spin ml-1 js-load-checkout" style="display: none"></i>
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</main>

@endsection

@push('scripts')
<script>
$(document).ready(function() {

    // ==========================================
    // TOGGLE ENTREGA / RETIRADA
    // ==========================================
    $('input[name="tipo_entrega"]').on('change', function() {
        var tipo = $(this).val();

        // Atualiza visual
        $(this).closest('.flex-check').find('label').removeClass('active').css('border-color', '#ddd');
        $(this).closest('label').addClass('active').css('border-color', 'var(--color-primary, #013E3B)');

        if (tipo === 'delivery') {
            $('#box_delivery').show();
            $('#box_pickup').hide();
            // Se CEP já preenchido, carregar slots
            var cep = $('#shipping_zip_code').val().replace(/\D/g, '');
            if (cep.length === 8) {
                loadDeliverySlots(cep);
                loadShippingCost(cep);
            }
        } else {
            $('#box_delivery').hide();
            $('#box_pickup').show();
            $('#box_frete').hide();
            loadPickupStores();
        }
    });

    // ==========================================
    // CARREGAR SLOTS DE ENTREGA (AJAX)
    // ==========================================
    function loadDeliverySlots(cep) {
        $('#delivery_slots_container').html('<p style="color: #888;"><i class="fa fa-spinner fa-spin"></i> Buscando horários...</p>');

        $.ajax({
            url: '{{ route("shipping.slots") }}',
            data: { cep: cep },
            dataType: 'json',
            success: function(data) {
                if (!data.disponivel || !data.dias.length) {
                    $('#delivery_slots_container').html('<p style="color: #e74c3c;"><i class="fa fa-exclamation-circle"></i> Não há horários de entrega disponíveis para este CEP.</p>');
                    return;
                }

                // Gera HTML dos slots agrupados por data
                var html = '<label style="font-weight: 600; margin-bottom: 10px; display: block;">Selecione a data e horário:</label>';
                html += '<div style="max-height: 300px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; padding: 10px;">';

                data.dias.forEach(function(dia) {
                    html += '<div style="margin-bottom: 8px;">';
                    html += '<strong style="font-size: 13px; color: var(--color-primary, #013E3B);">' + dia.date_formatted + '</strong>';
                    dia.slots.forEach(function(slot) {
                        if (!slot.veiculo_periodo_id) return;
                        var checked = ($('#veiculo_periodo_id').val() == slot.veiculo_periodo_id) ? 'checked' : '';
                        html += '<label style="display: block; padding: 6px 12px; cursor: pointer; border-radius: 6px; margin: 2px 0;" ' +
                                'onmouseover="this.style.background=\'#f0f9f0\'" onmouseout="this.style.background=\'transparent\'">';
                        html += '<input type="radio" name="slot_entrega" value="' + slot.veiculo_periodo_id + '|' + dia.date + '" ' + checked + ' style="margin-right: 8px;">';
                        html += '<i class="fa fa-clock-o"></i> ' + slot.time_formatted;
                        html += '</label>';
                    });
                    html += '</div>';
                });

                html += '</div>';
                $('#delivery_slots_container').html(html);

                // Ao selecionar um slot, preenche os hidden fields
                $(document).off('change', 'input[name="slot_entrega"]').on('change', 'input[name="slot_entrega"]', function() {
                    var parts = $(this).val().split('|');
                    $('#veiculo_periodo_id').val(parts[0]);
                    $('#data_entrega').val(parts[1]);
                });
            },
            error: function() {
                $('#delivery_slots_container').html('<p style="color: #e74c3c;">Erro ao buscar horários. Tente novamente.</p>');
            }
        });
    }

    // ==========================================
    // CARREGAR VALOR DO FRETE (AJAX)
    // ==========================================
    function loadShippingCost(cep) {
        var subtotal = {{ $subtotal ?? 0 }};
        var formaPagId = $('input[name="formas_pagamento_id"]:checked').val() || '';

        $.ajax({
            url: '{{ route("shipping.calculate") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                cep: cep,
                subtotal: subtotal,
                formas_pagamento_id: formaPagId
            },
            dataType: 'json',
            success: function(data) {
                if (data.frete_gratis) {
                    $('#valor_frete_display').text('R$ 0,00');
                    $('#frete_gratis_badge').show();
                } else {
                    $('#valor_frete_display').text(data.valor_formatado);
                    $('#frete_gratis_badge').hide();
                }
                $('#box_frete').show();
            }
        });
    }

    // ==========================================
    // CARREGAR LOJAS PARA RETIRADA (AJAX)
    // ==========================================
    function loadPickupStores() {
        $.ajax({
            url: '{{ route("shipping.pickup_stores") }}',
            dataType: 'json',
            success: function(data) {
                if (!data.lojas || !data.lojas.length) {
                    $('#pickup_stores_container').html('<p style="color: #888;">Nenhuma loja disponível para retirada.</p>');
                    return;
                }

                var html = '<label style="font-weight: 600; margin-bottom: 10px; display: block;">Selecione a loja:</label>';
                data.lojas.forEach(function(loja) {
                    var checked = ($('#loja_retirada_id').val() == loja.id) ? 'checked' : '';
                    html += '<label style="display: flex; align-items: flex-start; gap: 10px; padding: 12px; border: 1px solid #eee; border-radius: 8px; margin-bottom: 8px; cursor: pointer;">';
                    html += '<input type="radio" name="loja_pickup" value="' + loja.id + '" ' + checked + ' style="margin-top: 3px;">';
                    html += '<div>';
                    html += '<strong>' + loja.nome + '</strong><br>';
                    html += '<small style="color: #888;">' + loja.endereco + '</small>';
                    if (loja.horarios && loja.horarios.weekdays) {
                        html += '<br><small style="color: #888;"><i class="fa fa-clock-o"></i> Seg-Sex: ' + loja.horarios.weekdays + '</small>';
                    }
                    html += '</div></label>';
                });
                $('#pickup_stores_container').html(html);

                // Ao selecionar loja, preenche hidden
                $(document).off('change', 'input[name="loja_pickup"]').on('change', 'input[name="loja_pickup"]', function() {
                    $('#loja_retirada_id').val($(this).val());
                });
            }
        });
    }

    // Se tipo pickup já selecionado (old values), carregar lojas
    if ($('input[name="tipo_entrega"]:checked').val() === 'pickup') {
        loadPickupStores();
    }

    // ==========================================
    // CUPOM DE DESCONTO (AJAX)
    // ==========================================
    $('#btn-aplicar-cupom').on('click', function() {
        var code = $('#coupon_code').val().trim();
        if (!code) {
            showCouponFeedback('Informe o código do cupom.', false);
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: '{{ route("coupon.validate") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                code: code,
                subtotal: {{ $subtotal ?? 0 }},
                formas_pagamento_id: $('input[name="formas_pagamento_id"]:checked').val() || ''
            },
            dataType: 'json',
            success: function(data) {
                showCouponFeedback(data.message, data.valid);
            },
            error: function() {
                showCouponFeedback('Erro ao validar cupom. Tente novamente.', false);
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fa fa-tag"></i> Aplicar');
            }
        });
    });

    // Enter no campo de cupom aciona validação
    $('#coupon_code').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#btn-aplicar-cupom').click();
        }
    });

    function showCouponFeedback(message, isValid) {
        var $feedback = $('#coupon_feedback');
        $feedback.show()
            .css({
                'background': isValid ? '#d4edda' : '#f8d7da',
                'color': isValid ? '#155724' : '#721c24',
                'border': '1px solid ' + (isValid ? '#c3e6cb' : '#f5c6cb')
            })
            .html((isValid ? '<i class="fa fa-check-circle"></i> ' : '<i class="fa fa-times-circle"></i> ') + message);
    }

    // ==========================================
    // SELEÇÃO DE FORMA DE PAGAMENTO
    // Highlight visual ao selecionar
    // ==========================================
    $('input[name="formas_pagamento_id"]').on('change', function() {
        // Remove highlight de todas as opções
        $('.forma-pagamento-option').css({
            'border-color': '#ddd',
            'background': '#fff'
        }).removeClass('active');

        // Aplica highlight na opção selecionada
        $(this).closest('.forma-pagamento-option').css({
            'border-color': 'var(--color-primary, #013E3B)',
            'background': '#f0f9f0'
        }).addClass('active');
    });

    // ==========================================
    // SELEÇÃO DE ENDEREÇO SALVO
    // Ao selecionar no dropdown, preenche os campos automaticamente
    // ==========================================
    $('#endereco_salvo').on('change', function() {
        var $selected = $(this).find(':selected');
        var enderecoId = $(this).val();

        // Atualiza campo hidden com ID do endereço selecionado
        $('#endereco_id').val(enderecoId);

        if (enderecoId) {
            // Preenche campos com dados do endereço salvo (via data attributes)
            var cepSalvo = $selected.data('zip') || '';
            $('#shipping_zip_code').val(cepSalvo);
            $('#shipping_address').val($selected.data('street') || '');
            $('#shipping_number').val($selected.data('number') || '');
            $('#shipping_complement').val($selected.data('complement') || '');
            $('#shipping_neighborhood').val($selected.data('neighborhood') || '');
            $('#shipping_city').val($selected.data('city') || '');
            $('#shipping_state').val($selected.data('state') || '');

            // Exibe bloco de endereço
            $('#box_endereco').show();

            // Carrega slots de entrega e frete para o CEP do endereço salvo
            var cepLimpo = cepSalvo.toString().replace(/\D/g, '');
            if (cepLimpo.length === 8 && $('input[name="tipo_entrega"]:checked').val() === 'delivery') {
                loadDeliverySlots(cepLimpo);
                loadShippingCost(cepLimpo);
            }
        } else {
            // "Usar outro endereço" — limpa campos para preenchimento manual
            $('#shipping_zip_code').val('');
            $('#shipping_address').val('');
            $('#shipping_number').val('');
            $('#shipping_complement').val('');
            $('#shipping_neighborhood').val('');
            $('#shipping_city').val('');
            $('#shipping_state').val('');

            // Esconde bloco até digitar CEP
            $('#box_endereco').hide();
        }
    });

    // ==========================================
    // MÁSCARAS DE INPUT
    // ==========================================
    if (typeof $.fn.inputmask !== 'undefined') {
        $('.js-cpf').inputmask('999.999.999-99');
        $('.js-cnpj').inputmask('99.999.999/9999-99');
        $('.js-phone-mask').inputmask({
            mask: ['(99) 9999-9999', '(99) 99999-9999'],
            keepStatic: true
        });
        $('.js-cep').inputmask('99999-999');
    }

    // ==========================================
    // BUSCA DE CEP VIA API VIACEP
    // Usa IDs com prefixo shipping_ (diferente da página de cadastro)
    // ==========================================
    $('#shipping_zip_code').on('blur', function() {
        var cep = $(this).val().replace(/\D/g, '');

        if (cep.length !== 8) {
            return;
        }

        $('#cep-loading').show();

        $.ajax({
            url: 'https://viacep.com.br/ws/' + cep + '/json/',
            dataType: 'json',
            timeout: 10000,
            success: function(data) {
                if (data.erro) {
                    if (typeof swal !== 'undefined') {
                        swal({
                            title: 'CEP não encontrado',
                            text: 'Verifique o CEP informado e tente novamente.',
                            type: 'warning'
                        });
                    }
                    return;
                }

                // Preenche campos de endereço com prefixo shipping_
                $('#shipping_address').val(data.logradouro);
                $('#shipping_neighborhood').val(data.bairro);
                $('#shipping_city').val(data.localidade);
                $('#shipping_state').val(data.uf);

                // Limpa seleção de endereço salvo (novo endereço via CEP)
                $('#endereco_salvo').val('');
                $('#endereco_id').val('');

                // Carrega slots de entrega e frete para o novo CEP
                if ($('input[name="tipo_entrega"]:checked').val() === 'delivery') {
                    loadDeliverySlots(cep);
                    loadShippingCost(cep);
                }

                // Exibe o bloco de endereço e foca no número
                $('#box_endereco').show();
                $('#shipping_number').focus();
            },
            error: function() {
                if (typeof swal !== 'undefined') {
                    swal({
                        title: 'Erro',
                        text: 'Não foi possível buscar o CEP. Tente novamente.',
                        type: 'error'
                    });
                }
            },
            complete: function() {
                $('#cep-loading').hide();
            }
        });
    });

    // Se já houver endereço preenchido (old values ou dados do usuário), exibe o bloco
    if ($('#shipping_address').val()) {
        $('#box_endereco').show();
    }

    // ==========================================
    // SUBMIT DO FORMULÁRIO
    // Feedback visual + proteção contra duplo-clique
    // ==========================================
    $('#form-checkout').on('submit', function() {
        var $btn = $('#btn-confirmar-pedido');
        $btn.prop('disabled', true);
        $btn.find('.js-load-checkout').show();
    });

});
</script>
@endpush
