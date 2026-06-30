<?php

namespace Tests\Feature\Coupon;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Role;
use App\Models\ShippingAddress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CouponModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_percentage_coupon_is_previewed_against_cart_total(): void
    {
        [$user] = $this->cartWithTotal('200.00');
        Coupon::factory()->create(['code' => 'SAVE10', 'type' => 'percentage', 'value' => 10]);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/coupons/validate', ['coupon_code' => 'save10'])
            ->assertOk()
            ->assertJsonPath('data.discount_amount', '20.00')
            ->assertJsonPath('data.total_after_discount', '180.00');
    }

    public function test_expired_minimum_and_exhausted_coupons_are_rejected(): void
    {
        [$user] = $this->cartWithTotal('100.00');
        Sanctum::actingAs($user);

        $cases = [
            ['code' => 'EXPIRED', 'expires_at' => now()->subMinute()],
            ['code' => 'MINIMUM', 'minimum_order_amount' => 200],
            ['code' => 'EXHAUSTED', 'usage_limit' => 1, 'usage_count' => 1],
        ];

        foreach ($cases as $attributes) {
            $coupon = Coupon::factory()->create($attributes);
            $this->postJson('/api/v1/coupons/validate', ['coupon_code' => $coupon->code])
                ->assertUnprocessable()->assertJsonValidationErrors('coupon_code');
        }
    }

    public function test_checkout_applies_coupon_after_product_discount_and_records_usage(): void
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
        CartItem::factory()->for($cart)->for($product)->create(['quantity' => 2]);
        $coupon = Coupon::factory()->create(['code' => 'EXTRA10', 'type' => 'percentage', 'value' => 10]);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/checkout', [
            'shipping_address_id' => $address->id,
            'coupon_code' => 'extra10',
        ])->assertCreated()
            ->assertJsonPath('data.coupon.code', 'EXTRA10')
            ->assertJsonPath('data.subtotal', '200.00')
            ->assertJsonPath('data.discount_total', '56.00')
            ->assertJsonPath('data.grand_total', '144.00');

        $this->assertSame(1, $coupon->fresh()->usage_count);
    }

    public function test_fixed_coupon_never_reduces_total_below_zero(): void
    {
        [$user] = $this->cartWithTotal('50.00');
        Coupon::factory()->create(['code' => 'BIGFIXED', 'type' => 'fixed', 'value' => 500]);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/coupons/validate', ['coupon_code' => 'BIGFIXED'])
            ->assertOk()
            ->assertJsonPath('data.discount_amount', '50.00')
            ->assertJsonPath('data.total_after_discount', '0.00');
    }

    public function test_admin_can_create_update_and_delete_unused_coupon(): void
    {
        $admin = User::factory()->for(Role::factory()->create(['slug' => 'admin']))->create();
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/admin/coupons', [
            'code' => ' launch20 ',
            'type' => 'percentage',
            'value' => 20,
            'expires_at' => now()->addMonth()->toISOString(),
        ])->assertCreated()->assertJsonPath('data.code', 'LAUNCH20');

        $id = $response->json('data.id');
        $this->patchJson("/api/v1/admin/coupons/{$id}", ['is_active' => false])
            ->assertOk()->assertJsonPath('data.is_active', false);
        $this->deleteJson("/api/v1/admin/coupons/{$id}")->assertNoContent();
    }

    public function test_customer_cannot_manage_coupons_and_percentage_cannot_exceed_one_hundred(): void
    {
        $customer = User::factory()->create();
        Sanctum::actingAs($customer);
        $this->getJson('/api/v1/admin/coupons')->assertForbidden();

        $admin = User::factory()->for(Role::factory()->create(['slug' => 'admin']))->create();
        Sanctum::actingAs($admin);
        $this->postJson('/api/v1/admin/coupons', [
            'code' => 'INVALID',
            'type' => 'percentage',
            'value' => 101,
        ])->assertUnprocessable()->assertJsonValidationErrors('value');
    }

    private function cartWithTotal(string $total): array
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->for($user)->create(['grand_total' => $total]);
        CartItem::factory()->for($cart)->create();

        return [$user, $cart];
    }
}
