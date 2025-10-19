@extends('adminlte::page')

@section('title', 'Synchronization')

@section('content_header')
    <h1>Data Synchronization</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Sync from Legacy Database</h3>
        </div>
        <div class="card-body">
            <p>Click the buttons below to sync data from the legacy Deep Freeze database to this application.</p>
            <div class="row">
                <div class="col-md-3">
                    <form action="{{ route('admin.sync.categories') }}" method="POST" class="sync-form">
                        @csrf
                        <button type="submit" class="btn btn-app bg-secondary w-100">
                            <i class="fas fa-tags"></i> Sync Categories
                        </button>
                    </form>
                </div>
                <div class="col-md-3">
                    <form action="{{ route('admin.sync.brands') }}" method="POST" class="sync-form">
                        @csrf
                        <button type="submit" class="btn btn-app bg-info w-100">
                            <i class="fas fa-copyright"></i> Sync Brands
                        </button>
                    </form>
                </div>
                <div class="col-md-3">
                    <form action="{{ route('admin.sync.products') }}" method="POST" class="sync-form">
                        @csrf
                        <button type="submit" class="btn btn-app bg-success w-100">
                            <i class="fas fa-box"></i> Sync Products
                        </button>
                    </form>
                </div>
                <div class="col-md-3">
                    <form action="{{ action([App\Http\Controllers\Admin\SyncController::class, 'syncImages']) }}" method="POST" class="sync-form">
                        @csrf
                        <button type="submit" class="btn btn-app bg-warning w-100">
                            <i class="fas fa-camera"></i> Sync Images
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Sync to Tray Commerce</h3>
        </div>
        <div class="card-body">
            <p>Click the buttons below to sync all new items to Tray Commerce.</p>
            <div class="row">
                <div class="col-md-3">
                    <form action="{{ route('admin.tray_sync.categories') }}" method="POST" class="sync-form">
                        @csrf
                        <button type="submit" class="btn btn-app bg-secondary w-100">
                            <i class="fas fa-tags"></i> Sync Categories
                        </button>
                    </form>
                </div>
                <div class="col-md-3">
                    <form action="{{ route('admin.tray_sync.brands') }}" method="POST" class="sync-form">
                        @csrf
                        <button type="submit" class="btn btn-app bg-info w-100">
                            <i class="fas fa-copyright"></i> Sync Brands
                        </button>
                    </form>
                </div>
                <div class="col-md-3">
                    <form action="{{ route('admin.tray_sync.products') }}" method="POST" class="sync-form">
                        @csrf
                        <button type="submit" class="btn btn-app bg-success w-100">
                            <i class="fas fa-box"></i> Sync Products
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    document.querySelectorAll('.sync-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const button = e.target.querySelector('button[type="submit"]');
            button.disabled = true;
            const originalIcon = button.querySelector('i').className;
            button.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Syncing...`;
        });
    });
</script>
@stop
