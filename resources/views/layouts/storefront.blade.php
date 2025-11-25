<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=5.0, shrink-to-fit=no, viewport-fit=cover">
    <meta content="yes" name="apple-mobile-web-app-capable" />
    <meta content="yes" name="mobile-web-app-capable" />
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="format-detection" content="telephone=no">

    {{-- JavaScript Vars --}}
    <script>
        var sys_site = '{{ url("/") }}/',
            theme_url = '{{ asset("storefront") }}/',
            sys_produtos_grid = '4',
            sys_trustvox_store_id = '';
    </script>

    {{-- Favicon --}}
    @include('storefront.partials.favicon')

    {{-- Preload Critical Assets --}}
    <link rel="preload" as="style" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.4.1/css/bootstrap.min.css" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" as="style" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.7.2/animate.min.css" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" as="script" href="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js">
    <link rel="preload" as="script" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.4.1/js/bootstrap.min.js">

    {{-- CSS --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.7.2/animate.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.7/css/jquery.fancybox.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-sweetalert/0.4.5/sweet-alert.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" rel="stylesheet">
    <link href="https://naturallisas.com.br/assets/css/icon-fonts-octoshop.min.css" rel="stylesheet">
    <link href="https://naturallisas.com.br/assets/css/style-extend.min.css" rel="stylesheet">
    <link href="https://naturallisas.com.br/lojas/naturallis/theme/assets/css/style.min.css" rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="{{ asset('storefront/css/icons-mapping.css') }}" rel="stylesheet">

    {{-- Theme CSS --}}
    <link href="{{ theme_css_url() }}" rel="stylesheet">
    <link href="{{ asset('storefront/css/theme-override.css') }}" rel="stylesheet">
    <link href="{{ asset('storefront/css/logo-custom.css') }}" rel="stylesheet">
    <link href="{{ asset('storefront/css/top-bar-custom.css') }}" rel="stylesheet">
    <link href="{{ asset('storefront/css/mobile-menu.css') }}" rel="stylesheet">

    {{-- Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Page Meta --}}
    <title>@yield('title', config('app.name'))</title>
    <meta name="title" content="@yield('meta_title', config('app.name'))">
    <meta name="author" content="{{ config('app.name') }}" />
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <meta name="description" content="@yield('meta_description', '')">
    <meta name="robots" content="@yield('meta_robots', 'index,follow')"/>

    {{-- Open Graph --}}
    <meta property="og:title" content="@yield('og_title', config('app.name'))" />
    <meta property="og:description" content="@yield('og_description', '')" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:site_name" content="{{ config('app.name') }}" />
    <meta property="og:type" content="website" />
    <meta property="og:locale" content="pt_BR" />
    @hasSection('og_image')
        <meta property="og:image" content="@yield('og_image')"/>
    @endif

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary" />
    <meta name="twitter:title" content="@yield('twitter_title', config('app.name'))">
    <meta name="twitter:description" content="@yield('twitter_description', '')">
    <meta name="twitter:url" content="{{ url()->current() }}">

    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @stack('head')
    @stack('styles')
</head>

<body class="@yield('body_class', 'pg-interna')">

    {{-- Header --}}
    @include('storefront.partials.header')

    {{-- Page Content --}}
    @yield('content')

    {{-- Footer --}}
    @include('storefront.partials.footer')

    {{-- JavaScript --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.matchHeight/0.7.2/jquery.matchHeight-min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/3.3.4/jquery.inputmask.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.7/js/jquery.fancybox.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-sweetalert/0.4.5/sweet-alert.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.2/bootstrap3-typeahead.min.js"></script>
    <script src="https://naturallisas.com.br/assets/js/loja-triggers.min.js"></script>
    <script src="https://naturallisas.com.br/lojas/naturallis/theme/assets/js/scripts.min.js"></script>

    {{-- Menu Mobile Handler --}}
    {{-- Baseado no padrão original da Naturallis: usa classe js-overlay-toggle no body --}}
    <script>
    $(document).ready(function() {
        // Abre menu mobile ao clicar no hamburger
        // Adiciona classe js-overlay-toggle no body (padrão Naturallis)
        $('.navbar-toggle').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('body').addClass('js-overlay-toggle menu-open');
            $('#menuTopo').addClass('in');
        });

        // Fecha menu mobile ao clicar no X
        $('.navbar-toggle-close').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('body').removeClass('js-overlay-toggle menu-open');
            $('#menuTopo').removeClass('in');
        });

        // Fecha menu ao clicar fora (no overlay)
        $(document).on('click', function(e) {
            if ($('body').hasClass('js-overlay-toggle')) {
                // Clicou fora do menu e fora do botão hamburger
                if (!$(e.target).closest('#menuTopo, .navbar-toggle').length) {
                    $('body').removeClass('js-overlay-toggle menu-open');
                    $('#menuTopo').removeClass('in');
                }
            }
        });

        // Toggle dropdown no menu mobile
        // IMPORTANTE: Usa apenas toggle de classe 'open' - a animação é feita via CSS
        // O CSS externo da Naturallis usa max-height transition, não slideToggle
        // Ref: .box-nav-submenu .dropdown.open .dropdown-menu { max-height: 900px }
        $('#menuTopo .box-nav-submenu').on('click', '.dropdown-toggle', function(e) {
            var $parent = $(this).parent('.dropdown');

            // Se é um link direto (linkdropdown), deixa navegar
            if ($(this).data('toggle') === 'linkdropdown') {
                return true;
            }

            // Se tem submenu, toggle a classe 'open'
            if ($parent.find('> .dropdown-menu').length > 0) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                // Fecha outros dropdowns abertos (apenas remove classe)
                $('#menuTopo .dropdown').not($parent).removeClass('open');

                // Toggle este dropdown (apenas classe - CSS faz a animação)
                $parent.toggleClass('open');
            }
        });

        // ===========================================
        // MENU DESKTOP - MEGA MENU (HOVER)
        // ===========================================
        // Adiciona classe 'active' no hover para exibir o dropdown
        // Compatível com CSS original: .menu-principal .dropdown.active ul.dropdown-menu{display:block}

        var menuTimeout;

        // Mouse entra no dropdown - adiciona classe active
        $('.menu-principal.css-desktop .dropdown').on('mouseenter', function() {
            var $dropdown = $(this);
            clearTimeout(menuTimeout);
            // Remove active de outros dropdowns
            $('.menu-principal.css-desktop .dropdown').not($dropdown).removeClass('active');
            // Adiciona active neste
            $dropdown.addClass('active');
        });

        // Mouse sai do dropdown - remove classe active com delay
        $('.menu-principal.css-desktop .dropdown').on('mouseleave', function() {
            var $dropdown = $(this);
            menuTimeout = setTimeout(function() {
                $dropdown.removeClass('active');
            }, 150); // Pequeno delay para evitar fechamento acidental
        });

        // Click no dropdown-toggle também abre/fecha (para acessibilidade)
        $('.menu-principal.css-desktop .dropdown > .dropdown-toggle').on('click', function(e) {
            var $dropdown = $(this).parent('.dropdown');
            var isActive = $dropdown.hasClass('active');

            // Se o link é javascript:void(0), previne e toggle
            if ($(this).attr('href') === 'javascript:void(0)') {
                e.preventDefault();
                if (isActive) {
                    $dropdown.removeClass('active');
                } else {
                    $('.menu-principal.css-desktop .dropdown').removeClass('active');
                    $dropdown.addClass('active');
                }
            }
        });

        // Fecha menu ao clicar fora
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.menu-principal.css-desktop .dropdown').length) {
                $('.menu-principal.css-desktop .dropdown').removeClass('active');
            }
        });

        // ===========================================
        // SELETOR DE QUANTIDADE NOS CARDS DE PRODUTO
        // ===========================================

        /**
         * Botão de diminuir quantidade (-)
         * Mínimo permitido: 1
         */
        $(document).on('click', '.js-btn-minus', function(e) {
            e.preventDefault();
            var productId = $(this).data('product-id');
            var $input = $('#qtd-' + productId);
            var currentQty = parseInt($input.val()) || 1;

            // Não permite quantidade menor que 1
            if (currentQty > 1) {
                $input.val(currentQty - 1);
            }
        });

        /**
         * Botão de aumentar quantidade (+)
         * Máximo permitido: 99 (limite arbitrário)
         */
        $(document).on('click', '.js-btn-plus', function(e) {
            e.preventDefault();
            var productId = $(this).data('product-id');
            var $input = $('#qtd-' + productId);
            var currentQty = parseInt($input.val()) || 1;

            // Limite máximo de 99 unidades
            if (currentQty < 99) {
                $input.val(currentQty + 1);
            }
        });

        /**
         * Adicionar ao carrinho com quantidade selecionada
         * Envia produto + quantidade via AJAX
         */
        $(document).on('click', '.js-add-to-cart', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var productId = $btn.data('product-id');

            // Busca a quantidade do input associado ao produto
            var $input = $('#qtd-' + productId);
            var quantity = $input.length ? parseInt($input.val()) || 1 : 1;

            // Feedback visual - desabilita botão enquanto processa
            $btn.addClass('loading').prop('disabled', true);

            // AJAX para adicionar ao carrinho
            $.ajax({
                url: sys_site + 'carrinho/adicionar',
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    product_id: productId,
                    quantity: quantity
                },
                success: function(response) {
                    // Feedback de sucesso
                    if (typeof swal !== 'undefined') {
                        swal({
                            title: 'Produto adicionado!',
                            text: 'O produto foi adicionado ao seu carrinho.',
                            type: 'success',
                            timer: 2000
                        });
                    }

                    // Atualiza contador do carrinho se existir
                    if (response.cart_count !== undefined) {
                        $('.cart-count, .js-cart-count').text(response.cart_count);
                    }

                    // Reseta quantidade para 1 após adicionar
                    $input.val(1);
                },
                error: function(xhr) {
                    // Feedback de erro
                    var msg = 'Erro ao adicionar produto ao carrinho.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }

                    if (typeof swal !== 'undefined') {
                        swal({
                            title: 'Erro!',
                            text: msg,
                            type: 'error'
                        });
                    } else {
                        alert(msg);
                    }
                },
                complete: function() {
                    // Remove feedback visual
                    $btn.removeClass('loading').prop('disabled', false);
                }
            });
        });
    });
    </script>

    {{-- Page Scripts --}}
    @stack('scripts')
</body>

</html>
