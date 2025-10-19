@extends('adminlte::page')

@section('title', 'Edit Property')

@section('content_header')
    <h1>Edit Property</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.properties.update', $property->id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('admin.properties._form')

                <div class="form-group">
                    <label for="tray_id">Tray ID</label>
                    <input type="text" id="tray_id" class="form-control" value="{{ $property->tray_id }}" readonly>
                </div>

            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.properties.index') }}" class="btn btn-secondary">Cancel</a>
                <form action="{{ route('admin.properties.sync_to_tray', $property) }}" method="POST" style="display: inline; float: right;">
                    @csrf
                    <button type="submit" class="btn btn-success">Sync to Tray</button>
                </form>
            </form>
        </div>
    </div>
@stop
