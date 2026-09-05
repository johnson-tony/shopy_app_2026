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
        if (Schema::hasTable('roles') && !Schema::hasColumn('roles', 'status')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->boolean('status')->default(true)->after('description');
            });
        }

        if (Schema::hasTable('permissions') && !Schema::hasColumn('permissions', 'module')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->string('module', 50)->nullable()->after('slug');
                $table->index('module');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('roles') && Schema::hasColumn('roles', 'status')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }

        if (Schema::hasTable('permissions') && Schema::hasColumn('permissions', 'module')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->dropIndex(['module']);
                $table->dropColumn('module');
            });
        }
    }
};
