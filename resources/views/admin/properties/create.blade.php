@extends('adminlte::page')

@section('title', 'Nova Propriedade')

@section('content_header')
    <h1>Nova Propriedade</h1>
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
