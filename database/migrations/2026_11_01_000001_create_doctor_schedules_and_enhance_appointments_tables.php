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
        // 1. Configurable Doctor Consultation Schedules Table
        Schema::create('doctor_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $table->enum('day_of_week', [
                'sunday',
                'monday',
                'tuesday',
                'wednesday',
                'thursday',
                'friday',
                'saturday'
            ])->index();
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('slot_duration_minutes')->default(20);
            $table->unsignedSmallInteger('max_patients')->default(20);
            $table->string('room_number', 50)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['doctor_id', 'day_of_week', 'start_time'], 'doctor_day_start_unique');
        });

        // 2. Enhance Appointments Table
        Schema::table('appointments', function (Blueprint $table) {
            $table->time('appointment_time')->nullable()->after('appointment_date');
            $table->string('guest_email', 191)->nullable()->after('guest_phone');
            $table->foreignId('confirmed_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->dateTime('confirmed_at')->nullable()->after('confirmed_by');
            $table->text('cancellation_reason')->nullable()->after('confirmed_at');
            $table->foreignId('cancelled_by')->nullable()->after('cancellation_reason')->constrained('users')->nullOnDelete();
            $table->dateTime('cancelled_at')->nullable()->after('cancelled_by');
            $table->foreignId('rescheduled_from_id')->nullable()->after('cancelled_at')->constrained('appointments')->nullOnDelete();
            $table->unsignedSmallInteger('slot_duration_minutes')->default(20)->after('rescheduled_from_id');
        });

        // Update status enum to include 'rescheduled' and 'no_show'
        DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('pending', 'confirmed', 'rescheduled', 'completed', 'cancelled', 'no_show') NOT NULL DEFAULT 'pending'");

        // 3. Appointment Histories / Audit Log Table
        Schema::create('appointment_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->string('action', 50)->index(); // requested, confirmed, rescheduled, cancelled, completed, no_show
            $table->string('previous_status', 30)->nullable();
            $table->string('new_status', 30)->index();
            $table->dateTime('previous_date')->nullable();
            $table->dateTime('new_date')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_histories');

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['confirmed_by']);
            $table->dropForeign(['cancelled_by']);
            $table->dropForeign(['rescheduled_from_id']);
            $table->dropColumn([
                'appointment_time',
                'guest_email',
                'confirmed_by',
                'confirmed_at',
                'cancellation_reason',
                'cancelled_by',
                'cancelled_at',
                'rescheduled_from_id',
                'slot_duration_minutes',
            ]);
        });

        DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('pending', 'confirmed', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");

        Schema::dropIfExists('doctor_schedules');
    }
};
