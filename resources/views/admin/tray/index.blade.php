@extends('adminlte::page')

@section('title', 'Tray Commerce Settings')

@section('content_header')
    <h1>Tray Commerce Settings</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">API Credentials</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.tray.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="store_id">Store ID</label>
                    <input type="text" name="store_id" id="store_id" class="form-control" value="{{ $credentials->store_id ?? old('store_id') }}" required>
                </div>
                <div class="form-group">
                    <label for="api_host">API Host (e.g., https://urldaloja.com.br/web_api)</label>
                    <input type="text" name="api_host" id="api_host" class="form-control" value="{{ $credentials->api_host ?? old('api_host') }}" required>
                </div>
                <div class="form-group">
                    <label for="consumer_key">Consumer Key</label>
                    <input type="text" name="consumer_key" id="consumer_key" class="form-control" value="{{ $credentials->consumer_key ?? old('consumer_key') }}" required>
                </div>
                <div class="form-group">
                    <label for="consumer_secret">Consumer Secret</label>
                    <input type="text" name="consumer_secret" id="consumer_secret" class="form-control" value="{{ $credentials->consumer_secret ?? old('consumer_secret') }}" required>
                </div>
                <div class="form-group">
                    <label for="code">Authorization Code</label>
                    <input type="text" name="code" id="code" class="form-control" value="{{ $credentials->code ?? old('code') }}" required>
                </div>

                <button type="submit" class="btn btn-primary">Save Credentials</button>
            </form>
        </div>
    </div>

    @if ($credentials)
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Access Tokens</h3>
        </div>
        <div class="card-body">
            <p><strong>Access Token:</strong> {{ $credentials->access_token ?? 'Not generated' }}</p>
            <p><strong>Expires at:</strong> {{ $credentials->date_expiration_access_token ?? 'N/A' }}</p>
            <hr>
            <p><strong>Refresh Token:</strong> {{ $credentials->refresh_token ?? 'Not generated' }}</p>
            <p><strong>Expires at:</strong> {{ $credentials->date_expiration_refresh_token ?? 'N/A' }}</p>
        </div>
        <div class="card-footer">
            <form action="{{ route('admin.tray.tokens') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success">Generate/Refresh Tokens</button>
            </form>
        </div>
    </div>
    @endif
@stop
