@extends('adminlte::page')

@section('title', 'Nova Marca')

@section('content_header')
    <h1>Nova Marca</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.brands.store') }}" method="POST">
                @csrf
                @include('admin.brands._form')
            </form>
        </div>
    </div>
@stop
