<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThemeSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Controller para gerenciar configurações de layout da loja
 *
 * Permite administradores editarem cores, fontes e outras configurações
 * visuais do tema sem precisar editar código.
 */
class LayoutController extends Controller
{
    /**
     * Hub principal de configurações de layout
     *
     * Exibe cards com diferentes módulos de customização:
     * - Cores
     * - Fontes (futuro)
     * - Espaçamentos (futuro)
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $theme = ThemeSetting::active()->first();

        return view('admin.layout.index', compact('theme'));
    }

    /**
     * Exibe formulário de edição de cores
     *
     * Apresenta color pickers organizados por categoria para facilitar
     * a customização visual do tema.
     *
     * @return \Illuminate\View\View
     */
    public function colors()
    {
        $theme = ThemeSetting::active()->firstOrFail();

        return view('admin.layout.colors', compact('theme'));
    }

    /**
     * Salva alterações nas cores do tema
     *
     * Valida e persiste as cores alteradas no banco de dados.
     * Invalida o cache para garantir que as mudanças sejam refletidas imediatamente.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateColors(Request $request)
    {
        $theme = ThemeSetting::active()->firstOrFail();

        // Validação básica: todas as cores devem ser hexadecimais ou rgba
        $request->validate([
            'colors' => 'required|array',
        ]);

        // Reorganiza o array flat para a estrutura aninhada esperada
        $colors = $this->buildNestedColorArray($request->input('colors'));

        // Atualiza as cores
        $theme->colors = $colors;
        $theme->save();

        // Invalida cache para aplicar mudanças imediatamente
        Cache::forget('theme.active');
        Cache::forget('theme.colors');
        Cache::forget('theme.css');

        return redirect()
            ->route('admin.layout.colors')
            ->with('success', 'Cores atualizadas com sucesso!');
    }

    /**
     * Gera CSS dinâmico baseado nas cores do tema ativo
     *
     * Este endpoint retorna um arquivo CSS com variáveis CSS (custom properties)
     * populadas com as cores do tema. O CSS é cacheado para performance.
     *
     * @return \Illuminate\Http\Response
     */
    public function generateCSS()
    {
        // Busca CSS do cache (válido por 24h)
        $css = Cache::remember('theme.css', 86400, function () {
            $theme = ThemeSetting::active()->first();

            if (!$theme) {
                return $this->getDefaultCSS();
            }

            return $this->buildCSSFromTheme($theme);
        });

        return response($css)
            ->header('Content-Type', 'text/css')
            ->header('Cache-Control', 'public, max-age=86400'); // Cache no browser por 24h
    }

    /**
     * Constrói array aninhado de cores a partir do input flat do formulário
     *
     * Converte:
     * ['brand.primary' => '#013E3B']
     * Para:
     * ['brand' => ['primary' => '#013E3B']]
     *
     * @param array $flatColors
     * @return array
     */
    private function buildNestedColorArray(array $flatColors): array
    {
        $nested = [];

        foreach ($flatColors as $key => $value) {
            data_set($nested, $key, $value);
        }

        return $nested;
    }

    /**
     * Constrói CSS com variáveis a partir do tema
     *
     * Gera CSS custom properties (--color-*) para uso no frontend.
     * Permite alterar cores sem rebuild de assets.
     *
     * @param ThemeSetting $theme
     * @return string
     */
    private function buildCSSFromTheme(ThemeSetting $theme): string
    {
        $colors = $theme->colors;

        $css = "/**\n";
        $css .= " * CSS Dinâmico do Tema: {$theme->name}\n";
        $css .= " * Gerado automaticamente - NÃO EDITE MANUALMENTE\n";
        $css .= " * Última atualização: " . $theme->updated_at->format('d/m/Y H:i:s') . "\n";
        $css .= " */\n\n";

        $css .= ":root {\n";

        // Cores da marca
        $css .= "  /* Cores da Marca */\n";
        $css .= "  --color-primary: {$colors['brand']['primary']};\n";
        $css .= "  --color-secondary: {$colors['brand']['secondary']};\n";
        $css .= "  --color-accent: {$colors['brand']['accent']};\n";
        $css .= "  --color-brand-light: {$colors['brand']['light']};\n\n";

        // Cores de texto
        $css .= "  /* Cores de Texto */\n";
        $css .= "  --color-text-primary: {$colors['text']['primary']};\n";
        $css .= "  --color-text-secondary: {$colors['text']['secondary']};\n";
        $css .= "  --color-text-muted: {$colors['text']['muted']};\n";
        $css .= "  --color-text-white: {$colors['text']['white']};\n\n";

        // Cores de fundo
        $css .= "  /* Cores de Fundo */\n";
        $css .= "  --color-bg-main: {$colors['background']['main']};\n";
        $css .= "  --color-bg-light: {$colors['background']['light']};\n";
        $css .= "  --color-bg-gray: {$colors['background']['gray']};\n\n";

        // Cores de botões
        $css .= "  /* Botões */\n";
        $css .= "  --color-btn-primary-bg: {$colors['button']['primary_bg']};\n";
        $css .= "  --color-btn-primary-text: {$colors['button']['primary_text']};\n";
        $css .= "  --color-btn-primary-hover: {$colors['button']['primary_hover']};\n";
        $css .= "  --color-btn-secondary-bg: {$colors['button']['secondary_bg']};\n";
        $css .= "  --color-btn-secondary-text: {$colors['button']['secondary_text']};\n";
        $css .= "  --color-btn-secondary-hover: {$colors['button']['secondary_hover']};\n\n";

        // Cores do botão Comprar (se existir)
        if (isset($colors['buy_button'])) {
            $css .= "  /* Botão Comprar (Produtos) */\n";
            $css .= "  --color-buy-btn-bg: {$colors['buy_button']['bg']};\n";
            $css .= "  --color-buy-btn-text: {$colors['buy_button']['text']};\n";
            $css .= "  --color-buy-btn-hover-bg: {$colors['buy_button']['hover_bg']};\n";
            $css .= "  --color-buy-btn-hover-text: {$colors['buy_button']['hover_text']};\n\n";
        }

        // Cores de links
        $css .= "  /* Links */\n";
        $css .= "  --color-link: {$colors['link']['default']};\n";
        $css .= "  --color-link-hover: {$colors['link']['hover']};\n\n";

        // Cores de bordas
        if (isset($colors['border'])) {
            $css .= "  /* Bordas */\n";
            $css .= "  --color-border-light: {$colors['border']['light']};\n";
            $css .= "  --color-border-medium: {$colors['border']['medium']};\n";
            $css .= "  --color-border-dark: {$colors['border']['dark']};\n\n";
        }

        // Cores de status
        $css .= "  /* Status/Feedback */\n";
        $css .= "  --color-success: {$colors['status']['success']};\n";
        $css .= "  --color-error: {$colors['status']['error']};\n";
        $css .= "  --color-warning: {$colors['status']['warning']};\n";
        $css .= "  --color-info: {$colors['status']['info']};\n\n";

        // Componentes
        if (isset($colors['components'])) {
            $css .= "  /* Componentes */\n";
            $css .= "  --color-input-border: {$colors['components']['input_border']};\n";
            $css .= "  --color-input-focus: {$colors['components']['input_focus']};\n";
            $css .= "  --color-table-header: {$colors['components']['table_header']};\n";
            $css .= "  --color-carousel-dots: {$colors['components']['carousel_dots']};\n\n";
        }

        $css .= "}\n";

        return $css;
    }

    /**
     * Retorna CSS padrão caso não haja tema ativo
     *
     * @return string
     */
    private function getDefaultCSS(): string
    {
        return ":root {\n  /* Tema não configurado */\n  --color-primary: #000000;\n}\n";
    }

    /**
     * Exibe formulário de upload de logo
     *
     * @return \Illuminate\View\View
     */
    public function logo()
    {
        $theme = ThemeSetting::active()->firstOrFail();

        return view('admin.layout.logo', compact('theme'));
    }

    /**
     * Faz upload e salva a nova logo do tema
     *
     * Valida dimensões (360x82px recomendado) e formato da imagem.
     * A logo é salva em storage/app/public/logos e acessível via link simbólico.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateLogo(Request $request)
    {
        $theme = ThemeSetting::active()->firstOrFail();

        // Validação do upload
        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg,svg|max:2048', // Máx 2MB
            'logo_alt' => 'nullable|string|max:255',
        ], [
            'logo.required' => 'Por favor, selecione uma imagem.',
            'logo.image' => 'O arquivo deve ser uma imagem.',
            'logo.mimes' => 'A logo deve ser PNG, JPG, JPEG ou SVG.',
            'logo.max' => 'A logo não pode ter mais de 2MB.',
        ]);

        // Valida altura mínima de 120px
        if ($request->hasFile('logo')) {
            $image = getimagesize($request->file('logo')->path());
            $width = $image[0];
            $height = $image[1];

            // Bloqueia se altura for menor que 120px
            if ($height < 120) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors([
                        'logo' => "A logo deve ter pelo menos 120px de altura. A imagem enviada tem {$width}x{$height}px."
                    ]);
            }

            // Alerta se altura for maior que 150px (mas permite)
            if ($height > 150) {
                // Permite, mas não alerta (funcionará normalmente)
            }
        }

        // Remove logo anterior se existir
        if ($theme->logo_path && \Storage::disk('public')->exists($theme->logo_path)) {
            \Storage::disk('public')->delete($theme->logo_path);
        }

        // Salva nova logo com nome único
        $path = $request->file('logo')->store('logos', 'public');

        // Atualiza tema
        $theme->logo_path = $path;
        $theme->logo_alt = $request->input('logo_alt', config('app.name'));
        $theme->save();

        // Invalida cache
        Cache::forget('theme.active');

        return redirect()
            ->route('admin.layout.logo')
            ->with('success', 'Logo atualizada com sucesso!');
    }

    /**
     * Exibe formulário de configuração da Top Bar (Barra de Anúncios)
     *
     * @return \Illuminate\View\View
     */
    public function topBar()
    {
        $theme = ThemeSetting::active()->firstOrFail();

        return view('admin.layout.topbar', compact('theme'));
    }

    /**
     * Atualiza configurações da Top Bar
     *
     * Permite ativar/desativar, definir texto (com HTML), cores de fundo e texto.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateTopBar(Request $request)
    {
        $theme = ThemeSetting::active()->firstOrFail();

        // Validação
        $request->validate([
            'top_bar_enabled' => 'required|boolean',
            'top_bar_text' => 'nullable|string|max:500',
            'top_bar_bg_color' => 'required|string|max:50',
            'top_bar_text_color' => 'required|string|max:50',
        ], [
            'top_bar_text.max' => 'O texto não pode ter mais de 500 caracteres.',
        ]);

        // Atualiza configurações
        $theme->top_bar_enabled = $request->input('top_bar_enabled', false);
        $theme->top_bar_text = $request->input('top_bar_text');
        $theme->top_bar_bg_color = $request->input('top_bar_bg_color', '#013E3B');
        $theme->top_bar_text_color = $request->input('top_bar_text_color', '#FFFFFF');
        $theme->save();

        // Invalida cache
        Cache::forget('theme.active');

        return redirect()
            ->route('admin.layout.topbar')
            ->with('success', 'Barra de Anúncios atualizada com sucesso!');
    }
}
