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
        Schema::create('lab_partners', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191)->unique();
            $table->string('code', 20)->unique();
            $table->string('contact_person', 100)->nullable();
            $table->string('phone', 20);
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('lab_test_catalog', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191)->index();
            $table->string('code', 20)->unique();
            $table->string('category', 50)->index();
            $table->decimal('standard_charge', 12, 2)->default(0.00);
            $table->enum('default_source', ['internal', 'external'])->default('internal');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('lab_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_id')->constrained('consultations')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->string('order_number', 32)->unique();
            $table->enum('source', ['internal', 'external'])->default('internal')->index();
            $table->foreignId('lab_partner_id')->nullable()->constrained('lab_partners')->nullOnDelete();
            $table->enum('status', ['pending', 'sample_collected', 'processing', 'completed', 'cancelled'])->default('pending')->index();
            $table->text('clinical_indication')->nullable();
            $table->timestamps();
        });

        Schema::create('lab_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_order_id')->constrained('lab_orders')->cascadeOnDelete();
            $table->foreignId('lab_test_id')->constrained('lab_test_catalog')->restrictOnDelete();
            $table->string('test_name', 191);
            $table->decimal('test_charge', 12, 2)->default(0.00);
            $table->enum('status', ['pending', 'sample_collected', 'completed', 'cancelled'])->default('pending')->index();
            $table->timestamps();
        });

        Schema::create('lab_samples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_order_id')->constrained('lab_orders')->cascadeOnDelete();
            $table->string('sample_type', 50);
            $table->string('barcode', 50)->unique();
            $table->dateTime('collected_at');
            $table->foreignId('collected_by')->constrained('users')->restrictOnDelete();
            $table->enum('status', ['collected', 'received', 'rejected'])->default('collected');
            $table->timestamps();
        });

        Schema::create('lab_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_order_item_id')->unique()->constrained('lab_order_items')->cascadeOnDelete();
            $table->string('file_path', 255);
            $table->text('findings_summary')->nullable();
            $table->json('parameters')->nullable();
            $table->enum('review_status', ['pending_review', 'reviewed'])->default('pending_review')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_reports');
        Schema::dropIfExists('lab_samples');
        Schema::dropIfExists('lab_order_items');
        Schema::dropIfExists('lab_orders');
        Schema::dropIfExists('lab_test_catalog');
        Schema::dropIfExists('lab_partners');
    }
};
