@extends('layouts.docs')

@section('title', 'API Documentation')

@section('styles')
    .hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 80px 0;
        margin-bottom: 40px;
    }
    .endpoint-card {
        border-left: 4px solid #007bff;
        margin-bottom: 20px;
        transition: transform 0.2s;
    }
    .endpoint-card:hover {
        transform: translateX(5px);
    }
    .method-badge {
        font-weight: 700;
        padding: 5px 12px;
        border-radius: 4px;
        font-size: 0.85rem;
    }
    .method-get { background: #28a745; color: white; }
    .method-post { background: #ffc107; color: #333; }
    .code-block {
        background: #2d2d2d;
        color: #f8f8f2;
        padding: 20px;
        border-radius: 8px;
        overflow-x: auto;
        margin: 15px 0;
    }
    .code-block pre {
        color: #f8f8f2;
        margin: 0;
    }
    .param-table th {
        background: #f8f9fa;
        font-weight: 600;
    }
    .section-title {
        border-bottom: 3px solid #007bff;
        padding-bottom: 10px;
        margin-top: 50px;
        margin-bottom: 30px;
    }
    .nav-pills .nav-link {
        color: #495057;
        border-radius: 8px;
    }
    .nav-pills .nav-link.active {
        background: #007bff;
    }
    .response-example {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin: 15px 0;
    }
    .badge-required {
        background: #dc3545;
        color: white;
        font-size: 0.7rem;
        padding: 2px 6px;
        border-radius: 3px;
        margin-left: 5px;
    }
    .badge-optional {
        background: #6c757d;
        color: white;
        font-size: 0.7rem;
        padding: 2px 6px;
        border-radius: 3px;
        margin-left: 5px;
    }
    .ai-scenario {
        background: #e7f3ff;
        border-left: 4px solid #2196F3;
        padding: 15px;
        margin: 15px 0;
        border-radius: 4px;
    }
@endsection

@section('content')
<!-- Hero Section -->
<div class="hero">
    <div class="container text-center">
        <h1 class="display-4"><i class="fas fa-code"></i> Sync Deep Freeze API</h1>
        <p class="lead">API Pública para integração com sistemas externos, IAs e aplicações</p>
        <div class="mt-4">
            <span class="badge badge-light mr-2">REST API</span>
            <span class="badge badge-light mr-2">JSON</span>
            <span class="badge badge-light mr-2">Rate Limited</span>
            <span class="badge badge-light">v1</span>
        </div>
    </div>
</div>

<div class="container mb-5">
    <!-- Quick Start -->
    <section id="quick-start">
        <h2 class="section-title"><i class="fas fa-rocket"></i> Quick Start</h2>
        
        <div class="alert alert-info">
            <strong><i class="fas fa-info-circle"></i> Base URL:</strong> 
            <code>{{ $baseUrl }}/api/v1</code>
        </div>

        <div class="alert alert-warning">
            <strong><i class="fas fa-shield-alt"></i> Rate Limit:</strong> 
            120 requisições por minuto por integração
        </div>

        <h4 class="mt-4">Exemplo básico:</h4>
        <div class="code-block">
<pre>GET {{ $baseUrl }}/api/v1/products
Authorization: Bearer SEU_TOKEN_AQUI

Response:
{
  "data": [
    {
      "id": 1,
      "name": "Lasanha à Bolonhesa",
      "category": "Massas",
      "price": 45.90,
      "allergens": {
        "contains_gluten": true,
        "lactose_free": false
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 150
  }
}</pre>
        </div>
    </section>

    <!-- Authentication -->
    <section id="authentication">
        <h2 class="section-title"><i class="fas fa-key"></i> Autenticação</h2>
        
        <div class="alert alert-danger">
            <strong><i class="fas fa-lock"></i> Autenticação Obrigatória:</strong> 
            Esta API requer autenticação via Bearer token. Apenas integradores cadastrados podem acessar.
        </div>

        <p>Todas as requisições devem incluir o token de autenticação no header <code>Authorization</code>:</p>

        <div class="code-block">
<pre>Authorization: Bearer SEU_TOKEN_AQUI</pre>
        </div>

        <h4 class="mt-4">Como obter seu token?</h4>
        <ol>
            <li>Acesse o painel administrativo em <code>/admin</code></li>
            <li>Vá em <strong>Integrations → Integrations</strong></li>
            <li>Crie uma nova integração ou visualize uma existente</li>
            <li>Copie o token gerado</li>
        </ol>

        <div class="alert alert-warning mt-3">
            <strong><i class="fas fa-exclamation-triangle"></i> Importante:</strong>
            <ul class="mb-0">
                <li>O token é único e pessoal para cada integração</li>
                <li>Mantenha seu token em segurança</li>
                <li>A integração deve estar com status <strong>active</strong></li>
                <li>Tokens inválidos ou inativos retornarão erro <code>401 Unauthorized</code></li>
            </ul>
        </div>

        <h4 class="mt-4">Erro de Autenticação</h4>
        <div class="code-block">
<pre>{
  "message": "Unauthorized. API token is required.",
  "error": "Missing Bearer token in Authorization header"
}</pre>
        </div>
    </section>

    <!-- Endpoints -->
    <section id="endpoints">
        <h2 class="section-title"><i class="fas fa-network-wired"></i> Endpoints</h2>

        <!-- Products Endpoints -->
        <h3 class="mt-4 mb-3"><i class="fas fa-box text-primary"></i> Produtos</h3>

        <!-- GET /products -->
        <div class="card endpoint-card">
            <div class="card-body">
                <h5>
                    <span class="method-badge method-get">GET</span>
                    <code>/products</code>
                </h5>
                <p class="text-muted">Lista todos os produtos com suporte a filtros avançados</p>

                <h6 class="mt-3">Query Parameters:</h6>
                <table class="table table-sm param-table">
                    <thead>
                        <tr>
                            <th>Parâmetro</th>
                            <th>Tipo</th>
                            <th>Descrição</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>page</code> <span class="badge-optional">opcional</span></td>
                            <td>integer</td>
                            <td>Número da página (padrão: 1)</td>
                        </tr>
                        <tr>
                            <td><code>per_page</code> <span class="badge-optional">opcional</span></td>
                            <td>integer</td>
                            <td>Items por página (padrão: 15, máx: 100)</td>
                        </tr>
                        <tr>
                            <td><code>category_id</code> <span class="badge-optional">opcional</span></td>
                            <td>integer</td>
                            <td>Filtrar por ID da categoria</td>
                        </tr>
                        <tr>
                            <td><code>brand_id</code> <span class="badge-optional">opcional</span></td>
                            <td>integer</td>
                            <td>Filtrar por ID da marca</td>
                        </tr>
                        <tr>
                            <td><code>gluten_free</code> <span class="badge-optional">opcional</span></td>
                            <td>boolean</td>
                            <td>Produtos sem glúten (true/false)</td>
                        </tr>
                        <tr>
                            <td><code>lactose_free</code> <span class="badge-optional">opcional</span></td>
                            <td>boolean</td>
                            <td>Produtos sem lactose (true/false)</td>
                        </tr>
                        <tr>
                            <td><code>sugar_free</code> <span class="badge-optional">opcional</span></td>
                            <td>boolean</td>
                            <td>Produtos sem açúcar (true/false)</td>
                        </tr>
                        <tr>
                            <td><code>has_ingredient</code> <span class="badge-optional">opcional</span></td>
                            <td>string</td>
                            <td>Produtos que CONTÉM o ingrediente</td>
                        </tr>
                        <tr>
                            <td><code>without_ingredient</code> <span class="badge-optional">opcional</span></td>
                            <td>string</td>
                            <td>Produtos que NÃO CONTÉM o ingrediente</td>
                        </tr>
                        <tr>
                            <td><code>allergen</code> <span class="badge-optional">opcional</span></td>
                            <td>string</td>
                            <td>Produtos que CONTÉM o alérgeno</td>
                        </tr>
                        <tr>
                            <td><code>without_allergen</code> <span class="badge-optional">opcional</span></td>
                            <td>string</td>
                            <td>Produtos que NÃO CONTÉM o alérgeno</td>
                        </tr>
                        <tr>
                            <td><code>min_price</code> <span class="badge-optional">opcional</span></td>
                            <td>decimal</td>
                            <td>Preço mínimo</td>
                        </tr>
                        <tr>
                            <td><code>max_price</code> <span class="badge-optional">opcional</span></td>
                            <td>decimal</td>
                            <td>Preço máximo</td>
                        </tr>
                        <tr>
                            <td><code>in_stock</code> <span class="badge-optional">opcional</span></td>
                            <td>boolean</td>
                            <td>Apenas produtos em estoque (true/false)</td>
                        </tr>
                        <tr>
                            <td><code>sort_by</code> <span class="badge-optional">opcional</span></td>
                            <td>string</td>
                            <td>Ordenar por: name, price, created_at (padrão: name)</td>
                        </tr>
                        <tr>
                            <td><code>sort_order</code> <span class="badge-optional">opcional</span></td>
                            <td>string</td>
                            <td>Ordem: asc, desc (padrão: asc)</td>
                        </tr>
                    </tbody>
                </table>

                <h6 class="mt-3">Exemplo de Requisição:</h6>
                <div class="code-block">
<pre>GET {{ $baseUrl }}/api/v1/products?gluten_free=true&lactose_free=true&sort_by=price&sort_order=asc</pre>
                </div>
            </div>
        </div>

        <!-- GET /products/search -->
        <div class="card endpoint-card">
            <div class="card-body">
                <h5>
                    <span class="method-badge method-get">GET</span>
                    <code>/products/search</code>
                </h5>
                <p class="text-muted">Busca produtos por termo (nome, descrição, ingredientes)</p>

                <h6 class="mt-3">Query Parameters:</h6>
                <table class="table table-sm param-table">
                    <thead>
                        <tr>
                            <th>Parâmetro</th>
                            <th>Tipo</th>
                            <th>Descrição</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>q</code> <span class="badge-required">obrigatório</span></td>
                            <td>string</td>
                            <td>Termo de busca</td>
                        </tr>
                        <tr>
                            <td colspan="3"><em>+ todos os filtros do endpoint /products</em></td>
                        </tr>
                    </tbody>
                </table>

                <h6 class="mt-3">Exemplo de Requisição:</h6>
                <div class="code-block">
<pre>GET {{ $baseUrl }}/api/v1/products/search?q=lasanha&without_ingredient=canela</pre>
                </div>
            </div>
        </div>

        <!-- GET /products/{id} -->
        <div class="card endpoint-card">
            <div class="card-body">
                <h5>
                    <span class="method-badge method-get">GET</span>
                    <code>/products/{id}</code>
                </h5>
                <p class="text-muted">Retorna detalhes de um produto específico</p>

                <h6 class="mt-3">Exemplo de Requisição:</h6>
                <div class="code-block">
<pre>GET {{ $baseUrl }}/api/v1/products/1</pre>
                </div>
            </div>
        </div>

        <!-- GET /products/featured -->
        <div class="card endpoint-card">
            <div class="card-body">
                <h5>
                    <span class="method-badge method-get">GET</span>
                    <code>/products/featured</code>
                </h5>
                <p class="text-muted">Retorna produtos em destaque</p>
            </div>
        </div>

        <!-- GET /products/on-sale -->
        <div class="card endpoint-card">
            <div class="card-body">
                <h5>
                    <span class="method-badge method-get">GET</span>
                    <code>/products/on-sale</code>
                </h5>
                <p class="text-muted">Retorna produtos em promoção</p>
            </div>
        </div>

        <!-- Categories Endpoints -->
        <h3 class="mt-5 mb-3"><i class="fas fa-tags text-success"></i> Categorias</h3>

        <div class="card endpoint-card">
            <div class="card-body">
                <h5>
                    <span class="method-badge method-get">GET</span>
                    <code>/categories</code>
                </h5>
                <p class="text-muted">Lista todas as categorias</p>
            </div>
        </div>

        <div class="card endpoint-card">
            <div class="card-body">
                <h5>
                    <span class="method-badge method-get">GET</span>
                    <code>/categories/{id}</code>
                </h5>
                <p class="text-muted">Retorna detalhes de uma categoria específica</p>
            </div>
        </div>

        <div class="card endpoint-card">
            <div class="card-body">
                <h5>
                    <span class="method-badge method-get">GET</span>
                    <code>/categories/{id}/products</code>
                </h5>
                <p class="text-muted">Retorna todos os produtos de uma categoria</p>
            </div>
        </div>

        <!-- Brands Endpoints -->
        <h3 class="mt-5 mb-3"><i class="fas fa-copyright text-warning"></i> Marcas</h3>

        <div class="card endpoint-card">
            <div class="card-body">
                <h5>
                    <span class="method-badge method-get">GET</span>
                    <code>/brands</code>
                </h5>
                <p class="text-muted">Lista todas as marcas</p>
            </div>
        </div>

        <div class="card endpoint-card">
            <div class="card-body">
                <h5>
                    <span class="method-badge method-get">GET</span>
                    <code>/brands/{id}</code>
                </h5>
                <p class="text-muted">Retorna detalhes de uma marca específica</p>
            </div>
        </div>

        <div class="card endpoint-card">
            <div class="card-body">
                <h5>
                    <span class="method-badge method-get">GET</span>
                    <code>/brands/{id}/products</code>
                </h5>
                <p class="text-muted">Retorna todos os produtos de uma marca</p>
            </div>
        </div>
    </section>

    <!-- Response Format -->
    <section id="response-format">
        <h2 class="section-title"><i class="fas fa-file-code"></i> Formato de Resposta</h2>

        <h4>Produto (Product Resource)</h4>
        <div class="code-block">
<pre>{
  "id": 1,
  "legacy_id": 123,
  "name": "Lasanha à Bolonhesa 350g",
  "description": "Deliciosa lasanha com molho bolonhesa...",
  "category": {
    "id": 5,
    "name": "Massas"
  },
  "brand": {
    "id": 2,
    "name": "Deep Freeze"
  },
  "manufacturer": {
    "id": 3,
    "name": "Fabricante XYZ"
  },
  "allergens": {
    "contains_gluten": true,
    "lactose_free": false,
    "sugar_free": false,
    "allergen_info": "Contém glúten e lactose"
  },
  "nutritional": {
    "ingredients": "Massa, carne bovina, molho...",
    "nutritional_info": "Porção 100g: 250kcal..."
  },
  "pricing": {
    "price": 45.90,
    "promotional_price": 39.90,
    "has_promotion": true,
    "discount_percentage": 13
  },
  "stock": {
    "available": true,
    "quantity": 150
  },
  "media": {
    "main_image": "https://...",
    "additional_images": [...]
  },
  "metadata": {
    "is_featured": false,
    "ean": "7891234567890",
    "ncm": "1902.19.00",
    "weight_kg": 0.35,
    "created_at": "2025-01-15T10:30:00Z",
    "updated_at": "2025-01-20T14:20:00Z"
  }
}</pre>
        </div>

        <h4 class="mt-4">Resposta com Paginação</h4>
        <div class="code-block">
<pre>{
  "data": [...],
  "links": {
    "first": "{{ $baseUrl }}/api/v1/products?page=1",
    "last": "{{ $baseUrl }}/api/v1/products?page=10",
    "prev": null,
    "next": "{{ $baseUrl }}/api/v1/products?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 10,
    "per_page": 15,
    "to": 15,
    "total": 150
  }
}</pre>
        </div>
    </section>

    <!-- AI Use Cases -->
    <section id="ai-scenarios">
        <h2 class="section-title"><i class="fas fa-robot"></i> Cenários de Uso para IA</h2>
        <p>Exemplos de como uma IA pode usar a API para atender solicitações de clientes:</p>

        <div class="ai-scenario">
            <h5><i class="fas fa-user"></i> Cliente: "Quero lasanhas sem glúten"</h5>
            <p><strong>Query:</strong></p>
            <div class="code-block">
<pre>GET {{ $baseUrl }}/api/v1/products/search?q=lasanha&gluten_free=true</pre>
            </div>
        </div>

        <div class="ai-scenario">
            <h5><i class="fas fa-user"></i> Cliente: "Produtos que não levam canela"</h5>
            <p><strong>Query:</strong></p>
            <div class="code-block">
<pre>GET {{ $baseUrl }}/api/v1/products?without_ingredient=canela</pre>
            </div>
        </div>

        <div class="ai-scenario">
            <h5><i class="fas fa-user"></i> Cliente: "Lasanha sem glúten e sem lactose com queijo"</h5>
            <p><strong>Query:</strong></p>
            <div class="code-block">
<pre>GET {{ $baseUrl }}/api/v1/products/search?q=lasanha&gluten_free=true&lactose_free=true&has_ingredient=queijo</pre>
            </div>
        </div>

        <div class="ai-scenario">
            <h5><i class="fas fa-user"></i> Cliente: "Produtos sem amendoim (alérgico)"</h5>
            <p><strong>Query:</strong></p>
            <div class="code-block">
<pre>GET {{ $baseUrl }}/api/v1/products?without_allergen=amendoim</pre>
            </div>
        </div>

        <div class="ai-scenario">
            <h5><i class="fas fa-user"></i> Cliente: "Produtos em promoção abaixo de R$ 30"</h5>
            <p><strong>Query:</strong></p>
            <div class="code-block">
<pre>GET {{ $baseUrl }}/api/v1/products/on-sale?max_price=30</pre>
            </div>
        </div>

        <div class="ai-scenario">
            <h5><i class="fas fa-user"></i> Cliente: "Massas da marca Deep Freeze disponíveis em estoque"</h5>
            <p><strong>Query:</strong></p>
            <div class="code-block">
<pre>GET {{ $baseUrl }}/api/v1/products?category_id=5&brand_id=2&in_stock=true</pre>
            </div>
        </div>

        <div class="ai-scenario">
            <h5><i class="fas fa-user"></i> Cliente: "Produtos sem açúcar ordenados por preço"</h5>
            <p><strong>Query:</strong></p>
            <div class="code-block">
<pre>GET {{ $baseUrl }}/api/v1/products?sugar_free=true&sort_by=price&sort_order=asc</pre>
            </div>
        </div>

        <div class="ai-scenario">
            <h5><i class="fas fa-user"></i> Cliente: "Buscar 'pizza' que contenha mussarela mas não tenha cebola"</h5>
            <p><strong>Query:</strong></p>
            <div class="code-block">
<pre>GET {{ $baseUrl }}/api/v1/products/search?q=pizza&has_ingredient=mussarela&without_ingredient=cebola</pre>
            </div>
        </div>
    </section>

    <!-- Code Examples -->
    <section id="code-examples">
        <h2 class="section-title"><i class="fas fa-code"></i> Exemplos de Código</h2>

        <ul class="nav nav-pills mb-3" id="codeTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="js-tab" data-toggle="pill" href="#javascript" role="tab">JavaScript</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="python-tab" data-toggle="pill" href="#python" role="tab">Python</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="php-tab" data-toggle="pill" href="#php" role="tab">PHP</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="curl-tab" data-toggle="pill" href="#curl" role="tab">cURL</a>
            </li>
        </ul>

        <div class="tab-content" id="codeTabContent">
            <div class="tab-pane fade show active" id="javascript" role="tabpanel">
                <div class="code-block">
<pre>// Usando fetch API
const baseUrl = '{{ $baseUrl }}/api/v1';
const apiToken = 'SEU_TOKEN_AQUI';

async function searchProducts(query, filters = {}) {
  const params = new URLSearchParams({
    q: query,
    ...filters
  });
  
  const response = await fetch(`${baseUrl}/products/search?${params}`, {
    headers: {
      'Authorization': `Bearer ${apiToken}`,
      'Accept': 'application/json'
    }
  });
  
  const data = await response.json();
  return data;
}

// Exemplo de uso
searchProducts('lasanha', {
  gluten_free: true,
  lactose_free: true
}).then(data => {
  console.log('Produtos encontrados:', data.data);
});</pre>
                </div>
            </div>

            <div class="tab-pane fade" id="python" role="tabpanel">
                <div class="code-block">
<pre># Usando requests
import requests

BASE_URL = '{{ $baseUrl }}/api/v1'
API_TOKEN = 'SEU_TOKEN_AQUI'

def search_products(query, **filters):
    params = {'q': query, **filters}
    headers = {
        'Authorization': f'Bearer {API_TOKEN}',
        'Accept': 'application/json'
    }
    response = requests.get(
        f'{BASE_URL}/products/search', 
        params=params,
        headers=headers
    )
    return response.json()

# Exemplo de uso
data = search_products('lasanha', gluten_free=True, lactose_free=True)
print(f"Produtos encontrados: {len(data['data'])}")</pre>
                </div>
            </div>

            <div class="tab-pane fade" id="php" role="tabpanel">
                <div class="code-block">
<pre>&lt;?php
// Usando cURL
function searchProducts($query, $filters = []) {
    $baseUrl = '{{ $baseUrl }}/api/v1';
    $apiToken = 'SEU_TOKEN_AQUI';
    $params = http_build_query(array_merge(['q' => $query], $filters));
    
    $ch = curl_init("{$baseUrl}/products/search?{$params}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Authorization: Bearer ' . $apiToken
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

// Exemplo de uso
$data = searchProducts('lasanha', [
    'gluten_free' => true,
    'lactose_free' => true
]);
echo "Produtos encontrados: " . count($data['data']);
?&gt;</pre>
                </div>
            </div>

            <div class="tab-pane fade" id="curl" role="tabpanel">
                <div class="code-block">
<pre># Busca simples
curl -H "Authorization: Bearer SEU_TOKEN_AQUI" \
     "{{ $baseUrl }}/api/v1/products/search?q=lasanha"

# Com filtros
curl -H "Authorization: Bearer SEU_TOKEN_AQUI" \
     "{{ $baseUrl }}/api/v1/products/search?q=lasanha&gluten_free=true&lactose_free=true"

# Produto específico
curl -H "Authorization: Bearer SEU_TOKEN_AQUI" \
     "{{ $baseUrl }}/api/v1/products/1"

# Lista de categorias
curl -H "Authorization: Bearer SEU_TOKEN_AQUI" \
     "{{ $baseUrl }}/api/v1/categories"</pre>
                </div>
            </div>
        </div>
    </section>

    <!-- Error Handling -->
    <section id="errors">
        <h2 class="section-title"><i class="fas fa-exclamation-triangle"></i> Tratamento de Erros</h2>

        <h4>Códigos de Status HTTP</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Significado</th>
                    <th>Descrição</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="badge badge-success">200</span></td>
                    <td>OK</td>
                    <td>Requisição bem-sucedida</td>
                </tr>
                <tr>
                    <td><span class="badge badge-warning">400</span></td>
                    <td>Bad Request</td>
                    <td>Parâmetros inválidos</td>
                </tr>
                <tr>
                    <td><span class="badge badge-warning">404</span></td>
                    <td>Not Found</td>
                    <td>Recurso não encontrado</td>
                </tr>
                <tr>
                    <td><span class="badge badge-danger">429</span></td>
                    <td>Too Many Requests</td>
                    <td>Rate limit excedido</td>
                </tr>
                <tr>
                    <td><span class="badge badge-danger">500</span></td>
                    <td>Internal Server Error</td>
                    <td>Erro no servidor</td>
                </tr>
            </tbody>
        </table>

        <h4 class="mt-4">Formato de Erro</h4>
        <div class="code-block">
<pre>{
  "message": "Product not found",
  "error": "Not Found"
}</pre>
        </div>

        <h4 class="mt-4">Rate Limit Headers</h4>
        <div class="code-block">
<pre>X-RateLimit-Limit: 120
X-RateLimit-Remaining: 115
Retry-After: 60</pre>
        </div>
    </section>

    <!-- Best Practices -->
    <section id="best-practices">
        <h2 class="section-title"><i class="fas fa-star"></i> Boas Práticas</h2>

        <div class="card mb-3">
            <div class="card-body">
                <h5><i class="fas fa-check-circle text-success"></i> Respeite o Rate Limit</h5>
                <p>Implemente throttling no seu lado para não exceder 120 requisições por minuto.</p>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h5><i class="fas fa-check-circle text-success"></i> Use Paginação</h5>
                <p>Sempre use o parâmetro <code>per_page</code> apropriado. Não requisite mais dados do que necessário.</p>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h5><i class="fas fa-check-circle text-success"></i> Combine Filtros</h5>
                <p>Use múltiplos filtros em uma única requisição ao invés de fazer múltiplas requisições.</p>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h5><i class="fas fa-check-circle text-success"></i> Cache Quando Possível</h5>
                <p>Categorias e marcas mudam raramente. Considere cachear essas informações localmente.</p>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h5><i class="fas fa-check-circle text-success"></i> Trate Erros Gracefully</h5>
                <p>Sempre implemente tratamento de erros robusto, especialmente para códigos 404 e 429.</p>
            </div>
        </div>
    </section>

    <!-- Support -->
    <section id="support" class="mb-5">
        <h2 class="section-title"><i class="fas fa-life-ring"></i> Suporte</h2>
        <div class="alert alert-info">
            <p class="mb-2"><strong>Precisa de ajuda?</strong></p>
            <p class="mb-0">
                <i class="fas fa-envelope"></i> Entre em contato: <strong>suporte@deepfreeze.com.br</strong><br>
                <i class="fas fa-clock"></i> Horário de atendimento: Segunda a Sexta, 9h às 18h
            </p>
        </div>
    </section>
</div>
@endsection
