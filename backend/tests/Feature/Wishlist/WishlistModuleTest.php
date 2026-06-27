<?php

namespace Tests\Feature\Wishlist;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WishlistModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewing_wishlist_creates_missing_wishlist_and_returns_empty_items(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/wishlist')
            ->assertOk()
            ->assertJsonCount(0, 'data.items')
            ->assertJsonPath('data.items_count', 0);

        $this->assertDatabaseHas('wishlists', ['user_id' => $user->id, 'status' => 'active']);
    }

    public function test_adding_same_product_twice_is_idempotent(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['status' => 'active']);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/wishlist/items', ['product_id' => $product->id])->assertOk();
        $this->postJson('/api/v1/wishlist/items', ['product_id' => $product->id])
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.product.id', $product->id);

        $this->assertDatabaseCount('wishlist_items', 1);
    }

    public function test_out_of_stock_product_can_be_added_but_inactive_product_cannot(): void
    {
        $user = User::factory()->create();
        $outOfStock = Product::factory()->create(['status' => 'active', 'stock_quantity' => 0]);
        $inactive = Product::factory()->create(['status' => 'inactive']);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/wishlist/items', ['product_id' => $outOfStock->id])
            ->assertOk()
            ->assertJsonPath('data.items.0.product.in_stock', false)
            ->assertJsonPath('data.items.0.product.is_available', true);

        $this->postJson('/api/v1/wishlist/items', ['product_id' => $inactive->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('product_id');
    }

    public function test_user_can_remove_own_item_but_not_another_users_item(): void
    {
        $user = User::factory()->create();
        $wishlist = Wishlist::factory()->for($user)->create();
        $ownItem = WishlistItem::factory()->for($wishlist)->create();
        $foreignItem = WishlistItem::factory()->create();
        Sanctum::actingAs($user);

        $this->deleteJson("/api/v1/wishlist/items/{$foreignItem->id}")->assertForbidden();
        $this->deleteJson("/api/v1/wishlist/items/{$ownItem->id}")
            ->assertOk()
            ->assertJsonCount(0, 'data.items');

        $this->assertDatabaseMissing('wishlist_items', ['id' => $ownItem->id]);
    }

    public function test_soft_deleted_product_remains_visible_as_unavailable(): void
    {
        $user = User::factory()->create();
        $wishlist = Wishlist::factory()->for($user)->create();
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create();
        WishlistItem::factory()->for($wishlist)->for($product)->create();
        $product->delete();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/wishlist')
            ->assertOk()
            ->assertJsonPath('data.items.0.product.id', $product->id)
            ->assertJsonPath('data.items.0.product.is_available', false);
    }
}
