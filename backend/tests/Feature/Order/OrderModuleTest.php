<?php

namespace Tests\Feature\Order;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Role;
use App\Models\ShippingAddress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_creates_snapshots_reprices_items_reduces_stock_and_clears_cart(): void
    {
        $user = User::factory()->create();
        $address = ShippingAddress::factory()->for($user)->create();
        $cart = Cart::factory()->for($user)->create();
        $product = Product::factory()->create([
            'price' => 100,
            'discount_price' => 80,
            'stock_quantity' => 10,
            'status' => 'active',
        ]);
        CartItem::factory()->for($cart)->for($product)->create([
            'quantity' => 2,
            'unit_price' => 1,
            'discount_amount' => 0,
            'total_price' => 2,
        ]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/checkout', [
            'shipping_address_id' => $address->id,
            'notes' => 'Leave at reception.',
        ])->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.subtotal', '200.00')
            ->assertJsonPath('data.discount_total', '40.00')
            ->assertJsonPath('data.grand_total', '160.00')
            ->assertJsonPath('data.items.0.product_name', $product->name);

        $order = Order::query()->findOrFail($response->json('data.id'));
        $this->assertSame($address->address_line_1, $order->shipping_address_line_1);
        $this->assertSame(8, $product->fresh()->stock_quantity);
        $this->assertDatabaseMissing('cart_items', ['cart_id' => $cart->id]);
        $this->assertSame('0.00', $cart->fresh()->grand_total);
    }

    public function test_checkout_rejects_another_users_address(): void
    {
        $user = User::factory()->create();
        $foreignAddress = ShippingAddress::factory()->create();
        $cart = Cart::factory()->for($user)->create();
        CartItem::factory()->for($cart)->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/checkout', ['shipping_address_id' => $foreignAddress->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('shipping_address_id');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_insufficient_stock_rolls_back_checkout(): void
    {
        $user = User::factory()->create();
        $address = ShippingAddress::factory()->for($user)->create();
        $cart = Cart::factory()->for($user)->create();
        $product = Product::factory()->create(['stock_quantity' => 1, 'status' => 'active']);
        $item = CartItem::factory()->for($cart)->for($product)->create(['quantity' => 2]);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/checkout', ['shipping_address_id' => $address->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('cart');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('cart_items', ['id' => $item->id]);
        $this->assertSame(1, $product->fresh()->stock_quantity);
    }

    public function test_empty_cart_cannot_be_checked_out(): void
    {
        $user = User::factory()->create();
        $address = ShippingAddress::factory()->for($user)->create();
        Cart::factory()->for($user)->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/checkout', ['shipping_address_id' => $address->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('cart');
    }

    public function test_customer_can_cancel_pending_order_and_stock_is_restored_once(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 5]);
        $order = Order::factory()->for($user)->create(['status' => 'pending']);
        OrderItem::factory()->for($order)->for($product)->create(['quantity' => 2]);
        Sanctum::actingAs($user);

        $this->postJson("/api/v1/orders/{$order->id}/cancel")
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        $this->assertSame(7, $product->fresh()->stock_quantity);
        $this->postJson("/api/v1/orders/{$order->id}/cancel")->assertForbidden();
        $this->assertSame(7, $product->fresh()->stock_quantity);
    }

    public function test_admin_can_progress_order_but_invalid_transition_is_rejected(): void
    {
        $admin = User::factory()->for(Role::factory()->create(['slug' => 'admin']))->create();
        $order = Order::factory()->create(['status' => 'pending']);
        Sanctum::actingAs($admin);

        $this->patchJson("/api/v1/admin/orders/{$order->id}/status", ['status' => 'shipped'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');

        foreach (['confirmed', 'processing', 'shipped', 'delivered'] as $status) {
            $this->patchJson("/api/v1/admin/orders/{$order->id}/status", ['status' => $status])
                ->assertOk()
                ->assertJsonPath('data.status', $status);
        }

        $this->assertSame('delivered', $order->fresh()->status);
    }
}
