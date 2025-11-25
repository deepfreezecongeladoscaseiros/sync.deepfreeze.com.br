<?php

namespace Database\Seeders;

use App\Models\FeatureBlock;
use Illuminate\Database\Seeder;

/**
 * Seeder para criar os 4 blocos de features/informações iniciais
 *
 * Dados baseados no layout original da Deep Freeze
 */
class FeatureBlockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpa blocos existentes
        FeatureBlock::truncate();

        // Bloco 1: Frete Expresso
        FeatureBlock::create([
            'order' => 1,
            'icon_path' => 'feature-blocks/icon-1.svg', // Caminho do ícone no storage (substitua pelo ícone real via admin)
            'title' => 'frete expresso',
            'description' => 'Entrega mais rápida de SP!',
            'bg_color' => '#D4F4DD',
            'text_color' => '#013E3B',
            'icon_color' => '#013E3B',
            'active' => true,
        ]);

        // Bloco 2: Entrega Expressa
        FeatureBlock::create([
            'order' => 2,
            'icon_path' => 'feature-blocks/icon-2.svg', // Caminho do ícone no storage (substitua pelo ícone real via admin)
            'title' => 'entrega expressa',
            'description' => 'receba no mesmo dia!',
            'bg_color' => '#D4F4DD',
            'text_color' => '#013E3B',
            'icon_color' => '#013E3B',
            'active' => true,
        ]);

        // Bloco 3: Praticidade
        FeatureBlock::create([
            'order' => 3,
            'icon_path' => 'feature-blocks/icon-3.svg', // Caminho do ícone no storage (substitua pelo ícone real via admin)
            'title' => 'praticidade',
            'description' => 'esquente no micro-ondas, forno ou air fryer',
            'bg_color' => '#D4F4DD',
            'text_color' => '#013E3B',
            'icon_color' => '#013E3B',
            'active' => true,
        ]);

        // Bloco 4: Pagamento
        FeatureBlock::create([
            'order' => 4,
            'icon_path' => 'feature-blocks/icon-4.svg', // Caminho do ícone no storage (substitua pelo ícone real via admin)
            'title' => 'pagamento',
            'description' => 'aceitamos pagamentos em VR e VA',
            'bg_color' => '#D4F4DD',
            'text_color' => '#013E3B',
            'icon_color' => '#013E3B',
            'active' => true,
        ]);
    }
}
