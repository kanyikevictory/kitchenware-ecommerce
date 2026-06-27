<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function get(User $user): Cart
    {
        return $this->load($this->resolveCart($user));
    }

    public function add(User $user, int $productId, int $quantity): Cart
    {
        $cartId = $this->resolveCart($user)->id;

        $cart = DB::transaction(function () use ($cartId, $productId, $quantity): Cart {
            $cart = Cart::query()->lockForUpdate()->findOrFail($cartId);
            $product = Product::query()->lockForUpdate()->findOrFail($productId);
            $this->ensureAvailable($product);
            $item = CartItem::query()
                ->where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->lockForUpdate()
                ->first();
            $newQuantity = ($item?->quantity ?? 0) + $quantity;
            $this->ensureStock($product, $newQuantity);

            $item ??= new CartItem(['cart_id' => $cart->id, 'product_id' => $product->id]);
            $item->quantity = $newQuantity;
            $this->priceItem($item, $product);
            $item->save();
            $this->recalculate($cart);

            return $cart;
        });

        return $this->load($cart);
    }

    public function update(CartItem $item, int $quantity): Cart
    {
        $cart = DB::transaction(function () use ($item, $quantity): Cart {
            $cart = Cart::query()->lockForUpdate()->findOrFail($item->cart_id);
            $product = Product::query()->lockForUpdate()->findOrFail($item->product_id);
            $lockedItem = CartItem::query()->lockForUpdate()->findOrFail($item->id);
            $this->ensureAvailable($product);
            $this->ensureStock($product, $quantity);
            $lockedItem->quantity = $quantity;
            $this->priceItem($lockedItem, $product);
            $lockedItem->save();
            $this->recalculate($cart);

            return $cart;
        });

        return $this->load($cart);
    }

    public function remove(CartItem $item): Cart
    {
        $cart = DB::transaction(function () use ($item): Cart {
            $cart = Cart::query()->lockForUpdate()->findOrFail($item->cart_id);
            $lockedItem = CartItem::query()->lockForUpdate()->findOrFail($item->id);
            $lockedItem->delete();
            $this->recalculate($cart);

            return $cart;
        });

        return $this->load($cart);
    }

    public function clear(User $user): Cart
    {
        $cartId = $this->resolveCart($user)->id;

        $cart = DB::transaction(function () use ($cartId): Cart {
            $cart = Cart::query()->lockForUpdate()->findOrFail($cartId);
            CartItem::query()->where('cart_id', $cart->id)->delete();
            $cart->update([
                'subtotal' => 0,
                'discount_total' => 0,
                'tax_total' => 0,
                'grand_total' => 0,
            ]);

            return $cart;
        });

        return $this->load($cart);
    }

    private function ensureAvailable(Product $product): void
    {
        if ($product->status !== 'active' || ! $product->category()->where('is_active', true)->exists()) {
            throw ValidationException::withMessages(['product_id' => ['This product is not currently available.']]);
        }
    }

    private function ensureStock(Product $product, int $quantity): void
    {
        if ($quantity > 100) {
            throw ValidationException::withMessages(['quantity' => ['A cart line may contain at most 100 units.']]);
        }

        if ($quantity > $product->stock_quantity) {
            throw ValidationException::withMessages([
                'quantity' => ["Only {$product->stock_quantity} units are currently available."],
            ]);
        }
    }

    private function priceItem(CartItem $item, Product $product): void
    {
        $unitPriceCents = $this->toCents((string) $product->price);
        $effectivePriceCents = $product->discount_price !== null
            ? $this->toCents((string) $product->discount_price)
            : $unitPriceCents;

        $item->fill([
            'unit_price' => $this->fromCents($unitPriceCents),
            'discount_amount' => $this->fromCents(($unitPriceCents - $effectivePriceCents) * $item->quantity),
            'total_price' => $this->fromCents($effectivePriceCents * $item->quantity),
        ]);
    }

    private function toCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');
        $fraction = str_pad(substr($fraction, 0, 2), 2, '0');

        return ((int) $whole * 100) + (int) $fraction;
    }

    private function fromCents(int $amount): string
    {
        return sprintf('%d.%02d', intdiv($amount, 100), $amount % 100);
    }

    private function recalculate(Cart $cart): void
    {
        /** @var object{subtotal: string|int|float, discount_total: string|int|float, grand_total: string|int|float} $totals */
        $totals = DB::table('cart_items')->where('cart_id', $cart->id)->selectRaw(
            'COALESCE(SUM(unit_price * quantity), 0) as subtotal, COALESCE(SUM(discount_amount), 0) as discount_total, COALESCE(SUM(total_price), 0) as grand_total'
        )->first();

        $cart->update([
            'subtotal' => $totals->subtotal,
            'discount_total' => $totals->discount_total,
            'tax_total' => 0,
            'grand_total' => $totals->grand_total,
        ]);
    }

    private function load(Cart $cart): Cart
    {
        return $cart->refresh()->load([
            'items' => fn ($query) => $query->oldest(),
            'items.product:id,name,slug,sku,stock_quantity,status,deleted_at',
            'items.product.category:id,is_active',
            'items.product.primaryImage:id,product_id,path',
        ]);
    }

    private function resolveCart(User $user): Cart
    {
        return Cart::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['currency' => 'UGX', 'status' => 'active'],
        );
    }
}
