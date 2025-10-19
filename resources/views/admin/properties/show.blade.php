@extends('adminlte::page')

@section('title', 'Property Values')

@section('content_header')
    <h1>Values for: {{ $property->name }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h4>New Value</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.properties.values.store', $property) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}">
                </div>
                <button type="submit" class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>Existing Values</h4>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th style="width: 150px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($property->values as $value)
                        <tr>
                            <td>{{ $value->name }}</td>
                            <td>
                                <a href="{{ route('admin.values.edit', $value->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('admin.values.destroy', $value->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop
