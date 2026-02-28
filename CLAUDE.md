# Sync Deep Freeze - Instruções do Projeto

## Sobre o Projeto
Sistema de sincronização e loja virtual para a **Deep Freeze Congelados Caseiros**.
Integra um sistema legado com a plataforma Tray Commerce e serve como storefront (loja virtual).

## Stack
- **Backend:** Laravel 10 (PHP 8.1+)
- **Frontend:** Blade + Tailwind CSS 3 + Alpine.js
- **Build:** Vite 5
- **Admin Panel:** AdminLTE 3 (jeroennoten/laravel-adminlte)
- **Auth:** Laravel Breeze
- **API Auth:** Laravel Sanctum
- **Template Engine:** Blade com Tailwind CSS dinâmico (theme.css gerado via controller)

## Estrutura Principal

### Controllers
- `app/Http/Controllers/Admin/` — Painel administrativo (CRUD de produtos, categorias, marcas, layout, menus, banners, etc.)
- `app/Http/Controllers/Storefront/` — Loja virtual pública (Home, Categoria, Produto)
- `app/Http/Controllers/Api/` — API REST (V1 + Webhooks)
- `app/Http/Controllers/` — Controllers gerais (Dashboard, Integração, Contato, Páginas)

### Models Principais
- `Product`, `Category`, `Brand`, `Manufacturer` — Catálogo
- `Variant`, `Property`, `PropertyValue` — Variantes e propriedades
- `ProductImage`, `ProductGallery`, `ProductNutritionalInfo` — Mídia e informações
- `Banner`, `DualBanner`, `SingleBanner`, `FeatureBlock`, `InfoBlock`, `StepBlock` — Componentes visuais da loja
- `HomeSection`, `HomeBlock` — Organização da homepage (blocos flexíveis)
- `Menu`, `MenuItem` — Navegação
- `ThemeSetting`, `CookieConsent`, `SocialNetwork`, `Page` — Configurações do site
- `Integration`, `TrayCredential` — Integrações externas
- `*WebhookLog`, `ApiRequestLog` — Logs de integração

### Services
- `TrayCommerceService.php` — Serviço de integração com Tray Commerce

### Rotas
- `/` — Homepage da loja
- `/categoria/{slug}` — Listagem de categoria
- `/produto/{sku}` — Produto por SKU
- `/{categorySlug}/{productSlug}` — Produto por slug amigável
- `/contato` — Formulário de contato
- `/{slug}` — Páginas internas (wildcard, DEVE ficar por último)
- `/admin/*` — Painel administrativo (autenticado)
- `/api/*` — API REST
- `/css/theme.css` — CSS dinâmico gerado pelo sistema

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
