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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->nullable()->constrained('patients')->nullOnDelete();
            $table->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('guest_name', 191)->nullable();
            $table->string('guest_phone', 20)->nullable();
            $table->dateTime('appointment_date')->index();
            $table->unsignedInteger('serial_number')->nullable();
            $table->enum('type', ['online', 'walk_in', 'follow_up'])->default('walk_in');
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending')->index();
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->string('visit_number', 32)->unique();
            $table->dateTime('visit_date')->index();
            $table->enum('visit_type', ['regular', 'emergency', 'follow_up'])->default('regular')->index();
            $table->enum('triage_level', ['normal', 'priority', 'critical'])->default('normal');
            $table->enum('status', ['waiting', 'in_consultation', 'completed', 'admitted', 'referred_out'])->default('waiting')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
        Schema::dropIfExists('appointments');
    }
};
