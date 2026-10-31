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
        Schema::create('treatment_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_id')->constrained('admissions')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users')->restrictOnDelete();
            $table->string('plan_title', 191);
            $table->text('details');
            $table->enum('status', ['active', 'completed', 'discontinued'])->default('active')->index();
            $table->timestamps();
        });

        Schema::create('treatment_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treatment_plan_id')->constrained('treatment_plans')->cascadeOnDelete();
            $table->foreignId('performed_by')->constrained('users')->restrictOnDelete();
            $table->string('procedure_performed', 191);
            $table->text('outcome_notes')->nullable();
            $table->dateTime('performed_at')->index();
            $table->timestamps();
        });

        Schema::create('nursing_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_id')->constrained('admissions')->cascadeOnDelete();
            $table->foreignId('nurse_id')->constrained('users')->restrictOnDelete();
            $table->enum('shift', ['morning', 'evening', 'night'])->default('morning');
            $table->decimal('temperature', 4, 1)->nullable();
            $table->string('blood_pressure', 15)->nullable();
            $table->unsignedSmallInteger('pulse_rate')->nullable();
            $table->unsignedSmallInteger('respiratory_rate')->nullable();
            $table->decimal('spo2', 4, 1)->nullable();
            $table->text('observations');
            $table->dateTime('recorded_at')->index();
            $table->timestamps();
        });

        Schema::create('medication_administrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_id')->constrained('admissions')->cascadeOnDelete();
            $table->foreignId('prescription_item_id')->nullable()->constrained('prescription_items')->nullOnDelete();
            $table->string('medicine_name', 191);
            $table->string('dose_given', 50);
            $table->foreignId('nurse_id')->constrained('users')->restrictOnDelete();
            $table->enum('status', ['administered', 'omitted', 'refused'])->default('administered')->index();
            $table->text('remarks')->nullable();
            $table->dateTime('administered_at')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medication_administrations');
        Schema::dropIfExists('nursing_notes');
        Schema::dropIfExists('treatment_records');
        Schema::dropIfExists('treatment_plans');
    }
};
