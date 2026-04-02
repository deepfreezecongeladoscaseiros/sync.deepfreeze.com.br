# TODO: Login Social (Google + Facebook)

**Prioridade:** Pós-virada do site
**Complexidade:** Média
**Data da análise:** 02/04/2026

---

## Resumo

Implementar login social com Google e Facebook na tela de login do storefront (`/login`), replicando a funcionalidade do site legado mas com validação de token server-side (segurança).

## Como funciona no legado

O login social é 100% client-side. Os SDKs JavaScript obtêm o email do usuário e fazem POST para `/pessoas/login_social` com email + nome. O servidor busca na `pessoas` por `email_primario`, cria se não existe, e faz login.

### Fluxo do legado

1. Usuário clica "Login com Google" ou "Login com Facebook"
2. SDK JavaScript do provider abre popup de autenticação
3. Provider retorna email + nome para o JS
4. JS faz POST para `/pessoas/login_social` com `data[email]`, `data[nome]`, `data[rede]`
5. Servidor busca `pessoas.email_primario = email`
6. Se não encontra, cria registro novo (sem senha)
7. Faz login via sessão

### Problemas do legado (que devemos corrigir no novo)

| Problema | Gravidade |
|----------|-----------|
| Google SDK `platform.js` (v1) descontinuado março/2023 — não funciona mais | Crítico |
| Zero validação server-side — qualquer email POST = acesso à conta | Crítico (segurança) |
| Sem vínculo social — tabela `pessoas` não tem `google_id` ou `facebook_id` | Arquitetural |
| Conta criada via social tem `senha = NULL` | UX |
| Falhas silenciosas — se Facebook não retorna email, nada acontece | UX |

### Credenciais existentes

| Provider | Tipo | Valor |
|----------|------|-------|
| Facebook | App ID | `1909311669134429` |
| Google | Client ID | `579967086280-lip2kqf14i7dufh5eehn329352bkg6nq.apps.googleusercontent.com` |

**Nota:** O Client ID do Google é do SDK v1 descontinuado. Será necessário criar novas credenciais no Google Console para o Google Identity Services (GIS).

## Impacto nos clientes existentes

**Zero impacto.** Clientes que já usam login social têm email cadastrado na `pessoas`. O novo login social fará o mesmo lookup por `email_primario`. Único cenário: clientes com `senha = NULL` não conseguem usar login email+senha (mas login social e "Esqueci minha senha" funcionam).

## Proposta de implementação para o site novo

### Abordagem: Laravel Socialite + SDKs modernos

1. **Google** — Google Identity Services (GIS) com validação de ID token no servidor
2. **Facebook** — Facebook JS SDK + validação do access token via Graph API no servidor

### Pacotes necessários

- `laravel/socialite` — abstrai OAuth2 para Google e Facebook
- Ou implementação manual com validação de token (mais leve)

### Arquivos a criar/modificar

| Arquivo | Ação |
|---------|------|
| Controller para callback social | Criar |
| Rota POST para receber token social | Criar |
| View login (botões Google/Facebook) | Modificar `login.blade.php` |
| Config (credenciais OAuth) | Modificar `config/services.php` e `.env` |

### Fluxo proposto (seguro)

1. Usuário clica botão "Entrar com Google" ou "Entrar com Facebook"
2. SDK JavaScript abre popup e retorna **ID token** (não apenas email)
3. JS envia o token para endpoint Laravel via POST
4. Servidor **valida o token** com o provider (Google API / Facebook Graph API)
5. Extrai email do token validado (confiável)
6. Busca `pessoas.email_primario` — se não existe, cria
7. Faz login via guard `customer`
8. Retorna redirect

### Configurações necessárias

**Google Console (console.cloud.google.com):**
- Criar credenciais OAuth 2.0 para o domínio do novo site
- Adicionar `https://sync.deepfreeze.com.br` (e futuro `https://www.deepfreeze.com.br`) como origens autorizadas
- Obter novo Client ID

**Facebook Developers (developers.facebook.com):**
- Verificar se o App ID existente aceita o novo domínio
- Adicionar `sync.deepfreeze.com.br` como domínio válido
- Ou criar novo App para o novo site

## Arquivos de referência no legado

| Arquivo | O que contém |
|---------|-------------|
| `siv_deepfreeze/html/mobile/app/webroot/js/social_login.js` | JS client-side completo (Google + Facebook) |
| `siv_deepfreeze/html/mobile/app/Controller/Component/PessoasComponent.php:663-714` | Server-side `login_social()` — busca/cria pessoa por email |
| `siv_deepfreeze/html/mobile/app/View/Pessoas/entrar.ctp:30-52` | View com botões sociais e meta tags |
| `siv_deepfreeze/html/mobile/app/webroot/js/plugins_mobile.js:61-67` | Lazy-load do Facebook JS SDK |
| `siv_deepfreeze/Dump_structure_novo/novo_pessoas.sql` | Schema da tabela pessoas (sem colunas social) |

## Estimativa

- Configuração OAuth (Google Console + Facebook Dev): ~30min
- Backend (controller, validação de token, login): ~2h
- Frontend (botões na tela de login, JS): ~1h
- Testes: ~30min
