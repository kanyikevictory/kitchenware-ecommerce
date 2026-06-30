<?php

namespace App\Listeners;

use App\Events\PaymentCompleted;
use App\Notifications\Customer\PaymentConfirmationNotification;

class SendPaymentConfirmation
{
    public function handle(PaymentCompleted $event): void
    {
        $event->payment->order?->user?->notify(new PaymentConfirmationNotification($event->payment));
    }
}
