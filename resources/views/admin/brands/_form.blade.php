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
    <label for="brand">Name</label>
    <input type="text" name="brand" id="brand" class="form-control" value="{{ $brand->brand ?? old('brand') }}">
</div>

<button type="submit" class="btn btn-primary">Save</button>
