<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\AuditLog;
use App\Models\Bed;
use App\Models\BedAssignment;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\MedicationAdministration;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\DepartmentAndBedSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Database\Seeders\UserSeeder;
use Tests\TestCase;

class MedicationAdministrationRecordTest extends TestCase
{
    protected User $adminUser;
    protected User $nurseUser;
    protected User $doctorUser;
    protected User $patientUser;

    protected function setUp(): void
    {
        parent::setUp();

        if (Department::count() === 0 || Bed::count() === 0) {
            $this->seed(RolePermissionSeeder::class);
            $this->seed(DepartmentAndBedSeeder::class);
            $this->seed(SystemSettingsSeeder::class);
            $this->seed(UserSeeder::class);
        }

        $this->adminUser = User::whereHas('roles', fn($q) => $q->where('name', 'administrator'))->first()
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('administrator');

        $this->nurseUser = User::whereHas('roles', fn($q) => $q->where('name', 'nurse'))->first()
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('nurse');

        $this->doctorUser = User::whereHas('roles', fn($q) => $q->where('name', 'doctor'))->first()
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('doctor');

        $this->patientUser = User::where('user_type', 'patient')->first()
            ?? User::factory()->create(['user_type' => 'patient']);
    }

    /**
     * Helper to create an active inpatient admission with authorized prescription.
     */
    protected function createInpatientWithPrescription(): array
    {
        $unique = rand(100000, 999999);
        $dept = Department::where('is_clinical', true)->first();

        $patient = Patient::create([
            'patient_code' => "MCNH-MAR-{$unique}",
            'name' => "eMAR Inpatient {$unique}",
            'date_of_birth' => '1990-05-15',
            'gender' => 'female',
            'phone' => "017{$unique}2",
            'blood_group' => 'B+',
        ]);

        $visit = Visit::create([
            'patient_id' => $patient->id,
            'visit_number' => "VST-MAR-{$unique}",
            'visit_date' => now()->subDays(1),
            'visit_type' => 'emergency',
            'status' => 'completed',
        ]);

        $bed = Bed::create([
            'bed_number' => "BED-MAR-{$unique}",
            'department_id' => $dept?->id,
            'category' => 'general',
            'daily_charge' => 900.00,
            'status' => 'occupied',
        ]);

        $admission = Admission::create([
            'visit_id' => $visit->id,
            'patient_id' => $patient->id,
            'department_id' => $dept?->id,
            'admission_number' => "ADM-MAR-{$unique}",
            'admission_date' => now()->subHours(12),
            'attending_doctor_id' => $this->doctorUser->id,
            'admission_reason' => 'Acute bacterial pneumonia',
            'status' => 'admitted',
        ]);

        BedAssignment::create([
            'admission_id' => $admission->id,
            'bed_id' => $bed->id,
            'assigned_at' => now()->subHours(12),
            'is_current' => true,
        ]);

        $consultation = Consultation::create([
            'visit_id' => $visit->id,
            'doctor_id' => $this->doctorUser->id,
            'chief_complaints' => 'High fever and productive cough',
            'status' => 'finalized',
            'finalized_at' => now()->subHours(10),
        ]);

        $prescription = Prescription::create([
            'consultation_id' => $consultation->id,
            'prescription_number' => "RX-MAR-{$unique}",
            'advice' => 'Adequate hydration, nebulize TDS',
            'status' => 'approved',
            'approved_at' => now()->subHours(10),
            'approved_by' => $this->doctorUser->id,
        ]);

        $item1 = PrescriptionItem::create([
            'prescription_id' => $prescription->id,
            'medicine_name' => 'Injection Ceftriaxone 1g',
            'dosage' => '1g',
            'frequency' => '1-0-1',
            'duration' => '5 days',
            'instructions' => 'IV Infusion in 100ml Normal Saline over 30 mins',
        ]);

        $item2 = PrescriptionItem::create([
            'prescription_id' => $prescription->id,
            'medicine_name' => 'Tablet Paracetamol 500mg',
            'dosage' => '500mg',
            'frequency' => '1-1-1',
            'duration' => '3 days',
            'instructions' => 'After food, hold if afebrile',
        ]);

        return [
            'patient' => $patient,
            'admission' => $admission,
            'bed' => $bed,
            'prescription' => $prescription,
            'item1' => $item1,
            'item2' => $item2,
        ];
    }

    /**
     * Requirement: View Authorized Prescription & eMAR Schedule.
     */
    public function test_nurse_can_view_authorized_prescription_and_emar_schedule(): void
    {
        $data = $this->createInpatientWithPrescription();
        $admission = $data['admission'];

        $response = $this->actingAs($this->nurseUser)
            ->get(route('portal.nurse.admissions.mar-flowsheet', $admission->id));

        $response->assertStatus(200);
        $response->assertSee('Daily Inpatient eMAR Flowsheet');
        $response->assertSee('Authorized Doctor Prescriptions');
        $response->assertSee('Injection Ceftriaxone 1g');
        $response->assertSee('Tablet Paracetamol 500mg');
        $response->assertSee('Read-Only Clinical Orders');
        $response->assertSee('Morning Shift');
        $response->assertSee('Evening Shift');
        $response->assertSee('Night Shift');
    }

    /**
     * Requirement: Nurses must not independently modify a doctor's prescription.
     */
    public function test_nurses_cannot_independently_modify_or_create_prescriptions(): void
    {
        $data = $this->createInpatientWithPrescription();
        $prescription = $data['prescription'];

        // Nurse attempts to update prescription -> 403 Forbidden
        $response = $this->actingAs($this->nurseUser)
            ->put(route('portal.prescriptions.update', $prescription->id), [
                'advice' => 'Nurse unauthorized alteration',
            ]);

        $response->assertStatus(403);

        // Nurse attempts to approve prescription -> 403 Forbidden
        $approveResponse = $this->actingAs($this->nurseUser)
            ->post(route('portal.prescriptions.approve', $prescription->id));

        $approveResponse->assertStatus(403);
    }

    /**
     * Requirement: Record actual administration events with reference to prescription.
     */
    public function test_nurse_can_record_actual_medication_administration(): void
    {
        $data = $this->createInpatientWithPrescription();
        $admission = $data['admission'];
        $item = $data['item1'];

        $payload = [
            'prescription_id' => $data['prescription']->id,
            'prescription_item_id' => $item->id,
            'medicine_name' => $item->medicine_name,
            'dose_given' => '1g',
            'route' => 'IV Infusion',
            'scheduled_shift' => 'morning',
            'status' => 'administered',
            'remarks' => 'Infused over 30 mins, patient tolerated well without allergic reaction',
            'administered_at' => now()->format('Y-m-d H:i:s'),
        ];

        $response = $this->actingAs($this->nurseUser)
            ->post(route('portal.nurse.medications.store', $admission->id), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('medication_administrations', [
            'admission_id' => $admission->id,
            'prescription_item_id' => $item->id,
            'medicine_name' => 'Injection Ceftriaxone 1g',
            'dose_given' => '1g',
            'route' => 'IV Infusion',
            'scheduled_shift' => 'morning',
            'nurse_id' => $this->nurseUser->id,
            'status' => 'administered',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->nurseUser->id,
            'event' => 'medication.administered',
        ]);
    }

    /**
     * Requirement: Withheld Dose Reason is mandatory when withholding medication.
     */
    public function test_withheld_dose_requires_mandatory_clinical_reason(): void
    {
        $data = $this->createInpatientWithPrescription();
        $admission = $data['admission'];
        $item = $data['item2'];

        // 1. Attempt withholding without withheld_reason -> should fail validation
        $response = $this->actingAs($this->nurseUser)
            ->post(route('portal.nurse.medications.store', $admission->id), [
                'prescription_item_id' => $item->id,
                'medicine_name' => $item->medicine_name,
                'dose_given' => '500mg',
                'route' => 'Oral',
                'scheduled_shift' => 'morning',
                'status' => 'withheld',
                'withheld_reason' => '', // missing
            ]);

        $response->assertSessionHasErrors(['withheld_reason']);

        // 2. Submit with valid clinical reasoning -> success
        $validResponse = $this->actingAs($this->nurseUser)
            ->post(route('portal.nurse.medications.store', $admission->id), [
                'prescription_item_id' => $item->id,
                'medicine_name' => $item->medicine_name,
                'dose_given' => '500mg',
                'route' => 'Oral',
                'scheduled_shift' => 'morning',
                'status' => 'withheld',
                'withheld_reason' => 'Patient afebrile (98.4°F), per doctor instructions hold paracetamol',
                'remarks' => 'Temperature verified bedside prior to morning dose',
            ]);

        $validResponse->assertRedirect();
        $validResponse->assertSessionHas('success');

        $this->assertDatabaseHas('medication_administrations', [
            'admission_id' => $admission->id,
            'status' => 'withheld',
            'withheld_reason' => 'Patient afebrile (98.4°F), per doctor instructions hold paracetamol',
        ]);
    }

    /**
     * Requirement: Record missed or refused dose with clinical justification.
     */
    public function test_nurse_can_record_missed_and_refused_doses(): void
    {
        $data = $this->createInpatientWithPrescription();
        $admission = $data['admission'];
        $item = $data['item1'];

        // Missed dose record
        $response = $this->actingAs($this->nurseUser)
            ->post(route('portal.nurse.medications.store', $admission->id), [
                'prescription_item_id' => $item->id,
                'medicine_name' => $item->medicine_name,
                'dose_given' => '1g',
                'route' => 'IV Infusion',
                'scheduled_shift' => 'night',
                'status' => 'missed',
                'remarks' => 'Patient was off-ward for emergency CT scan during scheduled dose window',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('medication_administrations', [
            'admission_id' => $admission->id,
            'status' => 'missed',
            'remarks' => 'Patient was off-ward for emergency CT scan during scheduled dose window',
        ]);

        // Refused dose record
        $refuseResponse = $this->actingAs($this->nurseUser)
            ->post(route('portal.nurse.medications.store', $admission->id), [
                'prescription_item_id' => $item->id,
                'medicine_name' => $item->medicine_name,
                'dose_given' => '1g',
                'route' => 'Oral',
                'scheduled_shift' => 'evening',
                'status' => 'refused',
                'remarks' => 'Patient expressed fear of nausea and refused oral medication; notified doctor',
            ]);

        $refuseResponse->assertRedirect();
        $this->assertDatabaseHas('medication_administrations', [
            'admission_id' => $admission->id,
            'status' => 'refused',
        ]);
    }

    /**
     * Requirement: Prevent duplicate administration records in the same shift window.
     */
    public function test_prevent_duplicate_administration_in_same_shift(): void
    {
        $data = $this->createInpatientWithPrescription();
        $admission = $data['admission'];
        $item = $data['item1'];

        // First administration: morning shift
        $first = $this->actingAs($this->nurseUser)
            ->post(route('portal.nurse.medications.store', $admission->id), [
                'prescription_item_id' => $item->id,
                'medicine_name' => $item->medicine_name,
                'dose_given' => '1g',
                'route' => 'IV Infusion',
                'scheduled_shift' => 'morning',
                'status' => 'administered',
                'administered_at' => now()->format('Y-m-d H:i:s'),
            ]);

        $first->assertRedirect();

        // Second administration: duplicate for morning shift on the same day -> must be blocked
        $second = $this->actingAs($this->nurseUser)
            ->post(route('portal.nurse.medications.store', $admission->id), [
                'prescription_item_id' => $item->id,
                'medicine_name' => $item->medicine_name,
                'dose_given' => '1g',
                'route' => 'IV Infusion',
                'scheduled_shift' => 'morning',
                'status' => 'administered',
                'administered_at' => now()->format('Y-m-d H:i:s'),
            ]);

        $second->assertSessionHasErrors(['medicine_name']);

        // Check that only 1 record exists
        $this->assertEquals(
            1,
            MedicationAdministration::where('admission_id', $admission->id)
                ->where('prescription_item_id', $item->id)
                ->where('scheduled_shift', 'morning')
                ->where('status', 'administered')
                ->count()
        );
    }

    /**
     * Requirement: Maintain correction history and audit trail when amending eMAR.
     */
    public function test_amendment_maintains_correction_history_and_audit_trail(): void
    {
        $data = $this->createInpatientWithPrescription();
        $admission = $data['admission'];
        $item = $data['item2'];

        // Initial administration
        $mar = MedicationAdministration::create([
            'admission_id' => $admission->id,
            'prescription_item_id' => $item->id,
            'medicine_name' => $item->medicine_name,
            'dose_given' => '500mg',
            'route' => 'Oral',
            'scheduled_shift' => 'morning',
            'nurse_id' => $this->nurseUser->id,
            'status' => 'administered',
            'administered_at' => now()->subHours(2),
        ]);

        // Attempt correction without correction_reason -> fails validation
        $failResponse = $this->actingAs($this->nurseUser)
            ->post(route('portal.nurse.medications.correct', $mar->id), [
                'dose_given' => '250mg',
                'route' => 'Oral',
                'status' => 'administered',
                'correction_reason' => '', // missing
            ]);

        $failResponse->assertSessionHasErrors(['correction_reason']);

        // Submit valid amendment
        $correctResponse = $this->actingAs($this->nurseUser)
            ->post(route('portal.nurse.medications.correct', $mar->id), [
                'dose_given' => '250mg',
                'route' => 'Oral',
                'status' => 'administered',
                'correction_reason' => 'Corrected dose typo: patient was administered 250mg half-tablet per verbal order',
            ]);

        $correctResponse->assertRedirect();
        $correctResponse->assertSessionHas('success');

        $mar->refresh();
        $this->assertEquals('250mg', $mar->dose_given);
        $this->assertTrue($mar->is_corrected);
        $this->assertEquals($this->nurseUser->id, $mar->corrected_by);
        $this->assertNotNull($mar->corrected_at);
        $this->assertStringContainsString('Corrected dose typo', $mar->correction_reason);

        // Check Audit Log
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->nurseUser->id,
            'event' => 'medication.corrected',
            'auditable_type' => MedicationAdministration::class,
            'auditable_id' => $mar->id,
        ]);
    }

    /**
     * Requirement: Unauthorized users cannot access eMAR ledger or record medications.
     */
    public function test_unauthorized_users_denied_access_to_emar(): void
    {
        $data = $this->createInpatientWithPrescription();
        $admission = $data['admission'];

        // 1. Unauthenticated access -> redirects to login
        $guestResponse = $this->get(route('portal.nurse.mar'));
        $guestResponse->assertRedirect(route('portal.login'));

        // 2. Patient user access to staff eMAR -> blocked and redirected to patient dashboard
        $patientResponse = $this->actingAs($this->patientUser)
            ->get(route('portal.nurse.mar'));
        $patientResponse->assertRedirect(route('patient.dashboard'));

        // 3. Unauthorized staff user (e.g. receptionist) without nursing/clinical role -> 403 Forbidden
        $receptionist = User::whereHas('roles', fn($q) => $q->where('name', 'receptionist'))->first()
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('receptionist');

        $staffResponse = $this->actingAs($receptionist)
            ->get(route('portal.nurse.mar'));
        $staffResponse->assertStatus(403);

        // 4. Unauthorized staff user attempting to record medication -> 403 Forbidden
        $staffRecord = $this->actingAs($receptionist)
            ->post(route('portal.nurse.medications.store', $admission->id), [
                'medicine_name' => 'Unauthorized Medicine',
                'dose_given' => '100mg',
                'status' => 'administered',
            ]);
        $staffRecord->assertStatus(403);
    }
}
