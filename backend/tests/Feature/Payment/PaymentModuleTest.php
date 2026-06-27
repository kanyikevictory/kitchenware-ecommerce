<?php

namespace Tests\Feature\Payment;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymentModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_prepare_cash_on_delivery_for_owned_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create(['grand_total' => '250000.00', 'status' => 'pending']);
        Sanctum::actingAs($user);

        $this->postJson("/api/v1/orders/{$order->id}/payments", ['method' => 'cash_on_delivery'])
            ->assertCreated()
            ->assertJsonPath('data.method', 'cash_on_delivery')
            ->assertJsonPath('data.amount', '250000.00')
            ->assertJsonPath('data.currency', 'UGX')
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'method' => 'cash_on_delivery']);
    }

    public function test_duplicate_active_cash_on_delivery_attempt_is_rejected(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();
        Payment::factory()->for($order)->create(['method' => 'cash_on_delivery', 'status' => 'pending']);
        Sanctum::actingAs($user);

        $this->postJson("/api/v1/orders/{$order->id}/payments", ['method' => 'cash_on_delivery'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('method');
    }

    public function test_mobile_money_requires_phone_and_is_marked_unsubmitted_until_provider_is_configured(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();
        Sanctum::actingAs($user);

        $this->postJson("/api/v1/orders/{$order->id}/payments", ['method' => 'mobile_money'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('phone');

        $response = $this->postJson("/api/v1/orders/{$order->id}/payments", [
            'method' => 'mobile_money',
            'phone' => '+256700123456',
        ])->assertAccepted()
            ->assertJsonPath('data.status', 'pending_configuration')
            ->assertJsonPath('data.meta.provider_status', 'not_submitted');

        $payment = Payment::query()->findOrFail($response->json('data.id'));
        $this->assertStringNotContainsString('+256700123456', json_encode($payment->meta));
        $this->assertStringStartsWith('MM-', $payment->transaction_id);
    }

    public function test_customer_cannot_access_another_customers_payments(): void
    {
        $customer = User::factory()->create();
        $foreignOrder = Order::factory()->create();
        Sanctum::actingAs($customer);

        $this->getJson("/api/v1/orders/{$foreignOrder->id}/payments")->assertForbidden();
        $this->postJson("/api/v1/orders/{$foreignOrder->id}/payments", ['method' => 'cash_on_delivery'])
            ->assertForbidden();
    }

    public function test_cancelled_or_already_paid_order_rejects_new_payment(): void
    {
        $user = User::factory()->create();
        $cancelled = Order::factory()->for($user)->create(['status' => 'cancelled']);
        $paid = Order::factory()->for($user)->create(['status' => 'confirmed']);
        Payment::factory()->for($paid)->create(['status' => 'completed']);
        Sanctum::actingAs($user);

        $this->postJson("/api/v1/orders/{$cancelled->id}/payments", ['method' => 'cash_on_delivery'])
            ->assertUnprocessable()->assertJsonValidationErrors('order');
        $this->postJson("/api/v1/orders/{$paid->id}/payments", ['method' => 'cash_on_delivery'])
            ->assertUnprocessable()->assertJsonValidationErrors('order');
    }

    public function test_admin_can_complete_cod_only_after_delivery(): void
    {
        $admin = User::factory()->for(Role::factory()->create(['slug' => 'admin']))->create();
        $order = Order::factory()->create(['status' => 'shipped']);
        $payment = Payment::factory()->for($order)->create(['method' => 'cash_on_delivery', 'status' => 'pending']);
        Sanctum::actingAs($admin);

        $this->patchJson("/api/v1/admin/payments/{$payment->id}/cash-on-delivery", ['status' => 'completed'])
            ->assertUnprocessable()->assertJsonValidationErrors('status');

        $order->update(['status' => 'delivered']);
        $this->patchJson("/api/v1/admin/payments/{$payment->id}/cash-on-delivery", ['status' => 'completed'])
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');

        $this->assertNotNull($payment->fresh()->paid_at);
    }
}
