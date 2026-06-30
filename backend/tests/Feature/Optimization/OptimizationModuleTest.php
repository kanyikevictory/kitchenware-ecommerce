<?php

namespace Tests\Feature\Optimization;

use App\Models\Category;
use App\Models\Role;
use App\Models\User;
use App\Services\CacheVersionService;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OptimizationModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_metrics_are_cached_until_version_is_bumped(): void
    {
        Cache::flush();
        $service = app(DashboardService::class);

        DB::enableQueryLog();
        $service->metrics(now()->year);
        $firstQueryCount = count(DB::getQueryLog());

        DB::flushQueryLog();
        $service->metrics(now()->year);
        $cachedQueryCount = count(DB::getQueryLog());

        app(CacheVersionService::class)->bump('dashboard');
        DB::flushQueryLog();
        $service->metrics(now()->year);
        $invalidatedQueryCount = count(DB::getQueryLog());

        $this->assertGreaterThan(0, $firstQueryCount);
        $this->assertSame(0, $cachedQueryCount);
        $this->assertGreaterThan(0, $invalidatedQueryCount);
    }

    public function test_product_mutation_invalidates_dashboard_cache(): void
    {
        Cache::flush();
        $admin = $this->admin();
        $category = Category::factory()->create();
        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/admin/dashboard')->assertJsonPath('data.summary.products', 0);

        $this->postJson('/api/v1/admin/products', [
            'category_id' => $category->id,
            'name' => 'Cached Dashboard Pan',
            'price' => 100,
            'sku' => 'CACHE-PAN-001',
            'stock_quantity' => 5,
            'status' => 'active',
        ])->assertCreated();

        $this->getJson('/api/v1/admin/dashboard')->assertJsonPath('data.summary.products', 1);
    }

    public function test_permission_relationship_is_reused_after_first_check(): void
    {
        $admin = $this->admin();
        $admin->unsetRelation('role');
        DB::enableQueryLog();

        $this->assertTrue($admin->hasPermission('products.manage'));
        $firstQueryCount = count(DB::getQueryLog());
        DB::flushQueryLog();
        $this->assertTrue($admin->hasPermission('orders.manage'));

        $this->assertGreaterThan(0, $firstQueryCount);
        $this->assertSame(0, count(DB::getQueryLog()));
    }

    public function test_checkout_has_a_dedicated_rate_limit(): void
    {
        Sanctum::actingAs(User::factory()->create());

        for ($attempt = 1; $attempt <= 10; $attempt++) {
            $this->postJson('/api/v1/checkout', [])->assertUnprocessable();
        }

        $this->postJson('/api/v1/checkout', [])->assertTooManyRequests();
    }

    public function test_database_queue_dispatches_after_commit(): void
    {
        $this->assertTrue(config('queue.connections.database.after_commit'));
    }

    private function admin(): User
    {
        return User::factory()->for(Role::factory()->create(['slug' => 'admin']))->create();
    }
}
