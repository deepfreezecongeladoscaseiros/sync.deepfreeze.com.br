@extends('adminlte::page')

@section('title', 'Criar Menu')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-plus mr-2"></i> Criar Novo Menu</h1>
        <a href="{{ route('admin.menus.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Voltar
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <form action="{{ route('admin.menus.store') }}" method="POST">
                @csrf

                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Informações do Menu</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Nome do Menu <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required
                                   placeholder="Ex: Menu Principal, Menu Rodapé">
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="slug">Identificador (Slug)</label>
                            <input type="text" name="slug" id="slug"
                                   class="form-control @error('slug') is-invalid @enderror"
                                   value="{{ old('slug') }}"
                                   placeholder="Ex: main, footer, mobile">
                            <small class="text-muted">Deixe vazio para gerar automaticamente</small>
                            @error('slug')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="location">Localização <span class="text-danger">*</span></label>
                            <select name="location" id="location"
                                    class="form-control @error('location') is-invalid @enderror" required>
                                @foreach($locations as $value => $label)
                                    <option value="{{ $value }}" {{ old('location') == $value ? 'selected' : '' }}>
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
                                      rows="2" placeholder="Descrição interna para referência">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="active" name="active" value="1"
                                       {{ old('active', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="active">Menu Ativo</label>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save mr-1"></i> Criar Menu
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-md-4">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i> Dicas</h3>
                </div>
                <div class="card-body">
                    <p><strong>Localizações:</strong></p>
                    <ul class="pl-3">
                        <li><strong>Cabeçalho:</strong> Menu principal no topo</li>
                        <li><strong>Mobile:</strong> Menu hamburguer</li>
                        <li><strong>Rodapé:</strong> Links no footer</li>
                        <li><strong>Customizada:</strong> Posição livre</li>
                    </ul>
                    <hr>
                    <p class="mb-0"><small class="text-muted">
                        Após criar o menu, você poderá adicionar itens como categorias,
                        páginas e links externos.
                    </small></p>
                </div>
            </div>
        </div>
    </div>
@stop
