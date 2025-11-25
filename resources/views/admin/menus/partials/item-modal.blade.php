{{--
    Partial: Modal de Adicionar/Editar Item de Menu
    Usado na tela de gerenciamento de itens
--}}
@php
    $isEdit = $isEdit ?? false;
    $formId = $isEdit ? 'editItemForm' : 'addItemForm';
    $formAction = $isEdit ? '' : route('admin.menus.items.add', $menu);
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="{{ $formId }}" action="{{ $formAction }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if($isEdit)
                    @method('PUT')
                    <input type="hidden" name="item_id">
                @endif
                <input type="hidden" name="parent_id" value="">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-{{ $isEdit ? 'edit' : 'plus' }} mr-2"></i>
                        {{ $modalTitle }}
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        {{-- Coluna Esquerda: Dados Básicos --}}
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3"><i class="fas fa-info-circle mr-1"></i> Informações Básicas</h6>

                            <div class="form-group">
                                <label for="{{ $modalId }}_title">Título <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="{{ $modalId }}_title"
                                       class="form-control" required placeholder="Texto exibido no menu">
                            </div>

                            <div class="form-group">
                                <label for="{{ $modalId }}_type">Tipo <span class="text-danger">*</span></label>
                                <select name="type" id="{{ $modalId }}_type" class="form-control" required>
                                    @foreach($itemTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Campo para Categoria --}}
                            <div class="form-group type-category" style="display: none;">
                                <label>Categoria</label>
                                <select name="category_id" class="form-control linkable-select" disabled>
                                    <option value="">Selecione...</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Campo para Página --}}
                            <div class="form-group type-page" style="display: none;">
                                <label>Página</label>
                                <select name="page_id" class="form-control linkable-select" disabled>
                                    <option value="">Selecione...</option>
                                    @foreach($pages as $page)
                                        <option value="{{ $page->id }}">{{ $page->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Campo para URL --}}
                            <div class="form-group type-url" style="display: none;">
                                <label>URL</label>
                                <input type="text" name="url" class="form-control" placeholder="https://..." disabled>
                            </div>

                            <div class="form-group">
                                <label for="{{ $modalId }}_target">Abrir em</label>
                                <select name="target" id="{{ $modalId }}_target" class="form-control">
                                    <option value="_self">Mesma janela</option>
                                    <option value="_blank">Nova janela</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="{{ $modalId }}_show_on">Exibir em</label>
                                <select name="show_on" id="{{ $modalId }}_show_on" class="form-control">
                                    @foreach($showOnOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            @if($isEdit)
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="{{ $modalId }}_active" name="active" value="1" checked>
                                        <label class="custom-control-label" for="{{ $modalId }}_active">Item Ativo</label>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Coluna Direita: Ícones e Mega Menu --}}
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3"><i class="fas fa-palette mr-1"></i> Aparência</h6>

                            <div class="form-group">
                                <label for="{{ $modalId }}_icon_class">Ícone (classe CSS)</label>
                                <input type="text" name="icon_class" id="{{ $modalId }}_icon_class"
                                       class="form-control" placeholder="Ex: fa fa-home">
                                <small class="text-muted">Use classes FontAwesome</small>
                            </div>

                            <div class="form-group">
                                <label for="{{ $modalId }}_icon_image">Ícone (imagem)</label>
                                <div class="custom-file">
                                    <input type="file" name="icon_image" id="{{ $modalId }}_icon_image"
                                           class="custom-file-input" accept="image/*">
                                    <label class="custom-file-label" for="{{ $modalId }}_icon_image">Escolher arquivo...</label>
                                </div>
                                <small class="text-muted">PNG, JPG, SVG. Max 1MB</small>
                            </div>

                            <div class="form-group">
                                <label for="{{ $modalId }}_css_class">Classes CSS extras</label>
                                <input type="text" name="css_class" id="{{ $modalId }}_css_class"
                                       class="form-control" placeholder="Ex: destaque, promo">
                            </div>

                            <hr>

                            <h6 class="text-muted mb-3"><i class="fas fa-th-large mr-1"></i> Mega Menu</h6>

                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="{{ $modalId }}_is_mega_menu" name="is_mega_menu" value="1">
                                    <label class="custom-control-label" for="{{ $modalId }}_is_mega_menu">
                                        Ativar Mega Menu
                                    </label>
                                </div>
                                <small class="text-muted">Exibe subitens em grid com imagem promocional</small>
                            </div>

                            <div class="mega-menu-options" style="display: none;">
                                <div class="form-group">
                                    <label>Imagem/Banner do Mega Menu</label>
                                    <div class="custom-file">
                                        <input type="file" name="mega_menu_image" class="custom-file-input" accept="image/*">
                                        <label class="custom-file-label">Escolher arquivo...</label>
                                    </div>
                                    <small class="text-muted">Recomendado: 600x400px</small>
                                </div>

                                <div class="form-group">
                                    <label>Link da Imagem</label>
                                    <input type="url" name="mega_menu_image_url" class="form-control" placeholder="https://...">
                                </div>

                                <div class="form-group">
                                    <label>Texto Alternativo</label>
                                    <input type="text" name="mega_menu_image_alt" class="form-control" placeholder="Descrição da imagem">
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>Posição da Imagem</label>
                                            <select name="mega_menu_image_position" class="form-control">
                                                @foreach($megaMenuPositions as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>Colunas dos Itens</label>
                                            <select name="mega_menu_columns" class="form-control">
                                                <option value="2">2 colunas</option>
                                                <option value="3">3 colunas</option>
                                                <option value="4">4 colunas</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>
                        {{ $isEdit ? 'Salvar Alterações' : 'Adicionar Item' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
