@extends('adminlte::page')

@section('title', 'Edit Variant')

@section('content_header')
    <h1>Edit Variant for: {{ $variant->product->name }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.variants.update', $variant->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="type">Type (e.g., Color)</label>
                    <input type="text" name="type" class="form-control" value="{{ $variant->type ?? old('type') }}" required>
                </div>
                <div class="form-group">
                    <label for="value">Value (e.g., Blue)</label>
                    <input type="text" name="value" class="form-control" value="{{ $variant->value ?? old('value') }}" required>
                </div>
                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" step="0.01" name="price" class="form-control" value="{{ $variant->price ?? old('price') }}">
                </div>
                <div class="form-group">
                    <label for="stock">Stock</label>
                    <input type="number" name="stock" class="form-control" value="{{ $variant->stock ?? old('stock') }}">
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('admin.products.edit', $variant->product_id) }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@stop
