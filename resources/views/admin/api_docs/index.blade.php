@extends('adminlte::page')

@section('title', 'Documentação da API')

@section('content_header')
    <h1>
        <i class="fas fa-book"></i> Sync Deep - Public API Documentation
        <small class="text-muted">v1.0</small>
    </h1>
@stop

@section('content')
<style>
    .endpoint-card {
        margin-bottom: 20px;
        border-left: 4px solid #007bff;
    }
    .method-badge {
        font-weight: bold;
        font-size: 12px;
        padding: 4px 8px;
        border-radius: 3px;
        font-family: monospace;
    }
    .method-get { background: #61affe; color: white; }
    .method-post { background: #49cc90; color: white; }
    .endpoint-url {
        background: #f5f5f5;
        padding: 10px;
        border-radius: 4px;
        font-family: monospace;
        font-size: 14px;
        margin: 10px 0;
    }
    .param-table th {
        background: #f8f9fa;
        font-weight: 600;
    }
    .example-request {
        background: #2d2d2d;
        color: #f8f8f2;
        padding: 15px;
        border-radius: 4px;
        font-family: 'Courier New', monospace;
        font-size: 13px;
        overflow-x: auto;
    }
    .toc {
        position: sticky;
        top: 20px;
    }
    .section-header {
        border-bottom: 2px solid #007bff;
        padding-bottom: 10px;
        margin-top: 30px;
        margin-bottom: 20px;
    }
    .badge-required { background: #dc3545; }
    .badge-optional { background: #6c757d; }
</style>

<div class="row">
    <!-- Table of Contents -->
    <div class="col-md-3">
        <div class="card toc">
            <div class="card-header bg-primary">
                <h5 class="mb-0"><i class="fas fa-list"></i> Índice</h5>
            </div>
            <div class="card-body p-0">
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link" href="#overview">Visão Geral</a></li>
                    <li class="nav-item"><a class="nav-link" href="#base-url">Base URL</a></li>
                    <li class="nav-item"><a class="nav-link" href="#rate-limit">Rate Limiting</a></li>
                    <li class="nav-item"><a class="nav-link" href="#products">Produtos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#categories">Categorias</a></li>
                    <li class="nav-item"><a class="nav-link" href="#brands">Marcas</a></li>
                    <li class="nav-item"><a class="nav-link" href="#filters">Filtros Avançados</a></li>
                    <li class="nav-item"><a class="nav-link" href="#use-cases">Casos de Uso IA</a></li>
                    <li class="nav-item"><a class="nav-link" href="#errors">Códigos de Erro</a></li>
                    <li class="nav-item"><a class="nav-link" href="#examples">Exemplos Completos</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Documentation Content -->
    <div class="col-md-9">
        
        <!-- Overview -->
        <div id="overview" class="card">
            <div class="card-header bg-info">
                <h4 class="mb-0"><i class="fas fa-info-circle"></i> Visão Geral</h4>
            </div>
            <div class="card-body">
                <p class="lead">A API Pública do Sync Deep foi projetada para <strong>Inteligências Artificiais</strong> e aplicações que precisam consultar produtos alimentícios com filtros complexos.</p>
                
                <div class="alert alert-primary">
                    <h5><i class="fas fa-star"></i> Características Principais:</h5>
                    <ul class="mb-0">
                        <li><strong>Filtros Combinados</strong>: Múltiplos filtros simultâneos</li>
                        <li><strong>Busca Inteligente</strong>: Nome, ingredientes, categoria, marca</li>
                        <li><strong>Filtros Nutricionais</strong>: Glúten-free, lactose-free, alérgenos</li>
                        <li><strong>Filtros de Ingredientes</strong>: Busca por inclusão ou exclusão</li>
                        <li><strong>Respostas Estruturadas</strong>: JSON organizado e completo</li>
                        <li><strong>Sem Autenticação</strong>: API pública, sem necessidade de token</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Base URL -->
        <div id="base-url" class="card mt-3">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-link"></i> Base URL</h4>
            </div>
            <div class="card-body">
                <div class="endpoint-url">
                    <strong>Produção:</strong> <code>{{ $baseUrl }}/api/v1</code>
                </div>
                <div class="endpoint-url">
                    <strong>Desenvolvimento:</strong> <code>http://localhost:8000/api/v1</code>
                </div>
                
                <div class="alert alert-warning mt-3">
                    <strong><i class="fas fa-exclamation-triangle"></i> Importante:</strong> Todas as requisições devem incluir o header <code>Accept: application/json</code>
                </div>
            </div>
        </div>

        <!-- Rate Limiting -->
        <div id="rate-limit" class="card mt-3">
            <div class="card-header bg-warning">
                <h4 class="mb-0"><i class="fas fa-tachometer-alt"></i> Rate Limiting</h4>
            </div>
            <div class="card-body">
                <p><strong>Limite:</strong> <span class="badge badge-danger">120 requisições por minuto</span> por IP</p>
                
                <p>Toda resposta inclui headers informativos:</p>
                <div class="example-request">
X-RateLimit-Limit: 120
X-RateLimit-Remaining: 115
X-RateLimit-Reset: 1642521600
                </div>

                <div class="alert alert-danger mt-3">
                    <strong>Erro 429 (Too Many Requests):</strong>
                    <pre class="mb-0" style="background: transparent; color: inherit;">{
  "message": "Too Many Attempts.",
  "retry_after": 60
}</pre>
                </div>
            </div>
        </div>

        <!-- Products Endpoints -->
        <div id="products" class="section-header">
            <h2><i class="fas fa-box"></i> Endpoints de Produtos</h2>
        </div>

        <!-- List Products -->
        <div class="endpoint-card card">
            <div class="card-header">
                <span class="method-badge method-get">GET</span>
                <strong>/products</strong>
                <span class="badge badge-secondary float-right">Listar Produtos</span>
            </div>
            <div class="card-body">
                <p>Lista todos os produtos ativos com paginação.</p>
                
                <h6>Parâmetros de Query:</h6>
                <table class="table table-sm param-table">
                    <thead>
                        <tr>
                            <th>Parâmetro</th>
                            <th>Tipo</th>
                            <th>Obrigatório</th>
                            <th>Descrição</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>per_page</code></td>
                            <td>integer</td>
                            <td><span class="badge badge-optional">Opcional</span></td>
                            <td>Itens por página (1-100, default: 15)</td>
                        </tr>
                        <tr>
                            <td><code>page</code></td>
                            <td>integer</td>
                            <td><span class="badge badge-optional">Opcional</span></td>
                            <td>Número da página (default: 1)</td>
                        </tr>
                        <tr>
                            <td><code>sort_by</code></td>
                            <td>string</td>
                            <td><span class="badge badge-optional">Opcional</span></td>
                            <td>Campo para ordenar (name, price, created_at...)</td>
                        </tr>
                        <tr>
                            <td><code>sort_order</code></td>
                            <td>string</td>
                            <td><span class="badge badge-optional">Opcional</span></td>
                            <td>Ordem (asc, desc - default: desc)</td>
                        </tr>
                    </tbody>
                </table>

                <h6>Exemplo de Request:</h6>
                <div class="example-request">
GET {{ $baseUrl }}/api/v1/products?per_page=20&page=1
                </div>

                <button class="btn btn-sm btn-primary mt-2" onclick="testEndpoint('{{ $baseUrl }}/api/v1/products?per_page=5')">
                    <i class="fas fa-play"></i> Testar Endpoint
                </button>
            </div>
        </div>

        <!-- Search Products -->
        <div class="endpoint-card card">
            <div class="card-header">
                <span class="method-badge method-get">GET</span>
                <strong>/products/search</strong>
                <span class="badge badge-info float-right">Busca Global</span>
            </div>
            <div class="card-body">
                <p>Busca produtos por termo. Busca em: nome, SKU, ingredientes, categoria, marca.</p>
                
                <h6>Parâmetros de Query:</h6>
                <table class="table table-sm param-table">
                    <thead>
                        <tr>
                            <th>Parâmetro</th>
                            <th>Tipo</th>
                            <th>Obrigatório</th>
                            <th>Descrição</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>q</code></td>
                            <td>string</td>
                            <td><span class="badge badge-required">Obrigatório</span></td>
                            <td>Termo de busca</td>
                        </tr>
                        <tr>
                            <td colspan="4"><em>+ Todos os filtros avançados (ver seção <a href="#filters">Filtros Avançados</a>)</em></td>
                        </tr>
                    </tbody>
                </table>

                <h6>Exemplo de Request:</h6>
                <div class="example-request">
GET {{ $baseUrl }}/api/v1/products/search?q=lasanha
                </div>

                <button class="btn btn-sm btn-primary mt-2" onclick="testEndpoint('{{ $baseUrl }}/api/v1/products/search?q=lasanha')">
                    <i class="fas fa-play"></i> Testar Endpoint
                </button>
            </div>
        </div>

        <!-- Featured Products -->
        <div class="endpoint-card card">
            <div class="card-header">
                <span class="method-badge method-get">GET</span>
                <strong>/products/featured</strong>
                <span class="badge badge-warning float-right">Produtos em Destaque</span>
            </div>
            <div class="card-body">
                <p>Retorna produtos em destaque, ordenados por prioridade.</p>
                
                <h6>Exemplo de Request:</h6>
                <div class="example-request">
GET {{ $baseUrl }}/api/v1/products/featured
                </div>

                <button class="btn btn-sm btn-primary mt-2" onclick="testEndpoint('{{ $baseUrl }}/api/v1/products/featured')">
                    <i class="fas fa-play"></i> Testar Endpoint
                </button>
            </div>
        </div>

        <!-- On Sale Products -->
        <div class="endpoint-card card">
            <div class="card-header">
                <span class="method-badge method-get">GET</span>
                <strong>/products/on-sale</strong>
                <span class="badge badge-success float-right">Produtos em Promoção</span>
            </div>
            <div class="card-body">
                <p>Retorna produtos com preço promocional definido.</p>
                
                <h6>Exemplo de Request:</h6>
                <div class="example-request">
GET {{ $baseUrl }}/api/v1/products/on-sale
                </div>

                <button class="btn btn-sm btn-primary mt-2" onclick="testEndpoint('{{ $baseUrl }}/api/v1/products/on-sale')">
                    <i class="fas fa-play"></i> Testar Endpoint
                </button>
            </div>
        </div>

        <!-- Product Details -->
        <div class="endpoint-card card">
            <div class="card-header">
                <span class="method-badge method-get">GET</span>
                <strong>/products/{id}</strong>
                <span class="badge badge-dark float-right">Detalhes do Produto</span>
            </div>
            <div class="card-body">
                <p>Retorna informações completas de um produto específico.</p>
                
                <h6>Exemplo de Request:</h6>
                <div class="example-request">
GET {{ $baseUrl }}/api/v1/products/1
                </div>

                <button class="btn btn-sm btn-primary mt-2" onclick="testEndpoint('{{ $baseUrl }}/api/v1/products/1')">
                    <i class="fas fa-play"></i> Testar Endpoint
                </button>
            </div>
        </div>

        <!-- Categories -->
        <div id="categories" class="section-header">
            <h2><i class="fas fa-tags"></i> Endpoints de Categorias</h2>
        </div>

        <div class="endpoint-card card">
            <div class="card-header">
                <span class="method-badge method-get">GET</span>
                <strong>/categories</strong>
            </div>
            <div class="card-body">
                <p>Lista todas as categorias com contagem de produtos.</p>
                <div class="example-request">
GET {{ $baseUrl }}/api/v1/categories
                </div>
                <button class="btn btn-sm btn-primary mt-2" onclick="testEndpoint('{{ $baseUrl }}/api/v1/categories')">
                    <i class="fas fa-play"></i> Testar Endpoint
                </button>
            </div>
        </div>

        <div class="endpoint-card card">
            <div class="card-header">
                <span class="method-badge method-get">GET</span>
                <strong>/categories/{id}/products</strong>
            </div>
            <div class="card-body">
                <p>Lista todos os produtos de uma categoria específica.</p>
                <div class="example-request">
GET {{ $baseUrl }}/api/v1/categories/1/products
                </div>
                <button class="btn btn-sm btn-primary mt-2" onclick="testEndpoint('{{ $baseUrl }}/api/v1/categories/1/products')">
                    <i class="fas fa-play"></i> Testar Endpoint
                </button>
            </div>
        </div>

        <!-- Brands -->
        <div id="brands" class="section-header">
            <h2><i class="fas fa-copyright"></i> Endpoints de Marcas</h2>
        </div>

        <div class="endpoint-card card">
            <div class="card-header">
                <span class="method-badge method-get">GET</span>
                <strong>/brands</strong>
            </div>
            <div class="card-body">
                <p>Lista todas as marcas com contagem de produtos.</p>
                <div class="example-request">
GET {{ $baseUrl }}/api/v1/brands
                </div>
                <button class="btn btn-sm btn-primary mt-2" onclick="testEndpoint('{{ $baseUrl }}/api/v1/brands')">
                    <i class="fas fa-play"></i> Testar Endpoint
                </button>
            </div>
        </div>

        <div class="endpoint-card card">
            <div class="card-header">
                <span class="method-badge method-get">GET</span>
                <strong>/brands/{id}/products</strong>
            </div>
            <div class="card-body">
                <p>Lista todos os produtos de uma marca específica.</p>
                <div class="example-request">
GET {{ $baseUrl }}/api/v1/brands/1/products
                </div>
                <button class="btn btn-sm btn-primary mt-2" onclick="testEndpoint('{{ $baseUrl }}/api/v1/brands/1/products')">
                    <i class="fas fa-play"></i> Testar Endpoint
                </button>
            </div>
        </div>

        <!-- Advanced Filters -->
        <div id="filters" class="section-header">
            <h2><i class="fas fa-filter"></i> Filtros Avançados</h2>
        </div>

        <div class="card">
            <div class="card-header bg-success">
                <h5 class="mb-0">Todos os filtros podem ser combinados!</h5>
            </div>
            <div class="card-body">
                
                <h5><i class="fas fa-heartbeat"></i> Filtros Nutricionais / Alérgenos</h5>
                <table class="table table-sm param-table">
                    <thead>
                        <tr>
                            <th style="width: 200px">Filtro</th>
                            <th style="width: 100px">Tipo</th>
                            <th>Descrição</th>
                            <th style="width: 150px">Exemplo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>gluten_free</code></td>
                            <td>boolean</td>
                            <td>Produtos SEM glúten</td>
                            <td><code>?gluten_free=true</code></td>
                        </tr>
                        <tr>
                            <td><code>lactose_free</code></td>
                            <td>boolean</td>
                            <td>Produtos SEM lactose</td>
                            <td><code>?lactose_free=true</code></td>
                        </tr>
                        <tr>
                            <td><code>low_lactose</code></td>
                            <td>boolean</td>
                            <td>Produtos com BAIXA lactose</td>
                            <td><code>?low_lactose=true</code></td>
                        </tr>
                        <tr>
                            <td><code>alcoholic</code></td>
                            <td>boolean</td>
                            <td>Bebidas alcoólicas (true) ou não (false)</td>
                            <td><code>?alcoholic=false</code></td>
                        </tr>
                    </tbody>
                </table>

                <h5 class="mt-4"><i class="fas fa-lemon"></i> Filtros de Ingredientes</h5>
                <table class="table table-sm param-table">
                    <thead>
                        <tr>
                            <th style="width: 200px">Filtro</th>
                            <th style="width: 100px">Tipo</th>
                            <th>Descrição</th>
                            <th style="width: 200px">Exemplo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>has_ingredient</code></td>
                            <td>string</td>
                            <td>Produtos que CONTÊM o ingrediente</td>
                            <td><code>?has_ingredient=queijo</code></td>
                        </tr>
                        <tr>
                            <td><code>without_ingredient</code></td>
                            <td>string</td>
                            <td>Produtos que NÃO CONTÊM o ingrediente</td>
                            <td><code>?without_ingredient=canela</code></td>
                        </tr>
                        <tr>
                            <td><code>allergen</code></td>
                            <td>string</td>
                            <td>Produtos que CONTÊM o alérgeno</td>
                            <td><code>?allergen=soja</code></td>
                        </tr>
                        <tr>
                            <td><code>without_allergen</code></td>
                            <td>string</td>
                            <td>Produtos que NÃO CONTÊM o alérgeno</td>
                            <td><code>?without_allergen=soja</code></td>
                        </tr>
                    </tbody>
                </table>

                <h5 class="mt-4"><i class="fas fa-dollar-sign"></i> Filtros de Preço e Estoque</h5>
                <table class="table table-sm param-table">
                    <thead>
                        <tr>
                            <th style="width: 200px">Filtro</th>
                            <th style="width: 100px">Tipo</th>
                            <th>Descrição</th>
                            <th style="width: 150px">Exemplo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>min_price</code></td>
                            <td>decimal</td>
                            <td>Preço mínimo</td>
                            <td><code>?min_price=10.00</code></td>
                        </tr>
                        <tr>
                            <td><code>max_price</code></td>
                            <td>decimal</td>
                            <td>Preço máximo</td>
                            <td><code>?max_price=50.00</code></td>
                        </tr>
                        <tr>
                            <td><code>in_stock</code></td>
                            <td>boolean</td>
                            <td>Apenas produtos em estoque</td>
                            <td><code>?in_stock=true</code></td>
                        </tr>
                    </tbody>
                </table>

                <h5 class="mt-4"><i class="fas fa-sort"></i> Ordenação</h5>
                <table class="table table-sm param-table">
                    <thead>
                        <tr>
                            <th style="width: 200px">Filtro</th>
                            <th style="width: 100px">Tipo</th>
                            <th>Valores Permitidos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>sort_by</code></td>
                            <td>string</td>
                            <td>name, price, promotional_price, created_at, stock, display_order</td>
                        </tr>
                        <tr>
                            <td><code>sort_order</code></td>
                            <td>string</td>
                            <td>asc, desc</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Use Cases for AI -->
        <div id="use-cases" class="section-header">
            <h2><i class="fas fa-robot"></i> Casos de Uso para IA</h2>
        </div>

        <div class="card">
            <div class="card-header bg-info">
                <h5 class="mb-0">Cenários Práticos de Integração com IA</h5>
            </div>
            <div class="card-body">
                
                <div class="mb-4">
                    <h6><i class="fas fa-comment-dots"></i> Cliente: "Quero produtos sem glúten"</h6>
                    <div class="example-request">
GET /api/v1/products?gluten_free=true
                    </div>
                    <button class="btn btn-sm btn-success mt-2" onclick="testEndpoint('{{ $baseUrl }}/api/v1/products?gluten_free=true')">
                        <i class="fas fa-play"></i> Testar
                    </button>
                </div>

                <div class="mb-4">
                    <h6><i class="fas fa-comment-dots"></i> Cliente: "Quero lasanhas sem glúten"</h6>
                    <div class="example-request">
GET /api/v1/products/search?q=lasanha&gluten_free=true
                    </div>
                    <button class="btn btn-sm btn-success mt-2" onclick="testEndpoint('{{ $baseUrl }}/api/v1/products/search?q=lasanha&gluten_free=true')">
                        <i class="fas fa-play"></i> Testar
                    </button>
                </div>

                <div class="mb-4">
                    <h6><i class="fas fa-comment-dots"></i> Cliente: "Produtos que não levam canela"</h6>
                    <div class="example-request">
GET /api/v1/products?without_ingredient=canela
                    </div>
                    <button class="btn btn-sm btn-success mt-2" onclick="testEndpoint('{{ $baseUrl }}/api/v1/products?without_ingredient=canela')">
                        <i class="fas fa-play"></i> Testar
                    </button>
                </div>

                <div class="mb-4">
                    <h6><i class="fas fa-comment-dots"></i> Cliente: "Produtos sem glúten e sem lactose"</h6>
                    <div class="example-request">
GET /api/v1/products?gluten_free=true&lactose_free=true
                    </div>
                    <button class="btn btn-sm btn-success mt-2" onclick="testEndpoint('{{ $baseUrl }}/api/v1/products?gluten_free=true&lactose_free=true')">
                        <i class="fas fa-play"></i> Testar
                    </button>
                </div>

                <div class="mb-4">
                    <h6><i class="fas fa-comment-dots"></i> Cliente: "Produtos com queijo"</h6>
                    <div class="example-request">
GET /api/v1/products?has_ingredient=queijo
                    </div>
                    <button class="btn btn-sm btn-success mt-2" onclick="testEndpoint('{{ $baseUrl }}/api/v1/products?has_ingredient=queijo')">
                        <i class="fas fa-play"></i> Testar
                    </button>
                </div>

                <div class="mb-4">
                    <h6><i class="fas fa-comment-dots"></i> Cliente: "Produtos em promoção sem glúten"</h6>
                    <div class="example-request">
GET /api/v1/products/on-sale?gluten_free=true
                    </div>
                    <button class="btn btn-sm btn-success mt-2" onclick="testEndpoint('{{ $baseUrl }}/api/v1/products/on-sale?gluten_free=true')">
                        <i class="fas fa-play"></i> Testar
                    </button>
                </div>

                <div class="mb-4">
                    <h6><i class="fas fa-comment-dots"></i> Cliente: "Produtos baratos (até R$ 30)"</h6>
                    <div class="example-request">
GET /api/v1/products?max_price=30&sort_by=price&sort_order=asc
                    </div>
                    <button class="btn btn-sm btn-success mt-2" onclick="testEndpoint('{{ $baseUrl }}/api/v1/products?max_price=30&sort_by=price&sort_order=asc')">
                        <i class="fas fa-play"></i> Testar
                    </button>
                </div>

                <div class="mb-4">
                    <h6><i class="fas fa-comment-dots"></i> Cliente: "Lasanhas com carne, sem glúten, em estoque"</h6>
                    <div class="example-request">
GET /api/v1/products/search?q=lasanha&has_ingredient=carne&gluten_free=true&in_stock=true
                    </div>
                    <button class="btn btn-sm btn-success mt-2" onclick="testEndpoint('{{ $baseUrl }}/api/v1/products/search?q=lasanha&has_ingredient=carne&gluten_free=true&in_stock=true')">
                        <i class="fas fa-play"></i> Testar
                    </button>
                </div>
            </div>
        </div>

        <!-- Error Codes -->
        <div id="errors" class="section-header">
            <h2><i class="fas fa-exclamation-triangle"></i> Códigos de Erro</h2>
        </div>

        <div class="card">
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th style="width: 80px">Código</th>
                            <th>Status</th>
                            <th>Descrição</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="table-success">
                            <td><strong>200</strong></td>
                            <td>OK</td>
                            <td>Requisição bem-sucedida</td>
                        </tr>
                        <tr class="table-warning">
                            <td><strong>400</strong></td>
                            <td>Bad Request</td>
                            <td>Parâmetros inválidos (ex: <code>q</code> vazio em /search)</td>
                        </tr>
                        <tr class="table-warning">
                            <td><strong>404</strong></td>
                            <td>Not Found</td>
                            <td>Recurso não encontrado (produto, categoria, marca)</td>
                        </tr>
                        <tr class="table-danger">
                            <td><strong>429</strong></td>
                            <td>Too Many Requests</td>
                            <td>Rate limit excedido (120 req/min)</td>
                        </tr>
                        <tr class="table-danger">
                            <td><strong>500</strong></td>
                            <td>Internal Server Error</td>
                            <td>Erro no servidor</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Complete Examples -->
        <div id="examples" class="section-header">
            <h2><i class="fas fa-code"></i> Exemplos Completos de Código</h2>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fab fa-js"></i> JavaScript (Fetch API)</h5>
            </div>
            <div class="card-body">
                <pre class="example-request">
// Buscar produtos sem glúten
fetch('{{ $baseUrl }}/api/v1/products?gluten_free=true', {
  method: 'GET',
  headers: {
    'Accept': 'application/json'
  }
})
.then(response => response.json())
.then(data => {
  console.log('Produtos:', data.data);
  console.log('Total:', data.meta.total);
})
.catch(error => console.error('Erro:', error));
                </pre>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="fab fa-python"></i> Python (Requests)</h5>
            </div>
            <div class="card-body">
                <pre class="example-request">
import requests

# Buscar lasanhas sem glúten
response = requests.get(
    '{{ $baseUrl }}/api/v1/products/search',
    params={
        'q': 'lasanha',
        'gluten_free': 'true'
    },
    headers={'Accept': 'application/json'}
)

data = response.json()
print(f"Produtos encontrados: {data['meta']['total']}")
for product in data['data']:
    print(f"- {product['name']} - R$ {product['pricing']['price']}")
                </pre>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="fab fa-php"></i> PHP (cURL)</h5>
            </div>
            <div class="card-body">
                <pre class="example-request">
&lt;?php
// Buscar produtos em promoção
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, '{{ $baseUrl }}/api/v1/products/on-sale');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);

$response = curl_exec($ch);
$data = json_decode($response, true);

echo "Produtos em promoção: " . $data['meta']['total'];
foreach ($data['data'] as $product) {
    echo $product['name'] . " - De R$ " . $product['pricing']['price'];
    echo " por R$ " . $product['pricing']['promotional_price'] . "\n";
}

curl_close($ch);
?&gt;
                </pre>
            </div>
        </div>

        <!-- Footer -->
        <div class="card mt-4 bg-light">
            <div class="card-body text-center">
                <h5><i class="fas fa-question-circle"></i> Precisa de Ajuda?</h5>
                <p>Consulte os <a href="{{ route('admin.api_logs.index') }}">logs da API</a> para debugar suas requisições.</p>
                <p class="mb-0">
                    <strong>Versão da API:</strong> v1.0 | 
                    <strong>Última Atualização:</strong> {{ now()->format('d/m/Y') }}
                </p>
            </div>
        </div>

    </div>
</div>

<!-- Modal for Test Results -->
<div class="modal fade" id="testModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Resultado do Teste</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="testLoading" class="text-center">
                    <i class="fas fa-spinner fa-spin fa-3x"></i>
                    <p>Carregando...</p>
                </div>
                <div id="testResult" style="display: none;">
                    <h6>Request URL:</h6>
                    <div class="endpoint-url" id="requestUrl"></div>
                    
                    <h6 class="mt-3">Status:</h6>
                    <div id="responseStatus"></div>
                    
                    <h6 class="mt-3">Response:</h6>
                    <pre class="example-request" id="responseBody" style="max-height: 400px; overflow-y: auto;"></pre>
                </div>
            </div>
        </div>
    </div>
</div>

@stop

@section('js')
<script>
function testEndpoint(url) {
    $('#testModal').modal('show');
    $('#testLoading').show();
    $('#testResult').hide();
    
    fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => {
        const status = response.status;
        return response.json().then(data => ({status, data}));
    })
    .then(result => {
        $('#testLoading').hide();
        $('#testResult').show();
        $('#requestUrl').text(url);
        
        let statusBadge = '';
        if (result.status >= 200 && result.status < 300) {
            statusBadge = `<span class="badge badge-success">${result.status} OK</span>`;
        } else if (result.status >= 400) {
            statusBadge = `<span class="badge badge-danger">${result.status} Error</span>`;
        }
        $('#responseStatus').html(statusBadge);
        
        $('#responseBody').text(JSON.stringify(result.data, null, 2));
    })
    .catch(error => {
        $('#testLoading').hide();
        $('#testResult').show();
        $('#requestUrl').text(url);
        $('#responseStatus').html('<span class="badge badge-danger">Error</span>');
        $('#responseBody').text('Erro: ' + error.message);
    });
}

// Smooth scroll for TOC
$(document).ready(function() {
    $('.nav-link').on('click', function(e) {
        e.preventDefault();
        const target = $(this).attr('href');
        $('html, body').animate({
            scrollTop: $(target).offset().top - 70
        }, 500);
    });
});
</script>
@stop
