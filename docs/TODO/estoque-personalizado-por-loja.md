# TODO: Estoque Personalizado por Loja (Baseado no CEP do Cliente)

**Prioridade:** Pós-virada do site (Fase 2)
**Complexidade:** Alta
**Data da análise:** 02/04/2026

---

## Resumo

Após o cliente informar seu CEP, o sistema identifica a loja/franquia que atende aquela região. A partir disso, as listagens de produtos passam a mostrar apenas produtos com estoque disponível naquela loja específica, em vez do estoque geral (soma de todas as lojas).

## Fluxo proposto

1. Cliente informa CEP (via modal "Entrega na minha região" ou no checkout)
2. Sistema resolve: CEP → região → loja (`lojas_entregas_regioes`)
3. Salva `loja_id` na sessão do cliente
4. Todas as listagens (home, categorias, busca) filtram produtos por `otm_estoques_lojas.loja_id = session.loja_id AND (estoque_atual_calculado - giro_balcao) > 0`
5. Carrinho valida estoque na loja específica
6. Se cliente não informou CEP, mantém comportamento atual (estoque geral)

## Dados disponíveis no banco legado

| Tabela | Campos | Registros |
|--------|--------|-----------|
| `otm_estoques_lojas` | produto_id, loja_id, estoque_atual_calculado, giro_balcao | Por produto × loja |
| `lojas` | id, nome (29 lojas cadastradas) | 29 |
| `lojas_entregas_regioes` | loja_id, entregas_regiao_id | Mapeamento região → loja |

## Impacto nos arquivos

| Arquivo | Alteração |
|---------|-----------|
| `Product.php` | Novo scope `scopeWithStockByStore($query, $lojaId)` |
| `ShippingService.php` | Salvar `loja_id` na sessão após lookup de CEP |
| `CategoryController.php` (storefront) | Usar scope filtrado se sessão tem loja_id |
| `HomeController.php` | Idem |
| `CartService.php` | Validar estoque na loja específica |
| Header/modal | Exibir "Entregamos na sua região! Loja X" |

## Scope do Product model (proposta)

```php
public function scopeWithStockByStore($query, int $lojaId)
{
    return $query->select('produtos.*')
        ->selectRaw(
            '(SELECT COALESCE(SUM(estoque_atual_calculado - giro_balcao), 0)
              FROM otm_estoques_lojas
              WHERE otm_estoques_lojas.produto_id = produtos.id
              AND otm_estoques_lojas.loja_id = ?) as _stock',
            [$lojaId]
        );
}
```

## Riscos

- Produtos com estoque zero na loja do cliente mas disponíveis em outras lojas não apareceriam
- Cliente pode mudar de CEP e o carrinho pode ter produtos sem estoque na nova loja
- Performance: subquery por loja é mais leve que SUM de todas as lojas (melhor)

## Estimativa

- Scope e service: ~2h
- Adaptar listagens (home, categoria, busca): ~3h  
- Carrinho e checkout: ~2h
- Testes: ~1h
