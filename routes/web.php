<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\IntegrationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/docs', [App\Http\Controllers\ApiDocsController::class, 'index'])->name('docs');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


require __DIR__.'/auth.php';

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::resource('integrations', IntegrationController::class);
    Route::post('categories/{category}/sync-to-tray', [App\Http\Controllers\Admin\CategoryController::class, 'syncToTray'])->name('categories.sync_to_tray');
    Route::resource('categories', App\Http\Controllers\Admin\CategoryController::class);
    Route::post('brands/{brand}/sync-to-tray', [App\Http\Controllers\Admin\BrandController::class, 'syncToTray'])->name('brands.sync_to_tray');
    Route::resource('brands', App\Http\Controllers\Admin\BrandController::class);
    Route::resource('manufacturers', App\Http\Controllers\Admin\ManufacturerController::class);
    
    Route::get('api-logs', [App\Http\Controllers\Admin\ApiLogController::class, 'index'])->name('api_logs.index');
    Route::get('api-logs/{apiLog}', [App\Http\Controllers\Admin\ApiLogController::class, 'show'])->name('api_logs.show');
    Route::delete('api-logs/{apiLog}', [App\Http\Controllers\Admin\ApiLogController::class, 'destroy'])->name('api_logs.destroy');
    Route::delete('api-logs-clear-old', [App\Http\Controllers\Admin\ApiLogController::class, 'clearOld'])->name('api_logs.clearOld');
    Route::get('api-docs', [App\Http\Controllers\Admin\ApiDocsController::class, 'index'])->name('api_docs.index');
    Route::post('products/{product}/sync-to-tray', [App\Http\Controllers\Admin\ProductController::class, 'syncToTray'])->name('products.sync_to_tray');
    Route::post('products/{product}/sync-image', [App\Http\Controllers\Admin\ProductController::class, 'syncImage'])->name('products.sync_image');
    Route::post('products/{product}/sync-properties', [App\Http\Controllers\Admin\ProductController::class, 'syncProperties'])->name('products.sync_properties');
    Route::resource('products', App\Http\Controllers\Admin\ProductController::class);
    Route::post('properties/{property}/sync-to-tray', [App\Http\Controllers\Admin\PropertyController::class, 'syncToTray'])->name('properties.sync_to_tray');
    Route::resource('properties', App\Http\Controllers\Admin\PropertyController::class);
    Route::resource('products.variants', App\Http\Controllers\Admin\VariantController::class)->shallow();
    Route::post('variants/{variant}/sync-to-tray', [App\Http\Controllers\Admin\VariantController::class, 'syncToTray'])->name('variants.sync_to_tray');
    Route::resource('properties.values', App\Http\Controllers\Admin\PropertyValueController::class)->shallow();
    Route::get('tray', [App\Http\Controllers\Admin\TrayController::class, 'index'])->name('tray.index');
    Route::post('tray', [App\Http\Controllers\Admin\TrayController::class, 'store'])->name('tray.store');
    Route::post('tray/tokens', [App\Http\Controllers\Admin\TrayController::class, 'generateTokens'])->name('tray.tokens');
    Route::get('sync', [App\Http\Controllers\Admin\SyncController::class, 'index'])->name('sync.index');
    Route::get('sync/test-db', [App\Http\Controllers\Admin\SyncController::class, 'testLegacyConnection'])->name('sync.test_db');
    Route::post('sync/categories', [App\Http\Controllers\Admin\SyncController::class, 'syncCategories'])->name('sync.categories');
    Route::post('sync/brands', [App\Http\Controllers\Admin\SyncController::class, 'syncBrands'])->name('sync.brands');
    Route::post('sync/manufacturers', [App\Http\Controllers\Admin\SyncController::class, 'syncManufacturers'])->name('sync.manufacturers');
    Route::post('sync/products', [App\Http\Controllers\Admin\SyncController::class, 'syncProducts'])->name('sync.products');
    Route::post('sync/images', [App\Http\Controllers\Admin\SyncController::class, 'syncImages'])->name('sync.images');

    Route::prefix('tray-sync')->name('tray_sync.')->group(function () {
        Route::post('categories', [App\Http\Controllers\Admin\TraySyncController::class, 'syncCategories'])->name('categories');
        Route::post('brands', [App\Http\Controllers\Admin\TraySyncController::class, 'syncBrands'])->name('brands');
        Route::post('products', [App\Http\Controllers\Admin\TraySyncController::class, 'syncProducts'])->name('products');
    });
});
