@extends('adminlte::page')

@section('title', 'Dashboard - Deep Sync')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-tachometer-alt mr-2"></i>Dashboard</h1>
        <small class="text-muted">Visão geral do sistema</small>
    </div>
@stop

@section('content')
    {{-- Estatísticas Principais --}}
    <div class="row">
        {{-- Total de Categorias --}}
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ number_format($stats['total_categories']) }}</h3>
                    <p>Categorias</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tags"></i>
                </div>
                <a href="{{ route('admin.categories.index') }}" class="small-box-footer">
                    Ver todas <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        {{-- Total de Marcas --}}
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($stats['total_brands']) }}</h3>
                    <p>Marcas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-copyright"></i>
                </div>
                <a href="{{ route('admin.brands.index') }}" class="small-box-footer">
                    Ver todas <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        {{-- Total de Fabricantes --}}
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($stats['total_manufacturers']) }}</h3>
                    <p>Fabricantes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-industry"></i>
                </div>
                <a href="{{ route('admin.manufacturers.index') }}" class="small-box-footer">
                    Ver todos <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        {{-- Total de Produtos --}}
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($stats['total_products']) }}</h3>
                    <p>Produtos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-box"></i>
                </div>
                <a href="{{ route('admin.products.index') }}" class="small-box-footer">
                    Ver todos <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Estatísticas de Produtos --}}
    <div class="row">
        {{-- Produtos Ativos --}}
        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Produtos Ativos</span>
                    <span class="info-box-number">{{ number_format($stats['active_products']) }}</span>
                </div>
            </div>
        </div>

        {{-- Produtos Inativos --}}
        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-danger"><i class="fas fa-times-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Produtos Inativos</span>
                    <span class="info-box-number">{{ number_format($stats['inactive_products']) }}</span>
                </div>
            </div>
        </div>

        {{-- Produtos em Estoque --}}
        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-primary"><i class="fas fa-cubes"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Com Estoque</span>
                    <span class="info-box-number">{{ number_format($stats['products_with_stock']) }}</span>
                </div>
            </div>
        </div>

        {{-- Produtos em Promoção --}}
        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-percentage"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Em Promoção</span>
                    <span class="info-box-number">{{ number_format($stats['products_on_sale']) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Produtos Adicionados Recentemente --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title"><i class="fas fa-clock mr-2"></i>Produtos Recentes</h3>
                </div>
                <div class="card-body p-0">
                    @if($recentProducts->count() > 0)
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Categoria</th>
                                    <th>Preço</th>
                                    <th class="text-center">Estoque</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentProducts as $product)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.products.show', $product->id) }}">
                                                {{ Str::limit($product->name, 30) }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ $product->category->name ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>R$ {{ number_format($product->price, 2, ',', '.') }}</td>
                                        <td class="text-center">
                                            @if($product->stock > 10)
                                                <span class="badge badge-success">{{ $product->stock }}</span>
                                            @elseif($product->stock > 0)
                                                <span class="badge badge-warning">{{ $product->stock }}</span>
                                            @else
                                                <span class="badge badge-danger">{{ $product->stock }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="p-3 text-center text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>Nenhum produto cadastrado ainda.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Top Categorias --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-success">
                    <h3 class="card-title"><i class="fas fa-star mr-2"></i>Top Categorias</h3>
                </div>
                <div class="card-body p-0">
                    @if($topCategories->count() > 0)
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Categoria</th>
                                    <th class="text-right">Total de Produtos</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topCategories as $category)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.categories.show', $category->id) }}">
                                                {{ $category->name }}
                                            </a>
                                        </td>
                                        <td class="text-right">
                                            <span class="badge badge-primary">{{ $category->products_count }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="p-3 text-center text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>Nenhuma categoria com produtos.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Produtos com Estoque Baixo --}}
    @if($lowStockProducts->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning">
                    <h3 class="card-title"><i class="fas fa-exclamation-triangle mr-2"></i>Produtos com Estoque Baixo (≤ 10 unidades)</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Categoria</th>
                                <th>Marca</th>
                                <th class="text-center">Estoque</th>
                                <th class="text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lowStockProducts as $product)
                                <tr>
                                    <td>{{ $product->name }}</td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ $product->category->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            {{ $product->brand->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-danger">{{ $product->stock }} unidades</span>
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Gráfico de Produtos por Categoria --}}
    @if($productsByCategory->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i>Distribuição de Produtos por Categoria</h3>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    @if($productsByCategory->count() > 0)
    const ctx = document.getElementById('categoryChart');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: {!! json_encode($productsByCategory->pluck('name')) !!},
            datasets: [{
                label: 'Produtos',
                data: {!! json_encode($productsByCategory->pluck('products_count')) !!},
                backgroundColor: [
                    '#4e5296',
                    '#0bc862',
                    '#17a2b8',
                    '#ffc107',
                    '#dc3545',
                    '#6c757d',
                    '#28a745',
                    '#007bff',
                    '#fd7e14',
                    '#e83e8c'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += context.parsed + ' produtos';
                            return label;
                        }
                    }
                }
            }
        }
    });
    @endif
</script>
@stop

@section('css')
<style>
    .small-box {
        border-radius: 10px;
        transition: transform 0.3s ease;
    }
    
    .small-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    
    .info-box {
        border-radius: 8px;
        transition: transform 0.3s ease;
    }
    
    .info-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    
    .card {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    
    .card-header {
        border-radius: 10px 10px 0 0 !important;
    }
</style>
@stop
