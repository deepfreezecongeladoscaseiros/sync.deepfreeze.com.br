<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('webhook')->middleware(\App\Http\Middleware\ValidateWebhookToken::class)->group(function () {
    Route::post('/product-update', [App\Http\Controllers\Api\WebhookController::class, 'productUpdate']);
    Route::post('/stock-update', [App\Http\Controllers\Api\WebhookController::class, 'stockUpdate']);
    Route::post('/price-update', [App\Http\Controllers\Api\WebhookController::class, 'priceUpdate']);
    Route::post('/category-update', [App\Http\Controllers\Api\WebhookController::class, 'categoryUpdate']);
    Route::post('/brand-update', [App\Http\Controllers\Api\WebhookController::class, 'brandUpdate']);
    Route::post('/manufacturer-update', [App\Http\Controllers\Api\WebhookController::class, 'manufacturerUpdate']);
});

Route::prefix('v1')->middleware(['api.token', 'throttle:120,1', \App\Http\Middleware\LogApiRequests::class])->group(function () {
    Route::get('/products', [App\Http\Controllers\Api\V1\ProductController::class, 'index']);
    Route::get('/products/search', [App\Http\Controllers\Api\V1\ProductController::class, 'search']);
    Route::get('/products/featured', [App\Http\Controllers\Api\V1\ProductController::class, 'featured']);
    Route::get('/products/on-sale', [App\Http\Controllers\Api\V1\ProductController::class, 'onSale']);
    Route::get('/products/{id}', [App\Http\Controllers\Api\V1\ProductController::class, 'show']);
    
    Route::get('/categories', [App\Http\Controllers\Api\V1\CategoryController::class, 'index']);
    Route::get('/categories/{id}', [App\Http\Controllers\Api\V1\CategoryController::class, 'show']);
    Route::get('/categories/{id}/products', [App\Http\Controllers\Api\V1\CategoryController::class, 'products']);
    
    Route::get('/brands', [App\Http\Controllers\Api\V1\BrandController::class, 'index']);
    Route::get('/brands/{id}', [App\Http\Controllers\Api\V1\BrandController::class, 'show']);
    Route::get('/brands/{id}/products', [App\Http\Controllers\Api\V1\BrandController::class, 'products']);
});
