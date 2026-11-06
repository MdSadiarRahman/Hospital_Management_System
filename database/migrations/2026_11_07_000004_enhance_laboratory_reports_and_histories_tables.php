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
        // 1. Enhance lab_reports table
        Schema::table('lab_reports', function (Blueprint $table) {
            $table->string('file_path', 255)->nullable()->change();

            if (!Schema::hasColumn('lab_reports', 'source')) {
                $table->enum('source', ['internal', 'external'])->default('internal')->after('lab_order_item_id')->index();
            }

            if (!Schema::hasColumn('lab_reports', 'report_status')) {
                $table->enum('report_status', ['received', 'reviewed'])->default('received')->after('source')->index();
            }

            if (!Schema::hasColumn('lab_reports', 'lab_partner_id')) {
                $table->foreignId('lab_partner_id')->nullable()->after('report_status')->constrained('lab_partners')->nullOnDelete();
            }

            if (!Schema::hasColumn('lab_reports', 'external_reference_number')) {
                $table->string('external_reference_number', 100)->nullable()->after('lab_partner_id');
            }

            if (!Schema::hasColumn('lab_reports', 'uploaded_by')) {
                $table->foreignId('uploaded_by')->nullable()->after('external_reference_number')->constrained('users')->nullOnDelete();
            }

            if (!Schema::hasColumn('lab_reports', 'uploaded_at')) {
                $table->dateTime('uploaded_at')->nullable()->after('uploaded_by');
            }

            if (!Schema::hasColumn('lab_reports', 'original_file_name')) {
                $table->string('original_file_name', 255)->nullable()->after('file_path');
            }

            if (!Schema::hasColumn('lab_reports', 'file_type')) {
                $table->string('file_type', 50)->nullable()->after('original_file_name');
            }

            if (!Schema::hasColumn('lab_reports', 'file_size')) {
                $table->unsignedBigInteger('file_size')->nullable()->after('file_type');
            }

            if (!Schema::hasColumn('lab_reports', 'doctor_notes')) {
                $table->text('doctor_notes')->nullable()->after('findings_summary');
            }

            if (!Schema::hasColumn('lab_reports', 'is_critical')) {
                $table->boolean('is_critical')->default(false)->after('doctor_notes');
            }
        });

        // 2. Create lab_report_histories table
        if (!Schema::hasTable('lab_report_histories')) {
            Schema::create('lab_report_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lab_report_id')->constrained('lab_reports')->cascadeOnDelete();
                $table->string('action', 50)->index(); // result_entered, report_uploaded, doctor_reviewed, amended
                $table->foreignId('changed_by')->constrained('users')->restrictOnDelete();
                $table->string('previous_status', 50)->nullable();
                $table->string('new_status', 50);
                $table->text('notes')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_report_histories');

        Schema::table('lab_reports', function (Blueprint $table) {
            $columnsToDrop = [
                'source',
                'report_status',
                'lab_partner_id',
                'external_reference_number',
                'uploaded_by',
                'uploaded_at',
                'original_file_name',
                'file_type',
                'file_size',
                'doctor_notes',
                'is_critical',
            ];

            foreach ($columnsToDrop as $col) {
                if (Schema::hasColumn('lab_reports', $col)) {
                    if ($col === 'lab_partner_id') {
                        $table->dropForeign(['lab_partner_id']);
                    }
                    if ($col === 'uploaded_by') {
                        $table->dropForeign(['uploaded_by']);
                    }
                    $table->dropColumn($col);
                }
            }
        });
    }
};

