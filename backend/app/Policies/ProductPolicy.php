<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function manage(User $user, ?Product $product = null): bool
    {
        return $user->hasPermission('products.manage');
    }
}
