@extends('adminlte::page')

@section('title', 'Ordenar Seções da Home')

@section('content_header')
    <h1><i class="fa fa-sort-amount-asc"></i> Ordenar Seções da Home</h1>
@stop

@section('content')
{{-- Mensagem de feedback --}}
<div id="feedback-message" class="alert alert-success alert-dismissible" style="display: none;">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <i class="icon fa fa-check"></i> <span id="feedback-text"></span>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-arrows"></i> Arraste para Reordenar</h3>
                <p class="help-block" style="margin-top: 10px;">
                    <i class="fa fa-info-circle"></i>
                    Arraste os itens para alterar a ordem de exibição na página inicial.
                    A ordem é salva automaticamente.
                </p>
            </div>
            <div class="box-body">
                {{-- Lista de seções com drag-and-drop --}}
                <ul id="sortable-sections" class="list-group">
                    @foreach($sections as $section)
                    <li class="list-group-item section-item" data-id="{{ $section->id }}">
                        <div class="section-content">
                            {{-- Handle para arrastar --}}
                            <span class="drag-handle">
                                <i class="fa fa-bars"></i>
                            </span>

                            {{-- Ícone da seção --}}
                            <span class="section-icon">
                                <i class="{{ $section->icon ?? 'fa fa-puzzle-piece' }}"></i>
                            </span>

                            {{-- Informações da seção --}}
                            <div class="section-info">
                                <strong class="section-name">{{ $section->name }}</strong>
                                <small class="section-description text-muted">{{ $section->description }}</small>
                            </div>

                            {{-- Ações --}}
                            <div class="section-actions">
                                {{-- Link para editar itens (se houver) --}}
                                @if($section->hasAdminLink())
                                <a href="{{ $section->getAdminUrl() }}"
                                   class="btn btn-xs btn-default"
                                   title="Editar itens">
                                    <i class="fa fa-pencil"></i> Editar
                                </a>
                                @endif

                                {{-- Toggle ativo/inativo --}}
                                <button type="button"
                                        class="btn btn-xs toggle-active {{ $section->is_active ? 'btn-success' : 'btn-default' }}"
                                        data-id="{{ $section->id }}"
                                        title="{{ $section->is_active ? 'Clique para desativar' : 'Clique para ativar' }}">
                                    <i class="fa {{ $section->is_active ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                    {{ $section->is_active ? 'Ativo' : 'Inativo' }}
                                </button>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        {{-- Legenda --}}
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-question-circle"></i> Como usar</h3>
            </div>
            <div class="box-body">
                <p><i class="fa fa-bars text-muted"></i> <strong>Arrastar:</strong> Clique e arraste para reordenar</p>
                <p><i class="fa fa-eye text-success"></i> <strong>Ativo:</strong> Seção visível na home</p>
                <p><i class="fa fa-eye-slash text-muted"></i> <strong>Inativo:</strong> Seção oculta</p>
                <p><i class="fa fa-pencil text-primary"></i> <strong>Editar:</strong> Gerenciar itens da seção</p>
                <hr>
                <p class="text-muted">
                    <small>
                        <i class="fa fa-info-circle"></i>
                        As alterações são salvas automaticamente ao arrastar.
                    </small>
                </p>
            </div>
        </div>

        {{-- Preview rápido --}}
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-eye"></i> Ordem atual</h3>
            </div>
            <div class="box-body" id="order-preview">
                @foreach($sections as $index => $section)
                <div class="preview-item {{ !$section->is_active ? 'text-muted' : '' }}">
                    <span class="badge {{ $section->is_active ? 'bg-green' : 'bg-gray' }}">{{ $index + 1 }}</span>
                    {{ $section->name }}
                    @if(!$section->is_active)
                    <small>(oculto)</small>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    /* Estilo dos itens da lista */
    .section-item {
        cursor: move;
        padding: 15px;
        margin-bottom: 5px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background: #fff;
        transition: all 0.2s ease;
    }

    .section-item:hover {
        background: #f8f9fa;
        border-color: #3c8dbc;
    }

    .section-item.sortable-ghost {
        opacity: 0.4;
        background: #e3f2fd;
    }

    .section-item.sortable-chosen {
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    /* Conteúdo do item */
    .section-content {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    /* Handle para arrastar */
    .drag-handle {
        color: #aaa;
        font-size: 18px;
        cursor: grab;
    }

    .drag-handle:active {
        cursor: grabbing;
    }

    /* Ícone da seção */
    .section-icon {
        width: 40px;
        height: 40px;
        background: #f4f4f4;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: #666;
    }

    /* Informações da seção */
    .section-info {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .section-name {
        font-size: 15px;
        color: #333;
    }

    .section-description {
        font-size: 12px;
    }

    /* Ações */
    .section-actions {
        display: flex;
        gap: 5px;
    }

    /* Preview */
    .preview-item {
        padding: 5px 0;
        border-bottom: 1px solid #eee;
    }

    .preview-item:last-child {
        border-bottom: none;
    }

    /* Animação de feedback */
    @keyframes highlight {
        0% { background-color: #dff0d8; }
        100% { background-color: #fff; }
    }

    .section-item.updated {
        animation: highlight 1s ease;
    }
</style>
@stop

@section('js')
{{-- SortableJS para drag-and-drop --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sortableList = document.getElementById('sortable-sections');
    const feedbackDiv = document.getElementById('feedback-message');
    const feedbackText = document.getElementById('feedback-text');

    // Função para mostrar feedback
    function showFeedback(message, type = 'success') {
        feedbackDiv.className = `alert alert-${type} alert-dismissible`;
        feedbackText.textContent = message;
        feedbackDiv.style.display = 'block';

        // Esconde após 3 segundos
        setTimeout(() => {
            feedbackDiv.style.display = 'none';
        }, 3000);
    }

    // Inicializa SortableJS
    new Sortable(sortableList, {
        animation: 150,
        handle: '.drag-handle',
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        onEnd: function(evt) {
            // Coleta a nova ordem dos IDs
            const order = Array.from(sortableList.querySelectorAll('.section-item'))
                .map(item => item.dataset.id);

            // Envia para o servidor via AJAX
            fetch('{{ route("admin.home-sections.update-order") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ order: order })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showFeedback(data.message);
                    // Atualiza o preview
                    updatePreview();
                } else {
                    showFeedback('Erro ao salvar ordem', 'danger');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showFeedback('Erro ao salvar ordem', 'danger');
            });
        }
    });

    // Toggle ativo/inativo
    document.querySelectorAll('.toggle-active').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const button = this;

            fetch(`{{ url('admin/home-sections') }}/${id}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualiza o botão
                    if (data.is_active) {
                        button.classList.remove('btn-default');
                        button.classList.add('btn-success');
                        button.innerHTML = '<i class="fa fa-eye"></i> Ativo';
                        button.title = 'Clique para desativar';
                    } else {
                        button.classList.remove('btn-success');
                        button.classList.add('btn-default');
                        button.innerHTML = '<i class="fa fa-eye-slash"></i> Inativo';
                        button.title = 'Clique para ativar';
                    }

                    showFeedback(data.message);
                    updatePreview();
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showFeedback('Erro ao atualizar status', 'danger');
            });
        });
    });

    // Atualiza o preview da ordem
    function updatePreview() {
        const items = sortableList.querySelectorAll('.section-item');
        const previewDiv = document.getElementById('order-preview');
        let html = '';

        items.forEach((item, index) => {
            const name = item.querySelector('.section-name').textContent;
            const isActive = item.querySelector('.toggle-active').classList.contains('btn-success');
            const badgeClass = isActive ? 'bg-green' : 'bg-gray';
            const textClass = isActive ? '' : 'text-muted';
            const suffix = isActive ? '' : '<small>(oculto)</small>';

            html += `<div class="preview-item ${textClass}">
                <span class="badge ${badgeClass}">${index + 1}</span>
                ${name} ${suffix}
            </div>`;
        });

        previewDiv.innerHTML = html;
    }
});
</script>
@stop
