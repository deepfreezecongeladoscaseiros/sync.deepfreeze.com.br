@extends('adminlte::page')

@section('title', 'Create Manufacturer')

@section('content_header')
    <h1>Create Manufacturer</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.manufacturers.store') }}" method="POST">
                @csrf
                @include('admin.manufacturers._form')
                
                <button type="submit" class="btn btn-primary">Create</button>
                <a href="{{ route('admin.manufacturers.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@stop
