@extends('adminlte::page')

@section('title', 'Mensagem de ' . $message->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-envelope-open-text mr-2"></i> Mensagem de Contato</h1>
        <a href="{{ route('admin.contact.messages') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Voltar
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            {{-- Card: Mensagem --}}
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user mr-2"></i> {{ $message->name }}
                    </h3>
                    <div class="card-tools">
                        <span class="badge {{ $message->read ? 'badge-success' : 'badge-danger' }}">
                            {{ $message->read ? 'Lida' : 'Não Lida' }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <strong><i class="fas fa-envelope mr-2"></i> E-mail:</strong>
                            <a href="mailto:{{ $message->email }}">{{ $message->email }}</a>
                        </div>
                        <div class="col-md-6">
                            <strong><i class="fas fa-phone mr-2"></i> Telefone:</strong>
                            @if($message->phone)
                                <a href="tel:{{ $message->phone }}">{{ $message->getFormattedPhone() }}</a>
                            @else
                                <span class="text-muted">Não informado</span>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <div class="message-content" style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; border-left: 4px solid #007bff;">
                        <h5 class="mb-3"><i class="fas fa-comment mr-2"></i> Mensagem:</h5>
                        <p style="white-space: pre-wrap; line-height: 1.8;">{{ $message->message }}</p>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">
                            <i class="fas fa-clock mr-1"></i>
                            Recebida em {{ $message->created_at->format('d/m/Y \à\s H:i') }}
                            ({{ $message->created_at->diffForHumans() }})
                        </span>

                        <div class="btn-group">
                            <a href="mailto:{{ $message->email }}?subject=Re: Contato - {{ config('app.name') }}" class="btn btn-primary">
                                <i class="fas fa-reply mr-1"></i> Responder por E-mail
                            </a>
                            @if($message->phone)
                                <a href="https://api.whatsapp.com/send?phone={{ preg_replace('/[^0-9]/', '', $message->phone) }}" target="_blank" class="btn btn-success">
                                    <i class="fab fa-whatsapp mr-1"></i> WhatsApp
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            {{-- Card: Informações Técnicas --}}
            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i> Informações Técnicas</h3>
                </div>
                <div class="card-body">
                    <dl>
                        <dt><i class="fas fa-fingerprint mr-2"></i> ID:</dt>
                        <dd>{{ $message->id }}</dd>

                        <dt class="mt-3"><i class="fas fa-globe mr-2"></i> IP de Origem:</dt>
                        <dd><code>{{ $message->ip_address ?? 'N/A' }}</code></dd>

                        <dt class="mt-3"><i class="fas fa-desktop mr-2"></i> Navegador:</dt>
                        <dd><small class="text-muted">{{ Str::limit($message->user_agent, 100) ?? 'N/A' }}</small></dd>

                        @if($message->read && $message->read_at)
                            <dt class="mt-3"><i class="fas fa-eye mr-2"></i> Lida em:</dt>
                            <dd>{{ $message->read_at->format('d/m/Y H:i') }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Card: Ações --}}
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-cogs mr-2"></i> Ações</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.contact.messages.toggle-read', $message) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-block {{ $message->read ? 'btn-outline-warning' : 'btn-outline-success' }}">
                            <i class="fas {{ $message->read ? 'fa-envelope' : 'fa-envelope-open' }} mr-1"></i>
                            {{ $message->read ? 'Marcar como Não Lida' : 'Marcar como Lida' }}
                        </button>
                    </form>

                    <form action="{{ route('admin.contact.messages.destroy', $message) }}" method="POST"
                          onsubmit="return confirm('Tem certeza que deseja excluir esta mensagem?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-block">
                            <i class="fas fa-trash mr-1"></i> Excluir Mensagem
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
