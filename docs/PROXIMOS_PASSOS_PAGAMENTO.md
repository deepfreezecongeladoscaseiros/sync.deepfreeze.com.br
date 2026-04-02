# Próximos Passos — Integração de Pagamentos

> Documento gerado em 31/03/2026 após conclusão da implementação dos gateways Cielo Checkout + Rede e-Rede no sync.deepfreeze.com.br

---

## 1. Testes com Sandbox

### Cielo Checkout
- [ ] Cadastrar MerchantId de sandbox no banco legado (tabela `lojas.merchant_id`)
- [ ] Alterar temporariamente a URL da API no `CieloCheckoutService` para o ambiente sandbox da Cielo: `https://cieloecommerce.cielo.com.br/api/public/v1/orders` (verificar se sandbox usa URL diferente)
- [ ] Testar fluxo completo: checkout → redirect Cielo → pagar → webhook → polling → confirmação
- [ ] Verificar que o webhook do legado (`/siv_v2/cielo/notificacao`) grava corretamente em `pagamentos_cielo`
- [ ] Verificar que o polling do sync detecta o pagamento e redireciona para confirmação
- [ ] Testar cenários de erro: pagamento negado, expirado, cancelado pelo cliente
- [ ] Testar timeout do polling (10 minutos sem resposta)

### Rede e-Rede
- [ ] Usar credenciais de sandbox (PV/Token de teste) — alterar temporariamente para `\Rede\Environment::sandbox()` no `RedePaymentService`
- [ ] Testar crédito: form → aprovação → confirmação
- [ ] Testar débito: form → 3D Secure → callback → confirmação
- [ ] Testar cartão negado (usar número de teste que retorna erro)
- [ ] Testar validação de formulário: número inválido, CVV vazio, validade expirada
- [ ] Verificar que dados do cartão são mascarados em `pagamentos_rede.transaction_json`
- [ ] Verificar detecção de bandeira (Visa, Mastercard, Elo)
- [ ] Verificar que `formas_pagamento_id` é atualizado corretamente pela bandeira

### Pagamento Offline
- [ ] Testar dinheiro com retirada na loja → pedido deve ficar `finalizado=1` direto
- [ ] Testar PIX → pedido fica `finalizado=0`, cliente vê confirmação com instruções
- [ ] Verificar que o SIV enxerga os pedidos criados pela nova loja

---

## 2. Infraestrutura e SSL

- [ ] Garantir que `sync.deepfreeze.com.br` tem SSL (HTTPS) ativo — obrigatório para ReturnUrl da Cielo e URLs de 3D Secure da Rede
- [ ] Verificar que a rota `/pagamento/aguardar-cielo/{id}` funciona após redirect da Cielo — o cliente volta do domínio Cielo e pode perder a session Laravel (cookie de sessão). Testar com navegador real.
- [ ] Se a session for perdida: considerar usar um token na URL ou cookie persistente para manter o acesso à página de polling

---

## 3. Webhook da Cielo

O webhook permanece no legado (`/siv_v2/cielo/notificacao`). **Não é necessário alterar nada na Cielo.**

Porém, quando o sync substituir completamente o legado:
- [ ] Migrar o endpoint de webhook para o sync (`/pagamento/callback`)
- [ ] Implementar parsing do payload Cielo no `PaymentController::callback()`
- [ ] Atualizar a URL de notificação no painel da Cielo
- [ ] Manter o endpoint legado como fallback durante a transição

---

## 4. Credenciais por Loja

### Descoberta importante
Apenas a **loja 9 (Copacabana)** tem `merchant_id` da Cielo cadastrado. O `CieloCheckoutService` já tem fallback automático — busca qualquer loja ativa com merchant_id se a loja do pedido não tiver.

- [ ] Verificar se as outras lojas precisam de merchant_id próprio ou se compartilham um único
- [ ] Confirmar com a Cielo se é possível usar um único MerchantId para todas as lojas
- [ ] Se necessário, cadastrar merchant_id nas lojas que processam pedidos online

### Rede
PV e Token estão cadastrados em todas as lojas ativas. Sem ação necessária.

---

## 5. Deprecation Warnings do SDK Rede

O SDK Rede foi copiado do legado e foi escrito para PHP 7.x. No PHP 8.1+ emite warnings:
- Parâmetros nullable implícitos (`eRede::__construct()`)
- `jsonSerialize()` sem `#[\ReturnTypeWillChange]`

**Não é bloqueante** — o SDK funciona normalmente.

- [ ] Corrigir os warnings adicionando `#[\ReturnTypeWillChange]` nos métodos `jsonSerialize()`
- [ ] Corrigir parâmetros nullable explícitos (`?Type $param = null`)
- [ ] Alternativa: verificar se existe versão moderna do SDK Rede para Composer

---

## 6. Melhorias Futuras (não bloqueantes)

- [ ] **PIX com QR Code**: Implementar geração de QR Code PIX em vez de enviar chave por e-mail
- [ ] **Parcelamento**: Cielo Checkout suporta parcelamento nativo — avaliar se a Deep Freeze quer oferecer
- [ ] **E-mail de pagamento aprovado**: Enviar e-mail específico quando pagamento online é confirmado (hoje só envia na criação do pedido)
- [ ] **Retry de pagamento**: Se o pagamento falhar, permitir que o cliente tente novamente sem criar novo pedido
- [ ] **Admin: status de pagamento**: Exibir status do pagamento no painel admin (lido de `pagamentos_cielo` e `pagamentos_rede`)
- [ ] **Boleto**: Avaliar integração de boleto se houver demanda
- [ ] **Refund/Estorno**: Implementar estorno via API (Cielo e Rede suportam)

---

## 7. Arquivos Implementados (referência)

| Arquivo | Função |
|---------|--------|
| `app/Models/Legacy/PagamentoRede.php` | Model tabela `pagamentos_rede` |
| `app/Libraries/Rede/` | SDK Rede e-Rede (32 arquivos) |
| `app/Services/CieloCheckoutService.php` | API Cielo Checkout v1 |
| `app/Services/RedePaymentService.php` | Crédito/débito via SDK Rede |
| `app/Services/PaymentService.php` | Helpers de gateway (modificado) |
| `app/Http/Controllers/Storefront/PaymentController.php` | Controller com 6 actions |
| `app/Http/Controllers/Storefront/CheckoutController.php` | Redirect para gateway (modificado) |
| `app/Models/Legacy/Loja.php` | Credenciais mapeadas (modificado) |
| `routes/web.php` | 7 rotas de pagamento (modificado) |
| `resources/views/storefront/payment/aguardar-cielo.blade.php` | Polling Cielo |
| `resources/views/storefront/payment/rede-cartao.blade.php` | Form de cartão Rede |
| `docs/superpowers/plans/2026-03-31-payment-gateway-integration.md` | Plano de implementação |
