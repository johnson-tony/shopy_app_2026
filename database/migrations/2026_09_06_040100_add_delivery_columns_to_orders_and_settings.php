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
        // Delivery partner assignment + milestone timestamps on orders.
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('delivery_partner_id')->nullable()->after('mode_id')
                ->constrained('delivery_partners')->nullOnDelete();

            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('ready_for_delivery_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('out_for_delivery_at')->nullable();

            $table->index('delivery_partner_id');
            $table->index(['status', 'delivery_partner_id']);
        });

        // Global + per-mode delivery toggles on the singleton settings record.
        Schema::table('admin_settings', function (Blueprint $table) {
            $table->boolean('is_delivery_enabled')->default(true)->after('site_logo');
            $table->boolean('delivery_enabled_shopy')->default(false)->after('is_delivery_enabled');
            $table->boolean('delivery_enabled_minutes')->default(true)->after('delivery_enabled_shopy');
            $table->boolean('delivery_enabled_food')->default(false)->after('delivery_enabled_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_settings', function (Blueprint $table) {
            $table->dropColumn(['is_delivery_enabled', 'delivery_enabled_shopy', 'delivery_enabled_minutes', 'delivery_enabled_food']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['delivery_partner_id']);
            $table->dropIndex(['status', 'delivery_partner_id']);
            $table->dropColumn(['delivery_partner_id', 'assigned_at', 'ready_for_delivery_at', 'picked_up_at', 'out_for_delivery_at']);
        });
    }
};
