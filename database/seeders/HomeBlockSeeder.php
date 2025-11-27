<?php

namespace Database\Seeders;

use App\Models\HomeBlock;
use App\Models\ProductGallery;
use App\Models\DualBanner;
use App\Models\InfoBlock;
use App\Models\SingleBanner;
use Illuminate\Database\Seeder;

/**
 * Seeder para popular os blocos padrão da home page
 *
 * Cria uma configuração inicial com os blocos na ordem:
 * 1. Banner Hero
 * 2. Blocos de Informações (Feature Blocks)
 * 3. Galerias de Produtos (cada uma individualmente)
 * 4. Banners Duplos (cada um individualmente)
 * 5. Blocos de Informação (Info Blocks)
 * 6. Blocos de Passos (Step Blocks)
 * 7. Banners Únicos (Single Banners)
 *
 * Este seeder pode ser executado múltiplas vezes sem duplicar,
 * pois usa truncate para limpar a tabela antes.
 */
class HomeBlockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpa a tabela para evitar duplicatas
        // ATENÇÃO: Isso apaga toda configuração existente!
        // Comente esta linha se quiser preservar blocos existentes
        HomeBlock::truncate();

        $order = 0;

        // 1. Banner Hero (exibe todos os banners hero)
        HomeBlock::create([
            'type' => 'hero_banners',
            'reference_id' => null,
            'custom_title' => null,
            'order' => $order++,
            'is_active' => true,
        ]);

        // 2. Blocos de Informações/Features (exibe todos os 4 blocos)
        HomeBlock::create([
            'type' => 'feature_blocks',
            'reference_id' => null,
            'custom_title' => null,
            'order' => $order++,
            'is_active' => true,
        ]);

        // 3. Galerias de Produtos (uma entrada para cada galeria ativa)
        $galleries = ProductGallery::where('active', true)->orderBy('order')->get();
        foreach ($galleries as $gallery) {
            HomeBlock::create([
                'type' => 'product_gallery',
                'reference_id' => $gallery->id,
                'custom_title' => null, // Usa o título da galeria
                'order' => $order++,
                'is_active' => true,
            ]);
        }

        // 4. Banners Duplos (uma entrada para cada banner duplo ativo)
        $dualBanners = DualBanner::where('active', true)->orderBy('order')->get();
        foreach ($dualBanners as $dualBanner) {
            HomeBlock::create([
                'type' => 'dual_banner',
                'reference_id' => $dualBanner->id,
                'custom_title' => null,
                'order' => $order++,
                'is_active' => true,
            ]);
        }

        // 5. Blocos de Informação (uma entrada para cada bloco ativo)
        $infoBlocks = InfoBlock::where('active', true)->orderBy('order')->get();
        foreach ($infoBlocks as $infoBlock) {
            HomeBlock::create([
                'type' => 'info_block',
                'reference_id' => $infoBlock->id,
                'custom_title' => null,
                'order' => $order++,
                'is_active' => true,
            ]);
        }

        // 6. Blocos de Passos (exibe todos os 4 passos)
        HomeBlock::create([
            'type' => 'step_blocks',
            'reference_id' => null,
            'custom_title' => null,
            'order' => $order++,
            'is_active' => true,
        ]);

        // 7. Banners Únicos (uma entrada para cada banner único ativo)
        $singleBanners = SingleBanner::where('active', true)->orderBy('order')->get();
        foreach ($singleBanners as $singleBanner) {
            HomeBlock::create([
                'type' => 'single_banner',
                'reference_id' => $singleBanner->id,
                'custom_title' => null,
                'order' => $order++,
                'is_active' => true,
            ]);
        }

        $this->command->info("Home blocks seeded successfully! Total: {$order} blocos criados.");
    }
}
