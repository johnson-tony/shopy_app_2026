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
                $columns = [
                    'shipping_name',
                    'shipping_phone',
                    'shipping_address_line1',
                    'shipping_address_line2',
                    'shipping_landmark',
                    'shipping_city',
                    'shipping_state',
                    'shipping_postal_code',
                    'shipping_country',
                    'shipping_address_type',
                ];

                foreach ($columns as $column) {
                    if (!Schema::hasColumn('orders', $column)) {
                        $table->string($column)->nullable();
                    }
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
