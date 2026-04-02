# Informações Nutricionais — Leitura Direta do Legado — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Exibir a tabela de informações nutricionais (padrão ANVISA) na página de detalhes do produto, lendo os dados diretamente do banco legado — sem copiar dados para o banco sync.

**Architecture:** Criar um Service (`NutritionalInfoService`) que replica a mesma lógica de consulta do sistema legado (query `MAX(id)` por nutriente em `informacoes_nutricionais`, com cache de 1 hora). O service retorna um DTO/array que o controller passa para a view. O template será reescrito para receber os dados no formato do legado (valores por 100g + cálculo de porção + %VD na view), seguindo o mesmo padrão visual já existente no CSS.

**Tech Stack:** Laravel 10, MySQL Legacy (`mysql_legacy`), Cache (1h como o legado), Blade

---

## Contexto: Como o Legado Funciona

### Fluxo de dados no legado (mobile/storefront)

1. **Controller** verifica `produtos.informacao_nutricional_siv`:
   - Se `1` → busca com `origem = 'S'` (SIV/calculado)
   - Se `0` → busca com `origem = 'J'` (JOB/manual)

2. **Model** executa duas queries:
   ```sql
   -- Query 1: Busca IDs dos registros mais recentes por nutriente
   SELECT MAX(id) as _id FROM informacoes_nutricionais
   WHERE produto_id = ? AND origem = ?
   GROUP BY dietpro_nutriente

   -- Query 2: Busca os valores desses registros
   SELECT dietpro_nutriente, valor FROM informacoes_nutricionais
   WHERE id IN (...)
   ```

3. **Resultado**: Array `[nutriente_id => valor_por_100g]`, ex: `[1 => 122.67, 2 => 10.67, ...]`

4. **View** recebe `$nutri` (array acima), `$nutri_vdr` (valores diários de referência) e dados do produto (porção, peso, etc.), e calcula na view:
   - Valor por porção: `valor_100g * porcao / 100`
   - %VD: `valor_porcao * 100 / vd_valor`
   - Arredondamentos ANVISA (Anexo III e VI)

5. **Guard**: `count($nutri) === 10` — se não tem os 10 nutrientes, não exibe a tabela

### IDs de nutrientes e VD

| ID | Nutriente | VD |
|----|-----------|-----|
| 1 | Valor energético (kcal) | 2000 |
| 2 | Proteínas (g) | 50 |
| 3 | Gorduras totais (g) | 65 |
| 4 | Carboidratos (g) | 300 |
| 58 | Sódio (mg) | 2000 |
| 69 | Fibras alimentares (g) | 25 |
| 185 | Gorduras saturadas (g) | 20 |
| 186 | Gorduras trans (g) | 2 |
| 187 | Açúcares totais (g) | 0 (sem %VD) |
| 188 | Açúcares adicionados (g) | 50 |

### Campos do produto relevantes (tabela `produtos`, legado)

| Campo | Uso |
|-------|-----|
| `porcao` | Tamanho da porção declarada (default 100) |
| `unidade_medida_peso` | 'g' ou 'ml' |
| `medida_caseira` | Ex: "1 fatia" (pode ser vazio) |
| `peso_liquido` | Peso total da embalagem |
| `informacao_nutricional_siv` | 0=manual(J), 1=calculado(S) |
| `tipo_de_etiqueta` | >= 4 inclui açúcares totais/adicionados |

---

## Mapeamento de Arquivos

| Arquivo | Ação | Responsabilidade |
|---------|------|------------------|
| `app/Services/NutritionalInfoService.php` | **Criar** | Consulta dados nutricionais do banco legado (replica lógica do mobile) |
| `app/Http/Controllers/Storefront/ProductController.php` | **Modificar** | Chamar o service e passar dados para a view |
| `resources/views/storefront/product/partials/nutritional-info.blade.php` | **Reescrever** | Exibir tabela ANVISA com dados do legado (valores por 100g, porção, %VD) |
| `resources/views/storefront/product/partials/info.blade.php` | **Modificar** (linha 20-24) | Exibir calorias usando dados do service em vez de `nutritionalInfo` |
| `resources/views/storefront/product/show.blade.php` | **Modificar** (linhas 152-155) | Exibir calorias na barra mobile usando dados do service |
| CSS (`public/storefront/css/product-detail.css`) | **Sem alteração** | Já pronto |

---

### Task 1: Criar NutritionalInfoService

**Files:**
- Create: `app/Services/NutritionalInfoService.php`

- [ ] **Step 1: Criar o service**

Criar `app/Services/NutritionalInfoService.php`:

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Service: Informações Nutricionais do Produto
 *
 * Replica a lógica do sistema legado (html/mobile/app/Model/InformacaoNutricional.php)
 * para buscar dados nutricionais diretamente do banco legado.
 *
 * Arquitetura do legado:
 * - Tabela informacoes_nutricionais: append-only, ~175K registros
 * - Valor atual = MAX(id) agrupado por (produto_id, dietpro_nutriente, origem)
 * - Origem: 'S' (SIV/calculado) ou 'J' (JOB/manual), controlado por produtos.informacao_nutricional_siv
 * - Cache de 1 hora (mesmo padrão do legado mobile)
 */
class NutritionalInfoService
{
    // IDs dos nutrientes obrigatórios (tabela dietpro_nutrientes)
    private const NUTRIENT_IDS = [1, 2, 3, 4, 58, 69, 185, 186, 187, 188];

    // Valores Diários de Referência (%VD) — ANVISA, dieta de 2000 kcal
    private const VD_REFERENCES = [
        1   => 2000,  // Energia (kcal)
        2   => 50,    // Proteínas (g)
        3   => 65,    // Gorduras totais (g)
        4   => 300,   // Carboidratos (g)
        58  => 2000,  // Sódio (mg)
        69  => 25,    // Fibras (g)
        185 => 20,    // Gorduras saturadas (g)
        186 => 2,     // Gorduras trans (g)
        187 => 0,     // Açúcares totais (sem VD)
        188 => 50,    // Açúcares adicionados (g)
    ];

    /**
     * Busca dados nutricionais completos de um produto.
     *
     * Retorna null se o produto não tem dados nutricionais suficientes (< 10 nutrientes).
     *
     * @param object $product Produto do legado (precisa de: id, informacao_nutricional_siv, porcao,
     *                        peso_liquido, unidade_medida_peso, medida_caseira, tipo_de_etiqueta)
     * @return array|null Array com chaves: 'nutri', 'vdr', 'porcao', 'peso_liquido',
     *                    'unidade', 'medida_caseira', 'tipo_etiqueta'
     */
    public function getForProduct(object $product): ?array
    {
        // Determina origem dos dados conforme flag do produto
        $origem = $product->informacao_nutricional_siv ? 'S' : 'J';

        // Busca valores nutricionais com cache de 1 hora (mesmo padrão do legado)
        $nutri = $this->getNutrientValues($product->id, $origem);

        // Guard: precisa ter exatamente 10 nutrientes (mesma regra do legado)
        if (count($nutri) !== 10) {
            return null;
        }

        return [
            'nutri'           => $nutri,                                    // [nutriente_id => valor_por_100g]
            'vdr'             => self::VD_REFERENCES,                       // [nutriente_id => valor_diario]
            'porcao'          => (int) ($product->porcao ?: 100),           // Tamanho da porção (g ou ml)
            'peso_liquido'    => (float) ($product->peso_liquido ?: 0),     // Peso total embalagem
            'unidade'         => $product->unidade_medida_peso ?: 'g',      // 'g' ou 'ml'
            'medida_caseira'  => $product->medida_caseira ?: '',            // Ex: "1 fatia"
            'tipo_etiqueta'   => (int) ($product->tipo_de_etiqueta ?: 0),   // >= 4 inclui açúcares
        ];
    }

    /**
     * Busca valores dos nutrientes para um produto.
     * Replica: InformacaoNutricional::get_valores_nutricientes_criterio()
     *
     * @return array [dietpro_nutriente_id => valor_por_100g]
     */
    private function getNutrientValues(int $productId, string $origem): array
    {
        $cacheKey = "nutri_product_{$productId}_{$origem}";

        return Cache::remember($cacheKey, 3600, function () use ($productId, $origem) {
            // Query 1: MAX(id) por nutriente — busca o registro mais recente de cada nutriente
            $maxIds = DB::connection('mysql_legacy')
                ->table('informacoes_nutricionais')
                ->selectRaw('MAX(id) as max_id')
                ->where('produto_id', $productId)
                ->where('origem', $origem)
                ->whereIn('dietpro_nutriente', self::NUTRIENT_IDS)
                ->groupBy('dietpro_nutriente')
                ->pluck('max_id')
                ->toArray();

            if (empty($maxIds)) {
                return [];
            }

            // Query 2: Busca os valores dos registros encontrados
            return DB::connection('mysql_legacy')
                ->table('informacoes_nutricionais')
                ->whereIn('id', $maxIds)
                ->pluck('valor', 'dietpro_nutriente')
                ->toArray();
        });
    }
}
```

- [ ] **Step 2: Testar o service via tinker**

Run:
```bash
php artisan tinker --execute="
\$svc = app(App\Services\NutritionalInfoService::class);
\$prod = DB::connection('mysql_legacy')->table('produtos')->where('id', 1)->first();
\$data = \$svc->getForProduct(\$prod);
if (\$data) {
    echo 'Nutrientes: ' . count(\$data['nutri']) . PHP_EOL;
    echo 'Energia/100g: ' . \$data['nutri'][1] . ' kcal' . PHP_EOL;
    echo 'Porção: ' . \$data['porcao'] . \$data['unidade'] . PHP_EOL;
} else {
    echo 'Sem dados nutricionais' . PHP_EOL;
}
"
```

Expected: `Nutrientes: 10`, `Energia/100g: 122.67 kcal`, `Porção: 100g` (ou similar)

- [ ] **Step 3: Commit**

```bash
git add app/Services/NutritionalInfoService.php
git commit -m "feat: cria NutritionalInfoService para ler dados nutricionais do legado

Replica lógica do mobile legado (MAX(id) por nutriente, cache 1h).
Retorna array com valores por 100g, VDR e dados da porção.

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>"
```

---

### Task 2: Integrar service no ProductController

**Files:**
- Modify: `app/Http/Controllers/Storefront/ProductController.php`

- [ ] **Step 1: Injetar o service e buscar dados nutricionais no método `show()`**

Adicionar import no topo do arquivo:
```php
use App\Services\NutritionalInfoService;
```

No método `show()`, após a busca de `$productStars` (linha ~88) e antes do `$breadcrumb`, adicionar:

```php
        // Informações nutricionais — leitura direta do banco legado
        $nutritionalInfoService = app(NutritionalInfoService::class);
        $nutritionalData = $nutritionalInfoService->getForProduct($product);
```

**ATENÇÃO**: O `$product` é um Model Eloquent (`App\Models\Product`) que lê da tabela `produtos` via `mysql_legacy`. Os campos `informacao_nutricional_siv`, `porcao`, `peso_liquido`, `unidade_medida_peso`, `medida_caseira`, `tipo_de_etiqueta` precisam estar acessíveis. Verificar se o Product model já expõe esses campos (via `$columnMap` ou diretamente). Se não, será necessário garantir acesso a eles.

Adicionar `'nutritionalData'` ao `compact()` da view (linha ~97-104):

```php
        return view('storefront.product.show', compact(
            'product',
            'category',
            'relatedProducts',
            'breadcrumb',
            'reviews',
            'productStars',
            'nutritionalData'
        ));
```

- [ ] **Step 2: Verificar que o Product model expõe os campos nutricionais necessários**

Os campos necessários do legado são: `informacao_nutricional_siv`, `porcao`, `peso_liquido`, `unidade_medida_peso`, `medida_caseira`, `tipo_de_etiqueta`.

Verificar no Product model se esses campos estão em `$columnMap` ou são acessíveis diretamente. Se o model usa `$columnMap`, os campos podem ter nomes diferentes. Verificar e ajustar se necessário.

Se os campos NÃO estiverem mapeados, adicionar ao `$columnMap` ou acessá-los via o nome original da coluna do legado.

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/Storefront/ProductController.php app/Models/Product.php
git commit -m "feat: integra NutritionalInfoService no ProductController

Busca dados nutricionais do legado e passa para a view como \$nutritionalData.

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>"
```

---

### Task 3: Reescrever template nutritional-info.blade.php

**Files:**
- Modify: `resources/views/storefront/product/partials/nutritional-info.blade.php` (reescrita completa)

- [ ] **Step 1: Reescrever o template**

O template deve receber `$nutritionalData` (array do service ou null) em vez de `$product->nutritionalInfo`.

Replica a lógica de exibição do legado (`html/mobile/app/View/Elements/nutri.ctp`):
- Valores por 100g na primeira coluna
- Valores por porção na segunda coluna (calculados: `valor * porcao / 100`)
- %VD na terceira coluna
- Arredondamentos ANVISA (Anexo III)
- Coluna de porção oculta quando `porcao == 100` (seria redundante)
- Açúcares totais/adicionados só aparecem se `tipo_etiqueta >= 4`
- Selos "Alto em" quando açúcar >= 15, gordura saturada >= 6, sódio >= 600

Substituir TODO o conteúdo de `nutritional-info.blade.php` por:

```blade
{{--
    Partial: Informações Nutricionais do Produto

    Exibe tabela nutricional completa padrão ANVISA.
    Dados lidos diretamente do banco legado via NutritionalInfoService.

    Variáveis:
    - $nutritionalData: array|null do NutritionalInfoService::getForProduct()
      Se não null, contém: nutri, vdr, porcao, peso_liquido, unidade, medida_caseira, tipo_etiqueta
--}}

<div class="box-tabela-nutricional mod-2">

    @if($nutritionalData)
        @php
            $nutri          = $nutritionalData['nutri'];
            $vdr            = $nutritionalData['vdr'];
            $porcao         = $nutritionalData['porcao'];
            $pesoLiquido    = $nutritionalData['peso_liquido'];
            $unidade        = $nutritionalData['unidade'];
            $medidaCaseira  = $nutritionalData['medida_caseira'];
            $tipoEtiqueta   = $nutritionalData['tipo_etiqueta'];
            $ocultarPorcao  = ($porcao == 100);

            // Funções de arredondamento ANVISA (Anexo III)
            // Replica: get_arredondado_info_nutri() do legado
            $arredondar = function($valor, $medida = 'g') {
                if ($valor === null) return '-';
                $valorInt = floor($valor);

                if ($valorInt == $valor) return (string) $valorInt;

                if ($valor >= 10) {
                    $dif = ($valor - $valorInt) * 10;
                    if (substr((string) $dif, 0, 1) >= 5) $valorInt++;
                    return (string) $valorInt;
                }

                if (($valor >= 1 && $valor < 10) || ($valor < 1 && $medida == 'g')) {
                    $v = number_format($valor, 3, '.', '');
                    $pos = strpos($v, '.');
                    if (substr($v, $pos + 2, 1) >= 5) $valor += 0.1;
                    $v = substr(number_format($valor, 3, '.', ''), 0, $pos + 2);
                    $v = number_format((float)$v, 1, ',', '');
                    return str_replace(',0', '', $v);
                }

                if ($valor < 1 && $medida == 'mg') {
                    $v = number_format($valor, 3, '.', '');
                    $pos = strpos($v, '.');
                    if (substr($v, $pos + 3, 1) >= 5) $valor += 0.01;
                    $v = substr(number_format($valor, 3, '.', ''), 0, $pos + 3);
                    $v = number_format((float)$v, 2, ',', '');
                    return str_replace(',00', ',0', $v);
                }

                return number_format($valor, 1, ',', '');
            };

            // Valor por porção: (valor_100g * porcao) / 100
            $valorPorcao = function($id, $medida = 'g') use ($nutri, $porcao, $arredondar) {
                if (!isset($nutri[$id])) return '-';
                $valor = $porcao * $nutri[$id] / 100;
                return $arredondar($valor, $medida);
            };

            // %VD por porção
            $percentVD = function($id) use ($nutri, $vdr, $porcao) {
                if (!isset($nutri[$id]) || !isset($vdr[$id]) || $vdr[$id] <= 0) return '';
                $valorPorcao = $porcao * $nutri[$id] / 100;
                return round($valorPorcao * 100 / $vdr[$id]);
            };

            // Porções por embalagem (Anexo VI ANVISA)
            $porcoesEmbalagem = function() use ($pesoLiquido, $porcao) {
                if ($pesoLiquido <= 0 || $porcao <= 0) return '';
                $qtd = $pesoLiquido / $porcao;
                $qtdInt = floor($qtd);
                $dif = ($qtd - $qtdInt) * 10;
                if (substr((string) $dif, 0, 1) >= 5) $qtdInt++;
                return ($qtdInt != floor($pesoLiquido / $porcao) + 1) ? $qtdInt : "Cerca de {$qtdInt}";
            };

            // Medida caseira
            $descricaoMedida = function() use ($pesoLiquido, $porcao, $medidaCaseira) {
                if (!empty($medidaCaseira)) return $medidaCaseira;
                if ($pesoLiquido == $porcao) return '1 Unidade';
                if ($pesoLiquido > $porcao) {
                    $qtd = (int) round($pesoLiquido / $porcao);
                    return "1/{$qtd} Unidade";
                }
                $qtd = (int) round($porcao / $pesoLiquido);
                return "{$qtd} Unidades";
            };

            $unidadeLabel = ($unidade == 'ml') ? 'ml' : 'g';
        @endphp

        {{-- Oculta coluna de porção se porcao == 100 (seria redundante com coluna 100g) --}}
        @if($ocultarPorcao)
            <style>.ocultar-porcao { display: none; }</style>
        @endif

        <table class="tabela-padrao">
            <thead>
                <tr class="tb-title">
                    <th colspan="4">Informação Nutricional</th>
                </tr>
                <tr class="tb-subtitle">
                    <th colspan="4">
                        <span class="tb-porcao-embalagem">
                            Porções por embalagem: {{ $porcoesEmbalagem() }}
                        </span>
                        <span class="tb-porcao">
                            Porção: {{ (int) $porcao }}{{ $unidadeLabel }} ({{ $descricaoMedida() }})
                        </span>
                    </th>
                </tr>
                <tr class="tb-legend">
                    <th>&nbsp;</th>
                    <th class="text-center">100{{ $unidadeLabel }}</th>
                    <th class="text-center ocultar-porcao">{{ (int) $porcao }}{{ $unidadeLabel }}</th>
                    <th class="text-center">%VD*</th>
                </tr>
            </thead>
            <tbody>
                {{-- Valor Energético --}}
                <tr>
                    <td>Valor energético (kcal)</td>
                    <td class="text-center">{{ $arredondar($nutri[1] ?? null) }}</td>
                    <td class="text-center ocultar-porcao">{{ $valorPorcao(1) }}</td>
                    <td class="text-center">{{ $percentVD(1) }}</td>
                </tr>

                {{-- Carboidratos --}}
                <tr>
                    <td>Carboidratos totais (g)</td>
                    <td class="text-center">{{ $arredondar($nutri[4] ?? null) }}</td>
                    <td class="text-center ocultar-porcao">{{ $valorPorcao(4) }}</td>
                    <td class="text-center">{{ $percentVD(4) }}</td>
                </tr>

                {{-- Açúcares totais e adicionados (somente se tipo_etiqueta >= 4) --}}
                @if($tipoEtiqueta >= 4)
                    <tr>
                        <td class="pl-2">Açúcares totais (g)</td>
                        <td class="text-center">{{ $arredondar($nutri[187] ?? null) }}</td>
                        <td class="text-center ocultar-porcao">{{ $valorPorcao(187) }}</td>
                        <td class="text-center">{{ $percentVD(187) }}</td>
                    </tr>
                    <tr>
                        <td class="pl-3">Açúcares adicionados (g)</td>
                        <td class="text-center">{{ $arredondar($nutri[188] ?? null) }}</td>
                        <td class="text-center ocultar-porcao">{{ $valorPorcao(188) }}</td>
                        <td class="text-center">{{ $percentVD(188) }}</td>
                    </tr>
                @endif

                {{-- Proteínas --}}
                <tr>
                    <td>Proteínas (g)</td>
                    <td class="text-center">{{ $arredondar($nutri[2] ?? null) }}</td>
                    <td class="text-center ocultar-porcao">{{ $valorPorcao(2) }}</td>
                    <td class="text-center">{{ $percentVD(2) }}</td>
                </tr>

                {{-- Gorduras Totais --}}
                <tr>
                    <td>Gorduras totais (g)</td>
                    <td class="text-center">{{ $arredondar($nutri[3] ?? null) }}</td>
                    <td class="text-center ocultar-porcao">{{ $valorPorcao(3) }}</td>
                    <td class="text-center">{{ $percentVD(3) }}</td>
                </tr>

                {{-- Gorduras Saturadas --}}
                <tr>
                    <td class="pl-2">Gorduras saturadas (g)</td>
                    <td class="text-center">{{ $arredondar($nutri[185] ?? null) }}</td>
                    <td class="text-center ocultar-porcao">{{ $valorPorcao(185) }}</td>
                    <td class="text-center">{{ $percentVD(185) }}</td>
                </tr>

                {{-- Gorduras Trans --}}
                <tr>
                    <td class="pl-2">Gorduras trans (g)</td>
                    <td class="text-center">{{ $arredondar($nutri[186] ?? null) }}</td>
                    <td class="text-center ocultar-porcao">{{ $valorPorcao(186) }}</td>
                    <td class="text-center">{{ $percentVD(186) }}</td>
                </tr>

                {{-- Fibras --}}
                <tr>
                    <td>Fibras alimentares (g)</td>
                    <td class="text-center">{{ $arredondar($nutri[69] ?? null) }}</td>
                    <td class="text-center ocultar-porcao">{{ $valorPorcao(69) }}</td>
                    <td class="text-center">{{ $percentVD(69) }}</td>
                </tr>

                {{-- Sódio --}}
                <tr>
                    <td>Sódio (mg)</td>
                    <td class="text-center">{{ $arredondar($nutri[58] ?? null, 'mg') }}</td>
                    <td class="text-center ocultar-porcao">{{ $valorPorcao(58, 'mg') }}</td>
                    <td class="text-center">{{ $percentVD(58) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="caption">
            *Percentual de valores diários fornecidos pela porção, com base em uma dieta de 2.000 kcal.
        </div>

        {{-- Selos "Alto em" (RDC 429 ANVISA) --}}
        @php
            $altoEm = [];
            if (($nutri[188] ?? 0) >= 15) $altoEm[] = 'acucar';
            if (($nutri[185] ?? 0) >= 6) $altoEm[] = 'gordura';
            if (($nutri[58] ?? 0) >= 600) $altoEm[] = 'sodio';
        @endphp

        @if(count($altoEm) > 0)
            <div class="selos-alto-em">
                <img src="https://img.deepfreeze.com.br/siv_v2/img/alto_em.jpg" alt="Alto em" class="selo-alto">
                @foreach($altoEm as $selo)
                    <img src="https://img.deepfreeze.com.br/siv_v2/img/alto_em_{{ $selo }}.jpg" alt="Alto em {{ $selo }}" class="selo-alto">
                @endforeach
                <img src="https://img.deepfreeze.com.br/siv_v2/img/alto_em_fim.jpg" alt="" class="selo-alto-fim">
            </div>
        @endif

    @else
        {{-- Placeholder: Dados nutricionais não disponíveis --}}
        <div class="nutritional-coming-soon">
            <div class="icon">
                <i class="fa fa-leaf"></i>
            </div>
            <h4>Informações Nutricionais</h4>
            <p>Em breve, as informações nutricionais completas deste produto estarão disponíveis.</p>
        </div>
    @endif

</div>
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/storefront/product/partials/nutritional-info.blade.php
git commit -m "feat: reescreve template nutricional para ler dados do legado

Exibe tabela ANVISA com valores por 100g, porção e %VD.
Replica lógica de arredondamento e selos 'Alto em' do legado.

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>"
```

---

### Task 4: Atualizar referências de calorias em info.blade.php e show.blade.php

**Files:**
- Modify: `resources/views/storefront/product/partials/info.blade.php` (linhas 20-24)
- Modify: `resources/views/storefront/product/show.blade.php` (linhas 152-155)

- [ ] **Step 1: Atualizar exibição de calorias no info.blade.php**

Substituir as linhas 20-24 que usam `$product->nutritionalInfo->energy_kcal` por:

```blade
    @if($nutritionalData && isset($nutritionalData['nutri'][1]))
        <p class="info">|</p>
        <p class="info">
            <span class="kcal">{{ number_format($nutritionalData['nutri'][1], 0, ',', '.') }}</span> calorias
        </p>
    @endif
```

- [ ] **Step 2: Atualizar exibição de calorias na barra mobile do show.blade.php**

Substituir as linhas 152-155 que usam `$product->nutritionalInfo->energy_kcal` por:

```blade
                @if($nutritionalData && isset($nutritionalData['nutri'][1]))
                    <div class="kcal-button">
                        <p class="kcal">{{ number_format($nutritionalData['nutri'][1], 0, ',', '.') }} calorias</p>
                    </div>
                @endif
```

- [ ] **Step 3: Commit**

```bash
git add resources/views/storefront/product/partials/info.blade.php resources/views/storefront/product/show.blade.php
git commit -m "feat: atualiza referências de calorias para usar dados do legado

Substitui \$product->nutritionalInfo->energy_kcal por \$nutritionalData['nutri'][1].

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>"
```

---

### Task 5: Deploy e verificação visual em produção

**Files:** Nenhum

- [ ] **Step 1: Push para o repositório**

```bash
git push origin main
```

- [ ] **Step 2: Deploy no servidor kicolApps**

```bash
ssh kicolApps "cd /var/www/sync.deepfreeze.com.br && git pull origin main && php artisan optimize:clear"
```

- [ ] **Step 3: Verificar visualmente em produção**

Navegar até um produto que tenha dados nutricionais no legado e confirmar:
- Tabela ANVISA aparece no lugar do placeholder "Em breve"
- Valores por 100g, porção e %VD estão corretos
- Coluna de porção oculta quando porção = 100g
- Açúcares só aparecem se tipo_etiqueta >= 4
- Selos "Alto em" aparecem quando aplicável
- Calorias exibidas no info sidebar e na barra mobile

Para produtos SEM dados nutricionais, o placeholder "Em breve" deve continuar aparecendo.

---

## Resumo de Impacto

| Item | Detalhe |
|------|---------|
| Arquivos criados | 1 (`NutritionalInfoService.php`) |
| Arquivos modificados | 3 (controller, 2 templates) |
| Arquivos reescritos | 1 (`nutritional-info.blade.php`) |
| Banco legado | **Apenas leitura** — nenhuma alteração |
| Banco sync | **Nenhuma alteração** |
| CSS | **Sem alteração** (já pronto) |
| Dependências novas | Nenhuma |
| Cache | 1 hora por produto (mesmo padrão do legado mobile) |
| Risco de regressão | Baixo — leitura direta, sem modificar dados |
