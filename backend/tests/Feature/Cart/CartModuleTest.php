<?php

namespace Tests\Feature\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CartModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_adding_same_product_merges_quantity_and_calculates_totals(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->for(Category::factory())->create([
            'price' => 100,
            'discount_price' => 80,
            'stock_quantity' => 10,
            'status' => 'active',
        ]);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 1])->assertOk();
        $response = $this->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 2])
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.quantity', 3)
            ->assertJsonPath('data.quantity_total', 3)
            ->assertJsonPath('data.subtotal', '300.00')
            ->assertJsonPath('data.discount_total', '60.00')
            ->assertJsonPath('data.grand_total', '240.00');

        $this->assertSame(1, $response->json('data.items_count'));
        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_quantity_cannot_exceed_stock_and_failed_update_keeps_existing_quantity(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 3, 'status' => 'active']);
        $cart = Cart::factory()->for($user)->create();
        $item = CartItem::factory()->for($cart)->for($product)->create(['quantity' => 1]);
        Sanctum::actingAs($user);

        $this->patchJson("/api/v1/cart/items/{$item->id}", ['quantity' => 4])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('quantity');

        $this->assertSame(1, $item->fresh()->quantity);
    }

    public function test_user_cannot_update_or_remove_another_users_cart_item(): void
    {
        $user = User::factory()->create();
        $foreignItem = CartItem::factory()->create();
        Sanctum::actingAs($user);

        $this->patchJson("/api/v1/cart/items/{$foreignItem->id}", ['quantity' => 2])->assertForbidden();
        $this->deleteJson("/api/v1/cart/items/{$foreignItem->id}")->assertForbidden();
    }

    public function test_item_can_be_removed_and_cart_can_be_cleared(): void
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->for($user)->create();
        $first = CartItem::factory()->for($cart)->create();
        CartItem::factory()->for($cart)->create();
        Sanctum::actingAs($user);

        $this->deleteJson("/api/v1/cart/items/{$first->id}")
            ->assertOk()->assertJsonPath('data.items_count', 1);

        $this->deleteJson('/api/v1/cart')
            ->assertOk()
            ->assertJsonCount(0, 'data.items')
            ->assertJsonPath('data.grand_total', '0.00');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_inactive_or_out_of_stock_product_cannot_be_added(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $inactive = Product::factory()->create(['status' => 'inactive', 'stock_quantity' => 5]);
        $outOfStock = Product::factory()->create(['status' => 'active', 'stock_quantity' => 0]);

        $this->postJson('/api/v1/cart/items', ['product_id' => $inactive->id, 'quantity' => 1])
            ->assertUnprocessable()->assertJsonValidationErrors('product_id');
        $this->postJson('/api/v1/cart/items', ['product_id' => $outOfStock->id, 'quantity' => 1])
            ->assertUnprocessable()->assertJsonValidationErrors('quantity');
    }

    public function test_soft_deleted_product_remains_renderable_as_unavailable_in_existing_cart(): void
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->for($user)->create();
        $product = Product::factory()->create();
        CartItem::factory()->for($cart)->for($product)->create();
        $product->delete();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/cart')
            ->assertOk()
            ->assertJsonPath('data.items.0.product.id', $product->id)
            ->assertJsonPath('data.items.0.product.is_available', false);
    }
}
