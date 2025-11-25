@extends('adminlte::page')

@section('title', 'Banners Duplos')

@section('content_header')
    <h1>Banners Duplos</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista de Pares de Banners</h3>
            <div class="card-tools">
                <a href="{{ route('admin.dual-banners.create') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i> Novo Par de Banners
                </a>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    {{ session('success') }}
                </div>
            @endif

            @if($dualBanners->isEmpty())
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Nenhum par de banners cadastrado.
                    <a href="{{ route('admin.dual-banners.create') }}">Clique aqui</a> para criar o primeiro.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 60px;">Ordem</th>
                                <th>Banner Esquerdo</th>
                                <th>Banner Direito</th>
                                <th style="width: 100px;">Status</th>
                                <th style="width: 180px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dualBanners as $dualBanner)
                                <tr>
                                    <td class="text-center">
                                        <span class="badge badge-secondary">{{ $dualBanner->order }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $dualBanner->getLeftImageUrl() }}"
                                                 alt="{{ $dualBanner->left_alt_text }}"
                                                 class="img-thumbnail mr-2"
                                                 style="max-width: 150px; max-height: 80px; object-fit: cover;">
                                            <div>
                                                <div><strong>Link:</strong> {{ $dualBanner->left_link ?: 'Não definido' }}</div>
                                                @if($dualBanner->left_start_date || $dualBanner->left_end_date)
                                                    <div class="text-sm text-muted">
                                                        <i class="fas fa-calendar"></i>
                                                        {{ $dualBanner->left_start_date?->format('d/m/Y') ?? '∞' }} -
                                                        {{ $dualBanner->left_end_date?->format('d/m/Y') ?? '∞' }}
                                                    </div>
                                                @endif
                                                @if($dualBanner->isLeftVisible())
                                                    <span class="badge badge-success">Visível</span>
                                                @else
                                                    <span class="badge badge-warning">Fora do período</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $dualBanner->getRightImageUrl() }}"
                                                 alt="{{ $dualBanner->right_alt_text }}"
                                                 class="img-thumbnail mr-2"
                                                 style="max-width: 150px; max-height: 80px; object-fit: cover;">
                                            <div>
                                                <div><strong>Link:</strong> {{ $dualBanner->right_link ?: 'Não definido' }}</div>
                                                @if($dualBanner->right_start_date || $dualBanner->right_end_date)
                                                    <div class="text-sm text-muted">
                                                        <i class="fas fa-calendar"></i>
                                                        {{ $dualBanner->right_start_date?->format('d/m/Y') ?? '∞' }} -
                                                        {{ $dualBanner->right_end_date?->format('d/m/Y') ?? '∞' }}
                                                    </div>
                                                @endif
                                                @if($dualBanner->isRightVisible())
                                                    <span class="badge badge-success">Visível</span>
                                                @else
                                                    <span class="badge badge-warning">Fora do período</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($dualBanner->active)
                                            <span class="badge badge-success">Ativo</span>
                                        @else
                                            <span class="badge badge-secondary">Inativo</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a href="{{ route('admin.dual-banners.edit', $dualBanner) }}"
                                               class="btn btn-sm btn-primary"
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.dual-banners.destroy', $dualBanner) }}"
                                                  method="POST"
                                                  style="display: inline;"
                                                  onsubmit="return confirm('Tem certeza que deseja excluir este par de banners?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Excluir">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="alert alert-info">
        <h5><i class="fas fa-info-circle"></i> Informações Importantes</h5>
        <ul class="mb-0">
            <li><strong>Tamanho recomendado das imagens:</strong> 670 x 380 pixels</li>
            <li>Cada registro gerencia um PAR de banners (esquerdo e direito) exibidos lado a lado</li>
            <li>Os banners são exibidos na ordem definida pelo campo "Ordem"</li>
            <li>As datas de início/fim são opcionais - deixe em branco para exibir sempre</li>
            <li>Apenas pares marcados como "Ativo" e dentro do período serão exibidos na home</li>
        </ul>
    </div>
@stop

@section('css')
    <style>
        .text-sm {
            font-size: 0.875rem;
        }
    </style>
@stop
