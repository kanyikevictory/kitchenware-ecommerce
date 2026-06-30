<?php

namespace App\Notifications\Customer;

use App\Notifications\QueuedMailNotification;
use Illuminate\Notifications\Messages\MailMessage;

class WelcomeNotification extends QueuedMailNotification
{
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to '.config('app.name'))
            ->greeting("Welcome, {$notifiable->name}!")
            ->line('Your kitchen store account has been created successfully.')
            ->line('Verify your email address to access checkout and other protected features.');
    }
}
