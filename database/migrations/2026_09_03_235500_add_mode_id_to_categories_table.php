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
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('mode_id')
                ->nullable()
                ->after('id')
                ->constrained('modes')
                ->nullOnDelete();

            $table->index(['mode_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['mode_id']);
            $table->dropIndex(['mode_id', 'status']);
            $table->dropColumn('mode_id');
        });
    }
};
