{{--
    View: Página de Resultados da Busca
    Exibe produtos encontrados pela busca com filtros e ordenação
--}}
@extends('layouts.storefront')

@section('title', 'Busca: ' . $termo . ' - ' . config('app.name'))
@section('meta_title', 'Busca: ' . $termo)
@section('meta_description', 'Resultados da busca por "' . $termo . '" na loja ' . config('app.name'))
@section('body_class', 'pg-departamento pg-busca')

@section('content')
    {{-- Banner da Busca --}}
    @include('storefront.components.banner-interno', ['title' => 'Resultados da busca', 'image' => ''])

    {{-- Breadcrumb --}}
    @include('storefront.components.breadcrumb', ['items' => [['title' => 'Home', 'url' => url('/')], ['title' => 'Busca', 'url' => null]]])

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
                            {{-- Termo buscado --}}
                            <div class="col-xs-12 col-sm-6 col-md-7 col-lg-8">
                                @if($termo)
                                    <h4 style="margin: 5px 0 10px;">Você buscou por: <strong>"{{ $termo }}"</strong></h4>
                                @endif
                            </div>

                            {{-- Contador e Ordenação --}}
                            <div class="col-xs-12 col-sm-6 col-md-5 col-lg-4">
                                <div class="box-filtro-produto">
                                    <div class="flex-end no-flex-xs">
                                        <h5><span>{{ $totalProducts }}</span> resultados</h5>
                                        <form action="{{ url('/busca') }}" method="get" name="frm_filtro" id="form-filtro">
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
                                    <i class="fa fa-search"></i>
                                    @if($termo)
                                        Nenhum produto encontrado para <strong>"{{ $termo }}"</strong>.
                                    @else
                                        Digite um termo para buscar produtos.
                                    @endif
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
    // Match Height para cards
    $('.js-h-card').matchHeight();
});
</script>
@endpush
