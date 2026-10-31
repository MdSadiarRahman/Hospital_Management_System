<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentAndBedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $departments = [
            ['name' => 'Emergency', 'code' => 'EMERG', 'description' => '24/7 Emergency medical assessment and stabilization', 'is_clinical' => true, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Gynecology', 'code' => 'GYN', 'description' => 'Maternal, obstetrics, and gynecological care', 'is_clinical' => true, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Orthopedic', 'code' => 'ORTHO', 'description' => 'Bone, joint, and musculoskeletal trauma care', 'is_clinical' => true, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Surgery', 'code' => 'SURG', 'description' => 'General surgery and post-operative recovery', 'is_clinical' => true, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('departments')->insertOrIgnore($departments);

        $deptMap = DB::table('departments')->pluck('id', 'code');

        $beds = [
            ['department_id' => $deptMap['EMERG'], 'bed_number' => 'EM-01', 'category' => 'department', 'daily_charge' => 1500.00, 'status' => 'available', 'created_at' => $now, 'updated_at' => $now],
            ['department_id' => $deptMap['EMERG'], 'bed_number' => 'EM-02', 'category' => 'department', 'daily_charge' => 1500.00, 'status' => 'available', 'created_at' => $now, 'updated_at' => $now],
            ['department_id' => $deptMap['GYN'], 'bed_number' => 'GYN-01', 'category' => 'department', 'daily_charge' => 1200.00, 'status' => 'available', 'created_at' => $now, 'updated_at' => $now],
            ['department_id' => $deptMap['GYN'], 'bed_number' => 'GYN-02', 'category' => 'department', 'daily_charge' => 1200.00, 'status' => 'available', 'created_at' => $now, 'updated_at' => $now],
            ['department_id' => $deptMap['SURG'], 'bed_number' => 'SUR-01', 'category' => 'department', 'daily_charge' => 1500.00, 'status' => 'available', 'created_at' => $now, 'updated_at' => $now],
            ['department_id' => $deptMap['SURG'], 'bed_number' => 'SUR-02', 'category' => 'department', 'daily_charge' => 1500.00, 'status' => 'available', 'created_at' => $now, 'updated_at' => $now],
            ['department_id' => $deptMap['SURG'], 'bed_number' => 'GEN-01', 'category' => 'general', 'daily_charge' => 800.00, 'status' => 'available', 'created_at' => $now, 'updated_at' => $now],
            ['department_id' => $deptMap['GYN'], 'bed_number' => 'GEN-02', 'category' => 'general', 'daily_charge' => 800.00, 'status' => 'available', 'created_at' => $now, 'updated_at' => $now],
            ['department_id' => $deptMap['ORTHO'], 'bed_number' => 'GEN-03', 'category' => 'general', 'daily_charge' => 800.00, 'status' => 'available', 'created_at' => $now, 'updated_at' => $now],
            ['department_id' => $deptMap['ORTHO'], 'bed_number' => 'GEN-04', 'category' => 'general', 'daily_charge' => 800.00, 'status' => 'available', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('beds')->insertOrIgnore($beds);
    }
}
