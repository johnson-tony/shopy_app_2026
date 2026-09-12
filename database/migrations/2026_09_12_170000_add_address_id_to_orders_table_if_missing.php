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
        if (Schema::hasTable('orders') && !Schema::hasColumn('orders', 'address_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->foreignId('address_id')
                    ->nullable()
                    ->after('mode_id')
                    ->constrained('user_addresses')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'address_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropForeign(['address_id']);
                $table->dropColumn('address_id');
            });
        }
    }
};
