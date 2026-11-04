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
        Schema::table('beds', function (Blueprint $table) {
            $table->string('room_number', 32)->nullable()->after('bed_number');
            $table->text('description')->nullable()->after('daily_charge');
            $table->foreignId('department_id')->nullable()->change();
        });

        Schema::create('bed_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bed_id')->constrained('beds')->cascadeOnDelete();
            $table->enum('from_status', ['available', 'occupied', 'reserved', 'cleaning', 'maintenance'])->nullable();
            $table->enum('to_status', ['available', 'occupied', 'reserved', 'cleaning', 'maintenance']);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['bed_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bed_status_histories');

        Schema::table('beds', function (Blueprint $table) {
            $table->dropColumn(['room_number', 'description']);
            $table->foreignId('department_id')->nullable(false)->change();
        });
    }
};

