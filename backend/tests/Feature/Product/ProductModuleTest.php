<?php

namespace Tests\Feature\Product;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_catalogue_supports_search_filters_sorting_and_pagination(): void
    {
        $category = Category::factory()->create();
        Product::factory()->for($category)->create([
            'name' => 'Steel Frying Pan',
            'slug' => 'steel-frying-pan',
            'brand' => 'Kitchen Pro',
            'price' => 120,
            'stock_quantity' => 8,
            'is_featured' => true,
        ]);
        Product::factory()->for($category)->create(['name' => 'Hidden Pan', 'status' => 'draft']);

        $this->getJson("/api/v1/products?search=Frying&category_id={$category->id}&brand=Kitchen%20Pro&in_stock=1&featured=1&sort=price_asc&per_page=1")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'steel-frying-pan')
            ->assertJsonPath('meta.per_page', 1);

        $this->getJson('/api/v1/products/steel-frying-pan')->assertOk();
    }

    public function test_inactive_product_or_product_in_inactive_category_is_not_public(): void
    {
        $inactiveCategory = Category::factory()->create(['is_active' => false]);
        $product = Product::factory()->for($inactiveCategory)->create(['slug' => 'hidden-product']);
        $draft = Product::factory()->create(['slug' => 'draft-product', 'status' => 'draft']);

        $this->getJson("/api/v1/products/{$product->slug}")->assertNotFound();
        $this->getJson("/api/v1/products/{$draft->slug}")->assertNotFound();
    }

    public function test_admin_can_create_product_with_optimized_webp_images(): void
    {
        Storage::fake('public');
        Sanctum::actingAs($this->userWithRole('admin'));
        $category = Category::factory()->create();

        $response = $this->postJson('/api/v1/admin/products', [
            ...$this->productPayload($category),
            'images' => [
                UploadedFile::fake()->image('pan.jpg', 2000, 1200),
                UploadedFile::fake()->image('pan-side.png', 800, 800),
            ],
        ])->assertCreated()->assertJsonCount(2, 'data.images');

        $product = Product::query()->findOrFail($response->json('data.id'));
        $images = $product->images()->orderBy('sort_order')->get();

        $this->assertTrue($images->first()->is_primary);
        $this->assertStringEndsWith('.webp', $images->first()->path);
        Storage::disk('public')->assertExists($images->first()->path);

        [$width, $height] = getimagesize(Storage::disk('public')->path($images->first()->path));
        $this->assertLessThanOrEqual(1600, max($width, $height));
    }

    public function test_slug_is_collision_safe_and_product_is_soft_deleted(): void
    {
        Sanctum::actingAs($this->userWithRole('admin'));
        $category = Category::factory()->create();
        Product::factory()->for($category)->create(['name' => 'Chef Knife', 'slug' => 'chef-knife']);

        $response = $this->postJson('/api/v1/admin/products', [
            ...$this->productPayload($category),
            'name' => 'Chef Knife',
            'sku' => 'KNIFE-002',
        ])->assertCreated()->assertJsonPath('data.slug', 'chef-knife-2');

        $this->deleteJson('/api/v1/admin/products/'.$response->json('data.id'))->assertNoContent();
        $this->assertSoftDeleted('products', ['id' => $response->json('data.id')]);
    }

    public function test_admin_can_change_primary_image_and_delete_an_image(): void
    {
        Storage::fake('public');
        Sanctum::actingAs($this->userWithRole('admin'));
        $product = Product::factory()->create();
        $first = ProductImage::factory()->for($product)->create(['path' => 'products/first.webp', 'is_primary' => true]);
        $second = ProductImage::factory()->for($product)->create(['path' => 'products/second.webp', 'is_primary' => false]);
        Storage::disk('public')->put($first->path, 'first');
        Storage::disk('public')->put($second->path, 'second');

        $this->patchJson("/api/v1/admin/products/{$product->id}/images/{$second->id}/primary")->assertOk();
        $this->assertFalse($first->fresh()->is_primary);
        $this->assertTrue($second->fresh()->is_primary);

        $this->deleteJson("/api/v1/admin/products/{$product->id}/images/{$second->id}")->assertNoContent();
        Storage::disk('public')->assertMissing($second->path);
        $this->assertTrue($first->fresh()->is_primary);
    }

    public function test_customer_cannot_manage_products(): void
    {
        Sanctum::actingAs($this->userWithRole('customer'));

        $this->getJson('/api/v1/admin/products')->assertForbidden();
        $this->postJson('/api/v1/admin/products', [])->assertForbidden();
    }

    private function productPayload(Category $category): array
    {
        return [
            'category_id' => $category->id,
            'name' => 'Premium Frying Pan',
            'description' => 'A durable kitchen pan.',
            'price' => 150000,
            'discount_price' => 125000,
            'brand' => 'Kitchen Pro',
            'sku' => 'PAN-001',
            'stock_quantity' => 25,
            'status' => 'active',
            'is_featured' => true,
        ];
    }

    private function userWithRole(string $slug): User
    {
        return User::factory()->for(Role::factory()->create(['slug' => $slug]))->create();
    }
}
