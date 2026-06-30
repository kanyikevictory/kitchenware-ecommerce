<?php

namespace Tests\Feature\Authorization;

use App\Http\Middleware\EnsureRole;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AuthorizationModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_permission_seeder_assigns_all_admin_permissions_and_none_to_customer(): void
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class]);

        $admin = Role::query()->where('slug', 'admin')->firstOrFail();
        $superAdmin = Role::query()->where('slug', 'super-admin')->firstOrFail();
        $customer = Role::query()->where('slug', 'customer')->firstOrFail();

        $this->assertSame(count(Permission::DEFINITIONS), $admin->permissions()->count());
        $this->assertSame(count(Permission::DEFINITIONS), $superAdmin->permissions()->count());
        $this->assertSame(0, $customer->permissions()->count());
    }

    public function test_admin_route_and_policy_require_their_permissions(): void
    {
        $admin = $this->userWithRole('admin');
        $dashboardPermission = Permission::query()->where('slug', 'dashboard.view')->firstOrFail();
        $admin->role->permissions()->detach($dashboardPermission);
        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/admin/dashboard')->assertForbidden();

        $admin->role->permissions()->attach($dashboardPermission);
        $this->getJson('/api/v1/admin/dashboard')->assertOk();
    }

    public function test_custom_role_can_access_admin_endpoint_when_permissions_are_assigned(): void
    {
        $role = Role::factory()->create(['slug' => 'analyst']);
        $permissions = collect(['admin.access', 'dashboard.view'])->map(
            fn (string $slug) => Permission::factory()->create(['slug' => $slug])->id,
        );
        $role->permissions()->attach($permissions);
        $analyst = User::factory()->for($role)->create();
        Sanctum::actingAs($analyst);

        $this->getJson('/api/v1/admin/dashboard')->assertOk();
    }

    public function test_super_admin_bypasses_database_permission_assignments(): void
    {
        $role = Role::query()->create(['name' => 'Super Admin', 'slug' => 'super-admin']);
        $superAdmin = User::factory()->for($role)->create();
        Sanctum::actingAs($superAdmin);

        $this->assertSame(0, $role->permissions()->count());
        $this->getJson('/api/v1/admin/dashboard')->assertOk();
    }

    public function test_customer_is_blocked_by_permission_middleware(): void
    {
        Sanctum::actingAs($this->userWithRole('customer'));

        $this->getJson('/api/v1/admin/dashboard')->assertForbidden();
    }

    public function test_authenticated_user_response_contains_permissions_and_role_middleware_works(): void
    {
        $admin = $this->userWithRole('admin');
        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.role.slug', 'admin')
            ->assertJsonFragment(['admin.access']);

        $request = Request::create('/internal-test');
        $request->setUserResolver(fn () => $admin);
        $response = (new EnsureRole)->handle($request, fn () => new Response(status: 204), 'admin');

        $this->assertSame(204, $response->getStatusCode());
    }

    private function userWithRole(string $slug): User
    {
        return User::factory()->for(Role::factory()->create(['slug' => $slug]))->create();
    }
}
