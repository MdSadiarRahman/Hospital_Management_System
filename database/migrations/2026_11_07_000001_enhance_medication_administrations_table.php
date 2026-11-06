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
        Schema::table('medication_administrations', function (Blueprint $table) {
            if (!Schema::hasColumn('medication_administrations', 'prescription_id')) {
                $table->foreignId('prescription_id')->nullable()->after('admission_id')->constrained('prescriptions')->nullOnDelete();
            }
            if (!Schema::hasColumn('medication_administrations', 'route')) {
                $table->string('route', 50)->default('Oral')->after('dose_given');
            }
            if (!Schema::hasColumn('medication_administrations', 'scheduled_time')) {
                $table->dateTime('scheduled_time')->nullable()->after('route');
            }
            if (!Schema::hasColumn('medication_administrations', 'scheduled_shift')) {
                $table->enum('scheduled_shift', ['morning', 'evening', 'night'])->nullable()->after('scheduled_time');
            }
            if (!Schema::hasColumn('medication_administrations', 'withheld_reason')) {
                $table->string('withheld_reason', 191)->nullable()->after('status');
            }
            if (!Schema::hasColumn('medication_administrations', 'is_corrected')) {
                $table->boolean('is_corrected')->default(false)->after('remarks');
            }
            if (!Schema::hasColumn('medication_administrations', 'correction_reason')) {
                $table->text('correction_reason')->nullable()->after('is_corrected');
            }
            if (!Schema::hasColumn('medication_administrations', 'corrected_by')) {
                $table->foreignId('corrected_by')->nullable()->after('correction_reason')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('medication_administrations', 'corrected_at')) {
                $table->dateTime('corrected_at')->nullable()->after('corrected_by');
            }
        });

        // Expand status enum to support 'missed' and 'withheld'
        DB::statement("ALTER TABLE medication_administrations MODIFY COLUMN status ENUM('administered', 'missed', 'withheld', 'refused', 'omitted') NOT NULL DEFAULT 'administered'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medication_administrations', function (Blueprint $table) {
            if (Schema::hasColumn('medication_administrations', 'prescription_id')) {
                $table->dropForeign(['prescription_id']);
            }
            if (Schema::hasColumn('medication_administrations', 'corrected_by')) {
                $table->dropForeign(['corrected_by']);
            }
            $columnsToDrop = array_filter([
                'prescription_id',
                'route',
                'scheduled_time',
                'scheduled_shift',
                'withheld_reason',
                'is_corrected',
                'correction_reason',
                'corrected_by',
                'corrected_at',
            ], fn($c) => Schema::hasColumn('medication_administrations', $c));

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });

        DB::statement("ALTER TABLE medication_administrations MODIFY COLUMN status ENUM('administered', 'omitted', 'refused') NOT NULL DEFAULT 'administered'");
    }
};

