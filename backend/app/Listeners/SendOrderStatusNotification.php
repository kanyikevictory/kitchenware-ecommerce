<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Notifications\Customer\OrderStatusNotification;

class SendOrderStatusNotification
{
    public function handle(OrderStatusChanged $event): void
    {
        if (in_array($event->currentStatus, ['shipped', 'delivered', 'cancelled'], true)) {
            $event->order->user?->notify(new OrderStatusNotification($event->order, $event->currentStatus));
        }
    }
}
