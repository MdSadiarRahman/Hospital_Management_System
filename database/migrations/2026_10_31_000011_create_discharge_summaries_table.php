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
        Schema::create('discharge_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_id')->unique()->constrained('admissions')->cascadeOnDelete();
            $table->text('discharge_diagnosis');
            $table->longText('treatment_given');
            $table->enum('condition_at_discharge', ['cured', 'improved', 'referred', 'dor'])->default('improved');
            $table->longText('discharge_medications');
            $table->text('follow_up_instructions')->nullable();
            $table->foreignId('authorized_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discharge_summaries');
    }
};
