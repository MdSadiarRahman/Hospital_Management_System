<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Bed;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\DepartmentAndBedSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Database\Seeders\UserSeeder;
use Tests\TestCase;

class PrescriptionManagementTest extends TestCase
{
    protected User $adminUser;
    protected User $doctorUserA;
    protected User $doctorUserB;
    protected User $nurseUser;
    protected User $receptionistUser;
    protected User $patientUserA;
    protected User $patientUserB;
    protected Patient $patientA;
    protected Patient $patientB;

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
            ?? User::factory()->create(['user_type' => 'staff']);
        if (!$this->adminUser->hasRole('administrator')) {
            $role = Role::where('name', 'administrator')->first();
            if ($role) $this->adminUser->roles()->syncWithoutDetaching([$role->id]);
        }

        $doctors = User::whereHas('roles', fn($q) => $q->where('name', 'doctor'))->get();
        $this->doctorUserA = $doctors->first();
        $this->doctorUserB = $doctors->count() > 1 ? $doctors->get(1) : User::factory()->create(['user_type' => 'staff']);
        if (!$this->doctorUserB->hasRole('doctor')) {
            $role = Role::where('name', 'doctor')->first();
            if ($role) $this->doctorUserB->roles()->syncWithoutDetaching([$role->id]);
        }

        $this->nurseUser = User::whereHas('roles', fn($q) => $q->where('name', 'nurse'))->first();
        $this->receptionistUser = User::whereHas('roles', fn($q) => $q->where('name', 'receptionist'))->first();

        // Create two verified patients with user accounts
        $this->patientUserA = User::factory()->create(['user_type' => 'patient']);
        $this->patientA = $this->createTestPatient(['user_id' => $this->patientUserA->id]);

        $this->patientUserB = User::factory()->create(['user_type' => 'patient']);
        $this->patientB = $this->createTestPatient(['user_id' => $this->patientUserB->id]);
    }

    protected function createTestPatient(array $attributes = []): Patient
    {
        $uniqueId = rand(100000, 999999);
        return Patient::create(array_merge([
            'patient_code' => 'MCNH-2026-' . $uniqueId,
            'name' => 'Fictional Test Patient ' . $uniqueId,
            'date_of_birth' => '1992-06-20',
            'gender' => 'female',
            'blood_group' => 'A+',
            'phone' => '+88017' . rand(10000000, 99999999),
            'registration_date' => now()->toDateString(),
            'is_verified' => true,
        ], $attributes));
    }

    protected function createTestVisit(Patient $patient, array $attributes = []): Visit
    {
        $uniqueId = rand(100000, 999999);
        return Visit::create(array_merge([
            'patient_id' => $patient->id,
            'visit_number' => 'VST-' . now()->format('Ymd') . '-' . $uniqueId,
            'visit_date' => now(),
            'visit_type' => 'regular',
            'triage_level' => 'normal',
            'status' => 'waiting',
        ], $attributes));
    }

    protected function createTestConsultation(Patient $patient, User $doctor, array $attributes = []): Consultation
    {
        $visit = $this->createTestVisit($patient);
        return Consultation::create(array_merge([
            'visit_id' => $visit->id,
            'doctor_id' => $doctor->id,
            'chief_complaints' => 'Presenting fever, cough, and body aches for 3 days.',
            'clinical_notes' => 'Chest clear, normal vesicular breath sounds. Pulse 80 bpm.',
            'status' => 'finalized',
            'finalized_at' => now(),
        ], $attributes));
    }

    /**
     * 1. Doctor can view prescriptions index in staff portal.
     */
    public function test_doctor_can_view_prescriptions_index(): void
    {
        $response = $this->actingAs($this->doctorUserA)->get(route('portal.prescriptions.index'));
        $response->assertStatus(200);
        $response->assertSee('Prescriptions Management');
        $response->assertSee('Clinical Governance');
    }

    /**
     * 2. Doctor can create a prescription with auto-generated prescription number and line items.
     */
    public function test_doctor_can_create_prescription_with_generated_number(): void
    {
        $consultation = $this->createTestConsultation($this->patientA, $this->doctorUserA);

        $payload = [
            'consultation_id' => $consultation->id,
            'advice' => 'Drink plenty of warm water and take complete bed rest for 3 days.',
            'follow_up_date' => now()->addDays(5)->toDateString(),
            'status' => 'approved',
            'items' => [
                [
                    'medicine_name' => 'Tab. Paracetamol 500mg',
                    'dosage' => '500mg',
                    'frequency' => '1+1+1',
                    'duration' => '3 days',
                    'instructions' => 'Take after meals',
                ],
                [
                    'medicine_name' => 'Syp. Dextromethorphan 100ml',
                    'dosage' => '2 teaspoonfuls',
                    'frequency' => '0+0+1',
                    'duration' => '5 days',
                    'instructions' => 'Take at bedtime',
                ],
            ],
        ];

        $response = $this->actingAs($this->doctorUserA)->post(route('portal.prescriptions.store'), $payload);

        $response->assertRedirect();

        $prescription = Prescription::where('consultation_id', $consultation->id)->first();
        $this->assertNotNull($prescription);
        $this->assertNotNull($prescription->prescription_number);
        $this->assertStringStartsWith('RX-' . now()->format('Ymd'), $prescription->prescription_number);
        $this->assertEquals('approved', $prescription->status);
        $this->assertNotNull($prescription->approved_at);
        $this->assertEquals($this->doctorUserA->id, $prescription->approved_by);

        // Verify items created
        $this->assertDatabaseHas('prescription_items', [
            'prescription_id' => $prescription->id,
            'medicine_name' => 'Tab. Paracetamol 500mg',
            'dosage' => '500mg',
            'frequency' => '1+1+1',
        ]);
        $this->assertDatabaseHas('prescription_items', [
            'prescription_id' => $prescription->id,
            'medicine_name' => 'Syp. Dextromethorphan 100ml',
        ]);

        // Verify Audit Log
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Prescription::class,
            'auditable_id' => $prescription->id,
            'event' => 'created',
            'user_id' => $this->doctorUserA->id,
        ]);
    }

    /**
     * 3. Only authorized doctors can create or approve prescriptions.
     */
    public function test_only_authorized_doctor_can_create_or_approve_prescriptions(): void
    {
        $consultation = $this->createTestConsultation($this->patientA, $this->doctorUserA);

        $payload = [
            'consultation_id' => $consultation->id,
            'status' => 'draft',
            'items' => [
                [
                    'medicine_name' => 'Tab. Amoxicillin 500mg',
                    'dosage' => '500mg',
                    'frequency' => '1+1+1',
                    'duration' => '5 days',
                ],
            ],
        ];

        // Receptionist cannot create prescription
        $response = $this->actingAs($this->receptionistUser)->post(route('portal.prescriptions.store'), $payload);
        $response->assertStatus(403);

        // Nurse cannot create prescription
        $response = $this->actingAs($this->nurseUser)->post(route('portal.prescriptions.store'), $payload);
        $response->assertStatus(403);

        // Patient cannot create prescription (redirected or 403)
        $response = $this->actingAs($this->patientUserA)->post(route('portal.prescriptions.store'), $payload);
        $this->assertTrue(in_array($response->status(), [302, 403]));
    }

    /**
     * 4. Doctor can approve a draft prescription.
     */
    public function test_doctor_can_approve_draft_prescription(): void
    {
        $consultation = $this->createTestConsultation($this->patientA, $this->doctorUserA);

        $prescription = Prescription::create([
            'prescription_number' => Prescription::generatePrescriptionNumber(),
            'consultation_id' => $consultation->id,
            'advice' => 'Hydration and rest',
            'status' => 'draft',
        ]);

        $prescription->items()->create([
            'medicine_name' => 'Tab. Cetirizine 10mg',
            'dosage' => '10mg',
            'frequency' => '0+0+1',
            'duration' => '5 days',
        ]);

        $response = $this->actingAs($this->doctorUserA)->post(route('portal.prescriptions.approve', $prescription));
        $response->assertRedirect();

        $prescription->refresh();
        $this->assertEquals('approved', $prescription->status);
        $this->assertNotNull($prescription->approved_at);
        $this->assertEquals($this->doctorUserA->id, $prescription->approved_by);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Prescription::class,
            'auditable_id' => $prescription->id,
            'event' => 'approved',
        ]);
    }

    /**
     * 5. Patient can view ONLY authorized (approved) prescriptions belonging to them.
     */
    public function test_patient_can_view_only_authorized_prescriptions(): void
    {
        $consultationA = $this->createTestConsultation($this->patientA, $this->doctorUserA);

        // 1. Approved prescription belonging to Patient A
        $approvedRxPatientA = Prescription::create([
            'prescription_number' => Prescription::generatePrescriptionNumber(),
            'consultation_id' => $consultationA->id,
            'advice' => 'Approved advice for Patient A',
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $this->doctorUserA->id,
        ]);
        $approvedRxPatientA->items()->create([
            'medicine_name' => 'Tab. Ciprofloxacin 500mg',
            'dosage' => '500mg',
            'frequency' => '1+0+1',
            'duration' => '7 days',
        ]);

        // Patient A CAN view their approved prescription
        $response = $this->actingAs($this->patientUserA)->get(route('patient.prescriptions.show', $approvedRxPatientA->id));
        $response->assertStatus(200);
        $response->assertSee('Tab. Ciprofloxacin 500mg');
        $response->assertSee($approvedRxPatientA->prescription_number);

        // 2. Draft (unapproved) prescription belonging to Patient A
        $draftRxPatientA = Prescription::create([
            'prescription_number' => Prescription::generatePrescriptionNumber(),
            'consultation_id' => $consultationA->id,
            'advice' => 'Draft advice not ready for patient',
            'status' => 'draft',
        ]);
        $draftRxPatientA->items()->create([
            'medicine_name' => 'Tab. Experimental 50mg',
            'dosage' => '50mg',
            'frequency' => '1+0+0',
            'duration' => '3 days',
        ]);

        // Patient A CANNOT view their own draft prescription (403 forbidden)
        $response = $this->actingAs($this->patientUserA)->get(route('patient.prescriptions.show', $draftRxPatientA->id));
        $response->assertStatus(403);

        // 3. Approved prescription belonging to Patient B
        $consultationB = $this->createTestConsultation($this->patientB, $this->doctorUserA);
        $approvedRxPatientB = Prescription::create([
            'prescription_number' => Prescription::generatePrescriptionNumber(),
            'consultation_id' => $consultationB->id,
            'advice' => 'Approved advice for Patient B',
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $this->doctorUserA->id,
        ]);
        $approvedRxPatientB->items()->create([
            'medicine_name' => 'Tab. Metformin 500mg',
            'dosage' => '500mg',
            'frequency' => '1+0+1',
            'duration' => '30 days',
        ]);

        // Patient A CANNOT view Patient B's approved prescription (privacy violation check)
        $response = $this->actingAs($this->patientUserA)->get(route('patient.prescriptions.show', $approvedRxPatientB->id));
        $response->assertStatus(403);
    }

    /**
     * 6. Amending an approved prescription requires mandatory correction reason.
     */
    public function test_amending_approved_prescription_requires_correction_reason(): void
    {
        $consultation = $this->createTestConsultation($this->patientA, $this->doctorUserA);

        $prescription = Prescription::create([
            'prescription_number' => Prescription::generatePrescriptionNumber(),
            'consultation_id' => $consultation->id,
            'advice' => 'Take rest',
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $this->doctorUserA->id,
        ]);

        $prescription->items()->create([
            'medicine_name' => 'Tab. Ibuprofen 400mg',
            'dosage' => '400mg',
            'frequency' => '1+0+1',
            'duration' => '3 days',
        ]);

        // Attempting to update without correction_reason fails validation
        $payload = [
            'advice' => 'Updated advice without reason',
            'status' => 'approved',
            'correction_reason' => '', // Omitted!
            'items' => [
                [
                    'medicine_name' => 'Tab. Ibuprofen 400mg',
                    'dosage' => '400mg',
                    'frequency' => '1+1+1',
                    'duration' => '5 days',
                ],
            ],
        ];

        $response = $this->actingAs($this->doctorUserA)->put(route('portal.prescriptions.update', $prescription), $payload);
        $response->assertSessionHasErrors('correction_reason');

        // Providing valid correction reason succeeds and creates audit log
        $payload['correction_reason'] = 'Increased frequency to 1+1+1 due to severe inflammatory swelling.';
        $response = $this->actingAs($this->doctorUserA)->put(route('portal.prescriptions.update', $prescription), $payload);
        $response->assertRedirect(route('portal.prescriptions.show', $prescription));

        $auditLog = AuditLog::where('auditable_type', Prescription::class)
            ->where('auditable_id', $prescription->id)
            ->where('event', 'corrected')
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertStringContainsString('severe inflammatory swelling', $auditLog->new_values['correction_reason']);
    }

    /**
     * 7. Approved prescriptions cannot be deleted.
     */
    public function test_approved_prescription_cannot_be_deleted(): void
    {
        $consultation = $this->createTestConsultation($this->patientA, $this->doctorUserA);

        $prescription = Prescription::create([
            'prescription_number' => Prescription::generatePrescriptionNumber(),
            'consultation_id' => $consultation->id,
            'advice' => 'Legal medical record',
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $this->doctorUserA->id,
        ]);

        $this->assertFalse($this->doctorUserA->can('delete', $prescription));
        $this->assertFalse($this->adminUser->can('delete', $prescription));
    }

    /**
     * 8. Secure prescription printing renders clinic branding, doctor info, and items.
     */
    public function test_secure_prescription_printing_renders_branding(): void
    {
        $consultation = $this->createTestConsultation($this->patientA, $this->doctorUserA);

        $prescription = Prescription::create([
            'prescription_number' => Prescription::generatePrescriptionNumber(),
            'consultation_id' => $consultation->id,
            'advice' => 'Strict low-salt diet and morning brisk walking.',
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $this->doctorUserA->id,
        ]);

        $prescription->items()->create([
            'medicine_name' => 'Tab. Amlodipine 5mg',
            'dosage' => '5mg',
            'frequency' => '0+0+1',
            'duration' => '30 days',
            'instructions' => 'Take at night after food',
        ]);

        $response = $this->actingAs($this->doctorUserA)->get(route('portal.prescriptions.print', $prescription));
        $response->assertStatus(200);
        $response->assertSee('Meghna Clinic');
        $response->assertSee('Shohid Minar Mor, Dinajpur');
        $response->assertSee('Tab. Amlodipine 5mg');
        $response->assertSee($prescription->prescription_number);
        $response->assertSee('Authorized Doctor');
    }

    /**
     * 9. Prescription history ledger displays chronological timeline for patient.
     */
    public function test_patient_prescription_history_timeline(): void
    {
        $consultation1 = $this->createTestConsultation($this->patientA, $this->doctorUserA);
        $consultation2 = $this->createTestConsultation($this->patientA, $this->doctorUserA);

        $rx1 = Prescription::create([
            'prescription_number' => Prescription::generatePrescriptionNumber(),
            'consultation_id' => $consultation1->id,
            'advice' => 'Rx 1 advice',
            'status' => 'approved',
        ]);
        $rx1->items()->create([
            'medicine_name' => 'Tab. DrugAlpha 100mg',
            'dosage' => '100mg',
            'frequency' => '1+0+0',
            'duration' => '3 days',
        ]);

        $rx2 = Prescription::create([
            'prescription_number' => Prescription::generatePrescriptionNumber(),
            'consultation_id' => $consultation2->id,
            'advice' => 'Rx 2 advice',
            'status' => 'approved',
        ]);
        $rx2->items()->create([
            'medicine_name' => 'Tab. DrugBeta 200mg',
            'dosage' => '200mg',
            'frequency' => '1+0+1',
            'duration' => '5 days',
        ]);

        $response = $this->actingAs($this->doctorUserA)->get(route('portal.prescriptions.patient-history', $this->patientA));
        $response->assertStatus(200);
        $response->assertSee($this->patientA->name);
        $response->assertSee('Tab. DrugAlpha 100mg');
        $response->assertSee('Tab. DrugBeta 200mg');
    }
}

