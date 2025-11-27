@extends('adminlte::page')

@section('title', 'Montar Home Page')

@section('content_header')
    <h1><i class="fa fa-puzzle-piece"></i> Montar Home Page</h1>
@stop

@section('content')
{{-- Mensagem de feedback --}}
<div id="feedback-message" class="alert alert-success alert-dismissible" style="display: none;">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <i class="icon fa fa-check"></i> <span id="feedback-text"></span>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-th-list"></i> Blocos da Home Page</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#addBlockModal">
                        <i class="fa fa-plus"></i> Adicionar Bloco
                    </button>
                </div>
            </div>
            <div class="box-body">
                <p class="text-muted">
                    <i class="fa fa-info-circle"></i>
                    Arraste os blocos para alterar a ordem. Você pode adicionar múltiplas galerias, banners, etc.
                </p>

                {{-- Lista de blocos com drag-and-drop --}}
                <ul id="sortable-blocks" class="list-group">
                    @forelse($blocks as $block)
                    <li class="list-group-item block-item" data-id="{{ $block->id }}">
                        <div class="block-content">
                            {{-- Handle para arrastar --}}
                            <span class="drag-handle">
                                <i class="fa fa-bars"></i>
                            </span>

                            {{-- Ícone do tipo --}}
                            <span class="block-icon">
                                <i class="{{ $block->type_icon }}"></i>
                            </span>

                            {{-- Informações do bloco --}}
                            <div class="block-info">
                                <strong class="block-title">{{ $block->display_title }}</strong>
                                <small class="block-type text-muted">{{ $block->type_label }}</small>
                            </div>

                            {{-- Ações --}}
                            <div class="block-actions">
                                {{-- Link para editar itens --}}
                                @if($block->getAdminUrl())
                                <a href="{{ $block->getAdminUrl() }}"
                                   class="btn btn-xs btn-default"
                                   title="Editar itens deste tipo">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                @endif

                                {{-- Toggle ativo/inativo --}}
                                <button type="button"
                                        class="btn btn-xs toggle-active {{ $block->is_active ? 'btn-success' : 'btn-default' }}"
                                        data-id="{{ $block->id }}"
                                        title="{{ $block->is_active ? 'Clique para desativar' : 'Clique para ativar' }}">
                                    <i class="fa {{ $block->is_active ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                </button>

                                {{-- Remover bloco --}}
                                <button type="button"
                                        class="btn btn-xs btn-danger remove-block"
                                        data-id="{{ $block->id }}"
                                        title="Remover bloco">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </li>
                    @empty
                    <li class="list-group-item text-center text-muted" id="empty-message">
                        <i class="fa fa-inbox fa-3x"></i>
                        <p class="mt-2">Nenhum bloco adicionado ainda.</p>
                        <p>Clique em "Adicionar Bloco" para começar a montar sua home page.</p>
                    </li>
                    @endforelse
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
                <p><i class="fa fa-plus text-success"></i> <strong>Adicionar:</strong> Insere novo bloco ao final</p>
                <p><i class="fa fa-bars text-muted"></i> <strong>Arrastar:</strong> Reordena os blocos</p>
                <p><i class="fa fa-eye text-success"></i> <strong>Ativo:</strong> Bloco visível na home</p>
                <p><i class="fa fa-eye-slash text-muted"></i> <strong>Inativo:</strong> Bloco oculto</p>
                <p><i class="fa fa-pencil text-primary"></i> <strong>Editar:</strong> Gerenciar itens do tipo</p>
                <p><i class="fa fa-trash text-danger"></i> <strong>Remover:</strong> Remove o bloco da home</p>
                <hr>
                <p class="text-muted">
                    <small>
                        <i class="fa fa-lightbulb-o"></i>
                        Você pode adicionar múltiplas galerias de produtos, intercalando com banners!
                    </small>
                </p>
            </div>
        </div>

        {{-- Preview da ordem --}}
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-eye"></i> Preview da Home</h3>
            </div>
            <div class="box-body" id="order-preview">
                @foreach($blocks as $index => $block)
                <div class="preview-item {{ !$block->is_active ? 'text-muted' : '' }}" data-id="{{ $block->id }}">
                    <span class="badge {{ $block->is_active ? 'bg-green' : 'bg-gray' }}">{{ $index + 1 }}</span>
                    <i class="{{ $block->type_icon }}"></i>
                    {{ Str::limit($block->display_title, 20) }}
                    @if(!$block->is_active)
                    <small>(oculto)</small>
                    @endif
                </div>
                @endforeach

                @if($blocks->isEmpty())
                <p class="text-muted text-center">
                    <small>Adicione blocos para visualizar aqui</small>
                </p>
                @endif
            </div>
        </div>

        {{-- Link para ver a home --}}
        <div class="box box-success">
            <div class="box-body text-center">
                <a href="{{ route('home') }}" target="_blank" class="btn btn-success btn-block">
                    <i class="fa fa-external-link"></i> Ver Home Page
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Modal para adicionar bloco --}}
<div class="modal fade" id="addBlockModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><i class="fa fa-plus"></i> Adicionar Bloco</h4>
            </div>
            <div class="modal-body">
                <form id="addBlockForm">
                    {{-- Seleção do tipo de bloco --}}
                    <div class="form-group">
                        <label for="block_type">Tipo de Bloco <span class="text-danger">*</span></label>
                        <select class="form-control" id="block_type" name="type" required>
                            <option value="">Selecione o tipo...</option>
                            @foreach($blockTypes as $typeKey => $typeConfig)
                            <option value="{{ $typeKey }}"
                                    data-requires-reference="{{ $typeConfig['requires_reference'] ? '1' : '0' }}"
                                    data-icon="{{ $typeConfig['icon'] }}">
                                {{ $typeConfig['label'] }}
                            </option>
                            @endforeach
                        </select>
                        <p class="help-block" id="type_description"></p>
                    </div>

                    {{-- Seleção do item específico (aparece só para tipos que requerem) --}}
                    <div class="form-group" id="reference_group" style="display: none;">
                        <label for="reference_id">Selecione o Item <span class="text-danger">*</span></label>
                        <select class="form-control" id="reference_id" name="reference_id">
                            <option value="">Carregando...</option>
                        </select>
                        <p class="help-block">
                            <a href="#" id="create_new_item_link" target="_blank">
                                <i class="fa fa-plus"></i> Criar novo item
                            </a>
                        </p>
                    </div>

                    {{-- Título customizado (opcional) --}}
                    <div class="form-group">
                        <label for="custom_title">Título Customizado <small class="text-muted">(opcional)</small></label>
                        <input type="text" class="form-control" id="custom_title" name="custom_title"
                               placeholder="Deixe em branco para usar o título padrão">
                        <p class="help-block">Sobrescreve o título original do item.</p>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnAddBlock">
                    <i class="fa fa-plus"></i> Adicionar
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
    /* Estilo dos itens da lista */
    .block-item {
        cursor: move;
        padding: 12px 15px;
        margin-bottom: 5px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background: #fff;
        transition: all 0.2s ease;
    }

    .block-item:hover {
        background: #f8f9fa;
        border-color: #3c8dbc;
    }

    .block-item.sortable-ghost {
        opacity: 0.4;
        background: #e3f2fd;
    }

    .block-item.sortable-chosen {
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    /* Conteúdo do item */
    .block-content {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    /* Handle para arrastar */
    .drag-handle {
        color: #aaa;
        font-size: 16px;
        cursor: grab;
        padding: 5px;
    }

    .drag-handle:active {
        cursor: grabbing;
    }

    /* Ícone do bloco */
    .block-icon {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        color: #555;
    }

    /* Informações do bloco */
    .block-info {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .block-title {
        font-size: 14px;
        color: #333;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .block-type {
        font-size: 11px;
    }

    /* Ações */
    .block-actions {
        display: flex;
        gap: 4px;
        flex-shrink: 0;
    }

    /* Preview */
    .preview-item {
        padding: 6px 0;
        border-bottom: 1px solid #eee;
        font-size: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .preview-item:last-child {
        border-bottom: none;
    }

    .preview-item .badge {
        font-size: 10px;
        min-width: 20px;
    }

    /* Modal - Tipos de bloco como cards */
    #block_type option {
        padding: 10px;
    }

    /* Animação de feedback */
    @keyframes highlight {
        0% { background-color: #dff0d8; }
        100% { background-color: #fff; }
    }

    .block-item.updated {
        animation: highlight 1s ease;
    }

    .block-item.new-item {
        animation: highlight 2s ease;
    }

    /* Empty state */
    #empty-message {
        padding: 40px 20px;
    }

    #empty-message i {
        color: #ccc;
    }
</style>
@stop

@section('js')
{{-- SortableJS para drag-and-drop --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sortableList = document.getElementById('sortable-blocks');
    const feedbackDiv = document.getElementById('feedback-message');
    const feedbackText = document.getElementById('feedback-text');
    const emptyMessage = document.getElementById('empty-message');

    // Configuração dos tipos de blocos (do PHP)
    const blockTypes = @json($blockTypes);

    // Função para mostrar feedback
    function showFeedback(message, type = 'success') {
        feedbackDiv.className = `alert alert-${type} alert-dismissible`;
        feedbackText.textContent = message;
        feedbackDiv.style.display = 'block';

        setTimeout(() => {
            feedbackDiv.style.display = 'none';
        }, 3000);
    }

    // Inicializa SortableJS
    const sortable = new Sortable(sortableList, {
        animation: 150,
        handle: '.drag-handle',
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        filter: '#empty-message', // Ignora o item de mensagem vazia
        onEnd: function(evt) {
            const order = Array.from(sortableList.querySelectorAll('.block-item'))
                .map(item => item.dataset.id);

            if (order.length === 0) return;

            fetch('{{ route("admin.home-blocks.update-order") }}', {
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
    document.addEventListener('click', function(e) {
        if (e.target.closest('.toggle-active')) {
            const button = e.target.closest('.toggle-active');
            const id = button.dataset.id;

            fetch(`{{ url('admin/home-blocks') }}/${id}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.is_active) {
                        button.classList.remove('btn-default');
                        button.classList.add('btn-success');
                        button.innerHTML = '<i class="fa fa-eye"></i>';
                        button.title = 'Clique para desativar';
                    } else {
                        button.classList.remove('btn-success');
                        button.classList.add('btn-default');
                        button.innerHTML = '<i class="fa fa-eye-slash"></i>';
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
        }
    });

    // Remover bloco
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-block')) {
            const button = e.target.closest('.remove-block');
            const id = button.dataset.id;
            const item = button.closest('.block-item');
            const title = item.querySelector('.block-title').textContent;

            if (!confirm(`Remover o bloco "${title}" da home page?`)) {
                return;
            }

            fetch(`{{ url('admin/home-blocks') }}/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    item.remove();
                    showFeedback(data.message);
                    updatePreview();

                    // Mostra mensagem de vazio se não houver mais blocos
                    if (sortableList.querySelectorAll('.block-item').length === 0) {
                        sortableList.innerHTML = `
                            <li class="list-group-item text-center text-muted" id="empty-message">
                                <i class="fa fa-inbox fa-3x"></i>
                                <p class="mt-2">Nenhum bloco adicionado ainda.</p>
                                <p>Clique em "Adicionar Bloco" para começar a montar sua home page.</p>
                            </li>
                        `;
                    }
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showFeedback('Erro ao remover bloco', 'danger');
            });
        }
    });

    // Modal: Ao selecionar tipo de bloco
    const blockTypeSelect = document.getElementById('block_type');
    const referenceGroup = document.getElementById('reference_group');
    const referenceSelect = document.getElementById('reference_id');
    const typeDescription = document.getElementById('type_description');
    const createNewItemLink = document.getElementById('create_new_item_link');

    blockTypeSelect.addEventListener('change', function() {
        const type = this.value;

        if (!type) {
            referenceGroup.style.display = 'none';
            typeDescription.textContent = '';
            return;
        }

        const config = blockTypes[type];
        typeDescription.textContent = config.description || '';

        if (config.requires_reference) {
            referenceGroup.style.display = 'block';
            referenceSelect.innerHTML = '<option value="">Carregando...</option>';

            // Atualiza link para criar novo item
            createNewItemLink.href = '{{ url("admin") }}/' + config.admin_route.replace('admin.', '').replace('.index', '').replace(/\./g, '/');

            // Busca itens disponíveis
            fetch(`{{ url('admin/home-blocks/items') }}/${type}`)
                .then(response => response.json())
                .then(data => {
                    if (data.items && data.items.length > 0) {
                        let options = '<option value="">Selecione...</option>';
                        data.items.forEach(item => {
                            const status = item.active ? '' : ' (inativo)';
                            options += `<option value="${item.id}">${item.title}${status}</option>`;
                        });
                        referenceSelect.innerHTML = options;
                    } else {
                        referenceSelect.innerHTML = '<option value="">Nenhum item disponível</option>';
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    referenceSelect.innerHTML = '<option value="">Erro ao carregar itens</option>';
                });
        } else {
            referenceGroup.style.display = 'none';
        }
    });

    // Adicionar bloco
    document.getElementById('btnAddBlock').addEventListener('click', function() {
        const type = blockTypeSelect.value;
        const referenceId = referenceSelect.value;
        const customTitle = document.getElementById('custom_title').value;

        if (!type) {
            alert('Selecione o tipo de bloco.');
            return;
        }

        const config = blockTypes[type];
        if (config.requires_reference && !referenceId) {
            alert('Selecione o item para este tipo de bloco.');
            return;
        }

        const data = {
            type: type,
            reference_id: referenceId || null,
            custom_title: customTitle || null
        };

        fetch('{{ route("admin.home-blocks.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove mensagem de vazio se existir
                const emptyMsg = document.getElementById('empty-message');
                if (emptyMsg) emptyMsg.remove();

                // Adiciona o novo bloco na lista
                const block = data.block;
                const newItem = document.createElement('li');
                newItem.className = 'list-group-item block-item new-item';
                newItem.dataset.id = block.id;
                newItem.innerHTML = `
                    <div class="block-content">
                        <span class="drag-handle"><i class="fa fa-bars"></i></span>
                        <span class="block-icon"><i class="${block.type_icon}"></i></span>
                        <div class="block-info">
                            <strong class="block-title">${block.display_title}</strong>
                            <small class="block-type text-muted">${block.type_label}</small>
                        </div>
                        <div class="block-actions">
                            ${block.admin_url ? `<a href="${block.admin_url}" class="btn btn-xs btn-default" title="Editar itens"><i class="fa fa-pencil"></i></a>` : ''}
                            <button type="button" class="btn btn-xs toggle-active btn-success" data-id="${block.id}" title="Clique para desativar">
                                <i class="fa fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-xs btn-danger remove-block" data-id="${block.id}" title="Remover bloco">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
                sortableList.appendChild(newItem);

                // Fecha o modal e limpa o form
                $('#addBlockModal').modal('hide');
                document.getElementById('addBlockForm').reset();
                referenceGroup.style.display = 'none';
                typeDescription.textContent = '';

                showFeedback(data.message);
                updatePreview();
            } else {
                alert(data.message || 'Erro ao adicionar bloco.');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao adicionar bloco.');
        });
    });

    // Atualiza o preview da ordem
    function updatePreview() {
        const items = sortableList.querySelectorAll('.block-item');
        const previewDiv = document.getElementById('order-preview');

        if (items.length === 0) {
            previewDiv.innerHTML = '<p class="text-muted text-center"><small>Adicione blocos para visualizar aqui</small></p>';
            return;
        }

        let html = '';
        items.forEach((item, index) => {
            const title = item.querySelector('.block-title').textContent;
            const icon = item.querySelector('.block-icon i').className;
            const isActive = item.querySelector('.toggle-active').classList.contains('btn-success');
            const badgeClass = isActive ? 'bg-green' : 'bg-gray';
            const textClass = isActive ? '' : 'text-muted';
            const suffix = isActive ? '' : '<small>(oculto)</small>';

            html += `<div class="preview-item ${textClass}" data-id="${item.dataset.id}">
                <span class="badge ${badgeClass}">${index + 1}</span>
                <i class="${icon}"></i>
                ${title.length > 20 ? title.substring(0, 20) + '...' : title}
                ${suffix}
            </div>`;
        });

        previewDiv.innerHTML = html;
    }

    // Limpa o form quando o modal é fechado
    $('#addBlockModal').on('hidden.bs.modal', function() {
        document.getElementById('addBlockForm').reset();
        referenceGroup.style.display = 'none';
        typeDescription.textContent = '';
    });
});
</script>
@stop
