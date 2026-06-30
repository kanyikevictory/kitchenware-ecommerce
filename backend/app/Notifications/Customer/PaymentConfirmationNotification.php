<?php

namespace App\Notifications\Customer;

use App\Models\Payment;
use App\Notifications\QueuedMailNotification;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentConfirmationNotification extends QueuedMailNotification
{
    public function __construct(public readonly Payment $payment)
    {
        parent::__construct();
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment confirmed')
            ->greeting("Hello {$notifiable->name},")
            ->line("Your payment of {$this->payment->amount} {$this->payment->currency} was confirmed.")
            ->line("Transaction reference: {$this->payment->transaction_id}")
            ->action('View Order', rtrim((string) config('app.frontend_url'), '/').'/orders/'.$this->payment->order_id);
    }
}
