@extends('adminlte::page')

@section('title', 'New Category')

@section('content_header')
    <h1>New Category</h1>
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
