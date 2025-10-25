@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="form-group">
    <label for="brand">Nome</label>
    <input type="text" name="brand" id="brand" class="form-control" value="{{ $brand->brand ?? old('brand') }}" required>
</div>

<button type="submit" class="btn btn-primary">Salvar</button>
