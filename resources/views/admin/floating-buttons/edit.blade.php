@extends('adminlte::page')

@section('title', 'Ícones Flutuantes')

@section('content_header')
    <h1>Ícones Flutuantes</h1>
@stop

@section('content')
    <div class="card">
        <form action="{{ route('admin.floating-buttons.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                {{-- Posição dos ícones --}}
                <div class="form-group">
                    <label>Posição na tela</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="position" id="posLeft"
                                   value="left" {{ old('position', $config->position) === 'left' ? 'checked' : '' }}>
                            <label class="form-check-label" for="posLeft">
                                <i class="fas fa-arrow-left"></i> Esquerda
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="position" id="posRight"
                                   value="right" {{ old('position', $config->position) === 'right' ? 'checked' : '' }}>
                            <label class="form-check-label" for="posRight">
                                <i class="fas fa-arrow-right"></i> Direita
                            </label>
                        </div>
                    </div>
                    @error('position')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <hr>

                {{-- WhatsApp --}}
                <h5><i class="fab fa-whatsapp text-success"></i> WhatsApp</h5>
                <p class="text-muted small">Deixe o número em branco para ocultar o ícone do WhatsApp.</p>

                <div class="form-group">
                    <label for="whatsapp_number">Número do WhatsApp (com DDI e DDD)</label>
                    <input type="text" name="whatsapp_number" id="whatsapp_number"
                           class="form-control @error('whatsapp_number') is-invalid @enderror"
                           value="{{ old('whatsapp_number', $config->whatsapp_number) }}"
                           placeholder="Ex: 5521934783000">
                    <small class="form-text text-muted">Formato: DDI + DDD + número (apenas números)</small>
                    @error('whatsapp_number')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="whatsapp_message">Mensagem pré-configurada</label>
                    <input type="text" name="whatsapp_message" id="whatsapp_message"
                           class="form-control @error('whatsapp_message') is-invalid @enderror"
                           value="{{ old('whatsapp_message', $config->whatsapp_message) }}"
                           placeholder="Ex: Olá! Gostaria de saber mais sobre os produtos.">
                    <small class="form-text text-muted">Texto enviado automaticamente ao abrir o chat</small>
                    @error('whatsapp_message')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <hr>

                {{-- Instagram --}}
                <h5><i class="fab fa-instagram text-danger"></i> Instagram</h5>
                <p class="text-muted small">Deixe o link em branco para ocultar o ícone do Instagram.</p>

                <div class="form-group">
                    <label for="instagram_url">Link do perfil do Instagram</label>
                    <input type="url" name="instagram_url" id="instagram_url"
                           class="form-control @error('instagram_url') is-invalid @enderror"
                           value="{{ old('instagram_url', $config->instagram_url) }}"
                           placeholder="Ex: https://www.instagram.com/deepfreezecongelados/">
                    @error('instagram_url')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Salvar Configurações
                </button>
            </div>
        </form>
    </div>
@stop
