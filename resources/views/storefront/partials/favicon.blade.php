{{--
    Partial: Favicon e ícones do app
    Definido via configurações do tema
--}}
@php
    $faviconBase = asset('storefront/img/favicon');
@endphp

<link rel="apple-touch-icon" sizes="57x57" href="{{ $faviconBase }}/apple-icon-57x57.png">
<link rel="apple-touch-icon" sizes="60x60" href="{{ $faviconBase }}/apple-icon-60x60.png">
<link rel="apple-touch-icon" sizes="72x72" href="{{ $faviconBase }}/apple-icon-72x72.png">
<link rel="apple-touch-icon" sizes="76x76" href="{{ $faviconBase }}/apple-icon-76x76.png">
<link rel="apple-touch-icon" sizes="114x114" href="{{ $faviconBase }}/apple-icon-114x114.png">
<link rel="apple-touch-icon" sizes="120x120" href="{{ $faviconBase }}/apple-icon-120x120.png">
<link rel="apple-touch-icon" sizes="144x144" href="{{ $faviconBase }}/apple-icon-144x144.png">
<link rel="apple-touch-icon" sizes="152x152" href="{{ $faviconBase }}/apple-icon-152x152.png">
<link rel="apple-touch-icon" sizes="180x180" href="{{ $faviconBase }}/apple-icon-180x180.png">
<link rel="icon" type="image/png" sizes="192x192" href="{{ $faviconBase }}/android-icon-192x192.png">
<link rel="icon" type="image/png" sizes="32x32" href="{{ $faviconBase }}/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="96x96" href="{{ $faviconBase }}/favicon-96x96.png">
<link rel="icon" type="image/png" sizes="16x16" href="{{ $faviconBase }}/favicon-16x16.png">
<link rel="icon" type="image/x-icon" href="{{ $faviconBase }}/favicon.ico">
<link rel="shortcut icon" type="image/x-icon" href="{{ $faviconBase }}/favicon.ico">
<meta name="msapplication-TileColor" content="{{ theme_color('primary') }}">
<meta name="theme-color" content="{{ theme_color('primary') }}">
