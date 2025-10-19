@extends('adminlte::page')

@section('title', 'Edit Brand')

@section('content_header')
    <h1>Edit Brand</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.brands.update', $brand->id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('admin.brands._form')

                <div class="form-group">
                    <label for="tray_id">Tray ID</label>
                    <input type="text" id="tray_id" class="form-control" value="{{ $brand->tray_id }}" readonly>
                </div>

            </form>
        </div>
        <div class="card-footer">
            <form action="{{ route('admin.brands.sync_to_tray', $brand) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success">Sync to Tray</button>
            </form>
        </div>
    </div>
@stop
