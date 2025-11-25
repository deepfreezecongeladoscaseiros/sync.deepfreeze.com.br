@extends('adminlte::page')

@section('title', 'Configurações de Contato')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-envelope mr-2"></i> Configurações de Contato</h1>
        <a href="{{ route('admin.contact.messages') }}" class="btn btn-info">
            <i class="fas fa-inbox mr-1"></i> Ver Mensagens
            @if($unreadCount > 0)
                <span class="badge badge-danger ml-1">{{ $unreadCount }}</span>
            @endif
        </a>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.contact.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row">
            {{-- Coluna Esquerda: Informações da Página --}}
            <div class="col-md-8">
                {{-- Card: Informações Básicas --}}
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i> Informações da Página</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="page_title">Título da Página <span class="text-danger">*</span></label>
                            <input type="text" name="page_title" id="page_title" class="form-control @error('page_title') is-invalid @enderror"
                                   value="{{ old('page_title', $settings->page_title) }}" required>
                            @error('page_title')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="intro_text">Texto Introdutório</label>
                            <textarea name="intro_text" id="intro_text" class="form-control @error('intro_text') is-invalid @enderror"
                                      rows="3" placeholder="Texto exibido acima do formulário...">{{ old('intro_text', $settings->intro_text) }}</textarea>
                            <small class="text-muted">Texto que aparece acima do formulário de contato</small>
                            @error('intro_text')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Card: Informações de Contato --}}
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-phone mr-2"></i> Informações de Contato</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="whatsapp">WhatsApp (número)</label>
                                    <input type="text" name="whatsapp" id="whatsapp" class="form-control @error('whatsapp') is-invalid @enderror"
                                           value="{{ old('whatsapp', $settings->whatsapp) }}" placeholder="5511999999999">
                                    <small class="text-muted">Apenas números, com código do país (ex: 5511999999999)</small>
                                    @error('whatsapp')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="whatsapp_display">WhatsApp (exibição)</label>
                                    <input type="text" name="whatsapp_display" id="whatsapp_display" class="form-control @error('whatsapp_display') is-invalid @enderror"
                                           value="{{ old('whatsapp_display', $settings->whatsapp_display) }}" placeholder="(11) 99999-9999">
                                    <small class="text-muted">Formato que será exibido na página</small>
                                    @error('whatsapp_display')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">E-mail de Contato</label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $settings->email) }}" placeholder="contato@exemplo.com.br">
                            <small class="text-muted">E-mail exibido na página para o visitante</small>
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="business_hours">Horário de Atendimento</label>
                            <textarea name="business_hours" id="business_hours" class="form-control @error('business_hours') is-invalid @enderror"
                                      rows="3" placeholder="Segunda a Sexta das 8h às 18h...">{{ old('business_hours', $settings->business_hours) }}</textarea>
                            <small class="text-muted">Cada linha será exibida separadamente</small>
                            @error('business_hours')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Card: Configurações do Formulário --}}
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-paper-plane mr-2"></i> Configurações do Formulário</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="form_recipient_email">E-mail que Receberá as Mensagens</label>
                            <input type="email" name="form_recipient_email" id="form_recipient_email" class="form-control @error('form_recipient_email') is-invalid @enderror"
                                   value="{{ old('form_recipient_email', $settings->form_recipient_email) }}" placeholder="contato@exemplo.com.br">
                            <small class="text-muted">As mensagens do formulário serão enviadas para este e-mail</small>
                            @error('form_recipient_email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="form_subject">Assunto do E-mail</label>
                            <input type="text" name="form_subject" id="form_subject" class="form-control @error('form_subject') is-invalid @enderror"
                                   value="{{ old('form_subject', $settings->form_subject) }}" placeholder="Nova mensagem de contato">
                            @error('form_subject')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Card: SEO --}}
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-search mr-2"></i> SEO (Otimização para Buscadores)</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="meta_title">Meta Title</label>
                            <input type="text" name="meta_title" id="meta_title" class="form-control @error('meta_title') is-invalid @enderror"
                                   value="{{ old('meta_title', $settings->meta_title) }}" placeholder="Contato - Nome da Loja">
                            <small class="text-muted">Deixe vazio para usar o título da página</small>
                            @error('meta_title')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="meta_description">Meta Description</label>
                            <textarea name="meta_description" id="meta_description" class="form-control @error('meta_description') is-invalid @enderror"
                                      rows="2" maxlength="500" placeholder="Entre em contato conosco...">{{ old('meta_description', $settings->meta_description) }}</textarea>
                            <small class="text-muted">Máximo 500 caracteres</small>
                            @error('meta_description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Coluna Direita: Banner e Status --}}
            <div class="col-md-4">
                {{-- Card: Status --}}
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-toggle-on mr-2"></i> Status</h3>
                    </div>
                    <div class="card-body">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="active" name="active" value="1"
                                   {{ old('active', $settings->active) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="active">Página Ativa</label>
                        </div>
                        <small class="text-muted d-block mt-2">Se desativada, a página retornará erro 404</small>
                    </div>
                </div>

                {{-- Card: Banner --}}
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-image mr-2"></i> Banner Interno</h3>
                    </div>
                    <div class="card-body">
                        @if($settings->banner_image)
                            <div class="mb-3">
                                <img src="{{ Storage::url($settings->banner_image) }}" alt="Banner atual" class="img-fluid rounded">
                            </div>
                        @else
                            <div class="mb-3">
                                <img src="{{ $settings->getBannerUrl() }}" alt="Banner padrão" class="img-fluid rounded">
                                <small class="text-muted d-block mt-1">Banner padrão (nenhuma imagem definida)</small>
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="banner_image">Alterar Banner</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('banner_image') is-invalid @enderror"
                                       id="banner_image" name="banner_image" accept="image/*">
                                <label class="custom-file-label" for="banner_image">Escolher arquivo...</label>
                            </div>
                            <small class="text-muted">JPG, PNG ou WebP. Tamanho recomendado: 1920x300px</small>
                            @error('banner_image')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Card: Preview --}}
                <div class="card card-dark">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-eye mr-2"></i> Visualizar</h3>
                    </div>
                    <div class="card-body text-center">
                        <a href="{{ route('contact') }}" target="_blank" class="btn btn-outline-primary btn-block">
                            <i class="fas fa-external-link-alt mr-2"></i> Abrir Página de Contato
                        </a>
                    </div>
                </div>

                {{-- Botão Salvar --}}
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-success btn-lg btn-block">
                            <i class="fas fa-save mr-2"></i> Salvar Configurações
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Atualiza label do file input
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });
});
</script>
@stop
