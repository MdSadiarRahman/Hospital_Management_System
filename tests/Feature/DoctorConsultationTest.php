<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Bed;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\Diagnosis;
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

class DoctorConsultationTest extends TestCase
{
    protected User $adminUser;
    protected User $doctorUserA;
    protected User $doctorUserB;
    protected User $nurseUser;
    protected User $receptionistUser;
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
        $this->patientUser = User::where('user_type', 'patient')->first()
            ?? User::factory()->create(['user_type' => 'patient']);
    }

    protected function createTestPatient(array $attributes = []): Patient
    {
        $uniqueId = rand(100000, 999999);
        return Patient::create(array_merge([
            'patient_code' => 'MCNH-2026-' . $uniqueId,
            'name' => 'Synthetic Test Patient ' . $uniqueId,
            'date_of_birth' => '1990-05-15',
            'gender' => 'female',
            'blood_group' => 'B+',
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

    /**
     * 1. Doctor can view consultations list and filters.
     */
    public function test_doctor_can_view_consultations_index(): void
    {
        $response = $this->actingAs($this->doctorUserA)->get(route('portal.consultations.index'));
        $response->assertStatus(200);
        $response->assertSee('Doctor Consultations Registry');
        $response->assertSee('Clinical Governance');
    }

    /**
     * 2. Doctor can open patient record / consultation authoring form for a visit.
     */
    public function test_doctor_can_open_authoring_workspace_for_visit(): void
    {
        $patient = $this->createTestPatient();
        $visit = $this->createTestVisit($patient);

        $response = $this->actingAs($this->doctorUserA)->get(route('portal.consultations.create', $visit));
        $response->assertStatus(200);
        $response->assertSee($patient->name);
        $response->assertSee($patient->patient_code);
        $response->assertSee($visit->visit_number);
        $response->assertSee('Chief Complaints (Mandatory)');
        $response->assertSee('Clinical Diagnoses');
        $response->assertSee('Prescription');
        $response->assertSee('clinical decisions made by authorized healthcare professionals');
    }

    /**
     * 3. Doctor can create a consultation as DRAFT.
     */
    public function test_doctor_can_create_draft_consultation(): void
    {
        $patient = $this->createTestPatient();
        $visit = $this->createTestVisit($patient);

        $payload = [
            'visit_id' => $visit->id,
            'chief_complaints' => 'Moderate lower abdominal discomfort for 2 days, mild nausea.',
            'clinical_notes' => 'Soft abdomen on palpation, no rebound tenderness. Pulse 78 bpm, BP 120/80 mmHg.',
            'status' => 'draft',
            'diagnoses' => [
                [
                    'condition_name' => 'Mild Dyspepsia',
                    'icd_code' => 'K30',
                    'diagnosis_type' => 'provisional',
                ],
            ],
            'advice' => 'Drink boiled water, avoid spicy and oily food.',
        ];

        $response = $this->actingAs($this->doctorUserA)->post(route('portal.consultations.store'), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('consultations', [
            'visit_id' => $visit->id,
            'doctor_id' => $this->doctorUserA->id,
            'chief_complaints' => 'Moderate lower abdominal discomfort for 2 days, mild nausea.',
            'status' => 'draft',
        ]);

        // Visit status transitions to 'in_consultation'
        $visit->refresh();
        $this->assertEquals('in_consultation', $visit->status);

        // Audit log created
        $consultation = Consultation::where('visit_id', $visit->id)->first();
        $this->assertNotNull($consultation);
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Consultation::class,
            'auditable_id' => $consultation->id,
            'event' => 'created',
            'user_id' => $this->doctorUserA->id,
        ]);
    }

    /**
     * 4. Doctor can create a FINALIZED consultation with diagnoses and prescription.
     */
    public function test_doctor_can_create_finalized_consultation(): void
    {
        $patient = $this->createTestPatient();
        $visit = $this->createTestVisit($patient);

        $payload = [
            'visit_id' => $visit->id,
            'chief_complaints' => 'Acute lower right quadrant pain with low-grade fever for 24 hours.',
            'clinical_notes' => 'Tenderness at McBurney point, Rovsing sign positive. Mild leukocytosis on earlier CBC.',
            'status' => 'finalized',
            'diagnoses' => [
                [
                    'condition_name' => 'Acute Appendicitis',
                    'icd_code' => 'K35.80',
                    'diagnosis_type' => 'provisional',
                ],
            ],
            'advice' => 'NPO (nothing by mouth). Prepare for surgical evaluation and abdominal ultrasound.',
            'follow_up_date' => now()->addDays(2)->toDateString(),
            'medications' => [
                [
                    'medicine_name' => 'Inj. Ceftriaxone 1g',
                    'dosage' => '1g IV',
                    'frequency' => '12 hourly',
                    'duration' => '3 days',
                    'instructions' => 'Slow IV injection over 5 minutes',
                ],
                [
                    'medicine_name' => 'Inj. Paracetamol 100ml',
                    'dosage' => '100ml IV infusion',
                    'frequency' => 'PRN (as needed for fever)',
                    'duration' => '2 days',
                    'instructions' => 'Infuse over 15 minutes',
                ],
            ],
        ];

        $response = $this->actingAs($this->doctorUserA)->post(route('portal.consultations.store'), $payload);

        $consultation = Consultation::where('visit_id', $visit->id)->first();
        $this->assertNotNull($consultation);
        $this->assertEquals('finalized', $consultation->status);
        $this->assertNotNull($consultation->finalized_at);

        // Diagnoses created
        $this->assertDatabaseHas('diagnoses', [
            'consultation_id' => $consultation->id,
            'condition_name' => 'Acute Appendicitis',
            'icd_code' => 'K35.80',
        ]);

        // Prescription and Items created
        $this->assertDatabaseHas('prescriptions', [
            'consultation_id' => $consultation->id,
        ]);
        $this->assertDatabaseHas('prescription_items', [
            'medicine_name' => 'Inj. Ceftriaxone 1g',
            'dosage' => '1g IV',
        ]);

        // Outpatient visit status transitions to 'completed'
        $visit->refresh();
        $this->assertEquals('completed', $visit->status);
    }

    /**
     * 5. Chief complaints is mandatory.
     */
    public function test_chief_complaints_is_mandatory(): void
    {
        $patient = $this->createTestPatient();
        $visit = $this->createTestVisit($patient);

        $payload = [
            'visit_id' => $visit->id,
            'chief_complaints' => '', // Empty
            'status' => 'draft',
        ];

        $response = $this->actingAs($this->doctorUserA)->post(route('portal.consultations.store'), $payload);
        $response->assertSessionHasErrors('chief_complaints');
    }

    /**
     * 6. Clinical diagnosis is mandatory before finalizing a consultation.
     */
    public function test_diagnosis_is_mandatory_before_finalizing(): void
    {
        $patient = $this->createTestPatient();
        $visit = $this->createTestVisit($patient);

        $payload = [
            'visit_id' => $visit->id,
            'chief_complaints' => 'Patient complains of recurrent migraine headache.',
            'status' => 'finalized',
            'diagnoses' => [], // Empty diagnoses
        ];

        $response = $this->actingAs($this->doctorUserA)->post(route('portal.consultations.store'), $payload);
        $response->assertSessionHasErrors('diagnoses');
    }

    /**
     * 7. Doctor can finalize an existing draft consultation.
     */
    public function test_doctor_can_finalize_draft_consultation(): void
    {
        $patient = $this->createTestPatient();
        $visit = $this->createTestVisit($patient);

        $consultation = Consultation::create([
            'visit_id' => $visit->id,
            'doctor_id' => $this->doctorUserA->id,
            'chief_complaints' => 'Persistent dry cough for 1 week.',
            'status' => 'draft',
        ]);

        $consultation->diagnoses()->create([
            'condition_name' => 'Acute Bronchitis',
            'icd_code' => 'J20.9',
            'diagnosis_type' => 'provisional',
        ]);

        $response = $this->actingAs($this->doctorUserA)->post(route('portal.consultations.finalize', $consultation));
        $response->assertRedirect();

        $consultation->refresh();
        $this->assertEquals('finalized', $consultation->status);
        $this->assertNotNull($consultation->finalized_at);

        // Audit log created for finalization event
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Consultation::class,
            'auditable_id' => $consultation->id,
            'event' => 'finalized',
        ]);
    }

    /**
     * 8. Amending a finalized consultation requires mandatory correction reason.
     */
    public function test_amending_finalized_consultation_requires_correction_reason(): void
    {
        $patient = $this->createTestPatient();
        $visit = $this->createTestVisit($patient);

        $consultation = Consultation::create([
            'visit_id' => $visit->id,
            'doctor_id' => $this->doctorUserA->id,
            'chief_complaints' => 'Fever and body aches.',
            'status' => 'finalized',
            'finalized_at' => now(),
        ]);

        $consultation->diagnoses()->create([
            'condition_name' => 'Viral Fever',
            'diagnosis_type' => 'provisional',
        ]);

        $payload = [
            'chief_complaints' => 'Fever, body aches, and persistent productive cough.',
            'status' => 'finalized',
            'correction_reason' => '', // Omitted correction reason!
            'diagnoses' => [
                [
                    'condition_name' => 'Viral Fever',
                    'diagnosis_type' => 'provisional',
                ],
            ],
        ];

        $response = $this->actingAs($this->doctorUserA)->put(route('portal.consultations.update', $consultation), $payload);
        $response->assertSessionHasErrors('correction_reason');
    }

    /**
     * 9. Amending finalized consultation with documented reason saves and tracks audit history.
     */
    public function test_amending_finalized_consultation_with_reason_tracks_audit_trail(): void
    {
        $patient = $this->createTestPatient();
        $visit = $this->createTestVisit($patient);

        $consultation = Consultation::create([
            'visit_id' => $visit->id,
            'doctor_id' => $this->doctorUserA->id,
            'chief_complaints' => 'Initial complaint: fever and headache.',
            'status' => 'finalized',
            'finalized_at' => now(),
        ]);

        $consultation->diagnoses()->create([
            'condition_name' => 'Pyrexia of Unknown Origin',
            'diagnosis_type' => 'provisional',
        ]);

        $payload = [
            'chief_complaints' => 'Revised complaint: High continuous fever with chills and severe retro-orbital headache.',
            'clinical_notes' => 'Platelet count drop noticed on CBC report (90,000/uL). NS1 antigen positive.',
            'status' => 'finalized',
            'correction_reason' => 'Updated clinical diagnosis following confirmatory Dengue NS1 positive report.',
            'diagnoses' => [
                [
                    'condition_name' => 'Dengue Fever with Warning Signs',
                    'icd_code' => 'A97.1',
                    'diagnosis_type' => 'final',
                ],
            ],
            'advice' => 'Adequate oral rehydration therapy (ORS), close monitoring of urine output.',
        ];

        $response = $this->actingAs($this->doctorUserA)->put(route('portal.consultations.update', $consultation), $payload);
        $response->assertRedirect(route('portal.consultations.show', $consultation));

        $consultation->refresh();
        $this->assertEquals('Dengue Fever with Warning Signs', $consultation->diagnoses->first()->condition_name);

        // Audit log created with 'corrected' event and correction_reason
        $auditLog = AuditLog::where('auditable_type', Consultation::class)
            ->where('auditable_id', $consultation->id)
            ->where('event', 'corrected')
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertEquals($this->doctorUserA->id, $auditLog->user_id);
        $this->assertStringContainsString('confirmatory Dengue NS1', $auditLog->new_values['correction_reason']);
    }

    /**
     * 10. Unauthorized user (receptionist, patient, or other doctor) cannot modify another doctor's consultation.
     */
    public function test_unauthorized_user_cannot_modify_consultation(): void
    {
        $patient = $this->createTestPatient();
        $visit = $this->createTestVisit($patient);

        $consultation = Consultation::create([
            'visit_id' => $visit->id,
            'doctor_id' => $this->doctorUserA->id,
            'chief_complaints' => 'Authored by Doctor A.',
            'status' => 'draft',
        ]);

        // Receptionist cannot update
        $response = $this->actingAs($this->receptionistUser)->put(route('portal.consultations.update', $consultation), [
            'chief_complaints' => 'Unauthorized edit attempt.',
            'status' => 'draft',
        ]);
        $response->assertStatus(403);

        // Patient cannot access staff portal (redirected by EnsureStaff middleware)
        $response = $this->actingAs($this->patientUser)->put(route('portal.consultations.update', $consultation), [
            'chief_complaints' => 'Unauthorized edit attempt.',
            'status' => 'draft',
        ]);
        $response->assertRedirect(route('patient.dashboard'));

        // Doctor B cannot update Doctor A's consultation
        $response = $this->actingAs($this->doctorUserB)->put(route('portal.consultations.update', $consultation), [
            'chief_complaints' => 'Unauthorized edit attempt by Doctor B.',
            'status' => 'draft',
        ]);
        $response->assertStatus(403);
    }

    /**
     * 11. Finalized consultation cannot be deleted (policy enforces legal medical record retention).
     */
    public function test_finalized_consultation_cannot_be_deleted(): void
    {
        $patient = $this->createTestPatient();
        $visit = $this->createTestVisit($patient);

        $consultation = Consultation::create([
            'visit_id' => $visit->id,
            'doctor_id' => $this->doctorUserA->id,
            'chief_complaints' => 'Permanent legal medical record.',
            'status' => 'finalized',
            'finalized_at' => now(),
        ]);

        $this->assertFalse($this->doctorUserA->can('delete', $consultation));
        $this->assertFalse($this->adminUser->can('delete', $consultation));
    }

    /**
     * 12. Printable prescription renders properly with clinic branding and disclaimer.
     */
    public function test_printable_prescription_renders_with_branding(): void
    {
        $patient = $this->createTestPatient();
        $visit = $this->createTestVisit($patient);

        $consultation = Consultation::create([
            'visit_id' => $visit->id,
            'doctor_id' => $this->doctorUserA->id,
            'chief_complaints' => 'Chronic lower back pain radiating to left leg.',
            'status' => 'finalized',
            'finalized_at' => now(),
        ]);

        $consultation->diagnoses()->create([
            'condition_name' => 'Lumbar Spondylosis',
            'diagnosis_type' => 'final',
        ]);

        $prescription = $consultation->prescription()->create([
            'advice' => 'Physiotherapy exercises, avoid lifting heavy objects.',
            'follow_up_date' => now()->addWeeks(2)->toDateString(),
        ]);

        $prescription->items()->create([
            'medicine_name' => 'Tab. Naproxen 500mg',
            'dosage' => '500mg',
            'frequency' => '1+0+1',
            'duration' => '7 days',
            'instructions' => 'Take with full stomach',
        ]);

        $response = $this->actingAs($this->doctorUserA)->get(route('portal.consultations.print', $consultation));
        $response->assertStatus(200);
        $response->assertSee('Meghna Clinic');
        $response->assertSee('Shohid Minar Mor, Dinajpur');
        $response->assertSee('Tab. Naproxen 500mg');
        $response->assertSee('Lumbar Spondylosis');
        $response->assertSee('Physiotherapy exercises');
        $response->assertSee('Authorized Doctor');
    }

    /**
     * 13. Patient consultation history ledger displays all visits for that patient.
     */
    public function test_patient_consultations_history_ledger(): void
    {
        $patient = $this->createTestPatient();
        $visit1 = $this->createTestVisit($patient);
        $visit2 = $this->createTestVisit($patient);

        Consultation::create([
            'visit_id' => $visit1->id,
            'doctor_id' => $this->doctorUserA->id,
            'chief_complaints' => 'Visit 1 complaints: Gastritis',
            'status' => 'finalized',
        ]);

        Consultation::create([
            'visit_id' => $visit2->id,
            'doctor_id' => $this->doctorUserA->id,
            'chief_complaints' => 'Visit 2 complaints: Follow-up review',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->doctorUserA)->get(route('portal.consultations.patient-history', $patient));
        $response->assertStatus(200);
        $response->assertSee($patient->name);
        $response->assertSee('Visit 1 complaints');
        $response->assertSee('Visit 2 complaints');
    }
}
