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
    <label for="name">Name</label>
    <input type="text" name="name" id="name" class="form-control" value="{{ $product->name ?? old('name') }}">
</div>

@if (isset($product) && $product->images->isNotEmpty())
    <div class="form-group">
        <label>Product Images</label>
        <div class="row">
            @foreach ($product->images as $image)
                <div class="col-md-3 mb-3">
                    <div class="card">
                        <img src="{{ asset('storage/' . $image->path) }}" alt="{{ $product->name }}" class="card-img-top" style="height: 200px; object-fit: cover;">
                        <div class="card-body p-2 text-center">
                            <small class="text-muted">Position: {{ $image->position }}</small>
                            @if ($image->is_main)
                                <br><span class="badge badge-success">Main</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@else
    <div class="alert alert-info">
        <strong>No images synced yet.</strong> Use the "Sync Images" button in the admin panel to download images from the legacy system.
    </div>
@endif

<div class="form-group">
    <label for="price">Price</label>
    <input type="number" step="0.01" name="price" id="price" class="form-control" value="{{ $product->price ?? old('price') }}">
</div>

<div class="form-group">
    <label for="category_id">Category</label>
    <select name="category_id" id="category_id" class="form-control">
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" {{ (isset($product) && $product->category_id == $category->id) || old('category_id') == $category->id ? 'selected' : '' }}>
                {{ $category->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label for="brand_id">Brand</label>
    <select name="brand_id" id="brand_id" class="form-control">
        <option value="">-- Select a Brand --</option>
        @foreach ($brands as $brand)
            <option value="{{ $brand->id }}" {{ (isset($product) && $product->brand_id == $brand->id) || old('brand_id') == $brand->id ? 'selected' : '' }}>
                {{ $brand->brand }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label for="sku">SKU</label>
    <input type="text" name="sku" id="sku" class="form-control" value="{{ $product->sku ?? old('sku') }}" readonly>
    <small class="form-text text-muted">Read-only field synced from legacy system</small>
</div>

<div class="form-group">
    <label for="description">Description (Concatenated - Read Only)</label>
    <textarea name="description" id="description" class="form-control" rows="4" readonly>{{ $product->description ?? old('description') }}</textarea>
    <small class="form-text text-muted">Auto-generated from fields below</small>
</div>

<hr>
<h4>Presentation & Marketing</h4>

<div class="form-group">
    <label for="presentation">Presentation</label>
    <textarea name="presentation" id="presentation" class="form-control" rows="3" readonly>{{ $product->presentation ?? old('presentation') }}</textarea>
</div>

<div class="form-group">
    <label for="properties">Properties</label>
    <textarea name="properties" id="properties" class="form-control" rows="3" readonly>{{ $product->properties ?? old('properties') }}</textarea>
</div>

<div class="form-group">
    <label for="benefits">Benefits</label>
    <textarea name="benefits" id="benefits" class="form-control" rows="3" readonly>{{ $product->benefits ?? old('benefits') }}</textarea>
</div>

<div class="form-group">
    <label for="chef_tips">Chef Tips</label>
    <textarea name="chef_tips" id="chef_tips" class="form-control" rows="3" readonly>{{ $product->chef_tips ?? old('chef_tips') }}</textarea>
</div>

<div class="form-group">
    <label for="dish_history">Dish History</label>
    <textarea name="dish_history" id="dish_history" class="form-control" rows="3" readonly>{{ $product->dish_history ?? old('dish_history') }}</textarea>
</div>

<div class="form-group">
    <label for="ingredients">Ingredients</label>
    <textarea name="ingredients" id="ingredients" class="form-control" rows="3" readonly>{{ $product->ingredients ?? old('ingredients') }}</textarea>
</div>

<div class="form-group">
    <label for="consumption_instructions">Consumption Instructions</label>
    <textarea name="consumption_instructions" id="consumption_instructions" class="form-control" rows="3" readonly>{{ $product->consumption_instructions ?? old('consumption_instructions') }}</textarea>
</div>

<hr>
<h4>Weight & Dimensions</h4>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="weight">Net Weight (kg)</label>
            <input type="number" step="0.001" name="weight" id="weight" class="form-control" value="{{ $product->weight ?? old('weight') }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="gross_weight">Gross Weight (kg)</label>
            <input type="number" step="0.001" name="gross_weight" id="gross_weight" class="form-control" value="{{ $product->gross_weight ?? old('gross_weight') }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="weight_unit">Weight Unit</label>
            <select name="weight_unit" id="weight_unit" class="form-control">
                <option value="g" {{ (isset($product) && $product->weight_unit == 'g') || old('weight_unit') == 'g' ? 'selected' : '' }}>Grams (g)</option>
                <option value="kg" {{ (isset($product) && $product->weight_unit == 'kg') || old('weight_unit') == 'kg' ? 'selected' : '' }}>Kilograms (kg)</option>
            </select>
        </div>
    </div>
</div>

<hr>
<h4>Shelf Life & Portion</h4>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="shelf_life_days">Shelf Life (days)</label>
            <input type="number" name="shelf_life_days" id="shelf_life_days" class="form-control" value="{{ $product->shelf_life_days ?? old('shelf_life_days') }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="portion_size">Portion Size (g)</label>
            <input type="number" name="portion_size" id="portion_size" class="form-control" value="{{ $product->portion_size ?? old('portion_size', 100) }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="home_measure">Home Measure</label>
            <input type="text" name="home_measure" id="home_measure" class="form-control" value="{{ $product->home_measure ?? old('home_measure') }}" placeholder="Ex: 1 porção">
        </div>
    </div>
</div>

<hr>
<h4>Dietary Information & Allergens</h4>

<div class="row">
    <div class="col-md-12">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="contains_gluten" id="contains_gluten" value="1" {{ (isset($product) && $product->contains_gluten) || old('contains_gluten') ? 'checked' : '' }}>
            <label class="form-check-label" for="contains_gluten">Contains Gluten</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="lactose_free" id="lactose_free" value="1" {{ (isset($product) && $product->lactose_free) || old('lactose_free') ? 'checked' : '' }}>
            <label class="form-check-label" for="lactose_free">Lactose Free</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="low_lactose" id="low_lactose" value="1" {{ (isset($product) && $product->low_lactose) || old('low_lactose') ? 'checked' : '' }}>
            <label class="form-check-label" for="low_lactose">Low Lactose</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="contains_lactose" id="contains_lactose" value="1" {{ (isset($product) && $product->contains_lactose) || old('contains_lactose') ? 'checked' : '' }}>
            <label class="form-check-label" for="contains_lactose">Contains Lactose</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="alcoholic_beverage" id="alcoholic_beverage" value="1" {{ (isset($product) && $product->alcoholic_beverage) || old('alcoholic_beverage') ? 'checked' : '' }}>
            <label class="form-check-label" for="alcoholic_beverage">Alcoholic Beverage</label>
        </div>
    </div>
</div>

<div class="form-group mt-3">
    <label for="allergens">Allergens</label>
    <textarea name="allergens" id="allergens" class="form-control" rows="2" placeholder="Ex: Pode conter traços de amendoim, castanhas">{{ $product->allergens ?? old('allergens') }}</textarea>
</div>

<hr>
<h4>Additional Information</h4>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="label_description">Label Description</label>
            <input type="text" name="label_description" id="label_description" class="form-control" value="{{ $product->label_description ?? old('label_description') }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="freezing_time">Freezing Time</label>
            <input type="time" name="freezing_time" id="freezing_time" class="form-control" value="{{ $product->freezing_time ?? old('freezing_time') }}">
        </div>
    </div>
</div>

<div class="form-group">
    <label for="description_english">Description (English)</label>
    <textarea name="description_english" id="description_english" class="form-control" rows="3">{{ $product->description_english ?? old('description_english') }}</textarea>
</div>

<hr>
<h4>Status & Control</h4>

<div class="row">
    <div class="col-md-12">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="active" id="active" value="1" {{ (isset($product) && $product->active) || old('active') ? 'checked' : '' }} disabled>
            <label class="form-check-label" for="active">Active</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="is_package" id="is_package" value="1" {{ (isset($product) && $product->is_package) || old('is_package') ? 'checked' : '' }} disabled>
            <label class="form-check-label" for="is_package">Is Package</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="is_combo" id="is_combo" value="1" {{ (isset($product) && $product->is_combo) || old('is_combo') ? 'checked' : '' }} disabled>
            <label class="form-check-label" for="is_combo">Is Combo</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="is_gift_card" id="is_gift_card" value="1" {{ (isset($product) && $product->is_gift_card) || old('is_gift_card') ? 'checked' : '' }} disabled>
            <label class="form-check-label" for="is_gift_card">Is Gift Card</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="made_to_order" id="made_to_order" value="1" {{ (isset($product) && $product->made_to_order) || old('made_to_order') ? 'checked' : '' }} disabled>
            <label class="form-check-label" for="made_to_order">Made to Order</label>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-4">
        <div class="form-group">
            <label for="display_order">Display Order</label>
            <input type="number" name="display_order" id="display_order" class="form-control" value="{{ $product->display_order ?? old('display_order', 0) }}" readonly>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="background_color">Background Color</label>
            <input type="text" name="background_color" id="background_color" class="form-control" value="{{ $product->background_color ?? old('background_color', '#F0F0F0') }}" readonly>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="text_color">Text Color</label>
            <input type="text" name="text_color" id="text_color" class="form-control" value="{{ $product->text_color ?? old('text_color', '#000000') }}" readonly>
        </div>
    </div>
</div>

<hr>
<h4>Manufacturer</h4>

<div class="form-group">
    <label for="manufacturer">Manufacturer</label>
    <input type="text" name="manufacturer" id="manufacturer" class="form-control" value="{{ isset($product) && $product->manufacturer ? $product->manufacturer->trade_name : 'N/A' }}" readonly>
</div>

<hr>
<h4>Integration & Marketplaces</h4>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="ifood_percentage">iFood Percentage (%)</label>
            <input type="number" step="0.01" name="ifood_percentage" id="ifood_percentage" class="form-control" value="{{ $product->ifood_percentage ?? old('ifood_percentage') }}" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="ifood_promotion_percentage">iFood Promotion Percentage (%)</label>
            <input type="number" step="0.01" name="ifood_promotion_percentage" id="ifood_promotion_percentage" class="form-control" value="{{ $product->ifood_promotion_percentage ?? old('ifood_promotion_percentage') }}" readonly>
        </div>
    </div>
</div>

<hr>
<h4>Properties</h4>

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

<button type="submit" class="btn btn-primary">Save</button>
