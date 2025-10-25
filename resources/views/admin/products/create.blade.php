@extends('adminlte::page')

@section('title', 'Novo Produto')

@section('content_header')
    <h1>Novo Produto</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @include('admin.products._form')
            </form>
        </div>
    </div>
@stop
