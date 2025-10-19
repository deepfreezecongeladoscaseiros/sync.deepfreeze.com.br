@extends('adminlte::page')

@section('title', 'Edit Product')

@section('content_header')
    <h1>Edit Product: {{ $product->name }}</h1>
@stop

@section('content')
    {{-- Product Edit Form --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Product Details</h3>
            <div class="card-tools">
                <form action="{{ route('admin.products.sync_to_tray', $product) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-success">Sync to Tray</button>
                </form>
                <form action="{{ route('admin.products.sync_image', $product) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-info">Sync Image</button>
                </form>
                <form action="{{ route('admin.products.sync_properties', $product) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-warning">Sync Properties</button>
                </form>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="tray_id">Tray ID</label>
                    <input type="text" id="tray_id" class="form-control" value="{{ $product->tray_id }}" readonly>
                </div>

                @include('admin.products._form')
            </form>
        </div>
    </div>

    {{-- Variants Management --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Variants</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#variantModal">
                    New Variant
                </button>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tray ID</th>
                        <th>Type</th>
                        <th>Value</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th style="width: 200px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($product->variations as $variant)
                        <tr>
                            <td>{{ $variant->tray_id }}</td>
                            <td>{{ $variant->type }}</td>
                            <td>{{ $variant->value }}</td>
                            <td>R$ {{ number_format($variant->price ?? $product->price, 2, ',', '.') }}</td>
                            <td>{{ $variant->stock }}</td>
                            <td>
                                <a href="{{ route('admin.variants.edit', $variant->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('admin.variants.destroy', $variant->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                                <form action="{{ route('admin.variants.sync_to_tray', $variant->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">Sync</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">No variants found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

{{-- New Variant Modal --}}
<div class="modal fade" id="variantModal" tabindex="-1" role="dialog" aria-labelledby="variantModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="variantModalLabel">New Variant</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="variantForm" action="{{ route('admin.products.variants.store', $product) }}" method="POST">
                <div class="modal-body">
                    @csrf
                    <div class="form-group">
                        <label for="type">Type (e.g., Color)</label>
                        <input type="text" name="type" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="value">Value (e.g., Blue)</label>
                        <input type="text" name="value" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="price">Price (leave blank to use main product price)</label>
                        <input type="number" step="0.01" name="price" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="stock">Stock</label>
                        <input type="number" name="stock" class="form-control" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Variant</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
