<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return $user->id === $order->user_id;
    }

    public function cancel(User $user, Order $order): bool
    {
        return $this->view($user, $order) && in_array($order->status, ['pending', 'confirmed'], true);
    }

    public function manage(User $user, ?Order $order = null): bool
    {
        return $user->hasPermission('orders.manage');
    }
}
