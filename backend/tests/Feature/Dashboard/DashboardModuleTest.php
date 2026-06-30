<?php

namespace Tests\Feature\Dashboard;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_receives_dashboard_metrics(): void
    {
        $admin = $this->userWithRole('admin');
        $customer = $this->userWithRole('customer');
        $category = Category::factory()->create(['is_active' => true]);
        $bestSeller = Product::factory()->for($category)->create(['status' => 'active', 'stock_quantity' => 4]);
        Product::factory()->for($category)->create(['status' => 'active', 'stock_quantity' => 0]);
        Product::factory()->for($category)->create(['status' => 'inactive', 'stock_quantity' => 1]);

        $delivered = Order::factory()->for($customer)->create(['status' => 'delivered']);
        $confirmed = Order::factory()->for($customer)->create(['status' => 'confirmed']);
        $cancelled = Order::factory()->for($customer)->create(['status' => 'cancelled']);
        OrderItem::factory()->for($delivered)->for($bestSeller)->create([
            'product_name' => $bestSeller->name,
            'quantity' => 3,
            'total_price' => 90,
        ]);
        OrderItem::factory()->for($confirmed)->for($bestSeller)->create([
            'product_name' => $bestSeller->name,
            'quantity' => 2,
            'total_price' => 60,
        ]);
        Payment::factory()->for($delivered)->create([
            'status' => 'completed',
            'amount' => 100,
            'paid_at' => now()->startOfYear()->addDays(3),
        ]);
        Payment::factory()->for($confirmed)->create([
            'status' => 'completed',
            'amount' => 150,
            'paid_at' => now()->startOfYear()->addMonths(2),
        ]);
        Payment::factory()->for($cancelled)->create(['status' => 'pending', 'amount' => 999, 'paid_at' => null]);
        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/admin/dashboard?year='.now()->year)
            ->assertOk()
            ->assertJsonPath('data.summary.total_sales', 1)
            ->assertJsonPath('data.summary.revenue', '250.00')
            ->assertJsonPath('data.summary.orders', 3)
            ->assertJsonPath('data.summary.customers', 1)
            ->assertJsonPath('data.summary.products', 2)
            ->assertJsonPath('data.summary.categories', 1)
            ->assertJsonPath('data.summary.low_stock_products', 1)
            ->assertJsonPath('data.summary.out_of_stock_products', 1)
            ->assertJsonPath('data.order_statuses.delivered', 1)
            ->assertJsonPath('data.order_statuses.cancelled', 1)
            ->assertJsonPath('data.best_sellers.0.product_id', $bestSeller->id)
            ->assertJsonPath('data.best_sellers.0.quantity_sold', 5)
            ->assertJsonPath('data.best_sellers.0.revenue', '150.00')
            ->assertJsonPath('data.monthly_sales.0.revenue', '100.00')
            ->assertJsonPath('data.monthly_sales.2.revenue', '150.00');
    }

    public function test_customer_cannot_access_admin_dashboard(): void
    {
        Sanctum::actingAs($this->userWithRole('customer'));

        $this->getJson('/api/v1/admin/dashboard')->assertForbidden();
    }

    public function test_monthly_sales_respect_requested_year_and_year_is_validated(): void
    {
        $admin = $this->userWithRole('super-admin');
        $order = Order::factory()->create();
        Payment::factory()->for($order)->create([
            'status' => 'completed',
            'amount' => 75,
            'paid_at' => now()->subYear()->startOfYear(),
        ]);
        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/admin/dashboard?year='.(now()->year - 1))
            ->assertOk()->assertJsonPath('data.monthly_sales.0.revenue', '75.00');
        $this->getJson('/api/v1/admin/dashboard?year=1999')
            ->assertUnprocessable()->assertJsonValidationErrors('year');
    }

    private function userWithRole(string $slug): User
    {
        return User::factory()->for(Role::factory()->create(['slug' => $slug]))->create();
    }
}
