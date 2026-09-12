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
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'shipping_name')) {
                    $table->string('shipping_name')->nullable()->change();
                }
                if (Schema::hasColumn('orders', 'shipping_phone')) {
                    $table->string('shipping_phone')->nullable()->change();
                }
                if (Schema::hasColumn('orders', 'shipping_address_line1')) {
                    $table->string('shipping_address_line1')->nullable()->change();
                }
                if (Schema::hasColumn('orders', 'shipping_city')) {
                    $table->string('shipping_city')->nullable()->change();
                }
                if (Schema::hasColumn('orders', 'shipping_state')) {
                    $table->string('shipping_state')->nullable()->change();
                }
                if (Schema::hasColumn('orders', 'shipping_postal_code')) {
                    $table->string('shipping_postal_code')->nullable()->change();
                }
                if (Schema::hasColumn('orders', 'shipping_country')) {
                    $table->string('shipping_country')->nullable()->change();
                }
                if (Schema::hasColumn('orders', 'shipping_address_type')) {
                    $table->string('shipping_address_type')->nullable()->change();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op to preserve data safety
    }
};
