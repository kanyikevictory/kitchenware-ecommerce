<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CategoryModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_category_listing_is_searchable_paginated_and_hides_inactive_categories(): void
    {
        Category::factory()->create(['name' => 'Cookware', 'slug' => 'cookware']);
        Category::factory()->create(['name' => 'Knives', 'slug' => 'knives']);
        Category::factory()->create(['name' => 'Hidden Cookware', 'slug' => 'hidden-cookware', 'is_active' => false]);

        $this->getJson('/api/v1/categories?search=Cookware&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'cookware')
            ->assertJsonPath('meta.per_page', 1);

        $this->getJson('/api/v1/categories/hidden-cookware')->assertNotFound();
    }

    public function test_public_can_view_active_category_by_slug_with_active_children(): void
    {
        $parent = Category::factory()->create(['slug' => 'cookware']);
        Category::factory()->for($parent, 'parent')->create(['slug' => 'pans']);
        Category::factory()->for($parent, 'parent')->create(['slug' => 'hidden', 'is_active' => false]);

        $this->getJson('/api/v1/categories/cookware')
            ->assertOk()
            ->assertJsonCount(1, 'data.children')
            ->assertJsonPath('data.children.0.slug', 'pans');
    }

    public function test_admin_can_create_category_with_image_and_collision_safe_slug(): void
    {
        Storage::fake('public');
        $admin = $this->userWithRole('admin');
        Category::factory()->create(['name' => 'Cookware', 'slug' => 'cookware']);
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/admin/categories', [
            'name' => 'Cookware',
            'description' => 'Premium cookware.',
            'image' => UploadedFile::fake()->image('cookware.jpg', 600, 600),
            'sort_order' => 1,
            'is_active' => true,
        ])->assertCreated()->assertJsonPath('data.slug', 'cookware-2');

        $category = Category::query()->findOrFail($response->json('data.id'));
        Storage::disk('public')->assertExists($category->image_path);
    }

    public function test_updating_image_removes_old_file(): void
    {
        Storage::fake('public');
        $admin = $this->userWithRole('admin');
        $oldImage = UploadedFile::fake()->image('old.jpg', 600, 600)->store('categories', 'public');
        $category = Category::factory()->create(['image_path' => $oldImage]);
        Sanctum::actingAs($admin);

        $this->postJson("/api/v1/admin/categories/{$category->id}", [
            '_method' => 'PUT',
            'name' => 'Updated Category',
            'image' => UploadedFile::fake()->image('new.jpg', 600, 600),
        ])->assertOk();

        Storage::disk('public')->assertMissing($oldImage);
        Storage::disk('public')->assertExists($category->fresh()->image_path);
    }

    public function test_category_with_children_cannot_be_deleted(): void
    {
        $admin = $this->userWithRole('admin');
        $category = Category::factory()->create();
        Category::factory()->for($category, 'parent')->create();
        Sanctum::actingAs($admin);

        $this->deleteJson("/api/v1/admin/categories/{$category->id}")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('category');
    }

    public function test_customer_cannot_manage_categories(): void
    {
        $customer = $this->userWithRole('customer');
        Sanctum::actingAs($customer);

        $this->getJson('/api/v1/admin/categories')->assertForbidden();
        $this->postJson('/api/v1/admin/categories', ['name' => 'Forbidden'])->assertForbidden();
    }

    private function userWithRole(string $slug): User
    {
        $role = Role::factory()->create(['slug' => $slug]);

        return User::factory()->for($role)->create();
    }
}
