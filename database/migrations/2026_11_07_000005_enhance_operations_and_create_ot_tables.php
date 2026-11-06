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
        Schema::table('operations', function (Blueprint $table) {
            if (!Schema::hasColumn('operations', 'operation_number')) {
                $table->string('operation_number', 30)->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('operations', 'patient_id')) {
                $table->foreignId('patient_id')->nullable()->after('operation_number')->constrained('patients')->restrictOnDelete();
            }
            if (!Schema::hasColumn('operations', 'department_id')) {
                $table->foreignId('department_id')->nullable()->after('procedure_name')->constrained('departments')->nullOnDelete();
            }
            if (!Schema::hasColumn('operations', 'ot_room')) {
                $table->string('ot_room', 100)->default('OT-1 (Main Theatre)')->after('department_id')->index();
            }
            if (!Schema::hasColumn('operations', 'urgency')) {
                $table->enum('urgency', ['elective', 'urgent', 'emergency'])->default('elective')->after('ot_room')->index();
            }
            if (!Schema::hasColumn('operations', 'indication')) {
                $table->text('indication')->nullable()->after('urgency');
            }
            if (!Schema::hasColumn('operations', 'estimated_duration_minutes')) {
                $table->unsignedSmallInteger('estimated_duration_minutes')->default(60)->after('scheduled_at');
            }
            if (!Schema::hasColumn('operations', 'scheduled_end_at')) {
                $table->dateTime('scheduled_end_at')->nullable()->after('estimated_duration_minutes')->index();
            }
            if (!Schema::hasColumn('operations', 'ot_nurse_name')) {
                $table->string('ot_nurse_name', 191)->nullable()->after('anesthetist_name');
            }
            if (!Schema::hasColumn('operations', 'requested_by')) {
                $table->foreignId('requested_by')->nullable()->after('assistant_surgeon_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('operations', 'rescheduled_reason')) {
                $table->text('rescheduled_reason')->nullable()->after('post_op_notes');
            }
            if (!Schema::hasColumn('operations', 'rescheduled_at')) {
                $table->dateTime('rescheduled_at')->nullable()->after('rescheduled_reason');
            }
            if (!Schema::hasColumn('operations', 'rescheduled_by')) {
                $table->foreignId('rescheduled_by')->nullable()->after('rescheduled_at')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('operations', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('rescheduled_by');
            }
            if (!Schema::hasColumn('operations', 'cancelled_at')) {
                $table->dateTime('cancelled_at')->nullable()->after('cancellation_reason');
            }
            if (!Schema::hasColumn('operations', 'cancelled_by')) {
                $table->foreignId('cancelled_by')->nullable()->after('cancelled_at')->constrained('users')->nullOnDelete();
            }
        });

        // Expand status enum to support requested and rescheduled statuses
        DB::statement("ALTER TABLE `operations` MODIFY COLUMN `status` ENUM('requested', 'scheduled', 'rescheduled', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'scheduled'");

        // Operation History Audit Table
        if (!Schema::hasTable('operation_histories')) {
            Schema::create('operation_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('operation_id')->constrained('operations')->cascadeOnDelete();
                $table->string('action', 50)->index(); // 'requested', 'scheduled', 'rescheduled', 'started', 'completed', 'cancelled', 'notes_updated', 'observation_recorded'
                $table->foreignId('changed_by')->constrained('users')->restrictOnDelete();
                $table->string('previous_status', 50)->nullable();
                $table->string('new_status', 50)->nullable();
                $table->dateTime('previous_scheduled_at')->nullable();
                $table->dateTime('new_scheduled_at')->nullable();
                $table->text('reason')->nullable();
                $table->text('notes')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        // Post-Operative Bedside Observations Table
        if (!Schema::hasTable('post_op_observations')) {
            Schema::create('post_op_observations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('operation_id')->constrained('operations')->cascadeOnDelete();
                $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
                $table->dateTime('observed_at')->index();
                $table->unsignedSmallInteger('blood_pressure_systolic')->nullable();
                $table->unsignedSmallInteger('blood_pressure_diastolic')->nullable();
                $table->unsignedSmallInteger('heart_rate')->nullable();
                $table->unsignedSmallInteger('respiratory_rate')->nullable();
                $table->decimal('temperature_celsius', 4, 1)->nullable();
                $table->unsignedSmallInteger('spo2_percent')->nullable();
                $table->enum('consciousness_level', ['alert', 'drowsy', 'stupor', 'unconscious'])->default('alert');
                $table->unsignedTinyInteger('pain_score')->nullable(); // 0-10
                $table->string('surgical_site_condition', 255)->nullable();
                $table->unsignedSmallInteger('drain_output_ml')->nullable();
                $table->unsignedSmallInteger('urine_output_ml')->nullable();
                $table->text('observation_notes');
                $table->boolean('discharge_criteria_met')->default(false);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_op_observations');
        Schema::dropIfExists('operation_histories');

        Schema::table('operations', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
            $table->dropForeign(['department_id']);
            $table->dropForeign(['requested_by']);
            $table->dropForeign(['rescheduled_by']);
            $table->dropForeign(['cancelled_by']);

            $table->dropColumn([
                'operation_number',
                'patient_id',
                'department_id',
                'ot_room',
                'urgency',
                'indication',
                'estimated_duration_minutes',
                'scheduled_end_at',
                'ot_nurse_name',
                'requested_by',
                'rescheduled_reason',
                'rescheduled_at',
                'rescheduled_by',
                'cancellation_reason',
                'cancelled_at',
                'cancelled_by',
            ]);
        });

        DB::statement("ALTER TABLE `operations` MODIFY COLUMN `status` ENUM('scheduled', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'scheduled'");
    }
};

