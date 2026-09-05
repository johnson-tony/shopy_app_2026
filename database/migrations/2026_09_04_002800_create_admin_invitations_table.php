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
        if (!Schema::hasTable('admin_invitations')) {
            Schema::create('admin_invitations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('admin_id')->nullable()->constrained('admins')->cascadeOnDelete();
                $table->string('name');
                $table->string('email');
                $table->string('token', 64)->unique();
                $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
                $table->json('mode_ids')->nullable();
                $table->timestamp('expires_at');
                $table->timestamp('accepted_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();
                $table->timestamps();

                $table->index(['email', 'token']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_invitations');
    }
};
