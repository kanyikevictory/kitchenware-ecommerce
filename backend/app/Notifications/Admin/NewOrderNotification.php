<?php

namespace App\Notifications\Admin;

use App\Models\Order;
use App\Notifications\QueuedMailNotification;
use Illuminate\Notifications\Messages\MailMessage;

class NewOrderNotification extends QueuedMailNotification
{
    public function __construct(public readonly Order $order)
    {
        parent::__construct();
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New order {$this->order->order_number}")
            ->line("A new order worth {$this->order->grand_total} UGX has been placed.")
            ->action('View Order', rtrim((string) config('app.frontend_url'), '/').'/admin/orders/'.$this->order->id);
    }
}
