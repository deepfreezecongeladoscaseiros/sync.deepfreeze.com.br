@extends('adminlte::page')
@section('title', 'Blocos de Passos')
@section('content_header')<h1>Blocos de Passos (4 itens)</h1>@stop
@section('content')
<div class="card">
    <div class="card-header">
        <a href="{{ route('admin.step-blocks.create') }}" class="btn btn-success float-right"><i class="fas fa-plus"></i> Novo</a>
    </div>
    <div class="card-body">
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($blocks->isEmpty())
            <p>Nenhum bloco cadastrado.</p>
        @else
            <table class="table table-bordered">
                <tr><th>Ordem</th><th>Item 1</th><th>Item 2</th><th>Item 3</th><th>Item 4</th><th>Status</th><th>Ações</th></tr>
                @foreach($blocks as $b)
                <tr>
                    <td>{{ $b->order }}</td>
                    <td><img src="{{ $b->getIconUrl(1) }}" width="40"><br>{{ $b->item_1_title }}</td>
                    <td><img src="{{ $b->getIconUrl(2) }}" width="40"><br>{{ $b->item_2_title }}</td>
                    <td><img src="{{ $b->getIconUrl(3) }}" width="40"><br>{{ $b->item_3_title }}</td>
                    <td><img src="{{ $b->getIconUrl(4) }}" width="40"><br>{{ $b->item_4_title }}</td>
                    <td>{{ $b->active ? 'Ativo' : 'Inativo' }}</td>
                    <td>
                        <a href="{{ route('admin.step-blocks.edit', $b) }}" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('admin.step-blocks.destroy', $b) }}" method="POST" style="display:inline" onsubmit="return confirm('Excluir?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </table>
        @endif
    </div>
</div>
@stop
