<?php

namespace Tests\Feature\Database;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Role;
use App\Models\ShippingAddress;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchemaRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_belong_to_roles_and_have_personal_data_relations(): void
    {
        $role = Role::factory()->create();
        $user = User::factory()->create([
            'role_id' => $role->id,
        ]);

        $address = ShippingAddress::factory()->create([
            'user_id' => $user->id,
        ]);

        $wishlist = Wishlist::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertTrue($user->role->is($role));
        $this->assertTrue($user->shippingAddresses->contains($address));
        $this->assertTrue($user->wishlist->is($wishlist));
    }

    public function test_categories_can_nest_and_own_products(): void
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->create([
            'parent_id' => $parent->id,
        ]);
        $product = Product::factory()->create([
            'category_id' => $parent->id,
        ]);

        $this->assertTrue($parent->children->contains($child));
        $this->assertTrue($child->parent->is($parent));
        $this->assertTrue($parent->products->contains($product));
    }

    public function test_orders_snapshot_shipping_data_and_link_to_items_and_payments(): void
    {
        $order = Order::factory()->create([
            'shipping_address_id' => null,
        ]);

        $item = OrderItem::factory()->create([
            'order_id' => $order->id,
        ]);

        $payment = Payment::factory()->create([
            'order_id' => $order->id,
        ]);

        $this->assertTrue($order->items->contains($item));
        $this->assertTrue($order->payments->contains($payment));
        $this->assertNotEmpty($order->shipping_first_name);
        $this->assertNotEmpty($order->shipping_address_line_1);
    }
}
