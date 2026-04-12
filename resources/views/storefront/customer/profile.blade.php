{{--
    Página: Meus Dados — Editar Perfil
    Formulário com dados pessoais do cliente.
    Grava diretamente na tabela pessoas do banco legado.
--}}
@extends('storefront.customer.layout')

@section('title', 'Meus Dados - ' . config('app.name'))

@section('customer-content')

    <h2>Meus Dados</h2>
    <p class="section-subtitle">Atualize suas informações pessoais.</p>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('customer.profile.update') }}" method="POST" class="customer-form">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group">
                    <label for="nome">Nome completo *</label>
                    <input type="text" name="nome" id="nome" class="form-control"
                           value="{{ old('nome', $customer->nome) }}" required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-6">
                <div class="form-group">
                    <label for="email_primario">E-mail *</label>
                    <input type="email" name="email_primario" id="email_primario" class="form-control"
                           value="{{ old('email_primario', $customer->email_primario) }}" required>
                </div>
            </div>
            <div class="col-xs-12 col-md-6">
                <div class="form-group">
                    <label for="cpf">CPF</label>
                    <input type="text" name="cpf" id="cpf" class="form-control"
                           value="{{ old('cpf', $customer->cpf) }}">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-4">
                <div class="form-group">
                    <label for="telefone_celular">Celular</label>
                    <input type="text" name="telefone_celular" id="telefone_celular" class="form-control"
                           value="{{ old('telefone_celular', $customer->telefone_celular) }}">
                </div>
            </div>
            <div class="col-xs-12 col-md-4">
                <div class="form-group">
                    <label for="nascimento">Data de Nascimento</label>
                    <input type="text" name="nascimento" id="nascimento" class="form-control"
                           value="{{ old('nascimento', $customer->nascimento ? $customer->nascimento->format('d/m/Y') : '') }}">
                </div>
            </div>
            <div class="col-xs-12 col-md-4">
                <div class="form-group">
                    <label>Sexo</label>
                    <select name="sexo" class="form-control">
                        <option value="">Selecione</option>
                        <option value="M" {{ old('sexo', $customer->sexo) === 'M' ? 'selected' : '' }}>Masculino</option>
                        <option value="F" {{ old('sexo', $customer->sexo) === 'F' ? 'selected' : '' }}>Feminino</option>
                        <option value="O" {{ old('sexo', $customer->sexo) === 'O' ? 'selected' : '' }}>Outros</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Preferências de comunicação --}}
        <div style="margin-top: 16px; margin-bottom: 20px;">
            <label style="font-weight: 600; font-size: 0.9em; color: #555; margin-bottom: 10px; display: block;">Preferências de comunicação</label>
            <div class="row">
                <div class="col-xs-6 col-md-3">
                    <label style="font-weight: 400; font-size: 0.88em;">
                        <input type="checkbox" name="autoriza_newsletter" value="1"
                               {{ old('autoriza_newsletter', $customer->autoriza_newsletter) ? 'checked' : '' }}>
                        Newsletter
                    </label>
                </div>
                <div class="col-xs-6 col-md-3">
                    <label style="font-weight: 400; font-size: 0.88em;">
                        <input type="checkbox" name="aceita_whats_app" value="1"
                               {{ old('aceita_whats_app', $customer->aceita_whats_app) ? 'checked' : '' }}>
                        WhatsApp
                    </label>
                </div>
                <div class="col-xs-6 col-md-3">
                    <label style="font-weight: 400; font-size: 0.88em;">
                        <input type="checkbox" name="aceita_sms" value="1"
                               {{ old('aceita_sms', $customer->aceita_sms) ? 'checked' : '' }}>
                        SMS
                    </label>
                </div>
                <div class="col-xs-6 col-md-3">
                    <label style="font-weight: 400; font-size: 0.88em;">
                        <input type="checkbox" name="aceita_ligacao" value="1"
                               {{ old('aceita_ligacao', $customer->aceita_ligacao) ? 'checked' : '' }}>
                        Ligação
                    </label>
                </div>
            </div>
        </div>

        <button type="submit" class="btn-save">
            <i class="fa fa-check"></i> Salvar Alterações
        </button>
    </form>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#cpf').inputmask('999.999.999-99');
    $('#telefone_celular').inputmask('(99) 99999-9999');
    $('#nascimento').inputmask('99/99/9999');
});
</script>
@endpush
