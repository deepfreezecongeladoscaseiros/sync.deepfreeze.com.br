<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Manufacturer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class SyncController extends Controller
{
    public function index()
    {
        return view('admin.sync.index');
    }

    public function syncCategories()
    {
        $legacyCategories = DB::connection('mysql_legacy')->table('categorias')->get();
        $createdCount = 0;
        $updatedCount = 0;

        foreach ($legacyCategories as $legacyCategory) {
            $category = Category::updateOrCreate(
                ['legacy_id' => $legacyCategory->id],
                [
                    'name' => $legacyCategory->nome,
                    'slug' => $legacyCategory->slug ?? Str::slug($legacyCategory->nome),
                    'description' => $legacyCategory->descricao,
                    'created_at' => $legacyCategory->created,
                    'updated_at' => $legacyCategory->modified,
                ]
            );

            if ($category->wasRecentlyCreated) {
                $createdCount++;
            } elseif ($category->wasChanged()) {
                $updatedCount++;
            }
        }

        return redirect()->route('admin.sync.index')
            ->with('success', "Categories synced successfully. Created: {$createdCount}, Updated: {$updatedCount}.");
    }

    public function syncBrands()
    {
        $legacyBrands = DB::connection('mysql_legacy')->table('marcas')->get();
        $createdCount = 0;
        $updatedCount = 0;

        foreach ($legacyBrands as $legacyBrand) {
            $slug = Str::slug($legacyBrand->nome_marca);

            $existingBrand = Brand::where('slug', $slug)->first();

            if (!$existingBrand) {
                Brand::create([
                    'legacy_id' => $legacyBrand->id,
                    'brand' => $legacyBrand->nome_marca,
                    'slug' => $slug,
                    'created_at' => $legacyBrand->created,
                    'updated_at' => $legacyBrand->updated,
                ]);
                $createdCount++;
            }
        }

        return redirect()->route('admin.sync.index')
            ->with('success', "Brands synced successfully. Created: {$createdCount}, Updated: {$updatedCount}.");
    }

    public function syncManufacturers()
    {
        $legacyManufacturers = DB::connection('mysql_legacy')->table('fabricantes')->get();
        $createdCount = 0;
        $updatedCount = 0;

        foreach ($legacyManufacturers as $legacyManufacturer) {
            $manufacturer = Manufacturer::updateOrCreate(
                ['legacy_id' => $legacyManufacturer->id],
                [
                    'trade_name' => $legacyManufacturer->nome_fantasia ?? $legacyManufacturer->razao_social,
                    'legal_name' => $legacyManufacturer->razao_social,
                    'cnpj' => $legacyManufacturer->cnpj,
                    'address' => $legacyManufacturer->endereco ?? null,
                    'city' => $legacyManufacturer->cidade ?? null,
                    'state' => $legacyManufacturer->estado ?? null,
                    'zip_code' => $legacyManufacturer->cep ?? null,
                    'phone' => $legacyManufacturer->telefone ?? null,
                    'email' => $legacyManufacturer->email ?? null,
                    'active' => true,
                    'created_at' => $legacyManufacturer->created ?? now(),
                    'updated_at' => $legacyManufacturer->updated ?? now(),
                ]
            );

            if ($manufacturer->wasRecentlyCreated) {
                $createdCount++;
            } elseif ($manufacturer->wasChanged()) {
                $updatedCount++;
            }
        }

        return redirect()->route('admin.sync.index')
            ->with('success', "Manufacturers synced successfully. Created: {$createdCount}, Updated: {$updatedCount}.");
    }

    public function syncProducts()
    {
        $legacyProducts = DB::connection('mysql_legacy')
            ->table('produtos')
            ->where('ativo', 1)
            ->get();
        
        $createdCount = 0;
        $updatedCount = 0;

        $localCategories = Category::all()->keyBy('legacy_id');
        $localBrands = Brand::all()->keyBy('legacy_id');
        $localManufacturers = Manufacturer::all()->keyBy('legacy_id');

        foreach ($legacyProducts as $legacyProduct) {
            if (!isset($localCategories[$legacyProduct->categoria_id])) {
                Log::warning('Product sync skipped: Category not found.', ['legacy_product_id' => $legacyProduct->id, 'legacy_category_id' => $legacyProduct->categoria_id]);
                continue;
            }
            $categoryId = $localCategories[$legacyProduct->categoria_id]->id;

            $brandId = null;
            if (isset($legacyProduct->marca_id) && isset($localBrands[$legacyProduct->marca_id])) {
                $brandId = $localBrands[$legacyProduct->marca_id]->id;
            } elseif (isset($legacyProduct->marca_id)) {
                Log::warning('Product sync warning: Brand not found.', ['legacy_product_id' => $legacyProduct->id, 'legacy_brand_id' => $legacyProduct->marca_id]);
            }

            $manufacturerId = null;
            if (isset($legacyProduct->fabricante_id) && isset($localManufacturers[$legacyProduct->fabricante_id])) {
                $manufacturerId = $localManufacturers[$legacyProduct->fabricante_id]->id;
            }

            $description = '';
            $fieldsToConcat = [
                'Apresentação' => 'apresentacao',
                'Propriedades' => 'propriedades',
                'Benefícios' => 'beneficios',
                'Dicas do Chef' => 'dica_do_chef',
                'História do Prato' => 'historia_do_prato',
                'Ingredientes' => 'ingredientes',
                'Instruções para Consumo' => 'instrucoes_para_consumo',
            ];

            foreach ($fieldsToConcat as $title => $field) {
                if (!empty($legacyProduct->$field)) {
                    $description .= "<h3>{$title}</h3><p>" . nl2br(e($legacyProduct->$field)) . "</p>";
                }
            }

            $product = Product::updateOrCreate(
                ['legacy_id' => $legacyProduct->id],
                [
                    'sku' => $legacyProduct->codigo,
                    'name' => $legacyProduct->descricao,
                    'description' => $description,
                    
                    'presentation' => $legacyProduct->apresentacao,
                    'properties' => $legacyProduct->propriedades,
                    'benefits' => $legacyProduct->beneficios,
                    'chef_tips' => $legacyProduct->dica_do_chef,
                    'dish_history' => $legacyProduct->historia_do_prato,
                    'ingredients' => $legacyProduct->ingredientes,
                    'consumption_instructions' => $legacyProduct->instrucoes_para_consumo,
                    
                    'price' => $legacyProduct->preco,
                    'promotional_price' => empty($legacyProduct->preco_promocional) ? null : $legacyProduct->preco_promocional,
                    'ean' => empty($legacyProduct->codigo_de_barras) ? null : $legacyProduct->codigo_de_barras,
                    'ncm' => $legacyProduct->ncm,
                    'category_id' => $categoryId,
                    'brand_id' => $brandId,
                    'manufacturer_id' => $manufacturerId,
                    
                    'weight' => $this->convertWeightToKg($legacyProduct->peso_liquido, $legacyProduct->unidade_medida_peso),
                    'gross_weight' => $this->convertWeightToKg($legacyProduct->peso_bruto, $legacyProduct->unidade_medida_peso),
                    'weight_unit' => $legacyProduct->unidade_medida_peso ?? 'g',
                    
                    'shelf_life_days' => $legacyProduct->validade_dias,
                    'portion_size' => $legacyProduct->porcao ?? 100,
                    'home_measure' => $legacyProduct->medida_caseira,
                    
                    'contains_gluten' => $legacyProduct->in_contem_gluten ?? false,
                    'lactose_free' => $legacyProduct->in_sem_lactose ?? false,
                    'low_lactose' => $legacyProduct->in_baixo_lactose ?? false,
                    'contains_lactose' => $legacyProduct->in_contem_lactose ?? false,
                    'allergens' => $legacyProduct->alergenicos_manual,
                    
                    'alcoholic_beverage' => $legacyProduct->bebida_alcoolica ?? false,
                    'label_description' => $legacyProduct->descricao_etiqueta,
                    'description_english' => $legacyProduct->descricao_ingles,
                    'freezing_time' => $legacyProduct->tempo_congelamento,
                    
                    'active' => $legacyProduct->ativo ?? true,
                    'is_package' => !empty($legacyProduct->pacote),
                    'is_combo' => $legacyProduct->combo ?? false,
                    'is_gift_card' => $legacyProduct->gift_card ?? false,
                    'made_to_order' => $legacyProduct->produzido_por_encomenda ?? false,
                    'order_deadline' => $legacyProduct->data_final_encomenda,
                    
                    'background_color' => $legacyProduct->cor_fundo ?? '#F0F0F0',
                    'text_color' => $legacyProduct->cor_texto ?? '#000000',
                    'display_order' => $legacyProduct->ordem_exibicao_site ?? 0,
                    
                    'ifood_percentage' => $legacyProduct->percentualifood,
                    'ifood_promotion_percentage' => $legacyProduct->percentualpromocaoifood,
                    
                    'created_at' => $legacyProduct->created,
                    'updated_at' => $legacyProduct->updated,
                ]
            );

            if ($product->wasRecentlyCreated) {
                $createdCount++;
            } elseif ($product->wasChanged()) {
                $updatedCount++;
            }
        }

        return redirect()->route('admin.sync.index')
            ->with('success', "Products synced successfully. Created: {$createdCount}, Updated: {$updatedCount}.");
    }

    public function testLegacyConnection()
    {
        try {
            DB::connection('mysql_legacy')->getPdo();
            $dbName = DB::connection('mysql_legacy')->getDatabaseName();
            return "Successfully connected to the legacy database: " . $dbName;
        } catch (\Exception $e) {
            return "Could not connect to the legacy database. Please check your .env file. Error: " . $e->getMessage();
        }
    }

    public function syncImages()
    {
        $products = Product::whereNotNull('legacy_id')->get();
        $imageBaseUrl = config('services.legacy.image_base_url');
        $downloadedCount = 0;

        foreach ($products as $product) {
            // Limpa imagens existentes para evitar duplicatas
            $product->images()->delete();

            $imageUrls = [];

            // 1. Buscar no banco de dados legado
            $legacyImages = DB::connection('mysql_legacy')
                ->table('produtos_imagens')
                ->where('produto_id', $product->legacy_id)
                ->where('ativa', 1)
                ->orderBy('posicao', 'asc')
                ->get();

            if ($legacyImages->isNotEmpty()) {
                foreach ($legacyImages as $img) {
                    $imageUrls[] = [
                        'url' => rtrim($imageBaseUrl, '/') . '/img/pratos/big/' . $img->imagem_src,
                        'position' => $img->posicao
                    ];
                }
            }
            // 2. Fallback por convenção de arquivo
            else if ($product->sku) {
                $imageUrl = rtrim($imageBaseUrl, '/') . '/img/pratos/big/' . $product->sku . '.jpg';
                // Verifica se a imagem existe antes de adicionar
                try {
                    $response = Http::head($imageUrl);
                    if ($response->successful()) {
                         $imageUrls[] = ['url' => $imageUrl, 'position' => 1];
                    }
                } catch (\Exception $e) {
                    Log::warning('Could not check legacy image by convention.', ['url' => $imageUrl, 'error' => $e->getMessage()]);
                }
            }

            // Processa e baixa as imagens encontradas
            foreach ($imageUrls as $index => $imageData) {
                try {
                    $contents = Http::get($imageData['url'])->body();
                    $filename = basename($imageData['url']);
                    $path = "products/{$product->id}/{$filename}";
                    Storage::disk('public')->put($path, $contents);

                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => $path,
                        'position' => $imageData['position'],
                        'is_main' => ($index === 0), // A primeira imagem é a principal
                    ]);
                    $downloadedCount++;
                } catch (\Exception $e) {
                    Log::error('Failed to download image.', ['url' => $imageData['url'], 'error' => $e->getMessage()]);
                }
            }
        }

        return redirect()->route('admin.sync.index')
            ->with('success', "Images synced successfully. Downloaded: {$downloadedCount}.");
    }

    protected function convertWeightToKg($peso, $unidade)
    {
        if (empty($peso)) {
            return null;
        }
        
        if ($unidade === 'g') {
            return round($peso / 1000, 3);
        }
        
        return $peso;
    }
}
