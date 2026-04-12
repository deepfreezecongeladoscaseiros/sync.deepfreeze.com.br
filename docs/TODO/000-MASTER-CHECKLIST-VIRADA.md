# MASTER CHECKLIST — Virada do Site Deep Freeze

**Documento unificado de todas as etapas para lançamento do site novo.**
**Última atualização:** 03/04/2026

---

## Visão Geral dos Servidores

| Servidor | IP | Função Atual | Função Pós-Virada |
|----------|-----|-------------|-------------------|
| **DeepFreeze** | 187.33.4.212 | Tudo (site, SIV, PDV, banco, NF-e, iFood) | SIV, PDV, NF-e, iFood (sem site público) |
| **WeFreeze** | 177.70.123.9 | Site WeFreeze + WhatsApp META | Sem alteração |
| **KicolApps** | 217.196.63.163 | Homologação do site novo | Homologação (sem alteração) |
| **Hostinger** | 168.231.91.29 | Banco sync (homologação) | **Produção: site novo + banco principal** |

## Visão Geral dos Domínios

| Domínio | Aponta Hoje | Aponta Pós-Virada |
|---------|------------|-------------------|
| `www.deepfreeze.com.br` | DeepFreeze (187.33.4.212) | **Hostinger (168.231.91.29)** |
| `deepfreeze.com.br` | DeepFreeze (187.33.4.212) | **Hostinger (168.231.91.29)** |
| `siv.deepfreeze.com.br` | Não existe | **DeepFreeze (187.33.4.212)** |
| `pdv.deepfreeze.com.br` | Não existe | **DeepFreeze (187.33.4.212)** |
| `img.deepfreeze.com.br` | Não existe | **DeepFreeze (187.33.4.212)** |
| `api.deepfreeze.com.br` | Não existe | **DeepFreeze (187.33.4.212)** |
| `legacy.deepfreeze.com.br` | Não existe | **DeepFreeze (187.33.4.212)** |
| `sync.deepfreeze.com.br` | KicolApps (217.196.63.163) | KicolApps (homologação) |
| `site.deepfreeze.com.br` | Não existe | **Hostinger (168.231.91.29)** |

---

## FASE 1 — PREPARAÇÃO DE INFRAESTRUTURA
*Pode ser feito a qualquer momento, sem impacto em produção.*

### 1.1 Firewall — Liberar acessos cruzados

- [ ] **DeepFreeze `block.sh`**: Adicionar IP da Hostinger (168.231.91.29)
- [ ] **WeFreeze `block.sh`**: Adicionar IP da Hostinger (168.231.91.29)
- [ ] **Hostinger firewall**: Liberar IP do DeepFreeze (187.33.4.212)
- [ ] **Hostinger firewall**: Liberar IP do WeFreeze (177.70.123.9)
- [ ] **Hostinger firewall**: Liberar porta 3306 apenas para IPs autorizados
- [ ] Testar conectividade porta 3306 entre os 3 servidores

**Referência:** `docs/TODO/migracao-banco-hostinger.md`

### 1.2 SSH — Acesso ao servidor Hostinger

- [ ] Subir chave `ed25519_kicol` para o servidor Hostinger
- [ ] Configurar host `hostinger-df` no `~/.ssh/config`
- [ ] Testar `ssh hostinger-df`

### 1.3 Subdomínios — Criar registros DNS

- [ ] `siv.deepfreeze.com.br` → A → 187.33.4.212
- [ ] `pdv.deepfreeze.com.br` → A → 187.33.4.212
- [ ] `img.deepfreeze.com.br` → A → 187.33.4.212
- [ ] `api.deepfreeze.com.br` → A → 187.33.4.212
- [ ] `legacy.deepfreeze.com.br` → A → 187.33.4.212
- [ ] `site.deepfreeze.com.br` → A → 168.231.91.29
- [ ] Aguardar propagação DNS (até 48h, geralmente <2h)

**Referência:** `docs/TODO/plano-migracao-dominios-virada.md`

### 1.4 Apache — VirtualHosts no servidor DeepFreeze

Configurar para que cada subdomínio sirva o path correto:

- [ ] `siv.deepfreeze.com.br` → DocumentRoot `/var/www/html/siv_v2/app/webroot`
- [ ] `pdv.deepfreeze.com.br` → DocumentRoot `/var/www/html/pdv/app/webroot`
- [ ] `img.deepfreeze.com.br` → DocumentRoot `/var/www/html/app/webroot` (servir `/img/`)
- [ ] `api.deepfreeze.com.br` → DocumentRoot `/var/www/html` (servir `/deepfreeze/` e `/mobile/`)
- [ ] `legacy.deepfreeze.com.br` → DocumentRoot `/var/www/html` (fallback completo)

### 1.5 SSL — Certificados para subdomínios

- [ ] Instalar certbot nos subdomínios do DeepFreeze
- [ ] SSL para `siv.deepfreeze.com.br`
- [ ] SSL para `pdv.deepfreeze.com.br`
- [ ] SSL para `img.deepfreeze.com.br`
- [ ] SSL para `api.deepfreeze.com.br`
- [ ] SSL para `legacy.deepfreeze.com.br`
- [ ] SSL para `site.deepfreeze.com.br` (Hostinger)

### 1.6 Testar subdomínios (antes de trocar o www)

- [ ] Acessar `https://siv.deepfreeze.com.br` → SIV funciona
- [ ] Acessar `https://pdv.deepfreeze.com.br` → PDV funciona
- [ ] Acessar `https://img.deepfreeze.com.br/img/pratos/big/` → Imagens carregam
- [ ] Acessar `https://api.deepfreeze.com.br/deepfreeze/` → Scripts respondem
- [ ] Acessar `https://site.deepfreeze.com.br` → Site novo funciona

---

## FASE 2 — MIGRAÇÃO DO BANCO DE DADOS
*Fazer em horário de baixo movimento. Pode ser feito antes da virada do domínio.*

### 2.1 Preparar MySQL na Hostinger

- [ ] Instalar MySQL 8.0 (ou versão compatível)
- [ ] Configurar `bind-address = 0.0.0.0`
- [ ] Criar usuário com acesso remoto
- [ ] Configurar charset/collation compatível com MySQL 5.5 do legado

### 2.2 Migrar dados

- [ ] Dump do banco `novo` no DeepFreeze
- [ ] Dump do banco `pdv_deepfreeze` no DeepFreeze
- [ ] Transferir dumps para Hostinger
- [ ] Importar dumps
- [ ] Validar integridade (contagem de tabelas e registros críticos)

### 2.3 Atualizar conexões — WeFreeze (CRÍTICO — antes da virada do domínio!)

Trocar `www.deepfreeze.com.br` por IP fixo em TODOS os arquivos:

- [ ] `app/Config/database.php` → IP da Hostinger
- [ ] `siv_v2/app/Config/database.php` → IP da Hostinger
- [ ] `mobile/app/Config/database.php` → IP da Hostinger
- [ ] `pdv/app/Config/database.php` → IP da Hostinger
- [ ] `deepfreeze/modulos/MySQLSiv.php` → IP da Hostinger
- [ ] `gravacao.py` → IP da Hostinger

### 2.4 Atualizar conexões — DeepFreeze

Trocar `localhost` por IP da Hostinger:

- [ ] `app/Config/database.php`
- [ ] `siv_v2/app/Config/database.php`
- [ ] `mobile/app/Config/database.php`
- [ ] `pdv/app/Config/database.php`
- [ ] `deepfreeze/modulos/MySQLSiv.php`
- [ ] Scripts em `deepfreeze/siv/` com conexão hardcoded

### 2.5 Atualizar conexão — Site Novo (Hostinger)

- [ ] `.env`: `DB_HOST_LEGACY` → `localhost` (banco agora local)

### 2.6 Validar conexões

- [ ] DeepFreeze → Hostinger MySQL: `mysql -h 168.231.91.29 -u root -p -e "SELECT 1"`
- [ ] WeFreeze → Hostinger MySQL: testar conexão
- [ ] Site novo → banco local: testar via artisan tinker
- [ ] Testar latência a partir de cada servidor

**Referência:** `docs/TODO/migracao-banco-hostinger.md`

---

## FASE 3 — PREPARAÇÃO DO SITE NOVO PARA PRODUÇÃO
*Ajustes no Laravel antes da virada.*

### 3.1 Middleware de redirect legado

- [ ] Criar `RedirectLegacyPaths` middleware no Laravel
- [ ] Mapear todos os paths: `/siv_v2/`, `/pdv/`, `/deepfreeze/`, `/mobile/`, `/img/`, `/cielo/`, `/rede/`, `/ame/`
- [ ] GET → redirect 301, POST/PUT → redirect 307 (preserva método HTTP)
- [ ] Registrar no Kernel como primeiro middleware global
- [ ] Testar cada redirect

### 3.2 Atualizar config de imagens

- [ ] `config/legacy.php`: `image_base_url` → `https://img.deepfreeze.com.br`
- [ ] Testar carregamento de imagens nos produtos

### 3.3 Features pendentes do site

- [ ] Modal "Entrega na minha região" — **FEITO**
- [ ] Ícones flutuantes WhatsApp/Instagram — **FEITO**
- [ ] Painel de usuários admin — **FEITO**
- [ ] Painel de estatísticas CEP — **FEITO**
- [ ] Login com CPF + Nascimento — **FEITO**
- [ ] Informações nutricionais — **FEITO**
- [ ] Carrossel de avaliações — **FEITO**
- [ ] Login social Google/Facebook — **PÓS-VIRADA** (docs/TODO/)
- [ ] Estoque personalizado por loja — **PÓS-VIRADA** (docs/TODO/)

### 3.4 Deploy do site novo na Hostinger

- [ ] Clonar repositório na Hostinger
- [ ] Configurar `.env` de produção (banco local, chaves, etc.)
- [ ] `composer install --optimize-autoloader --no-dev`
- [ ] `php artisan migrate --force`
- [ ] `php artisan optimize`
- [ ] Configurar Apache/Nginx na Hostinger
- [ ] Testar site em `https://site.deepfreeze.com.br`

---

## FASE 4 — ATUALIZAR INTEGRAÇÕES EXTERNAS
*Fazer ANTES da virada do domínio para que os callbacks já apontem para os subdomínios.*

### 4.1 Cron jobs (150+ jobs no DeepFreeze)

- [ ] Exportar crontab atual: `crontab -l > /var/www/backups/crontab_antes_virada.txt`
- [ ] Substituir `www.deepfreeze.com.br/siv_v2/` → `siv.deepfreeze.com.br/`
- [ ] Substituir `www.deepfreeze.com.br/pdv/` → `pdv.deepfreeze.com.br/`
- [ ] Substituir `www.deepfreeze.com.br/deepfreeze/` → `api.deepfreeze.com.br/deepfreeze/`
- [ ] Substituir `www.deepfreeze.com.br/producao/` → `legacy.deepfreeze.com.br/producao/`
- [ ] Testar jobs críticos manualmente (iFood, newsletters, NF-e)

### 4.2 Gateways de pagamento

- [ ] **Cielo**: Atualizar URL de callback no painel Cielo
- [ ] **Rede (e-Rede)**: Atualizar URL de callback
- [ ] **Ame**: Atualizar URL de callback
- [ ] Testar transação de R$1 em cada gateway após atualizar

### 4.3 Integrações externas

- [ ] **iFood**: Atualizar URL de polling para `api.deepfreeze.com.br`
- [ ] **Google Shopping**: Feed XML → `api.deepfreeze.com.br/deepfreeze/siv/shopping/shopping.php`
- [ ] **Facebook Catalog**: Feed → `api.deepfreeze.com.br/deepfreeze/siv/shopping/facebook.php`
- [ ] **WhatsApp Webhook**: Verificar se usa domínio ou IP
- [ ] **Neura Partner IA**: Verificar acesso ao banco (porta 3306 liberada)

### 4.4 Comunicar equipe

- [ ] Informar equipe SIV: novo acesso em `https://siv.deepfreeze.com.br`
- [ ] Informar lojas/PDV: novo acesso em `https://pdv.deepfreeze.com.br`
- [ ] Informar equipe de marketing: painel admin em `https://www.deepfreeze.com.br/admin`

---

## FASE 5 — VIRADA (DIA D)
*Executar em horário de baixo movimento (madrugada ou domingo cedo).*
*Tempo estimado: ~30 minutos se tudo estiver preparado.*

### 5.1 Pré-virada (verificações finais)

- [ ] Todos os subdomínios funcionando? ✓
- [ ] Banco migrado e conexões atualizadas? ✓
- [ ] Cron jobs atualizados? ✓
- [ ] Callbacks de pagamento atualizados? ✓
- [ ] Site novo testado em `site.deepfreeze.com.br`? ✓
- [ ] Middleware de redirect configurado? ✓
- [ ] Backup completo do banco feito hoje? ✓

### 5.2 Baixar TTL do DNS (fazer 24-48h antes)

- [ ] Reduzir TTL de `www.deepfreeze.com.br` para 300 (5 minutos)
- [ ] Reduzir TTL de `deepfreeze.com.br` para 300

### 5.3 Executar a virada

- [ ] Alterar DNS: `www.deepfreeze.com.br` → 168.231.91.29 (Hostinger)
- [ ] Alterar DNS: `deepfreeze.com.br` → 168.231.91.29 (Hostinger)
- [ ] Limpar cache no site novo: `php artisan optimize:clear`

### 5.4 Validação imediata (primeiros 10 minutos)

- [ ] Site novo carrega em `www.deepfreeze.com.br`?
- [ ] Imagens carregam via `img.deepfreeze.com.br`?
- [ ] Login de cliente funciona?
- [ ] Carrinho funciona?
- [ ] Produtos com preço correto?

### 5.5 Validação operacional (próximos 30 minutos)

- [ ] SIV acessível em `siv.deepfreeze.com.br`?
- [ ] PDV acessível em `pdv.deepfreeze.com.br`?
- [ ] iFood recebendo pedidos?
- [ ] NF-e/NFC-e emitindo?
- [ ] Emails sendo enviados?
- [ ] WhatsApp (WeFreeze) funcionando?
- [ ] Redirect de `www.deepfreeze.com.br/siv_v2/` funciona?

### 5.6 Rollback (se necessário)

Se algo crítico quebrar e não puder ser resolvido em 15 minutos:

- [ ] Reverter DNS: `www.deepfreeze.com.br` → 187.33.4.212 (DeepFreeze)
- [ ] Esperar propagação (~5min com TTL baixo)
- [ ] Verificar que tudo voltou ao normal
- [ ] Analisar o problema e tentar novamente outro dia

---

## FASE 6 — PÓS-VIRADA
*Primeiras semanas após o lançamento.*

### 6.1 Monitoramento (primeiras 48h)

- [ ] Monitorar logs de erro do Laravel
- [ ] Monitorar logs do Apache em todos os servidores
- [ ] Verificar se algum cron job falhou
- [ ] Verificar se algum redirect não mapeado gera 404
- [ ] Monitorar estatísticas de CEP (painel admin)

### 6.2 Restaurar TTL do DNS

- [ ] Aumentar TTL de volta para 3600 (1h) ou 86400 (24h)

### 6.3 Limpeza

- [ ] Remover site antigo do servidor DeepFreeze (quando estável)
- [ ] Limpar tabelas órfãs do banco sync (properties, tray_*, etc.)
- [ ] Avaliar desligar MySQL local do DeepFreeze (se tudo roda na Hostinger)

### 6.4 Features pós-virada

- [ ] Login social Google + Facebook (`docs/TODO/login-social-google-facebook.md`)
- [ ] Estoque personalizado por loja/CEP (`docs/TODO/estoque-personalizado-por-loja.md`)
- [ ] Avaliar migração do banco do WeFreeze para Hostinger também

---

## Índice de Documentação

| Documento | Conteúdo |
|-----------|----------|
| `docs/TODO/000-MASTER-CHECKLIST-VIRADA.md` | **Este documento** — checklist unificado |
| `docs/TODO/plano-migracao-dominios-virada.md` | Detalhes dos subdomínios, VirtualHosts, cron jobs, redirects |
| `docs/TODO/migracao-banco-hostinger.md` | Detalhes da migração do banco, conexões, firewall |
| `docs/TODO/login-social-google-facebook.md` | Login social (pós-virada) |
| `docs/TODO/estoque-personalizado-por-loja.md` | Estoque por CEP/loja (pós-virada) |
