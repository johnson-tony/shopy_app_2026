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
        if (Schema::hasTable('admin_settings')) {
            Schema::table('admin_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('admin_settings', 'upi_id')) {
                    $table->string('upi_id')->nullable()->default('shopy@upi')->after('delivery_enabled_food');
                }
                if (!Schema::hasColumn('admin_settings', 'upi_merchant_name')) {
                    $table->string('upi_merchant_name')->nullable()->default('Shopy Store')->after('upi_id');
                }
                if (!Schema::hasColumn('admin_settings', 'upi_qr_image')) {
                    $table->string('upi_qr_image')->nullable()->after('upi_merchant_name');
                }
                if (!Schema::hasColumn('admin_settings', 'is_cod_enabled')) {
                    $table->boolean('is_cod_enabled')->default(true)->after('upi_qr_image');
                }
                if (!Schema::hasColumn('admin_settings', 'is_upi_enabled')) {
                    $table->boolean('is_upi_enabled')->default(true)->after('is_cod_enabled');
                }
                if (!Schema::hasColumn('admin_settings', 'is_card_enabled')) {
                    $table->boolean('is_card_enabled')->default(true)->after('is_upi_enabled');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('admin_settings')) {
            Schema::table('admin_settings', function (Blueprint $table) {
                $columns = ['upi_id', 'upi_merchant_name', 'upi_qr_image', 'is_cod_enabled', 'is_upi_enabled', 'is_card_enabled'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('admin_settings', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
