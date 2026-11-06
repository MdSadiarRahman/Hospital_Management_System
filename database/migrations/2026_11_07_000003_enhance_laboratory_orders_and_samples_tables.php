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
        // 1. Enhance lab_orders
        Schema::table('lab_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('lab_orders', 'doctor_id')) {
                $table->foreignId('doctor_id')->nullable()->after('patient_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('lab_orders', 'priority')) {
                $table->enum('priority', ['routine', 'urgent', 'stat'])->default('routine')->after('status')->index();
            }
            if (!Schema::hasColumn('lab_orders', 'total_charge')) {
                $table->decimal('total_charge', 12, 2)->default(0.00)->after('priority');
            }
        });

        // 2. Enhance lab_order_items
        Schema::table('lab_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('lab_order_items', 'lab_sample_id')) {
                $table->foreignId('lab_sample_id')->nullable()->after('lab_test_id')->constrained('lab_samples')->nullOnDelete();
            }
            if (!Schema::hasColumn('lab_order_items', 'instructions')) {
                $table->text('instructions')->nullable()->after('status');
            }
        });

        // Ensure status column on lab_order_items includes 'processing'
        DB::statement("ALTER TABLE `lab_order_items` MODIFY COLUMN `status` ENUM('pending', 'sample_collected', 'processing', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");

        // 3. Enhance lab_samples
        Schema::table('lab_samples', function (Blueprint $table) {
            if (!Schema::hasColumn('lab_samples', 'notes')) {
                $table->text('notes')->nullable()->after('collected_by');
            }
            if (!Schema::hasColumn('lab_samples', 'received_at')) {
                $table->dateTime('received_at')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('lab_samples', 'received_by')) {
                $table->foreignId('received_by')->nullable()->after('received_at')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('lab_samples', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('received_by');
            }
        });

        // 4. Create lab_order_status_histories
        if (!Schema::hasTable('lab_order_status_histories')) {
            Schema::create('lab_order_status_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lab_order_id')->constrained('lab_orders')->cascadeOnDelete();
                $table->string('from_status', 50)->nullable();
                $table->string('to_status', 50)->index();
                $table->foreignId('changed_by')->constrained('users')->restrictOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_order_status_histories');

        Schema::table('lab_samples', function (Blueprint $table) {
            if (Schema::hasColumn('lab_samples', 'received_by')) {
                $table->dropForeign(['received_by']);
                $table->dropColumn('received_by');
            }
            if (Schema::hasColumn('lab_samples', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
            if (Schema::hasColumn('lab_samples', 'received_at')) {
                $table->dropColumn('received_at');
            }
            if (Schema::hasColumn('lab_samples', 'notes')) {
                $table->dropColumn('notes');
            }
        });

        Schema::table('lab_order_items', function (Blueprint $table) {
            if (Schema::hasColumn('lab_order_items', 'lab_sample_id')) {
                $table->dropForeign(['lab_sample_id']);
                $table->dropColumn('lab_sample_id');
            }
            if (Schema::hasColumn('lab_order_items', 'instructions')) {
                $table->dropColumn('instructions');
            }
        });

        Schema::table('lab_orders', function (Blueprint $table) {
            if (Schema::hasColumn('lab_orders', 'doctor_id')) {
                $table->dropForeign(['doctor_id']);
                $table->dropColumn('doctor_id');
            }
            if (Schema::hasColumn('lab_orders', 'total_charge')) {
                $table->dropColumn('total_charge');
            }
            if (Schema::hasColumn('lab_orders', 'priority')) {
                $table->dropColumn('priority');
            }
        });
    }
};

