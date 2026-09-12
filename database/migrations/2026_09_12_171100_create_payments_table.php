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
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('payment_method')->default('pay_on_delivery'); // pay_on_delivery, mock_upi, mock_card, etc.
                $table->string('payment_gateway')->default('manual'); // manual, direct_upi, simulated_card, razorpay, phonepe
                $table->string('transaction_id')->unique();
                $table->decimal('amount', 12, 2);
                $table->string('currency', 10)->default('INR');
                $table->string('status')->default('pending'); // pending, completed, failed, refunded
                $table->text('notes')->nullable();
                $table->json('payload')->nullable(); // For gateway response data or debug info
                $table->timestamps();

                $table->index(['order_id', 'status']);
                $table->index(['user_id', 'status']);
                $table->index('transaction_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
