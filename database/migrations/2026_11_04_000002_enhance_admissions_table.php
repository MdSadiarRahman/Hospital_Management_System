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
        Schema::table('admissions', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('patient_id')->constrained('departments')->nullOnDelete();
            $table->text('notes')->nullable()->after('admission_reason');
            $table->foreignId('cancelled_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->dateTime('cancelled_at')->nullable()->after('cancelled_by');
            $table->text('cancellation_reason')->nullable()->after('cancelled_at');
        });

        // Expand status enum in admissions to include 'cancelled'
        DB::statement("ALTER TABLE admissions MODIFY COLUMN status ENUM('admitted', 'discharged', 'referred_external', 'cancelled') NOT NULL DEFAULT 'admitted'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE admissions MODIFY COLUMN status ENUM('admitted', 'discharged', 'referred_external') NOT NULL DEFAULT 'admitted'");

        Schema::table('admissions', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
            $table->dropColumn('notes');
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn(['cancelled_by', 'cancelled_at', 'cancellation_reason']);
        });
    }
};

