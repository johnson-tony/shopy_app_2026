<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_settings', function (Blueprint $table) {
            $table->id();
            // Single boolean column: false (No) = Light Theme (default), true (Yes) = Dark Theme
            $table->boolean('is_dark_mode')->default(false);
            $table->timestamps();
        });

        // Insert default single setting row: default Light theme (No / false)
        DB::table('admin_settings')->insert([
            'id' => 1,
            'is_dark_mode' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_settings');
    }
};
