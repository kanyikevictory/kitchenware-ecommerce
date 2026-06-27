<?php

namespace App\Policies;

use App\Models\ShippingAddress;
use App\Models\User;

class ShippingAddressPolicy
{
    public function view(User $user, ShippingAddress $shippingAddress): bool
    {
        return $user->id === $shippingAddress->user_id;
    }

    public function update(User $user, ShippingAddress $shippingAddress): bool
    {
        return $this->view($user, $shippingAddress);
    }

    public function delete(User $user, ShippingAddress $shippingAddress): bool
    {
        return $this->view($user, $shippingAddress);
    }
}
