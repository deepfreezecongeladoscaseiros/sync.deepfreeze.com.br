@extends('adminlte::page')

@section('title', 'Integration Details')

@section('content_header')
    <h1>Integration Details</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ $integration->name }}" disabled>
            </div>
            <div class="form-group">
                <label for="token">Token</label>
                <div class="input-group">
                    <input type="text" name="token" id="token" class="form-control" value="{{ $integration->token }}" readonly>
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" onclick="copyToken()">Copy</button>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <input type="text" name="status" id="status" class="form-control" value="{{ $integration->status ? 'Active' : 'Inactive' }}" disabled>
            </div>
            <a href="{{ route('admin.integrations.index') }}" class="btn btn-secondary">Back to List</a>
        </div>
    </div>
@stop

@section('js')
    <script>
        function copyToken() {
            var copyText = document.getElementById("token");
            copyText.select();
            copyText.setSelectionRange(0, 99999); /* For mobile devices */
            document.execCommand("copy");
            alert("Copied the token: " + copyText.value);
        }
    </script>
@stop
