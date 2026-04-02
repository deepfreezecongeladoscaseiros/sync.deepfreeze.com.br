# Ícones Flutuantes (WhatsApp + Instagram) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Criar ícones flutuantes de WhatsApp e Instagram configuráveis via painel admin, exibidos em position:fixed no storefront.

**Architecture:** Model singleton `FloatingButtonConfig` (banco sync, padrão `ContactSetting` com cache), controller admin com formulário edit/update, partial Blade no layout storefront com CSS inline para position:fixed. Ícones SVG nativos (sem dependência de CDN externo).

**Tech Stack:** Laravel 10, Blade, CSS3 (position:fixed), banco sync (migration)

---

## Mapeamento de Arquivos

| Arquivo | Ação | Responsabilidade |
|---------|------|------------------|
| `database/migrations/xxxx_create_floating_button_config_table.php` | **Criar** | Tabela singleton com campos de configuração |
| `app/Models/FloatingButtonConfig.php` | **Criar** | Model singleton com cache (padrão ContactSetting) |
| `app/Http/Controllers/Admin/FloatingButtonController.php` | **Criar** | Controller admin edit/update (padrão CookieConsentController) |
| `resources/views/admin/floating-buttons/edit.blade.php` | **Criar** | Formulário de configuração no painel admin |
| `resources/views/storefront/partials/floating-buttons.blade.php` | **Criar** | Partial com ícones flutuantes (CSS position:fixed) |
| `resources/views/layouts/storefront.blade.php` | **Modificar** | Incluir o partial antes do `</body>` |
| `routes/web.php` | **Modificar** | Adicionar rotas admin edit/update |
| `config/adminlte.php` | **Modificar** | Adicionar item no menu LOJA VIRTUAL |

---

### Task 1: Migration — tabela `floating_button_config`

**Files:**
- Create: `database/migrations/2026_04_02_000000_create_floating_button_config_table.php`

- [ ] **Step 1: Criar a migration**

```bash
php artisan make:migration create_floating_button_config_table
```

Editar o arquivo gerado com:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Configuração dos ícones flutuantes (WhatsApp + Instagram)
 *
 * Tabela singleton (1 registro) — armazena configurações de exibição
 * dos botões flutuantes no storefront. Banco sync (configuração visual).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('floating_button_config', function (Blueprint $table) {
            $table->id();

            // Posição dos ícones na tela: 'left' ou 'right'
            $table->enum('position', ['left', 'right'])
                  ->default('right')
                  ->comment('Posição dos ícones: left = canto esquerdo, right = canto direito');

            // WhatsApp — se vazio, ícone não aparece
            $table->string('whatsapp_number', 20)
                  ->nullable()
                  ->comment('Número do WhatsApp com DDI+DDD (ex: 5521934783000). Vazio = ícone oculto');

            $table->string('whatsapp_message', 255)
                  ->nullable()
                  ->comment('Texto pré-configurado enviado ao abrir o WhatsApp');

            // Instagram — se vazio, ícone não aparece
            $table->string('instagram_url', 255)
                  ->nullable()
                  ->comment('URL do perfil do Instagram (ex: https://instagram.com/deepfreezecongelados). Vazio = ícone oculto');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('floating_button_config');
    }
};
```

- [ ] **Step 2: Executar a migration**

Run: `php artisan migrate`
Expected: `DONE` para `create_floating_button_config_table`

- [ ] **Step 3: Commit**

```bash
git add database/migrations/*floating_button_config*
git commit -m "feat: cria migration floating_button_config para ícones flutuantes

Tabela singleton com: position (left/right), whatsapp_number,
whatsapp_message, instagram_url. Banco sync (configuração visual).

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>"
```

---

### Task 2: Model `FloatingButtonConfig`

**Files:**
- Create: `app/Models/FloatingButtonConfig.php`

- [ ] **Step 1: Criar o model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Model: Configuração dos Ícones Flutuantes (WhatsApp + Instagram)
 *
 * Padrão singleton com cache (mesmo padrão de ContactSetting).
 * Armazena configurações dos botões flutuantes exibidos no storefront.
 * Banco: sync (tabela floating_button_config).
 */
class FloatingButtonConfig extends Model
{
    protected $table = 'floating_button_config';

    protected $fillable = [
        'position',
        'whatsapp_number',
        'whatsapp_message',
        'instagram_url',
    ];

    protected const CACHE_KEY = 'floating_button_config';
    protected const CACHE_TTL = 3600; // 1 hora

    /**
     * Retorna o registro único de configuração (singleton com cache).
     * Cria com valores padrão se não existir.
     */
    public static function getConfig(): self
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $config = self::first();

            if (!$config) {
                $config = self::create([
                    'position'         => 'right',
                    'whatsapp_number'  => null,
                    'whatsapp_message' => 'Olá! Gostaria de saber mais sobre os produtos da Deep Freeze.',
                    'instagram_url'    => null,
                ]);
            }

            return $config;
        });
    }

    /**
     * Limpa o cache ao salvar/deletar.
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            Cache::forget(self::CACHE_KEY);
        });
    }

    /**
     * Retorna a URL completa do WhatsApp API com número e mensagem.
     * Retorna null se o número não estiver configurado.
     */
    public function getWhatsappUrl(): ?string
    {
        if (empty($this->whatsapp_number)) {
            return null;
        }

        // Remove caracteres não numéricos
        $number = preg_replace('/[^0-9]/', '', $this->whatsapp_number);
        $message = $this->whatsapp_message ?? '';

        return "https://api.whatsapp.com/send?phone={$number}&text=" . urlencode($message);
    }

    /**
     * Verifica se o ícone do WhatsApp deve ser exibido.
     */
    public function showWhatsapp(): bool
    {
        return !empty($this->whatsapp_number);
    }

    /**
     * Verifica se o ícone do Instagram deve ser exibido.
     */
    public function showInstagram(): bool
    {
        return !empty($this->instagram_url);
    }

    /**
     * Verifica se pelo menos um ícone deve ser exibido.
     */
    public function hasAnyButton(): bool
    {
        return $this->showWhatsapp() || $this->showInstagram();
    }
}
```

- [ ] **Step 2: Testar via tinker**

Run: `php artisan tinker --execute="$c = App\Models\FloatingButtonConfig::getConfig(); echo 'position: ' . $c->position . ' | whatsapp: ' . ($c->whatsapp_number ?? 'null') . ' | instagram: ' . ($c->instagram_url ?? 'null');"`

Expected: `position: right | whatsapp: null | instagram: null`

- [ ] **Step 3: Commit**

```bash
git add app/Models/FloatingButtonConfig.php
git commit -m "feat: cria model FloatingButtonConfig (singleton com cache)

Padrão ContactSetting: getConfig(), cache 1h, auto-clear ao salvar.
Helpers: getWhatsappUrl(), showWhatsapp(), showInstagram().

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>"
```

---

### Task 3: Controller Admin + View + Rotas + Menu

**Files:**
- Create: `app/Http/Controllers/Admin/FloatingButtonController.php`
- Create: `resources/views/admin/floating-buttons/edit.blade.php`
- Modify: `routes/web.php`
- Modify: `config/adminlte.php`

- [ ] **Step 1: Criar o controller**

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FloatingButtonConfig;
use Illuminate\Http\Request;

/**
 * Controller: Configuração dos Ícones Flutuantes (Admin)
 *
 * Formulário singleton edit/update (padrão CookieConsentController).
 * Permite configurar WhatsApp, Instagram e posição dos ícones.
 */
class FloatingButtonController extends Controller
{
    /**
     * Exibe formulário de configuração dos ícones flutuantes.
     */
    public function edit()
    {
        $config = FloatingButtonConfig::getConfig();
        return view('admin.floating-buttons.edit', compact('config'));
    }

    /**
     * Atualiza configurações dos ícones flutuantes.
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'position'         => 'required|in:left,right',
            'whatsapp_number'  => 'nullable|string|max:20',
            'whatsapp_message' => 'nullable|string|max:255',
            'instagram_url'    => 'nullable|url|max:255',
        ]);

        $config = FloatingButtonConfig::getConfig();
        $config->update($data);

        return redirect()->route('admin.floating-buttons.edit')
            ->with('success', 'Configurações dos ícones flutuantes atualizadas com sucesso!');
    }
}
```

- [ ] **Step 2: Criar a view do formulário**

Criar `resources/views/admin/floating-buttons/edit.blade.php`:

```blade
@extends('adminlte::page')

@section('title', 'Ícones Flutuantes')

@section('content_header')
    <h1>Ícones Flutuantes</h1>
@stop

@section('content')
    <div class="card">
        <form action="{{ route('admin.floating-buttons.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                {{-- Posição dos ícones --}}
                <div class="form-group">
                    <label>Posição na tela</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="position" id="posLeft"
                                   value="left" {{ old('position', $config->position) === 'left' ? 'checked' : '' }}>
                            <label class="form-check-label" for="posLeft">
                                <i class="fas fa-arrow-left"></i> Esquerda
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="position" id="posRight"
                                   value="right" {{ old('position', $config->position) === 'right' ? 'checked' : '' }}>
                            <label class="form-check-label" for="posRight">
                                <i class="fas fa-arrow-right"></i> Direita
                            </label>
                        </div>
                    </div>
                    @error('position')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <hr>

                {{-- WhatsApp --}}
                <h5><i class="fab fa-whatsapp text-success"></i> WhatsApp</h5>
                <p class="text-muted small">Deixe o número em branco para ocultar o ícone do WhatsApp.</p>

                <div class="form-group">
                    <label for="whatsapp_number">Número do WhatsApp (com DDI e DDD)</label>
                    <input type="text" name="whatsapp_number" id="whatsapp_number"
                           class="form-control @error('whatsapp_number') is-invalid @enderror"
                           value="{{ old('whatsapp_number', $config->whatsapp_number) }}"
                           placeholder="Ex: 5521934783000">
                    <small class="form-text text-muted">Formato: DDI + DDD + número (apenas números)</small>
                    @error('whatsapp_number')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="whatsapp_message">Mensagem pré-configurada</label>
                    <input type="text" name="whatsapp_message" id="whatsapp_message"
                           class="form-control @error('whatsapp_message') is-invalid @enderror"
                           value="{{ old('whatsapp_message', $config->whatsapp_message) }}"
                           placeholder="Ex: Olá! Gostaria de saber mais sobre os produtos.">
                    <small class="form-text text-muted">Texto enviado automaticamente ao abrir o chat</small>
                    @error('whatsapp_message')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <hr>

                {{-- Instagram --}}
                <h5><i class="fab fa-instagram text-danger"></i> Instagram</h5>
                <p class="text-muted small">Deixe o link em branco para ocultar o ícone do Instagram.</p>

                <div class="form-group">
                    <label for="instagram_url">Link do perfil do Instagram</label>
                    <input type="url" name="instagram_url" id="instagram_url"
                           class="form-control @error('instagram_url') is-invalid @enderror"
                           value="{{ old('instagram_url', $config->instagram_url) }}"
                           placeholder="Ex: https://www.instagram.com/deepfreezecongelados/">
                    @error('instagram_url')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Salvar Configurações
                </button>
            </div>
        </form>
    </div>
@stop
```

- [ ] **Step 3: Adicionar rotas**

Em `routes/web.php`, dentro do grupo admin (após as rotas de cookie-consent), adicionar:

```php
    // Ícones flutuantes (WhatsApp + Instagram)
    Route::get('floating-buttons', [App\Http\Controllers\Admin\FloatingButtonController::class, 'edit'])->name('floating-buttons.edit');
    Route::put('floating-buttons', [App\Http\Controllers\Admin\FloatingButtonController::class, 'update'])->name('floating-buttons.update');
```

- [ ] **Step 4: Adicionar ao menu admin**

Em `config/adminlte.php`, na seção LOJA VIRTUAL, após o item "Redes Sociais", adicionar:

```php
        ['text' => 'Ícones Flutuantes', 'route'  => 'admin.floating-buttons.edit', 'icon' => 'fas fa-fw fa-comments'],
```

- [ ] **Step 5: Testar acesso ao formulário**

Acessar `http://127.0.0.1:8000/admin/floating-buttons` e verificar que o formulário carrega com valores padrão.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Admin/FloatingButtonController.php resources/views/admin/floating-buttons/edit.blade.php routes/web.php config/adminlte.php
git commit -m "feat: cria painel admin para configurar ícones flutuantes

Formulário singleton edit/update com:
- Posição (esquerda/direita)
- WhatsApp (número + mensagem pré-configurada)
- Instagram (URL do perfil)
Menu na seção LOJA VIRTUAL.

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>"
```

---

### Task 4: Partial Blade dos ícones flutuantes no storefront

**Files:**
- Create: `resources/views/storefront/partials/floating-buttons.blade.php`
- Modify: `resources/views/layouts/storefront.blade.php`

- [ ] **Step 1: Criar o partial**

Criar `resources/views/storefront/partials/floating-buttons.blade.php`:

```blade
{{--
    Partial: Ícones Flutuantes (WhatsApp + Instagram)

    Exibe botões fixos no canto da tela para contato rápido.
    Configurável via painel admin (FloatingButtonConfig).

    Ordem vertical (de cima para baixo): Instagram, WhatsApp.
    Posição (left/right) definida na configuração.
    Ícone só aparece se o respectivo campo estiver preenchido.
--}}

@php
    $floatingConfig = \App\Models\FloatingButtonConfig::getConfig();
@endphp

@if($floatingConfig->hasAnyButton())
    <div class="floating-buttons floating-buttons--{{ $floatingConfig->position }}">

        {{-- Instagram (aparece acima do WhatsApp) --}}
        @if($floatingConfig->showInstagram())
            <a href="{{ $floatingConfig->instagram_url }}"
               target="_blank"
               rel="noopener noreferrer"
               class="floating-btn floating-btn--instagram"
               title="Siga-nos no Instagram">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="#fff">
                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                </svg>
            </a>
        @endif

        {{-- WhatsApp (aparece abaixo do Instagram) --}}
        @if($floatingConfig->showWhatsapp())
            <a href="{{ $floatingConfig->getWhatsappUrl() }}"
               target="_blank"
               rel="noopener noreferrer"
               class="floating-btn floating-btn--whatsapp"
               title="Fale conosco pelo WhatsApp">
                <svg viewBox="0 0 24 24" width="26" height="26" fill="#fff">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
            </a>
        @endif

    </div>

    <style>
        /* Ícones flutuantes — position fixed no canto da tela */
        .floating-buttons {
            position: fixed;
            bottom: 100px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        /* Posição: direita ou esquerda */
        .floating-buttons--right { right: 20px; }
        .floating-buttons--left { left: 20px; }

        /* Botão base */
        .floating-btn {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            text-decoration: none;
        }

        .floating-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        /* WhatsApp: verde oficial */
        .floating-btn--whatsapp {
            background: #25D366;
        }

        /* Instagram: gradiente oficial */
        .floating-btn--instagram {
            background: radial-gradient(circle at 30% 107%, #fdf497 0%, #fdf497 5%, #fd5949 45%, #d6249f 60%, #285AEB 90%);
        }

        /* Mobile: ajusta posição para não sobrepor barra fixa */
        @media (max-width: 767px) {
            .floating-buttons {
                bottom: 80px;
            }

            .floating-btn {
                width: 46px;
                height: 46px;
            }

            .floating-btn svg {
                width: 22px;
                height: 22px;
            }
        }
    </style>
@endif
```

- [ ] **Step 2: Incluir o partial no layout storefront**

Em `resources/views/layouts/storefront.blade.php`, adicionar antes do fechamento `</body>` (após os scripts, antes do `@stack('scripts')`... na verdade, logo antes do `</body>`):

Localizar a linha com `</body>` e adicionar o include logo antes:

```blade
    {{-- Ícones Flutuantes (WhatsApp + Instagram) --}}
    @include('storefront.partials.floating-buttons')

</body>
```

- [ ] **Step 3: Testar visualmente**

1. Acessar `http://127.0.0.1:8000/admin/floating-buttons`
2. Preencher: WhatsApp `5521934783000`, mensagem `Olá!`, Instagram `https://www.instagram.com/deepfreezecongelados/`, posição `Direita`
3. Salvar
4. Acessar o storefront e verificar que os dois ícones aparecem no canto direito
5. Testar clique no WhatsApp (abre api.whatsapp.com) e Instagram (abre perfil)
6. Remover o número do WhatsApp, salvar, verificar que só o Instagram aparece
7. Remover o Instagram também, verificar que nenhum ícone aparece

- [ ] **Step 4: Commit**

```bash
git add resources/views/storefront/partials/floating-buttons.blade.php resources/views/layouts/storefront.blade.php
git commit -m "feat: adiciona ícones flutuantes de WhatsApp e Instagram no storefront

Position:fixed no canto da tela, configurável via admin.
SVG nativo, cores oficiais, responsivo. Ordem: Instagram (cima), WhatsApp (baixo).
Ícones só aparecem se configurados no painel.

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>"
```

---

### Task 5: Deploy e verificação em produção

**Files:** Nenhum

- [ ] **Step 1: Push**

```bash
git push origin main
```

- [ ] **Step 2: Deploy no kicolApps**

```bash
ssh kicolApps "cd /var/www/sync.deepfreeze.com.br && git pull origin main && php artisan migrate --force && php artisan optimize:clear"
```

Expected: Migration executada, caches limpos.

- [ ] **Step 3: Configurar via admin em produção**

Acessar o painel admin em produção → Ícones Flutuantes → preencher WhatsApp e Instagram → salvar.

- [ ] **Step 4: Verificar no storefront em produção**

Confirmar que os ícones aparecem corretamente no canto da tela.

---

## Resumo de Impacto

| Item | Detalhe |
|------|---------|
| Arquivos criados | 4 (migration, model, controller, 2 views) |
| Arquivos modificados | 3 (layout, routes, adminlte config) |
| Banco legado | Nenhuma alteração |
| Banco sync | 1 tabela nova (`floating_button_config`) |
| Dependências | Nenhuma (SVG nativo, CSS puro) |
| Risco | Baixo — feature isolada, não afeta nada existente |
