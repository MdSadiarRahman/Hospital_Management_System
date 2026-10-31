<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Bed;
use App\Models\BedAssignment;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\Diagnosis;
use App\Models\DischargeSummary;
use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\LabPartner;
use App\Models\LabReport;
use App\Models\LabSample;
use App\Models\LabTestCatalog;
use App\Models\MedicationAdministration;
use App\Models\NursingNote;
use App\Models\Patient;
use App\Models\PatientContact;
use App\Models\Payment;
use App\Models\Permission;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Refund;
use App\Models\Role;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ModelRelationshipTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_roles_and_permissions_relationship(): void
    {
        $user = User::create([
            'name' => 'Dr. Test Physician',
            'email' => 'physician_test@meghnaclinic.com',
            'phone' => '+8801700000001',
            'password' => 'secret123',
            'user_type' => 'staff',
            'is_active' => true,
        ]);

        $role = Role::where('name', 'doctor')->first();
        $this->assertNotNull($role);

        $user->roles()->attach($role->id);

        $this->assertTrue($user->fresh()->hasRole('doctor'));
        $this->assertTrue($user->fresh()->roles->contains('name', 'doctor'));
        $this->assertTrue($role->users->contains('id', $user->id));
        $this->assertTrue($role->permissions->isNotEmpty());
    }

    public function test_department_and_beds_relationship(): void
    {
        $department = Department::where('code', 'EMERG')->first();
        $this->assertNotNull($department);
        $this->assertTrue($department->is_clinical);

        $beds = $department->beds;
        $this->assertNotEmpty($beds);
        $this->assertEquals($department->id, $beds->first()->department_id);
        $this->assertEquals('EMERG', $beds->first()->department->code);
    }

    public function test_patient_contacts_and_visits_relationship(): void
    {
        $patient = Patient::create([
            'patient_code' => 'MCNH-TEST-0001',
            'name' => 'Fictional Patient',
            'phone' => '+8801711111111',
            'gender' => 'female',
            'date_of_birth' => '1995-05-15',
            'blood_group' => 'O+',
        ]);

        $contact = $patient->contacts()->create([
            'name' => 'Fictional Guardian',
            'relationship' => 'Spouse',
            'phone' => '+8801722222222',
            'is_primary' => true,
        ]);

        $this->assertEquals($patient->id, $contact->patient->id);
        $this->assertTrue($patient->fresh()->contacts->contains('id', $contact->id));

        $visit = $patient->visits()->create([
            'visit_number' => 'VIS-TEST-0001',
            'visit_date' => now(),
            'visit_type' => 'emergency',
            'status' => 'waiting',
        ]);

        $this->assertEquals($patient->id, $visit->patient->id);
        $this->assertTrue($patient->fresh()->visits->contains('id', $visit->id));
    }

    public function test_clinical_consultation_prescription_and_diagnosis_chain(): void
    {
        $patient = Patient::create([
            'patient_code' => 'MCNH-TEST-0002',
            'name' => 'Clinical Case Patient',
            'phone' => '+8801733333333',
            'gender' => 'male',
            'date_of_birth' => '1980-01-01',
        ]);

        $doctor = User::create([
            'name' => 'Dr. Surgeon Test',
            'email' => 'surgeon_test@meghnaclinic.com',
            'phone' => '+8801744444444',
            'password' => 'secret123',
            'user_type' => 'staff',
        ]);

        $visit = Visit::create([
            'patient_id' => $patient->id,
            'visit_number' => 'VIS-TEST-0002',
            'visit_date' => now(),
            'visit_type' => 'regular',
            'status' => 'in_consultation',
        ]);

        $consultation = Consultation::create([
            'visit_id' => $visit->id,
            'doctor_id' => $doctor->id,
            'chief_complaints' => 'Acute abdominal discomfort',
            'status' => 'finalized',
        ]);

        $diagnosis = $consultation->diagnoses()->create([
            'condition_name' => 'Acute Appendicitis',
            'icd_code' => 'K35.8',
            'diagnosis_type' => 'provisional',
        ]);

        $prescription = $consultation->prescription()->create([
            'advice' => 'Complete bed rest and plenty of fluids',
            'follow_up_date' => now()->addDays(7)->toDateString(),
        ]);

        $item = $prescription->items()->create([
            'medicine_name' => 'Ciprofloxacin 500mg',
            'dosage' => '1 tablet',
            'frequency' => '1+0+1',
            'duration' => '5 days',
            'instructions' => 'After food',
        ]);

        $this->assertEquals($consultation->id, $visit->fresh()->consultation->id);
        $this->assertTrue($consultation->fresh()->diagnoses->contains('id', $diagnosis->id));
        $this->assertEquals($prescription->id, $consultation->fresh()->prescription->id);
        $this->assertTrue($prescription->fresh()->items->contains('id', $item->id));
        $this->assertEquals($prescription->id, $item->prescription->id);
    }

    public function test_admission_bed_assignment_and_discharge_summary(): void
    {
        $patient = Patient::create([
            'patient_code' => 'MCNH-TEST-0003',
            'name' => 'Inpatient Test',
            'phone' => '+8801755555555',
            'gender' => 'female',
            'date_of_birth' => '1998-08-20',
        ]);

        $dept = Department::where('code', 'GYN')->first() ?? Department::first();
        $bed = Bed::where('department_id', $dept->id)->first() ?? Bed::first();

        $doctor = User::create([
            'name' => 'Dr. Gyn Specialist',
            'email' => 'gyn_spec@meghnaclinic.com',
            'phone' => '+8801766666666',
            'password' => 'secret123',
            'user_type' => 'staff',
        ]);

        $visit = Visit::create([
            'patient_id' => $patient->id,
            'visit_number' => 'VIS-TEST-0003',
            'visit_date' => now(),
            'visit_type' => 'regular',
            'status' => 'admitted',
        ]);

        $admission = Admission::create([
            'visit_id' => $visit->id,
            'patient_id' => $patient->id,
            'admission_number' => 'ADM-TEST-0001',
            'admission_date' => now(),
            'attending_doctor_id' => $doctor->id,
            'admission_reason' => 'Normal delivery observation',
            'status' => 'admitted',
        ]);

        $assignment = BedAssignment::create([
            'admission_id' => $admission->id,
            'bed_id' => $bed->id,
            'assigned_at' => now(),
            'daily_charge_rate' => $bed->daily_charge,
        ]);

        $nursingNote = NursingNote::create([
            'admission_id' => $admission->id,
            'nurse_id' => $doctor->id,
            'shift' => 'morning',
            'temperature' => 98.4,
            'blood_pressure' => '120/80',
            'pulse_rate' => 74,
            'spo2' => 99.0,
            'observations' => 'Patient resting comfortably.',
            'recorded_at' => now(),
        ]);

        $discharge = DischargeSummary::create([
            'admission_id' => $admission->id,
            'discharge_diagnosis' => 'Post-partum uncomplicated',
            'treatment_given' => 'Standard delivery care',
            'condition_at_discharge' => 'improved',
            'discharge_medications' => 'Multivitamins, Calcium',
            'follow_up_instructions' => 'Review in 2 weeks',
            'authorized_by' => $doctor->id,
        ]);

        $this->assertEquals($admission->id, $patient->fresh()->admissions->first()->id);
        $this->assertEquals($assignment->id, $admission->fresh()->currentBedAssignment->id);
        $this->assertTrue($admission->fresh()->nursingNotes->contains('id', $nursingNote->id));
        $this->assertEquals($discharge->id, $admission->fresh()->dischargeSummary->id);
        $this->assertEquals($admission->id, $discharge->admission->id);
    }

    public function test_billing_items_payments_and_refunds(): void
    {
        $patient = Patient::create([
            'patient_code' => 'MCNH-TEST-0004',
            'name' => 'Billing Patient Test',
            'phone' => '+8801777777777',
            'gender' => 'male',
            'date_of_birth' => '1975-03-12',
        ]);

        $staff = User::create([
            'name' => 'Cashier Test',
            'email' => 'cashier_test@meghnaclinic.com',
            'phone' => '+8801788888888',
            'password' => 'secret123',
            'user_type' => 'staff',
        ]);

        $bill = Bill::create([
            'invoice_number' => 'BILL-TEST-0001',
            'subtotal_amount' => 1000.00,
            'discount_amount' => 100.00,
            'total_amount' => 900.00,
            'paid_amount' => 900.00,
            'due_amount' => 0.00,
            'status' => 'paid',
            'created_by' => $staff->id,
        ]);

        $item = $bill->items()->create([
            'item_type' => 'consultation',
            'item_name' => 'Consultation Fee - Specialist',
            'unit_price' => 1000.00,
            'quantity' => 1,
            'total_price' => 1000.00,
        ]);

        $payment = $bill->payments()->create([
            'receipt_number' => 'RCPT-TEST-0001',
            'amount' => 900.00,
            'payment_method' => 'cash',
            'payment_date' => now(),
            'received_by' => $staff->id,
            'status' => 'success',
        ]);

        $refund = $payment->refunds()->create([
            'refund_voucher' => 'REF-TEST-0001',
            'amount' => 100.00,
            'reason' => 'Duplicate item adjusted',
            'refunded_at' => now(),
            'approved_by' => $staff->id,
        ]);

        $this->assertTrue($bill->fresh()->items->contains('id', $item->id));
        $this->assertTrue($bill->fresh()->payments->contains('id', $payment->id));
        $this->assertEquals($payment->id, $refund->payment->id);
        $this->assertTrue($payment->fresh()->refunds->contains('id', $refund->id));
    }

    public function test_laboratory_order_and_reports_chain(): void
    {
        $patient = Patient::create([
            'patient_code' => 'MCNH-TEST-0005',
            'name' => 'Lab Patient Test',
            'phone' => '+8801799999999',
            'gender' => 'female',
            'date_of_birth' => '2004-12-05',
        ]);

        $doctor = User::create([
            'name' => 'Dr. Lab Referrer',
            'email' => 'lab_doc@meghnaclinic.com',
            'phone' => '+8801700000009',
            'password' => 'secret123',
            'user_type' => 'staff',
        ]);

        $visit = Visit::create([
            'patient_id' => $patient->id,
            'visit_number' => 'VIS-TEST-0005',
            'visit_date' => now(),
            'visit_type' => 'regular',
            'status' => 'in_consultation',
        ]);

        $consultation = Consultation::create([
            'visit_id' => $visit->id,
            'doctor_id' => $doctor->id,
            'chief_complaints' => 'Fever and fatigue',
            'status' => 'draft',
        ]);

        $catalog = LabTestCatalog::create([
            'code' => 'CBC-TEST',
            'name' => 'Complete Blood Count',
            'category' => 'Hematology',
            'standard_charge' => 400.00,
            'default_source' => 'internal',
            'is_active' => true,
        ]);

        $order = LabOrder::create([
            'consultation_id' => $consultation->id,
            'patient_id' => $patient->id,
            'order_number' => 'LAB-TEST-0001',
            'source' => 'internal',
            'status' => 'pending',
        ]);

        $orderItem = $order->items()->create([
            'lab_test_id' => $catalog->id,
            'test_name' => $catalog->name,
            'test_charge' => 400.00,
            'status' => 'pending',
        ]);

        $sample = LabSample::create([
            'lab_order_id' => $order->id,
            'sample_type' => 'Blood (EDTA)',
            'barcode' => 'BC-TEST-0001',
            'collected_at' => now(),
            'collected_by' => $doctor->id,
            'status' => 'collected',
        ]);

        $report = LabReport::create([
            'lab_order_item_id' => $orderItem->id,
            'file_path' => 'secure_medical_reports/cbc_test.pdf',
            'findings_summary' => 'Hemoglobin: 12.5 g/dL (Normal)',
            'parameters' => ['hb' => '12.5', 'wbc' => '7500'],
            'review_status' => 'reviewed',
            'reviewed_by' => $doctor->id,
            'reviewed_at' => now(),
        ]);

        $this->assertEquals($order->id, $orderItem->labOrder->id);
        $this->assertEquals($catalog->id, $orderItem->labTest->id);
        $this->assertEquals($sample->id, $order->fresh()->samples->first()->id);
        $this->assertEquals($report->id, $orderItem->fresh()->report->id);
    }

    public function test_system_settings_helper(): void
    {
        $clinicName = SystemSetting::get('clinic_name');
        $this->assertEquals('Meghna Clinic and Nursing Home', $clinicName);

        SystemSetting::set('test_dynamic_key', 'test_value', 'general', 'string', 'Test setting');
        $this->assertEquals('test_value', SystemSetting::get('test_dynamic_key'));
    }
}
