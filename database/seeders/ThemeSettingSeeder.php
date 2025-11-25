<?php

namespace Database\Seeders;

use App\Models\ThemeSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Seeder para criar tema padrão baseado no design da Naturallis
 *
 * Cores extraídas do CSS original do site Naturallis.
 * As cores estão organizadas por categoria para facilitar a manutenção.
 */
class ThemeSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ThemeSetting::create([
            'name' => 'Naturallis Original',
            'is_active' => true,
            'colors' => [
                // Cores da marca (identidade visual)
                'brand' => [
                    'primary' => '#013E3B',      // Verde escuro principal (mais usado: 139 ocorrências)
                    'secondary' => '#FFA733',    // Laranja/amarelo (destaque: 130 ocorrências)
                    'accent' => '#4CAF00',       // Verde claro para sucesso
                    'light' => '#A5EFC6',        // Verde água (destaque sutil)
                ],

                // Cores de texto
                'text' => [
                    'primary' => '#443E3F',      // Cinza escuro (texto principal: 82 ocorrências)
                    'secondary' => '#4D4849',    // Cinza médio (texto secundário: 63 ocorrências)
                    'muted' => '#566841',        // Verde acinzentado (texto desativado)
                    'white' => '#FFFFFF',        // Branco (sobre fundos escuros)
                ],

                // Cores de fundo
                'background' => [
                    'main' => '#FFFFFF',         // Branco (fundo principal: 184 ocorrências)
                    'light' => '#F8FCF5',        // Verde muito claro (seções alternadas: 22 ocorrências)
                    'gray' => '#e4e4e4',         // Cinza claro (bordas e divisórias: 47 ocorrências)
                ],

                // Cores de botões
                'button' => [
                    'primary_bg' => '#FFA733',   // Fundo do botão primário (laranja)
                    'primary_text' => '#FFFFFF', // Texto do botão primário
                    'primary_hover' => '#013E3B', // Hover do botão primário (verde escuro)
                    'secondary_bg' => '#013E3B', // Fundo do botão secundário (verde)
                    'secondary_text' => '#FFFFFF', // Texto do botão secundário
                    'secondary_hover' => '#FFA733', // Hover do botão secundário (laranja)
                ],

                // Cores do botão Comprar (produtos)
                // Usado na classe .box-adicionar span (botão de adicionar produto ao carrinho)
                'buy_button' => [
                    'bg' => '#FFA733',           // Fundo do botão Comprar (laranja original)
                    'text' => '#FFFFFF',         // Texto do botão Comprar
                    'hover_bg' => '#013E3B',     // Fundo ao passar mouse (verde escuro)
                    'hover_text' => '#FFFFFF',   // Texto ao passar mouse
                ],

                // Cores de links
                'link' => [
                    'default' => '#013E3B',      // Cor padrão de links
                    'hover' => '#FFA733',        // Cor ao passar o mouse
                ],

                // Cores de bordas
                'border' => [
                    'light' => '#e4e4e4',        // Borda clara (divisórias sutis)
                    'medium' => '#ccc',          // Borda média
                    'dark' => '#443E3F',         // Borda escura
                ],

                // Cores de status/feedback
                'status' => [
                    'success' => '#4CAF00',      // Verde para sucesso (11 ocorrências)
                    'error' => '#e74c3c',        // Vermelho para erro (5 ocorrências)
                    'warning' => '#fc9801',      // Laranja para aviso
                    'info' => '#39579b',         // Azul para informação (redes sociais)
                ],

                // Cores de overlay/sombra (transparências)
                'overlay' => [
                    'dark_10' => 'rgba(0,0,0,0.1)',   // Sombra leve (13 ocorrências)
                    'dark_35' => 'rgba(0,0,0,0.35)',  // Sombra média (5 ocorrências)
                    'dark_70' => 'rgba(0,0,0,0.7)',   // Sombra forte (2 ocorrências)
                    'white_80' => 'rgba(255,255,255,0.8)', // Overlay branco (2 ocorrências)
                ],

                // Cores específicas de componentes
                'components' => [
                    'carousel_dots' => '#013E3B',     // Pontos do carousel
                    'carousel_dots_active' => '#FFF', // Ponto ativo do carousel
                    'input_border' => '#e4e4e4',      // Borda de inputs
                    'input_focus' => '#FFA733',       // Borda de input focado
                    'table_header' => '#013E3B',      // Cabeçalho de tabelas
                    'table_row' => '#FFFFFF',         // Linha de tabela
                ],
            ],

            // Fontes (para implementação futura)
            'fonts' => [
                'primary' => 'Rubik, sans-serif',     // Fonte principal do site
                'secondary' => 'Comfortaa, cursive',  // Fonte secundária (títulos)
            ],

            // Layout (para implementação futura)
            'layout' => [
                'border_radius' => '30px',  // Raio de borda padrão dos botões
                'container_width' => '1170px', // Largura máxima do container
            ],
        ]);
    }
}
