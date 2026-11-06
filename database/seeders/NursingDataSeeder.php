<?php

namespace Database\Seeders;

use App\Models\Admission;
use App\Models\MedicationAdministration;
use App\Models\NursingNote;
use App\Models\User;
use Illuminate\Database\Seeder;

class NursingDataSeeder extends Seeder
{
    /**
     * Run the database seeds for nursing clinical demonstrations.
     */
    public function run(): void
    {
        $nurse = User::whereHas('roles', fn($q) => $q->where('name', 'nurse'))->first();
        if (!$nurse) {
            return;
        }

        $activeAdmissions = Admission::where('status', 'admitted')
            ->whereHas('currentBedAssignment')
            ->take(6)
            ->get();

        if ($activeAdmissions->isEmpty()) {
            return;
        }

        $sampleVitals = [
            [
                'temp' => 98.4,
                'bp' => '118/76',
                'pulse' => 74,
                'rr' => 16,
                'spo2' => 99.0,
                'obs' => 'Patient alert and oriented. Vital signs within normal limits. Tolerating oral diet well.',
            ],
            [
                'temp' => 101.4,
                'bp' => '132/86',
                'pulse' => 106,
                'rr' => 20,
                'spo2' => 96.5,
                'obs' => 'Febrile spike noted during morning rounds. Cold sponging initiated and notified on-duty emergency doctor.',
            ],
            [
                'temp' => 98.6,
                'bp' => '122/80',
                'pulse' => 80,
                'rr' => 18,
                'spo2' => 98.5,
                'obs' => 'Post-op dressing intact and clean. IV maintenance fluid infusing smoothly. Pain score 2/10.',
            ],
            [
                'temp' => 99.0,
                'bp' => '125/82',
                'pulse' => 88,
                'rr' => 18,
                'spo2' => 97.0,
                'obs' => 'Slight tachycardia on mobilization. Oxygen saturation stable on room air. Encouraged deep breathing exercises.',
            ],
            [
                'temp' => 98.2,
                'bp' => '115/75',
                'pulse' => 72,
                'rr' => 16,
                'spo2' => 99.5,
                'obs' => 'Patient sleeping comfortably. No acute bedside distress. Surgical drain output 30ml serous.',
            ],
            [
                'temp' => 97.8,
                'bp' => '110/70',
                'pulse' => 68,
                'rr' => 14,
                'spo2' => 98.0,
                'obs' => 'Pre-discharge clinical observation. Ambulated without assistance. Vitals completely stable.',
            ],
        ];

        $sampleMeds = [
            ['name' => 'Inj. Ceftriaxone', 'dose' => '1g IV', 'status' => 'administered', 'remarks' => 'Slow IV push over 5 mins. IV site clean.'],
            ['name' => 'Tab. Paracetamol', 'dose' => '500mg PO', 'status' => 'administered', 'remarks' => 'Administered for mild post-op discomfort.'],
            ['name' => 'Inj. Omeprazole', 'dose' => '40mg IV', 'status' => 'administered', 'remarks' => 'Pre-meal morning prophylaxis dose.'],
            ['name' => 'Tab. Cefuroxime', 'dose' => '500mg PO', 'status' => 'administered', 'remarks' => 'Morning oral antibiotic delivered with water.'],
            ['name' => 'Syrup Lactulose', 'dose' => '15ml PO', 'status' => 'omitted', 'remarks' => 'Omitted as patient had two loose bowel motions this morning.'],
            ['name' => 'Inj. Tramadol', 'dose' => '50mg IM', 'status' => 'refused', 'remarks' => 'Patient refused IM injection due to fear of needle pain.'],
        ];

        foreach ($activeAdmissions as $index => $adm) {
            $vital = $sampleVitals[$index % count($sampleVitals)];
            $med = $sampleMeds[$index % count($sampleMeds)];

            // Create past vitals note
            NursingNote::firstOrCreate(
                [
                    'admission_id' => $adm->id,
                    'recorded_at' => now()->subHours(2 + $index),
                ],
                [
                    'nurse_id' => $nurse->id,
                    'shift' => 'morning',
                    'temperature' => $vital['temp'],
                    'blood_pressure' => $vital['bp'],
                    'pulse_rate' => $vital['pulse'],
                    'respiratory_rate' => $vital['rr'],
                    'spo2' => $vital['spo2'],
                    'observations' => $vital['obs'],
                ]
            );

            // Create medication administration
            MedicationAdministration::firstOrCreate(
                [
                    'admission_id' => $adm->id,
                    'medicine_name' => $med['name'],
                    'administered_at' => now()->subHours(1 + $index),
                ],
                [
                    'prescription_item_id' => null,
                    'dose_given' => $med['dose'],
                    'nurse_id' => $nurse->id,
                    'status' => $med['status'],
                    'remarks' => $med['remarks'],
                ]
            );
        }
    }
}

