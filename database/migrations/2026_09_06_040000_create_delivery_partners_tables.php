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
        Schema::create('delivery_partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable()->index();
            $table->string('password');
            $table->string('avatar')->nullable();
            // vehicle_type: bike, scooter, car, van, etc.
            $table->string('vehicle_type')->nullable();
            // status: active, inactive, suspended
            $table->string('status', 20)->default('active')->index();
            // is_available: currently accepting assignments / online
            $table->boolean('is_available')->default(true)->index();
            // Base/registered location (set once when the partner is onboarded).
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            // Live location provider callback key (used by the frontend GPS updater).
            $table->string('location_source', 20)->default('static')->index();
            $table->timestamp('last_location_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index(['status', 'is_available']);
        });

        Schema::create('partner_password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Modes a partner is allowed to fulfil (Shopy / Minutes / Food).
        Schema::create('delivery_partner_modes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_partner_id')->constrained('delivery_partners')->cascadeOnDelete();
            $table->foreignId('mode_id')->constrained('modes')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['delivery_partner_id', 'mode_id']);
        });

        // Live GPS location history broadcast by the partner panel.
        Schema::create('partner_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_partner_id')->constrained('delivery_partners')->cascadeOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('accuracy', 10, 2)->nullable();
            $table->timestamp('recorded_at')->nullable()->index();

            $table->index(['delivery_partner_id', 'recorded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_locations');
        Schema::dropIfExists('delivery_partner_modes');
        Schema::dropIfExists('partner_password_reset_tokens');
        Schema::dropIfExists('delivery_partners');
    }
};
