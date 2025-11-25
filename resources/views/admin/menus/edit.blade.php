@extends('adminlte::page')

@section('title', 'Editar Menu')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-edit mr-2"></i> Editar Menu: {{ $menu->name }}</h1>
        <div>
            <a href="{{ route('admin.menus.items', $menu) }}" class="btn btn-primary">
                <i class="fas fa-list mr-1"></i> Gerenciar Itens
            </a>
            <a href="{{ route('admin.menus.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Voltar
            </a>
        </div>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <form action="{{ route('admin.menus.update', $menu) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Informações do Menu</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Nome do Menu <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $menu->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="slug">Identificador (Slug)</label>
                            <input type="text" name="slug" id="slug"
                                   class="form-control @error('slug') is-invalid @enderror"
                                   value="{{ old('slug', $menu->slug) }}">
                            @error('slug')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="location">Localização <span class="text-danger">*</span></label>
                            <select name="location" id="location"
                                    class="form-control @error('location') is-invalid @enderror" required>
                                @foreach($locations as $value => $label)
                                    <option value="{{ $value }}" {{ old('location', $menu->location) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('location')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description">Descrição</label>
                            <textarea name="description" id="description"
                                      class="form-control @error('description') is-invalid @enderror"
                                      rows="2">{{ old('description', $menu->description) }}</textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="active" name="active" value="1"
                                       {{ old('active', $menu->active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="active">Menu Ativo</label>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save mr-1"></i> Salvar Alterações
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-md-4">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i> Informações</h3>
                </div>
                <div class="card-body">
                    <dl>
                        <dt>ID:</dt>
                        <dd>{{ $menu->id }}</dd>

                        <dt>Criado em:</dt>
                        <dd>{{ $menu->created_at->format('d/m/Y H:i') }}</dd>

                        <dt>Atualizado em:</dt>
                        <dd>{{ $menu->updated_at->format('d/m/Y H:i') }}</dd>

                        <dt>Total de Itens:</dt>
                        <dd>{{ $menu->items()->count() }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-exclamation-triangle mr-2"></i> Zona de Perigo</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Excluir este menu removerá todos os itens associados.</p>
                    <form action="{{ route('admin.menus.destroy', $menu) }}" method="POST"
                          onsubmit="return confirm('Tem certeza? Esta ação não pode ser desfeita!')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash mr-1"></i> Excluir Menu
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
