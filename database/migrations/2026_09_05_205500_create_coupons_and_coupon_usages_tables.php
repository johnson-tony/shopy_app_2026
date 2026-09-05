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
        // 1. Coupons Table
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('type', 30)->default('percentage'); // percentage, fixed, free_delivery
            $table->decimal('value', 12, 2)->default(0.00);
            $table->decimal('min_order_amount', 12, 2)->default(0.00);
            $table->decimal('max_discount_amount', 12, 2)->nullable();
            $table->foreignId('mode_id')->nullable()->constrained('modes')->cascadeOnDelete();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_limit_per_user')->default(1);
            $table->unsignedInteger('times_used')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index(['code', 'status']);
            $table->index(['mode_id', 'status']);
        });

        // 2. Coupon Usages Table (Audit & Abuse Prevention)
        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->timestamp('used_at')->useCurrent();

            $table->index(['coupon_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupons');
    }
};
