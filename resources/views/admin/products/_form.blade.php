@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="form-group">
    <label for="name">Nome</label>
    <input type="text" name="name" id="name" class="form-control" value="{{ $product->name ?? old('name') }}">
</div>

@if (isset($product) && $product->images->isNotEmpty())
    <div class="form-group">
        <label>Imagens do Produto</label>
        <div class="row">
            @foreach ($product->images as $image)
                <div class="col-md-3 mb-3">
                    <div class="card">
                        <img src="{{ asset('storage/' . $image->path) }}" alt="{{ $product->name }}" class="card-img-top" style="height: 200px; object-fit: cover;">
                        <div class="card-body p-2 text-center">
                            <small class="text-muted">Posição: {{ $image->position }}</small>
                            @if ($image->is_main)
                                <br><span class="badge badge-success">Principal</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@else
    <div class="alert alert-info">
        <strong>Nenhuma imagem sincronizada ainda.</strong> Use o botão "Sincronizar Imagens" no painel administrativo para baixar imagens do sistema legado.
    </div>
@endif

<div class="form-group">
    <label for="price">Preço</label>
    <input type="number" step="0.01" name="price" id="price" class="form-control" value="{{ $product->price ?? old('price') }}">
</div>

<div class="form-group">
    <label for="category_id">Categoria</label>
    <select name="category_id" id="category_id" class="form-control">
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" {{ (isset($product) && $product->category_id == $category->id) || old('category_id') == $category->id ? 'selected' : '' }}>
                {{ $category->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label for="brand_id">Marca</label>
    <select name="brand_id" id="brand_id" class="form-control">
        <option value="">-- Selecione uma Marca --</option>
        @foreach ($brands as $brand)
            <option value="{{ $brand->id }}" {{ (isset($product) && $product->brand_id == $brand->id) || old('brand_id') == $brand->id ? 'selected' : '' }}>
                {{ $brand->brand }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label for="sku">Código</label>
    <input type="text" name="sku" id="sku" class="form-control" value="{{ $product->sku ?? old('sku') }}" readonly>
    <small class="form-text text-muted">Campo somente leitura sincronizado do sistema legado</small>
</div>

<div class="form-group">
    <label for="description">Descrição (Concatenada - Somente Leitura)</label>
    <textarea name="description" id="description" class="form-control" rows="4" readonly>{{ $product->description ?? old('description') }}</textarea>
    <small class="form-text text-muted">Gerado automaticamente dos campos abaixo</small>
</div>

<hr>
<h4>Apresentação e Marketing</h4>

<div class="form-group">
    <label for="presentation">Apresentação</label>
    <textarea name="presentation" id="presentation" class="form-control" rows="3" readonly>{{ $product->presentation ?? old('presentation') }}</textarea>
</div>

<div class="form-group">
    <label for="properties">Propriedades</label>
    <textarea name="properties" id="properties" class="form-control" rows="3" readonly>{{ $product->properties ?? old('properties') }}</textarea>
</div>

<div class="form-group">
    <label for="benefits">Benefícios</label>
    <textarea name="benefits" id="benefits" class="form-control" rows="3" readonly>{{ $product->benefits ?? old('benefits') }}</textarea>
</div>

<div class="form-group">
    <label for="chef_tips">Dicas do Chef</label>
    <textarea name="chef_tips" id="chef_tips" class="form-control" rows="3" readonly>{{ $product->chef_tips ?? old('chef_tips') }}</textarea>
</div>

<div class="form-group">
    <label for="dish_history">História do Prato</label>
    <textarea name="dish_history" id="dish_history" class="form-control" rows="3" readonly>{{ $product->dish_history ?? old('dish_history') }}</textarea>
</div>

<div class="form-group">
    <label for="ingredients">Ingredientes</label>
    <textarea name="ingredients" id="ingredients" class="form-control" rows="3" readonly>{{ $product->ingredients ?? old('ingredients') }}</textarea>
</div>

<div class="form-group">
    <label for="consumption_instructions">Instruções de Consumo</label>
    <textarea name="consumption_instructions" id="consumption_instructions" class="form-control" rows="3" readonly>{{ $product->consumption_instructions ?? old('consumption_instructions') }}</textarea>
</div>

<hr>
<h4>Peso e Dimensões</h4>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="weight">Peso Líquido (kg)</label>
            <input type="number" step="0.001" name="weight" id="weight" class="form-control" value="{{ $product->weight ?? old('weight') }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="gross_weight">Peso Bruto (kg)</label>
            <input type="number" step="0.001" name="gross_weight" id="gross_weight" class="form-control" value="{{ $product->gross_weight ?? old('gross_weight') }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="weight_unit">Unidade de Peso</label>
            <select name="weight_unit" id="weight_unit" class="form-control">
                <option value="g" {{ (isset($product) && $product->weight_unit == 'g') || old('weight_unit') == 'g' ? 'selected' : '' }}>Gramas (g)</option>
                <option value="kg" {{ (isset($product) && $product->weight_unit == 'kg') || old('weight_unit') == 'kg' ? 'selected' : '' }}>Quilogramas (kg)</option>
            </select>
        </div>
    </div>
</div>

<hr>
<h4>Validade e Porção</h4>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="shelf_life_days">Validade (Dias)</label>
            <input type="number" name="shelf_life_days" id="shelf_life_days" class="form-control" value="{{ $product->shelf_life_days ?? old('shelf_life_days') }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="portion_size">Tamanho da Porção (g)</label>
            <input type="number" name="portion_size" id="portion_size" class="form-control" value="{{ $product->portion_size ?? old('portion_size', 100) }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="home_measure">Medida Caseira</label>
            <input type="text" name="home_measure" id="home_measure" class="form-control" value="{{ $product->home_measure ?? old('home_measure') }}" placeholder="Ex: 1 porção">
        </div>
    </div>
</div>

<hr>
<h4>Informações Dietéticas e Alérgenos</h4>

<div class="row">
    <div class="col-md-12">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="contains_gluten" id="contains_gluten" value="1" {{ (isset($product) && $product->contains_gluten) || old('contains_gluten') ? 'checked' : '' }}>
            <label class="form-check-label" for="contains_gluten">Contém Glúten</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="lactose_free" id="lactose_free" value="1" {{ (isset($product) && $product->lactose_free) || old('lactose_free') ? 'checked' : '' }}>
            <label class="form-check-label" for="lactose_free">Sem Lactose</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="low_lactose" id="low_lactose" value="1" {{ (isset($product) && $product->low_lactose) || old('low_lactose') ? 'checked' : '' }}>
            <label class="form-check-label" for="low_lactose">Baixa Lactose</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="contains_lactose" id="contains_lactose" value="1" {{ (isset($product) && $product->contains_lactose) || old('contains_lactose') ? 'checked' : '' }}>
            <label class="form-check-label" for="contains_lactose">Contém Lactose</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="alcoholic_beverage" id="alcoholic_beverage" value="1" {{ (isset($product) && $product->alcoholic_beverage) || old('alcoholic_beverage') ? 'checked' : '' }}>
            <label class="form-check-label" for="alcoholic_beverage">Bebida Alcoólica</label>
        </div>
    </div>
</div>

<div class="form-group mt-3">
    <label for="allergens">Alérgenos</label>
    <textarea name="allergens" id="allergens" class="form-control" rows="2" placeholder="Ex: Pode conter traços de amendoim, castanhas">{{ $product->allergens ?? old('allergens') }}</textarea>
</div>

<hr>
<h4>Informações Adicionais</h4>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="label_description">Label Description</label>
            <input type="text" name="label_description" id="label_description" class="form-control" value="{{ $product->label_description ?? old('label_description') }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="freezing_time">Tempo de Congelamento</label>
            <input type="time" name="freezing_time" id="freezing_time" class="form-control" value="{{ $product->freezing_time ?? old('freezing_time') }}">
        </div>
    </div>
</div>

<div class="form-group">
    <label for="description_english">Descrição (Inglês)</label>
    <textarea name="description_english" id="description_english" class="form-control" rows="3">{{ $product->description_english ?? old('description_english') }}</textarea>
</div>

<hr>
<h4>Status e Controle</h4>

<div class="row">
    <div class="col-md-12">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="active" id="active" value="1" {{ (isset($product) && $product->active) || old('active') ? 'checked' : '' }} disabled>
            <label class="form-check-label" for="active">Ativo</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="is_package" id="is_package" value="1" {{ (isset($product) && $product->is_package) || old('is_package') ? 'checked' : '' }} disabled>
            <label class="form-check-label" for="is_package">É Pacote</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="is_combo" id="is_combo" value="1" {{ (isset($product) && $product->is_combo) || old('is_combo') ? 'checked' : '' }} disabled>
            <label class="form-check-label" for="is_combo">É Combo</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="is_gift_card" id="is_gift_card" value="1" {{ (isset($product) && $product->is_gift_card) || old('is_gift_card') ? 'checked' : '' }} disabled>
            <label class="form-check-label" for="is_gift_card">É Gift Card</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="made_to_order" id="made_to_order" value="1" {{ (isset($product) && $product->made_to_order) || old('made_to_order') ? 'checked' : '' }} disabled>
            <label class="form-check-label" for="made_to_order">Feito sob Encomenda</label>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-4">
        <div class="form-group">
            <label for="display_order">Ordem de Exibição</label>
            <input type="number" name="display_order" id="display_order" class="form-control" value="{{ $product->display_order ?? old('display_order', 0) }}" readonly>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="background_color">Cor de Fundo</label>
            <input type="text" name="background_color" id="background_color" class="form-control" value="{{ $product->background_color ?? old('background_color', '#F0F0F0') }}" readonly>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="text_color">Cor do Texto</label>
            <input type="text" name="text_color" id="text_color" class="form-control" value="{{ $product->text_color ?? old('text_color', '#000000') }}" readonly>
        </div>
    </div>
</div>

<hr>
<h4>Fabricante</h4>

<div class="form-group">
    <label for="manufacturer">Fabricante</label>
    <input type="text" name="manufacturer" id="manufacturer" class="form-control" value="{{ isset($product) && $product->manufacturer ? $product->manufacturer->trade_name : 'N/A' }}" readonly>
</div>

<hr>
<h4>Integração e Marketplaces</h4>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="ifood_percentage">Percentual iFood (%)</label>
            <input type="number" step="0.01" name="ifood_percentage" id="ifood_percentage" class="form-control" value="{{ $product->ifood_percentage ?? old('ifood_percentage') }}" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="ifood_promotion_percentage">Percentual de Promoção iFood (%)</label>
            <input type="number" step="0.01" name="ifood_promotion_percentage" id="ifood_promotion_percentage" class="form-control" value="{{ $product->ifood_promotion_percentage ?? old('ifood_promotion_percentage') }}" readonly>
        </div>
    </div>
</div>

<hr>
<h4>Propriedades</h4>

@foreach ($properties as $property)
    <div class="form-group">
        <label>{{ $property->name }}</label>
        <div>
            @foreach ($property->values as $value)
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="property_values[]" value="{{ $value->id }}" 
                        {{ (isset($product) && $product->propertyValues->contains($value->id)) || (is_array(old('property_values')) && in_array($value->id, old('property_values'))) ? 'checked' : '' }}>
                    <label class="form-check-label">{{ $value->name }}</label>
                </div>
            @endforeach
        </div>
    </div>
@endforeach

<button type="submit" class="btn btn-primary">Salvar</button>
