<?php

namespace Tests\Feature\Notification;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Role;
use App\Models\ShippingAddress;
use App\Models\User;
use App\Notifications\Admin\NewOrderNotification;
use App\Notifications\Admin\NewUserNotification;
use App\Notifications\Admin\StockAlertNotification;
use App\Notifications\Auth\ResetPasswordNotification;
use App\Notifications\Auth\VerifyEmailNotification;
use App\Notifications\Customer\OrderConfirmationNotification;
use App\Notifications\Customer\OrderStatusNotification;
use App\Notifications\Customer\PaymentConfirmationNotification;
use App\Notifications\Customer\WelcomeNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_queues_welcome_verification_and_admin_notifications(): void
    {
        Notification::fake();
        Role::factory()->create(['slug' => 'customer']);
        $admin = $this->userWithRole('admin');

        $this->postJson('/api/v1/auth/register', [
            'name' => 'New Customer',
            'email' => 'new@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'device_name' => 'React storefront',
        ])->assertCreated();

        $customer = User::query()->where('email', 'new@example.com')->firstOrFail();
        Notification::assertSentTo($customer, WelcomeNotification::class);
        Notification::assertSentTo($customer, VerifyEmailNotification::class, $this->queuedNotification());
        Notification::assertSentTo($admin, NewUserNotification::class);
    }

    public function test_password_reset_notification_is_queued(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->postJson('/api/v1/auth/forgot-password', ['email' => $user->email])->assertOk();

        Notification::assertSentTo($user, ResetPasswordNotification::class, $this->queuedNotification());
    }

    public function test_checkout_queues_customer_admin_and_stock_notifications(): void
    {
        Notification::fake();
        $admin = $this->userWithRole('admin');
        $user = User::factory()->create();
        $address = ShippingAddress::factory()->for($user)->create();
        $cart = Cart::factory()->for($user)->create();
        $product = Product::factory()->create(['stock_quantity' => 6, 'status' => 'active']);
        CartItem::factory()->for($cart)->for($product)->create(['quantity' => 2]);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/checkout', ['shipping_address_id' => $address->id])->assertCreated();

        Notification::assertSentTo($user, OrderConfirmationNotification::class, $this->queuedNotification());
        Notification::assertSentTo($admin, NewOrderNotification::class);
        Notification::assertSentTo($admin, StockAlertNotification::class, function ($notification): bool {
            return $notification->product->stock_quantity === 4;
        });
    }

    public function test_completed_cod_payment_queues_confirmation(): void
    {
        Notification::fake();
        $admin = $this->userWithRole('admin');
        $customer = User::factory()->create();
        $order = Order::factory()->for($customer)->create(['status' => 'delivered']);
        $payment = Payment::factory()->for($order)->create([
            'method' => 'cash_on_delivery',
            'status' => 'pending',
        ]);
        Sanctum::actingAs($admin);

        $this->patchJson("/api/v1/admin/payments/{$payment->id}/cash-on-delivery", ['status' => 'completed'])
            ->assertOk();

        Notification::assertSentTo($customer, PaymentConfirmationNotification::class, $this->queuedNotification());
    }

    public function test_shipped_delivered_and_cancelled_transitions_queue_customer_emails(): void
    {
        Notification::fake();
        $admin = $this->userWithRole('admin');
        $customer = User::factory()->create();
        $order = Order::factory()->for($customer)->create(['status' => 'processing']);
        Sanctum::actingAs($admin);

        $this->patchJson("/api/v1/admin/orders/{$order->id}/status", ['status' => 'shipped'])->assertOk();
        $this->patchJson("/api/v1/admin/orders/{$order->id}/status", ['status' => 'delivered'])->assertOk();

        $cancelledOrder = Order::factory()->for($customer)->create(['status' => 'pending']);
        $this->patchJson("/api/v1/admin/orders/{$cancelledOrder->id}/status", ['status' => 'cancelled'])->assertOk();

        foreach (['shipped', 'delivered', 'cancelled'] as $status) {
            Notification::assertSentTo($customer, OrderStatusNotification::class, function ($notification) use ($status): bool {
                return $notification->status === $status
                    && $notification instanceof ShouldQueue
                    && $notification->queue === 'notifications';
            });
        }
    }

    private function queuedNotification(): callable
    {
        return fn ($notification): bool => $notification instanceof ShouldQueue
            && $notification->queue === 'notifications'
            && $notification->afterCommit === true;
    }

    private function userWithRole(string $slug): User
    {
        return User::factory()->for(Role::factory()->create(['slug' => $slug]))->create();
    }
}
