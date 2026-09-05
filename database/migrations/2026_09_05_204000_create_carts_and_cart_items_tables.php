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
        // 1. Carts Table
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('session_id', 100)->nullable()->index();
            $table->foreignId('mode_id')->constrained('modes')->cascadeOnDelete();
            $table->string('coupon_code', 50)->nullable();
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Performant lookups for active carts
            $table->index(['user_id', 'mode_id']);
            $table->index(['session_id', 'mode_id']);
        });

        // 2. Cart Items Table
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->timestamps();

            // Prevent duplicate line items for the same product in a single cart
            $table->unique(['cart_id', 'product_id']);
            $table->index('cart_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
