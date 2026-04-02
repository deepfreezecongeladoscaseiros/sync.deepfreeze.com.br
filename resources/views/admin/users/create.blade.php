@extends('adminlte::page')

@section('title', 'Novo Usuário')

@section('content_header')
    <h1>Novo Usuário</h1>
@stop

@section('content')
    <div class="card">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @include('admin.users._form')
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Criar Usuário
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-default">Cancelar</a>
            </div>
        </form>
    </div>
@stop
