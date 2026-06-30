<?php

namespace Tests\Feature\Search;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_search_returns_matching_products_and_categories(): void
    {
        $category = Category::factory()->create([
            'name' => 'Cookware',
            'slug' => 'cookware',
            'description' => 'Kitchen cookware collection',
        ]);
        Product::factory()->for($category)->create([
            'name' => 'Cookware Frying Pan',
            'slug' => 'cookware-frying-pan',
            'status' => 'active',
        ]);

        $this->getJson('/api/v1/search?q=cookware')
            ->assertOk()
            ->assertJsonPath('data.query', 'cookware')
            ->assertJsonPath('data.products.meta.total', 1)
            ->assertJsonPath('data.products.data.0.slug', 'cookware-frying-pan')
            ->assertJsonPath('data.categories.meta.total', 1)
            ->assertJsonPath('data.categories.data.0.slug', 'cookware');
    }

    public function test_type_selection_omits_unrequested_result_group(): void
    {
        $product = Product::factory()->create(['name' => 'Steel Pan', 'slug' => 'steel-pan']);

        $this->getJson('/api/v1/search?q=steel&type=products')
            ->assertOk()
            ->assertJsonPath('data.products.data.0.id', $product->id)
            ->assertJsonPath('data.categories', null);
    }

    public function test_product_filters_use_effective_price_stock_featured_and_safe_sorting(): void
    {
        $category = Category::factory()->create();
        $matching = Product::factory()->for($category)->create([
            'name' => 'Premium Pan Alpha',
            'brand' => 'Kitchen Pro',
            'price' => 200,
            'discount_price' => 120,
            'stock_quantity' => 4,
            'is_featured' => true,
        ]);
        Product::factory()->for($category)->create([
            'name' => 'Premium Pan Beta',
            'brand' => 'Other Brand',
            'price' => 100,
            'stock_quantity' => 0,
            'is_featured' => false,
        ]);

        $url = "/api/v1/search?q=premium&type=products&category_id={$category->id}"
            .'&brand=Kitchen%20Pro&min_price=100&max_price=150&in_stock=1&featured=1&sort=price_desc';

        $this->getJson($url)
            ->assertOk()
            ->assertJsonPath('data.products.meta.total', 1)
            ->assertJsonPath('data.products.data.0.id', $matching->id);
    }

    public function test_product_and_category_pages_are_independent(): void
    {
        $category = Category::factory()->create(['name' => 'Pan Collection One']);
        Category::factory()->create(['name' => 'Pan Collection Two']);
        Product::factory()->for($category)->create(['name' => 'Pan Product One']);
        Product::factory()->for($category)->create(['name' => 'Pan Product Two']);

        $this->getJson('/api/v1/search?q=pan&per_page=1&product_page=2&category_page=2')
            ->assertOk()
            ->assertJsonPath('data.products.meta.current_page', 2)
            ->assertJsonPath('data.categories.meta.current_page', 2)
            ->assertJsonCount(1, 'data.products.data')
            ->assertJsonCount(1, 'data.categories.data');
    }

    public function test_inactive_records_are_hidden_and_search_input_is_validated(): void
    {
        $inactiveCategory = Category::factory()->create(['name' => 'Hidden Pan', 'is_active' => false]);
        Product::factory()->for($inactiveCategory)->create(['name' => 'Hidden Pan Product', 'status' => 'active']);
        Product::factory()->create(['name' => 'Draft Pan Product', 'status' => 'draft']);

        $this->getJson('/api/v1/search?q=hidden')
            ->assertOk()
            ->assertJsonPath('data.products.meta.total', 0)
            ->assertJsonPath('data.categories.meta.total', 0);

        $this->getJson('/api/v1/search?q=x')
            ->assertUnprocessable()->assertJsonValidationErrors('q');
        $this->getJson('/api/v1/search?q=pan&sort=unsafe-column')
            ->assertUnprocessable()->assertJsonValidationErrors('sort');
    }
}
