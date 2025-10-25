@extends('adminlte::page')

@section('title', 'Propriedades')

@section('content_header')
    <h1>Propriedades</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <a href="{{ route('admin.properties.create') }}" class="btn btn-primary">Nova Propriedade</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th style="width: 150px">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($properties as $property)
                        <tr>
                            <td>{{ $property->name }}</td>
                            <td>
                                <a href="{{ route('admin.properties.show', $property->id) }}" class="btn btn-sm btn-info">Valores</a>
                                <a href="{{ route('admin.properties.edit', $property->id) }}" class="btn btn-sm btn-warning">Editar</a>
                                <form action="{{ route('admin.properties.destroy', $property->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $properties->links() }}
        </div>
    </div>
@stop
