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
        Schema::create('operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_id')->constrained('admissions')->restrictOnDelete();
            $table->string('procedure_name', 191)->index();
            $table->dateTime('scheduled_at')->index();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->foreignId('primary_surgeon_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('assistant_surgeon_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('anesthetist_name', 191)->nullable();
            $table->longText('operation_notes')->nullable();
            $table->text('post_op_notes')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled')->index();
            $table->timestamps();
        });

        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->nullable()->constrained('visits')->nullOnDelete();
            $table->foreignId('admission_id')->nullable()->constrained('admissions')->nullOnDelete();
            $table->enum('referral_type', ['icu_ccu_emergency', 'specialist', 'tertiary_transfer'])->default('icu_ccu_emergency')->index();
            $table->string('facility_name', 191);
            $table->text('clinical_reason');
            $table->boolean('transport_arranged')->default(false);
            $table->enum('status', ['initiated', 'transferred', 'completed'])->default('initiated');
            $table->foreignId('authorized_by')->constrained('users')->restrictOnDelete();
            $table->dateTime('referred_at')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('operations');
    }
};
