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
        // 1. Enhance Admissions Table with Nursing Clearance, Financial Exception, and Final Approver
        Schema::table('admissions', function (Blueprint $table) {
            $table->boolean('nursing_clearance')->default(false)->after('clinical_clearance_by')->index();
            $table->dateTime('nursing_clearance_at')->nullable()->after('nursing_clearance');
            $table->foreignId('nursing_clearance_by')->nullable()->after('nursing_clearance_at')->constrained('users')->nullOnDelete();
            $table->text('nursing_clearance_notes')->nullable()->after('nursing_clearance_by');

            $table->boolean('financial_exception_allowed')->default(false)->after('financial_clearance_by')->index();
            $table->text('financial_exception_reason')->nullable()->after('financial_exception_allowed');
            $table->foreignId('financial_exception_by')->nullable()->after('financial_exception_reason')->constrained('users')->nullOnDelete();

            $table->foreignId('discharged_by')->nullable()->after('discharge_date')->constrained('users')->nullOnDelete();
        });

        // 2. Enhance Discharge Summaries Table
        Schema::table('discharge_summaries', function (Blueprint $table) {
            $table->text('hospital_course')->nullable()->after('treatment_given');
            $table->text('investigations_summary')->nullable()->after('hospital_course');
        });

        // 3. Create Discharge Histories Table for Immutable Discharge Audit Trail
        Schema::create('discharge_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_id')->constrained('admissions')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->string('clearance_type', 32)->index(); // clinical, nursing, financial, final_approval, reversal
            $table->string('action', 100);
            $table->string('status_before', 50)->nullable();
            $table->string('status_after', 50)->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('performed_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discharge_histories');

        Schema::table('discharge_summaries', function (Blueprint $table) {
            $table->dropColumn(['hospital_course', 'investigations_summary']);
        });

        Schema::table('admissions', function (Blueprint $table) {
            $table->dropForeign(['nursing_clearance_by']);
            $table->dropForeign(['financial_exception_by']);
            $table->dropForeign(['discharged_by']);
            $table->dropColumn([
                'nursing_clearance',
                'nursing_clearance_at',
                'nursing_clearance_by',
                'nursing_clearance_notes',
                'financial_exception_allowed',
                'financial_exception_reason',
                'financial_exception_by',
                'discharged_by',
            ]);
        });
    }
};

