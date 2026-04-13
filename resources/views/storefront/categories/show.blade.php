{{--
    View: Página da Categoria
    Exibe todos os produtos de uma categoria com filtros e ordenação
--}}
@extends('layouts.storefront')

@section('title', $category->name . ' - ' . config('app.name'))
@section('meta_title', $category->name)
@section('meta_description', $category->description ?? 'Confira os produtos da categoria ' . $category->name)
@section('body_class', 'pg-departamento')

@section('content')
    {{-- Banner da Categoria --}}
    @include('storefront.components.banner-interno', ['title' => $category->name, 'image' => $category->banner_url ?? ''])

    {{-- Breadcrumb --}}
    @include('storefront.components.breadcrumb', ['items' => [['title' => 'Home', 'url' => url('/')], ['title' => $category->name, 'url' => null]]])

    {{-- Vitrine de Produtos --}}
    <section class="vitrine-home">
        <div class="container">
            <div class="row">

                {{-- Sidebar Left (Desktop) --}}
                <div class="col-xs-12 col-md-3 css-desktop">
                    @include('storefront.partials.category-sidebar')
                </div>

                {{-- Content Right --}}
                <div class="col-xs-12 col-md-9">

                    {{-- Filtros e Ordenação --}}
                    <div class="row">
                        <div class="flex no-flex-xs group-custom">
                            {{-- Selos/Tags de filtro (opcional) --}}
                            <div class="col-xs-12 col-sm-6 col-md-7 col-lg-8">
                                {{-- Aqui podem entrar filtros por selos como "Sem Lactose", "Sem Glúten", etc --}}
                            </div>

                            {{-- Contador e Ordenação --}}
                            <div class="col-xs-12 col-sm-6 col-md-5 col-lg-4">
                                <div class="box-filtro-produto">
                                    <div class="flex-end no-flex-xs">
                                        <h5><span>{{ $totalProducts }}</span> resultados</h5>
                                        <form action="{{ url()->current() }}" method="get" name="frm_filtro" id="form-filtro">
                                            {{-- Mantém outros parâmetros da URL --}}
                                            @foreach(request()->except(['filtro', 'page']) as $key => $value)
                                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                            @endforeach

                                            <select name="filtro" id="filtro" class="form-control filtro" onchange="this.form.submit()">
                                                @foreach($sortOptions as $value => $label)
                                                    <option value="{{ $value }}" {{ request('filtro') == $value ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Grid de Produtos --}}
                    @php
                        $starsMap = $starsMap ?? \App\Models\Legacy\Depoimento::getStarsByProduct();
                    @endphp
                    <div class="row listagem-produtos">
                        @forelse($products as $product)
                            @include('storefront.partials.product.card', ['product' => $product, 'starsMap' => $starsMap])
                        @empty
                            <div class="col-xs-12">
                                <div class="alert alert-info text-center">
                                    <i class="fa fa-info-circle"></i>
                                    Nenhum produto encontrado nesta categoria.
                                </div>
                            </div>
                        @endforelse
                    </div>

                    {{-- Paginação --}}
                    @if($products->hasPages())
                        <div class="row">
                            <div class="col-xs-12">
                                <nav class="text-center" aria-label="Paginação">
                                    {{ $products->links('storefront.partials.pagination') }}
                                </nav>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Controle de quantidade nos cards de kit
    $('.js-minus').on('click', function() {
        var $input = $(this).siblings('.qtd');
        var val = parseInt($input.val()) || 1;
        if (val > 1) {
            $input.val(val - 1);
        }
    });

    $('.js-plus').on('click', function() {
        var $input = $(this).siblings('.qtd');
        var val = parseInt($input.val()) || 1;
        $input.val(val + 1);
    });

    // Match Height para cards
    $('.js-h-card').matchHeight();
});
</script>
@endpush
