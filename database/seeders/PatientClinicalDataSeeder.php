<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\Diagnosis;
use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\LabReport;
use App\Models\LabTestCatalog;
use App\Models\Patient;
use App\Models\PatientContact;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\SystemNotification;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Seeder;

class PatientClinicalDataSeeder extends Seeder
{
    /**
     * Run the database seeds for patient clinical demonstration data.
     */
    public function run(): void
    {
        $patientUser = User::where('email', 'patient@meghnaclinic.com')->first();
        $doctor = User::where('email', 'doctor@meghnaclinic.com')->first();
        $receptionist = User::where('email', 'reception@meghnaclinic.com')->first();
        $billingStaff = User::where('email', 'billing@meghnaclinic.com')->first();
        $gynDept = Department::where('code', 'GYN')->first();

        if (!$patientUser || !$doctor) {
            return;
        }

        // 1. Create or update linked clinical Patient record for Rokeya Akhter
        $clinicalPatient = Patient::firstOrCreate(
            ['patient_code' => 'MCNH-2026-0042'],
            [
                'user_id' => $patientUser->id,
                'name' => $patientUser->name,
                'date_of_birth' => '1992-06-15',
                'gender' => 'female',
                'blood_group' => 'O+',
                'phone' => $patientUser->phone,
                'nid_or_birth_cert' => '1992271100223344',
                'address' => 'Shohid Minar Mor, Dinajpur Sadar, Dinajpur',
                'is_verified' => true,
                'verified_by' => $receptionist?->id,
                'verified_at' => now()->subDays(10),
            ]
        );

        if (!$clinicalPatient->user_id) {
            $clinicalPatient->update([
                'user_id' => $patientUser->id,
                'is_verified' => true,
                'verified_by' => $receptionist?->id,
                'verified_at' => now()->subDays(10),
            ]);
        }

        // 2. Emergency Contact
        PatientContact::firstOrCreate(
            ['patient_id' => $clinicalPatient->id, 'name' => 'Md. Rafiqul Islam'],
            [
                'relationship' => 'Husband',
                'phone' => '+8801712000042',
                'is_primary' => true,
            ]
        );

        // 3. Upcoming Appointment
        Appointment::firstOrCreate(
            [
                'patient_id' => $clinicalPatient->id,
                'appointment_date' => now()->addDays(2)->setTime(10, 30),
            ],
            [
                'department_id' => $gynDept?->id,
                'doctor_id' => $doctor->id,
                'serial_number' => 5,
                'type' => 'follow_up',
                'status' => 'confirmed',
                'reason' => 'Second trimester routine ultrasound review & blood pressure check.',
            ]
        );

        // 4. Past Appointment & Visit
        $pastAppointment = Appointment::firstOrCreate(
            [
                'patient_id' => $clinicalPatient->id,
                'appointment_date' => now()->subDays(5)->setTime(11, 00),
            ],
            [
                'department_id' => $gynDept?->id,
                'doctor_id' => $doctor->id,
                'serial_number' => 8,
                'type' => 'walk_in',
                'status' => 'confirmed',
                'reason' => 'First trimester antenatal checkup & general health check.',
            ]
        );

        $pastVisit = Visit::firstOrCreate(
            ['visit_number' => 'VST-2026-0042'],
            [
                'patient_id' => $clinicalPatient->id,
                'appointment_id' => $pastAppointment->id,
                'visit_date' => now()->subDays(5)->setTime(11, 15),
                'visit_type' => 'regular',
                'triage_level' => 'normal',
                'status' => 'completed',
            ]
        );

        // 5. Consultation & Diagnosis
        $consultation = Consultation::firstOrCreate(
            ['visit_id' => $pastVisit->id],
            [
                'doctor_id' => $doctor->id,
                'chief_complaints' => 'Mild morning nausea and fatigue; routine prenatal assessment.',
                'clinical_notes' => 'General physical exam satisfactory. BP 115/75 mmHg, Pulse 78 bpm. Fundal height consistent with dates. Advised high protein diet and iron supplements.',
                'status' => 'finalized',
                'finalized_at' => now()->subDays(5)->setTime(11, 45),
            ]
        );

        Diagnosis::firstOrCreate(
            ['consultation_id' => $consultation->id, 'condition_name' => 'Supervision of normal pregnancy, second trimester'],
            [
                'icd_code' => 'Z34.8',
                'diagnosis_type' => 'provisional',
            ]
        );

        // 6. Prescription & Prescription Items
        $prescription = Prescription::firstOrCreate(
            ['consultation_id' => $consultation->id],
            [
                'advice' => 'Drink at least 2.5 liters of clean filtered water daily. Consume green leafy vegetables and seasonal fruits. Avoid strenuous physical exertion. Walk gently 20 mins every morning.',
                'follow_up_date' => now()->addDays(25)->toDateString(),
            ]
        );

        PrescriptionItem::firstOrCreate(
            ['prescription_id' => $prescription->id, 'medicine_name' => 'Tab. Fefol-Z (Iron + Folic Acid)'],
            [
                'dosage' => '1 tab',
                'frequency' => '1+0+1',
                'duration' => '30 days',
                'instructions' => 'Take orally after meals with fresh water or orange juice.',
            ]
        );

        PrescriptionItem::firstOrCreate(
            ['prescription_id' => $prescription->id, 'medicine_name' => 'Tab. Calbo-D (Calcium 500mg + Vitamin D3)'],
            [
                'dosage' => '1 tab',
                'frequency' => '0+0+1',
                'duration' => '30 days',
                'instructions' => 'Take once daily after dinner before bedtime.',
            ]
        );

        // 7. Laboratory Test Catalog & Order
        $cbcTest = LabTestCatalog::firstOrCreate(
            ['code' => 'CBC01'],
            [
                'name' => 'Complete Blood Count (CBC) with ESR',
                'category' => 'Hematology',
                'standard_charge' => 450.00,
                'default_source' => 'internal',
                'is_active' => true,
            ]
        );

        $labOrder = LabOrder::firstOrCreate(
            ['order_number' => 'LAB-2026-0042'],
            [
                'consultation_id' => $consultation->id,
                'patient_id' => $clinicalPatient->id,
                'source' => 'internal',
                'status' => 'completed',
                'clinical_indication' => 'Routine prenatal hematology baseline screening.',
            ]
        );

        $orderItem = LabOrderItem::firstOrCreate(
            ['lab_order_id' => $labOrder->id, 'lab_test_id' => $cbcTest->id],
            [
                'test_name' => 'Complete Blood Count (CBC) with ESR',
                'test_charge' => 450.00,
                'status' => 'completed',
            ]
        );

        LabReport::firstOrCreate(
            ['lab_order_item_id' => $orderItem->id],
            [
                'file_path' => 'lab_reports/LAB-2026-0042_CBC.pdf',
                'findings_summary' => 'Hemoglobin 11.2 g/dL (reference: 11.5 - 15.0). RBC 4.1 million/mcL. Platelet count 245,000/mcL. Total WBC 7,800/mcL. Mild physiological gestational anemia noted.',
                'parameters' => [
                    'Hemoglobin' => '11.2 g/dL',
                    'Total RBC Count' => '4.1 M/mcL',
                    'Total WBC Count' => '7,800 /mcL',
                    'Platelet Count' => '245,000 /mcL',
                    'ESR (Westergren)' => '18 mm in 1st hour',
                    'Hematocrit (PCV)' => '34.5 %',
                ],
                'review_status' => 'reviewed',
                'reviewed_by' => $doctor->id,
                'reviewed_at' => now()->subDays(4)->setTime(16, 30),
            ]
        );

        // 8. Billing & Payments
        $bill = Bill::firstOrCreate(
            ['invoice_number' => 'INV-2026-0042'],
            [
                'visit_id' => $pastVisit->id,
                'subtotal_amount' => 1050.00,
                'discount_amount' => 0.00,
                'discount_reason' => null,
                'total_amount' => 1050.00,
                'paid_amount' => 1050.00,
                'due_amount' => 0.00,
                'status' => 'paid',
                'created_by' => $billingStaff?->id ?? $receptionist?->id,
            ]
        );

        BillItem::firstOrCreate(
            ['bill_id' => $bill->id, 'item_name' => 'Doctor Consultation (Gynecology & Obstetrics)'],
            [
                'item_type' => 'consultation',
                'unit_price' => 600.00,
                'quantity' => 1,
                'total_price' => 600.00,
            ]
        );

        BillItem::firstOrCreate(
            ['bill_id' => $bill->id, 'item_name' => 'Complete Blood Count (CBC) with ESR'],
            [
                'item_type' => 'lab',
                'unit_price' => 450.00,
                'quantity' => 1,
                'total_price' => 450.00,
            ]
        );

        Payment::firstOrCreate(
            ['receipt_number' => 'REC-2026-0042'],
            [
                'bill_id' => $bill->id,
                'amount' => 1050.00,
                'payment_method' => 'mobile_banking',
                'transaction_ref' => 'BK78219034X',
                'received_by' => $billingStaff?->id ?? $receptionist?->id,
                'status' => 'success',
                'payment_date' => now()->subDays(5)->setTime(12, 10),
            ]
        );

        // 9. Notifications for Patient User
        SystemNotification::firstOrCreate(
            ['user_id' => $patientUser->id, 'title' => 'Upcoming Appointment Confirmed'],
            [
                'message' => 'Your antenatal follow-up appointment with Dr. Nasreen Sultana is confirmed for ' . now()->addDays(2)->format('d M Y, h:i A') . ' (Serial #05).',
                'type' => 'appointment',
                'action_url' => route('patient.dashboard') . '#appointments-section',
                'is_read' => false,
                'read_at' => null,
            ]
        );

        SystemNotification::firstOrCreate(
            ['user_id' => $patientUser->id, 'title' => 'Diagnostic Lab Report Available'],
            [
                'message' => 'Your Complete Blood Count (CBC) report has been finalized and reviewed by Dr. Nasreen Sultana. You can view findings online.',
                'type' => 'lab_ready',
                'action_url' => route('patient.dashboard') . '#reports-section',
                'is_read' => false,
                'read_at' => null,
            ]
        );

        SystemNotification::firstOrCreate(
            ['user_id' => $patientUser->id, 'title' => 'Payment Receipt #REC-2026-0042'],
            [
                'message' => 'Payment of ৳ 1,050.00 for Invoice #INV-2026-0042 was successfully recorded via bKash (Ref: BK78219034X).',
                'type' => 'billing',
                'action_url' => route('patient.dashboard') . '#billing-section',
                'is_read' => true,
                'read_at' => now()->subDays(5),
            ]
        );
    }
}
