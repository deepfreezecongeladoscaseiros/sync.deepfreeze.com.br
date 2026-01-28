{{--
    Página Institucional Genérica (show)
    Exibe páginas dinâmicas (Sobre Nós, FAQ, Política, etc.)
    Usa o layout storefront para herdar header, footer, CSS e JS base.
--}}
@extends('layouts.storefront')

@section('title', $page->getSeoTitle())
@section('body_class', 'pg-institucional')

@if($page->meta_description)
    @section('meta_description', $page->meta_description)
@endif

@section('content')

{{-- Breadcrumb --}}
<div class="box-breadcrumb">
    <div class="container">
        <ol class="breadcrumb">
            <li><a href="/">Home</a></li>
            <li class="active">{{ $page->title }}</li>
        </ol>
    </div>
</div>

{{-- Conteúdo da Página --}}
<section class="page-content" style="padding: 40px 0; min-height: 400px;">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 style="margin-bottom: 30px; color: var(--color-primary);">{{ $page->title }}</h1>

                <div class="page-body" style="line-height: 1.8; font-size: 15px;">
                    {!! $page->content !!}
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
