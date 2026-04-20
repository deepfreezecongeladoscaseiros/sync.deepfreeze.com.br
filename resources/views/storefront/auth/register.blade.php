{{--
    Página de Cadastro de Cliente
    Usa o layout storefront para herdar header, footer, CSS e JS base.
    Formulário completo com suporte a Pessoa Física e Jurídica,
    busca de CEP via ViaCEP, e máscaras de input.
--}}
@extends('layouts.storefront')

@section('title', 'Cadastre-se - ' . config('app.name'))
@section('body_class', 'pg-interna')

@push('styles')
<style>
    /*
     * Estilos específicos da página de cadastro.
     * A maioria dos estilos vem do tema (style.css / style-extend.css):
     * - .form-control já tem border-radius:30px, height:45px, padding:0 20px
     * - .form-cadastro-new já tem position:relative, display:inline-block, width:100%
     * - .check-custom.mod-2 label já tem border-radius:30px, padding:8px 16px
     * - .check-custom.mod-2 label.active já tem background-color:#FFA733, color:#FFF
     * - .flex-check já tem display:inline-flex, align-items:center, justify-content:center
     * - .d-inline já tem display:inline-block, width:100%
     * - .form-text já tem font-size:80%, color:#e18307
     * - .form-group label span, h6 span já tem color:#f53126
     * Aqui definimos APENAS o que o tema NÃO cobre.
     */

    /* Banner interno — mesmo padrão da página de contato */
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

    /* Padding geral da área do formulário */
    .form-cadastro-new {
        padding: 40px 0;
    }

    /* Feedback de erro nos campos (validação Laravel) */
    .form-group.has-error .form-control {
        border-color: #e74c3c !important;
    }
    .form-group .help-block {
        color: #e74c3c;
        font-size: 12px;
        margin-top: 4px;
    }

    /* Alerta geral de erro */
    .alert-cadastro {
        margin-bottom: 20px;
    }
</style>
@endpush

@section('content')

{{-- Banner Interno --}}
<section class="banner-interna small" style="background-image: url('{{ asset('storefront/img/ban-interna-1.jpg') }}');">
    <div class="pg-titulo">
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <h1 class="animated fadeIn">Cadastre-se</h1>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Conteúdo Principal --}}
<main class="pg-internas form-cadastro-new">
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-lg-6 col-lg-offset-3">

                {{-- Cabeçalho do formulário --}}
                <div class="d-inline mb-3">
                    <div class="flex-space">
                        <div class="group">
                            <h3>Crie sua conta</h3>
                            <h6 class="clear">Preencha os dados abaixo para se cadastrar no site</h6>
                        </div>
                        <h6 class="clear"><span>*</span> Campos obrigatórios.</h6>
                    </div>
                </div>

                {{-- Alerta de erro geral --}}
                @if ($errors->any())
                    <div class="alert alert-danger alert-cadastro">
                        <i class="fa fa-exclamation-circle"></i>
                        <strong>Verifique os campos abaixo:</strong>
                        <ul style="margin-top: 5px; margin-bottom: 0;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Formulário de cadastro --}}
                <form method="POST" action="{{ route('register') }}" id="form-cadastro" novalidate>
                    @csrf

                    {{-- ========================================== --}}
                    {{-- SEÇÃO: E-mail e Senha --}}
                    {{-- ========================================== --}}
                    <div class="d-inline mb-3 d-senha">
                        <div class="row">
                            {{-- E-mail --}}
                            <div class="col-xs-12 col-sm-7">
                                <div class="form-group @error('email') has-error @enderror">
                                    <label for="email">Seu e-mail <span>*</span></label>
                                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                                           class="form-control" maxlength="100" required>
                                    @error('email')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text">A confirmação e o acompanhamento de seu pedido serão enviados ao seu e-mail cadastrado.</small>
                                </div>
                            </div>

                            {{-- Senha --}}
                            <div class="col-xs-12 col-sm-5">
                                <div class="form-group @error('password') has-error @enderror">
                                    <label for="password">Sua senha <span>*</span></label>
                                    <input type="password" name="password" id="password"
                                           class="form-control" maxlength="100" required>
                                    <i class="fa fa-eye password js-toggle-password"></i>
                                    @error('password')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text">Use oito ou mais caracteres com uma combinação de letras, números e símbolos.</small>
                                </div>
                            </div>

                            {{-- Confirmar Senha — logo abaixo do campo de senha --}}
                            <div class="col-xs-12 col-sm-5 col-sm-offset-7" style="margin-top: -5px;">
                                <div class="form-group">
                                    <label for="password_confirmation">Confirmar senha <span>*</span></label>
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                           class="form-control" maxlength="100" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ========================================== --}}
                    {{-- SEÇÃO: Perfil (Pessoa Física / Jurídica) --}}
                    {{-- ========================================== --}}
                    <div class="d-inline">
                        <h3 class="mb-2 titulo-perfil">Qual seu perfil?</h3>

                        {{-- Toggle Pessoa Física / Jurídica --}}
                        <div class="flex-check mb-2 check-perfil">
                            <div class="check-custom mod-2 check-fisica">
                                <label class="{{ old('person_type', 'fisica') == 'fisica' ? 'active' : '' }}">
                                    <input type="radio" name="person_type" value="fisica"
                                           {{ old('person_type', 'fisica') == 'fisica' ? 'checked' : '' }}>
                                    <i class="fa fa-user"></i> Pessoa Física
                                </label>
                            </div>
                            <div class="check-custom mod-2 check-juridica">
                                <label class="{{ old('person_type') == 'juridica' ? 'active' : '' }}">
                                    <input type="radio" name="person_type" value="juridica"
                                           {{ old('person_type') == 'juridica' ? 'checked' : '' }}>
                                    <i class="fa fa-building"></i> Pessoa Jurídica
                                </label>
                            </div>
                        </div>

                        {{-- Campos Pessoa Física --}}
                        <div id="form_fisica" style="display: {{ old('person_type', 'fisica') == 'fisica' ? 'block' : 'none' }};">
                            <div class="row">
                                <div class="col-xs-12 col-sm-4">
                                    <div class="form-group @error('name') has-error @enderror">
                                        <label for="name">Nome <span>*</span></label>
                                        <input type="text" name="name" id="name" value="{{ old('name') }}"
                                               class="form-control" maxlength="100">
                                        @error('name')
                                            <span class="help-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-4">
                                    <div class="form-group @error('surname') has-error @enderror">
                                        <label for="surname">Sobrenome <span>*</span></label>
                                        <input type="text" name="surname" id="surname" value="{{ old('surname') }}"
                                               class="form-control" maxlength="100">
                                        @error('surname')
                                            <span class="help-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-xs-6 col-sm-4">
                                    <div class="form-group @error('cpf') has-error @enderror">
                                        <label for="cpf">CPF <span>*</span></label>
                                        <input type="tel" name="cpf" id="cpf" value="{{ old('cpf') }}"
                                               class="form-control js-cpf" placeholder="___.___.___-__">
                                        @error('cpf')
                                            <span class="help-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Campos Pessoa Jurídica --}}
                        <div id="form_juridica" style="display: {{ old('person_type') == 'juridica' ? 'block' : 'none' }};">
                            <div class="row">
                                <div class="col-xs-12 col-sm-4">
                                    <div class="form-group @error('company_name') has-error @enderror">
                                        <label for="company_name">Nome da empresa <span>*</span></label>
                                        <input type="text" name="company_name" id="company_name" value="{{ old('company_name') }}"
                                               class="form-control" maxlength="100">
                                        @error('company_name')
                                            <span class="help-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-4">
                                    <div class="form-group @error('name') has-error @enderror">
                                        <label for="name_pj">Nome responsável <span>*</span></label>
                                        <input type="text" name="name" id="name_pj" value="{{ old('name') }}"
                                               class="form-control js-name-pj" maxlength="100">
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-4">
                                    <div class="form-group @error('surname') has-error @enderror">
                                        <label for="surname_pj">Sobrenome <span>*</span></label>
                                        <input type="text" name="surname" id="surname_pj" value="{{ old('surname') }}"
                                               class="form-control js-surname-pj" maxlength="100">
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-4">
                                    <div class="form-group @error('cnpj') has-error @enderror">
                                        <label for="cnpj">CNPJ <span>*</span></label>
                                        <input type="tel" name="cnpj" id="cnpj" value="{{ old('cnpj') }}"
                                               class="form-control js-cnpj" placeholder="__.___.___/____-__">
                                        @error('cnpj')
                                            <span class="help-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-8">
                                    <div class="form-group @error('state_registration') has-error @enderror">
                                        <div class="flex-space">
                                            <label for="state_registration">Inscrição estadual</label>
                                            <div class="check-custom isento">
                                                <label>
                                                    <input type="checkbox" id="isento" name="isento" value="1">
                                                    <i class="fa fa-square-o"></i><i class="fa fa-check-square-o"></i> Isento
                                                </label>
                                            </div>
                                        </div>
                                        <input type="text" name="state_registration" id="state_registration"
                                               value="{{ old('state_registration') }}" class="form-control" maxlength="15">
                                        @error('state_registration')
                                            <span class="help-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ========================================== --}}
                    {{-- SEÇÃO: Dados pessoais --}}
                    {{-- ========================================== --}}
                    <div class="d-inline mb-3">
                        <div class="row">
                            {{-- Gênero --}}
                            <div class="col-xs-6 col-sm-4">
                                <div class="form-group @error('gender') has-error @enderror">
                                    <label for="gender">Gênero <span>*</span></label>
                                    <select name="gender" id="gender" class="form-control">
                                        <option value="">...</option>
                                        <option value="f" {{ old('gender') == 'f' ? 'selected' : '' }}>Feminino</option>
                                        <option value="m" {{ old('gender') == 'm' ? 'selected' : '' }}>Masculino</option>
                                    </select>
                                    @error('gender')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Data de nascimento --}}
                            <div class="col-xs-6 col-sm-4">
                                <div class="form-group @error('birth_date') has-error @enderror">
                                    <label for="birth_date">Data de nascimento</label>
                                    <input type="tel" name="birth_date" id="birth_date" value="{{ old('birth_date') }}"
                                           class="form-control js-date-mask" placeholder="__/__/____">
                                    @error('birth_date')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Telefone --}}
                            <div class="col-xs-6 col-sm-4">
                                <div class="form-group @error('phone') has-error @enderror">
                                    <label for="phone">Celular ou telefone fixo <span>*</span></label>
                                    <input type="tel" name="phone" id="phone" value="{{ old('phone') }}"
                                           class="form-control js-phone-mask" placeholder="(XX) XXXXX-XXXX">
                                    @error('phone')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ========================================== --}}
                    {{-- SEÇÃO: Endereço --}}
                    {{-- ========================================== --}}
                    <div class="d-inline">
                        <h3 class="mb-2">Seu endereço</h3>
                        <div class="row">
                            {{-- CEP --}}
                            <div class="col-xs-12 col-sm-4">
                                <div class="form-group @error('zip_code') has-error @enderror">
                                    <div class="flex-space">
                                        <label for="zip_code">CEP <span>*</span></label>
                                        <a href="https://buscacepinter.correios.com.br/app/endereco/index.php" target="_blank" class="btn-link">Não sei meu CEP</a>
                                    </div>
                                    <div class="group">
                                        <input type="tel" name="zip_code" id="zip_code" value="{{ old('zip_code') }}"
                                               class="form-control js-cep" placeholder="_____-___">
                                        <span class="loading"><i class="fa fa-spinner fa-spin" id="cep-loading" style="display: none;"></i></span>
                                    </div>
                                    @error('zip_code')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Campos de endereço (aparecem após busca do CEP ou se já preenchidos) --}}
                        <div id="box_endereco" class="row mt-2" style="display: {{ old('address') ? 'flex' : 'none' }};">
                            {{-- Endereço --}}
                            <div class="col-xs-12 col-sm-6">
                                <div class="form-group @error('address') has-error @enderror">
                                    <label for="address">Endereço <span>*</span></label>
                                    <input type="text" name="address" id="address" value="{{ old('address') }}"
                                           class="form-control" maxlength="60">
                                    @error('address')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Número --}}
                            <div class="col-xs-4 col-sm-2">
                                <div class="form-group @error('number') has-error @enderror">
                                    <label for="number">Número <span>*</span></label>
                                    <input type="tel" name="number" id="number" value="{{ old('number') }}"
                                           class="form-control" maxlength="5">
                                    @error('number')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Complemento --}}
                            <div class="col-xs-8 col-sm-4">
                                <div class="form-group">
                                    <label for="complement">Complemento</label>
                                    <input type="text" name="complement" id="complement" value="{{ old('complement') }}"
                                           class="form-control" maxlength="40">
                                </div>
                            </div>

                            {{-- Bairro --}}
                            <div class="col-xs-12 col-sm-4">
                                <div class="form-group @error('neighborhood') has-error @enderror">
                                    <label for="neighborhood">Bairro <span>*</span></label>
                                    <input type="text" name="neighborhood" id="neighborhood" value="{{ old('neighborhood') }}"
                                           class="form-control" maxlength="60">
                                    @error('neighborhood')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Cidade --}}
                            <div class="col-xs-6 col-sm-4">
                                <div class="form-group @error('city') has-error @enderror">
                                    <label for="city">Cidade <span>*</span></label>
                                    <input type="text" name="city" id="city" value="{{ old('city') }}"
                                           class="form-control" maxlength="60">
                                    @error('city')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Estado --}}
                            <div class="col-xs-6 col-sm-4">
                                <div class="form-group @error('state') has-error @enderror">
                                    <label for="state">Estado <span>*</span></label>
                                    <select name="state" id="state" class="form-control">
                                        <option value="">...</option>
                                        @php
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
                                        @foreach ($estados as $uf => $nome)
                                            <option value="{{ $uf }}" {{ old('state') == $uf ? 'selected' : '' }}>{{ $nome }}</option>
                                        @endforeach
                                    </select>
                                    @error('state')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ========================================== --}}
                    {{-- SEÇÃO: Newsletter e Submit --}}
                    {{-- ========================================== --}}
                    <div class="d-inline mt-3">
                        <hr>

                        {{-- Newsletter --}}
                        <div class="check-custom mb-4">
                            <label>
                                <input type="checkbox" name="newsletter" value="1"
                                       {{ old('newsletter', '1') ? 'checked' : '' }}>
                                <i class="fa fa-square-o"></i><i class="fa fa-check-square-o"></i>
                                Desejo assinar a newsletter e receber mensagens de lançamentos e promoções no meu e-mail.
                            </label>
                        </div>

                        {{-- Termos --}}
                        <h6 class="clear mb-4">
                            Ao criar uma conta, você concorda com a nossa
                            <a href="{{ url('/politicas-de-privacidade') }}" target="_blank">Política de Privacidade</a>.
                        </h6>

                        {{-- Botão de envio --}}
                        <button type="submit" class="btn btn-primary btn-large" id="btn-cadastrar">
                            Salvar cadastro <i class="fa fa-spinner fa-spin ml-1 js-load-cadastro" style="display: none"></i>
                        </button>

                        {{-- Link para login --}}
                        <p style="margin-top: 20px; color: #666;">
                            Já tem uma conta? <a href="{{ route('login') }}" style="color: var(--color-primary, #333); font-weight: 600;">Faça login</a>
                        </p>
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
    // TOGGLE PESSOA FÍSICA / JURÍDICA
    // Alterna exibição dos campos conforme o tipo de pessoa selecionado
    // ==========================================
    $('.check-perfil input[type="radio"]').on('change', function() {
        var tipo = $(this).val();

        // Atualiza visual dos labels (active)
        $('.check-perfil label').removeClass('active');
        $(this).closest('label').addClass('active');

        if (tipo === 'fisica') {
            $('#form_fisica').show();
            $('#form_juridica').hide();
            // Habilita campos de PF e desabilita PJ para evitar conflitos de name
            $('#form_fisica input').prop('disabled', false);
            $('#form_juridica input').prop('disabled', true);
        } else {
            $('#form_fisica').hide();
            $('#form_juridica').show();
            // Habilita campos de PJ e desabilita PF
            $('#form_juridica input').prop('disabled', false);
            $('#form_fisica input').prop('disabled', true);
        }
    });

    // Inicializa o estado dos campos disabled conforme seleção
    // PF é o padrão — desabilita campos de PJ no carregamento
    var tipoInicial = $('input[name="person_type"]:checked').val();
    if (tipoInicial === 'juridica') {
        $('#form_fisica input').prop('disabled', true);
    } else {
        $('#form_juridica input').prop('disabled', true);
    }

    // ==========================================
    // MÁSCARAS DE INPUT
    // Usa jQuery InputMask (já carregado no layout)
    // ==========================================

    // Máscara de CPF: 999.999.999-99
    $('.js-cpf').inputmask('999.999.999-99');

    // Máscara de CNPJ: 99.999.999/9999-99
    $('.js-cnpj').inputmask('99.999.999/9999-99');

    // Máscara de telefone: (99) 9999-9999 ou (99) 99999-9999
    $('.js-phone-mask').inputmask({
        mask: ['(99) 9999-9999', '(99) 99999-9999'],
        keepStatic: true
    });

    // Máscara de CEP: 99999-999
    $('.js-cep').inputmask('99999-999');

    // Máscara de data: 99/99/9999
    $('.js-date-mask').inputmask('99/99/9999');

    // ==========================================
    // TOGGLE VISIBILIDADE DA SENHA
    // Alterna entre text/password ao clicar no ícone do olho
    // ==========================================
    $('.js-toggle-password').on('click', function() {
        var $input = $(this).siblings('input');
        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $(this).removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            $input.attr('type', 'password');
            $(this).removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // ==========================================
    // CHECKBOX "ISENTO" - INSCRIÇÃO ESTADUAL
    // Quando marcado, limpa e desabilita o campo de inscrição estadual
    // ==========================================
    $('#isento').on('change', function() {
        var $input = $('#state_registration');
        if ($(this).is(':checked')) {
            $input.val('Isento').prop('readonly', true);
        } else {
            $input.val('').prop('readonly', false);
        }
    });

    // ==========================================
    // BUSCA DE CEP VIA API VIACEP
    // Preenche automaticamente endereço, bairro, cidade e estado
    // ==========================================
    $('#zip_code').on('blur', function() {
        var cep = $(this).val().replace(/\D/g, '');

        // CEP deve ter 8 dígitos
        if (cep.length !== 8) {
            return;
        }

        // Exibe loading
        $('#cep-loading').show();

        $.ajax({
            url: 'https://viacep.com.br/ws/' + cep + '/json/',
            dataType: 'json',
            timeout: 10000,
            success: function(data) {
                if (data.erro) {
                    // CEP não encontrado
                    if (typeof swal !== 'undefined') {
                        swal({
                            title: 'CEP não encontrado',
                            text: 'Verifique o CEP informado e tente novamente.',
                            type: 'warning'
                        });
                    }
                    return;
                }

                // Preenche os campos de endereço com os dados retornados
                $('#address').val(data.logradouro);
                $('#neighborhood').val(data.bairro);
                $('#city').val(data.localidade);
                $('#state').val(data.uf);

                // Exibe o bloco de endereço e foca no campo "Número"
                $('#box_endereco').show();
                $('#number').focus();
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

    // Se já houver endereço preenchido (old values), exibe o bloco
    if ($('#address').val()) {
        $('#box_endereco').show();
    }

    // ==========================================
    // SUBMIT DO FORMULÁRIO
    // Feedback visual no botão durante envio
    // ==========================================
    $('#form-cadastro').on('submit', function() {
        var $btn = $('#btn-cadastrar');
        $btn.prop('disabled', true);
        $btn.find('.js-load-cadastro').show();
    });

});
</script>
@endpush
