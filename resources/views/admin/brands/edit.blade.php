@extends('adminlte::page')

@section('title', 'Editar Marca')

@section('content_header')
    <h1>Editar Marca: {{ $brand->brand }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.brands.update', $brand->id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('admin.brands._form')

                <div class="form-group">
                    <label for="tray_id">ID Tray</label>
                    <input type="text" id="tray_id" class="form-control" value="{{ $brand->tray_id }}" readonly>
                </div>

                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
        <div class="card-footer">
            <form action="{{ route('admin.brands.sync_to_tray', $brand) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success">Sincronizar com Tray</button>
            </form>
        </div>
    </div>
@stop
