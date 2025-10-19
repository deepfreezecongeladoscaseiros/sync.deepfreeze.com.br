<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Manufacturer;
use App\Models\ProductImage;
use App\Models\ProductWebhookLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ProcessProductWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;
    public $backoff = [10, 30, 60];

    protected $productData;
    protected $logId;

    public function __construct(array $productData, int $logId)
    {
        $this->productData = $productData;
        $this->logId = $logId;
    }

    public function handle(): void
    {
        $log = ProductWebhookLog::find($this->logId);

        if (!$log) {
            Log::error('ProductWebhookLog not found', ['log_id' => $this->logId]);
            return;
        }

        try {
            $legacy_id = $this->productData['legacy_id'];
            $product = Product::where('legacy_id', $legacy_id)->first();
            
            if (!$product) {
                $log->update(['event_type' => 'create']);
            }

            $category_id = $this->resolveCategory($this->productData['categoria_id'] ?? null);
            $brand_id = $this->resolveBrand($this->productData['marca_id'] ?? null);
            $manufacturer_id = $this->resolveManufacturer($this->productData['fabricante_id'] ?? null);

            $productPayload = [
                'legacy_id' => $legacy_id,
                'sku' => $this->productData['codigo'] ?? null,
                'name' => $this->productData['descricao'],
                'category_id' => $category_id,
                'brand_id' => $brand_id,
                'manufacturer_id' => $manufacturer_id,
                
                'presentation' => $this->productData['apresentacao'] ?? null,
                'properties' => $this->productData['propriedades'] ?? null,
                'benefits' => $this->productData['beneficios'] ?? null,
                'chef_tips' => $this->productData['dica_do_chef'] ?? null,
                'dish_history' => $this->productData['historia_do_prato'] ?? null,
                'ingredients' => $this->productData['ingredientes'] ?? null,
                'consumption_instructions' => $this->productData['instrucoes_para_consumo'] ?? null,
                
                'price' => $this->productData['preco'] ?? 0,
                'promotional_price' => $this->productData['preco_promocional'] ?? null,
                'stock' => $this->productData['stock'] ?? null,
                
                'weight' => $this->convertWeightToKg($this->productData['peso_liquido'] ?? null, $this->productData['unidade_medida_peso'] ?? 'g'),
                'gross_weight' => $this->convertWeightToKg($this->productData['peso_bruto'] ?? null, $this->productData['unidade_medida_peso'] ?? 'g'),
                'weight_unit' => $this->productData['unidade_medida_peso'] ?? 'g',
                
                'shelf_life_days' => $this->productData['validade_dias'] ?? null,
                'portion_size' => $this->productData['porcao'] ?? 100,
                'home_measure' => $this->productData['medida_caseira'] ?? null,
                
                'contains_gluten' => $this->productData['in_contem_gluten'] ?? false,
                'lactose_free' => $this->productData['in_sem_lactose'] ?? false,
                'low_lactose' => $this->productData['in_baixo_lactose'] ?? false,
                'contains_lactose' => $this->productData['in_contem_lactose'] ?? false,
                'allergens' => $this->productData['alergenicos_manual'] ?? null,
                
                'alcoholic_beverage' => $this->productData['bebida_alcoolica'] ?? false,
                'label_description' => $this->productData['descricao_etiqueta'] ?? null,
                'description_english' => $this->productData['descricao_ingles'] ?? null,
                'freezing_time' => $this->productData['tempo_congelamento'] ?? null,
                
                'active' => $this->productData['ativo'] ?? true,
                'is_package' => !empty($this->productData['pacote']),
                'is_combo' => $this->productData['combo'] ?? false,
                'is_gift_card' => $this->productData['gift_card'] ?? false,
                'made_to_order' => $this->productData['produzido_por_encomenda'] ?? false,
                'order_deadline' => $this->productData['data_final_encomenda'] ?? null,
                
                'background_color' => $this->productData['cor_fundo'] ?? '#F0F0F0',
                'text_color' => $this->productData['cor_texto'] ?? '#000000',
                'display_order' => $this->productData['ordem_exibicao_site'] ?? 0,
                
                'ifood_percentage' => $this->productData['percentualifood'] ?? null,
                'ifood_promotion_percentage' => $this->productData['percentualpromocaoifood'] ?? null,
            ];

            $product = Product::updateOrCreate(
                ['legacy_id' => $legacy_id],
                $productPayload
            );

            if (isset($this->productData['imagens']) && is_array($this->productData['imagens'])) {
                $this->syncImages($product, $this->productData['imagens']);
            }

            $log->update([
                'product_id' => $product->id,
                'status' => 'success',
                'processed_at' => now(),
            ]);

        } catch (\Exception $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'retry_count' => $log->retry_count + 1,
                'processed_at' => now(),
            ]);

            Log::error('ProcessProductWebhook failed', [
                'log_id' => $this->logId,
                'legacy_id' => $this->productData['legacy_id'] ?? null,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $log = ProductWebhookLog::find($this->logId);
        
        if ($log) {
            $log->update([
                'status' => 'failed',
                'error_message' => 'Max retries exceeded: ' . $exception->getMessage(),
                'processed_at' => now(),
            ]);
        }

        Log::error('ProcessProductWebhook permanently failed', [
            'log_id' => $this->logId,
            'error' => $exception->getMessage(),
        ]);
    }

    protected function resolveCategory($legacy_category_id)
    {
        if (!$legacy_category_id) {
            return null;
        }

        $category = Category::where('legacy_id', $legacy_category_id)->first();
        return $category ? $category->id : null;
    }

    protected function resolveBrand($legacy_brand_id)
    {
        if (!$legacy_brand_id) {
            return null;
        }

        $brand = Brand::where('legacy_id', $legacy_brand_id)->first();
        return $brand ? $brand->id : null;
    }

    protected function resolveManufacturer($legacy_manufacturer_id)
    {
        if (!$legacy_manufacturer_id) {
            return null;
        }

        $manufacturer = Manufacturer::where('legacy_id', $legacy_manufacturer_id)->first();
        return $manufacturer ? $manufacturer->id : null;
    }

    protected function convertWeightToKg($peso, $unidade)
    {
        if (empty($peso)) {
            return null;
        }

        if ($unidade === 'g') {
            return $peso / 1000;
        }

        return $peso;
    }

    protected function syncImages(Product $product, array $images)
    {
        try {
            $product->images()->delete();

            Storage::disk('public')->deleteDirectory("products/{$product->id}");

            foreach ($images as $imageData) {
                if (empty($imageData['url'])) {
                    continue;
                }

                try {
                    $response = Http::timeout(30)->get($imageData['url']);
                    
                    if (!$response->successful()) {
                        Log::warning('Failed to download image', [
                            'product_id' => $product->id,
                            'url' => $imageData['url'],
                            'status' => $response->status()
                        ]);
                        continue;
                    }

                    $contents = $response->body();
                    $filename = basename(parse_url($imageData['url'], PHP_URL_PATH));
                    
                    if (empty($filename)) {
                        $filename = 'image_' . uniqid() . '.jpg';
                    }

                    $path = "products/{$product->id}/{$filename}";
                    Storage::disk('public')->put($path, $contents);

                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => $path,
                        'position' => $imageData['posicao'] ?? $imageData['position'] ?? 999,
                        'is_main' => ($imageData['principal'] ?? $imageData['is_main'] ?? false) == 1,
                    ]);

                    Log::info('Image downloaded successfully', [
                        'product_id' => $product->id,
                        'url' => $imageData['url'],
                        'path' => $path
                    ]);

                } catch (\Exception $e) {
                    Log::error('Failed to download individual image', [
                        'product_id' => $product->id,
                        'url' => $imageData['url'],
                        'error' => $e->getMessage()
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error('Failed to sync images for product', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
