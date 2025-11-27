<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\Storefront\HomeController;
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

// Rota raiz - Exibe a página inicial da loja virtual
Route::get('/', [HomeController::class, 'index'])->name('home');

// Rota de categoria - Exibe produtos de uma categoria
Route::get('/categoria/{slug}', [App\Http\Controllers\Storefront\CategoryController::class, 'show'])->name('category.show');

Route::get('/admin/login', function () {
    return view('auth.login');
})->middleware('guest')->name('admin.login');

Route::post('/admin/logout', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('admin.logout');

Route::get('/docs', [App\Http\Controllers\ApiDocsController::class, 'index'])->name('docs');

require __DIR__.'/auth.php';

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
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

    // Rotas de Layout (Cores, Fontes, Logo, Top Bar, etc.)
    Route::prefix('layout')->name('layout.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\LayoutController::class, 'index'])->name('index');
        Route::get('/colors', [App\Http\Controllers\Admin\LayoutController::class, 'colors'])->name('colors');
        Route::put('/colors', [App\Http\Controllers\Admin\LayoutController::class, 'updateColors'])->name('colors.update');
        Route::get('/logo', [App\Http\Controllers\Admin\LayoutController::class, 'logo'])->name('logo');
        Route::post('/logo', [App\Http\Controllers\Admin\LayoutController::class, 'updateLogo'])->name('logo.update');
        Route::get('/topbar', [App\Http\Controllers\Admin\LayoutController::class, 'topBar'])->name('topbar');
        Route::put('/topbar', [App\Http\Controllers\Admin\LayoutController::class, 'updateTopBar'])->name('topbar.update');
    });

    // Rotas de Banners Hero
    Route::resource('banners', App\Http\Controllers\Admin\BannerController::class);

    // Rotas de Blocos de Informações (Feature Blocks)
    Route::get('feature-blocks', [App\Http\Controllers\Admin\FeatureBlockController::class, 'index'])->name('feature-blocks.index');
    Route::get('feature-blocks/{featureBlock}/edit', [App\Http\Controllers\Admin\FeatureBlockController::class, 'edit'])->name('feature-blocks.edit');
    Route::put('feature-blocks/{featureBlock}', [App\Http\Controllers\Admin\FeatureBlockController::class, 'update'])->name('feature-blocks.update');

    // Rotas de Galerias de Produtos
    Route::resource('product-galleries', App\Http\Controllers\Admin\ProductGalleryController::class);

    // Rotas de Banners Duplos
    Route::resource('dual-banners', App\Http\Controllers\Admin\DualBannerController::class);

    // Rotas de Blocos de Informação
    Route::resource('info-blocks', App\Http\Controllers\Admin\InfoBlockController::class);

    // Rotas de Blocos de Passos (4 itens)
    Route::resource('step-blocks', App\Http\Controllers\Admin\StepBlockController::class);

    // Rotas de Banners Únicos
    Route::resource('single-banners', App\Http\Controllers\Admin\SingleBannerController::class);

    // Rotas de Ordenação das Seções da Home (sistema antigo - mantido para compatibilidade)
    Route::prefix('home-sections')->name('home-sections.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\HomeSectionController::class, 'index'])->name('index');
        Route::post('/update-order', [App\Http\Controllers\Admin\HomeSectionController::class, 'updateOrder'])->name('update-order');
        Route::post('/{homeSection}/toggle', [App\Http\Controllers\Admin\HomeSectionController::class, 'toggleActive'])->name('toggle');
    });

    // Rotas de Blocos Flexíveis da Home (sistema novo - permite intercalar blocos)
    Route::prefix('home-blocks')->name('home-blocks.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\HomeBlockController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\Admin\HomeBlockController::class, 'store'])->name('store');
        Route::delete('/{homeBlock}', [App\Http\Controllers\Admin\HomeBlockController::class, 'destroy'])->name('destroy');
        Route::post('/update-order', [App\Http\Controllers\Admin\HomeBlockController::class, 'updateOrder'])->name('update-order');
        Route::post('/{homeBlock}/toggle', [App\Http\Controllers\Admin\HomeBlockController::class, 'toggleActive'])->name('toggle');
        Route::put('/{homeBlock}/title', [App\Http\Controllers\Admin\HomeBlockController::class, 'updateTitle'])->name('update-title');
        Route::get('/items/{type}', [App\Http\Controllers\Admin\HomeBlockController::class, 'getItems'])->name('items');
    });

    // Rotas de Cookie Consent LGPD
    Route::get('cookie-consent', [App\Http\Controllers\Admin\CookieConsentController::class, 'edit'])->name('cookie-consent.edit');
    Route::put('cookie-consent', [App\Http\Controllers\Admin\CookieConsentController::class, 'update'])->name('cookie-consent.update');

    // Rotas de Redes Sociais
    Route::resource('social-networks', App\Http\Controllers\Admin\SocialNetworkController::class);

    // Rotas de Páginas Internas (Institucional)
    Route::resource('pages', App\Http\Controllers\Admin\PageController::class);

    // Rotas de Contato (Configurações e Mensagens)
    Route::prefix('contact')->name('contact.')->group(function () {
        // Configurações da página de contato
        Route::get('/', [App\Http\Controllers\Admin\ContactController::class, 'edit'])->name('edit');
        Route::put('/', [App\Http\Controllers\Admin\ContactController::class, 'update'])->name('update');

        // Mensagens recebidas pelo formulário de contato
        Route::get('/messages', [App\Http\Controllers\Admin\ContactController::class, 'messages'])->name('messages');
        Route::get('/messages/{message}', [App\Http\Controllers\Admin\ContactController::class, 'showMessage'])->name('messages.show');
        Route::post('/messages/{message}/toggle-read', [App\Http\Controllers\Admin\ContactController::class, 'toggleRead'])->name('messages.toggle-read');
        Route::delete('/messages/{message}', [App\Http\Controllers\Admin\ContactController::class, 'destroyMessage'])->name('messages.destroy');
        Route::post('/messages/mark-all-read', [App\Http\Controllers\Admin\ContactController::class, 'markAllRead'])->name('messages.mark-all-read');
        Route::delete('/messages/clear-old', [App\Http\Controllers\Admin\ContactController::class, 'clearOld'])->name('messages.clear-old');
    });

    // Rotas de Menus de Navegação
    Route::prefix('menus')->name('menus.')->group(function () {
        // CRUD de Menus
        Route::get('/', [App\Http\Controllers\Admin\MenuController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\MenuController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\MenuController::class, 'store'])->name('store');
        Route::get('/{menu}/edit', [App\Http\Controllers\Admin\MenuController::class, 'edit'])->name('edit');
        Route::put('/{menu}', [App\Http\Controllers\Admin\MenuController::class, 'update'])->name('update');
        Route::delete('/{menu}', [App\Http\Controllers\Admin\MenuController::class, 'destroy'])->name('destroy');

        // Gerenciamento de Itens do Menu
        Route::get('/{menu}/items', [App\Http\Controllers\Admin\MenuController::class, 'items'])->name('items');
        Route::post('/{menu}/items', [App\Http\Controllers\Admin\MenuController::class, 'addItem'])->name('items.add');
        Route::put('/{menu}/items/{item}', [App\Http\Controllers\Admin\MenuController::class, 'updateItem'])->name('items.update');
        Route::delete('/{menu}/items/{item}', [App\Http\Controllers\Admin\MenuController::class, 'destroyItem'])->name('items.destroy');
        Route::post('/{menu}/items/reorder', [App\Http\Controllers\Admin\MenuController::class, 'reorderItems'])->name('items.reorder');
        Route::post('/{menu}/items/{item}/toggle', [App\Http\Controllers\Admin\MenuController::class, 'toggleItemStatus'])->name('items.toggle');
        Route::post('/{menu}/items/{item}/duplicate', [App\Http\Controllers\Admin\MenuController::class, 'duplicateItem'])->name('items.duplicate');
        Route::delete('/{menu}/items/{item}/icon', [App\Http\Controllers\Admin\MenuController::class, 'removeItemIcon'])->name('items.remove-icon');
        Route::delete('/{menu}/items/{item}/mega-image', [App\Http\Controllers\Admin\MenuController::class, 'removeMegaMenuImage'])->name('items.remove-mega-image');
    });
});

// Rota pública para CSS dinâmico (sem autenticação)
Route::get('/css/theme.css', [App\Http\Controllers\Admin\LayoutController::class, 'generateCSS'])->name('theme.css');

// Rotas públicas de Contato (formulário e envio)
Route::get('/contato', [App\Http\Controllers\ContactController::class, 'index'])->name('contact');
Route::post('/contato/enviar', [App\Http\Controllers\ContactController::class, 'send'])->name('contact.send');

// Rota de produto por SKU (ex: /produto/KR57)
Route::get('/produto/{sku}', [App\Http\Controllers\Storefront\ProductController::class, 'showBySku'])
    ->name('product.show.sku')
    ->where('sku', '[A-Za-z0-9\-]+');

// Rota de produto por slug (ex: /kit-refeicao/roupa-velha-arroz-branco-e-feijao)
// DEVE vir antes da wildcard route de páginas internas
Route::get('/{categorySlug}/{productSlug}', [App\Http\Controllers\Storefront\ProductController::class, 'show'])
    ->name('product.show')
    ->where('categorySlug', '[a-z0-9_\-]+')
    ->where('productSlug', '[a-z0-9\-]+');

// IMPORTANTE: Wildcard route para Páginas Internas - DEVE FICAR NO FINAL
// Captura qualquer URL não encontrada e verifica se é uma página interna
Route::get('/{slug}', [App\Http\Controllers\PageController::class, 'show'])->name('pages.show')
    ->where('slug', '^(?!admin|login|logout|register|cadastro|api|css|js|storage|images).*$');
