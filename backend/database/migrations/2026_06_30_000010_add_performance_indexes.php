<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', fn (Blueprint $table) => $table->index(['status', 'paid_at'], 'payments_status_paid_at_index'));
        Schema::table('products', fn (Blueprint $table) => $table->index(['status', 'stock_quantity'], 'products_status_stock_index'));
        Schema::table('orders', fn (Blueprint $table) => $table->index(['status', 'placed_at'], 'orders_status_placed_at_index'));
        Schema::table('order_items', fn (Blueprint $table) => $table->index(['product_id', 'order_id'], 'order_items_product_order_index'));
    }

    public function down(): void
    {
        Schema::table('payments', fn (Blueprint $table) => $table->dropIndex('payments_status_paid_at_index'));
        Schema::table('products', fn (Blueprint $table) => $table->dropIndex('products_status_stock_index'));
        Schema::table('orders', fn (Blueprint $table) => $table->dropIndex('orders_status_placed_at_index'));
        Schema::table('order_items', fn (Blueprint $table) => $table->dropIndex('order_items_product_order_index'));
    }
};
