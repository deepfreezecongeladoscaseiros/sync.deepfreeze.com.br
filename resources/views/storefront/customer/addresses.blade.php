{{--
    Página: Meus Endereços
    Lista endereços do cliente com opção de remover e adicionar.
    Dados do banco legado (tabela enderecos).
--}}
@extends('storefront.customer.layout')

@section('title', 'Meus Endereços - ' . config('app.name'))

@section('customer-content')

    <h2>Meus Endereços</h2>
    <p class="section-subtitle">Gerencie seus endereços de entrega.</p>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Lista de endereços --}}
    <div class="address-list">
        @forelse($addresses as $address)
            <div class="address-card {{ $address->end_principal ? 'is-main' : '' }}">
                <div class="address-info">
                    <strong>{{ $address->logradouro }}, {{ $address->logradouro_complemento_numero }}</strong>
                    @if($address->end_principal)
                        <span class="badge-principal">Principal</span>
                    @endif
                    <br>
                    @if($address->logradouro_complemento)
                        {{ $address->logradouro_complemento }} —
                    @endif
                    {{ $address->bairro }}<br>
                    {{ $address->cidade }}/{{ $address->uf }} — CEP {{ $address->cep }}
                </div>
                <div class="address-actions">
                    <form action="{{ route('customer.address.delete', $address->id) }}" method="POST"
                          onsubmit="return confirm('Deseja remover este endereço?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-remove">
                            <i class="fa fa-trash"></i> Remover
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <p style="color: #999; text-align: center; padding: 20px;">Nenhum endereço cadastrado.</p>
        @endforelse
    </div>

    {{-- Botão para abrir formulário --}}
    <button type="button" class="btn-add-address" id="btn-toggle-address-form">
        <i class="fa fa-plus"></i> Adicionar novo endereço
    </button>

    {{-- Formulário novo endereço (toggle) --}}
    <div class="address-form-wrapper" id="address-form-wrapper">
        <form action="{{ route('customer.address.store') }}" method="POST" class="customer-form">
            @csrf

            <div class="row">
                <div class="col-xs-12 col-md-4">
                    <div class="form-group">
                        <label for="addr_cep">CEP *</label>
                        <input type="text" name="zip_code" id="addr_cep" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12 col-md-8">
                    <div class="form-group">
                        <label for="addr_street">Rua/Avenida *</label>
                        <input type="text" name="street" id="addr_street" class="form-control" required>
                    </div>
                </div>
                <div class="col-xs-12 col-md-4">
                    <div class="form-group">
                        <label for="addr_number">Número *</label>
                        <input type="text" name="number" id="addr_number" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12 col-md-4">
                    <div class="form-group">
                        <label for="addr_complement">Complemento</label>
                        <input type="text" name="complement" id="addr_complement" class="form-control">
                    </div>
                </div>
                <div class="col-xs-12 col-md-4">
                    <div class="form-group">
                        <label for="addr_neighborhood">Bairro *</label>
                        <input type="text" name="neighborhood" id="addr_neighborhood" class="form-control" required>
                    </div>
                </div>
                <div class="col-xs-12 col-md-4">
                    <div class="form-group">
                        <label for="addr_city">Cidade *</label>
                        <input type="text" name="city" id="addr_city" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12 col-md-2">
                    <div class="form-group">
                        <label for="addr_state">UF *</label>
                        <input type="text" name="state" id="addr_state" class="form-control" maxlength="2" required>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-save">
                <i class="fa fa-check"></i> Salvar Endereço
            </button>
            <button type="button" class="btn-add-address" id="btn-cancel-address" style="margin-left: 10px;">
                Cancelar
            </button>
        </form>
    </div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Mascara de CEP (Inputmask ja carregado globalmente)
    $('#addr_cep').inputmask('99999-999');

    // Toggle formulario de novo endereco
    $('#btn-toggle-address-form').on('click', function() {
        $('#address-form-wrapper').slideDown(300);
        $(this).hide();
        $('#addr_cep').focus();
    });

    // Cancelar fecha o formulario e restaura botao
    $('#btn-cancel-address').on('click', function() {
        $('#address-form-wrapper').slideUp(300);
        $('#btn-toggle-address-form').show();
    });

    // Auto-preenchimento via ViaCEP ao sair do campo CEP
    $('#addr_cep').on('blur', function() {
        var cep = $(this).val().replace(/\D/g, '');
        if (cep.length !== 8) return;

        $.getJSON('https://viacep.com.br/ws/' + cep + '/json/', function(data) {
            if (!data.erro) {
                $('#addr_street').val(data.logradouro || '');
                $('#addr_neighborhood').val(data.bairro || '');
                $('#addr_city').val(data.localidade || '');
                $('#addr_state').val(data.uf || '');
                $('#addr_number').focus();
            }
        });
    });
});
</script>
@endpush
