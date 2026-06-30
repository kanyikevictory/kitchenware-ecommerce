<?php

namespace Tests\Feature\Security;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ApiAccessSecurityTest extends TestCase
{
    use RefreshDatabase;

    public static function protectedEndpoints(): array
    {
        return [
            ['getJson', '/api/v1/me'],
            ['getJson', '/api/v1/profile'],
            ['getJson', '/api/v1/cart'],
            ['getJson', '/api/v1/wishlist'],
            ['getJson', '/api/v1/orders'],
            ['getJson', '/api/v1/admin/dashboard'],
        ];
    }

    #[DataProvider('protectedEndpoints')]
    public function test_protected_endpoints_reject_unauthenticated_requests(string $method, string $uri): void
    {
        $this->{$method}($uri)->assertUnauthorized();
    }

    public function test_unverified_customer_cannot_access_verified_commerce_routes(): void
    {
        $user = User::factory()->unverified()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/cart')->assertForbidden();
        $this->getJson('/api/v1/wishlist')->assertForbidden();
        $this->postJson('/api/v1/checkout', [])->assertForbidden();
    }

    public function test_customer_cannot_cross_admin_permission_boundary(): void
    {
        $role = Role::factory()->create(['slug' => 'customer']);
        Sanctum::actingAs(User::factory()->for($role)->create());

        $this->getJson('/api/v1/admin/users')->assertForbidden();
        $this->getJson('/api/v1/admin/products')->assertForbidden();
        $this->getJson('/api/v1/admin/orders')->assertForbidden();
    }
}
