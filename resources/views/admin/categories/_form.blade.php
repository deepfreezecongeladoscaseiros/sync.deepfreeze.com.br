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
    <label for="name">Nome</label>
    <input type="text" name="name" id="name" class="form-control" value="{{ $category->name ?? old('name') }}" required>
</div>

<button type="submit" class="btn btn-primary">Salvar</button>
