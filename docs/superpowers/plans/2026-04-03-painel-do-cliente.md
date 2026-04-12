# Painel do Cliente ("Minha Conta") — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Criar a área "Minha Conta" do cliente logado com dashboard, edição de perfil, alteração de senha, gestão de endereços e acesso ao histórico de pedidos — tudo lendo/gravando no banco legado.

**Architecture:** Todas as páginas sob o prefixo `/minha-conta` com guard `auth:customer`. Layout sidebar + conteúdo (sidebar com menu de navegação, conteúdo à direita). CSS dedicado `customer-area.css` com cache busting. Formulários gravam no banco legado (mesmas operações que o site legado faz). Senha em MD5 para compatibilidade.

**Tech Stack:** Laravel 10, Blade, Bootstrap 3, jQuery 2, Inputmask (já carregado), banco legado (`mysql_legacy`)

---

## Mapeamento de Arquivos

| Arquivo | Ação | Responsabilidade |
|---------|------|------------------|
| `public/storefront/css/customer-area.css` | **Criar** | CSS dedicado para toda a área do cliente |
| `resources/views/storefront/customer/layout.blade.php` | **Criar** | Layout com sidebar + conteúdo (extends storefront) |
| `resources/views/storefront/customer/dashboard.blade.php` | **Criar** | Dashboard com cards de navegação |
| `resources/views/storefront/customer/profile.blade.php` | **Criar** | Formulário de edição de perfil |
| `resources/views/storefront/customer/password.blade.php` | **Criar** | Formulário de alteração de senha |
| `resources/views/storefront/customer/addresses.blade.php` | **Criar** | Listagem + formulário de endereços |
| `resources/views/storefront/customer/orders.blade.php` | **Modificar** | Adaptar para usar layout com sidebar |
| `resources/views/storefront/customer/order-detail.blade.php` | **Modificar** | Adaptar para usar layout com sidebar |
| `app/Http/Controllers/Storefront/CustomerController.php` | **Modificar** | Adicionar métodos: dashboard, profile, updateProfile, password, updatePassword, addresses, deleteAddress |
| `resources/views/layouts/storefront.blade.php` | **Modificar** | Adicionar link CSS customer-area.css |
| `resources/views/storefront/partials/header.blade.php` | **Modificar** | Adicionar link "Minha Conta" para cliente logado |
| `routes/web.php` | **Modificar** | Adicionar rotas da área do cliente |

## Design — Identidade Visual

A área do cliente segue o padrão do storefront existente:
- Banner interno com imagem de fundo e título
- Container Bootstrap com `pg-internas bg-loja`
- Layout duas colunas: **sidebar esquerda** (menu de navegação) + **conteúdo à direita**
- Cards com `border-radius: 10px`, `box-shadow` sutil, transição no hover
- Cores primárias do tema via CSS variables (`--color-primary: #013E3B`)
- Botões com `border-radius: 30px` (padrão do tema)
- Mobile: sidebar vira menu horizontal no topo

### Estrutura da Sidebar

```
[Avatar com iniciais] Nome do Cliente
─────────────────────────────────
📊 Dashboard            (ativo)
👤 Meus Dados
🔒 Alterar Senha
📍 Meus Endereços
📦 Meus Pedidos
─────────────────────────────────
🚪 Sair
```

---

### Task 1: CSS da área do cliente + layout com sidebar

**Files:**
- Create: `public/storefront/css/customer-area.css`
- Create: `resources/views/storefront/customer/layout.blade.php`
- Modify: `resources/views/layouts/storefront.blade.php`

- [ ] **Step 1: Criar o CSS dedicado**

Criar `public/storefront/css/customer-area.css`:

```css
/* ============================================
   ÁREA DO CLIENTE — MINHA CONTA
   Layout sidebar + conteúdo, responsivo.
   ============================================ */

/* Container principal */
.customer-area {
    padding: 30px 0;
    min-height: 400px;
}

/* ============================================
   SIDEBAR
   ============================================ */

.customer-sidebar {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    padding: 24px 0;
    margin-bottom: 20px;
}

/* Avatar + Nome do cliente no topo da sidebar */
.customer-sidebar-header {
    text-align: center;
    padding: 0 20px 20px;
    border-bottom: 1px solid #eee;
    margin-bottom: 10px;
}

.customer-avatar {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: var(--color-primary, #013E3B);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3em;
    font-weight: 700;
    margin: 0 auto 10px;
    letter-spacing: 1px;
}

.customer-sidebar-header h4 {
    margin: 0;
    font-size: 1em;
    font-weight: 600;
    color: #333;
}

.customer-sidebar-header small {
    color: #999;
    font-size: 0.82em;
}

/* Menu de navegação */
.customer-nav {
    list-style: none;
    margin: 0;
    padding: 0;
}

.customer-nav li a {
    display: block;
    padding: 12px 24px;
    color: #555;
    text-decoration: none;
    font-size: 0.93em;
    transition: all 0.15s ease;
    border-left: 3px solid transparent;
}

.customer-nav li a:hover {
    background: #f8f8f8;
    color: var(--color-primary, #013E3B);
}

.customer-nav li a.active {
    background: rgba(1, 62, 59, 0.05);
    color: var(--color-primary, #013E3B);
    font-weight: 600;
    border-left-color: var(--color-primary, #013E3B);
}

.customer-nav li a i {
    width: 20px;
    margin-right: 10px;
    text-align: center;
}

.customer-nav-divider {
    border-top: 1px solid #eee;
    margin: 8px 0;
}

.customer-nav li a.nav-logout {
    color: #dc3545;
}

.customer-nav li a.nav-logout:hover {
    background: #fff5f5;
}

/* ============================================
   CONTEÚDO
   ============================================ */

.customer-content {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    padding: 30px;
    min-height: 400px;
}

.customer-content h2 {
    font-size: 1.4em;
    font-weight: 700;
    color: #333;
    margin: 0 0 6px;
}

.customer-content .section-subtitle {
    color: #888;
    font-size: 0.9em;
    margin-bottom: 24px;
}

/* ============================================
   DASHBOARD — CARDS
   ============================================ */

.dashboard-cards {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
    margin-top: 20px;
}

.dash-card {
    background: #fff;
    border: 1px solid #eee;
    border-radius: 10px;
    padding: 24px 20px;
    text-align: center;
    text-decoration: none;
    color: #333;
    transition: all 0.2s ease;
}

.dash-card:hover {
    border-color: var(--color-primary, #013E3B);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    color: var(--color-primary, #013E3B);
    text-decoration: none;
}

.dash-card i {
    font-size: 2em;
    display: block;
    margin-bottom: 10px;
    color: var(--color-primary, #013E3B);
}

.dash-card span {
    font-weight: 600;
    font-size: 0.95em;
}

.dash-card small {
    display: block;
    color: #999;
    font-size: 0.8em;
    margin-top: 4px;
}

/* ============================================
   FORMULÁRIOS — PERFIL, SENHA
   ============================================ */

.customer-form .form-group {
    margin-bottom: 18px;
}

.customer-form label {
    font-weight: 600;
    font-size: 0.9em;
    color: #555;
    margin-bottom: 6px;
}

.customer-form .form-control {
    border-radius: 8px;
    height: 44px;
    border: 1px solid #ddd;
    font-size: 0.93em;
    transition: border-color 0.2s;
}

.customer-form .form-control:focus {
    border-color: var(--color-primary, #013E3B);
    box-shadow: 0 0 0 2px rgba(1, 62, 59, 0.1);
}

.customer-form .btn-save {
    background: var(--color-primary, #013E3B);
    color: #fff;
    border: none;
    border-radius: 30px;
    padding: 10px 32px;
    font-weight: 600;
    font-size: 0.93em;
    cursor: pointer;
    transition: background 0.2s;
}

.customer-form .btn-save:hover {
    background: var(--color-secondary, #FFA733);
}

.customer-form .alert {
    border-radius: 8px;
    font-size: 0.9em;
}

/* ============================================
   ENDEREÇOS — CARDS
   ============================================ */

.address-list {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
    margin-bottom: 20px;
}

.address-card {
    background: #f9f9f9;
    border: 1px solid #eee;
    border-radius: 8px;
    padding: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.address-card.is-main {
    border-left: 3px solid var(--color-primary, #013E3B);
    background: #f0faf9;
}

.address-card .address-info {
    flex: 1;
    font-size: 0.9em;
    color: #555;
    line-height: 1.5;
}

.address-card .address-info strong {
    color: #333;
}

.address-card .address-actions {
    margin-left: 16px;
}

.address-card .btn-remove {
    background: none;
    border: 1px solid #dc3545;
    color: #dc3545;
    border-radius: 6px;
    padding: 6px 12px;
    font-size: 0.82em;
    cursor: pointer;
    transition: all 0.2s;
}

.address-card .btn-remove:hover {
    background: #dc3545;
    color: #fff;
}

.btn-add-address {
    display: inline-block;
    padding: 10px 24px;
    border: 2px dashed #ccc;
    border-radius: 8px;
    color: #888;
    text-decoration: none;
    font-size: 0.9em;
    transition: all 0.2s;
    cursor: pointer;
    background: none;
}

.btn-add-address:hover {
    border-color: var(--color-primary, #013E3B);
    color: var(--color-primary, #013E3B);
    text-decoration: none;
}

/* ============================================
   BADGE ENDEREÇO PRINCIPAL
   ============================================ */

.badge-principal {
    display: inline-block;
    padding: 2px 8px;
    background: var(--color-primary, #013E3B);
    color: #fff;
    border-radius: 4px;
    font-size: 0.75em;
    font-weight: 600;
    margin-left: 8px;
}

/* ============================================
   FORMULÁRIO NOVO ENDEREÇO (toggle)
   ============================================ */

.address-form-wrapper {
    display: none;
    margin-top: 16px;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 10px;
    border: 1px solid #eee;
}

/* ============================================
   RESPONSIVO
   ============================================ */

@media (max-width: 767px) {
    .customer-area {
        padding: 15px 0;
    }

    .customer-sidebar {
        border-radius: 0;
        margin-bottom: 10px;
        padding: 16px 0;
    }

    .customer-sidebar-header {
        padding: 0 16px 16px;
    }

    .customer-avatar {
        width: 48px;
        height: 48px;
        font-size: 1em;
    }

    .customer-nav li a {
        padding: 10px 16px;
        font-size: 0.88em;
    }

    .customer-content {
        border-radius: 0;
        padding: 20px 16px;
        min-height: auto;
    }

    .dashboard-cards {
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .dash-card {
        padding: 16px 12px;
    }

    .dash-card i {
        font-size: 1.5em;
    }

    .address-card {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .address-card .address-actions {
        margin-left: 0;
    }
}
```

- [ ] **Step 2: Criar o layout com sidebar**

Criar `resources/views/storefront/customer/layout.blade.php`:

```blade
{{--
    Layout: Área do Cliente (Minha Conta)

    Estrutura duas colunas: sidebar (menu) + conteúdo.
    Todas as páginas da área do cliente estendem este layout.

    Variáveis:
    - $activeMenu: string indicando o item ativo ('dashboard', 'profile', 'password', 'addresses', 'orders')
--}}
@extends('layouts.storefront')

@section('body_class', 'pg-interna')

@section('content')

{{-- Banner Interno --}}
<section class="banner-interna" style="background-image: url('{{ asset('storefront/img/ban-interna-1.jpg') }}');">
    <div class="pg-titulo">
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <h1 class="animated fadeIn">Minha Conta</h1>
                </div>
            </div>
        </div>
    </div>
</section>

<main class="pg-internas bg-loja">
    <div class="container">
        <div class="customer-area">
            <div class="row">

                {{-- Sidebar --}}
                <div class="col-xs-12 col-md-3">
                    @php
                        $customer = auth('customer')->user();
                        $nameParts = explode(' ', trim($customer->nome ?? ''));
                        $initials = mb_strtoupper(mb_substr($nameParts[0], 0, 1));
                        if (count($nameParts) > 1) {
                            $initials .= mb_strtoupper(mb_substr(end($nameParts), 0, 1));
                        }
                    @endphp

                    <div class="customer-sidebar">
                        <div class="customer-sidebar-header">
                            <div class="customer-avatar">{{ $initials }}</div>
                            <h4>{{ ucfirst(mb_strtolower($nameParts[0])) }}</h4>
                            <small>{{ $customer->email_primario }}</small>
                        </div>

                        <ul class="customer-nav">
                            <li>
                                <a href="{{ route('customer.dashboard') }}" class="{{ ($activeMenu ?? '') === 'dashboard' ? 'active' : '' }}">
                                    <i class="fa fa-th-large"></i> Dashboard
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('customer.profile') }}" class="{{ ($activeMenu ?? '') === 'profile' ? 'active' : '' }}">
                                    <i class="fa fa-user"></i> Meus Dados
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('customer.password') }}" class="{{ ($activeMenu ?? '') === 'password' ? 'active' : '' }}">
                                    <i class="fa fa-lock"></i> Alterar Senha
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('customer.addresses') }}" class="{{ ($activeMenu ?? '') === 'addresses' ? 'active' : '' }}">
                                    <i class="fa fa-map-marker"></i> Meus Endereços
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('customer.orders') }}" class="{{ ($activeMenu ?? '') === 'orders' ? 'active' : '' }}">
                                    <i class="fa fa-shopping-bag"></i> Meus Pedidos
                                </a>
                            </li>
                            <li class="customer-nav-divider"></li>
                            <li>
                                <a href="{{ route('logout') }}" class="nav-logout"
                                   onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();">
                                    <i class="fa fa-sign-out"></i> Sair
                                </a>
                                <form id="logout-form-sidebar" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Conteúdo --}}
                <div class="col-xs-12 col-md-9">
                    <div class="customer-content">
                        @yield('customer-content')
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>

@endsection
```

- [ ] **Step 3: Incluir o CSS no layout storefront**

Em `resources/views/layouts/storefront.blade.php`, após a linha do `product-detail.css`, adicionar:

```blade
    <link href="{{ asset('storefront/css/customer-area.css') }}?v={{ filemtime(public_path('storefront/css/customer-area.css')) }}" rel="stylesheet">
```

- [ ] **Step 4: Commit**

```bash
git add public/storefront/css/customer-area.css resources/views/storefront/customer/layout.blade.php resources/views/layouts/storefront.blade.php
git commit -m "feat: cria CSS e layout sidebar da área do cliente (Minha Conta)"
```

---

### Task 2: Dashboard + Rotas + Header link

**Files:**
- Create: `resources/views/storefront/customer/dashboard.blade.php`
- Modify: `app/Http/Controllers/Storefront/CustomerController.php`
- Modify: `routes/web.php`
- Modify: `resources/views/storefront/partials/header.blade.php`

- [ ] **Step 1: Criar a view do dashboard**

Criar `resources/views/storefront/customer/dashboard.blade.php`:

```blade
{{--
    Página: Dashboard — Minha Conta
    Cards de navegação + resumo do cliente.
--}}
@extends('storefront.customer.layout')

@section('title', 'Minha Conta - ' . config('app.name'))

@section('customer-content')

    <h2>Olá, {{ ucfirst(mb_strtolower(explode(' ', $customer->nome)[0])) }}!</h2>
    <p class="section-subtitle">Gerencie suas informações, endereços e pedidos.</p>

    <div class="dashboard-cards">
        <a href="{{ route('customer.profile') }}" class="dash-card">
            <i class="fa fa-user"></i>
            <span>Meus Dados</span>
            <small>Editar nome, email, telefone</small>
        </a>

        <a href="{{ route('customer.password') }}" class="dash-card">
            <i class="fa fa-lock"></i>
            <span>Alterar Senha</span>
            <small>Trocar sua senha de acesso</small>
        </a>

        <a href="{{ route('customer.addresses') }}" class="dash-card">
            <i class="fa fa-map-marker"></i>
            <span>Meus Endereços</span>
            <small>{{ $addressCount }} {{ $addressCount === 1 ? 'endereço' : 'endereços' }} cadastrados</small>
        </a>

        <a href="{{ route('customer.orders') }}" class="dash-card">
            <i class="fa fa-shopping-bag"></i>
            <span>Meus Pedidos</span>
            <small>{{ $orderCount }} {{ $orderCount === 1 ? 'pedido' : 'pedidos' }}</small>
        </a>
    </div>

@endsection
```

- [ ] **Step 2: Adicionar método dashboard() no CustomerController**

No `CustomerController.php`, adicionar imports e métodos. Adicionar no topo:

```php
use App\Models\Legacy\Endereco;
```

Adicionar método antes do `orders()`:

```php
    /**
     * Dashboard da área do cliente.
     * GET /minha-conta
     */
    public function dashboard(): View|RedirectResponse
    {
        $customer = auth()->user();

        if (!$customer || !($customer instanceof Pessoa)) {
            return redirect()->route('login');
        }

        $addressCount = Endereco::where('pessoa_id', $customer->id)->where('ativo', 1)->count();
        $orderCount = Pedido::where('pessoa_id', $customer->id)->where('finalizado', '>', 0)->count();

        $activeMenu = 'dashboard';

        return view('storefront.customer.dashboard', compact('customer', 'addressCount', 'orderCount', 'activeMenu'));
    }
```

- [ ] **Step 3: Adicionar rotas**

Em `routes/web.php`, substituir o bloco `minha-conta` existente:

```php
    // Área do cliente (requer login)
    Route::prefix('minha-conta')->middleware('auth:customer')->group(function () {
        Route::get('/', [App\Http\Controllers\Storefront\CustomerController::class, 'dashboard'])->name('customer.dashboard');
        Route::get('/dados', [App\Http\Controllers\Storefront\CustomerController::class, 'profile'])->name('customer.profile');
        Route::put('/dados', [App\Http\Controllers\Storefront\CustomerController::class, 'updateProfile'])->name('customer.profile.update');
        Route::get('/senha', [App\Http\Controllers\Storefront\CustomerController::class, 'password'])->name('customer.password');
        Route::put('/senha', [App\Http\Controllers\Storefront\CustomerController::class, 'updatePassword'])->name('customer.password.update');
        Route::get('/enderecos', [App\Http\Controllers\Storefront\CustomerController::class, 'addresses'])->name('customer.addresses');
        Route::delete('/enderecos/{id}', [App\Http\Controllers\Storefront\CustomerController::class, 'deleteAddress'])->name('customer.address.delete');
        Route::post('/enderecos', [App\Http\Controllers\Storefront\CustomerController::class, 'storeAddress'])->name('customer.address.store');
        Route::get('/pedidos', [App\Http\Controllers\Storefront\CustomerController::class, 'orders'])->name('customer.orders');
        Route::get('/pedidos/{id}', [App\Http\Controllers\Storefront\CustomerController::class, 'orderDetail'])->name('customer.order.detail');
    });
```

- [ ] **Step 4: Adicionar link "Minha Conta" no header**

Em `resources/views/storefront/partials/header.blade.php`, localizar o link de "Login" e adicionar lógica para cliente logado (mostrar "Minha Conta" em vez de "Login"):

Buscar as linhas com `route('login')` e `route('register')` e envolver com `@auth('customer')` / `@else` / `@endauth` para exibir "Minha Conta" quando logado.

- [ ] **Step 5: Commit**

```bash
git add resources/views/storefront/customer/dashboard.blade.php app/Http/Controllers/Storefront/CustomerController.php routes/web.php resources/views/storefront/partials/header.blade.php
git commit -m "feat: cria dashboard Minha Conta com cards de navegação e rotas"
```

---

### Task 3: Editar Perfil

**Files:**
- Create: `resources/views/storefront/customer/profile.blade.php`
- Modify: `app/Http/Controllers/Storefront/CustomerController.php`

- [ ] **Step 1: Criar a view do perfil**

Criar `resources/views/storefront/customer/profile.blade.php`:

```blade
{{--
    Página: Meus Dados — Editar Perfil
    Formulário com dados pessoais do cliente.
    Grava diretamente na tabela pessoas do banco legado.
--}}
@extends('storefront.customer.layout')

@section('title', 'Meus Dados - ' . config('app.name'))

@section('customer-content')

    <h2>Meus Dados</h2>
    <p class="section-subtitle">Atualize suas informações pessoais.</p>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('customer.profile.update') }}" method="POST" class="customer-form">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group">
                    <label for="nome">Nome completo *</label>
                    <input type="text" name="nome" id="nome" class="form-control"
                           value="{{ old('nome', $customer->nome) }}" required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-6">
                <div class="form-group">
                    <label for="email_primario">E-mail *</label>
                    <input type="email" name="email_primario" id="email_primario" class="form-control"
                           value="{{ old('email_primario', $customer->email_primario) }}" required>
                </div>
            </div>
            <div class="col-xs-12 col-md-6">
                <div class="form-group">
                    <label for="cpf">CPF</label>
                    <input type="text" name="cpf" id="cpf" class="form-control"
                           value="{{ old('cpf', $customer->cpf) }}">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-4">
                <div class="form-group">
                    <label for="telefone_celular">Celular</label>
                    <input type="text" name="telefone_celular" id="telefone_celular" class="form-control"
                           value="{{ old('telefone_celular', $customer->telefone_celular) }}">
                </div>
            </div>
            <div class="col-xs-12 col-md-4">
                <div class="form-group">
                    <label for="nascimento">Data de Nascimento</label>
                    <input type="text" name="nascimento" id="nascimento" class="form-control"
                           value="{{ old('nascimento', $customer->nascimento ? $customer->nascimento->format('d/m/Y') : '') }}">
                </div>
            </div>
            <div class="col-xs-12 col-md-4">
                <div class="form-group">
                    <label>Sexo</label>
                    <select name="sexo" class="form-control">
                        <option value="">Selecione</option>
                        <option value="M" {{ old('sexo', $customer->sexo) === 'M' ? 'selected' : '' }}>Masculino</option>
                        <option value="F" {{ old('sexo', $customer->sexo) === 'F' ? 'selected' : '' }}>Feminino</option>
                        <option value="O" {{ old('sexo', $customer->sexo) === 'O' ? 'selected' : '' }}>Outros</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Preferências de comunicação --}}
        <div style="margin-top: 16px; margin-bottom: 20px;">
            <label style="font-weight: 600; font-size: 0.9em; color: #555; margin-bottom: 10px; display: block;">Preferências de comunicação</label>
            <div class="row">
                <div class="col-xs-6 col-md-3">
                    <label style="font-weight: 400; font-size: 0.88em;">
                        <input type="checkbox" name="autoriza_newsletter" value="1"
                               {{ old('autoriza_newsletter', $customer->autoriza_newsletter) ? 'checked' : '' }}>
                        Newsletter
                    </label>
                </div>
                <div class="col-xs-6 col-md-3">
                    <label style="font-weight: 400; font-size: 0.88em;">
                        <input type="checkbox" name="aceita_whats_app" value="1"
                               {{ old('aceita_whats_app', $customer->aceita_whats_app) ? 'checked' : '' }}>
                        WhatsApp
                    </label>
                </div>
                <div class="col-xs-6 col-md-3">
                    <label style="font-weight: 400; font-size: 0.88em;">
                        <input type="checkbox" name="aceita_sms" value="1"
                               {{ old('aceita_sms', $customer->aceita_sms) ? 'checked' : '' }}>
                        SMS
                    </label>
                </div>
                <div class="col-xs-6 col-md-3">
                    <label style="font-weight: 400; font-size: 0.88em;">
                        <input type="checkbox" name="aceita_ligacao" value="1"
                               {{ old('aceita_ligacao', $customer->aceita_ligacao) ? 'checked' : '' }}>
                        Ligação
                    </label>
                </div>
            </div>
        </div>

        <button type="submit" class="btn-save">
            <i class="fa fa-check"></i> Salvar Alterações
        </button>
    </form>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#cpf').inputmask('999.999.999-99');
    $('#telefone_celular').inputmask('(99) 99999-9999');
    $('#nascimento').inputmask('99/99/9999');
});
</script>
@endpush
```

- [ ] **Step 2: Adicionar métodos profile() e updateProfile() no CustomerController**

```php
    /**
     * Exibe formulário de edição de perfil.
     * GET /minha-conta/dados
     */
    public function profile(): View|RedirectResponse
    {
        $customer = auth()->user();

        if (!$customer || !($customer instanceof Pessoa)) {
            return redirect()->route('login');
        }

        $activeMenu = 'profile';

        return view('storefront.customer.profile', compact('customer', 'activeMenu'));
    }

    /**
     * Atualiza dados do perfil no banco legado.
     * PUT /minha-conta/dados
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $customer = auth()->user();

        if (!$customer || !($customer instanceof Pessoa)) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'nome'                => 'required|string|max:255',
            'email_primario'      => 'required|email|max:255',
            'cpf'                 => 'nullable|string|max:50',
            'telefone_celular'    => 'nullable|string|max:15',
            'nascimento'          => 'nullable|string|max:10',
            'sexo'                => 'nullable|in:M,F,O',
        ], [
            'nome.required'           => 'O nome é obrigatório.',
            'email_primario.required' => 'O e-mail é obrigatório.',
            'email_primario.email'    => 'Informe um e-mail válido.',
        ]);

        // Converte data de DD/MM/YYYY para YYYY-MM-DD
        $nascimento = null;
        if (!empty($validated['nascimento'])) {
            $parts = explode('/', $validated['nascimento']);
            if (count($parts) === 3) {
                $nascimento = "{$parts[2]}-{$parts[1]}-{$parts[0]}";
            }
        }

        // Atualiza no banco legado (mesma operação que o perfil.ctp do legado faz)
        $customer->nome              = $validated['nome'];
        $customer->email_primario    = $validated['email_primario'];
        $customer->cpf               = $validated['cpf'] ?? $customer->cpf;
        $customer->telefone_celular  = $validated['telefone_celular'] ?? $customer->telefone_celular;
        $customer->nascimento        = $nascimento;
        $customer->sexo              = $validated['sexo'] ?? $customer->sexo;
        $customer->autoriza_newsletter = $request->has('autoriza_newsletter') ? 1 : 0;
        $customer->aceita_whats_app    = $request->has('aceita_whats_app') ? 1 : 0;
        $customer->aceita_sms          = $request->has('aceita_sms') ? 1 : 0;
        $customer->aceita_ligacao      = $request->has('aceita_ligacao') ? 1 : 0;
        $customer->save();

        return redirect()->route('customer.profile')->with('success', 'Dados atualizados com sucesso!');
    }
```

- [ ] **Step 3: Commit**

```bash
git add resources/views/storefront/customer/profile.blade.php app/Http/Controllers/Storefront/CustomerController.php
git commit -m "feat: cria edição de perfil do cliente com gravação no banco legado"
```

---

### Task 4: Alterar Senha

**Files:**
- Create: `resources/views/storefront/customer/password.blade.php`
- Modify: `app/Http/Controllers/Storefront/CustomerController.php`

- [ ] **Step 1: Criar a view de alterar senha**

Criar `resources/views/storefront/customer/password.blade.php`:

```blade
{{--
    Página: Alterar Senha
    Senha atual + nova senha + confirmação.
    Grava em MD5 na tabela pessoas para compatibilidade com o legado.
--}}
@extends('storefront.customer.layout')

@section('title', 'Alterar Senha - ' . config('app.name'))

@section('customer-content')

    <h2>Alterar Senha</h2>
    <p class="section-subtitle">Sua senha é criptografada e não pode ser visualizada.</p>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('customer.password.update') }}" method="POST" class="customer-form" style="max-width: 400px;">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="current_password">Senha atual *</label>
            <input type="password" name="current_password" id="current_password" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="new_password">Nova senha *</label>
            <input type="password" name="new_password" id="new_password" class="form-control" required minlength="6">
        </div>

        <div class="form-group">
            <label for="new_password_confirmation">Confirmar nova senha *</label>
            <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control" required>
        </div>

        <button type="submit" class="btn-save">
            <i class="fa fa-lock"></i> Alterar Senha
        </button>
    </form>

@endsection
```

- [ ] **Step 2: Adicionar métodos password() e updatePassword() no CustomerController**

```php
    /**
     * Exibe formulário de alteração de senha.
     * GET /minha-conta/senha
     */
    public function password(): View|RedirectResponse
    {
        $customer = auth()->user();

        if (!$customer || !($customer instanceof Pessoa)) {
            return redirect()->route('login');
        }

        $activeMenu = 'password';

        return view('storefront.customer.password', compact('customer', 'activeMenu'));
    }

    /**
     * Atualiza senha do cliente no banco legado.
     * Senha armazenada em MD5 para compatibilidade com o SIV.
     * PUT /minha-conta/senha
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $customer = auth()->user();

        if (!$customer || !($customer instanceof Pessoa)) {
            return redirect()->route('login');
        }

        $request->validate([
            'current_password'     => 'required|string',
            'new_password'         => 'required|string|min:6|confirmed',
        ], [
            'current_password.required'     => 'Informe sua senha atual.',
            'new_password.required'         => 'Informe a nova senha.',
            'new_password.min'              => 'A nova senha deve ter no mínimo 6 caracteres.',
            'new_password.confirmed'        => 'A confirmação da senha não confere.',
        ]);

        // Verifica senha atual (MD5 — compatibilidade com legado)
        if (md5($request->input('current_password')) !== $customer->senha) {
            return redirect()->back()->withErrors(['current_password' => 'Senha atual incorreta.']);
        }

        // Grava nova senha em MD5 (mesma operação que senha.ctp do legado faz)
        $customer->senha = md5($request->input('new_password'));
        $customer->save();

        return redirect()->route('customer.password')->with('success', 'Senha alterada com sucesso!');
    }
```

- [ ] **Step 3: Commit**

```bash
git add resources/views/storefront/customer/password.blade.php app/Http/Controllers/Storefront/CustomerController.php
git commit -m "feat: cria alteração de senha do cliente (MD5, compatível com legado)"
```

---

### Task 5: Meus Endereços

**Files:**
- Create: `resources/views/storefront/customer/addresses.blade.php`
- Modify: `app/Http/Controllers/Storefront/CustomerController.php`

- [ ] **Step 1: Criar a view de endereços**

Criar `resources/views/storefront/customer/addresses.blade.php`:

```blade
{{--
    Página: Meus Endereços
    Lista endereços do cliente com opção de remover e adicionar.
    Dados do banco legado (tabela enderecos).
--}}
@extends('storefront.customer.layout')

@section('title', 'Meus Endereços - ' . config('app.name'))

@section('customer-content')

    <h2>Meus Endereços</h2>
    <p class="section-subtitle">Gerencie seus endereços de entrega.</p>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Lista de endereços --}}
    <div class="address-list">
        @forelse($addresses as $address)
            <div class="address-card {{ $address->end_principal ? 'is-main' : '' }}">
                <div class="address-info">
                    <strong>{{ $address->logradouro }}, {{ $address->logradouro_complemento_numero }}</strong>
                    @if($address->end_principal)
                        <span class="badge-principal">Principal</span>
                    @endif
                    <br>
                    @if($address->logradouro_complemento)
                        {{ $address->logradouro_complemento }} —
                    @endif
                    {{ $address->bairro }}<br>
                    {{ $address->cidade }}/{{ $address->uf }} — CEP {{ $address->cep }}
                </div>
                <div class="address-actions">
                    <form action="{{ route('customer.address.delete', $address->id) }}" method="POST"
                          onsubmit="return confirm('Deseja remover este endereço?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-remove">
                            <i class="fa fa-trash"></i> Remover
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <p style="color: #999; text-align: center; padding: 20px;">Nenhum endereço cadastrado.</p>
        @endforelse
    </div>

    {{-- Botão para abrir formulário --}}
    <button type="button" class="btn-add-address" id="btn-toggle-address-form">
        <i class="fa fa-plus"></i> Adicionar novo endereço
    </button>

    {{-- Formulário novo endereço (toggle) --}}
    <div class="address-form-wrapper" id="address-form-wrapper">
        <form action="{{ route('customer.address.store') }}" method="POST" class="customer-form">
            @csrf

            <div class="row">
                <div class="col-xs-12 col-md-4">
                    <div class="form-group">
                        <label for="addr_cep">CEP *</label>
                        <input type="text" name="zip_code" id="addr_cep" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12 col-md-8">
                    <div class="form-group">
                        <label for="addr_street">Rua/Avenida *</label>
                        <input type="text" name="street" id="addr_street" class="form-control" required>
                    </div>
                </div>
                <div class="col-xs-12 col-md-4">
                    <div class="form-group">
                        <label for="addr_number">Número *</label>
                        <input type="text" name="number" id="addr_number" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12 col-md-4">
                    <div class="form-group">
                        <label for="addr_complement">Complemento</label>
                        <input type="text" name="complement" id="addr_complement" class="form-control">
                    </div>
                </div>
                <div class="col-xs-12 col-md-4">
                    <div class="form-group">
                        <label for="addr_neighborhood">Bairro *</label>
                        <input type="text" name="neighborhood" id="addr_neighborhood" class="form-control" required>
                    </div>
                </div>
                <div class="col-xs-12 col-md-4">
                    <div class="form-group">
                        <label for="addr_city">Cidade *</label>
                        <input type="text" name="city" id="addr_city" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12 col-md-2">
                    <div class="form-group">
                        <label for="addr_state">UF *</label>
                        <input type="text" name="state" id="addr_state" class="form-control" maxlength="2" required>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-save">
                <i class="fa fa-check"></i> Salvar Endereço
            </button>
            <button type="button" class="btn-add-address" id="btn-cancel-address" style="margin-left: 10px;">
                Cancelar
            </button>
        </form>
    </div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Máscara de CEP
    $('#addr_cep').inputmask('99999-999');

    // Toggle formulário
    $('#btn-toggle-address-form').on('click', function() {
        $('#address-form-wrapper').slideDown(300);
        $(this).hide();
        $('#addr_cep').focus();
    });

    $('#btn-cancel-address').on('click', function() {
        $('#address-form-wrapper').slideUp(300);
        $('#btn-toggle-address-form').show();
    });

    // Auto-preenchimento via ViaCEP ao sair do campo CEP
    $('#addr_cep').on('blur', function() {
        var cep = $(this).val().replace(/\D/g, '');
        if (cep.length !== 8) return;

        $.getJSON('https://viacep.com.br/ws/' + cep + '/json/', function(data) {
            if (!data.erro) {
                $('#addr_street').val(data.logradouro || '');
                $('#addr_neighborhood').val(data.bairro || '');
                $('#addr_city').val(data.localidade || '');
                $('#addr_state').val(data.uf || '');
                $('#addr_number').focus();
            }
        });
    });
});
</script>
@endpush
```

- [ ] **Step 2: Adicionar métodos addresses(), deleteAddress() e storeAddress() no CustomerController**

```php
    /**
     * Lista endereços do cliente.
     * GET /minha-conta/enderecos
     */
    public function addresses(): View|RedirectResponse
    {
        $customer = auth()->user();

        if (!$customer || !($customer instanceof Pessoa)) {
            return redirect()->route('login');
        }

        $addresses = Endereco::where('pessoa_id', $customer->id)
            ->where('ativo', 1)
            ->orderByDesc('end_principal')
            ->get();

        $activeMenu = 'addresses';

        return view('storefront.customer.addresses', compact('customer', 'addresses', 'activeMenu'));
    }

    /**
     * Remove endereço (soft delete — marca ativo=0).
     * DELETE /minha-conta/enderecos/{id}
     */
    public function deleteAddress(int $id): RedirectResponse
    {
        $customer = auth()->user();

        if (!$customer || !($customer instanceof Pessoa)) {
            return redirect()->route('login');
        }

        // Garante que o endereço pertence ao cliente logado
        $address = Endereco::where('id', $id)
            ->where('pessoa_id', $customer->id)
            ->first();

        if (!$address) {
            return redirect()->route('customer.addresses')->with('error', 'Endereço não encontrado.');
        }

        // Soft delete — mesma operação que EnderecosController::remover() do legado
        $address->ativo = 0;
        $address->save();

        return redirect()->route('customer.addresses')->with('success', 'Endereço removido com sucesso.');
    }

    /**
     * Cadastra novo endereço.
     * POST /minha-conta/enderecos
     */
    public function storeAddress(Request $request): RedirectResponse
    {
        $customer = auth()->user();

        if (!$customer || !($customer instanceof Pessoa)) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'zip_code'     => 'required|string|max:10',
            'street'       => 'required|string|max:190',
            'number'       => 'required|string|max:40',
            'complement'   => 'nullable|string|max:80',
            'neighborhood' => 'required|string|max:90',
            'city'         => 'required|string|max:90',
            'state'        => 'required|string|size:2',
        ]);

        Endereco::create([
            'pessoa_id'                     => $customer->id,
            'cep'                           => $validated['zip_code'],
            'logradouro'                    => $validated['street'],
            'logradouro_complemento_numero' => $validated['number'],
            'logradouro_complemento'        => $validated['complement'] ?? null,
            'bairro'                        => $validated['neighborhood'],
            'cidade'                        => $validated['city'],
            'uf'                            => $validated['state'],
            'ativo'                         => 1,
        ]);

        return redirect()->route('customer.addresses')->with('success', 'Endereço cadastrado com sucesso!');
    }
```

- [ ] **Step 3: Commit**

```bash
git add resources/views/storefront/customer/addresses.blade.php app/Http/Controllers/Storefront/CustomerController.php
git commit -m "feat: cria gestão de endereços (listar, adicionar, remover) com ViaCEP"
```

---

### Task 6: Adaptar Meus Pedidos para usar layout sidebar

**Files:**
- Modify: `resources/views/storefront/customer/orders.blade.php`
- Modify: `resources/views/storefront/customer/order-detail.blade.php`
- Modify: `app/Http/Controllers/Storefront/CustomerController.php`

- [ ] **Step 1: Reescrever orders.blade.php para usar layout sidebar**

Substituir o `@extends('layouts.storefront')` por `@extends('storefront.customer.layout')` e mover o conteúdo para `@section('customer-content')`. Remover o banner interno (já está no layout) e estilos inline redundantes (já estão no CSS dedicado).

- [ ] **Step 2: Reescrever order-detail.blade.php para usar layout sidebar**

Mesma adaptação — trocar extends, usar `@section('customer-content')`, remover banner duplicado.

- [ ] **Step 3: Adicionar `$activeMenu = 'orders'` nos métodos orders() e orderDetail()**

Nos métodos existentes `orders()` e `orderDetail()` do CustomerController, adicionar `$activeMenu = 'orders'` e incluir no `compact()`.

- [ ] **Step 4: Commit**

```bash
git add resources/views/storefront/customer/orders.blade.php resources/views/storefront/customer/order-detail.blade.php app/Http/Controllers/Storefront/CustomerController.php
git commit -m "feat: adapta pedidos para layout sidebar da área do cliente"
```

---

## Resumo de Impacto

| Item | Detalhe |
|------|---------|
| Arquivos criados | 5 (CSS, layout, dashboard, perfil, senha, endereços) |
| Arquivos modificados | 5 (controller, rotas, layout, header, orders, order-detail) |
| Banco legado | **Leitura + escrita** (UPDATE pessoas, UPDATE enderecos.ativo, INSERT enderecos) |
| Banco sync | Nenhuma alteração |
| Migrations | Nenhuma |
| Dependências | Nenhuma nova (Inputmask, ViaCEP já disponíveis) |
| Risco | Baixo — escritas replicam operações existentes do legado |
