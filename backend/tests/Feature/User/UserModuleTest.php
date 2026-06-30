<?php

namespace Tests\Feature\User;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Role;
use App\Models\ShippingAddress;
use App\Models\User;
use App\Notifications\Auth\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_and_update_profile_and_email_change_requires_reverification(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/profile')->assertOk()->assertJsonPath('data.email', $user->email);

        $this->putJson('/api/v1/profile', [
            'name' => 'Updated Customer',
            'email' => 'updated@example.com',
            'phone' => '+256700000002',
        ])->assertOk()->assertJsonPath('data.email_verified_at', null);

        $user->refresh();
        $this->assertSame('Updated Customer', $user->name);
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_user_can_change_password_and_other_tokens_are_revoked(): void
    {
        $user = User::factory()->create(['password' => 'OldSecurePassword123!']);
        $currentToken = $user->createToken('current')->plainTextToken;
        $user->createToken('other');

        $this->withToken($currentToken)->putJson('/api/v1/profile/password', [
            'current_password' => 'OldSecurePassword123!',
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ])->assertOk();

        $this->assertTrue(Hash::check('NewSecurePassword123!', $user->fresh()->password));
        $this->assertCount(1, $user->tokens);
    }

    public function test_shipping_addresses_enforce_ownership_and_one_default(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $first = $this->postJson('/api/v1/shipping-addresses', $this->addressPayload())
            ->assertCreated()->assertJsonPath('data.is_default', true)->json('data.id');

        $second = $this->postJson('/api/v1/shipping-addresses', [
            ...$this->addressPayload(),
            'label' => 'Office',
            'is_default' => true,
        ])->assertCreated()->json('data.id');

        $this->assertDatabaseHas('shipping_addresses', ['id' => $first, 'is_default' => false]);
        $this->assertDatabaseHas('shipping_addresses', ['id' => $second, 'is_default' => true]);

        $foreignAddress = ShippingAddress::factory()->create();
        $this->getJson("/api/v1/shipping-addresses/{$foreignAddress->id}")->assertForbidden();
    }

    public function test_deleting_default_address_promotes_another_address(): void
    {
        $user = User::factory()->create();
        $default = ShippingAddress::factory()->for($user)->create(['is_default' => true]);
        $remaining = ShippingAddress::factory()->for($user)->create(['is_default' => false]);
        Sanctum::actingAs($user);

        $this->deleteJson("/api/v1/shipping-addresses/{$default->id}")->assertNoContent();

        $this->assertTrue($remaining->fresh()->is_default);
    }

    public function test_customer_can_only_view_their_own_order_history(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();
        OrderItem::factory()->for($order)->create();
        $foreignOrder = Order::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/orders')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson("/api/v1/orders/{$order->id}")->assertOk()->assertJsonCount(1, 'data.items');
        $this->getJson("/api/v1/orders/{$foreignOrder->id}")->assertForbidden();
    }

    public function test_admin_can_manage_customers_but_customer_cannot_access_admin_users(): void
    {
        $adminRole = Role::factory()->create(['slug' => 'admin']);
        $customerRole = Role::factory()->create(['slug' => 'customer']);
        $admin = User::factory()->for($adminRole)->create();
        $customer = User::factory()->for($customerRole)->create();
        $customer->createToken('customer device');

        Sanctum::actingAs($admin);
        $this->getJson('/api/v1/admin/users')->assertOk();
        $this->patchJson("/api/v1/admin/users/{$customer->id}/status", ['status' => 'inactive'])
            ->assertOk()->assertJsonPath('data.status', 'inactive');
        $this->assertCount(0, $customer->tokens);

        Sanctum::actingAs($customer);
        $this->getJson('/api/v1/admin/users')->assertForbidden();
    }

    private function addressPayload(): array
    {
        return [
            'label' => 'Home',
            'first_name' => 'Jane',
            'last_name' => 'Customer',
            'phone' => '+256700000003',
            'country' => 'Uganda',
            'state' => 'Central',
            'city' => 'Kampala',
            'address_line_1' => '1 Kitchen Street',
            'postal_code' => '00000',
        ];
    }
}
