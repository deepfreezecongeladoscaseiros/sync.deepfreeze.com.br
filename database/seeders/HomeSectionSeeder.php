<?php

namespace Database\Seeders;

use App\Models\HomeSection;
use Illuminate\Database\Seeder;

/**
 * Seeder para popular as seções padrão da home page
 *
 * Define as 7 seções disponíveis com ordem inicial e informações
 * para o painel administrativo.
 */
class HomeSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seções da home page na ordem padrão
        $sections = [
            [
                'name' => 'Banner Principal (Hero)',
                'slug' => 'hero_banners',
                'helper_function' => 'hero_banners',
                'description' => 'Carrossel de banners no topo da página',
                'icon' => 'bi bi-image',
                'is_active' => true,
                'order' => 0,
                'admin_route' => 'admin.banners.index',
            ],
            [
                'name' => 'Blocos de Informações',
                'slug' => 'feature_blocks',
                'helper_function' => 'feature_blocks',
                'description' => '4 blocos com ícones e texto (frete, entrega, etc.)',
                'icon' => 'bi bi-grid-3x3-gap',
                'is_active' => true,
                'order' => 1,
                'admin_route' => 'admin.feature-blocks.index',
            ],
            [
                'name' => 'Galerias de Produtos',
                'slug' => 'product_galleries',
                'helper_function' => 'product_galleries',
                'description' => 'Carrosseis de produtos por categoria',
                'icon' => 'bi bi-collection',
                'is_active' => true,
                'order' => 2,
                'admin_route' => 'admin.product-galleries.index',
            ],
            [
                'name' => 'Banners Duplos',
                'slug' => 'dual_banners',
                'helper_function' => 'dual_banners',
                'description' => 'Dois banners lado a lado',
                'icon' => 'bi bi-layout-split',
                'is_active' => true,
                'order' => 3,
                'admin_route' => 'admin.dual-banners.index',
            ],
            [
                'name' => 'Blocos de Informação',
                'slug' => 'info_blocks',
                'helper_function' => 'info_blocks',
                'description' => 'Seções com imagem e texto (ex: Refeições Saudáveis)',
                'icon' => 'bi bi-info-circle',
                'is_active' => true,
                'order' => 4,
                'admin_route' => 'admin.info-blocks.index',
            ],
            [
                'name' => 'Blocos de Passos',
                'slug' => 'step_blocks',
                'helper_function' => 'step_blocks',
                'description' => '4 passos com ícone, título e descrição',
                'icon' => 'bi bi-list-ol',
                'is_active' => true,
                'order' => 5,
                'admin_route' => 'admin.step-blocks.index',
            ],
            [
                'name' => 'Banners Únicos',
                'slug' => 'single_banners',
                'helper_function' => 'single_banners',
                'description' => 'Banners em largura total (desktop + mobile)',
                'icon' => 'bi bi-card-image',
                'is_active' => true,
                'order' => 6,
                'admin_route' => 'admin.single-banners.index',
            ],
        ];

        foreach ($sections as $section) {
            // Usa updateOrCreate para permitir re-executar o seeder
            HomeSection::updateOrCreate(
                ['slug' => $section['slug']], // Busca pelo slug
                $section // Dados para criar/atualizar
            );
        }

        $this->command->info('Home sections seeded successfully!');
    }
}
