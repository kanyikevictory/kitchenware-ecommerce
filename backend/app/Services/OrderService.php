<?php

namespace App\Services;

use App\Events\OrderPlaced;
use App\Events\OrderStatusChanged;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingAddress;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(private readonly CouponService $couponService) {}

    public function checkout(User $user, int $shippingAddressId, ?string $notes, ?string $couponCode = null): Order
    {
        $order = DB::transaction(function () use ($user, $shippingAddressId, $notes, $couponCode): Order {
            $address = ShippingAddress::query()
                ->where('user_id', $user->id)
                ->find($shippingAddressId);

            if (! $address) {
                throw ValidationException::withMessages([
                    'shipping_address_id' => ['The selected shipping address is invalid.'],
                ]);
            }

            $cart = Cart::query()->where('user_id', $user->id)->lockForUpdate()->first();

            if (! $cart) {
                throw ValidationException::withMessages(['cart' => ['Your cart is empty.']]);
            }

            $items = CartItem::query()
                ->where('cart_id', $cart->id)
                ->orderBy('product_id')
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => ['Your cart is empty.']]);
            }

            $products = Product::query()
                ->whereIn('id', $items->pluck('product_id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $lines = $this->buildLines($items, $products);
            $subtotalCents = $lines->sum('subtotal_cents');
            $productDiscountCents = $lines->sum('discount_cents');
            $eligibleAmountCents = $subtotalCents - $productDiscountCents;
            $couponApplication = $couponCode
                ? $this->couponService->evaluate($couponCode, $eligibleAmountCents, true)
                : null;
            $discountCents = $productDiscountCents + ($couponApplication?->discountCents ?? 0);
            $grandTotalCents = $subtotalCents - $discountCents;

            $order = Order::query()->create([
                'order_number' => 'ORD-'.Str::upper((string) Str::ulid()),
                'user_id' => $user->id,
                'shipping_address_id' => $address->id,
                'coupon_id' => $couponApplication?->coupon->id,
                'shipping_first_name' => $address->first_name,
                'shipping_last_name' => $address->last_name,
                'shipping_phone' => $address->phone,
                'shipping_country' => $address->country,
                'shipping_state' => $address->state,
                'shipping_city' => $address->city,
                'shipping_address_line_1' => $address->address_line_1,
                'shipping_address_line_2' => $address->address_line_2,
                'shipping_postal_code' => $address->postal_code,
                'subtotal' => $this->fromCents($subtotalCents),
                'discount_total' => $this->fromCents($discountCents),
                'shipping_total' => '0.00',
                'tax_total' => '0.00',
                'grand_total' => $this->fromCents($grandTotalCents),
                'status' => 'pending',
                'notes' => $notes,
                'placed_at' => now(),
            ]);

            foreach ($lines as $line) {
                $product = $line['product'];
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'unit_price' => $this->fromCents($line['unit_price_cents']),
                    'quantity' => $line['quantity'],
                    'discount_amount' => $this->fromCents($line['discount_cents']),
                    'total_price' => $this->fromCents($line['total_cents']),
                ]);

                $product->stock_quantity -= $line['quantity'];
                $product->save();
            }

            CartItem::query()->where('cart_id', $cart->id)->delete();
            $cart->update([
                'subtotal' => 0,
                'discount_total' => 0,
                'tax_total' => 0,
                'grand_total' => 0,
            ]);

            if ($couponApplication) {
                $this->couponService->recordUsage($couponApplication->coupon);
            }

            return $order->load(['items', 'coupon']);
        });

        event(new OrderPlaced($order));

        return $order;
    }

    public function cancel(Order $order): Order
    {
        return $this->transition($order, 'cancelled', true);
    }

    public function updateStatus(Order $order, string $status): Order
    {
        return $this->transition($order, $status);
    }

    /**
     * @param  Collection<int, CartItem>  $items
     * @param  Collection<int, Product>  $products
     * @return Collection<int, array{product: Product, quantity: int, unit_price_cents: int, subtotal_cents: int, discount_cents: int, total_cents: int}>
     */
    private function buildLines(Collection $items, Collection $products): Collection
    {
        return $items->map(function (CartItem $item) use ($products): array {
            $product = $products->get($item->product_id);

            if (! $product || $product->status !== 'active' || ! $product->category()->where('is_active', true)->exists()) {
                throw ValidationException::withMessages(['cart' => ['Your cart contains an unavailable product.']]);
            }

            if ($item->quantity > $product->stock_quantity) {
                throw ValidationException::withMessages([
                    'cart' => ["{$product->name} has only {$product->stock_quantity} units available."],
                ]);
            }

            $unitPriceCents = $this->toCents((string) $product->price);
            $effectivePriceCents = $product->discount_price !== null
                ? $this->toCents((string) $product->discount_price)
                : $unitPriceCents;

            return [
                'product' => $product,
                'quantity' => $item->quantity,
                'unit_price_cents' => $unitPriceCents,
                'subtotal_cents' => $unitPriceCents * $item->quantity,
                'discount_cents' => ($unitPriceCents - $effectivePriceCents) * $item->quantity,
                'total_cents' => $effectivePriceCents * $item->quantity,
            ];
        });
    }

    private function transition(Order $order, string $status, bool $customerCancellation = false): Order
    {
        [$updatedOrder, $previousStatus] = DB::transaction(function () use ($order, $status, $customerCancellation): array {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $previousStatus = $lockedOrder->status;
            $allowed = [
                'pending' => ['confirmed', 'cancelled'],
                'confirmed' => ['processing', 'cancelled'],
                'processing' => ['shipped', 'cancelled'],
                'shipped' => ['delivered'],
                'delivered' => [],
                'cancelled' => [],
            ];

            if (($customerCancellation && ! in_array($lockedOrder->status, ['pending', 'confirmed'], true))
                || ! in_array($status, $allowed[$lockedOrder->status] ?? [], true)) {
                throw ValidationException::withMessages([
                    'status' => ["An order cannot move from {$lockedOrder->status} to {$status}."],
                ]);
            }

            if ($status === 'cancelled') {
                $this->restoreStock($lockedOrder);
            }

            $lockedOrder->update(['status' => $status]);

            return [$lockedOrder->load(['items', 'user:id,name,email']), $previousStatus];
        });

        event(new OrderStatusChanged($updatedOrder, $previousStatus, $status));

        return $updatedOrder;
    }

    private function restoreStock(Order $order): void
    {
        $items = OrderItem::query()->where('order_id', $order->id)->orderBy('product_id')->get();
        $products = Product::withTrashed()->whereIn('id', $items->pluck('product_id')->filter())
            ->orderBy('id')->lockForUpdate()->get()->keyBy('id');

        foreach ($items as $item) {
            $product = $products->get($item->product_id);

            if ($product instanceof Product) {
                $product->update([
                    'stock_quantity' => $product->stock_quantity + $item->quantity,
                ]);
            }
        }
    }

    private function toCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }

    private function fromCents(int $amount): string
    {
        return sprintf('%d.%02d', intdiv($amount, 100), $amount % 100);
    }
}
