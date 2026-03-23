# Sync Deep Freeze - Instruções do Projeto

## Sobre o Projeto
Sistema de sincronização e loja virtual para a **Deep Freeze Congelados Caseiros**.
Integra um sistema legado com a plataforma Tray Commerce e serve como storefront (loja virtual).

## Stack
- **Backend:** Laravel 10 (PHP 8.1+)
- **Frontend:** Blade + Bootstrap 3 (storefront) + AdminLTE 3 (admin)
- **Build:** Vite 5
- **Admin Panel:** AdminLTE 3 (jeroennoten/laravel-adminlte)
- **Auth Admin:** Guard `web` → tabela `users` (bcrypt)
- **Auth Clientes:** Guard `customer` → tabela `pessoas` do banco legado (MD5)
- **API Auth:** Laravel Sanctum
- **Banco de dados:** Dois bancos — `mysql` (layout/admin do sync) + `mysql_legacy` (dados de negócio do SIV)

## Estrutura Principal

### Controllers
- `app/Http/Controllers/Admin/` — Painel administrativo (CRUD de produtos, categorias, marcas, layout, menus, banners, etc.)
- `app/Http/Controllers/Storefront/` — Loja virtual pública (Home, Categoria, Produto, Cart, Checkout, Customer)
- `app/Http/Controllers/Storefront/Auth/` — Login (MD5) e Registro (grava em `pessoas`) de clientes
- `app/Http/Controllers/Api/` — API REST (V1 + Webhooks)

### Models — Banco Legado (`app/Models/Legacy/`)
Todos usam `$connection = 'mysql_legacy'` + `$columnMap` + `getAttribute()` override.

**Pedidos:** `Pedido`, `PedidoProduto`, `SessaoPedido`, `StatusPedido`, `Status`
**Clientes:** `Pessoa` (implements Authenticatable), `Endereco`
**Pagamento:** `FormaPagamento`, `LojaFormaPagamento`, `PagamentoCielo`
**Entrega:** `Logradouro`, `EntregaRegiao`, `EntregaRegiaoLogradouro`, `EntregaPeriodo`, `VeiculoPeriodo`, `LojaEntregaRegiao`, `EntregaDataBloqueada`, `PrecoFrete`, `PedidoInformacaoFrete`, `Loja`
**Descontos:** `Promocional`, `PedidoDesconto`
**E-mails:** `NewsletterPessoa`

### Models — Banco Sync (`app/Models/`)
**Catálogo (mysql_legacy):** `Product`, `Category`, `Brand`, `Manufacturer`, `ProductImage`, `Tag`, `OtmEstoqueLoja`
**Layout (mysql):** `Banner`, `DualBanner`, `SingleBanner`, `FeatureBlock`, `InfoBlock`, `StepBlock`, `HomeSection`, `HomeBlock`, `Menu`, `MenuItem`, `ThemeSetting`, `CookieConsent`, `SocialNetwork`, `Page`
**Deprecated:** `Order`, `OrderItem` — substituídos por Legacy/Pedido e Legacy/PedidoProduto

### Services
- `LegacyOrderService.php` — Criação de pedidos no banco legado (pedidos + pedidos_produtos + status_pedidos)
- `CartService.php` — Carrinho session-based
- `PaymentService.php` — Formas de pagamento e registro em pagamentos_cielo
- `ShippingService.php` — CEP → região → loja → períodos → cálculo de frete
- `CouponService.php` — Validação e aplicação de cupons (promocionais + pedidos_descontos)
- `TrayCommerceService.php` — Integração com Tray Commerce

### Rotas
- `/` — Homepage da loja
- `/categoria/{slug}` — Listagem de categoria
- `/produto/{sku}` — Produto por SKU
- `/{categorySlug}/{productSlug}` — Produto por slug amigável
- `/carrinho/*` — Carrinho (session-based, sem auth)
- `/checkout/*` — Checkout (requer guard `customer`)
- `/minha-conta/pedidos` — Histórico de pedidos do cliente
- `/entrega/*` — AJAX: consultar CEP, calcular frete, períodos, lojas retirada
- `/cupom/validar` — AJAX: validar cupom de desconto
- `/pagamento/*` — Callback e retorno do gateway
- `/enderecos/*` — AJAX: endereços salvos do cliente
- `/login`, `/register`, `/logout` — Auth de clientes (guard `customer`)
- `/contato` — Formulário de contato
- `/admin/*` — Painel administrativo (guard `web`)
- `/admin/login`, `/admin/logout` — Auth do admin
- `/api/*` — API REST
- `/css/theme.css` — CSS dinâmico
- `/{slug}` — Páginas internas (wildcard, DEVE ficar por último)

### Auth — Dual Guard
- **Guard `customer`** (storefront): tabela `pessoas` do banco legado, senha MD5. Provider: `LegacyCustomerProvider`. Middleware `customer.guard` aplica `Auth::shouldUse('customer')` em todas as rotas da storefront.
- **Guard `web`** (admin): tabela `users` do banco sync, senha bcrypt. Inalterado.

### Fluxo de Pedidos (grava no banco legado)
1. Carrinho (session) → CartService
2. Checkout → LegacyOrderService::createOrder()
3. Gera sessão única (SessaoPedido::generate()) → INSERT `sessoes_pedidos`
4. Cria pedido com `origem='INTERNET'`, `finalizado=0` → INSERT `pedidos`
5. Cria itens com código do produto → INSERT `pedidos_produtos`
6. Registra status → INSERT `status_pedidos` (statu_id=1)
7. Aplica cupom se informado → `pedidos_descontos` + `promocionais`
8. Salva info de frete → `pedidos_informacoes_frete`
9. Envia e-mail de confirmação via Laravel

### Helpers
- `app/Helpers/ThemeHelper.php` — Helper de tema (autoloaded via composer)

## Convenções

### Código
- Idioma do código: inglês (nomes de classes, métodos, variáveis)
- Idioma da UI: português brasileiro
- Comentários: português brasileiro
- Migrations devem ter comentários explicando cada campo e enums
- Seguir PSR-4 e padrões Laravel

### Banco de Dados
- Campos `legacy_id` — IDs do sistema legado
- Campos `tray_id` — IDs da Tray Commerce
- Usar Tinker para consultas ao banco

### Git
- Mensagens de commit em português
- Branch principal: `main`

## Integrações
- **Sistema Legado:** Sincronização de categorias, marcas, fabricantes, produtos e imagens
- **Tray Commerce:** Exportação de dados (categorias, marcas, produtos) via API
- **Webhooks:** Recebimento de atualizações de produtos, preços e estoque

## Documentação Existente
- `API_DOCUMENTATION.md` — Documentação da API pública
- `WEBHOOK_INTEGRATION_GUIDE.md` — Guia de webhooks
- `WEBHOOK_ESPECIFICACAO_SISTEMA_LEGADO.md` — Especificação de webhooks do legado
- `DOCUMENTACAO_EXPORTACAO_PRODUTOS.md` — Documentação de exportação
- `ANALISE_CAMPOS_FALTANTES.md` — Análise de campos
- `ANALISE_SISTEMA_IMAGENS_PRODUTOS.md` — Sistema de imagens
- `etapas_do_projeto.md` — Etapas do projeto
- `LAYOUT_SYSTEM_README.md` — Sistema de layout
