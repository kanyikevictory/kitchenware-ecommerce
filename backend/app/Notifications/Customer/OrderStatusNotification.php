<?php

namespace App\Notifications\Customer;

use App\Models\Order;
use App\Notifications\QueuedMailNotification;
use Illuminate\Notifications\Messages\MailMessage;

class OrderStatusNotification extends QueuedMailNotification
{
    public function __construct(public readonly Order $order, public readonly string $status)
    {
        parent::__construct();
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = match ($this->status) {
            'shipped' => 'Your order has shipped and is on its way.',
            'delivered' => 'Your order has been delivered.',
            'cancelled' => 'Your order has been cancelled.',
            default => "Your order status is now {$this->status}.",
        };

        return (new MailMessage)
            ->subject("Order {$this->order->order_number}: ".ucfirst($this->status))
            ->greeting("Hello {$notifiable->name},")
            ->line($message)
            ->action('View Order', rtrim((string) config('app.frontend_url'), '/').'/orders/'.$this->order->id);
    }
}
