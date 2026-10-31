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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_id')->nullable()->constrained('admissions')->nullOnDelete();
            $table->foreignId('visit_id')->nullable()->constrained('visits')->nullOnDelete();
            $table->string('invoice_number', 32)->unique();
            $table->decimal('subtotal_amount', 12, 2)->default(0.00);
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->string('discount_reason', 191)->nullable();
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->decimal('paid_amount', 12, 2)->default(0.00);
            $table->decimal('due_amount', 12, 2)->default(0.00)->index();
            $table->enum('status', ['unpaid', 'partial', 'paid'])->default('unpaid')->index();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('bill_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained('bills')->cascadeOnDelete();
            $table->enum('item_type', ['bed', 'consultation', 'lab', 'surgery', 'service'])->index();
            $table->string('item_name', 191);
            $table->decimal('unit_price', 12, 2)->default(0.00);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('total_price', 12, 2)->default(0.00);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained('bills')->restrictOnDelete();
            $table->string('receipt_number', 32)->unique();
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['cash', 'card', 'mobile_banking'])->default('cash');
            $table->string('transaction_ref', 100)->nullable();
            $table->foreignId('received_by')->constrained('users')->restrictOnDelete();
            $table->enum('status', ['success', 'refunded'])->default('success')->index();
            $table->dateTime('payment_date')->index();
            $table->timestamps();
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->restrictOnDelete();
            $table->string('refund_voucher', 32)->unique();
            $table->decimal('amount', 12, 2);
            $table->text('reason');
            $table->foreignId('approved_by')->constrained('users')->restrictOnDelete();
            $table->dateTime('refunded_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('bill_items');
        Schema::dropIfExists('bills');
    }
};
