<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $settings = [
            ['key' => 'clinic_name', 'value' => 'Meghna Clinic and Nursing Home', 'group' => 'general', 'description' => 'Official name of the hospital', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'clinic_address', 'value' => 'Shohid Minar Mor, Dinajpur, Bangladesh', 'group' => 'general', 'description' => 'Physical clinic location', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'clinic_hotline', 'value' => '+8801700000000', 'group' => 'general', 'description' => '24/7 Emergency contact number', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'total_bed_capacity', 'value' => '10', 'group' => 'clinical', 'description' => 'Initial total inpatient bed capacity', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'patient_code_prefix', 'value' => 'MCNH', 'group' => 'clinical', 'description' => 'Prefix for unique patient identifiers', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'currency_symbol', 'value' => 'BDT', 'group' => 'billing', 'description' => 'Default financial transaction currency', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'bed_billing_cycle', 'value' => '24_hours', 'group' => 'billing', 'description' => 'Calculation method for daily bed rates', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('system_settings')->insertOrIgnore($settings);
    }
}
