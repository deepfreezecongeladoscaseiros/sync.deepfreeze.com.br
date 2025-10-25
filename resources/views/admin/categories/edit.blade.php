@extends('adminlte::page')

@section('title', 'Editar Categoria')

@section('content_header')
    <h1>Editar Categoria: {{ $category->name }}</h1>
@stop

@section('content')
    <div class="card">
        <form action="{{ route('admin.categories.update', $category->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('admin.categories._form')

                <div class="form-group">
                    <label for="tray_id">ID Tray</label>
                    <input type="text" id="tray_id" class="form-control" value="{{ $category->tray_id }}" readonly>
                </div>

                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
        <div class="card-footer">
            <form action="{{ route('admin.categories.sync_to_tray', $category) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success">Sincronizar com Tray</button>
            </form>
        </div>
    </div>
@stop
