@extends('adminlte::page')

@section('title', 'Nova Categoria')

@section('content_header')
    <h1>Nova Categoria</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf
                @include('admin.categories._form')
            </form>
        </div>
    </div>
@stop
