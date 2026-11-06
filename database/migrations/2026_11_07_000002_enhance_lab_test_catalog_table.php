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
        Schema::table('lab_test_catalog', function (Blueprint $table) {
            if (!Schema::hasColumn('lab_test_catalog', 'description')) {
                $table->text('description')->nullable()->after('category');
            }
            if (!Schema::hasColumn('lab_test_catalog', 'sample_type')) {
                $table->string('sample_type', 100)->nullable()->default('Blood')->after('standard_charge');
            }
            if (!Schema::hasColumn('lab_test_catalog', 'turnaround_time')) {
                $table->string('turnaround_time', 50)->nullable()->default('4 hours')->after('sample_type');
            }
            if (!Schema::hasColumn('lab_test_catalog', 'instructions')) {
                $table->text('instructions')->nullable()->after('turnaround_time');
            }
            if (!Schema::hasColumn('lab_test_catalog', 'lab_partner_id')) {
                $table->foreignId('lab_partner_id')->nullable()->after('default_source')->constrained('lab_partners')->nullOnDelete();
            }
        });

        // Modify default_source enum to allow 'both' if not already present
        DB::statement("ALTER TABLE `lab_test_catalog` MODIFY COLUMN `default_source` ENUM('internal', 'external', 'both') NOT NULL DEFAULT 'internal'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lab_test_catalog', function (Blueprint $table) {
            if (Schema::hasColumn('lab_test_catalog', 'lab_partner_id')) {
                $table->dropForeign(['lab_partner_id']);
                $table->dropColumn('lab_partner_id');
            }
            if (Schema::hasColumn('lab_test_catalog', 'instructions')) {
                $table->dropColumn('instructions');
            }
            if (Schema::hasColumn('lab_test_catalog', 'turnaround_time')) {
                $table->dropColumn('turnaround_time');
            }
            if (Schema::hasColumn('lab_test_catalog', 'sample_type')) {
                $table->dropColumn('sample_type');
            }
            if (Schema::hasColumn('lab_test_catalog', 'description')) {
                $table->dropColumn('description');
            }
        });

        DB::statement("ALTER TABLE `lab_test_catalog` MODIFY COLUMN `default_source` ENUM('internal', 'external') NOT NULL DEFAULT 'internal'");
    }
};

