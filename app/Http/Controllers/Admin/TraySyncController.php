<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\TrayCommerceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TraySyncController extends Controller
{
    public function syncCategories(TrayCommerceService $trayCommerceService)
    {
        $categoriesToSync = Category::whereNull('tray_id')->get();
        $syncedCount = 0;

        foreach ($categoriesToSync as $category) {
            try {
                $trayCommerceService->sendCategory($category);
                $syncedCount++;
            } catch (\Exception $e) {
                Log::error('Failed to sync category to Tray.', ['category_id' => $category->id, 'error' => $e->getMessage()]);
            }
        }

        return redirect()->route('admin.sync.index')
            ->with('success', "{$syncedCount} categories synced to Tray successfully.");
    }

    public function syncBrands(TrayCommerceService $trayCommerceService)
    {
        $brandsToSync = Brand::whereNull('tray_id')->get();
        $syncedCount = 0;

        foreach ($brandsToSync as $brand) {
            try {
                $trayCommerceService->sendBrand($brand);
                $syncedCount++;
            } catch (\Exception $e) {
                Log::error('Failed to sync brand to Tray.', ['brand_id' => $brand->id, 'error' => $e->getMessage()]);
            }
        }

        return redirect()->route('admin.sync.index')
            ->with('success', "{$syncedCount} brands synced to Tray successfully.");
    }

    public function syncProducts(TrayCommerceService $trayCommerceService)
    {
        $productsToSync = Product::whereNull('tray_id')->get();
        $syncedCount = 0;

        foreach ($productsToSync as $product) {
            try {
                $trayCommerceService->sendProduct($product);
                $syncedCount++;
            } catch (\Exception $e) {
                Log::error('Failed to sync product to Tray.', ['product_id' => $product->id, 'error' => $e->getMessage()]);
            }
        }

        return redirect()->route('admin.sync.index')
            ->with('success', "{$syncedCount} products synced to Tray successfully.");
    }
}
