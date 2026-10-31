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
        Schema::table('staff_profiles', function (Blueprint $table) {
            $table->string('employee_id', 32)->nullable()->unique()->after('user_id');
            $table->enum('employment_type', ['full_time', 'part_time', 'visiting_consultant', 'contractual'])
                ->default('full_time')
                ->after('shift');
            $table->date('joining_date')->nullable()->after('employment_type');
            $table->string('qualification', 191)->nullable()->after('joining_date');
            $table->decimal('salary', 10, 2)->nullable()->after('qualification');
            $table->string('nid_number', 30)->nullable()->after('salary');
            $table->string('emergency_contact_name', 100)->nullable()->after('nid_number');
            $table->string('emergency_contact_phone', 20)->nullable()->after('emergency_contact_name');
            $table->text('address')->nullable()->after('emergency_contact_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'employee_id',
                'employment_type',
                'joining_date',
                'qualification',
                'salary',
                'nid_number',
                'emergency_contact_name',
                'emergency_contact_phone',
                'address',
            ]);
        });
    }
};
