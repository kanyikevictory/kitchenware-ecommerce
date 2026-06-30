<?php

namespace App\Notifications\Admin;

use App\Models\User;
use App\Notifications\QueuedMailNotification;
use Illuminate\Notifications\Messages\MailMessage;

class NewUserNotification extends QueuedMailNotification
{
    public function __construct(public readonly User $user)
    {
        parent::__construct();
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New customer registration')
            ->line("{$this->user->name} registered with {$this->user->email}.")
            ->action('View Users', rtrim((string) config('app.frontend_url'), '/').'/admin/users');
    }
}
