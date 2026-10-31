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
        // Multi-Department Allocation Pivot Table
        Schema::create('department_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_profile_id')->constrained('staff_profiles')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->string('role_in_department', 100)->nullable();
            $table->timestamps();

            $table->unique(['staff_profile_id', 'department_id'], 'dept_staff_unique');
        });

        // Shift Schedules & History Table
        Schema::create('shift_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_profile_id')->constrained('staff_profiles')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $table->date('shift_date')->index();
            $table->enum('shift_type', ['morning', 'evening', 'night'])->index();
            $table->time('start_time')->default('08:00:00');
            $table->time('end_time')->default('16:00:00');
            $table->enum('status', ['scheduled', 'completed', 'swapped', 'absent', 'cancelled'])
                ->default('scheduled')
                ->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Prevent duplicate incompatible overlapping shifts for the same staff on the same date
            $table->unique(['staff_profile_id', 'shift_date', 'shift_type'], 'staff_shift_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_schedules');
        Schema::dropIfExists('department_staff');
    }
};
