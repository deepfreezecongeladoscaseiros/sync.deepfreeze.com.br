@extends('adminlte::page')
@section('title', 'Novo Bloco de Passos')
@section('content')
<form action="{{ route('admin.step-blocks.store') }}" method="POST" enctype="multipart/form-data">
@csrf
<div class="card">
<div class="card-body">
    <div class="form-group"><label>Ordem</label><input type="number" name="order" class="form-control" value="{{ $nextOrder }}" required></div>
    <div class="custom-control custom-switch"><input type="checkbox" class="custom-control-input" id="active" name="active" value="1" checked><label class="custom-control-label" for="active">Ativo</label></div>
    <hr>
    @for($i=1; $i<=4; $i++)
    <h5>Item {{ $i }}</h5>
    <div class="form-group"><label>Ícone {{ $i }} *</label><input type="file" name="item_{{ $i }}_icon" class="form-control" required></div>
    <div class="form-group"><label>Título {{ $i }} *</label><input type="text" name="item_{{ $i }}_title" class="form-control" required></div>
    <div class="form-group"><label>Descrição {{ $i }} *</label><textarea name="item_{{ $i }}_description" class="form-control" required></textarea></div>
    <div class="form-group"><label>Alt {{ $i }}</label><input type="text" name="item_{{ $i }}_alt" class="form-control"></div>
    @if($i < 4)<hr>@endif
    @endfor
</div>
<div class="card-footer"><button type="submit" class="btn btn-success">Salvar</button><a href="{{ route('admin.step-blocks.index') }}" class="btn btn-secondary">Cancelar</a></div>
</div>
</form>
@stop
