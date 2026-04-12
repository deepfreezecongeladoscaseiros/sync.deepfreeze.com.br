# TODO: Migração do Banco de Dados para Hostinger + Infraestrutura de Produção

**Prioridade:** Antes da virada do site
**Complexidade:** Alta
**Data da análise:** 03/04/2026

---

## Contexto

O site novo da Deep Freeze (`sync.deepfreeze.com.br`, futuro `site.deepfreeze.com.br`) está em homologação no servidor kicolApps (217.196.63.163). Na virada, ele vai para uma **VPS da Deep Freeze na Hostinger**. O kicolApps NÃO será alterado — é ambiente de homologação.

O banco de dados principal da Deep Freeze hoje roda no servidor legado (187.33.4.212) em MySQL 5.5. O plano é migrar esse banco para a VPS da Hostinger para melhor performance, mesmo sabendo que haverá latência de rede (ambos em São Paulo mas redes diferentes).

## Servidores Envolvidos

| Servidor | IP | Função | MySQL |
|----------|-----|--------|-------|
| **DeepFreeze (legado)** | 187.33.4.212 | SIV, PDV, storefront legado, NF-e, iFood | MySQL 5.5, porta 3306 |
| **WeFreeze** | 177.70.123.9 | Site WeFreeze + WhatsApp Business API (META) | MySQL 5.5, porta 3307 (local) |
| **KicolApps** | 217.196.63.163 | Homologação do site novo (NÃO ALTERAR) | — |
| **Hostinger (VPS Deep Freeze)** | 168.231.91.29 (srv952.hstgr.io) | Produção do site novo + futuro banco | MySQL a configurar |

## Mapeamento Atual de Conexões ao Banco DeepFreeze

### Servidor DeepFreeze → Banco Local (localhost)

Todas as aplicações do servidor legado conectam via `localhost`:
- `app/` (storefront legado)
- `siv_v2/` (painel admin)
- `pdv/` (ponto de venda)
- `mobile/` (API mobile)
- `deepfreeze/` (scripts, iFood, frete)

Bancos: `novo` (principal) e `pdv_deepfreeze` (PDV/NF-e)

### Servidor WeFreeze → Banco DeepFreeze (REMOTO)

Todas as aplicações do WeFreeze conectam via `www.deepfreeze.com.br` (resolve para 187.33.4.212):

| Aplicação | Host configurado | Banco | Porta |
|-----------|-----------------|-------|-------|
| `app/` (CakePHP) | `www.deepfreeze.com.br` | novo, pdv_deepfreeze | 3306 |
| `siv_v2/` | `www.deepfreeze.com.br` | novo, pdv_deepfreeze | 3306 |
| `mobile/` | `www.deepfreeze.com.br` | novo | 3306 |
| `pdv/` | `www.deepfreeze.com.br` | novo, pdv_deepfreeze | 3306 |
| `deepfreeze/MySQLSiv.php` | `www.deepfreeze.com.br` | novo | 3306 |
| `gravacao.py` (WhatsApp) | `www.deepfreeze.com.br` | — | 3306 |

**ATENÇÃO:** Usam hostname `www.deepfreeze.com.br` e não IP fixo. Na virada do domínio, essas conexões vão apontar para o servidor errado (Hostinger em vez do legado).

### Servidor WeFreeze → Banco LOCAL

| Aplicação | Host | Banco | Porta |
|-----------|------|-------|-------|
| `app/` e `siv_v2/` | `177.70.123.9` | paneh (dados locais) | 3306 |
| `deepfreeze/MySQLSiv.php` | `localhost` | littlepush (WhatsApp/push) | 3307 |

### Servidor KicolApps → Bancos

| Aplicação | Host | Banco | Função |
|-----------|------|-------|--------|
| sync (Laravel) | `srv952.hstgr.io` | sync DB (layout/temas) | Banco próprio do sync |
| sync (Laravel) | `217.196.63.163` | novo (legado) | **HOMOLOGAÇÃO — usa banco de teste** |

### Credenciais MySQL (servidor DeepFreeze produção)

- **User:** root
- **Password:** akjmnyg98547uptoahsjdmna96541
- **Porta:** 3306
- **Bancos:** `novo`, `pdv_deepfreeze`

---

## Plano de Migração do Banco para Hostinger

### Fase 0 — Preparação de Rede (liberar acessos entre servidores)

Cada servidor tem firewall (block.sh) que bloqueia tudo por padrão. Precisamos liberar acessos cruzados:

**No DeepFreeze (block.sh):**
- [x] IP do kicolApps (217.196.63.163) — JÁ LIBERADO
- [ ] IP da Hostinger (168.231.91.29) — ADICIONAR

**No WeFreeze (block.sh):**
- [x] IP do kicolApps (217.196.63.163) — JÁ LIBERADO
- [x] IP do DeepFreeze (187.33.4.212) — JÁ LIBERADO
- [ ] IP da Hostinger (168.231.91.29) — ADICIONAR

**Na Hostinger (firewall/iptables):**
- [ ] IP do DeepFreeze (187.33.4.212) — LIBERAR
- [ ] IP do WeFreeze (177.70.123.9) — LIBERAR
- [ ] Configurar MySQL para aceitar conexões remotas (bind-address)

### Fase 1 — Instalar e configurar MySQL na Hostinger

- [ ] Instalar MySQL 8.0 (ou compatível) na VPS Hostinger
- [ ] Configurar `bind-address = 0.0.0.0` (aceitar conexões remotas)
- [ ] Criar usuário com acesso remoto para os servidores autorizados
- [ ] Configurar firewall na Hostinger liberando porta 3306 apenas para IPs autorizados

### Fase 2 — Migração dos dados

- [ ] Dump completo do banco `novo` no servidor DeepFreeze
- [ ] Dump completo do banco `pdv_deepfreeze`
- [ ] Transferir dumps para a Hostinger
- [ ] Importar dumps na Hostinger
- [ ] Verificar integridade (contagem de tabelas, registros em tabelas críticas)

### Fase 3 — Configurar replicação (opcional mas recomendado)

Configurar replicação Master (Hostinger) → Slave (DeepFreeze legado) ou vice-versa durante período de transição, para manter ambos sincronizados.

### Fase 4 — Atualizar conexões

**No DeepFreeze (187.33.4.212):**
Alterar todas as configs que apontam para `localhost` para apontar para a Hostinger:
- `app/Config/database.php`
- `siv_v2/app/Config/database.php`
- `mobile/app/Config/database.php`
- `pdv/app/Config/database.php`
- `deepfreeze/modulos/MySQLSiv.php`
- Qualquer outro script com conexão hardcoded

**No WeFreeze (177.70.123.9):**
Alterar todas as configs que apontam para `www.deepfreeze.com.br` para apontar para a Hostinger:
- `app/Config/database.php`
- `siv_v2/app/Config/database.php`
- `mobile/app/Config/database.php`
- `pdv/app/Config/database.php`
- `deepfreeze/modulos/MySQLSiv.php`
- `gravacao.py`

**No site novo (Hostinger):**
- `.env`: `DB_HOST_LEGACY` → `localhost` (banco agora é local na Hostinger)

### Fase 5 — Validação

- [ ] Testar acesso ao banco da Hostinger a partir do DeepFreeze
- [ ] Testar acesso ao banco da Hostinger a partir do WeFreeze
- [ ] Testar site novo (sync) com banco local na Hostinger
- [ ] Testar SIV, PDV, mobile no DeepFreeze com banco remoto (Hostinger)
- [ ] Testar iFood (polling a cada 30s)
- [ ] Testar NF-e/NFC-e
- [ ] Testar WhatsApp (WeFreeze)
- [ ] Medir latência: `mysql -h hostinger-ip -e "SELECT 1"` a partir de cada servidor

---

## Impacto da Virada do Domínio nas Conexões do WeFreeze

**PROBLEMA CRÍTICO:** O WeFreeze conecta ao banco do DeepFreeze via `www.deepfreeze.com.br`. Quando o domínio apontar para o Hostinger, as conexões MySQL do WeFreeze vão resolver para o IP errado.

**Solução:** Independente da migração do banco, as configs do WeFreeze DEVEM ser alteradas para usar **IP fixo** em vez de hostname:
- Trocar `www.deepfreeze.com.br` → `187.33.4.212` (ou IP da Hostinger se o banco já migrou)

Isso deve ser feito ANTES da virada do domínio.

---

## Latência Esperada

| Rota | Latência estimada |
|------|-------------------|
| DeepFreeze → Hostinger | ~2-5ms (ambos SP, redes diferentes) |
| WeFreeze → Hostinger | ~2-5ms (ambos SP) |
| Hostinger local (site novo → banco) | <1ms |
| Atual: DeepFreeze localhost | <1ms |
| Atual: WeFreeze → DeepFreeze | ~2-5ms (já é remoto) |

**Análise:** O DeepFreeze perde a vantagem do localhost (~1ms → ~3ms), mas o WeFreeze e o site novo ganham. No geral, a centralização na Hostinger simplifica a arquitetura e oferece hardware provavelmente superior ao servidor legado (que roda Ubuntu 14.04 com disco em 85%).

---

## Riscos

| Risco | Probabilidade | Mitigação |
|-------|--------------|-----------|
| Latência impacta operações de alto volume (iFood, PDV) | Média | Testar com carga real antes de desligar o banco local |
| Dump/import corrompe dados | Baixa | Verificar checksums, manter banco original intacto até validação |
| Firewall bloqueia conexão | Alta | Testar conexão porta 3306 ANTES de migrar |
| MySQL 5.5 → 8.0 incompatibilidade | Média | Testar queries críticas, verificar charset/collation |
| Timeout em queries longas pela rede | Média | Ajustar `wait_timeout`, `net_read_timeout` no MySQL |

---

## Estimativa

- Preparação de rede (firewalls): ~1h
- Instalação MySQL na Hostinger: ~1h
- Dump + transfer + import: ~2-4h (depende do tamanho do banco)
- Atualização de configs: ~2h
- Validação completa: ~4h
- **Total:** ~10-12h de trabalho, recomendado fazer em horário de baixo movimento
