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
        Schema::table('referrals', function (Blueprint $table) {
            if (!Schema::hasColumn('referrals', 'referral_number')) {
                $table->string('referral_number', 30)->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('referrals', 'patient_id')) {
                $table->foreignId('patient_id')->nullable()->after('referral_number')->constrained('patients')->restrictOnDelete();
            }
            if (!Schema::hasColumn('referrals', 'is_critical')) {
                $table->boolean('is_critical')->default(false)->after('referral_type')->index();
            }
            if (!Schema::hasColumn('referrals', 'requires_icu')) {
                $table->boolean('requires_icu')->default(false)->after('is_critical')->index();
            }
            if (!Schema::hasColumn('referrals', 'destination_department')) {
                $table->string('destination_department', 100)->nullable()->after('facility_name');
            }
            if (!Schema::hasColumn('referrals', 'receiving_doctor_contact')) {
                $table->string('receiving_doctor_contact', 191)->nullable()->after('destination_department');
            }
            if (!Schema::hasColumn('referrals', 'transport_type')) {
                $table->string('transport_type', 100)->nullable()->after('transport_arranged');
            }
            if (!Schema::hasColumn('referrals', 'ambulance_driver_contact')) {
                $table->string('ambulance_driver_contact', 100)->nullable()->after('transport_type');
            }
            if (!Schema::hasColumn('referrals', 'accompanying_staff')) {
                $table->string('accompanying_staff', 191)->nullable()->after('ambulance_driver_contact');
            }
            if (!Schema::hasColumn('referrals', 'oxygen_support_transit')) {
                $table->boolean('oxygen_support_transit')->default(false)->after('accompanying_staff');
            }
            if (!Schema::hasColumn('referrals', 'emergency_assessment')) {
                $table->text('emergency_assessment')->nullable()->after('clinical_reason');
            }
            if (!Schema::hasColumn('referrals', 'interventions_given')) {
                $table->text('interventions_given')->nullable()->after('emergency_assessment');
            }
            if (!Schema::hasColumn('referrals', 'vitals_snapshot')) {
                $table->json('vitals_snapshot')->nullable()->after('interventions_given');
            }
            if (!Schema::hasColumn('referrals', 'referral_notes')) {
                $table->text('referral_notes')->nullable()->after('vitals_snapshot');
            }
            if (!Schema::hasColumn('referrals', 'transferred_at')) {
                $table->dateTime('transferred_at')->nullable()->after('referred_at');
            }
            if (!Schema::hasColumn('referrals', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('transferred_at');
            }
            if (!Schema::hasColumn('referrals', 'cancelled_at')) {
                $table->dateTime('cancelled_at')->nullable()->after('cancellation_reason');
            }
            if (!Schema::hasColumn('referrals', 'cancelled_by')) {
                $table->foreignId('cancelled_by')->nullable()->after('cancelled_at')->constrained('users')->nullOnDelete();
            }
        });

        // Expand status enum to support in_transit and cancelled
        DB::statement("ALTER TABLE `referrals` MODIFY COLUMN `status` ENUM('initiated', 'in_transit', 'transferred', 'completed', 'cancelled') NOT NULL DEFAULT 'initiated'");

        // Referral Histories Audit Trail Table
        if (!Schema::hasTable('referral_histories')) {
            Schema::create('referral_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('referral_id')->constrained('referrals')->cascadeOnDelete();
                $table->string('action', 50)->index(); // 'initiated', 'dispatched_in_transit', 'transferred', 'completed', 'cancelled', 'notes_updated'
                $table->foreignId('changed_by')->constrained('users')->restrictOnDelete();
                $table->string('previous_status', 50)->nullable();
                $table->string('new_status', 50)->nullable();
                $table->text('notes')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_histories');

        Schema::table('referrals', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
            $table->dropForeign(['cancelled_by']);

            $table->dropColumn([
                'referral_number',
                'patient_id',
                'is_critical',
                'requires_icu',
                'destination_department',
                'receiving_doctor_contact',
                'transport_type',
                'ambulance_driver_contact',
                'accompanying_staff',
                'oxygen_support_transit',
                'emergency_assessment',
                'interventions_given',
                'vitals_snapshot',
                'referral_notes',
                'transferred_at',
                'cancellation_reason',
                'cancelled_at',
                'cancelled_by',
            ]);
        });

        DB::statement("ALTER TABLE `referrals` MODIFY COLUMN `status` ENUM('initiated', 'transferred', 'completed') NOT NULL DEFAULT 'initiated'");
    }
};

