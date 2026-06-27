<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('shipping_address_id')->nullable()->constrained('shipping_addresses')->nullOnDelete();
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();

            $table->string('shipping_first_name');
            $table->string('shipping_last_name');
            $table->string('shipping_phone');
            $table->string('shipping_country');
            $table->string('shipping_state');
            $table->string('shipping_city');
            $table->string('shipping_address_line_1');
            $table->string('shipping_address_line_2')->nullable();
            $table->string('shipping_postal_code')->nullable();

            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('shipping_total', 12, 2)->default(0);
            $table->decimal('tax_total', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2);
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('placed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status', 'placed_at']);
            $table->index(['order_number', 'coupon_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
