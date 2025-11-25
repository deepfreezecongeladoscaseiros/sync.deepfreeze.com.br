@extends('adminlte::page')

@section('title', 'Itens do Menu: ' . $menu->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-list mr-2"></i> Itens do Menu: {{ $menu->name }}</h1>
        <div>
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addItemModal">
                <i class="fas fa-plus mr-1"></i> Adicionar Item
            </button>
            <a href="{{ route('admin.menus.edit', $menu) }}" class="btn btn-warning">
                <i class="fas fa-cog mr-1"></i> Configurações
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
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-grip-lines mr-2"></i>
                        Arraste para reordenar os itens
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-info">{{ $items->count() }} itens</span>
                    </div>
                </div>
                <div class="card-body">
                    @if($items->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-list-ul fa-4x text-muted mb-3"></i>
                            <p class="text-muted">Nenhum item no menu.</p>
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addItemModal">
                                <i class="fas fa-plus mr-1"></i> Adicionar Primeiro Item
                            </button>
                        </div>
                    @else
                        <div class="dd" id="menu-nestable">
                            <ol class="dd-list">
                                @foreach($items as $item)
                                    @include('admin.menus.partials.item-tree', ['item' => $item])
                                @endforeach
                            </ol>
                        </div>

                        <div class="mt-3">
                            <button type="button" class="btn btn-primary" id="saveOrderBtn" disabled>
                                <i class="fas fa-save mr-1"></i> Salvar Ordem
                            </button>
                            <span class="text-muted ml-2" id="orderStatus"></span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            {{-- Card de Informações --}}
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i> Informações</h3>
                </div>
                <div class="card-body">
                    <dl class="mb-0">
                        <dt>Menu:</dt>
                        <dd>{{ $menu->name }}</dd>
                        <dt>Localização:</dt>
                        <dd><span class="badge badge-info">{{ $menu->getLocationLabel() }}</span></dd>
                        <dt>Status:</dt>
                        <dd>
                            @if($menu->active)
                                <span class="badge badge-success">Ativo</span>
                            @else
                                <span class="badge badge-danger">Inativo</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>

            {{-- Card de Legenda --}}
            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-tags mr-2"></i> Legenda</h3>
                </div>
                <div class="card-body">
                    <p><span class="badge badge-primary">Categoria</span> Link para categoria</p>
                    <p><span class="badge badge-success">Página</span> Link para página institucional</p>
                    <p><span class="badge badge-info">URL</span> Link externo</p>
                    <p><span class="badge badge-warning">Home</span> Link para home</p>
                    <p><span class="badge badge-secondary">Contato</span> Link para contato</p>
                    <p><span class="badge badge-dark">Título</span> Apenas título (sem link)</p>
                    <hr>
                    <p class="mb-0"><i class="fas fa-th-large text-info"></i> = Mega Menu</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Adicionar Item --}}
    @include('admin.menus.partials.item-modal', ['modalId' => 'addItemModal', 'modalTitle' => 'Adicionar Item'])

    {{-- Modal Editar Item --}}
    @include('admin.menus.partials.item-modal', ['modalId' => 'editItemModal', 'modalTitle' => 'Editar Item', 'isEdit' => true])
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nestable2/1.6.0/jquery.nestable.min.css">
<style>
    .dd { max-width: 100%; }
    .dd-list { display: block; position: relative; margin: 0; padding: 0; list-style: none; }
    .dd-item { display: block; position: relative; margin: 10px 0; padding: 0; min-height: 20px; line-height: 20px; }
    .dd-handle { display: block; padding: 10px 15px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; cursor: move; }
    .dd-handle:hover { background: #e9ecef; }
    .dd-item > button { position: absolute; left: 0; top: 8px; width: 25px; height: 25px; }
    .dd-placeholder { background: #f0f9ff; border: 2px dashed #17a2b8; border-radius: 4px; margin: 5px 0; }
    .dd-empty { min-height: 100px; background: #f8f9fa; border: 2px dashed #dee2e6; border-radius: 4px; }
    .dd-collapsed .dd-list { display: none; }
    .dd3-content { padding: 10px 15px 10px 50px; }
    .dd3-handle { position: absolute; left: 0; top: 0; width: 40px; height: 100%; background: #6c757d; color: #fff; text-align: center; line-height: 44px; border-radius: 4px 0 0 4px; cursor: move; }
    .dd3-handle:hover { background: #5a6268; }
    .item-inactive { opacity: 0.5; }
    .item-mega-menu { border-left: 3px solid #17a2b8; }
    .item-actions { float: right; }
    .item-actions .btn { padding: 2px 8px; font-size: 12px; }
    .item-icon { width: 20px; height: 20px; object-fit: contain; margin-right: 5px; vertical-align: middle; }
</style>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/nestable2/1.6.0/jquery.nestable.min.js"></script>
<script>
$(document).ready(function() {
    // Inicializa Nestable (drag-and-drop)
    $('#menu-nestable').nestable({
        maxDepth: 5,
        callback: function(l, e) {
            // Habilita botão de salvar quando ordem muda
            $('#saveOrderBtn').prop('disabled', false);
            $('#orderStatus').text('Alterações não salvas');
        }
    });

    // Salvar ordem dos itens
    $('#saveOrderBtn').on('click', function() {
        var $btn = $(this);
        var serialized = $('#menu-nestable').nestable('serialize');
        var items = flattenTree(serialized, null, 0);

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Salvando...');

        $.ajax({
            url: '{{ route("admin.menus.items.reorder", $menu) }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                items: items
            },
            success: function(response) {
                $btn.html('<i class="fas fa-check mr-1"></i> Salvo!');
                $('#orderStatus').text('').addClass('text-success');
                setTimeout(function() {
                    $btn.html('<i class="fas fa-save mr-1"></i> Salvar Ordem');
                }, 2000);
            },
            error: function() {
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Salvar Ordem');
                $('#orderStatus').text('Erro ao salvar').addClass('text-danger');
            }
        });
    });

    // Flatten árvore nestable para array plano
    function flattenTree(items, parentId, position) {
        var result = [];
        $.each(items, function(i, item) {
            result.push({
                id: item.id,
                parent_id: parentId,
                position: position + i
            });
            if (item.children) {
                result = result.concat(flattenTree(item.children, item.id, 0));
            }
        });
        return result;
    }

    // Abrir modal de edição
    $(document).on('click', '.edit-item-btn', function() {
        var itemId = $(this).data('id');
        var $item = $(this).closest('.dd-item');

        // Preenche dados do modal
        $('#editItemModal [name="item_id"]').val(itemId);
        $('#editItemModal [name="title"]').val($item.data('title'));
        $('#editItemModal [name="type"]').val($item.data('type')).trigger('change');
        $('#editItemModal [name="url"]').val($item.data('url'));
        $('#editItemModal [name="target"]').val($item.data('target'));
        $('#editItemModal [name="icon_class"]').val($item.data('icon-class'));
        $('#editItemModal [name="css_class"]').val($item.data('css-class'));
        $('#editItemModal [name="show_on"]').val($item.data('show-on'));
        $('#editItemModal [name="is_mega_menu"]').prop('checked', $item.data('is-mega-menu') == 1);
        $('#editItemModal [name="mega_menu_image_url"]').val($item.data('mega-menu-image-url'));
        $('#editItemModal [name="mega_menu_image_alt"]').val($item.data('mega-menu-image-alt'));
        $('#editItemModal [name="mega_menu_image_position"]').val($item.data('mega-menu-image-position'));
        $('#editItemModal [name="mega_menu_columns"]').val($item.data('mega-menu-columns'));
        $('#editItemModal [name="active"]').prop('checked', $item.data('active') == 1);

        // Preenche category_id ou page_id conforme o tipo do item
        // O linkable_id é usado para ambos, mas em selects separados
        var linkableId = $item.data('linkable-id');
        var itemType = $item.data('type');

        // Limpa os selects primeiro
        $('#editItemModal select[name="category_id"]').val('');
        $('#editItemModal select[name="page_id"]').val('');

        // Preenche o select correto baseado no tipo
        if (itemType === 'category' && linkableId) {
            $('#editItemModal select[name="category_id"]').val(linkableId);
        } else if (itemType === 'page' && linkableId) {
            $('#editItemModal select[name="page_id"]').val(linkableId);
        }

        // Trigger do mega menu para mostrar/esconder opções
        $('#editItemModal [name="is_mega_menu"]').trigger('change');

        // Atualiza URL do form - usando replace para evitar URL malformada
        var updateUrl = '{{ route("admin.menus.items.update", [$menu->id, "__ITEM_ID__"]) }}'.replace('__ITEM_ID__', itemId);
        $('#editItemForm').attr('action', updateUrl);

        console.log('Edit form action:', updateUrl); // Debug

        $('#editItemModal').modal('show');
    });

    // Toggle mega menu options
    $('[name="is_mega_menu"]').on('change', function() {
        var $megaOptions = $(this).closest('form').find('.mega-menu-options');
        if ($(this).is(':checked')) {
            $megaOptions.slideDown();
        } else {
            $megaOptions.slideUp();
        }
    });

    // Mostrar campos baseado no tipo e habilitar/desabilitar inputs
    // Isso evita erro de validação HTML5 em campos ocultos
    $('[name="type"]').on('change', function() {
        var type = $(this).val();
        var $form = $(this).closest('form');

        // Esconde e desabilita todos os campos condicionais
        $form.find('.type-category, .type-page, .type-url').hide();
        $form.find('.type-category select, .type-page select, .type-url input').prop('disabled', true);

        // Mostra e habilita apenas o campo do tipo selecionado
        if (type === 'category') {
            $form.find('.type-category').show();
            $form.find('.type-category select').prop('disabled', false);
        } else if (type === 'page') {
            $form.find('.type-page').show();
            $form.find('.type-page select').prop('disabled', false);
        } else if (type === 'url') {
            $form.find('.type-url').show();
            $form.find('.type-url input').prop('disabled', false);
        }
    });

    // Trigger inicial para ambos os modais
    $('[name="type"]').trigger('change');

    // Toggle status do item
    $(document).on('click', '.toggle-status-btn', function(e) {
        e.preventDefault();
        var itemId = $(this).data('id');
        var $item = $(this).closest('.dd-item');

        $.post('{{ route("admin.menus.items.toggle", [$menu, ""]) }}/' + itemId, {
            _token: '{{ csrf_token() }}'
        }, function(response) {
            if (response.success) {
                if (response.active) {
                    $item.removeClass('item-inactive');
                } else {
                    $item.addClass('item-inactive');
                }
            }
        });
    });

    // Deletar item
    $(document).on('click', '.delete-item-btn', function(e) {
        e.preventDefault();
        if (!confirm('Excluir este item e todos os subitens?')) return;

        var itemId = $(this).data('id');
        var $item = $(this).closest('.dd-item');

        $.ajax({
            url: '{{ route("admin.menus.items.destroy", [$menu, ""]) }}/' + itemId,
            method: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    $item.fadeOut(function() {
                        $(this).remove();
                    });
                }
            }
        });
    });

    // Adicionar item - definir parent_id ao abrir modal de um subitem
    $(document).on('click', '.add-subitem-btn', function() {
        var parentId = $(this).data('parent-id');
        $('#addItemModal [name="parent_id"]').val(parentId);
        $('#addItemModal').modal('show');
    });

    // Limpar parent_id ao abrir modal normal
    $('[data-target="#addItemModal"]').on('click', function() {
        if (!$(this).hasClass('add-subitem-btn')) {
            $('#addItemModal [name="parent_id"]').val('');
        }
    });

    // Handler para submit do form de edição via AJAX
    $('#editItemForm').on('submit', function(e) {
        e.preventDefault();
        console.log('Edit form submitted'); // Debug

        var $form = $(this);
        var $btn = $form.find('[type="submit"]');
        var formData = new FormData(this);

        // Debug: mostra dados do form
        console.log('Form action:', $form.attr('action'));
        for (var pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Salvando...');

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-HTTP-Method-Override': 'PUT'
            },
            success: function(response) {
                console.log('Response:', response); // Debug
                if (response.success) {
                    // Atualiza o item na árvore
                    var item = response.item;
                    var $item = $('.dd-item[data-id="' + item.id + '"]');

                    // Atualiza dados do item
                    $item.data('title', item.title);
                    $item.data('type', item.type);
                    $item.data('active', item.active);
                    $item.data('is-mega-menu', item.is_mega_menu);

                    // Atualiza o texto exibido
                    $item.find('> .dd3-content .item-title').text(item.title);

                    // Atualiza badge de tipo
                    var typeBadges = {
                        'category': 'primary',
                        'page': 'success',
                        'url': 'info',
                        'home': 'warning',
                        'contact': 'secondary',
                        'submenu_title': 'dark'
                    };
                    $item.find('> .dd3-content .badge').first()
                        .removeClass('badge-primary badge-success badge-info badge-warning badge-secondary badge-dark')
                        .addClass('badge-' + typeBadges[item.type]);

                    // Atualiza classe de inativo
                    if (item.active) {
                        $item.removeClass('item-inactive');
                    } else {
                        $item.addClass('item-inactive');
                    }

                    // Atualiza classe de mega menu
                    if (item.is_mega_menu) {
                        $item.addClass('item-mega-menu');
                    } else {
                        $item.removeClass('item-mega-menu');
                    }

                    $('#editItemModal').modal('hide');
                }
            },
            error: function(xhr, status, error) {
                console.log('Error:', status, error); // Debug
                console.log('Response:', xhr.responseText); // Debug

                var message = 'Erro ao salvar item.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    message = Object.values(xhr.responseJSON.errors).flat().join('\n');
                }

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: message
                    });
                } else {
                    alert(message);
                }
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Salvar Alterações');
            }
        });
    });

    // Handler para submit do form de adicionar via AJAX
    $('#addItemForm').on('submit', function(e) {
        e.preventDefault();
        console.log('Add form submitted'); // Debug

        var $form = $(this);
        var $btn = $form.find('[type="submit"]');
        var formData = new FormData(this);

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Adicionando...');

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Response:', response); // Debug
                if (response.success) {
                    // Recarrega a página para mostrar o novo item
                    window.location.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log('Error:', status, error); // Debug
                console.log('Response:', xhr.responseText); // Debug

                var message = 'Erro ao adicionar item.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    message = Object.values(xhr.responseJSON.errors).flat().join('\n');
                }

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: message
                    });
                } else {
                    alert(message);
                }

                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Adicionar Item');
            }
        });
    });
});
</script>
@stop
