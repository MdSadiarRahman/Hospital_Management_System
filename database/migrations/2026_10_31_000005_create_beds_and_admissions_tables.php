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
        Schema::create('beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $table->string('bed_number', 32)->unique();
            $table->enum('category', ['general', 'department', 'cabin', 'post_op'])->default('department');
            $table->decimal('daily_charge', 12, 2)->default(0.00);
            $table->enum('status', ['available', 'occupied', 'reserved', 'cleaning', 'maintenance'])->default('available')->index();
            $table->timestamps();
        });

        Schema::create('admissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained('visits')->restrictOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->string('admission_number', 32)->unique();
            $table->dateTime('admission_date')->index();
            $table->dateTime('discharge_date')->nullable();
            $table->foreignId('attending_doctor_id')->constrained('users')->restrictOnDelete();
            $table->text('admission_reason');
            $table->boolean('clinical_clearance')->default(false)->index();
            $table->dateTime('clinical_clearance_at')->nullable();
            $table->foreignId('clinical_clearance_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('financial_clearance')->default(false)->index();
            $table->dateTime('financial_clearance_at')->nullable();
            $table->foreignId('financial_clearance_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['admitted', 'discharged', 'referred_external'])->default('admitted')->index();
            $table->timestamps();
        });

        Schema::create('bed_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_id')->constrained('admissions')->cascadeOnDelete();
            $table->foreignId('bed_id')->constrained('beds')->restrictOnDelete();
            $table->dateTime('assigned_at')->index();
            $table->dateTime('released_at')->nullable()->index();
            $table->text('transfer_reason')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Composite index to facilitate fast queries on active bed occupants
            $table->index(['bed_id', 'released_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bed_assignments');
        Schema::dropIfExists('admissions');
        Schema::dropIfExists('beds');
    }
};
