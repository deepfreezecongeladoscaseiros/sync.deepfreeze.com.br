@extends('adminlte::page')

@section('title', 'New Brand')

@section('content_header')
    <h1>New Brand</h1>
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
