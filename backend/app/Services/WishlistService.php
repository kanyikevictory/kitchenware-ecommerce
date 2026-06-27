<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WishlistService
{
    public function get(User $user): Wishlist
    {
        return $this->load($this->resolveWishlist($user));
    }

    public function add(User $user, int $productId): Wishlist
    {
        $wishlist = DB::transaction(function () use ($user, $productId): Wishlist {
            $wishlist = $this->resolveWishlist($user);
            $product = Product::query()->findOrFail($productId);

            if ($product->status !== 'active' || ! $product->category()->where('is_active', true)->exists()) {
                throw ValidationException::withMessages([
                    'product_id' => ['This product is not currently available.'],
                ]);
            }

            WishlistItem::query()->firstOrCreate([
                'wishlist_id' => $wishlist->id,
                'product_id' => $product->id,
            ]);

            return $wishlist;
        });

        return $this->load($wishlist);
    }

    public function remove(WishlistItem $item): Wishlist
    {
        $wishlist = DB::transaction(function () use ($item): Wishlist {
            $wishlist = Wishlist::query()->lockForUpdate()->findOrFail($item->wishlist_id);
            WishlistItem::query()->whereKey($item->id)->delete();

            return $wishlist;
        });

        return $this->load($wishlist);
    }

    private function resolveWishlist(User $user): Wishlist
    {
        return Wishlist::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['status' => 'active'],
        );
    }

    private function load(Wishlist $wishlist): Wishlist
    {
        return $wishlist->refresh()->load([
            'items' => fn ($query) => $query->latest(),
            'items.product:id,category_id,name,slug,sku,price,discount_price,stock_quantity,status,deleted_at',
            'items.product.category:id,is_active',
            'items.product.primaryImage:id,product_id,path',
        ]);
    }
}
