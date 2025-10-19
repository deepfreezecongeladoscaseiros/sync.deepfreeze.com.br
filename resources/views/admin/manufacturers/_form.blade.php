<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="trade_name">Trade Name *</label>
            <input type="text" 
                   name="trade_name" 
                   id="trade_name" 
                   class="form-control @error('trade_name') is-invalid @enderror" 
                   value="{{ old('trade_name', $manufacturer->trade_name ?? '') }}" 
                   required>
            @error('trade_name')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="legal_name">Legal Name</label>
            <input type="text" 
                   name="legal_name" 
                   id="legal_name" 
                   class="form-control @error('legal_name') is-invalid @enderror" 
                   value="{{ old('legal_name', $manufacturer->legal_name ?? '') }}">
            @error('legal_name')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="cnpj">CNPJ</label>
            <input type="text" 
                   name="cnpj" 
                   id="cnpj" 
                   class="form-control @error('cnpj') is-invalid @enderror" 
                   value="{{ old('cnpj', $manufacturer->cnpj ?? '') }}"
                   placeholder="00.000.000/0000-00">
            @error('cnpj')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="text" 
                   name="phone" 
                   id="phone" 
                   class="form-control @error('phone') is-invalid @enderror" 
                   value="{{ old('phone', $manufacturer->phone ?? '') }}"
                   placeholder="(00) 0000-0000">
            @error('phone')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" 
                   name="email" 
                   id="email" 
                   class="form-control @error('email') is-invalid @enderror" 
                   value="{{ old('email', $manufacturer->email ?? '') }}">
            @error('email')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label for="address">Address</label>
    <input type="text" 
           name="address" 
           id="address" 
           class="form-control @error('address') is-invalid @enderror" 
           value="{{ old('address', $manufacturer->address ?? '') }}">
    @error('address')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="city">City</label>
            <input type="text" 
                   name="city" 
                   id="city" 
                   class="form-control @error('city') is-invalid @enderror" 
                   value="{{ old('city', $manufacturer->city ?? '') }}">
            @error('city')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label for="state">State</label>
            <input type="text" 
                   name="state" 
                   id="state" 
                   class="form-control @error('state') is-invalid @enderror" 
                   value="{{ old('state', $manufacturer->state ?? '') }}"
                   maxlength="2"
                   placeholder="SP">
            @error('state')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label for="zip_code">ZIP Code</label>
            <input type="text" 
                   name="zip_code" 
                   id="zip_code" 
                   class="form-control @error('zip_code') is-invalid @enderror" 
                   value="{{ old('zip_code', $manufacturer->zip_code ?? '') }}"
                   placeholder="00000-000">
            @error('zip_code')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <div class="custom-control custom-checkbox">
        <input type="checkbox" 
               class="custom-control-input" 
               id="active" 
               name="active" 
               value="1"
               {{ old('active', $manufacturer->active ?? true) ? 'checked' : '' }}>
        <label class="custom-control-label" for="active">Active</label>
    </div>
</div>
