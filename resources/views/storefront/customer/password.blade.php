{{--
    Página: Alterar Senha
    Senha atual + nova senha + confirmação.
    Grava em MD5 na tabela pessoas para compatibilidade com o legado.
--}}
@extends('storefront.customer.layout')

@section('title', 'Alterar Senha - ' . config('app.name'))

@section('customer-content')

    <h2>Alterar Senha</h2>
    <p class="section-subtitle">Sua senha é criptografada e não pode ser visualizada.</p>

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

    <form action="{{ route('customer.password.update') }}" method="POST" class="customer-form" style="max-width: 400px;">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="current_password">Senha atual *</label>
            <input type="password" name="current_password" id="current_password" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="new_password">Nova senha *</label>
            <input type="password" name="new_password" id="new_password" class="form-control" required minlength="6">
        </div>

        <div class="form-group">
            <label for="new_password_confirmation">Confirmar nova senha *</label>
            <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control" required>
        </div>

        <button type="submit" class="btn-save">
            <i class="fa fa-lock"></i> Alterar Senha
        </button>
    </form>

@endsection
