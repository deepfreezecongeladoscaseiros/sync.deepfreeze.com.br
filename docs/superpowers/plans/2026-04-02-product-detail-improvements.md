# Melhorias na Página de Detalhes do Produto — Plano de Implementação

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Adicionar botões de compartilhar, bloco de avaliações/depoimentos de clientes, e melhorar os badges de alérgicos na página de detalhes do produto.

**Architecture:** Todas as alterações são na camada de views/CSS, com leitura do banco legado (tabela `depoimentos`). Model `Depoimento` já existe com métodos prontos. Sem alterações no banco.

**Tech Stack:** Laravel 10, Blade, Bootstrap 3, jQuery, CSS custom properties, banco legado MySQL

---

## Etapa 1: Botões de Compartilhar (compactos)

**Objetivo:** Adicionar ícones pequenos de compartilhar (WhatsApp, Facebook, copiar link) próximos ao botão Comprar.

### Arquivos:
- **Modificar:** `resources/views/storefront/product/partials/info.blade.php`
- **Modificar:** `public/storefront/css/product-detail.css`

### O que fazer:

Adicionar uma linha de ícones compactos logo abaixo do botão Comprar (antes dos tags/selos).

```blade
{{-- Compartilhar --}}
<div class="product-share">
    <span class="share-label">Compartilhe:</span>
    <a href="https://wa.me/?text={{ urlencode($product->name . ' - ' . $product->url) }}"
       target="_blank" rel="noopener" class="share-btn share-whatsapp" title="Compartilhar no WhatsApp">
        <i class="fa fa-whatsapp"></i>
    </a>
    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($product->url) }}"
       target="_blank" rel="noopener" class="share-btn share-facebook" title="Compartilhar no Facebook">
        <i class="fa fa-facebook"></i>
    </a>
    <a href="javascript:" class="share-btn share-copy js-copy-link" 
       data-url="{{ $product->url }}" title="Copiar link">
        <i class="fa fa-link"></i>
    </a>
</div>
```

**Posição no info.blade.php:** Após o botão Comprar (linha 89), antes dos Tags/Selos (linha 104).

**CSS:** Ícones 28x28px, cinza claro, inline, hover com cor da rede social (WhatsApp verde, Facebook azul, link primário).

**JS:** Ao clicar em "copiar link", copia a URL para o clipboard e mostra feedback.

### Critérios de aceite:
- [ ] 3 ícones compactos alinhados horizontalmente
- [ ] WhatsApp abre compartilhamento com nome + URL
- [ ] Facebook abre sharer
- [ ] Copiar link copia para clipboard com feedback visual
- [ ] Design discreto, não compete com botão Comprar

---

## Etapa 2: Bloco de Avaliações/Depoimentos

**Objetivo:** Exibir depoimentos de clientes com estrelas, nome, data e texto. Formulário para enviar nova avaliação (só logado).

### Arquivos:
- **Criar:** `resources/views/storefront/product/partials/reviews.blade.php`
- **Modificar:** `resources/views/storefront/product/show.blade.php` (incluir o partial)
- **Modificar:** `app/Http/Controllers/Storefront/ProductController.php` (carregar depoimentos)
- **Criar:** `app/Http/Controllers/Storefront/ReviewController.php` (POST de nova avaliação)
- **Modificar:** `routes/web.php` (rota POST para enviar avaliação)
- **Modificar:** `public/storefront/css/product-detail.css` (CSS do bloco)

### Dados do banco legado (tabela `depoimentos`):
- `produto_id` — FK para o produto
- `avaliacao` — 1 a 5 estrelas
- `depoimento` — texto (max 200 chars)
- `pessoa_id` — FK para o cliente
- `situacao_depoimento` — 0=pendente, 1=aprovado
- `created` — data de criação

### Model já existe: `app/Models/Legacy/Depoimento.php`
- `Depoimento::approved()->forProducts()->where('produto_id', $id)->get()`
- `Depoimento::getStarsForProduct($id)` — média + contagem

### Layout do bloco:

```
┌─────────────────────────────────────────────────────────┐
│  Avaliações dos Clientes                                │
│  ★★★★★  4.8  (12 avaliações)                           │
│                                                         │
│  ┌─────────────────────────────────────────────────────┐ │
│  │ ★★★★★   Maria Silva   25/03/2026                  │ │
│  │ Muito bom! Sabor caseiro de verdade.               │ │
│  └─────────────────────────────────────────────────────┘ │
│  ┌─────────────────────────────────────────────────────┐ │
│  │ ★★★★☆   João Santos   20/03/2026                  │ │
│  │ Gostei, mas poderia ter mais recheio.              │ │
│  └─────────────────────────────────────────────────────┘ │
│                                                         │
│  ┌─ Deixe sua avaliação ──────────────────────────────┐ │
│  │ ★ ★ ★ ★ ★  (clicáveis)                           │ │
│  │ [textarea max 200 chars]                           │ │
│  │              [Enviar Avaliação]                     │ │
│  └─────────────────────────────────────────────────────┘ │
│  (Precisa estar logado para avaliar)                    │
└─────────────────────────────────────────────────────────┘
```

### Controller (`ProductController::show`):
Carregar depoimentos e passar para view:
```php
$reviews = Depoimento::approved()
    ->forProducts()
    ->where('produto_id', $product->id)
    ->join('pessoas', 'pessoas.id', '=', 'depoimentos.pessoa_id')
    ->select('depoimentos.*', 'pessoas.nome')
    ->orderBy('depoimentos.id', 'desc')
    ->limit(10)
    ->get();

$productStars = Depoimento::getStarsForProduct($product->id);
```

### ReviewController (novo):
- `store(Request $request)` — POST `/produto/avaliar`
- Requer auth customer
- Valida: produto_id (exists), avaliacao (1-5), depoimento (string, max 200)
- Grava com `situacao_depoimento = 0` (pendente — aparece após aprovação no SIV)
- Retorna JSON success/error (AJAX)

### Estrelas no form:
SVG clicáveis (mesmo estilo gordinho dos cards), hover interativo via JS.

### Critérios de aceite:
- [ ] Bloco exibe média de estrelas + contagem
- [ ] Lista de depoimentos individuais (estrelas + nome + data + texto)
- [ ] Máximo 10 depoimentos visíveis
- [ ] Formulário de avaliação (só para clientes logados)
- [ ] Estrelas clicáveis no formulário (1-5)
- [ ] Textarea com contador de caracteres (max 200)
- [ ] Submit via AJAX com feedback (sucesso/erro)
- [ ] Mensagem "Sua avaliação será publicada após aprovação"
- [ ] Se não logado: mostra link para login
- [ ] Design moderno, alinhado com estética do site

---

## Etapa 3: Melhorar Badges de Alérgicos e Informações

**Objetivo:** Melhorar visual dos badges existentes (Contém Glúten, Contém Lactose) e adicionar informação textual de alérgenos manuais.

### Arquivos:
- **Modificar:** `resources/views/storefront/product/partials/info.blade.php` (bloco de tags)
- **Modificar:** `public/storefront/css/product-detail.css`

### O que melhorar:

1. **Badges com ícones** — adicionar ícone SVG/emoji antes do texto:
   - 🌾 Contém Glúten / ✅ Não Contém Glúten
   - 🥛 Contém Lactose / ✅ Sem Lactose / ⚠️ Baixa Lactose
   - 🍺 Contém Álcool (se `alcoholic_beverage`)

2. **Alérgenos manuais** — Se `$product->allergens` (campo `alergenicos_manual`), exibir texto completo:
   ```
   ⚠️ Alérgenos: Contém leite, ovos, trigo e derivados de soja.
   ```

3. **Layout** — Badges em linha, com fundo colorido contextual:
   - Verde para "Sem" (positivo/seguro)
   - Amarelo para "Contém" (atenção)
   - Vermelho para álcool

### Campos disponíveis no Product model:
- `$product->contains_gluten` (boolean) — `in_contem_gluten`
- `$product->lactose_free` (boolean) — `in_sem_lactose`
- `$product->low_lactose` (boolean) — `in_baixo_lactose`
- `$product->contains_lactose` (boolean) — `in_contem_lactose`
- `$product->alcoholic_beverage` (boolean) — `bebida_alcoolica`
- `$product->allergens` (text) — `alergenicos_manual`

### Critérios de aceite:
- [ ] Badges com ícones visuais (SVG ou emoji)
- [ ] Cores contextuais (verde=seguro, amarelo=atenção)
- [ ] Texto de alérgenos manuais exibido quando presente
- [ ] Design limpo, não polui a página
- [ ] Responsivo mobile

---

## Ordem de Execução

| Etapa | Complexidade | Tempo estimado | Dependências |
|-------|-------------|----------------|--------------|
| 1. Compartilhar | Baixa | Rápido | Nenhuma |
| 2. Avaliações | Alta | Mais demorado | Nenhuma |
| 3. Badges alérgicos | Baixa | Rápido | Nenhuma |

As 3 etapas são independentes — podem ser feitas em qualquer ordem. A sugestão é fazer 1 → 3 → 2 (do mais rápido ao mais complexo, validando cada uma).

---

## Notas importantes

- **Não alterar banco legado** — apenas ler/inserir nas tabelas existentes
- **Depoimentos pendentes** — novos depoimentos ficam com `situacao_depoimento=0` até aprovação no SIV
- **Cache de estrelas** — `Depoimento::getStarsByProduct()` tem cache de 30 min, limpar ao inserir novo
- **Design** — seguir padrão do site (Bootstrap 3, CSS vars `--color-primary`, Font Awesome, SVG estrelas)
