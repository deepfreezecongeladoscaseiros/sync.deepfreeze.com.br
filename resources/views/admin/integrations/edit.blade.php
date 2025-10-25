@extends('adminlte::page')

@section('title', 'Editar Integração')

@section('content_header')
    <h1>Edit Integration: {{ $integration->name }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.integrations.update', $integration->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $integration->name) }}">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
                        <option value="1" {{ old('status', $integration->status) == 1 ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('status', $integration->status) == 0 ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('admin.integrations.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@stop
