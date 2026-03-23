{{-- E-mail de confirmação de pedido (cliente e admin) --}}
{{-- Usa inline styles para compatibilidade máxima com clientes de e-mail --}}
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isAdmin ? 'Novo Pedido' : 'Confirmação do Pedido' }} #{{ $order->order_number }}</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.6; color: #333333; background-color: #f4f4f4;">

    {{-- Container principal --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4;">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

                    {{-- Header com cor do tema --}}
                    <tr>
                        <td style="background-color: #013E3B; color: #ffffff; padding: 30px 40px; text-align: center;">
                            <h1 style="margin: 0; font-size: 22px; font-weight: bold;">
                                @if($isAdmin)
                                    Novo Pedido Recebido
                                @else
                                    Pedido Confirmado!
                                @endif
                            </h1>
                            <p style="margin: 10px 0 0; font-size: 16px; opacity: 0.9;">
                                Pedido <strong>#{{ $order->order_number }}</strong>
                            </p>
                        </td>
                    </tr>

                    {{-- Mensagem introdutória --}}
                    <tr>
                        <td style="padding: 30px 40px 20px;">
                            @if($isAdmin)
                                <p style="margin: 0;">Um novo pedido foi realizado na loja. Confira os detalhes abaixo:</p>
                            @else
                                <p style="margin: 0;">Olá, <strong>{{ $order->customer_name }}</strong>!</p>
                                <p style="margin: 10px 0 0;">Seu pedido foi recebido com sucesso. Confira os detalhes abaixo:</p>
                            @endif
                        </td>
                    </tr>

                    {{-- Status do pedido --}}
                    <tr>
                        <td style="padding: 0 40px 20px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="background-color: #FFF3CD; border-left: 4px solid #FFC107; padding: 12px 16px; border-radius: 4px;">
                                        <strong>Status:</strong> {{ $order->status_label }}
                                        @if(!$isAdmin)
                                            <br><small style="color: #666;">O pagamento será combinado diretamente com a Deep Freeze.</small>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Tabela de itens --}}
                    <tr>
                        <td style="padding: 0 40px 20px;">
                            <h2 style="margin: 0 0 15px; font-size: 16px; color: #013E3B; border-bottom: 2px solid #013E3B; padding-bottom: 8px;">
                                Itens do Pedido
                            </h2>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
                                {{-- Cabeçalho da tabela --}}
                                <tr style="background-color: #f8f8f8;">
                                    <td style="padding: 10px 12px; font-weight: bold; font-size: 13px; border-bottom: 1px solid #ddd;">Produto</td>
                                    <td style="padding: 10px 12px; font-weight: bold; font-size: 13px; border-bottom: 1px solid #ddd; text-align: center;">Qtd</td>
                                    <td style="padding: 10px 12px; font-weight: bold; font-size: 13px; border-bottom: 1px solid #ddd; text-align: right;">Preço Unit.</td>
                                    <td style="padding: 10px 12px; font-weight: bold; font-size: 13px; border-bottom: 1px solid #ddd; text-align: right;">Total</td>
                                </tr>
                                {{-- Itens --}}
                                @foreach($order->items as $item)
                                    <tr>
                                        <td style="padding: 10px 12px; border-bottom: 1px solid #eee;">
                                            {{ $item->product_name }}
                                            @if($item->product_sku)
                                                <br><small style="color: #999;">SKU: {{ $item->product_sku }}</small>
                                            @endif
                                        </td>
                                        <td style="padding: 10px 12px; border-bottom: 1px solid #eee; text-align: center;">{{ $item->quantity }}</td>
                                        <td style="padding: 10px 12px; border-bottom: 1px solid #eee; text-align: right;">{{ $item->formatted_unit_price }}</td>
                                        <td style="padding: 10px 12px; border-bottom: 1px solid #eee; text-align: right;">{{ $item->formatted_total }}</td>
                                    </tr>
                                @endforeach
                            </table>

                            {{-- Totais --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top: 15px;">
                                <tr>
                                    <td style="padding: 6px 12px; text-align: right;">Subtotal:</td>
                                    <td style="padding: 6px 12px; text-align: right; width: 120px;">{{ $order->formatted_subtotal }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 6px 12px; text-align: right;">Frete:</td>
                                    <td style="padding: 6px 12px; text-align: right; width: 120px;">
                                        @if((float) $order->shipping_cost > 0)
                                            {{ $order->formatted_shipping_cost }}
                                        @else
                                            A combinar
                                        @endif
                                    </td>
                                </tr>
                                @if((float) $order->discount > 0)
                                    <tr>
                                        <td style="padding: 6px 12px; text-align: right; color: #28a745;">Desconto:</td>
                                        <td style="padding: 6px 12px; text-align: right; width: 120px; color: #28a745;">- {{ $order->formatted_discount }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td style="padding: 10px 12px; text-align: right; font-weight: bold; font-size: 16px; border-top: 2px solid #013E3B;">Total:</td>
                                    <td style="padding: 10px 12px; text-align: right; width: 120px; font-weight: bold; font-size: 16px; border-top: 2px solid #013E3B; color: #013E3B;">{{ $order->formatted_total }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Dados do cliente --}}
                    <tr>
                        <td style="padding: 0 40px 20px;">
                            <h2 style="margin: 0 0 15px; font-size: 16px; color: #013E3B; border-bottom: 2px solid #013E3B; padding-bottom: 8px;">
                                Dados do Cliente
                            </h2>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding: 4px 0;"><strong>Nome:</strong> {{ $order->customer_name }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 0;"><strong>E-mail:</strong> {{ $order->customer_email }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 0;"><strong>Telefone:</strong> {{ $order->customer_phone }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 0;">
                                        <strong>{{ $order->customer_person_type === 'juridica' ? 'CNPJ' : 'CPF' }}:</strong>
                                        {{ $order->customer_person_type === 'juridica' ? $order->customer_cnpj : $order->customer_cpf }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Endereço de entrega --}}
                    <tr>
                        <td style="padding: 0 40px 20px;">
                            <h2 style="margin: 0 0 15px; font-size: 16px; color: #013E3B; border-bottom: 2px solid #013E3B; padding-bottom: 8px;">
                                Endereço de Entrega
                            </h2>
                            <p style="margin: 0;">{{ $order->full_address }}</p>
                        </td>
                    </tr>

                    {{-- Observações (se houver) --}}
                    @if($order->notes)
                        <tr>
                            <td style="padding: 0 40px 20px;">
                                <h2 style="margin: 0 0 15px; font-size: 16px; color: #013E3B; border-bottom: 2px solid #013E3B; padding-bottom: 8px;">
                                    Observações
                                </h2>
                                <p style="margin: 0; background-color: #f8f8f8; padding: 12px; border-radius: 4px;">{{ $order->notes }}</p>
                            </td>
                        </tr>
                    @endif

                    {{-- Mensagem sobre pagamento (apenas para o cliente) --}}
                    @if(!$isAdmin)
                        <tr>
                            <td style="padding: 0 40px 30px;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="background-color: #E8F5E9; border-left: 4px solid #013E3B; padding: 15px; border-radius: 4px;">
                                            <strong>Próximos passos:</strong><br>
                                            O pagamento será combinado diretamente com a Deep Freeze.
                                            Entraremos em contato em breve para confirmar a forma de pagamento e o envio do seu pedido.
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endif

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color: #f8f8f8; padding: 20px 40px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #eee;">
                            <p style="margin: 0;">Deep Freeze Congelados Caseiros</p>
                            <p style="margin: 5px 0 0;">Este é um e-mail automático. Pedido realizado em {{ $order->created_at->format('d/m/Y \à\s H:i') }}.</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
