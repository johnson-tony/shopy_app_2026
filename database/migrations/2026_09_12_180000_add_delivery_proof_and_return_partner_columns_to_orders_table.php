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
        // Add KYC & onboarding fields to delivery_partners table
        if (Schema::hasTable('delivery_partners')) {
            Schema::table('delivery_partners', function (Blueprint $table) {
                if (!Schema::hasColumn('delivery_partners', 'license_number')) {
                    $table->string('license_number')->nullable()->after('vehicle_type');
                }
                if (!Schema::hasColumn('delivery_partners', 'license_image')) {
                    $table->string('license_image')->nullable()->after('license_number');
                }
                if (!Schema::hasColumn('delivery_partners', 'id_proof_type')) {
                    $table->string('id_proof_type', 30)->nullable()->after('license_image');
                }
                if (!Schema::hasColumn('delivery_partners', 'id_proof_number')) {
                    $table->string('id_proof_number')->nullable()->after('id_proof_type');
                }
                if (!Schema::hasColumn('delivery_partners', 'id_proof_image')) {
                    $table->string('id_proof_image')->nullable()->after('id_proof_number');
                }
                if (!Schema::hasColumn('delivery_partners', 'vehicle_number')) {
                    $table->string('vehicle_number')->nullable()->after('id_proof_image');
                }
                if (!Schema::hasColumn('delivery_partners', 'bank_account_number')) {
                    $table->string('bank_account_number')->nullable()->after('vehicle_number');
                }
                if (!Schema::hasColumn('delivery_partners', 'bank_ifsc')) {
                    $table->string('bank_ifsc')->nullable()->after('bank_account_number');
                }
                if (!Schema::hasColumn('delivery_partners', 'upi_id')) {
                    $table->string('upi_id')->nullable()->after('bank_ifsc');
                }
                if (!Schema::hasColumn('delivery_partners', 'rejection_reason')) {
                    $table->string('rejection_reason')->nullable()->after('upi_id');
                }
                if (!Schema::hasColumn('delivery_partners', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('rejection_reason');
                }
            });
        }

        // Add Proof of Delivery & Return Partner assignment to orders table
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'delivery_proof_image')) {
                    $table->string('delivery_proof_image')->nullable()->after('notes');
                }
                if (!Schema::hasColumn('orders', 'delivery_notes')) {
                    $table->text('delivery_notes')->nullable()->after('delivery_proof_image');
                }
                if (!Schema::hasColumn('orders', 'return_partner_id')) {
                    $table->foreignId('return_partner_id')->nullable()->after('delivery_notes')
                        ->constrained('delivery_partners')->nullOnDelete();
                }
                if (!Schema::hasColumn('orders', 'return_pickup_image')) {
                    $table->string('return_pickup_image')->nullable()->after('return_partner_id');
                }
                if (!Schema::hasColumn('orders', 'return_pickup_notes')) {
                    $table->text('return_pickup_notes')->nullable()->after('return_pickup_image');
                }
                if (!Schema::hasColumn('orders', 'return_picked_up_at')) {
                    $table->timestamp('return_picked_up_at')->nullable()->after('return_pickup_notes');
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
                if (Schema::hasColumn('orders', 'return_partner_id')) {
                    $table->dropForeign(['return_partner_id']);
                }
                $orderCols = [
                    'delivery_proof_image',
                    'delivery_notes',
                    'return_partner_id',
                    'return_pickup_image',
                    'return_pickup_notes',
                    'return_picked_up_at',
                ];
                foreach ($orderCols as $col) {
                    if (Schema::hasColumn('orders', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('delivery_partners')) {
            Schema::table('delivery_partners', function (Blueprint $table) {
                $partnerCols = [
                    'license_number',
                    'license_image',
                    'id_proof_type',
                    'id_proof_number',
                    'id_proof_image',
                    'vehicle_number',
                    'bank_account_number',
                    'bank_ifsc',
                    'upi_id',
                    'rejection_reason',
                    'approved_at',
                ];
                foreach ($partnerCols as $col) {
                    if (Schema::hasColumn('delivery_partners', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
