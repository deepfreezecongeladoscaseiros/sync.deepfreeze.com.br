# TODO: Implementação de Dados Nutricionais via Webhook

**Data:** 2025-11-25
**Status:** Pendente
**Prioridade:** Alta

---

## Resumo

Este documento descreve as modificações necessárias no **sistema legado** para enviar dados nutricionais via webhook, e as alterações já preparadas no **sistema Laravel (Sync)** para recebê-los.

---

## 1. Estrutura do Webhook Atual

O webhook `product-update` já envia diversos campos do produto, mas **não inclui informações nutricionais detalhadas**.

### Endpoint
```
POST /api/webhook/product-update
```

### Campos Atuais Enviados
```json
{
  "products": [
    {
      "legacy_id": 123,
      "codigo": "KR57",
      "descricao": "Roupa Velha, Arroz Branco e Feijão",
      "apresentacao": "...",
      "propriedades": "...",
      "beneficios": "...",
      "ingredientes": "...",
      "instrucoes_para_consumo": "...",
      "porcao": 100,
      "medida_caseira": "1 porção (100g)",
      "in_contem_gluten": 1,
      "in_contem_lactose": 1,
      "alergenicos_manual": "Contém glúten e lactose",
      // ... outros campos
    }
  ]
}
```

---

## 2. Modificações Necessárias no Sistema Legado

### 2.1 Novo Campo: `informacao_nutricional`

Adicionar ao payload do webhook um objeto `informacao_nutricional` com a seguinte estrutura:

```json
{
  "products": [
    {
      "legacy_id": 123,
      "codigo": "KR57",
      "descricao": "Roupa Velha, Arroz Branco e Feijão",

      // ... campos existentes ...

      "informacao_nutricional": {
        "porcao_tamanho": 350,
        "porcao_unidade": "g",
        "porcao_descricao": "1 unidade (350g)",
        "porcoes_por_embalagem": 1,

        "valores": {
          "valor_energetico_kcal": 805,
          "valor_energetico_kj": 3372,
          "carboidratos_totais": 98,
          "acucares_totais": 5,
          "acucares_adicionados": 0,
          "proteinas": 35,
          "gorduras_totais": 30,
          "gorduras_saturadas": 8.1,
          "gorduras_trans": 0,
          "gorduras_monoinsaturadas": null,
          "gorduras_poliinsaturadas": null,
          "colesterol": null,
          "fibra_alimentar": 9.5,
          "sodio": 2226,
          "calcio": null,
          "ferro": null,
          "potassio": null,
          "vitamina_a": null,
          "vitamina_c": null,
          "vitamina_d": null
        },

        "valores_diarios": {
          "valor_energetico": 40,
          "carboidratos_totais": 33,
          "proteinas": 70,
          "gorduras_totais": 46,
          "gorduras_saturadas": 41,
          "gorduras_trans": 0,
          "fibra_alimentar": 38,
          "sodio": 111
        }
      }
    }
  ]
}
```

### 2.2 Descrição dos Campos Nutricionais

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `porcao_tamanho` | decimal | Sim | Tamanho da porção (ex: 350) |
| `porcao_unidade` | string | Sim | Unidade de medida (g, ml) |
| `porcao_descricao` | string | Não | Descrição da porção (ex: "1 unidade (350g)") |
| `porcoes_por_embalagem` | integer | Não | Quantas porções por embalagem |
| `valores.valor_energetico_kcal` | decimal | Sim | Calorias em kcal |
| `valores.valor_energetico_kj` | decimal | Não | Calorias em kJ |
| `valores.carboidratos_totais` | decimal | Sim | Carboidratos em gramas |
| `valores.acucares_totais` | decimal | Não | Açúcares totais em gramas |
| `valores.acucares_adicionados` | decimal | Não | Açúcares adicionados em gramas |
| `valores.proteinas` | decimal | Sim | Proteínas em gramas |
| `valores.gorduras_totais` | decimal | Sim | Gorduras totais em gramas |
| `valores.gorduras_saturadas` | decimal | Sim | Gorduras saturadas em gramas |
| `valores.gorduras_trans` | decimal | Sim | Gorduras trans em gramas |
| `valores.gorduras_monoinsaturadas` | decimal | Não | Gorduras mono em gramas |
| `valores.gorduras_poliinsaturadas` | decimal | Não | Gorduras poli em gramas |
| `valores.colesterol` | decimal | Não | Colesterol em mg |
| `valores.fibra_alimentar` | decimal | Sim | Fibras em gramas |
| `valores.sodio` | decimal | Sim | Sódio em mg |
| `valores.calcio` | decimal | Não | Cálcio em mg |
| `valores.ferro` | decimal | Não | Ferro em mg |
| `valores.potassio` | decimal | Não | Potássio em mg |
| `valores.vitamina_a` | decimal | Não | Vitamina A em mcg |
| `valores.vitamina_c` | decimal | Não | Vitamina C em mg |
| `valores.vitamina_d` | decimal | Não | Vitamina D em mcg |
| `valores_diarios.*` | integer | Não | Percentual do Valor Diário (%VD) |

---

## 3. Modificações Já Preparadas no Laravel (Sync)

### 3.1 Nova Tabela: `product_nutritional_info`

Migration criada em: `database/migrations/XXXX_create_product_nutritional_info_table.php`

```php
Schema::create('product_nutritional_info', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->onDelete('cascade');

    // Informações da porção
    $table->decimal('portion_size', 10, 2)->nullable();
    $table->string('portion_unit', 10)->default('g');
    $table->string('portion_description')->nullable();
    $table->integer('servings_per_container')->nullable();

    // Valores nutricionais (por porção)
    $table->decimal('energy_kcal', 10, 2)->nullable();
    $table->decimal('energy_kj', 10, 2)->nullable();
    $table->decimal('carbohydrates', 10, 2)->nullable();
    $table->decimal('total_sugars', 10, 2)->nullable();
    $table->decimal('added_sugars', 10, 2)->nullable();
    $table->decimal('proteins', 10, 2)->nullable();
    $table->decimal('total_fat', 10, 2)->nullable();
    $table->decimal('saturated_fat', 10, 2)->nullable();
    $table->decimal('trans_fat', 10, 2)->nullable();
    $table->decimal('monounsaturated_fat', 10, 2)->nullable();
    $table->decimal('polyunsaturated_fat', 10, 2)->nullable();
    $table->decimal('cholesterol', 10, 2)->nullable();
    $table->decimal('dietary_fiber', 10, 2)->nullable();
    $table->decimal('sodium', 10, 2)->nullable();
    $table->decimal('calcium', 10, 2)->nullable();
    $table->decimal('iron', 10, 2)->nullable();
    $table->decimal('potassium', 10, 2)->nullable();
    $table->decimal('vitamin_a', 10, 2)->nullable();
    $table->decimal('vitamin_c', 10, 2)->nullable();
    $table->decimal('vitamin_d', 10, 2)->nullable();

    // Percentuais de Valor Diário (%VD)
    $table->integer('dv_energy')->nullable();
    $table->integer('dv_carbohydrates')->nullable();
    $table->integer('dv_proteins')->nullable();
    $table->integer('dv_total_fat')->nullable();
    $table->integer('dv_saturated_fat')->nullable();
    $table->integer('dv_trans_fat')->nullable();
    $table->integer('dv_dietary_fiber')->nullable();
    $table->integer('dv_sodium')->nullable();

    $table->timestamps();
});
```

### 3.2 Model: `ProductNutritionalInfo`

```php
class ProductNutritionalInfo extends Model
{
    protected $table = 'product_nutritional_info';

    protected $fillable = [
        'product_id',
        'portion_size', 'portion_unit', 'portion_description', 'servings_per_container',
        'energy_kcal', 'energy_kj',
        'carbohydrates', 'total_sugars', 'added_sugars',
        'proteins',
        'total_fat', 'saturated_fat', 'trans_fat', 'monounsaturated_fat', 'polyunsaturated_fat',
        'cholesterol', 'dietary_fiber', 'sodium',
        'calcium', 'iron', 'potassium',
        'vitamin_a', 'vitamin_c', 'vitamin_d',
        'dv_energy', 'dv_carbohydrates', 'dv_proteins', 'dv_total_fat',
        'dv_saturated_fat', 'dv_trans_fat', 'dv_dietary_fiber', 'dv_sodium',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
```

### 3.3 Atualização no ProcessProductWebhook

O Job será atualizado para processar `informacao_nutricional` quando presente:

```php
// No método handle(), após criar/atualizar o produto:
if (isset($this->productData['informacao_nutricional'])) {
    $this->syncNutritionalInfo($product, $this->productData['informacao_nutricional']);
}

protected function syncNutritionalInfo(Product $product, array $nutritionalData)
{
    $valores = $nutritionalData['valores'] ?? [];
    $vd = $nutritionalData['valores_diarios'] ?? [];

    ProductNutritionalInfo::updateOrCreate(
        ['product_id' => $product->id],
        [
            'portion_size' => $nutritionalData['porcao_tamanho'] ?? null,
            'portion_unit' => $nutritionalData['porcao_unidade'] ?? 'g',
            'portion_description' => $nutritionalData['porcao_descricao'] ?? null,
            'servings_per_container' => $nutritionalData['porcoes_por_embalagem'] ?? null,

            'energy_kcal' => $valores['valor_energetico_kcal'] ?? null,
            'energy_kj' => $valores['valor_energetico_kj'] ?? null,
            'carbohydrates' => $valores['carboidratos_totais'] ?? null,
            'total_sugars' => $valores['acucares_totais'] ?? null,
            'added_sugars' => $valores['acucares_adicionados'] ?? null,
            'proteins' => $valores['proteinas'] ?? null,
            'total_fat' => $valores['gorduras_totais'] ?? null,
            'saturated_fat' => $valores['gorduras_saturadas'] ?? null,
            'trans_fat' => $valores['gorduras_trans'] ?? null,
            'dietary_fiber' => $valores['fibra_alimentar'] ?? null,
            'sodium' => $valores['sodio'] ?? null,

            'dv_energy' => $vd['valor_energetico'] ?? null,
            'dv_carbohydrates' => $vd['carboidratos_totais'] ?? null,
            'dv_proteins' => $vd['proteinas'] ?? null,
            'dv_total_fat' => $vd['gorduras_totais'] ?? null,
            'dv_saturated_fat' => $vd['gorduras_saturadas'] ?? null,
            'dv_trans_fat' => $vd['gorduras_trans'] ?? null,
            'dv_dietary_fiber' => $vd['fibra_alimentar'] ?? null,
            'dv_sodium' => $vd['sodio'] ?? null,
        ]
    );
}
```

---

## 4. Exemplo Completo de Payload

```json
{
  "products": [
    {
      "legacy_id": 57,
      "codigo": "KR57",
      "descricao": "Roupa Velha, Arroz Branco e Feijão",
      "categoria_id": 23,
      "marca_id": 1,
      "apresentacao": "Musculo assado com cebola, bacon e temperos...",
      "ingredientes": "Carne bovina, farinha de mandioca, arroz branco...",
      "instrucoes_para_consumo": "Retirar plástico e abrir parcialmente a tampa...",
      "preco": 29.40,
      "peso_liquido": 350,
      "unidade_medida_peso": "g",
      "validade_dias": 240,
      "porcao": 350,
      "medida_caseira": "1 unidade (350g)",
      "in_contem_gluten": 1,
      "in_contem_lactose": 1,
      "alergenicos_manual": "Contém glúten, lactose, soja e derivados de leite. Pode conter trigo.",
      "ativo": 1,

      "informacao_nutricional": {
        "porcao_tamanho": 350,
        "porcao_unidade": "g",
        "porcao_descricao": "1 unidade (350g)",
        "porcoes_por_embalagem": 1,

        "valores": {
          "valor_energetico_kcal": 805,
          "valor_energetico_kj": 3372,
          "carboidratos_totais": 98,
          "acucares_totais": 5,
          "acucares_adicionados": 0,
          "proteinas": 35,
          "gorduras_totais": 30,
          "gorduras_saturadas": 8.1,
          "gorduras_trans": 0,
          "fibra_alimentar": 9.5,
          "sodio": 2226
        },

        "valores_diarios": {
          "valor_energetico": 40,
          "carboidratos_totais": 33,
          "proteinas": 70,
          "gorduras_totais": 46,
          "gorduras_saturadas": 41,
          "gorduras_trans": 0,
          "fibra_alimentar": 38,
          "sodio": 111
        }
      },

      "imagens": [
        {
          "url": "https://exemplo.com/imagem.jpg",
          "posicao": 1,
          "principal": 1
        }
      ]
    }
  ]
}
```

---

## 5. Checklist de Implementação

### Sistema Legado (Responsável: Equipe Legado)
- [ ] Identificar onde os dados nutricionais estão armazenados
- [ ] Criar consulta para obter dados nutricionais do produto
- [ ] Modificar endpoint de webhook para incluir `informacao_nutricional`
- [ ] Testar envio com payload completo

### Sistema Laravel/Sync (Responsável: Equipe Sync)
- [x] Criar documento de especificação (este documento)
- [x] Criar migration para tabela `product_nutritional_info`
- [x] Criar Model `ProductNutritionalInfo`
- [x] Atualizar `ProcessProductWebhook` para processar dados nutricionais
- [x] Criar view da página de produto com placeholder
- [ ] Remover placeholder quando dados estiverem disponíveis

---

## 6. Contato e Dúvidas

Em caso de dúvidas sobre a estrutura do webhook ou implementação, entrar em contato com a equipe de desenvolvimento.

---

**Última atualização:** 2025-11-25
