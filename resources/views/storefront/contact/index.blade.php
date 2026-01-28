{{--
    Página de Contato da Loja
    Usa o layout storefront para herdar header, footer, CSS e JS base.
    Conteúdo específico: formulário de contato + informações de contato.
--}}
@extends('layouts.storefront')

@section('title', $settings->getSeoTitle())
@section('body_class', 'pg-contato')

@if($settings->meta_description)
    @section('meta_description', $settings->meta_description)
@endif

@push('styles')
<style>
    /* Estilos específicos da página de contato */
    .banner-interna {
        background-size: cover;
        background-position: center;
        min-height: 200px;
        display: flex;
        align-items: center;
    }
    .banner-interna .pg-titulo h1 {
        color: #fff;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        margin: 0;
        font-size: 2.5em;
    }
    .pg-internas {
        padding: 40px 0;
    }
    .form-contato .form-group {
        margin-bottom: 20px;
    }
    .form-contato label {
        font-weight: 600;
        color: var(--color-primary);
    }
    .form-contato .btn {
        padding: 12px 40px;
        font-size: 16px;
    }
    .box-endereco {
        background-color: #f8f9fa;
        padding: 30px;
        border-radius: 8px;
        border-left: 4px solid var(--color-primary);
    }
    .box-endereco ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .box-endereco ul li {
        padding: 10px 0;
        border-bottom: 1px solid #eee;
        display: flex;
        align-items: flex-start;
    }
    .box-endereco ul li:last-child {
        border-bottom: none;
    }
    .box-endereco ul li i {
        color: var(--color-primary);
        margin-right: 15px;
        font-size: 20px;
        width: 25px;
        text-align: center;
    }
    .box-endereco ul li a {
        color: #333;
    }
    .box-endereco ul li a:hover {
        color: var(--color-primary);
    }
    .intro-text {
        margin-bottom: 30px;
        line-height: 1.8;
        color: #666;
    }
    .alert-form {
        display: none;
        margin-bottom: 20px;
    }
</style>
@endpush

@section('content')

{{-- Banner Interno --}}
<section class="banner-interna" style="background-image: url('{{ $settings->getBannerUrl() }}');">
    <div class="pg-titulo">
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <h1 class="animated fadeInDown">{{ $settings->page_title }}</h1>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Conteúdo Principal --}}
<main class="pg-internas">
    <div class="container">
        <div class="row">

            {{-- Formulário de Contato --}}
            <div class="col-xs-12 col-md-6 form-contato animated fadeIn">
                <div class="row">

                    @if($settings->intro_text)
                        <div class="col-xs-12">
                            <div class="intro-text">
                                {!! nl2br(e($settings->intro_text)) !!}
                            </div>
                        </div>
                    @endif

                    {{-- Alert de Sucesso/Erro --}}
                    <div class="col-xs-12">
                        <div class="alert alert-success alert-form" id="alert-success">
                            <i class="fa fa-check-circle"></i> <span></span>
                        </div>
                        <div class="alert alert-danger alert-form" id="alert-error">
                            <i class="fa fa-exclamation-circle"></i> <span></span>
                        </div>
                    </div>

                    <form id="form-contato" class="form-loja">
                        <div class="col-xs-12">
                            <div class="form-group">
                                <label for="nome">Nome Completo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nome" name="name" maxlength="100" placeholder="Seu nome" required>
                            </div>
                        </div>
                        <div class="col-xs-12">
                            <div class="form-group">
                                <label for="email">E-mail <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" maxlength="100" placeholder="Seu e-mail" required>
                            </div>
                        </div>
                        <div class="col-xs-12">
                            <div class="form-group">
                                <label for="telefone">Telefone</label>
                                <input type="tel" class="form-control js-phone-mask" id="telefone" name="phone" placeholder="(XX) XXXXX-XXXX">
                            </div>
                        </div>
                        <div class="col-xs-12">
                            <div class="form-group">
                                <label for="mensagem">Mensagem <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="mensagem" name="message" maxlength="1000" rows="5" placeholder="Digite sua mensagem" required></textarea>
                            </div>
                        </div>
                        <div class="col-xs-12">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-large" id="btn-enviar">
                                    <i class="fa fa-paper-plane"></i> Enviar Mensagem
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>

            {{-- Informações de Contato --}}
            <div class="col-xs-12 col-md-6">
                <div class="box-endereco">
                    <ul class="list-unstyled">
                        @if($settings->whatsapp)
                            <li>
                                <i class="fa fa-whatsapp"></i>
                                <a href="{{ $settings->getWhatsAppUrl() }}" target="_blank">
                                    {{ $settings->whatsapp_display ?: $settings->whatsapp }}
                                </a>
                            </li>
                        @endif

                        @if($settings->email)
                            <li>
                                <i class="fa fa-envelope"></i>
                                <a href="mailto:{{ $settings->email }}">{{ $settings->email }}</a>
                            </li>
                        @endif

                        @foreach($settings->getBusinessHoursLines() as $line)
                            <li>
                                <i class="fa fa-clock-o"></i>
                                <span>{{ $line }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

        </div>
    </div>
</main>

{{-- Modal WhatsApp --}}
@if($settings->whatsapp)
<div id="whatsapp-modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div class="content text-center">
                    <div class="icon"><i class="fa fa-whatsapp" style="font-size: 50px; color: #25d366;"></i></div>
                    <h2>WhatsApp</h2>
                    <div class="tel" style="font-size: 24px; margin-top: 15px;">
                        <a href="{{ $settings->getWhatsAppUrl() }}" target="_blank">
                            {{ $settings->whatsapp_display ?: $settings->whatsapp }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Máscara de telefone
    $('.js-phone-mask').inputmask({
        mask: ['(99) 9999-9999', '(99) 99999-9999'],
        keepStatic: true
    });

    // Configurar CSRF token para requisições AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Envio do formulário via AJAX
    $('#form-contato').on('submit', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $btn = $('#btn-enviar');
        var $alertSuccess = $('#alert-success');
        var $alertError = $('#alert-error');

        // Esconde alertas anteriores
        $alertSuccess.hide();
        $alertError.hide();

        // Desabilita botão durante envio
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Enviando...');

        $.ajax({
            url: '{{ route("contact.send") }}',
            method: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $form[0].reset();
                    $alertSuccess.find('span').text(response.message);
                    $alertSuccess.slideDown();
                    $('html, body').animate({
                        scrollTop: $('.form-contato').offset().top - 100
                    }, 500);
                } else {
                    $alertError.find('span').text(response.message);
                    $alertError.slideDown();
                }
            },
            error: function(xhr) {
                var message = 'Ocorreu um erro ao enviar sua mensagem. Por favor, tente novamente.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        var errors = xhr.responseJSON.errors;
                        message = errors[Object.keys(errors)[0]][0];
                    }
                }
                $alertError.find('span').text(message);
                $alertError.slideDown();
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Enviar Mensagem');
            }
        });
    });
});
</script>
@endpush
