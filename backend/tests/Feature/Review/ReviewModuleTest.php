<?php

namespace Tests\Feature\Review;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReviewModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_with_delivered_purchase_can_submit_one_review(): void
    {
        [$user, $product] = $this->deliveredPurchase();
        Sanctum::actingAs($user);

        $this->postJson("/api/v1/products/{$product->id}/reviews", $this->reviewPayload())
            ->assertCreated()
            ->assertJsonPath('data.rating', 5);

        $this->postJson("/api/v1/products/{$product->id}/reviews", $this->reviewPayload())
            ->assertUnprocessable()->assertJsonValidationErrors('product');

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'is_approved' => false,
        ]);
    }

    public function test_customer_without_delivered_purchase_cannot_review(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson("/api/v1/products/{$product->id}/reviews", $this->reviewPayload())
            ->assertUnprocessable()->assertJsonValidationErrors('product');
    }

    public function test_public_listing_contains_only_approved_reviews_and_summary(): void
    {
        $product = Product::factory()->create(['slug' => 'reviewed-pan']);
        Review::factory()->for($product)->create(['rating' => 5, 'is_approved' => true]);
        Review::factory()->for($product)->create(['rating' => 1, 'is_approved' => false]);

        $this->getJson('/api/v1/products/reviewed-pan/reviews')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('summary.average_rating', 5)
            ->assertJsonPath('summary.reviews_count', 1);
    }

    public function test_owner_can_edit_and_delete_review_but_other_customer_cannot(): void
    {
        $owner = User::factory()->create();
        $review = Review::factory()->for($owner)->create(['is_approved' => true]);
        $other = User::factory()->create();

        Sanctum::actingAs($other);
        $this->putJson("/api/v1/reviews/{$review->id}", ['rating' => 3])->assertForbidden();

        Sanctum::actingAs($owner);
        $this->putJson("/api/v1/reviews/{$review->id}", ['rating' => 4])
            ->assertOk()->assertJsonPath('data.rating', 4);
        $this->assertFalse($review->fresh()->is_approved);

        $this->deleteJson("/api/v1/reviews/{$review->id}")->assertNoContent();
        $this->assertSoftDeleted('reviews', ['id' => $review->id]);
    }

    public function test_admin_can_moderate_review_and_customer_cannot_access_queue(): void
    {
        $admin = User::factory()->for(Role::factory()->create(['slug' => 'admin']))->create();
        $customer = User::factory()->create();
        $review = Review::factory()->create(['is_approved' => false]);

        Sanctum::actingAs($customer);
        $this->getJson('/api/v1/admin/reviews')->assertForbidden();

        Sanctum::actingAs($admin);
        $this->getJson('/api/v1/admin/reviews?is_approved=0')->assertOk();
        $this->patchJson("/api/v1/admin/reviews/{$review->id}/moderation", ['is_approved' => true])
            ->assertOk()->assertJsonPath('data.is_approved', true);
    }

    private function deliveredPurchase(): array
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->for($user)->create(['status' => 'delivered']);
        OrderItem::factory()->for($order)->for($product)->create();

        return [$user, $product];
    }

    private function reviewPayload(): array
    {
        return [
            'rating' => 5,
            'title' => 'Excellent cookware',
            'comment' => 'This product performed very well in my kitchen.',
        ];
    }
}
