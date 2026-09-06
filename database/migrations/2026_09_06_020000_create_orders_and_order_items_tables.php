<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mode_id')->nullable()->constrained('modes')->nullOnDelete();

            // Delivery Address (foreign key to user_addresses)
            $table->foreignId('address_id')->nullable()->constrained('user_addresses')->nullOnDelete();

            // Order lifecycle status
            $table->string('status')->default('confirmed'); // confirmed, processing, shipped, delivered, cancelled
            $table->string('payment_method')->default('pay_on_delivery'); // pay_on_delivery, mock_upi, mock_card, mock_netbanking
            $table->string('payment_status')->default('pending'); // pending, paid, failed, refunded

            // Financial breakdown
            $table->decimal('subtotal', 12, 2);
            $table->decimal('delivery_fee', 12, 2)->default(0.00);
            $table->decimal('tax_amount', 12, 2)->default(0.00);
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->string('coupon_code')->nullable();
            $table->decimal('grand_total', 12, 2);

            // Additional details
            $table->text('notes')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->timestamps();

            // Performant query indexes
            $table->index(['user_id', 'status']);
            $table->index(['mode_id', 'status']);
            $table->index('address_id');
            $table->index('created_at');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name');
            $table->string('product_slug');
            $table->string('product_image')->nullable();
            $table->string('color')->nullable();
            $table->string('size')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();

            $table->index(['order_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
