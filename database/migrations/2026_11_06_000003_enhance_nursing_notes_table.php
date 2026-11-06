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
        Schema::table('nursing_notes', function (Blueprint $table) {
            if (!Schema::hasColumn('nursing_notes', 'weight')) {
                $table->decimal('weight', 5, 2)->nullable()->after('spo2');
            }
            if (!Schema::hasColumn('nursing_notes', 'temperature_unit')) {
                $table->string('temperature_unit', 10)->default('°F')->after('temperature');
            }
            if (!Schema::hasColumn('nursing_notes', 'weight_unit')) {
                $table->string('weight_unit', 10)->default('kg')->after('weight');
            }
            if (!Schema::hasColumn('nursing_notes', 'blood_pressure_unit')) {
                $table->string('blood_pressure_unit', 10)->default('mmHg')->after('blood_pressure');
            }
            if (!Schema::hasColumn('nursing_notes', 'pulse_rate_unit')) {
                $table->string('pulse_rate_unit', 10)->default('bpm')->after('pulse_rate');
            }
            if (!Schema::hasColumn('nursing_notes', 'respiratory_rate_unit')) {
                $table->string('respiratory_rate_unit', 20)->default('breaths/min')->after('respiratory_rate');
            }
            if (!Schema::hasColumn('nursing_notes', 'spo2_unit')) {
                $table->string('spo2_unit', 10)->default('%')->after('spo2');
            }
            if (!Schema::hasColumn('nursing_notes', 'is_flagged_abnormal')) {
                $table->boolean('is_flagged_abnormal')->default(false)->index()->after('observations');
            }
            if (!Schema::hasColumn('nursing_notes', 'clinical_flags')) {
                $table->json('clinical_flags')->nullable()->after('is_flagged_abnormal');
            }
            if (!Schema::hasColumn('nursing_notes', 'clinical_review_status')) {
                $table->enum('clinical_review_status', ['normal', 'flagged_for_review', 'reviewed'])
                    ->default('normal')
                    ->index()
                    ->after('clinical_flags');
            }
            if (!Schema::hasColumn('nursing_notes', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()->after('clinical_review_status')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('nursing_notes', 'reviewed_at')) {
                $table->dateTime('reviewed_at')->nullable()->after('reviewed_by');
            }
            if (!Schema::hasColumn('nursing_notes', 'review_notes')) {
                $table->text('review_notes')->nullable()->after('reviewed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nursing_notes', function (Blueprint $table) {
            if (Schema::hasColumn('nursing_notes', 'reviewed_by')) {
                $table->dropForeign(['reviewed_by']);
            }
            $columnsToDrop = array_filter([
                'weight',
                'temperature_unit',
                'weight_unit',
                'blood_pressure_unit',
                'pulse_rate_unit',
                'respiratory_rate_unit',
                'spo2_unit',
                'is_flagged_abnormal',
                'clinical_flags',
                'clinical_review_status',
                'reviewed_by',
                'reviewed_at',
                'review_notes',
            ], fn($c) => Schema::hasColumn('nursing_notes', $c));

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
