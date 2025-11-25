@extends('adminlte::page')

@section('title', 'Mensagens de Contato')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-inbox mr-2"></i> Mensagens de Contato</h1>
        <div>
            <a href="{{ route('admin.contact.edit') }}" class="btn btn-secondary">
                <i class="fas fa-cog mr-1"></i> Configurações
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

    <div class="card">
        <div class="card-header">
            {{-- Tabs de filtro --}}
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link {{ !request('filter') ? 'active' : '' }}" href="{{ route('admin.contact.messages') }}">
                        Todas <span class="badge badge-secondary ml-1">{{ $totalCount }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request('filter') === 'unread' ? 'active' : '' }}" href="{{ route('admin.contact.messages', ['filter' => 'unread']) }}">
                        Não Lidas <span class="badge badge-danger ml-1">{{ $unreadCount }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request('filter') === 'read' ? 'active' : '' }}" href="{{ route('admin.contact.messages', ['filter' => 'read']) }}">
                        Lidas <span class="badge badge-success ml-1">{{ $readCount }}</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body p-0">
            @if($messages->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <p class="text-muted">Nenhuma mensagem encontrada.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 40px;"></th>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Mensagem</th>
                                <th>Data</th>
                                <th style="width: 120px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($messages as $message)
                                <tr class="{{ !$message->read ? 'table-warning font-weight-bold' : '' }}">
                                    <td class="text-center">
                                        @if(!$message->read)
                                            <i class="fas fa-circle text-danger" style="font-size: 8px;" title="Não lida"></i>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $message->name }}
                                        @if($message->isRecent())
                                            <span class="badge badge-info">Novo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="mailto:{{ $message->email }}">{{ $message->email }}</a>
                                    </td>
                                    <td>
                                        <span title="{{ $message->message }}">
                                            {{ $message->getMessagePreview(50) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small>{{ $message->created_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.contact.messages.show', $message) }}" class="btn btn-info" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <form action="{{ route('admin.contact.messages.toggle-read', $message) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-secondary" title="{{ $message->read ? 'Marcar como não lida' : 'Marcar como lida' }}">
                                                    <i class="fas {{ $message->read ? 'fa-envelope' : 'fa-envelope-open' }}"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.contact.messages.destroy', $message) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('Tem certeza que deseja excluir esta mensagem?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger" title="Excluir">
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

        @if($messages->hasPages())
            <div class="card-footer">
                {{ $messages->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    {{-- Ações em lote --}}
    @if($totalCount > 0)
        <div class="row">
            <div class="col-md-6">
                @if($unreadCount > 0)
                    <form action="{{ route('admin.contact.messages.mark-all-read') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-success">
                            <i class="fas fa-check-double mr-1"></i> Marcar todas como lidas
                        </button>
                    </form>
                @endif
            </div>
            <div class="col-md-6 text-right">
                <form action="{{ route('admin.contact.messages.clear-old') }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Excluir todas as mensagens com mais de 90 dias?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="fas fa-broom mr-1"></i> Limpar mensagens antigas (+90 dias)
                    </button>
                </form>
            </div>
        </div>
    @endif
@stop
