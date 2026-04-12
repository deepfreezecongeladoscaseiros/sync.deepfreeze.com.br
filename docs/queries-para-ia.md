# Queries para Integração com IA — Deep Freeze

**Banco:** `novo` (MySQL)
**Data:** 03/04/2026
**Base URL de imagens:** `https://www.deepfreeze.com.br/img/pratos/big/{imagem_src}`

---

## 1. Query de Clientes

Busca dados de clientes (tabela `pessoas`) com endereço principal.

```sql
SELECT
    -- === IDENTIFICAÇÃO ===
    p.id,                                     -- ID interno do cliente
    p.nome,                                   -- Nome completo
    p.apelido,                                -- Apelido/nome de tratamento
    p.email_primario,                         -- E-mail principal (usado no login)
    p.cpf,                                    -- CPF (pessoa física)
    p.cnpj,                                   -- CNPJ (pessoa jurídica, se aplicável)
    p.razao_social,                           -- Razão social (PJ)
    p.nome_fantasia,                          -- Nome fantasia (PJ)
    p.sexo,                                   -- Sexo: 'M' = masculino, 'F' = feminino
    p.nascimento,                             -- Data de nascimento (YYYY-MM-DD)

    -- === CONTATO ===
    p.telefone_celular,                       -- Celular (principal)
    p.telefone_residencial,                   -- Telefone fixo
    p.telefone_empresarial,                   -- Telefone comercial

    -- === PREFERÊNCIAS ===
    p.autoriza_newsletter,                    -- 1 = aceita receber newsletter
    p.aceita_ligacao,                         -- 1 = aceita receber ligações
    p.aceita_sms,                             -- 1 = aceita receber SMS
    p.aceita_whats_app,                       -- 1 = aceita receber WhatsApp

    -- === STATUS ===
    p.ativo,                                  -- 1 = cliente ativo, 0 = inativo
    p.curva_abc,                              -- Classificação ABC de valor: 'A', 'B' ou 'C'
    p.ranking,                                -- Ranking numérico do cliente (menor = melhor)
    p.data_cadastro,                          -- Data/hora do cadastro

    -- === ENDEREÇO PRINCIPAL ===
    e.cep,                                    -- CEP do endereço principal
    e.logradouro,                             -- Rua/avenida
    e.logradouro_complemento_numero AS numero, -- Número
    e.logradouro_complemento AS complemento,  -- Complemento (apto, bloco, etc.)
    e.bairro,                                 -- Bairro
    e.cidade,                                 -- Cidade
    e.uf                                      -- Estado (sigla UF)

FROM
    pessoas p
LEFT JOIN
    enderecos e ON e.pessoa_id = p.id
                AND e.end_principal = 1           -- Apenas endereço marcado como principal
                AND e.ativo = 1                   -- Apenas endereços ativos
WHERE
    p.ativo = 1                                   -- Somente clientes ativos
ORDER BY
    p.id DESC;
```

---

## 2. Query de Listagem de Produtos (com busca)

Mesma regra de exibição usada no site: produto ativo + com imagem + cadastrado no canal Internet.
Permite busca por nome e ingredientes.

```sql
SELECT
    -- === IDENTIFICAÇÃO ===
    p.id,                                     -- ID interno do produto
    p.codigo,                                 -- SKU/código do produto (ex: 'SP03', 'KR57')
    p.descricao AS nome,                      -- Nome do produto exibido no site

    -- === PREÇO ===
    p.preco,                                  -- Preço normal (varchar, pode conter vírgula)
    p.preco_promocional,                      -- Preço promocional (NULL = sem promoção)

    -- === PESO ===
    p.peso_liquido,                           -- Peso líquido em gramas (ex: 450)
    p.unidade_medida_peso,                    -- Unidade: 'g' (gramas) ou 'ml' (mililitros)

    -- === CATEGORIA E MARCA ===
    c.nome AS categoria,                      -- Nome da categoria (ex: 'Sopas e Caldinhos')
    m.nome_marca AS marca,                    -- Nome da marca (ex: 'Deep Freeze')

    -- === IMAGEM PRINCIPAL ===
    -- URL da imagem: https://www.deepfreeze.com.br/img/pratos/big/{imagem_src}
    img.imagem_src,                           -- Nome do arquivo de imagem (ex: 'sopa-ervilha.jpg')

    -- === ALÉRGENOS (informação rápida para filtros) ===
    p.in_contem_gluten,                       -- 1 = contém glúten
    p.in_contem_lactose,                      -- 1 = contém lactose
    p.in_sem_lactose,                         -- 1 = sem lactose
    p.in_baixo_lactose,                       -- 1 = baixo teor de lactose

    -- === ESTOQUE (soma de todas as lojas) ===
    COALESCE((
        SELECT SUM(est.estoque_atual_calculado - est.giro_balcao)
        FROM otm_estoques_lojas est
        WHERE est.produto_id = p.id
    ), 0) AS estoque_disponivel                -- Estoque total disponível para venda online

FROM
    produtos p

    -- Categoria do produto
    LEFT JOIN categorias c ON c.id = p.categoria_id

    -- Marca do produto
    LEFT JOIN marcas m ON m.id = p.marca_id

    -- Imagem principal (primeira imagem ativa)
    LEFT JOIN produtos_imagens img ON img.produto_id = p.id
                                   AND img.ativa = 1

    -- Filtro: produto cadastrado no canal Internet (canais_venda_id = 1) com datas válidas
    INNER JOIN canais_vendas_produtos cvp ON cvp.produto_id = p.id
                                          AND cvp.canais_venda_id = 1
                                          AND cvp.data_inicial <= NOW()
                                          AND cvp.data_final >= NOW()

WHERE
    p.ativo = 1                               -- Produto ativo

    -- === BUSCA POR NOME OU INGREDIENTES (opcional, remover se não precisar) ===
    -- AND (
    --     p.descricao LIKE '%termo_busca%'
    --     OR p.ingredientes LIKE '%termo_busca%'
    -- )

GROUP BY
    p.id                                      -- Agrupa para evitar duplicatas de imagem

ORDER BY
    p.descricao ASC;
```

---

## 3. Query de Detalhes do Produto (página completa)

Todos os campos exibidos na página de detalhes do produto no site.

```sql
SELECT
    -- === IDENTIFICAÇÃO ===
    p.id,                                     -- ID interno do produto
    p.codigo,                                 -- SKU/código (ex: 'SP03')
    p.descricao AS nome,                      -- Nome do produto
    p.descricao_etiqueta,                     -- Nome curto para etiqueta/embalagem
    p.codigo_de_barras,                       -- EAN/código de barras

    -- === DESCRIÇÃO E CONTEÚDO ===
    p.apresentacao,                           -- Texto de apresentação (descrição longa, HTML possível)
    p.propriedades,                           -- Propriedades nutricionais (texto)
    p.beneficios,                             -- Benefícios do produto (texto)
    p.dica_do_chef,                           -- Dicas do chef para preparo (texto)
    p.historia_do_prato,                      -- História/origem do prato (texto)

    -- === INGREDIENTES E ALÉRGENOS ===
    p.ingredientes,                           -- Lista de ingredientes (texto completo)
    p.alergenicos_manual,                     -- Texto manual de alérgenos (ex: "CONTÉM GLÚTEN")
    p.in_contem_gluten,                       -- 1 = contém glúten, 0 = não contém
    p.in_contem_lactose,                      -- 1 = contém lactose
    p.in_sem_lactose,                         -- 1 = sem lactose (selo)
    p.in_baixo_lactose,                       -- 1 = baixo teor de lactose (selo)
    p.bebida_alcoolica,                       -- 1 = é bebida alcoólica

    -- === PREPARO ===
    p.instrucoes_para_consumo,                -- Modo de preparo (texto, pode ser longo)

    -- === PREÇO ===
    p.preco,                                  -- Preço normal (varchar, formato: '35.80' ou '35,80')
    p.preco_promocional,                      -- Preço promocional (NULL = sem promoção)

    -- === PESO E PORÇÃO ===
    p.peso_liquido,                           -- Peso líquido em gramas (ex: 450)
    p.peso_bruto,                             -- Peso bruto em gramas
    p.unidade_medida_peso,                    -- Unidade: 'g' ou 'ml'
    p.porcao,                                 -- Tamanho da porção declarada (ex: 100)
    p.validade_dias,                          -- Validade em dias após fabricação

    -- === CLASSIFICAÇÃO ===
    p.categoria_id,                           -- FK para categorias
    p.marca_id,                               -- FK para marcas
    p.fabricante_id,                          -- FK para fabricantes

    -- === CATEGORIA ===
    c.nome AS categoria_nome,                 -- Nome da categoria
    c.slug AS categoria_slug,                 -- Slug da categoria (URL amigável)

    -- === MARCA ===
    m.nome_marca AS marca_nome,               -- Nome da marca

    -- === FLAGS ===
    p.pacote,                                 -- > 0 = produto é um kit/pacote
    p.gift_card,                              -- 1 = é um gift card/vale-presente
    p.produzido_por_encomenda,                -- 1 = produzido somente por encomenda

    -- === ESTOQUE ===
    COALESCE((
        SELECT SUM(est.estoque_atual_calculado - est.giro_balcao)
        FROM otm_estoques_lojas est
        WHERE est.produto_id = p.id
    ), 0) AS estoque_disponivel               -- Estoque total disponível

FROM
    produtos p
    LEFT JOIN categorias c ON c.id = p.categoria_id
    LEFT JOIN marcas m ON m.id = p.marca_id

WHERE
    p.id = :produto_id;                       -- Substituir pelo ID do produto desejado
```

### 3.1 Imagens do Produto (query separada)

```sql
-- Retorna todas as imagens ativas do produto
-- URL completa: https://www.deepfreeze.com.br/img/pratos/big/{imagem_src}
SELECT
    id,
    imagem_src,                               -- Nome do arquivo (ex: 'sopa-ervilha-1.jpg')
    posicao,                                  -- Ordem de exibição (menor = primeira)
    esta_na_embalagem                         -- 1 = foto da embalagem, 0 = foto do prato
FROM
    produtos_imagens
WHERE
    produto_id = :produto_id
    AND ativa = 1                             -- Somente imagens ativas
ORDER BY
    posicao ASC;
```

### 3.2 Informações Nutricionais do Produto (query separada)

```sql
-- Busca valores nutricionais mais recentes (por 100g)
-- A tabela é append-only: o valor atual é o MAX(id) por nutriente
-- A origem é definida por produtos.informacao_nutricional_siv:
--   1 = usa origem 'S' (calculado pelo SIV)
--   0 = usa origem 'J' (digitado manualmente)
SELECT
    i.dietpro_nutriente,                      -- ID do nutriente (ver tabela abaixo)
    i.valor                                   -- Valor por 100g/ml
FROM
    informacoes_nutricionais i
WHERE
    i.id IN (
        SELECT MAX(id)
        FROM informacoes_nutricionais
        WHERE produto_id = :produto_id
          AND origem = :origem                -- 'S' ou 'J' conforme produtos.informacao_nutricional_siv
        GROUP BY dietpro_nutriente
    );

-- IDs dos nutrientes:
-- 1   = Valor energético (kcal)    | VD: 2000
-- 2   = Proteínas (g)              | VD: 50
-- 3   = Gorduras totais (g)        | VD: 65
-- 4   = Carboidratos (g)           | VD: 300
-- 58  = Sódio (mg)                 | VD: 2000
-- 69  = Fibras alimentares (g)     | VD: 25
-- 185 = Gorduras saturadas (g)     | VD: 20
-- 186 = Gorduras trans (g)         | VD: 2
-- 187 = Açúcares totais (g)        | sem VD
-- 188 = Açúcares adicionados (g)   | VD: 50

-- Cálculo de valor por porção: (valor * produtos.porcao) / 100
-- Cálculo de %VD:               (valor_porcao * 100) / VD
-- ATENÇÃO: No site, a porção exibida é produtos.peso_liquido (não produtos.porcao)
```

### 3.3 Avaliações do Produto (query separada)

```sql
-- Avaliações/depoimentos aprovados de clientes
SELECT
    d.avaliacao,                              -- Nota de 1 a 5 (estrelas)
    d.depoimento,                             -- Texto do depoimento (máx 200 chars)
    d.created AS data_avaliacao,              -- Data da avaliação
    pe.nome AS nome_cliente                   -- Nome do cliente que avaliou
FROM
    depoimentos d
    INNER JOIN pessoas pe ON pe.id = d.pessoa_id
WHERE
    d.produto_id = :produto_id
    AND d.situacao_depoimento = 1             -- 1 = aprovado (0 = pendente)
ORDER BY
    d.id DESC
LIMIT 10;                                     -- Últimas 10 avaliações
```

---

## Regras de Negócio Importantes

### Visibilidade de Produto no Site
Um produto aparece no site quando **todas** as condições são verdadeiras:
1. `produtos.ativo = 1`
2. Possui pelo menos uma imagem com `produtos_imagens.ativa = 1`
3. Está cadastrado no canal Internet: `canais_vendas_produtos.canais_venda_id = 1` com `data_inicial <= NOW()` e `data_final >= NOW()`

### Preço
- O campo `preco` é **varchar** (não decimal). Pode conter '35.80' ou '35,80'.
- Se `preco_promocional` não é NULL e é > 0, o produto está em promoção.

### Estoque
- Estoque é calculado por loja: `SUM(estoque_atual_calculado - giro_balcao)` da tabela `otm_estoques_lojas`.
- `giro_balcao` é a reserva mínima para a loja física — não pode ser vendido online.

### Informação Nutricional
- Valores armazenados por **100g/ml**.
- A "porção" exibida no site usa `produtos.peso_liquido` (peso total da embalagem), **não** `produtos.porcao`.
- Se o produto tem `informacao_nutricional_siv = 1`, usar origem `'S'`. Senão, usar origem `'J'`.
- A tabela nutricional só aparece se o produto tiver exatamente **10 nutrientes** cadastrados.

### Senha de Clientes
- Armazenada em **MD5** (campo `senha`, varchar(32)).
- Clientes que entraram via login social (Google/Facebook) podem ter `senha = NULL`.
