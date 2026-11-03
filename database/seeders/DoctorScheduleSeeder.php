<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\DoctorSchedule;
use App\Models\User;
use Illuminate\Database\Seeder;

class DoctorScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gynDept = Department::where('code', 'GYN')->first();
        $orthoDept = Department::where('code', 'ORTHO')->first();
        $emergDept = Department::where('code', 'EMERG')->first() ?? $gynDept;

        $drNasreen = User::where('email', 'doctor@meghnaclinic.com')->first();
        $drSamad = User::where('email', 'doctor.ortho@meghnaclinic.com')->first();
        $drFarhana = User::where('email', 'doctor.emerg@meghnaclinic.com')->first();

        // 1. Dr. Nasreen Sultana (Gynecology) - Morning OPD
        if ($drNasreen && $gynDept) {
            $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'saturday'];
            foreach ($days as $day) {
                DoctorSchedule::firstOrCreate(
                    [
                        'doctor_id' => $drNasreen->id,
                        'day_of_week' => $day,
                        'start_time' => '09:00:00',
                    ],
                    [
                        'department_id' => $gynDept->id,
                        'end_time' => '13:00:00',
                        'slot_duration_minutes' => 20,
                        'max_patients' => 15,
                        'room_number' => 'OPD Room 101',
                        'is_active' => true,
                    ]
                );
            }
        }

        // 2. Dr. M. A. Samad (Orthopedics) - Evening OPD
        if ($drSamad && $orthoDept) {
            $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'saturday'];
            foreach ($days as $day) {
                DoctorSchedule::firstOrCreate(
                    [
                        'doctor_id' => $drSamad->id,
                        'day_of_week' => $day,
                        'start_time' => '16:00:00',
                    ],
                    [
                        'department_id' => $orthoDept->id,
                        'end_time' => '20:00:00',
                        'slot_duration_minutes' => 20,
                        'max_patients' => 15,
                        'room_number' => 'OPD Room 102',
                        'is_active' => true,
                    ]
                );
            }
        }

        // 3. Dr. Farhana Yasmin (General / Emergency Consultation) - Morning / Afternoon
        if ($drFarhana && $emergDept) {
            $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
            foreach ($days as $day) {
                DoctorSchedule::firstOrCreate(
                    [
                        'doctor_id' => $drFarhana->id,
                        'day_of_week' => $day,
                        'start_time' => '10:00:00',
                    ],
                    [
                        'department_id' => $emergDept->id,
                        'end_time' => '14:00:00',
                        'slot_duration_minutes' => 20,
                        'max_patients' => 15,
                        'room_number' => 'OPD Room 103',
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
