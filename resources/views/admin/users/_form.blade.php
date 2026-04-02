{{-- Formulário compartilhado para criar/editar usuário --}}

<div class="form-group">
    <label for="name">Nome <span class="text-danger">*</span></label>
    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name', $user->name ?? '') }}" required>
    @error('name')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="email">E-mail <span class="text-danger">*</span></label>
    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
           value="{{ old('email', $user->email ?? '') }}" required>
    @error('email')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="password">Senha @if(!isset($user))<span class="text-danger">*</span>@endif</label>
            <input type="password" name="password" id="password"
                   class="form-control @error('password') is-invalid @enderror"
                   {{ !isset($user) ? 'required' : '' }}>
            @error('password')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="password_confirmation">Confirmar Senha @if(!isset($user))<span class="text-danger">*</span>@endif</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control"
                   {{ !isset($user) ? 'required' : '' }}>
        </div>
    </div>
</div>
