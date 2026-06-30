<?php

namespace App\Notifications\Customer;

use App\Models\Order;
use App\Notifications\QueuedMailNotification;
use Illuminate\Notifications\Messages\MailMessage;

class OrderConfirmationNotification extends QueuedMailNotification
{
    public function __construct(public readonly Order $order)
    {
        parent::__construct();
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Order {$this->order->order_number} confirmed")
            ->greeting("Hello {$notifiable->name},")
            ->line("We received your order {$this->order->order_number}.")
            ->line("Order total: {$this->order->grand_total} UGX")
            ->action('View Order', rtrim((string) config('app.frontend_url'), '/').'/orders/'.$this->order->id)
            ->line('We will notify you as the order progresses.');
    }
}
