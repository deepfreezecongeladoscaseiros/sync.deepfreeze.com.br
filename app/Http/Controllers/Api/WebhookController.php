<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessProductWebhook;
use App\Jobs\ProcessStockWebhook;
use App\Jobs\ProcessPriceWebhook;
use App\Jobs\ProcessCategoryWebhook;
use App\Jobs\ProcessBrandWebhook;
use App\Jobs\ProcessManufacturerWebhook;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Manufacturer;
use App\Models\ProductWebhookLog;
use App\Models\ProductStockWebhookLog;
use App\Models\ProductPriceWebhookLog;
use App\Models\CategoryWebhookLog;
use App\Models\BrandWebhookLog;
use App\Models\ManufacturerWebhookLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WebhookController extends Controller
{
    public function productUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'products' => 'required|array',
            'products.*.legacy_id' => 'required|integer',
            'products.*.codigo' => 'nullable|string|max:200',
            'products.*.descricao' => 'required|string|max:200',
            'products.*.categoria_id' => 'nullable|integer',
            'products.*.marca_id' => 'nullable|integer',
            'products.*.fabricante_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        $results = [];
        $integration_id = $request->integration_id;
        $ip_address = $request->ip();

        foreach ($request->products as $productData) {
            $legacy_id = $productData['legacy_id'];
            
            $log = ProductWebhookLog::create([
                'integration_id' => $integration_id,
                'legacy_id' => $legacy_id,
                'event_type' => 'update',
                'payload' => $productData,
                'headers' => $request->headers->all(),
                'status' => 'pending',
                'ip_address' => $ip_address,
            ]);

            ProcessProductWebhook::dispatch($productData, $log->id);

            $results[] = [
                'legacy_id' => $legacy_id,
                'log_id' => $log->id,
                'status' => 'queued',
            ];
        }

        return response()->json([
            'message' => 'Products queued for processing',
            'results' => $results
        ], 202);
    }

    public function stockUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'products' => 'required|array',
            'products.*.legacy_id' => 'required|integer',
            'products.*.stock' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        $results = [];
        $integration_id = $request->integration_id;
        $ip_address = $request->ip();

        foreach ($request->products as $productData) {
            $legacy_id = $productData['legacy_id'];
            
            $product = Product::where('legacy_id', $legacy_id)->first();

            if (!$product) {
                $results[] = [
                    'legacy_id' => $legacy_id,
                    'status' => 'failed',
                    'error' => 'Product not found. Use product-update webhook to create it first.',
                ];
                continue;
            }

            $log = ProductStockWebhookLog::create([
                'product_id' => $product->id,
                'integration_id' => $integration_id,
                'legacy_id' => $legacy_id,
                'old_stock' => $product->stock,
                'new_stock' => $productData['stock'],
                'payload' => $productData,
                'headers' => $request->headers->all(),
                'status' => 'pending',
                'ip_address' => $ip_address,
            ]);

            ProcessStockWebhook::dispatch($productData, $log->id);

            $results[] = [
                'legacy_id' => $legacy_id,
                'log_id' => $log->id,
                'status' => 'queued',
            ];
        }

        return response()->json([
            'message' => 'Stock updates queued for processing',
            'results' => $results
        ], 202);
    }

    public function priceUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'products' => 'required|array',
            'products.*.legacy_id' => 'required|integer',
            'products.*.preco' => 'required|numeric|min:0',
            'products.*.preco_promocional' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        $results = [];
        $integration_id = $request->integration_id;
        $ip_address = $request->ip();

        foreach ($request->products as $productData) {
            $legacy_id = $productData['legacy_id'];
            
            $product = Product::where('legacy_id', $legacy_id)->first();

            if (!$product) {
                $results[] = [
                    'legacy_id' => $legacy_id,
                    'status' => 'failed',
                    'error' => 'Product not found. Use product-update webhook to create it first.',
                ];
                continue;
            }

            $log = ProductPriceWebhookLog::create([
                'product_id' => $product->id,
                'integration_id' => $integration_id,
                'legacy_id' => $legacy_id,
                'old_price' => $product->price,
                'new_price' => $productData['preco'],
                'old_promotional_price' => $product->promotional_price,
                'new_promotional_price' => $productData['preco_promocional'] ?? null,
                'payload' => $productData,
                'headers' => $request->headers->all(),
                'status' => 'pending',
                'ip_address' => $ip_address,
            ]);

            ProcessPriceWebhook::dispatch($productData, $log->id);

            $results[] = [
                'legacy_id' => $legacy_id,
                'log_id' => $log->id,
                'status' => 'queued',
            ];
        }

        return response()->json([
            'message' => 'Price updates queued for processing',
            'results' => $results
        ], 202);
    }

    public function categoryUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'categories' => 'required|array',
            'categories.*.legacy_id' => 'required|integer',
            'categories.*.name' => 'required|string|max:200',
            'categories.*.slug' => 'nullable|string|max:200',
            'categories.*.description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        $results = [];
        $integration_id = $request->integration_id;
        $ip_address = $request->ip();

        foreach ($request->categories as $categoryData) {
            $legacy_id = $categoryData['legacy_id'];
            
            $log = CategoryWebhookLog::create([
                'integration_id' => $integration_id,
                'legacy_id' => $legacy_id,
                'event_type' => 'update',
                'payload' => $categoryData,
                'headers' => $request->headers->all(),
                'status' => 'pending',
                'ip_address' => $ip_address,
            ]);

            ProcessCategoryWebhook::dispatch($categoryData, $log->id);

            $results[] = [
                'legacy_id' => $legacy_id,
                'log_id' => $log->id,
                'status' => 'queued',
            ];
        }

        return response()->json([
            'message' => 'Categories queued for processing',
            'results' => $results
        ], 202);
    }

    public function brandUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'brands' => 'required|array',
            'brands.*.legacy_id' => 'required|integer',
            'brands.*.brand' => 'required|string|max:200',
            'brands.*.slug' => 'nullable|string|max:200',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        $results = [];
        $integration_id = $request->integration_id;
        $ip_address = $request->ip();

        foreach ($request->brands as $brandData) {
            $legacy_id = $brandData['legacy_id'];
            
            $log = BrandWebhookLog::create([
                'integration_id' => $integration_id,
                'legacy_id' => $legacy_id,
                'event_type' => 'update',
                'payload' => $brandData,
                'headers' => $request->headers->all(),
                'status' => 'pending',
                'ip_address' => $ip_address,
            ]);

            ProcessBrandWebhook::dispatch($brandData, $log->id);

            $results[] = [
                'legacy_id' => $legacy_id,
                'log_id' => $log->id,
                'status' => 'queued',
            ];
        }

        return response()->json([
            'message' => 'Brands queued for processing',
            'results' => $results
        ], 202);
    }

    public function manufacturerUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'manufacturers' => 'required|array',
            'manufacturers.*.legacy_id' => 'required|integer',
            'manufacturers.*.trade_name' => 'required|string|max:200',
            'manufacturers.*.legal_name' => 'nullable|string|max:200',
            'manufacturers.*.cnpj' => 'nullable|string|max:18',
            'manufacturers.*.address' => 'nullable|string',
            'manufacturers.*.city' => 'nullable|string|max:100',
            'manufacturers.*.state' => 'nullable|string|max:2',
            'manufacturers.*.zip_code' => 'nullable|string|max:10',
            'manufacturers.*.phone' => 'nullable|string|max:20',
            'manufacturers.*.email' => 'nullable|email|max:100',
            'manufacturers.*.active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        $results = [];
        $integration_id = $request->integration_id;
        $ip_address = $request->ip();

        foreach ($request->manufacturers as $manufacturerData) {
            $legacy_id = $manufacturerData['legacy_id'];
            
            $log = ManufacturerWebhookLog::create([
                'integration_id' => $integration_id,
                'legacy_id' => $legacy_id,
                'event_type' => 'update',
                'payload' => $manufacturerData,
                'headers' => $request->headers->all(),
                'status' => 'pending',
                'ip_address' => $ip_address,
            ]);

            ProcessManufacturerWebhook::dispatch($manufacturerData, $log->id);

            $results[] = [
                'legacy_id' => $legacy_id,
                'log_id' => $log->id,
                'status' => 'queued',
            ];
        }

        return response()->json([
            'message' => 'Manufacturers queued for processing',
            'results' => $results
        ], 202);
    }

}
