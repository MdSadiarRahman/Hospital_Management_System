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
        // 1. Service Categories
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // 2. Services Catalog
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('service_categories')->restrictOnDelete();
            $table->string('name', 191);
            $table->string('code', 50)->unique();
            $table->decimal('price', 12, 2)->default(0.00);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // 3. Enhance Bills
        Schema::table('bills', function (Blueprint $table) {
            $table->foreignId('patient_id')->nullable()->after('id')->constrained('patients')->nullOnDelete();
            $table->dateTime('bill_date')->nullable()->after('invoice_number');
            $table->foreignId('discount_authorized_by')->nullable()->after('discount_reason')->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable()->after('status');
        });

        // Backfill patient_id and bill_date for existing bills
        DB::statement("
            UPDATE bills b
            LEFT JOIN visits v ON b.visit_id = v.id
            LEFT JOIN admissions a ON b.admission_id = a.id
            SET b.patient_id = COALESCE(v.patient_id, a.patient_id),
                b.bill_date = b.created_at
            WHERE b.patient_id IS NULL OR b.bill_date IS NULL
        ");

        // 4. Enhance Bill Items
        Schema::table('bill_items', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable()->after('bill_id')->constrained('services')->nullOnDelete();
            $table->string('service_code', 50)->nullable()->after('service_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bill_items', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropColumn(['service_id', 'service_code']);
        });

        Schema::table('bills', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
            $table->dropForeign(['discount_authorized_by']);
            $table->dropColumn(['patient_id', 'bill_date', 'discount_authorized_by', 'notes']);
        });

        Schema::dropIfExists('services');
        Schema::dropIfExists('service_categories');
    }
};

