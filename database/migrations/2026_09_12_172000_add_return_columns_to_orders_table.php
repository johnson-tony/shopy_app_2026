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
                if (!Schema::hasColumn('orders', 'return_status')) {
                    $table->string('return_status')->nullable()->after('status'); // requested, approved, rejected, completed
                }
                if (!Schema::hasColumn('orders', 'return_reason')) {
                    $table->string('return_reason')->nullable()->after('return_status');
                }
                if (!Schema::hasColumn('orders', 'return_note')) {
                    $table->text('return_note')->nullable()->after('return_reason');
                }
                if (!Schema::hasColumn('orders', 'return_image')) {
                    $table->string('return_image')->nullable()->after('return_note');
                }
                if (!Schema::hasColumn('orders', 'return_requested_at')) {
                    $table->timestamp('return_requested_at')->nullable()->after('return_image');
                }
                if (!Schema::hasColumn('orders', 'return_resolved_at')) {
                    $table->timestamp('return_resolved_at')->nullable()->after('return_requested_at');
                }
                if (!Schema::hasColumn('orders', 'return_rejection_reason')) {
                    $table->string('return_rejection_reason')->nullable()->after('return_resolved_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                $columns = [
                    'return_status',
                    'return_reason',
                    'return_note',
                    'return_image',
                    'return_requested_at',
                    'return_resolved_at',
                    'return_rejection_reason',
                ];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('orders', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
