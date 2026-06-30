<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Models\User;
use App\Notifications\Admin\NewUserNotification;
use App\Notifications\Customer\WelcomeNotification;
use Illuminate\Support\Facades\Notification;

class SendRegistrationNotifications
{
    public function handle(UserRegistered $event): void
    {
        $event->user->notify(new WelcomeNotification);
        $event->user->sendEmailVerificationNotification();

        Notification::send($this->administrators(), new NewUserNotification($event->user));
    }

    private function administrators()
    {
        return User::query()->where('status', 'active')
            ->whereHas('role', fn ($query) => $query->whereIn('slug', ['admin', 'super-admin']))
            ->get();
    }
}
