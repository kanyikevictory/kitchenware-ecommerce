<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function view(User $user, Payment $payment): bool
    {
        return $payment->order?->user_id === $user->id;
    }

    public function manage(User $user, ?Payment $payment = null): bool
    {
        return in_array($user->role?->slug, ['admin', 'super-admin'], true);
    }
}
