<?php

namespace App\Services;

use App\Models\ShippingAddress;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ShippingAddressService
{
    public function create(User $user, array $attributes): ShippingAddress
    {
        return DB::transaction(function () use ($user, $attributes): ShippingAddress {
            $attributes['is_default'] = $attributes['is_default'] ?? ! $user->shippingAddresses()->exists();

            if ($attributes['is_default']) {
                $user->shippingAddresses()->update(['is_default' => false]);
            }

            return $user->shippingAddresses()->create($attributes);
        });
    }

    public function update(ShippingAddress $address, array $attributes): ShippingAddress
    {
        return DB::transaction(function () use ($address, $attributes): ShippingAddress {
            if ($attributes['is_default'] ?? false) {
                $address->user->shippingAddresses()->whereKeyNot($address->id)->update(['is_default' => false]);
            }

            $address->update($attributes);

            return $address->refresh();
        });
    }

    public function delete(ShippingAddress $address): void
    {
        DB::transaction(function () use ($address): void {
            $wasDefault = $address->is_default;
            $user = $address->user;
            $address->delete();

            if ($wasDefault) {
                $user->shippingAddresses()->latest()->first()?->update(['is_default' => true]);
            }
        });
    }
}
