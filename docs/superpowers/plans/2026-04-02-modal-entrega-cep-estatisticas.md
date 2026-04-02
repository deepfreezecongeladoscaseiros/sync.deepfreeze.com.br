# Modal "Entrega na minha região" + Estatísticas de CEP — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Corrigir o modal "Entrega na minha região" (atualmente 404), registrar cada consulta de CEP no banco sync com dados de localização (via ViaCEP), e criar um painel de estatísticas no admin para a equipe de marketing analisar demanda por região.

**Architecture:** Modal Bootstrap nativo no layout storefront (substituindo o iframe fancybox do legado) que chama o endpoint AJAX existente `GET /entrega/consultar-cep`. Cada consulta é logada na tabela `cep_queries_log` (banco sync) com enriquecimento de estado/cidade/bairro via API ViaCEP. Painel admin com listagem filtrada, totalizadores e exportação visual.

**Tech Stack:** Laravel 10, Bootstrap 3 modal, jQuery AJAX, Inputmask (já carregado), ViaCEP API (pública, sem auth), AdminLTE 3 (admin)

---

## Mapeamento de Arquivos

| Arquivo | Ação | Responsabilidade |
|---------|------|------------------|
| `database/migrations/xxxx_create_cep_queries_log_table.php` | **Criar** | Tabela de log de consultas CEP (banco sync) |
| `app/Models/CepQueryLog.php` | **Criar** | Model para log de consultas |
| `app/Services/ViaCepService.php` | **Criar** | Consulta API ViaCEP com cache 24h |
| `app/Http/Controllers/Storefront/ShippingController.php` | **Modificar** | Adicionar log após consulta de CEP |
| `resources/views/storefront/partials/modal-entrega.blade.php` | **Criar** | Modal HTML do storefront |
| `resources/views/layouts/storefront.blade.php` | **Modificar** | Incluir modal + novo JS para verificaEntregaCep |
| `app/Http/Controllers/Admin/CepStatsController.php` | **Criar** | Controller admin para estatísticas |
| `resources/views/admin/cep-stats/index.blade.php` | **Criar** | View admin com filtros e tabela |
| `routes/web.php` | **Modificar** | Rotas admin para estatísticas |
| `config/adminlte.php` | **Modificar** | Menu ESTATÍSTICAS no sidebar |

---

### Task 1: Migration + Model para log de consultas de CEP

**Files:**
- Create: `database/migrations/2026_04_02_000001_create_cep_queries_log_table.php`
- Create: `app/Models/CepQueryLog.php`

- [ ] **Step 1: Criar a migration**

```bash
php artisan make:migration create_cep_queries_log_table
```

Editar o arquivo gerado:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Log de consultas de CEP
 *
 * Registra cada consulta de "Entrega na minha região" feita no storefront.
 * Banco sync — dados de marketing/estatísticas.
 * Enriquecido com estado, cidade e bairro via ViaCEP.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cep_queries_log', function (Blueprint $table) {
            $table->id();
            $table->string('cep', 9)->comment('CEP consultado (formato 00000-000 ou 00000000)');
            $table->boolean('atendido')->comment('true = CEP está em região de entrega');
            $table->string('estado', 2)->nullable()->comment('UF obtida via ViaCEP');
            $table->string('cidade', 100)->nullable()->comment('Cidade obtida via ViaCEP');
            $table->string('bairro', 100)->nullable()->comment('Bairro obtido via ViaCEP');
            $table->unsignedInteger('regiao_id')->nullable()->comment('ID da entregas_regioes (se atendido)');
            $table->unsignedInteger('loja_id')->nullable()->comment('ID da loja que atende (se atendido)');
            $table->timestamp('created_at')->useCurrent()->comment('Data/hora da consulta');

            // Índices para consultas do painel de estatísticas
            $table->index('created_at');
            $table->index('atendido');
            $table->index('estado');
            $table->index('cidade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cep_queries_log');
    }
};
```

- [ ] **Step 2: Criar o model**

Criar `app/Models/CepQueryLog.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Log de consultas de CEP
 *
 * Registra consultas de "Entrega na minha região" no storefront.
 * Banco: sync (tabela cep_queries_log).
 * Sem updated_at — registros são imutáveis (apenas INSERT).
 */
class CepQueryLog extends Model
{
    public $timestamps = false;

    protected $table = 'cep_queries_log';

    protected $fillable = [
        'cep',
        'atendido',
        'estado',
        'cidade',
        'bairro',
        'regiao_id',
        'loja_id',
        'created_at',
    ];

    protected $casts = [
        'atendido'   => 'boolean',
        'created_at' => 'datetime',
    ];
}
```

- [ ] **Step 3: Rodar migration**

Run: `php artisan migrate`

- [ ] **Step 4: Commit**

```bash
git add database/migrations/*cep_queries_log* app/Models/CepQueryLog.php
git commit -m "feat: cria tabela cep_queries_log para estatísticas de consulta de CEP"
```

---

### Task 2: ViaCepService — consulta de localização com cache

**Files:**
- Create: `app/Services/ViaCepService.php`

- [ ] **Step 1: Criar o service**

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Service: Consulta de CEP via API ViaCEP
 *
 * API pública, gratuita, sem autenticação.
 * Retorna estado, cidade e bairro para um CEP.
 * Cache de 24h — mesmo CEP = mesma localidade.
 */
class ViaCepService
{
    /**
     * Consulta dados de localização de um CEP via ViaCEP.
     *
     * @param string $cep CEP (apenas dígitos, 8 caracteres)
     * @return array|null ['uf' => 'RJ', 'localidade' => 'Rio de Janeiro', 'bairro' => 'Tijuca'] ou null
     */
    public function lookup(string $cep): ?array
    {
        $cepLimpo = preg_replace('/\D/', '', $cep);

        if (strlen($cepLimpo) !== 8) {
            return null;
        }

        $cacheKey = "viacep_{$cepLimpo}";

        return Cache::remember($cacheKey, 86400, function () use ($cepLimpo) {
            try {
                $response = Http::timeout(5)->get("https://viacep.com.br/ws/{$cepLimpo}/json/");

                if ($response->failed()) {
                    return null;
                }

                $data = $response->json();

                // ViaCEP retorna {"erro": true} quando CEP não existe
                if (isset($data['erro']) && $data['erro'] === true) {
                    return null;
                }

                return [
                    'uf'         => $data['uf'] ?? null,
                    'localidade' => $data['localidade'] ?? null,
                    'bairro'     => $data['bairro'] ?? null,
                    'logradouro' => $data['logradouro'] ?? null,
                ];
            } catch (\Exception $e) {
                // Se ViaCEP estiver fora, não bloqueia — retorna null
                return null;
            }
        });
    }
}
```

- [ ] **Step 2: Testar via tinker**

Run: `php artisan tinker --execute="$s = app(App\Services\ViaCepService::class); $r = $s->lookup('20551030'); echo $r['uf'] . ' | ' . $r['localidade'] . ' | ' . $r['bairro'];"`

Expected: `RJ | Rio de Janeiro | Tijuca` (ou similar)

- [ ] **Step 3: Commit**

```bash
git add app/Services/ViaCepService.php
git commit -m "feat: cria ViaCepService para enriquecer dados de CEP com localização"
```

---

### Task 3: Modificar ShippingController para logar consultas

**Files:**
- Modify: `app/Http/Controllers/Storefront/ShippingController.php`

- [ ] **Step 1: Adicionar log na consulta de CEP**

Adicionar imports no topo:

```php
use App\Models\CepQueryLog;
use App\Services\ViaCepService;
```

Substituir o método `consultarCep()` inteiro por:

```php
    /**
     * Consulta CEP: verifica se é atendido e retorna região/loja.
     * Registra a consulta no log de estatísticas (banco sync).
     * GET /entrega/consultar-cep?cep=20551030
     */
    public function consultarCep(Request $request): JsonResponse
    {
        $cep = $request->input('cep', '');
        $result = $this->shippingService->lookupCep($cep);

        $atendido = $result !== null && $result['loja'] !== null;

        // Enriquece com dados de localização via ViaCEP (cache 24h)
        $viaCep = app(ViaCepService::class)->lookup($cep);

        // Registra consulta no log de estatísticas (banco sync)
        CepQueryLog::create([
            'cep'        => preg_replace('/\D/', '', $cep),
            'atendido'   => $atendido,
            'estado'     => $viaCep['uf'] ?? null,
            'cidade'     => $viaCep['localidade'] ?? null,
            'bairro'     => $viaCep['bairro'] ?? null,
            'regiao_id'  => $atendido ? $result['regiao']?->id : null,
            'loja_id'    => $atendido ? $result['loja']?->id : null,
            'created_at' => now(),
        ]);

        if (!$atendido) {
            return response()->json([
                'atendido' => false,
                'mensagem' => 'Infelizmente ainda não atendemos sua região. Registramos seu interesse para futura expansão.',
            ]);
        }

        return response()->json([
            'atendido' => true,
            'regiao'   => $result['regiao']?->nome,
            'loja'     => $result['loja'] ? [
                'id'   => $result['loja']->id,
                'nome' => $result['loja']->nome,
            ] : null,
            'endereco' => $viaCep ? ($viaCep['bairro'] . ', ' . $viaCep['localidade'] . ' - ' . $viaCep['uf']) : null,
        ]);
    }
```

- [ ] **Step 2: Commit**

```bash
git add app/Http/Controllers/Storefront/ShippingController.php
git commit -m "feat: adiciona log de consultas de CEP com enriquecimento ViaCEP"
```

---

### Task 4: Modal "Entrega na minha região" no storefront

**Files:**
- Create: `resources/views/storefront/partials/modal-entrega.blade.php`
- Modify: `resources/views/layouts/storefront.blade.php`

- [ ] **Step 1: Criar o modal**

Criar `resources/views/storefront/partials/modal-entrega.blade.php`:

```blade
{{--
    Modal: Entrega na minha região

    Consulta se o CEP é atendido via AJAX (endpoint já existente).
    Substitui o iframe fancybox do legado que gerava 404.
--}}
<div class="modal fade" id="modalEntregaCep" tabindex="-1" role="dialog" aria-labelledby="modalEntregaCepLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">

            {{-- Header --}}
            <div class="modal-header" style="background: var(--color-primary, #013E3B); color: #fff; border: none; padding: 18px 24px;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar" style="color: #fff; opacity: 0.8; font-size: 24px;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modalEntregaCepLabel" style="font-weight: 700;">
                    <i class="fa fa-map-marker"></i> Entrega na minha região?
                </h4>
            </div>

            {{-- Body --}}
            <div class="modal-body" style="padding: 24px;">

                {{-- Formulário de consulta --}}
                <div id="entrega-form-section">
                    <p style="color: #555; margin-bottom: 16px;">Informe seu CEP para verificar se realizamos entregas na sua região:</p>
                    <div class="input-group" style="max-width: 300px;">
                        <input type="text" id="entrega-cep-input" class="form-control input-lg"
                               placeholder="00000-000" style="border-radius: 30px 0 0 30px; height: 48px;">
                        <span class="input-group-btn">
                            <button type="button" id="entrega-cep-btn" class="btn btn-lg"
                                    style="background: var(--color-primary, #013E3B); color: #fff; border-radius: 0 30px 30px 0; height: 48px; padding: 0 20px;">
                                <i class="fa fa-search" id="entrega-cep-icon"></i>
                            </button>
                        </span>
                    </div>
                    <small class="text-muted" style="display: block; margin-top: 8px;">
                        Não sabe seu CEP? <a href="https://buscacepinter.correios.com.br/app/endereco/index.php" target="_blank" rel="noopener">Consulte aqui</a>
                    </small>
                </div>

                {{-- Resultado: Atendido --}}
                <div id="entrega-result-ok" style="display: none; text-align: center; padding: 20px 0;">
                    <div style="width: 60px; height: 60px; border-radius: 50%; background: #e8f5e9; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center;">
                        <i class="fa fa-check" style="font-size: 28px; color: #28a745;"></i>
                    </div>
                    <h4 style="color: #28a745; font-weight: 700; margin-bottom: 8px;">Entregamos na sua região!</h4>
                    <p style="color: #555;" id="entrega-result-endereco"></p>
                </div>

                {{-- Resultado: Não atendido --}}
                <div id="entrega-result-nao" style="display: none; text-align: center; padding: 20px 0;">
                    <div style="width: 60px; height: 60px; border-radius: 50%; background: #fff3e0; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center;">
                        <i class="fa fa-exclamation-triangle" style="font-size: 28px; color: #ff9800;"></i>
                    </div>
                    <h4 style="color: #e65100; font-weight: 700; margin-bottom: 8px;">Ainda não atendemos sua região</h4>
                    <p style="color: #555;">Registramos seu interesse! Estamos sempre expandindo nossas áreas de entrega.</p>
                </div>

                {{-- Resultado: Erro --}}
                <div id="entrega-result-erro" style="display: none; text-align: center; padding: 20px 0;">
                    <p style="color: #e74c3c;"><i class="fa fa-times-circle"></i> Ocorreu um erro ao consultar. Tente novamente.</p>
                </div>

            </div>

            {{-- Footer --}}
            <div class="modal-footer" style="border: none; padding: 12px 24px;">
                <button type="button" id="entrega-nova-consulta" class="btn" style="display: none; color: var(--color-primary, #013E3B);">
                    <i class="fa fa-refresh"></i> Consultar outro CEP
                </button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
            </div>

        </div>
    </div>
</div>
```

- [ ] **Step 2: Incluir modal e substituir JS no layout**

Em `resources/views/layouts/storefront.blade.php`, adicionar ANTES do `@stack('scripts')`:

```blade
    {{-- Modal: Entrega na minha região --}}
    @include('storefront.partials.modal-entrega')
```

E adicionar APÓS o `@stack('scripts')` (antes dos ícones flutuantes):

```blade
    {{-- JS do modal de entrega (sobrescreve verificaEntregaCep do JS legado) --}}
    <script>
    // Sobrescreve a função verificaEntregaCep() que existia no JS minificado
    // e tentava abrir um iframe fancybox que gerava 404
    function verificaEntregaCep() {
        // Reset do modal: mostra formulário, esconde resultados
        $('#entrega-form-section').show();
        $('#entrega-result-ok, #entrega-result-nao, #entrega-result-erro').hide();
        $('#entrega-nova-consulta').hide();
        $('#entrega-cep-input').val('');
        $('#modalEntregaCep').modal('show');
        setTimeout(function() { $('#entrega-cep-input').focus(); }, 500);
    }

    $(document).ready(function() {
        // Máscara de CEP
        $('#entrega-cep-input').inputmask('99999-999');

        // Consulta ao clicar no botão ou pressionar Enter
        $('#entrega-cep-btn').on('click', function() { consultarEntregaCep(); });
        $('#entrega-cep-input').on('keypress', function(e) {
            if (e.which === 13) { consultarEntregaCep(); }
        });

        // Botão "Consultar outro CEP"
        $('#entrega-nova-consulta').on('click', function() {
            $('#entrega-form-section').show();
            $('#entrega-result-ok, #entrega-result-nao, #entrega-result-erro').hide();
            $(this).hide();
            $('#entrega-cep-input').val('').focus();
        });

        function consultarEntregaCep() {
            var cep = $('#entrega-cep-input').val().replace(/\D/g, '');
            if (cep.length !== 8) { return; }

            // Loading
            var $btn = $('#entrega-cep-btn');
            var $icon = $('#entrega-cep-icon');
            $btn.prop('disabled', true);
            $icon.removeClass('fa-search').addClass('fa-spinner fa-spin');

            $.ajax({
                url: '/entrega/consultar-cep',
                method: 'GET',
                data: { cep: cep },
                success: function(response) {
                    $('#entrega-form-section').hide();
                    $('#entrega-nova-consulta').show();

                    if (response.atendido) {
                        $('#entrega-result-endereco').text(response.endereco || '');
                        $('#entrega-result-ok').fadeIn(300);

                        // Atualiza texto no header
                        $('.js-entrega-msg').hide();
                        $('.js-entrega-msg-ok').show();
                        $('.js-entrega-msg-endereco').text(response.endereco || response.regiao || '');
                    } else {
                        $('#entrega-result-nao').fadeIn(300);
                    }
                },
                error: function() {
                    $('#entrega-form-section').hide();
                    $('#entrega-result-erro').fadeIn(300);
                    $('#entrega-nova-consulta').show();
                },
                complete: function() {
                    $btn.prop('disabled', false);
                    $icon.removeClass('fa-spinner fa-spin').addClass('fa-search');
                }
            });
        }
    });
    </script>
```

- [ ] **Step 3: Testar localmente**

1. Acessar `http://127.0.0.1:8000`
2. Clicar em "Entrega na minha região?" no header
3. Modal deve abrir com campo de CEP e máscara
4. Digitar CEP válido atendido (ex: 20551030) → "Entregamos na sua região!"
5. Digitar CEP não atendido (ex: 01001000 — São Paulo) → "Ainda não atendemos"
6. Verificar no banco: `SELECT * FROM cep_queries_log ORDER BY id DESC LIMIT 5;`

- [ ] **Step 4: Commit**

```bash
git add resources/views/storefront/partials/modal-entrega.blade.php resources/views/layouts/storefront.blade.php
git commit -m "feat: cria modal Entrega na minha região com consulta de CEP via AJAX

Substitui iframe fancybox que gerava 404. Modal Bootstrap nativo com
campo CEP mascarado, resultado visual (atendido/não atendido), e botão
para nova consulta. Atualiza texto no header quando CEP é atendido."
```

---

### Task 5: Painel de Estatísticas no Admin

**Files:**
- Create: `app/Http/Controllers/Admin/CepStatsController.php`
- Create: `resources/views/admin/cep-stats/index.blade.php`
- Modify: `routes/web.php`
- Modify: `config/adminlte.php`

- [ ] **Step 1: Criar o controller**

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CepQueryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Controller: Estatísticas de Consultas de CEP (Admin)
 *
 * Exibe dados de consultas de "Entrega na minha região" para
 * a equipe de marketing analisar demanda por região.
 */
class CepStatsController extends Controller
{
    public function index(Request $request)
    {
        // Filtros
        $dateFrom  = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo    = $request->input('date_to', now()->format('Y-m-d'));
        $estado    = $request->input('estado');
        $cidade    = $request->input('cidade');
        $bairro    = $request->input('bairro');
        $atendido  = $request->input('atendido'); // '', '1', '0'

        // Query base com filtro de período
        $query = CepQueryLog::whereBetween('created_at', [
            $dateFrom . ' 00:00:00',
            $dateTo . ' 23:59:59',
        ]);

        if ($estado) {
            $query->where('estado', $estado);
        }
        if ($cidade) {
            $query->where('cidade', 'like', "%{$cidade}%");
        }
        if ($bairro) {
            $query->where('bairro', 'like', "%{$bairro}%");
        }
        if ($atendido !== null && $atendido !== '') {
            $query->where('atendido', (bool) $atendido);
        }

        // Totalizadores (sobre a query filtrada)
        $statsQuery = clone $query;
        $totalConsultas   = $statsQuery->count();
        $totalAtendidas   = (clone $statsQuery)->where('atendido', true)->count();
        $totalNaoAtendidas = $totalConsultas - $totalAtendidas;
        $percentAtendidas = $totalConsultas > 0 ? round(($totalAtendidas / $totalConsultas) * 100, 1) : 0;

        // Top 5 cidades não atendidas (para decisão de expansão)
        $topCidadesNaoAtendidas = CepQueryLog::whereBetween('created_at', [
                $dateFrom . ' 00:00:00',
                $dateTo . ' 23:59:59',
            ])
            ->where('atendido', false)
            ->whereNotNull('cidade')
            ->select('cidade', 'estado', DB::raw('COUNT(*) as total'))
            ->groupBy('cidade', 'estado')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Top 5 bairros não atendidos
        $topBairrosNaoAtendidos = CepQueryLog::whereBetween('created_at', [
                $dateFrom . ' 00:00:00',
                $dateTo . ' 23:59:59',
            ])
            ->where('atendido', false)
            ->whereNotNull('bairro')
            ->where('bairro', '!=', '')
            ->select('bairro', 'cidade', 'estado', DB::raw('COUNT(*) as total'))
            ->groupBy('bairro', 'cidade', 'estado')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Lista paginada
        $logs = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        // Estados disponíveis para o filtro select
        $estados = CepQueryLog::whereNotNull('estado')
            ->distinct()
            ->orderBy('estado')
            ->pluck('estado');

        return view('admin.cep-stats.index', compact(
            'logs',
            'totalConsultas',
            'totalAtendidas',
            'totalNaoAtendidas',
            'percentAtendidas',
            'topCidadesNaoAtendidas',
            'topBairrosNaoAtendidos',
            'estados',
            'dateFrom',
            'dateTo',
            'estado',
            'cidade',
            'bairro',
            'atendido'
        ));
    }
}
```

- [ ] **Step 2: Criar a view**

Criar `resources/views/admin/cep-stats/index.blade.php`:

```blade
@extends('adminlte::page')

@section('title', 'Estatísticas de CEP')

@section('content_header')
    <h1>Consultas de CEP — Entrega na minha região</h1>
@stop

@section('content')

    {{-- Cards totalizadores --}}
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($totalConsultas, 0, ',', '.') }}</h3>
                    <p>Total de Consultas</p>
                </div>
                <div class="icon"><i class="fas fa-search"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($totalAtendidas, 0, ',', '.') }}</h3>
                    <p>Atendidas ({{ $percentAtendidas }}%)</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($totalNaoAtendidas, 0, ',', '.') }}</h3>
                    <p>Não Atendidas</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-purple">
                <div class="inner">
                    <h3>{{ $topCidadesNaoAtendidas->first()->cidade ?? '-' }}</h3>
                    <p>Cidade mais demandada (não atendida)</p>
                </div>
                <div class="icon"><i class="fas fa-map-marker-alt"></i></div>
            </div>
        </div>
    </div>

    {{-- Top 5 rankings lado a lado --}}
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-city"></i> Top 5 Cidades NÃO Atendidas</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped">
                        <thead><tr><th>Cidade</th><th>UF</th><th class="text-right">Consultas</th></tr></thead>
                        <tbody>
                            @forelse($topCidadesNaoAtendidas as $item)
                                <tr>
                                    <td>{{ $item->cidade }}</td>
                                    <td>{{ $item->estado }}</td>
                                    <td class="text-right"><span class="badge badge-warning">{{ $item->total }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">Nenhum dado no período</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-map-pin"></i> Top 5 Bairros NÃO Atendidos</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped">
                        <thead><tr><th>Bairro</th><th>Cidade/UF</th><th class="text-right">Consultas</th></tr></thead>
                        <tbody>
                            @forelse($topBairrosNaoAtendidos as $item)
                                <tr>
                                    <td>{{ $item->bairro }}</td>
                                    <td>{{ $item->cidade }}/{{ $item->estado }}</td>
                                    <td class="text-right"><span class="badge badge-warning">{{ $item->total }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">Nenhum dado no período</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros + Tabela detalhada --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Consultas Detalhadas</h3>
        </div>
        <div class="card-body">
            {{-- Filtros --}}
            <form action="{{ route('admin.cep-stats.index') }}" method="GET" class="form-inline mb-3" style="gap: 8px; flex-wrap: wrap;">
                <div class="form-group">
                    <label class="mr-1">De:</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
                </div>
                <div class="form-group">
                    <label class="mr-1">Até:</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
                </div>
                <div class="form-group">
                    <select name="estado" class="form-control form-control-sm">
                        <option value="">Todos os estados</option>
                        @foreach($estados as $uf)
                            <option value="{{ $uf }}" {{ $estado === $uf ? 'selected' : '' }}>{{ $uf }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <input type="text" name="cidade" class="form-control form-control-sm" placeholder="Cidade" value="{{ $cidade }}">
                </div>
                <div class="form-group">
                    <input type="text" name="bairro" class="form-control form-control-sm" placeholder="Bairro" value="{{ $bairro }}">
                </div>
                <div class="form-group">
                    <select name="atendido" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        <option value="1" {{ $atendido === '1' ? 'selected' : '' }}>Atendidos</option>
                        <option value="0" {{ $atendido === '0' ? 'selected' : '' }}>Não atendidos</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter"></i> Filtrar</button>
                <a href="{{ route('admin.cep-stats.index') }}" class="btn btn-sm btn-default">Limpar</a>
            </form>

            {{-- Tabela --}}
            <table class="table table-bordered table-hover table-sm">
                <thead>
                    <tr>
                        <th style="width: 140px">Data/Hora</th>
                        <th style="width: 100px">CEP</th>
                        <th>Bairro</th>
                        <th>Cidade</th>
                        <th style="width: 50px">UF</th>
                        <th style="width: 100px">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td><small>{{ $log->created_at->format('d/m/Y H:i') }}</small></td>
                            <td><code>{{ $log->cep }}</code></td>
                            <td>{{ $log->bairro ?? '-' }}</td>
                            <td>{{ $log->cidade ?? '-' }}</td>
                            <td>{{ $log->estado ?? '-' }}</td>
                            <td>
                                @if($log->atendido)
                                    <span class="badge badge-success">Atendido</span>
                                @else
                                    <span class="badge badge-warning">Não atendido</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Nenhuma consulta encontrada no período.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $logs->links() }}
        </div>
    </div>

@stop
```

- [ ] **Step 3: Adicionar rotas**

Em `routes/web.php`, dentro do grupo admin, antes da seção ADMINISTRAÇÃO:

```php
    // Estatísticas de consultas de CEP
    Route::get('cep-stats', [App\Http\Controllers\Admin\CepStatsController::class, 'index'])->name('cep-stats.index');
```

- [ ] **Step 4: Adicionar ao menu**

Em `config/adminlte.php`, ANTES do `['header' => 'ADMINISTRAÇÃO']`, adicionar:

```php
        ['header' => 'ESTATÍSTICAS'],
        ['text' => 'Consultas de CEP', 'route'  => 'admin.cep-stats.index', 'icon' => 'fas fa-fw fa-chart-bar'],
```

- [ ] **Step 5: Testar no admin**

1. Acessar `http://127.0.0.1:8000/admin/cep-stats`
2. Verificar que os cards totalizadores aparecem (zerados no início)
3. Fazer consultas no modal do storefront
4. Voltar ao admin e verificar que os dados aparecem na tabela
5. Testar filtros (data, estado, cidade, bairro, status)

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Admin/CepStatsController.php resources/views/admin/cep-stats/index.blade.php routes/web.php config/adminlte.php
git commit -m "feat: cria painel de estatísticas de consultas de CEP

Dashboard com cards (total, atendidas, não atendidas), top 5 cidades
e bairros não atendidos, tabela detalhada com filtros por período,
estado, cidade, bairro e status."
```

---

### Task 6: Deploy e verificação em produção

**Files:** Nenhum

- [ ] **Step 1: Push, deploy e migrate**

```bash
git push origin main
ssh kicolApps "cd /var/www/sync.deepfreeze.com.br && git pull origin main && php artisan migrate --force && php artisan optimize:clear"
```

- [ ] **Step 2: Testar modal no storefront em produção**

- [ ] **Step 3: Testar painel de estatísticas no admin em produção**

---

## Resumo de Impacto

| Item | Detalhe |
|------|---------|
| Arquivos criados | 5 (migration, 2 models/services, 2 views) |
| Arquivos modificados | 4 (controller, layout, routes, menu) |
| Banco legado | **Nenhuma alteração** (apenas leitura) |
| Banco sync | 1 tabela nova (`cep_queries_log`) |
| APIs externas | ViaCEP (pública, cache 24h) |
| Dependências | Nenhuma nova (jQuery, Inputmask, Bootstrap modal já disponíveis) |
| Risco | Baixo — feature isolada, log não afeta fluxo de compra |
