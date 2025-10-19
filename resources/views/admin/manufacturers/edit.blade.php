@extends('adminlte::page')

@section('title', 'Edit Manufacturer')

@section('content_header')
    <h1>Edit Manufacturer</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.manufacturers.update', $manufacturer->id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('admin.manufacturers._form')
                
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('admin.manufacturers.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@stop
