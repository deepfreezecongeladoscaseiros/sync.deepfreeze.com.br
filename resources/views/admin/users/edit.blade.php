@extends('adminlte::page')

@section('title', 'Editar Usuário')

@section('content_header')
    <h1>Editar Usuário: {{ $user->name }}</h1>
@stop

@section('content')
    <div class="card">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('admin.users._form')

                <p class="text-muted mt-2">
                    <small>Deixe a senha em branco para manter a senha atual.</small>
                </p>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Salvar Alterações
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-default">Cancelar</a>
            </div>
        </form>
    </div>
@stop
