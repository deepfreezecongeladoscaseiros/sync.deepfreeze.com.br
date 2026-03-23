<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * E-mail de confirmação de pedido.
 *
 * Enviado tanto para o cliente quanto para o admin.
 * A flag $isAdmin controla o título e o tom da mensagem:
 * - Cliente: "Confirmação do Pedido #..."
 * - Admin: "Novo Pedido #..."
 *
 * Compatível com Order (banco sync) e Pedido (banco legado).
 */
class OrderConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public Model $order;
    public bool $isAdmin;

    public function __construct(Model $order, bool $isAdmin = false)
    {
        $this->order = $order;
        $this->isAdmin = $isAdmin;
    }

    public function envelope(): Envelope
    {
        // Determina o número do pedido (compatível com ambos os models)
        $orderNumber = $this->order->order_number ?? $this->order->id ?? '?';

        $subject = $this->isAdmin
            ? "Novo Pedido #{$orderNumber}"
            : "Confirmação do Pedido #{$orderNumber}";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-confirmation',
            with: [
                'order'   => $this->order,
                'isAdmin' => $this->isAdmin,
            ],
        );
    }
}
