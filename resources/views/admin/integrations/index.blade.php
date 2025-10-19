@extends('adminlte::page')

@section('title', 'Integrations')

@section('content_header')
    <h1>Integrations</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <a href="{{ route('admin.integrations.create') }}" class="btn btn-primary">New Integration</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Status</th>
                        <th style="width: 200px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($integrations as $integration)
                        <tr>
                            <td>{{ $integration->name }}</td>
                            <td>{{ $integration->status ? 'Active' : 'Inactive' }}</td>
                            <td>
                                <a href="{{ route('admin.integrations.show', $integration->id) }}" class="btn btn-sm btn-info">Show</a>
                                <a href="{{ route('admin.integrations.edit', $integration->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('admin.integrations.destroy', $integration->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $integrations->links() }}
        </div>
    </div>
@stop
