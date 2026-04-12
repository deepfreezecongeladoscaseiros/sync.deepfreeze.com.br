# TODO: Plano de Migração de Domínios — Virada do Site

**Prioridade:** Antes da virada
**Complexidade:** Alta — envolve DNS, Apache, cron jobs, integrações externas
**Data da análise:** 03/04/2026

---

## Situação Atual

Tudo roda no domínio `www.deepfreeze.com.br` apontando para o servidor legado (217.196.63.163):

| Path | Sistema | Função |
|------|---------|--------|
| `/` (raiz) | CakePHP App | Storefront antigo (será substituído pelo sync) |
| `/siv_v2/` | CakePHP | Painel admin/SIV (gestão da operação) |
| `/pdv/` | CakePHP | Ponto de Venda (lojas físicas) |
| `/mobile/` | CakePHP | API mobile + storefront mobile |
| `/deepfreeze/` | PHP puro | Scripts de integração, cron jobs, iFood, frete |
| `/img/pratos/big/` | Static | Imagens dos produtos |
| `/app/` | CakePHP | App legada principal |

## O Que Acontece na Virada

Quando `www.deepfreeze.com.br` apontar para o kicolApps (sync Laravel):

### QUEBRA IMEDIATA (segundos)
- **iFood** — polling a cada 30 segundos para `/deepfreeze/index.php?metodo=/siv/ifood/pedidos_ifood` — pedidos param de entrar
- **Callbacks de pagamento** — Cielo, Rede, Ame chamam URLs no domínio www para confirmar transações
- **PDV das lojas** — todas as lojas físicas acessam `/pdv/` — caixas param
- **SIV** — equipe administrativa acessa `/siv_v2/` — gestão para
- **150+ cron jobs** — chamam endpoints via `curl https://www.deepfreeze.com.br/...`

### QUEBRA EM HORAS
- Imagens dos produtos somem do site novo (servidas de `/img/pratos/big/`)
- Newsletters e emails transacionais param de ser enviados
- Sincronização PDV ↔ SIV para de funcionar
- NF-e/NFC-e param de ser emitidas e validadas

---

## Estratégia de Migração: Subdomínios

A solução é criar subdomínios que apontem para o servidor legado (217.196.63.163), **antes** de trocar o DNS do www.

### Subdomínios Necessários

| Subdomínio | Aponta para | Path no legado | Função |
|------------|------------|----------------|--------|
| `siv.deepfreeze.com.br` | 217.196.63.163 | `/siv_v2/` | Painel SIV (admin/gestão) |
| `pdv.deepfreeze.com.br` | 217.196.63.163 | `/pdv/` | Ponto de Venda (lojas) |
| `img.deepfreeze.com.br` | 217.196.63.163 | `/img/` | Imagens de produtos |
| `api.deepfreeze.com.br` | 217.196.63.163 | `/deepfreeze/` + `/mobile/` | Integrações, iFood, scripts, API mobile |
| `legacy.deepfreeze.com.br` | 217.196.63.163 | `/` (tudo) | Fallback para qualquer coisa não migrada |

### Configuração no Servidor Legado (Apache)

Para cada subdomínio, criar um VirtualHost no Apache do servidor legado:

```apache
# Exemplo: siv.deepfreeze.com.br → /siv_v2/
<VirtualHost *:80>
    ServerName siv.deepfreeze.com.br
    DocumentRoot /var/www/html/siv_v2/app/webroot
    # ... configurações CakePHP
</VirtualHost>

# Exemplo: img.deepfreeze.com.br → /img/
<VirtualHost *:80>
    ServerName img.deepfreeze.com.br
    DocumentRoot /var/www/html/app/webroot
    # Servir apenas imagens
</VirtualHost>
```

### Configuração DNS

Todos os subdomínios apontam para o IP do servidor legado:
```
siv.deepfreeze.com.br     A    217.196.63.163
pdv.deepfreeze.com.br     A    217.196.63.163
img.deepfreeze.com.br     A    217.196.63.163
api.deepfreeze.com.br     A    217.196.63.163
legacy.deepfreeze.com.br  A    217.196.63.163
```

---

## Itens que Precisam ser Atualizados na Virada

### 1. Cron Jobs (150+ jobs)

Todos os cron jobs chamam `https://www.deepfreeze.com.br/...`. Precisam ser atualizados para usar os subdomínios:

| De | Para |
|----|------|
| `curl https://www.deepfreeze.com.br/siv_v2/...` | `curl https://siv.deepfreeze.com.br/...` |
| `curl https://www.deepfreeze.com.br/pdv/...` | `curl https://pdv.deepfreeze.com.br/...` |
| `curl https://www.deepfreeze.com.br/deepfreeze/...` | `curl https://api.deepfreeze.com.br/...` |
| `curl https://www.deepfreeze.com.br/producao/...` | `curl https://legacy.deepfreeze.com.br/producao/...` |

**CRÍTICO:** iFood polling (a cada 30s) deve ser o primeiro a migrar.

### 2. Callbacks de Pagamento

Gateways de pagamento (Cielo, Rede, Ame) enviam callbacks para URLs configuradas nos painéis deles. Precisam ser atualizadas:

| Gateway | URL Atual | Nova URL |
|---------|-----------|----------|
| Cielo | `www.deepfreeze.com.br/.../cielo/aguardar_confirmacao_pagamento/` | `api.deepfreeze.com.br/.../cielo/aguardar_confirmacao_pagamento/` |
| Rede | `www.deepfreeze.com.br/rede/...` | `api.deepfreeze.com.br/rede/...` |
| Ame | `www.deepfreeze.com.br/ame/...` | `api.deepfreeze.com.br/ame/...` |

### 3. Integrações Externas

| Integração | O que precisa mudar |
|-----------|---------------------|
| iFood | URL de polling e webhook |
| Google Shopping | Feed XML em `/deepfreeze/siv/shopping/shopping.php` |
| Facebook Catalog | Feed em `/deepfreeze/siv/shopping/facebook.php` |
| WhatsApp Webhook | URL de recebimento de mensagens |
| SMS (respostas) | URL de callback |

### 4. Imagens no Site Novo (sync)

Atualizar `config/legacy.php` no sync:
```php
// De:
'image_base_url' => 'https://www.deepfreeze.com.br',
// Para:
'image_base_url' => 'https://img.deepfreeze.com.br',
```

### 5. Equipe SIV e PDV

Comunicar à equipe para acessar:
- SIV: `https://siv.deepfreeze.com.br` (em vez de `www.deepfreeze.com.br/siv`)
- PDV: `https://pdv.deepfreeze.com.br` (em vez de `www.deepfreeze.com.br/pdv`)

---

## Checklist de Execução (ordem recomendada)

### Fase 1 — Preparação (antes de trocar DNS)
- [ ] Criar registros DNS para todos os subdomínios (apontando para 217.196.63.163)
- [ ] Configurar VirtualHosts no Apache do servidor legado para cada subdomínio
- [ ] Instalar certificados SSL (Let's Encrypt) em cada subdomínio
- [ ] Testar acesso a cada subdomínio (siv, pdv, img, api, legacy)
- [ ] Testar imagens via `img.deepfreeze.com.br`
- [ ] Atualizar cron jobs para usar novos subdomínios
- [ ] Testar cron jobs com novos subdomínios
- [ ] Atualizar callbacks dos gateways de pagamento
- [ ] Atualizar URLs de integração iFood
- [ ] Atualizar feeds Google Shopping e Facebook Catalog

### Fase 2 — Virada (troca do DNS)
- [ ] Alterar DNS: `www.deepfreeze.com.br` → IP do kicolApps (servidor sync)
- [ ] Alterar DNS: `deepfreeze.com.br` → IP do kicolApps
- [ ] Atualizar `config/legacy.php` no sync: `image_base_url` → `https://img.deepfreeze.com.br`
- [ ] Limpar cache do sync: `php artisan optimize:clear`

### Fase 3 — Validação (após virada)
- [ ] Verificar site novo funcionando em `www.deepfreeze.com.br`
- [ ] Verificar imagens carregando via `img.deepfreeze.com.br`
- [ ] Verificar SIV acessível em `siv.deepfreeze.com.br`
- [ ] Verificar PDV acessível em `pdv.deepfreeze.com.br`
- [ ] Verificar iFood polling funcionando
- [ ] Verificar pagamentos (fazer teste Cielo)
- [ ] Verificar envio de newsletters/emails
- [ ] Verificar NF-e/NFC-e emitindo
- [ ] Verificar PDV sincronizando com SIV
- [ ] Monitorar logs por 24h

---

## Safety Net: Redirects no Laravel (CRÍTICO)

Mesmo com subdomínios configurados e cron jobs atualizados, sempre vai existir alguma referência esquecida — bookmarks de funcionários, integrações de terceiros, links hardcoded, crawlers do Google com URLs antigas.

**O Laravel precisa redirecionar automaticamente qualquer path do legado para o subdomínio correto.** Assim tudo funciona como se não houvesse migração.

### Middleware de Redirect Legado

Criar um middleware `RedirectLegacyPaths` que intercepta requests antes de chegar nas rotas do sync:

```php
// app/Http/Middleware/RedirectLegacyPaths.php

class RedirectLegacyPaths
{
    // Mapeamento: prefixo do path → subdomínio de destino
    private const REDIRECTS = [
        'siv_v2'     => 'siv.deepfreeze.com.br',
        'pdv'        => 'pdv.deepfreeze.com.br',
        'deepfreeze' => 'api.deepfreeze.com.br',
        'mobile'     => 'api.deepfreeze.com.br',
        'app'        => 'legacy.deepfreeze.com.br',
        'producao'   => 'legacy.deepfreeze.com.br',
        'cielo'      => 'api.deepfreeze.com.br',
        'rede'       => 'api.deepfreeze.com.br',
        'ame'        => 'api.deepfreeze.com.br',
    ];

    // Paths de imagem — redirect direto para img.deepfreeze.com.br
    private const IMAGE_PREFIXES = [
        'img/pratos',
        'img/marcas',
        'img/categorias',
    ];

    public function handle($request, $next)
    {
        $path = $request->path();

        // Redirect de imagens
        foreach (self::IMAGE_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return redirect('https://img.deepfreeze.com.br/' . $path, 301);
            }
        }

        // Redirect de aplicações legadas
        $firstSegment = explode('/', $path)[0];
        if (isset(self::REDIRECTS[$firstSegment])) {
            $subdomain = self::REDIRECTS[$firstSegment];
            $fullUrl = 'https://' . $subdomain . '/' . $path;

            // Query string preservada
            if ($request->getQueryString()) {
                $fullUrl .= '?' . $request->getQueryString();
            }

            // POST/PUT/PATCH: usar 307 (mantém o método HTTP)
            // GET: usar 301 (permanente, cacheia no browser)
            $code = $request->isMethod('GET') ? 301 : 307;

            return redirect($fullUrl, $code);
        }

        return $next($request);
    }
}
```

### Registrar o Middleware

No `app/Http/Kernel.php`, adicionar no grupo `web` (ANTES de todas as outras rotas):

```php
protected $middleware = [
    \App\Http\Middleware\RedirectLegacyPaths::class, // PRIMEIRO — antes de tudo
    // ... outros middlewares
];
```

### Por que isso é essencial

| Cenário | Sem redirect | Com redirect |
|---------|-------------|-------------|
| Funcionário acessa `www.deepfreeze.com.br/siv_v2` | 404 | Redireciona para `siv.deepfreeze.com.br/siv_v2` |
| Cron job esquecido chama `www.deepfreeze.com.br/deepfreeze/...` | 404 — job falha silenciosamente | Redireciona para `api.deepfreeze.com.br/deepfreeze/...` |
| Cielo envia callback para `www.deepfreeze.com.br/cielo/...` | 404 — pagamento não confirma | Redireciona 307 para `api.deepfreeze.com.br/cielo/...` |
| Google indexou imagem em `www.deepfreeze.com.br/img/pratos/...` | 404 — imagem quebrada no Google | 301 para `img.deepfreeze.com.br/img/pratos/...` |
| iFood chama endpoint no www | 404 — pedidos param | 307 para `api.deepfreeze.com.br/...` |

### ATENÇÃO: POST/PUT/PATCH com redirect 307

O código HTTP **307** é essencial para callbacks de pagamento e integrações que fazem POST. Diferente do 301/302, o 307 **preserva o método HTTP** — o browser/cliente refaz o POST no novo destino com o mesmo body.

Sem 307, um POST seria convertido em GET no redirect, e a integração falharia.

---

## Riscos e Mitigação

| Risco | Probabilidade | Mitigação |
|-------|--------------|-----------|
| DNS demora para propagar | Média | Fazer virada em horário de baixo tráfego, TTL baixo antes |
| Algum cron job esquecido | Alta | Listar TODOS via `crontab -l` no servidor, buscar `deepfreeze.com.br` |
| Callback de pagamento não atualizado | Média | Testar transação de R$1 em cada gateway após virada |
| Referências hardcoded no código legado | Alta | Buscar `www.deepfreeze.com.br` no código e avaliar cada ocorrência |
| iFood para de funcionar | Alta | Ser o primeiro item validado após virada |

---

## Estimativa

- Preparação (DNS, VirtualHosts, SSL, cron jobs): ~4h
- Atualização de integrações (gateways, iFood, feeds): ~2h
- Virada do DNS: ~15min
- Validação completa: ~2h
- Monitoramento pós-virada: 24h
