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

/*
|--------------------------------------------------------------------------
| Rotas da Storefront (guard: customer)
|--------------------------------------------------------------------------
|
| Todas as rotas da loja usam o middleware 'customer.guard' que define
| Auth::shouldUse('customer') — assim @auth, @guest e auth()->user()
| funcionam automaticamente com a tabela 'pessoas' do banco legado.
*/

// Todas as rotas da storefront usam o guard 'customer' (tabela pessoas)
Route::middleware('customer.guard')->group(function () {

    // Rota raiz - Exibe a página inicial da loja virtual
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // Rota de categoria - Padrão legado: /congelados/{slug}
    // Mantém compatibilidade com URLs já indexadas no Google
    Route::get('/congelados/{slug}', [App\Http\Controllers\Storefront\CategoryController::class, 'show'])->name('category.show');

    // Redirect 301 da rota antiga para manter links antigos do sync funcionando
    Route::get('/categoria/{slug}', function ($slug) {
        return redirect("/congelados/{$slug}", 301);
    });

}); // Fim do grupo customer.guard para rotas acima (categoria, redirect)

Route::get('/docs', [App\Http\Controllers\ApiDocsController::class, 'index'])->name('docs');

// Login do painel administrativo — FORA do customer.guard para usar guard 'web'
Route::get('/admin/login', function () {
    return view('auth.login');
})->middleware('guest')->name('admin.login');

Route::post('/admin/login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store'])
    ->middleware('guest')
    ->name('admin.login.store');

Route::post('/admin/logout', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('admin.logout');

require __DIR__.'/auth.php';

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('integrations', IntegrationController::class);
    // Categorias: somente listagem (CRUD é feito no SIV legado)
    Route::get('categories', [App\Http\Controllers\Admin\CategoryController::class, 'index'])->name('categories.index');
    // Marcas: somente listagem (CRUD é feito no SIV legado)
    Route::get('brands', [App\Http\Controllers\Admin\BrandController::class, 'index'])->name('brands.index');
    // Fabricantes: somente listagem (CRUD é feito no SIV legado)
    Route::get('manufacturers', [App\Http\Controllers\Admin\ManufacturerController::class, 'index'])->name('manufacturers.index');
    
    Route::get('api-logs', [App\Http\Controllers\Admin\ApiLogController::class, 'index'])->name('api_logs.index');
    Route::get('api-logs/{apiLog}', [App\Http\Controllers\Admin\ApiLogController::class, 'show'])->name('api_logs.show');
    Route::delete('api-logs/{apiLog}', [App\Http\Controllers\Admin\ApiLogController::class, 'destroy'])->name('api_logs.destroy');
    Route::delete('api-logs-clear-old', [App\Http\Controllers\Admin\ApiLogController::class, 'clearOld'])->name('api_logs.clearOld');
    Route::get('api-docs', [App\Http\Controllers\Admin\ApiDocsController::class, 'index'])->name('api_docs.index');
    // Produtos: somente listagem (CRUD é feito no SIV legado)
    Route::get('products', [App\Http\Controllers\Admin\ProductController::class, 'index'])->name('products.index');
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

// Rotas da storefront (guard customer) - carrinho, checkout, entrega, pagamento, etc.
Route::middleware('customer.guard')->group(function () {

    // Rotas públicas de Contato (formulário e envio)
    Route::get('/contato', [App\Http\Controllers\ContactController::class, 'index'])->name('contact');
    Route::post('/contato/enviar', [App\Http\Controllers\ContactController::class, 'send'])->name('contact.send');

    // Rotas do Carrinho de Compras (session-based, sem autenticação)
    Route::prefix('carrinho')->name('cart.')->group(function () {
        Route::get('/', [App\Http\Controllers\Storefront\CartController::class, 'index'])->name('index');
        Route::post('/adicionar', [App\Http\Controllers\Storefront\CartController::class, 'add'])->name('add');
        Route::post('/atualizar', [App\Http\Controllers\Storefront\CartController::class, 'update'])->name('update');
        Route::post('/remover', [App\Http\Controllers\Storefront\CartController::class, 'remove'])->name('remove');
        Route::get('/sidebar', [App\Http\Controllers\Storefront\CartController::class, 'sidebar'])->name('sidebar');
    });

    // Rotas do Checkout (login obrigatório para finalizar)
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/', [App\Http\Controllers\Storefront\CheckoutController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\Storefront\CheckoutController::class, 'store'])->name('store');
        Route::get('/confirmacao/{orderNumber}', [App\Http\Controllers\Storefront\CheckoutController::class, 'confirmation'])->name('confirmation');
    });

    // Rotas de endereços do cliente (AJAX, requer login)
    Route::prefix('enderecos')->name('address.')->middleware('auth:customer')->group(function () {
        Route::get('/', [App\Http\Controllers\Storefront\AddressController::class, 'list'])->name('list');
        Route::post('/', [App\Http\Controllers\Storefront\AddressController::class, 'store'])->name('store');
    });

    // Rotas de pagamento (gateways, polling, callback)
    Route::prefix('pagamento')->name('payment.')->group(function () {
        // Callback genérico (POST - webhook)
        Route::post('/callback', [App\Http\Controllers\Storefront\PaymentController::class, 'callback'])->name('callback');

        // Cielo Checkout
        Route::get('/cielo/{pedidoId}/{lojaId}', [App\Http\Controllers\Storefront\PaymentController::class, 'redirectToCielo'])->name('cielo.redirect');
        Route::get('/aguardar-cielo/{pedidoId}/{tentativa?}', [App\Http\Controllers\Storefront\PaymentController::class, 'aguardarCielo'])->name('cielo.aguardar');
        Route::get('/status-cielo/{pedidoId}', [App\Http\Controllers\Storefront\PaymentController::class, 'statusCielo'])->name('cielo.status');

        // Rede e-Rede
        Route::get('/rede/cartao/{pedidoId}/{lojaId}/{formaPagamentoId}', [App\Http\Controllers\Storefront\PaymentController::class, 'redeCartao'])->name('rede.cartao');
        Route::post('/rede/cartao/{pedidoId}/{lojaId}/{formaPagamentoId}', [App\Http\Controllers\Storefront\PaymentController::class, 'redeProcessar'])->name('rede.processar');
        Route::get('/rede/consultar-tid/{pedidoId}/{lojaId}', [App\Http\Controllers\Storefront\PaymentController::class, 'redeConsultarTid'])->name('rede.consultar_tid');
    });

    // Rota de validação de cupom (AJAX para checkout)
    Route::post('/cupom/validar', [App\Http\Controllers\Storefront\CouponController::class, 'check'])->name('coupon.validate');

    // Área do cliente (requer login)
    Route::prefix('minha-conta')->middleware('auth:customer')->group(function () {
        Route::get('/pedidos', [App\Http\Controllers\Storefront\CustomerController::class, 'orders'])->name('customer.orders');
        Route::get('/pedidos/{id}', [App\Http\Controllers\Storefront\CustomerController::class, 'orderDetail'])->name('customer.order.detail');
    });

    // Rotas de entrega/frete (AJAX para checkout)
    Route::prefix('entrega')->name('shipping.')->group(function () {
        Route::get('/consultar-cep', [App\Http\Controllers\Storefront\ShippingController::class, 'consultarCep'])->name('lookup');
        Route::post('/calcular-frete', [App\Http\Controllers\Storefront\ShippingController::class, 'calcularFrete'])->name('calculate');
        Route::get('/periodos', [App\Http\Controllers\Storefront\ShippingController::class, 'periodos'])->name('slots');
        Route::get('/lojas-retirada', [App\Http\Controllers\Storefront\ShippingController::class, 'lojasRetirada'])->name('pickup_stores');
    });

    // Rota para enviar avaliação de produto (AJAX, requer login)
    Route::post('/produto/avaliar', [App\Http\Controllers\Storefront\ReviewController::class, 'store'])->name('review.store');

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
        ->where('slug', '^(?!admin|login|logout|register|cadastro|api|css|js|storage|images|carrinho|contato|checkout).*$');

}); // Fim do grupo customer.guard
