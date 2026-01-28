<?php

use App\Models\ThemeSetting;
use Illuminate\Support\Facades\Cache;

if (!function_exists('theme_color')) {
    /**
     * Retorna uma cor específica do tema ativo
     *
     * Usa cache para evitar queries repetidas ao banco de dados.
     * A cache é invalidada automaticamente quando o tema é atualizado.
     *
     * Exemplos de uso:
     * - theme_color('brand.primary') // Retorna #013E3B
     * - theme_color('button.primary_bg') // Retorna #FFA733
     * - theme_color('text.primary', '#000') // Retorna cor ou fallback
     *
     * @param string $key - Chave da cor usando notação dot (ex: 'brand.primary')
     * @param string $default - Cor padrão caso não encontre (padrão: #000000)
     * @return string - Cor em formato hexadecimal ou rgba
     */
    function theme_color(string $key, string $default = '#000000'): string
    {
        // Busca todas as cores do cache (válido por 1 hora)
        $colors = Cache::remember('theme.colors', 3600, function () {
            $theme = ThemeSetting::active()->first();
            return $theme ? $theme->colors : [];
        });

        // Retorna a cor usando notação dot ou o padrão
        return data_get($colors, $key, $default);
    }
}

if (!function_exists('theme')) {
    /**
     * Retorna o tema ativo completo
     *
     * Útil quando precisa acessar múltiplas propriedades do tema.
     * Também usa cache para performance.
     *
     * Exemplo de uso:
     * - theme()->name // Retorna "Naturallis Original"
     * - theme()->colors // Retorna array completo de cores
     * - theme()->fonts // Retorna configurações de fontes
     *
     * @return ThemeSetting|null
     */
    function theme(): ?ThemeSetting
    {
        return Cache::remember('theme.active', 3600, function () {
            return ThemeSetting::active()->first();
        });
    }
}

if (!function_exists('theme_css_url')) {
    /**
     * Retorna a URL do CSS dinâmico do tema
     *
     * Este CSS contém variáveis CSS (custom properties) com as cores do tema.
     * Adiciona parâmetro de versão baseado no updated_at para forçar reload.
     *
     * Exemplo de uso no Blade:
     * <link href="{{ theme_css_url() }}" rel="stylesheet">
     *
     * @return string - URL do CSS dinâmico com parâmetro de versão
     */
    function theme_css_url(): string
    {
        $theme = theme();
        $version = $theme ? $theme->updated_at->timestamp : time();

        return route('theme.css') . '?v=' . $version;
    }
}

if (!function_exists('theme_logo')) {
    /**
     * Retorna a URL da logo do tema ativo
     *
     * Se não houver logo configurada, retorna um fallback padrão.
     * A logo sempre aponta para a home (/) do site.
     *
     * Exemplo de uso no Blade:
     * <a href="{{ url('/') }}">
     *     <img src="{{ theme_logo() }}" alt="{{ theme_logo_alt() }}">
     * </a>
     *
     * @return string - URL da logo ou fallback
     */
    function theme_logo(): string
    {
        $theme = theme();

        if ($theme && $theme->logo_path) {
            return asset('storage/' . $theme->logo_path);
        }

        // Fallback: logo padrão (se existir) ou placeholder
        return asset('images/logo-default.png');
    }
}

if (!function_exists('theme_logo_alt')) {
    /**
     * Retorna o texto alternativo (alt) da logo
     *
     * Importante para acessibilidade e SEO.
     *
     * @return string - Texto alternativo da logo
     */
    function theme_logo_alt(): string
    {
        $theme = theme();

        if ($theme && $theme->logo_alt) {
            return $theme->logo_alt;
        }

        return config('app.name', 'Logo');
    }
}

if (!function_exists('top_bar')) {
    /**
     * Renderiza a Top Bar (Barra de Anúncios) se estiver ativa
     *
     * Exibe uma barra no topo do site com texto personalizável e cores configuráveis.
     * O texto pode conter HTML básico (links, negrito, etc).
     *
     * Exemplo de uso no Blade:
     * {!! top_bar() !!}
     *
     * @return string - HTML da top bar ou string vazia se desativada
     */
    function top_bar(): string
    {
        $theme = theme();

        // Retorna vazio se não houver tema ou se estiver desativada
        if (!$theme || !$theme->top_bar_enabled || empty($theme->top_bar_text)) {
            return '';
        }

        // Cores padrão caso não estejam definidas
        $bgColor = $theme->top_bar_bg_color ?? '#013E3B';
        $textColor = $theme->top_bar_text_color ?? '#FFFFFF';

        // Renderiza a top bar com inline styles (usa !important para garantir que as cores sejam aplicadas)
        return '<section class="top-bar-announcement" style="background-color: ' . e($bgColor) . ' !important; color: ' . e($textColor) . ' !important; padding: 12px 0; text-align: center; font-size: 14px;">
            <div class="container" style="color: ' . e($textColor) . ' !important;">
                ' . $theme->top_bar_text . '
            </div>
        </section>';
    }
}

if (!function_exists('hero_banners')) {
    /**
     * Renderiza os Banners Hero (Principal) visíveis no momento
     *
     * Exibe carousels separados para desktop e mobile com os banners ativos.
     * - Desktop: imagens 1400x385px (hidden-xs)
     * - Mobile: imagens 766x981px (visible-xs)
     *
     * Apenas banners marcados como ativos e dentro do período de exibição são mostrados.
     * Banners são ordenados pelo campo 'order' (menor = primeiro).
     *
     * Usa Owl Carousel para animação de slides.
     *
     * Exemplo de uso no Blade:
     * {!! hero_banners() !!}
     *
     * @return string - HTML dos carousels ou string vazia se não houver banners
     */
    function hero_banners(): string
    {
        // Busca banners visíveis e ordenados
        $banners = \App\Models\Banner::visible()->ordered()->get();

        // Se não há banners, retorna vazio
        if ($banners->isEmpty()) {
            return '';
        }

        // Constrói HTML do carousel desktop
        $desktopHtml = '<div id="banner-principal" class="js-banner-principal owl-carousel owl-theme hidden-xs">';
        foreach ($banners as $banner) {
            $link = $banner->link ?: '#';
            $altText = e($banner->alt_text);
            $imageUrl = $banner->getDesktopImageUrl();

            $desktopHtml .= '<a href="' . e($link) . '">';
            $desktopHtml .= '<div class="item boxHeight">';
            $desktopHtml .= '<img src="' . e($imageUrl) . '" class="img-responsive" alt="' . $altText . '" title="">';
            $desktopHtml .= '</div>';
            $desktopHtml .= '</a>';
        }
        $desktopHtml .= '</div>';

        // Constrói HTML do carousel mobile
        $mobileHtml = '<div id="banner-principal-mob" class="js-banner-principal owl-carousel owl-theme visible-xs">';
        foreach ($banners as $banner) {
            $link = $banner->link ?: '#';
            $altText = e($banner->alt_text);
            $imageUrl = $banner->getMobileImageUrl();

            $mobileHtml .= '<a href="' . e($link) . '">';
            $mobileHtml .= '<div class="item boxHeight">';
            $mobileHtml .= '<img src="' . e($imageUrl) . '" class="img-responsive" alt="' . $altText . '" title="">';
            $mobileHtml .= '</div>';
            $mobileHtml .= '</a>';
        }
        $mobileHtml .= '</div>';

        // Retorna ambos os carousels envolvidos na estrutura de container Bootstrap
        // A estrutura section.container > row > col-xs-12 garante que o banner respeite
        // a largura máxima do site, igual aos demais blocos da página
        return '<section class="container">'
            . '<div class="row">'
            . '<div class="col-xs-12">'
            . $desktopHtml . "\n\n" . $mobileHtml
            . '</div>'
            . '</div>'
            . '</section>';
    }
}

if (!function_exists('feature_blocks')) {
    /**
     * Renderiza os blocos de features/informações (régua) exibidos abaixo do banner hero
     *
     * Exibe 4 blocos com ícones, títulos e descrições personalizáveis.
     * Cada bloco pode ter cores de fundo, texto e ícone configuráveis.
     *
     * Exemplo de uso no Blade:
     * {!! feature_blocks() !!}
     *
     * @return string - HTML dos blocos ou string vazia se não houver blocos ativos
     */
    function feature_blocks(): string
    {
        // Busca blocos ativos e ordenados
        $blocks = \App\Models\FeatureBlock::active()->ordered()->get();

        // Se não há blocos ativos, retorna vazio
        if ($blocks->isEmpty()) {
            return '';
        }

        // Constrói HTML dos blocos
        $html = '<div class="faixa-info">';
        $html .= '<div class="container">';
        $html .= '<div class="row">';

        foreach ($blocks as $block) {
            $html .= '<div class="col-xs-12 col-sm-6 col-md-3">';
            $html .= '<div class="item-info boxHeight" style="' . e($block->getInlineStyle()) . '">';
            // Renderiza o ícone como imagem (SVG/PNG) - sem styles inline para respeitar CSS do tema
            $html .= '<img class="img-responsive" src="' . e($block->getIconUrl()) . '" alt="" title="">';
            $html .= '<p><span>' . e($block->title) . '</span> ' . e($block->description) . '</p>';
            $html .= '</div>';
            $html .= '</div>';
        }

        $html .= '</div>'; // row
        $html .= '</div>'; // container
        $html .= '</div>'; // faixa-info

        return $html;
    }
}

if (!function_exists('product_galleries')) {
    /**
     * Renderiza as galerias de produtos ativas na home
     *
     * Exibe até 4 galerias configuráveis com filtros dinâmicos,
     * layout responsivo e cores customizáveis.
     *
     * Utiliza o componente padronizado de card de produto para consistência
     * visual em toda a loja.
     *
     * Exemplo de uso no Blade:
     * {!! product_galleries() !!}
     *
     * @return string - HTML das galerias ou string vazia se não houver galerias ativas
     */
    function product_galleries(): string
    {
        // Busca galerias ativas ordenadas
        $galleries = \App\Models\ProductGallery::active()->ordered()->get();

        // Se não há galerias ativas, retorna vazio
        if ($galleries->isEmpty()) {
            return '';
        }

        // Usa view para renderizar as galerias com o componente padronizado de produto
        return view('storefront.partials.product.galleries', [
            'galleries' => $galleries
        ])->render();
    }
}

if (!function_exists('render_product_card')) {
    /**
     * Renderiza um card de produto usando o componente padronizado
     *
     * Pode ser usado em qualquer lugar que precise exibir um produto:
     * - Galerias da home
     * - Listagem de categorias
     * - Resultados de busca
     * - Produtos relacionados
     *
     * @param \App\Models\Product $product - Produto a ser renderizado
     * @param string $columnClass - Classes de coluna Bootstrap (ex: 'col-xs-6 col-sm-4 col-lg-3')
     * @param bool $isCarousel - Se está dentro de um carousel (ajusta estrutura)
     * @return string - HTML do card de produto
     */
    function render_product_card(\App\Models\Product $product, string $columnClass = '', bool $isCarousel = false): string
    {
        return view('storefront.partials.product.card', [
            'product' => $product,
            'columnClass' => $columnClass,
            'isCarousel' => $isCarousel
        ])->render();
    }
}

if (!function_exists('dual_banners')) {
    /**
     * Renderiza os banners duplos ativos na home
     *
     * Exibe pares de banners lado a lado (desktop) ou empilhados (mobile).
     * Apenas banners ativos e dentro do período de exibição são mostrados.
     *
     * Exemplo de uso no Blade:
     * {!! dual_banners() !!}
     *
     * @return string - HTML dos banners duplos ou string vazia se não houver banners
     */
    function dual_banners(): string
    {
        // Busca pares de banners visíveis (ativos e dentro do período)
        $dualBanners = \App\Models\DualBanner::visible()->ordered()->get();

        // Se não há banners, retorna vazio
        if ($dualBanners->isEmpty()) {
            return '';
        }

        $html = '';

        // Renderiza cada par de banners
        foreach ($dualBanners as $dualBanner) {
            // Verifica se pelo menos um banner está visível
            $leftVisible = $dualBanner->isLeftVisible();
            $rightVisible = $dualBanner->isRightVisible();

            if (!$leftVisible && !$rightVisible) {
                continue;
            }

            // Inicia seção de banners duplos (estrutura igual ao original)
            $html .= '<section class="box-destaque-top order">';
            $html .= '<div class="container">';
            $html .= '<div class="item-destaque-home">';
            $html .= '<div class="row">';

            // Banner Esquerdo
            if ($leftVisible) {
                $leftLink = $dualBanner->left_link ?: '#';
                $leftAlt = e($dualBanner->left_alt_text ?: 'Banner');
                $leftImageUrl = $dualBanner->getLeftImageUrl();

                $html .= '<div class="col-xs-12 col-sm-6 boxHeight">';
                $html .= '<a class="item" href="' . e($leftLink) . '">';
                $html .= '<img class="img-responsive" src="' . e($leftImageUrl) . '" ';
                $html .= 'title="' . $leftAlt . '" ';
                $html .= 'alt="' . $leftAlt . '" />';
                $html .= '<div class="text-center">';
                $html .= '<span class="btn btn-large">Saiba mais</span>';
                $html .= '</div>';
                $html .= '</a>';
                $html .= '</div>';
            }

            // Banner Direito
            if ($rightVisible) {
                $rightLink = $dualBanner->right_link ?: '#';
                $rightAlt = e($dualBanner->right_alt_text ?: 'Banner');
                $rightImageUrl = $dualBanner->getRightImageUrl();

                $html .= '<div class="col-xs-12 col-sm-6 boxHeight">';
                $html .= '<a class="item" href="' . e($rightLink) . '">';
                $html .= '<img class="img-responsive" src="' . e($rightImageUrl) . '" ';
                $html .= 'title="' . $rightAlt . '" ';
                $html .= 'alt="' . $rightAlt . '" />';
                $html .= '<div class="text-center">';
                $html .= '<span class="btn btn-large">Saiba mais</span>';
                $html .= '</div>';
                $html .= '</a>';
                $html .= '</div>';
            }

            $html .= '</div>'; // row
            $html .= '</div>'; // item-destaque-home
            $html .= '</div>'; // container
            $html .= '</section>'; // box-destaque-top
        }

        return $html;
    }
}

if (!function_exists('info_blocks')) {
    /**
     * Renderiza blocos informativos ativos na home
     *
     * Exibe blocos com imagem grande + título + subtítulo
     * Exemplo: seção "Refeições Saudáveis"
     */
    function info_blocks(): string
    {
        $blocks = \App\Models\InfoBlock::active()->ordered()->get();

        if ($blocks->isEmpty()) {
            return '';
        }

        $html = '';

        foreach ($blocks as $block) {
            $bgStyle = $block->background_color ? ' style="background-color: ' . e($block->background_color) . ';"' : '';
            
            $html .= '<section class="bg-refeicoes-saudaveis"' . $bgStyle . '>';
            $html .= '<div class="container">';
            $html .= '<div class="row">';
            $html .= '<div class="flex no-flex-xs box-cover">';
            
            // Imagem
            $html .= '<div class="col-xs-12 col-sm-6 col-md-7">';
            $html .= '<img alt="' . e($block->image_alt ?: $block->title) . '" ';
            $html .= 'class="img-responsive" src="' . e($block->getImageUrl()) . '" />';
            $html .= '</div>';
            
            // Texto
            $html .= '<div class="col-xs-12 col-sm-6 col-md-5">';
            $html .= '<h2>' . e($block->title) . '</h2>';
            if ($block->subtitle) {
                $html .= '<h3>' . e($block->subtitle) . '</h3>';
            }
            $html .= '</div>';
            
            $html .= '</div>'; // flex
            $html .= '</div>'; // row
            $html .= '</div>'; // container
            $html .= '</section>';
        }

        return $html;
    }
}

if (!function_exists('step_blocks')) {
    /**
     * Renderiza blocos de passos (4 itens) ativos na home
     *
     * Exibe blocos com 4 itens contendo ícone, título e descrição
     * Exemplo: "Entrega agendada", "Descontos & Exclusividades", etc.
     *
     * Layout: 4 colunas em desktop (col-md-3), 2 colunas em tablet (col-sm-6), 1 coluna em mobile (col-xs-12)
     *
     * Exemplo de uso no Blade:
     * {!! step_blocks() !!}
     *
     * @return string - HTML dos blocos de passos ou string vazia se não houver blocos
     */
    function step_blocks(): string
    {
        // Busca blocos ativos ordenados
        $blocks = \App\Models\StepBlock::active()->ordered()->get();

        // Se não há blocos, retorna vazio
        if ($blocks->isEmpty()) {
            return '';
        }

        $html = '';

        // Renderiza cada bloco (cada bloco contém 4 itens)
        foreach ($blocks as $block) {
            $html .= '<section class="step-items-block">';
            $html .= '<div class="container">';
            $html .= '<div class="row">';

            // Renderiza os 4 itens
            for ($i = 1; $i <= 4; $i++) {
                $iconUrl = $block->getIconUrl($i);
                $title = $block->{"item_{$i}_title"};
                $description = $block->{"item_{$i}_description"};
                $alt = $block->{"item_{$i}_alt"} ?: $title;

                $html .= '<div class="col-xs-12 col-sm-6 col-md-3">';
                $html .= '<div class="box-item">';
                $html .= '<img alt="' . e($alt) . '" src="' . e($iconUrl) . '" />';
                $html .= '<h3>' . e($title) . '</h3>';
                $html .= '<p>' . e($description) . '</p>';
                $html .= '</div>'; // box-item
                $html .= '</div>'; // col
            }

            $html .= '</div>'; // row
            $html .= '</div>'; // container
            $html .= '</section>'; // step-items-block
        }

        return $html;
    }
}

if (!function_exists('single_banners')) {
    /**
     * Renderiza banners únicos ativos na home
     *
     * Exibe um banner por seção com imagem diferente para desktop e mobile.
     * Apenas banners ativos e dentro do período de exibição são mostrados.
     *
     * Estrutura HTML:
     * - Desktop: hidden-xs hidden-sm (apenas desktop)
     * - Mobile: visible-xs visible-sm (apenas mobile/tablet)
     *
     * Exemplo de uso no Blade:
     * {!! single_banners() !!}
     *
     * @return string - HTML dos banners únicos ou string vazia se não houver banners
     */
    function single_banners(): string
    {
        // Busca banners visíveis (ativos e dentro do período)
        $banners = \App\Models\SingleBanner::visible()->ordered()->get();

        // Se não há banners, retorna vazio
        if ($banners->isEmpty()) {
            return '';
        }

        $html = '';

        // Renderiza cada banner
        foreach ($banners as $banner) {
            $link = $banner->link ?: '#';
            $altText = e($banner->alt_text ?: 'Banner');
            $desktopImageUrl = $banner->getDesktopImageUrl();
            $mobileImageUrl = $banner->getMobileImageUrl();

            // Inicia seção do banner (classe bg-ban-link como no original)
            $html .= '<section class="bg-ban-link">';
            $html .= '<div class="container">';
            $html .= '<div class="row">';
            $html .= '<div class="col-xs-12">';

            // Link do banner
            $html .= '<a href="' . e($link) . '" target="_blank">';

            // Imagem Desktop (oculta em mobile)
            $html .= '<img alt="' . $altText . '" ';
            $html .= 'class="img-responsive hidden-xs hidden-sm" ';
            $html .= 'src="' . e($desktopImageUrl) . '" />';

            // Imagem Mobile (oculta em desktop)
            $html .= '<img alt="' . $altText . '" ';
            $html .= 'class="img-responsive visible-xs visible-sm" ';
            $html .= 'src="' . e($mobileImageUrl) . '" />';

            $html .= '</a>';

            $html .= '</div>'; // col-xs-12
            $html .= '</div>'; // row
            $html .= '</div>'; // container
            $html .= '</section>'; // bg-ban-link
        }

        return $html;
    }
}

if (!function_exists('cookie_consent')) {
    /**
     * Renderiza o disclaimer de Cookie Consent LGPD
     *
     * Exibe o disclaimer apenas se:
     * - Estiver ativo nas configurações
     * - O usuário ainda não aceitou (verificado via JavaScript/cookie)
     *
     * O disclaimer é exibido no rodapé da página e some após o usuário clicar em aceitar.
     * Um cookie é salvo no navegador para não exibir novamente.
     *
     * Exemplo de uso no Blade:
     * {!! cookie_consent() !!}
     *
     * @return string - HTML do disclaimer ou string vazia se inativo
     */
    function cookie_consent(): string
    {
        // Busca configuração
        $config = \App\Models\CookieConsent::active()->first();

        // Se não está ativo ou não existe, retorna vazio
        if (!$config) {
            return '';
        }

        // Cores e textos configuráveis
        $buttonBgColor = e($config->button_bg_color);
        $buttonTextColor = e($config->button_text_color);
        $buttonHoverBgColor = e($config->button_hover_bg_color);
        $buttonLabel = e($config->button_label);
        $messageText = $config->message_text; // Não escapar pois pode conter HTML

        // HTML do disclaimer (inicialmente oculto, será exibido via JavaScript se necessário)
        $html = '<div class="lgpd-cookies" style="display: none;">';
        $html .= '<div class="lgpd-cookies-group">';
        $html .= '<p>' . $messageText . '</p>';
        $html .= '<div class="lgpd-cookies-group-btn">';

        // Botão com cores customizáveis e evento JavaScript
        $html .= '<a href="javascript:void(0);" ';
        $html .= 'onclick="acceptCookieConsent()" ';
        $html .= 'class="btn js-lgpd-accept" ';
        $html .= 'style="';
        $html .= 'background-color: ' . $buttonBgColor . ' !important; ';
        $html .= 'color: ' . $buttonTextColor . ' !important; ';
        $html .= 'border-color: ' . $buttonBgColor . ' !important; ';
        $html .= 'border-radius: 30px; ';
        $html .= 'padding: 10px 25px; ';
        $html .= 'transition: all 0.3s ease; ';
        $html .= '" ';
        // Evento hover inline
        $html .= 'onmouseover="this.style.backgroundColor=\'' . $buttonHoverBgColor . '\'; this.style.borderColor=\'' . $buttonHoverBgColor . '\'" ';
        $html .= 'onmouseout="this.style.backgroundColor=\'' . $buttonBgColor . '\'; this.style.borderColor=\'' . $buttonBgColor . '\'">';
        $html .= $buttonLabel;
        $html .= '</a>';

        $html .= '</div>'; // lgpd-cookies-group-btn
        $html .= '</div>'; // lgpd-cookies-group
        $html .= '</div>'; // lgpd-cookies

        // JavaScript para controlar exibição (verifica cookie e exibe se necessário)
        $html .= '<script>';
        $html .= '(function() {';
        $html .= '  function getCookie(name) {';
        $html .= '    var value = "; " + document.cookie;';
        $html .= '    var parts = value.split("; " + name + "=");';
        $html .= '    if (parts.length === 2) return parts.pop().split(";").shift();';
        $html .= '    return null;';
        $html .= '  }';
        $html .= '  if (!getCookie("cookie_consent_accepted")) {';
        $html .= '    var disclaimer = document.querySelector(".lgpd-cookies");';
        $html .= '    if (disclaimer) disclaimer.style.display = "inline-flex";'; // Mudado de "block" para "inline-flex"
        $html .= '  }';
        $html .= '})();';
        $html .= 'function acceptCookieConsent() {';
        $html .= '  var expires = new Date();';
        $html .= '  expires.setTime(expires.getTime() + (365 * 24 * 60 * 60 * 1000));'; // 1 ano
        $html .= '  document.cookie = "cookie_consent_accepted=true; expires=" + expires.toUTCString() + "; path=/";';
        $html .= '  var disclaimer = document.querySelector(".lgpd-cookies");';
        $html .= '  if (disclaimer) {';
        $html .= '    disclaimer.style.opacity = "0";';
        $html .= '    disclaimer.style.transition = "opacity 0.3s ease";';
        $html .= '    setTimeout(function() { disclaimer.style.display = "none"; }, 300);';
        $html .= '  }';
        $html .= '}';
        $html .= '</script>';

        return $html;
    }
}

if (!function_exists('social_networks')) {
    /**
     * Renderiza os ícones das Redes Sociais ativas
     *
     * Exibe os ícones de redes sociais (Facebook, Instagram, WhatsApp, etc.)
     * que estão marcadas como ativas, ordenadas pelo campo 'order'.
     *
     * As redes sociais são exibidas em dois lugares do site:
     * 1. Topo da página (rede-social-topo)
     * 2. Rodapé (rede-social-footer)
     *
     * Exemplo de uso no Blade:
     * {!! social_networks() !!}
     *
     * @return string HTML dos ícones de redes sociais ou string vazia
     */
    function social_networks(): string
    {
        // Busca redes sociais ativas e ordenadas
        $socials = \App\Models\SocialNetwork::visible()->get();

        // Se não há redes sociais ativas, retorna vazio
        if ($socials->isEmpty()) {
            return '';
        }

        // Constrói HTML dos ícones (CSS controlado por theme-override.css)
        $html = '';
        foreach ($socials as $social) {
            $iconUrl = e($social->getIconUrl());
            $url = e($social->url);
            $name = e($social->name);

            $html .= '<a href="' . $url . '" target="_blank" rel="noopener noreferrer" title="' . $name . '">';
            $html .= '<img src="' . $iconUrl . '" alt="' . $name . '">';
            $html .= '</a>';
        }

        return $html;
    }
}

if (!function_exists('institutional_pages_menu')) {
    /**
     * Renderiza os links das Páginas Internas ativas no menu Institucional
     *
     * Exibe as páginas institucionais ativas como links no menu do rodapé.
     * Apenas páginas marcadas como ativas são exibidas.
     *
     * Exemplo de uso no Blade:
     * {!! institutional_pages_menu() !!}
     *
     * @return string HTML dos links de páginas ou string vazia
     */
    function institutional_pages_menu(): string
    {
        // Busca páginas ativas ordenadas por título
        $pages = \App\Models\Page::active()->orderBy('title', 'asc')->get();

        // Se não há páginas ativas, retorna vazio
        if ($pages->isEmpty()) {
            return '';
        }

        // Constrói HTML dos links (formato do rodapé)
        $html = '';
        foreach ($pages as $page) {
            $url = e($page->getUrl());
            $title = e($page->title);

            $html .= '<li><a href="' . $url . '">' . $title . '</a></li>';
        }

        return $html;
    }
}

// ============================================================================
// FUNÇÕES DE MENU DE NAVEGAÇÃO
// ============================================================================

if (!function_exists('main_menu')) {
    /**
     * Retorna o menu principal do cabeçalho
     *
     * Usado para renderizar o menu de navegação no header.
     * Retorna os itens raiz com filhos carregados recursivamente.
     *
     * Exemplo de uso no Blade:
     * @foreach(main_menu() as $item)
     *     @include('storefront.partials.menu.item', ['item' => $item])
     * @endforeach
     *
     * @param string $device Filtrar por dispositivo: 'desktop', 'mobile', 'all' (padrão)
     * @return \Illuminate\Support\Collection
     */
    function main_menu(string $device = 'all'): \Illuminate\Support\Collection
    {
        $menu = \App\Models\Menu::getMainMenu();

        if (!$menu) {
            return collect();
        }

        return $menu->getItemsTree($device);
    }
}

if (!function_exists('footer_menu')) {
    /**
     * Retorna o menu do rodapé
     *
     * Usado para renderizar links de navegação no footer.
     *
     * @param string $device Filtrar por dispositivo
     * @return \Illuminate\Support\Collection
     */
    function footer_menu(string $device = 'all'): \Illuminate\Support\Collection
    {
        $menu = \App\Models\Menu::getFooterMenu();

        if (!$menu) {
            return collect();
        }

        return $menu->getItemsTree($device);
    }
}

if (!function_exists('mobile_menu')) {
    /**
     * Retorna o menu lateral mobile
     *
     * Usado para renderizar o menu hamburguer/sidebar no mobile.
     *
     * @return \Illuminate\Support\Collection
     */
    function mobile_menu(): \Illuminate\Support\Collection
    {
        $menu = \App\Models\Menu::getMobileMenu();

        if (!$menu) {
            return collect();
        }

        return $menu->getItemsTree('mobile');
    }
}

if (!function_exists('sidebar_menu')) {
    /**
     * Retorna o menu lateral (sidebar) das páginas internas
     *
     * Usado para renderizar o menu lateral nas páginas de categoria e produto (desktop).
     * É um menu independente do menu mobile, permitindo configuração específica.
     *
     * Exemplo de uso no Blade:
     * @foreach(sidebar_menu() as $item)
     *     {!! renderSidebarMenuItem($item) !!}
     * @endforeach
     *
     * @return \Illuminate\Support\Collection
     */
    function sidebar_menu(): \Illuminate\Support\Collection
    {
        // Busca o menu específico do sidebar (slug: sidebar-categories)
        $menu = \App\Models\Menu::getBySlug('sidebar-categories');

        if (!$menu) {
            return collect();
        }

        return $menu->getItemsTree('all');
    }
}

if (!function_exists('menu')) {
    /**
     * Retorna um menu pelo slug
     *
     * Função genérica para obter qualquer menu pelo identificador único.
     *
     * Exemplo de uso:
     * @foreach(menu('footer-institucional') as $item)
     *     <li><a href="{{ $item->getResolvedUrl() }}">{{ $item->title }}</a></li>
     * @endforeach
     *
     * @param string $slug Identificador único do menu
     * @param string $device Filtrar por dispositivo
     * @return \Illuminate\Support\Collection
     */
    function menu(string $slug, string $device = 'all'): \Illuminate\Support\Collection
    {
        $menu = \App\Models\Menu::getBySlug($slug);

        if (!$menu) {
            return collect();
        }

        return $menu->getItemsTree($device);
    }
}

if (!function_exists('render_menu_item')) {
    /**
     * Renderiza um item de menu como HTML
     *
     * Função auxiliar para renderizar itens de menu com suporte a:
     * - Dropdowns e mega menus
     * - Ícones (classe ou imagem)
     * - Links ativos
     *
     * @param \App\Models\MenuItem $item
     * @param bool $isDesktop Se é renderização para desktop
     * @return string HTML do item
     */
    function render_menu_item(\App\Models\MenuItem $item, bool $isDesktop = true): string
    {
        $url = $item->getResolvedUrl();
        $hasChildren = $item->hasChildren();
        $classes = $item->getCssClasses();

        // Se é título de submenu (sem link)
        if ($item->type === 'submenu_title') {
            return '<li class="' . e($classes) . '"><span class="menu-title">' . e($item->title) . '</span></li>';
        }

        // Início do <li>
        $html = '<li class="' . e($classes) . '">';

        // Link principal
        if ($hasChildren && $isDesktop) {
            // Dropdown - link com toggle
            $html .= '<a class="dropdown-toggle" href="' . ($url ? e($url) : 'javascript:void(0)') . '"';
            if (!$url) {
                $html .= ' data-toggle="dropdown"';
            }
            $html .= '>';
        } else {
            // Link simples
            $html .= '<a href="' . ($url ? e($url) : '#') . '"';
            if ($item->target === '_blank') {
                $html .= ' target="_blank" rel="noopener noreferrer"';
            }
            $html .= '>';
        }

        // Ícone (classe CSS)
        if ($item->icon_class) {
            $html .= '<i class="' . e($item->icon_class) . '"></i> ';
        }

        // Ícone (imagem)
        if ($item->icon_image && $item->getIconImageUrl()) {
            $html .= '<img class="icon-menu" src="' . e($item->getIconImageUrl()) . '" alt="' . e($item->title) . '"> ';
        }

        // Título
        $html .= e($item->title);

        // Seta para dropdown
        if ($hasChildren) {
            $html .= '<i class="fa fa-caret-down"></i>';
        }

        $html .= '</a>';

        // Submenu (filhos)
        if ($hasChildren) {
            $html .= '<ul class="dropdown-menu" role="menu">';

            foreach ($item->activeChildren as $child) {
                $html .= render_menu_item($child, $isDesktop);
            }

            $html .= '</ul>';
        }

        $html .= '</li>';

        return $html;
    }
}

if (!function_exists('render_mega_menu')) {
    /**
     * Renderiza um mega menu completo com colunas e imagem promocional
     *
     * Estrutura:
     * - Lado esquerdo: itens organizados em colunas
     * - Lado direito: imagem promocional (se configurada)
     *
     * @param \App\Models\MenuItem $item Item raiz do mega menu
     * @return string HTML do mega menu
     */
    function render_mega_menu(\App\Models\MenuItem $item): string
    {
        if (!$item->is_mega_menu || !$item->hasChildren()) {
            return render_menu_item($item);
        }

        $url = $item->getResolvedUrl();
        $classes = $item->getCssClasses();
        $columns = $item->mega_menu_columns ?: 2;
        $hasImage = $item->hasMegaMenuImage();
        $imagePosition = $item->mega_menu_image_position;

        // Calcula tamanho das colunas baseado na imagem
        $contentCols = $hasImage ? 6 : 12;
        $imageCols = $hasImage ? 6 : 0;

        // Início do item
        $html = '<li class="dropdown ' . e($classes) . '">';

        // Link principal
        $html .= '<a class="dropdown-toggle" href="' . ($url ? e($url) : 'javascript:void(0)') . '">';
        $html .= e($item->title);
        $html .= '<i class="fa fa-caret-down"></i>';
        $html .= '</a>';

        // Dropdown mega menu
        $html .= '<ul class="dropdown-menu">';
        $html .= '<div class="container">';
        $html .= '<div class="row">';

        // Imagem à esquerda (se configurado)
        if ($hasImage && $imagePosition === 'left') {
            $html .= render_mega_menu_image($item, $imageCols);
        }

        // Conteúdo (colunas de itens)
        $html .= '<div class="col-xs-12 col-md-' . $contentCols . '">';
        $html .= '<div class="col-left">';
        $html .= '<div class="row">';

        // Renderiza itens filhos em colunas
        $colClass = 'col-xs-6 col-sm-3 col-md-' . (12 / $columns);
        foreach ($item->activeChildren as $child) {
            $html .= '<div class="' . $colClass . '">';
            $html .= '<div class="group-menu boxHeight2">';

            // Título do grupo (link para categoria principal)
            $childUrl = $child->getResolvedUrl();
            $html .= '<h5 class="boxHeight3">';
            if ($childUrl) {
                $html .= '<a href="' . e($childUrl) . '">' . e($child->title) . '</a>';
            } else {
                $html .= e($child->title);
            }
            $html .= '</h5>';

            // Subitens do grupo
            if ($child->hasChildren()) {
                foreach ($child->activeChildren as $subItem) {
                    $subUrl = $subItem->getResolvedUrl();
                    if ($subUrl) {
                        $html .= '<li><a href="' . e($subUrl) . '">' . e($subItem->title) . '</a></li>';
                    }
                }
            }

            $html .= '</div>'; // group-menu
            $html .= '</div>'; // col
        }

        $html .= '</div>'; // row
        $html .= '</div>'; // col-left
        $html .= '</div>'; // col conteúdo

        // Imagem à direita (padrão) ou abaixo
        if ($hasImage && $imagePosition === 'right') {
            $html .= render_mega_menu_image($item, $imageCols);
        }

        $html .= '</div>'; // row
        $html .= '</div>'; // container

        // Imagem abaixo (se configurado)
        if ($hasImage && $imagePosition === 'bottom') {
            $html .= '<div class="container">';
            $html .= '<div class="row">';
            $html .= render_mega_menu_image($item, 12);
            $html .= '</div>';
            $html .= '</div>';
        }

        $html .= '</ul>'; // dropdown-menu
        $html .= '</li>';

        return $html;
    }
}

if (!function_exists('render_mega_menu_image')) {
    /**
     * Renderiza a imagem/banner do mega menu
     *
     * @param \App\Models\MenuItem $item
     * @param int $cols Número de colunas Bootstrap
     * @return string HTML da imagem
     */
    function render_mega_menu_image(\App\Models\MenuItem $item, int $cols): string
    {
        $imageUrl = $item->getMegaMenuImageUrl();
        $imageAlt = $item->mega_menu_image_alt ?: $item->title;
        $linkUrl = $item->mega_menu_image_url;

        $html = '<div class="col-xs-12 col-md-' . $cols . ' boxHeight hidden-xs">';
        $html .= '<div class="col-right">';

        if ($linkUrl) {
            $html .= '<a href="' . e($linkUrl) . '">';
        }

        $html .= '<img class="img-responsive" src="' . e($imageUrl) . '" alt="' . e($imageAlt) . '">';

        if ($linkUrl) {
            $html .= '</a>';
        }

        $html .= '</div>'; // col-right
        $html .= '</div>'; // col

        return $html;
    }
}

if (!function_exists('home_sections')) {
    /**
     * Renderiza todas as seções da home na ordem configurada no admin
     *
     * Busca as seções ativas da tabela home_sections, ordenadas pelo campo 'order',
     * e renderiza cada uma chamando sua função helper correspondente.
     *
     * Exemplo de uso no Blade:
     * {!! home_sections() !!}
     *
     * Cada seção tem um helper_function associado (ex: hero_banners, feature_blocks)
     * que é chamado dinamicamente para gerar o HTML.
     *
     * @return string HTML de todas as seções concatenadas
     */
    function home_sections(): string
    {
        // Busca seções ativas ordenadas (usa cache para performance)
        $sections = Cache::remember('home.sections.ordered', 300, function () {
            return \App\Models\HomeSection::active()->ordered()->get();
        });

        $html = '';

        // Renderiza cada seção chamando seu helper
        foreach ($sections as $section) {
            $html .= $section->render();
        }

        return $html;
    }
}

if (!function_exists('clear_home_sections_cache')) {
    /**
     * Limpa o cache das seções da home
     *
     * Deve ser chamado sempre que a ordem ou status das seções for alterado.
     *
     * @return void
     */
    function clear_home_sections_cache(): void
    {
        Cache::forget('home.sections.ordered');
    }
}

// ============================================================================
// FUNÇÕES DE BLOCOS FLEXÍVEIS DA HOME (Sistema de blocos intercalados)
// ============================================================================

if (!function_exists('home_blocks')) {
    /**
     * Renderiza todos os blocos da home na ordem configurada no admin
     *
     * Este é o sistema FLEXÍVEL que substitui home_sections().
     * Permite intercalar diferentes tipos de blocos (galerias, banners, etc.)
     * em qualquer ordem desejada.
     *
     * Exemplo de uso no Blade:
     * {!! home_blocks() !!}
     *
     * @return string HTML de todos os blocos concatenados
     */
    function home_blocks(): string
    {
        // Busca blocos ativos ordenados (usa cache para performance)
        $blocks = Cache::remember('home.blocks.ordered', 300, function () {
            return \App\Models\HomeBlock::active()->ordered()->get();
        });

        // Otimização: pré-carrega TODOS os itens referenciados agrupados por tipo
        // Antes: 1 query por bloco (~550ms cada no banco remoto = N queries)
        // Depois: 1 query por TIPO de bloco (máximo 3-4 queries)
        $referencedItems = [];
        $groupedByType = $blocks->filter(fn($b) => $b->requiresReference() && $b->reference_id)
            ->groupBy('type');

        foreach ($groupedByType as $type => $typeBlocks) {
            $config = \App\Models\HomeBlock::BLOCK_TYPES[$type] ?? null;
            if ($config && $config['model']) {
                $modelClass = $config['model'];
                $ids = $typeBlocks->pluck('reference_id')->unique()->toArray();
                // Busca todos os itens desse tipo de uma vez
                $items = $modelClass::whereIn('id', $ids)->get()->keyBy('id');
                foreach ($items as $id => $item) {
                    $referencedItems[$type . '_' . $id] = $item;
                }
            }
        }

        $html = '';

        // Renderiza cada bloco, injetando o item pré-carregado quando disponível
        foreach ($blocks as $block) {
            if ($block->requiresReference() && $block->reference_id) {
                $key = $block->type . '_' . $block->reference_id;
                if (isset($referencedItems[$key])) {
                    // Injeta o item pré-carregado para evitar query individual no render()
                    $block->setRelation('_preloadedItem', $referencedItems[$key]);
                }
            }
            $html .= $block->render();
        }

        return $html;
    }
}

if (!function_exists('clear_home_blocks_cache')) {
    /**
     * Limpa o cache dos blocos da home
     *
     * Deve ser chamado sempre que blocos forem adicionados, removidos,
     * reordenados ou tiverem seu status alterado.
     *
     * @return void
     */
    function clear_home_blocks_cache(): void
    {
        Cache::forget('home.blocks.ordered');
    }
}

if (!function_exists('product_gallery')) {
    /**
     * Renderiza UMA galeria de produtos específica
     *
     * Diferente de product_galleries() que renderiza todas,
     * esta função renderiza apenas a galeria passada como parâmetro.
     *
     * Usada pelo sistema de blocos flexíveis (HomeBlock).
     *
     * @param \App\Models\ProductGallery $gallery - Galeria a ser renderizada
     * @return string - HTML da galeria ou string vazia se inativa
     */
    function product_gallery(\App\Models\ProductGallery $gallery): string
    {
        // Verifica se a galeria está ativa
        if (!$gallery->active) {
            return '';
        }

        // Usa view para renderizar a galeria com o componente padronizado de produto
        return view('storefront.partials.product.gallery-single', [
            'gallery' => $gallery
        ])->render();
    }
}

if (!function_exists('dual_banner')) {
    /**
     * Renderiza UM banner duplo específico
     *
     * Diferente de dual_banners() que renderiza todos,
     * esta função renderiza apenas o banner duplo passado como parâmetro.
     *
     * Usada pelo sistema de blocos flexíveis (HomeBlock).
     *
     * @param \App\Models\DualBanner $dualBanner - Banner duplo a ser renderizado
     * @return string - HTML do banner duplo ou string vazia se inativo
     */
    function dual_banner(\App\Models\DualBanner $dualBanner): string
    {
        // Verifica se pelo menos um lado está visível
        $leftVisible = $dualBanner->isLeftVisible();
        $rightVisible = $dualBanner->isRightVisible();

        if (!$leftVisible && !$rightVisible) {
            return '';
        }

        $html = '<section class="box-destaque-top order">';
        $html .= '<div class="container">';
        $html .= '<div class="item-destaque-home">';
        $html .= '<div class="row">';

        // Banner Esquerdo
        if ($leftVisible) {
            $leftLink = $dualBanner->left_link ?: '#';
            $leftAlt = e($dualBanner->left_alt_text ?: 'Banner');
            $leftImageUrl = $dualBanner->getLeftImageUrl();

            $html .= '<div class="col-xs-12 col-sm-6 boxHeight">';
            $html .= '<a class="item" href="' . e($leftLink) . '">';
            $html .= '<img class="img-responsive" src="' . e($leftImageUrl) . '" ';
            $html .= 'title="' . $leftAlt . '" ';
            $html .= 'alt="' . $leftAlt . '" />';
            $html .= '<div class="text-center">';
            $html .= '<span class="btn btn-large">Saiba mais</span>';
            $html .= '</div>';
            $html .= '</a>';
            $html .= '</div>';
        }

        // Banner Direito
        if ($rightVisible) {
            $rightLink = $dualBanner->right_link ?: '#';
            $rightAlt = e($dualBanner->right_alt_text ?: 'Banner');
            $rightImageUrl = $dualBanner->getRightImageUrl();

            $html .= '<div class="col-xs-12 col-sm-6 boxHeight">';
            $html .= '<a class="item" href="' . e($rightLink) . '">';
            $html .= '<img class="img-responsive" src="' . e($rightImageUrl) . '" ';
            $html .= 'title="' . $rightAlt . '" ';
            $html .= 'alt="' . $rightAlt . '" />';
            $html .= '<div class="text-center">';
            $html .= '<span class="btn btn-large">Saiba mais</span>';
            $html .= '</div>';
            $html .= '</a>';
            $html .= '</div>';
        }

        $html .= '</div>'; // row
        $html .= '</div>'; // item-destaque-home
        $html .= '</div>'; // container
        $html .= '</section>'; // box-destaque-top

        return $html;
    }
}

if (!function_exists('single_banner')) {
    /**
     * Renderiza UM banner único específico
     *
     * Diferente de single_banners() que renderiza todos,
     * esta função renderiza apenas o banner passado como parâmetro.
     *
     * Usada pelo sistema de blocos flexíveis (HomeBlock).
     *
     * @param \App\Models\SingleBanner $banner - Banner a ser renderizado
     * @return string - HTML do banner ou string vazia se inativo
     */
    function single_banner(\App\Models\SingleBanner $banner): string
    {
        // Verifica se o banner está visível (ativo e dentro do período)
        if (!$banner->isVisible()) {
            return '';
        }

        $link = $banner->link ?: '#';
        $altText = e($banner->alt_text ?: 'Banner');
        $desktopImageUrl = $banner->getDesktopImageUrl();
        $mobileImageUrl = $banner->getMobileImageUrl();

        $html = '<section class="bg-ban-link">';
        $html .= '<div class="container">';
        $html .= '<div class="row">';
        $html .= '<div class="col-xs-12">';

        $html .= '<a href="' . e($link) . '" target="_blank">';

        // Imagem Desktop
        $html .= '<img alt="' . $altText . '" ';
        $html .= 'class="img-responsive hidden-xs hidden-sm" ';
        $html .= 'src="' . e($desktopImageUrl) . '" />';

        // Imagem Mobile
        $html .= '<img alt="' . $altText . '" ';
        $html .= 'class="img-responsive visible-xs visible-sm" ';
        $html .= 'src="' . e($mobileImageUrl) . '" />';

        $html .= '</a>';

        $html .= '</div>'; // col-xs-12
        $html .= '</div>'; // row
        $html .= '</div>'; // container
        $html .= '</section>'; // bg-ban-link

        return $html;
    }
}

if (!function_exists('contact_settings')) {
    /**
     * Retorna as configurações de contato da loja
     *
     * Obtém os dados de contato (WhatsApp, email, horário de atendimento)
     * configurados no admin. Usa cache para performance.
     *
     * Exemplo de uso no Blade:
     * {{ contact_settings()->whatsapp_display }}
     * {{ contact_settings()->email }}
     * {!! contact_settings()->getWhatsAppUrl() !!}
     *
     * @return \App\Models\ContactSetting
     */
    function contact_settings(): \App\Models\ContactSetting
    {
        return \App\Models\ContactSetting::getSettings();
    }
}

if (!function_exists('info_block')) {
    /**
     * Renderiza UM bloco de informação específico
     *
     * Diferente de info_blocks() que renderiza todos,
     * esta função renderiza apenas o bloco passado como parâmetro.
     *
     * Usada pelo sistema de blocos flexíveis (HomeBlock).
     *
     * @param \App\Models\InfoBlock $block - Bloco a ser renderizado
     * @return string - HTML do bloco ou string vazia se inativo
     */
    function info_block(\App\Models\InfoBlock $block): string
    {
        // Verifica se o bloco está ativo
        if (!$block->active) {
            return '';
        }

        $bgStyle = $block->background_color ? ' style="background-color: ' . e($block->background_color) . ';"' : '';

        $html = '<section class="bg-refeicoes-saudaveis"' . $bgStyle . '>';
        $html .= '<div class="container">';
        $html .= '<div class="row">';
        $html .= '<div class="flex no-flex-xs box-cover">';

        // Imagem
        $html .= '<div class="col-xs-12 col-sm-6 col-md-7">';
        $html .= '<img alt="' . e($block->image_alt ?: $block->title) . '" ';
        $html .= 'class="img-responsive" src="' . e($block->getImageUrl()) . '" />';
        $html .= '</div>';

        // Texto
        $html .= '<div class="col-xs-12 col-sm-6 col-md-5">';
        $html .= '<h2>' . e($block->title) . '</h2>';
        if ($block->subtitle) {
            $html .= '<h3>' . e($block->subtitle) . '</h3>';
        }
        $html .= '</div>';

        $html .= '</div>'; // flex
        $html .= '</div>'; // row
        $html .= '</div>'; // container
        $html .= '</section>';

        return $html;
    }
}

