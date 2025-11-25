@extends('adminlte::page')
@section('title', 'Editar Bloco de Passos')
@section('content')
<form action="{{ route('admin.step-blocks.update', $stepBlock) }}" method="POST" enctype="multipart/form-data">
@csrf @method('PUT')
<div class="card">
<div class="card-body">
    <div class="form-group"><label>Ordem</label><input type="number" name="order" class="form-control" value="{{ $stepBlock->order }}" required></div>
    <div class="custom-control custom-switch"><input type="checkbox" class="custom-control-input" id="active" name="active" value="1" {{ $stepBlock->active ? 'checked' : '' }}><label class="custom-control-label" for="active">Ativo</label></div>
    <hr>
    @for($i=1; $i<=4; $i++)
    <h5>Item {{ $i }}</h5>
    <img src="{{ $stepBlock->getIconUrl($i) }}" width="80" class="mb-2">
    <div class="form-group"><label>Ícone {{ $i }}</label><input type="file" name="item_{{ $i }}_icon" class="form-control"></div>
    <div class="form-group"><label>Título {{ $i }} *</label><input type="text" name="item_{{ $i }}_title" class="form-control" value="{{ $stepBlock->{'item_'.$i.'_title'} }}" required></div>
    <div class="form-group"><label>Descrição {{ $i }} *</label><textarea name="item_{{ $i }}_description" class="form-control" required>{{ $stepBlock->{'item_'.$i.'_description'} }}</textarea></div>
    <div class="form-group"><label>Alt {{ $i }}</label><input type="text" name="item_{{ $i }}_alt" class="form-control" value="{{ $stepBlock->{'item_'.$i.'_alt'} }}"></div>
    @if($i < 4)<hr>@endif
    @endfor
</div>
<div class="card-footer"><button type="submit" class="btn btn-success">Atualizar</button><a href="{{ route('admin.step-blocks.index') }}" class="btn btn-secondary">Cancelar</a></div>
</div>
</form>
@stop
