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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('status', 32)->default('success')->change();
            $table->decimal('refunded_amount', 12, 2)->default(0.00)->after('amount');
            $table->text('notes')->nullable()->after('payment_date');
        });

        Schema::table('refunds', function (Blueprint $table) {
            $table->foreignId('bill_id')->nullable()->after('payment_id')->constrained('bills')->cascadeOnDelete();
            $table->string('refund_method', 32)->default('cash')->after('amount');
            $table->string('transaction_ref', 100)->nullable()->after('refund_method');
        });

        // Backfill bill_id on refunds from payments
        DB::statement("
            UPDATE refunds r
            INNER JOIN payments p ON r.payment_id = p.id
            SET r.bill_id = p.bill_id
            WHERE r.bill_id IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->dropForeign(['bill_id']);
            $table->dropColumn(['bill_id', 'refund_method', 'transaction_ref']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['refunded_amount', 'notes']);
        });
    }
};

