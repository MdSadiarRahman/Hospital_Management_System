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
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->string('prescription_number', 50)->nullable()->unique()->after('id');
            $table->enum('status', ['draft', 'approved'])->default('draft')->index()->after('follow_up_date');
            $table->dateTime('approved_at')->nullable()->after('status');
            $table->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
        });

        // Backfill existing prescriptions with unique prescription_number and status
        $prescriptions = DB::table('prescriptions')->get();
        foreach ($prescriptions as $p) {
            $cons = DB::table('consultations')->where('id', $p->consultation_id)->first();
            $isFinal = $cons && $cons->status === 'finalized';
            $status = $isFinal ? 'approved' : 'draft';
            $approvedAt = $isFinal ? ($cons->finalized_at ?? $p->created_at ?? now()) : null;
            $approvedBy = $isFinal ? $cons->doctor_id : null;
            $datePrefix = $p->created_at ? date('Ymd', strtotime($p->created_at)) : date('Ymd');
            $rxNumber = 'RX-' . $datePrefix . '-' . str_pad($p->id, 5, '0', STR_PAD_LEFT);

            DB::table('prescriptions')->where('id', $p->id)->update([
                'prescription_number' => $rxNumber,
                'status' => $status,
                'approved_at' => $approvedAt,
                'approved_by' => $approvedBy,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['approved_by', 'approved_at', 'status', 'prescription_number']);
        });
    }
};

