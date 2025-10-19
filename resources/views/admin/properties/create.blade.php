@extends('adminlte::page')

@section('title', 'New Property')

@section('content_header')
    <h1>New Property</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.properties.store') }}" method="POST">
                @csrf
                @include('admin.properties._form')
            </form>
        </div>
    </div>
@stop
