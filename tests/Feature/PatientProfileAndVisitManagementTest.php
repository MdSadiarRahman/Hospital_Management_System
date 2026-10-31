<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\AuditLog;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\Diagnosis;
use App\Models\DischargeSummary;
use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\LabTestCatalog;
use App\Models\Patient;
use App\Models\PatientContact;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use App\Services\VisitService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientProfileAndVisitManagementTest extends TestCase
{
    protected User $receptionistUser;
    protected User $doctorUser;
    protected User $patientUser;
    protected Patient $patient;
    protected VisitService $visitService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->visitService = app(VisitService::class);

        // Seed roles & permissions
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        // Receptionist User
        $this->receptionistUser = User::updateOrCreate(
            ['email' => 'receptionist.visit.test@meghnaclinic.test'],
            [
                'name' => 'Receptionist Staff Test',
                'phone' => '01719888001',
                'password' => bcrypt('Password123!'),
                'user_type' => 'staff',
                'is_active' => true,
            ]
        );
        $receptionistRole = Role::where('name', 'receptionist')->first();
        if ($receptionistRole) {
            $this->receptionistUser->roles()->syncWithoutDetaching([$receptionistRole->id]);
        }

        // Doctor User
        $this->doctorUser = User::updateOrCreate(
            ['email' => 'doctor.visit.test@meghnaclinic.test'],
            [
                'name' => 'Dr. Anisur Rahman Test',
                'phone' => '01719888002',
                'password' => bcrypt('Password123!'),
                'user_type' => 'staff',
                'is_active' => true,
            ]
        );
        $doctorRole = Role::where('name', 'doctor')->first();
        if ($doctorRole) {
            $this->doctorUser->roles()->syncWithoutDetaching([$doctorRole->id]);
        }

        // Patient User
        $this->patientUser = User::updateOrCreate(
            ['email' => 'patient.visit.user.test@meghnaclinic.test'],
            [
                'name' => 'Patient User Test',
                'phone' => '01719888003',
                'password' => bcrypt('Password123!'),
                'user_type' => 'patient',
                'is_active' => true,
            ]
        );
        $patientRole = Role::where('name', 'patient')->first();
        if ($patientRole) {
            $this->patientUser->roles()->syncWithoutDetaching([$patientRole->id]);
        }

        // Unique Patient Master Record
        $this->patient = Patient::updateOrCreate(
            ['patient_code' => 'MCNH-2026-9988'],
            [
                'user_id' => $this->patientUser->id,
                'name' => 'Md. Tariqul Islam Test',
                'date_of_birth' => '1988-06-15',
                'gender' => 'male',
                'blood_group' => 'B+',
                'phone' => '01719888111',
                'email' => 'tariqul.test@meghnaclinic.test',
                'nid_or_birth_cert' => '1988271890111',
                'address' => 'Goneshtola, Dinajpur Sadar',
                'registration_date' => now()->toDateString(),
                'is_verified' => true,
            ]
        );

        // Clean up visits and child clinical records for this test patient to prevent foreign key errors
        $visitIds = Visit::where('patient_id', $this->patient->id)->pluck('id');
        if ($visitIds->isNotEmpty()) {
            $consultationIds = Consultation::whereIn('visit_id', $visitIds)->pluck('id');
            if ($consultationIds->isNotEmpty()) {
                $prescriptionIds = Prescription::whereIn('consultation_id', $consultationIds)->pluck('id');
                PrescriptionItem::whereIn('prescription_id', $prescriptionIds)->delete();
                Prescription::whereIn('id', $prescriptionIds)->delete();
                Diagnosis::whereIn('consultation_id', $consultationIds)->delete();
                Consultation::whereIn('id', $consultationIds)->delete();
            }
            $admissionIds = Admission::whereIn('visit_id', $visitIds)->pluck('id');
            if ($admissionIds->isNotEmpty()) {
                DischargeSummary::whereIn('admission_id', $admissionIds)->delete();
                \App\Models\BedAssignment::whereIn('admission_id', $admissionIds)->delete();
                Admission::whereIn('id', $admissionIds)->delete();
            }
            AuditLog::where('auditable_type', Visit::class)->whereIn('auditable_id', $visitIds)->delete();
            Visit::whereIn('id', $visitIds)->delete();
        }

        PatientContact::updateOrCreate(
            ['patient_id' => $this->patient->id, 'is_primary' => true],
            [
                'name' => 'Nasrin Sultana Test',
                'relationship' => 'Spouse',
                'phone' => '01719888222',
            ]
        );
    }

    /**
     * Test unique visit number generation sequential format VIS-YYYYMMDD-XXXX.
     */
    public function test_unique_visit_number_auto_generation_sequence(): void
    {
        $testDate = Carbon::parse('2099-12-31');
        $prefix = 'VIS-20991231-';

        Visit::where('visit_number', 'like', $prefix . '%')->delete();

        $num1 = $this->visitService->generateVisitNumber($testDate);
        $this->assertEquals('VIS-20991231-0001', $num1);

        // Pre-create record
        Visit::create([
            'patient_id' => $this->patient->id,
            'visit_number' => 'VIS-20991231-0042',
            'visit_date' => $testDate,
            'visit_type' => 'regular',
            'triage_level' => 'normal',
            'status' => 'waiting',
        ]);

        $num2 = $this->visitService->generateVisitNumber($testDate);
        $this->assertEquals('VIS-20991231-0043', $num2);
    }

    /**
     * Test authorized staff can view clinic-wide visit directory with metrics and search.
     */
    public function test_authorized_staff_can_view_visits_directory_with_kpi_metrics_and_search(): void
    {
        Visit::where('visit_number', 'VIS-20261031-7701')->delete();
        Visit::create([
            'patient_id' => $this->patient->id,
            'visit_number' => 'VIS-20261031-7701',
            'visit_date' => now(),
            'visit_type' => 'emergency',
            'triage_level' => 'critical',
            'status' => 'waiting',
        ]);

        $response = $this->actingAs($this->receptionistUser)->get(route('portal.visits.index'));

        $response->assertOk();
        $response->assertViewIs('portal.visits.index');
        $response->assertSee('Patient Visits &amp; Triage Intake', false);
        $response->assertSee('VIS-20261031-7701');
        $response->assertSee('Md. Tariqul Islam Test');
        $response->assertSee('Critical');

        // Test search by visit number
        $searchResponse = $this->actingAs($this->receptionistUser)->get(route('portal.visits.index', ['search' => '7701']));
        $searchResponse->assertOk();
        $searchResponse->assertSee('VIS-20261031-7701');

        // Test filter by visit type
        $filterResponse = $this->actingAs($this->receptionistUser)->get(route('portal.visits.index', ['visit_type' => 'emergency']));
        $filterResponse->assertOk();
        $filterResponse->assertSee('VIS-20261031-7701');
    }

    /**
     * Test authorized staff can check in patient for visit with unique number and audit log.
     */
    public function test_authorized_staff_can_checkin_patient_for_visit_with_unique_number_and_audit_log(): void
    {
        $payload = [
            'patient_id' => $this->patient->id,
            'visit_date' => now()->format('Y-m-d H:i:s'),
            'visit_type' => 'regular',
            'triage_level' => 'priority',
            'status' => 'waiting',
            'chief_complaints' => 'Moderate fever (102°F) and severe joint pain for 3 days.',
            'doctor_id' => $this->doctorUser->id,
        ];

        $response = $this->actingAs($this->receptionistUser)->post(route('portal.visits.store'), $payload);

        $createdVisit = Visit::where('patient_id', $this->patient->id)
            ->where('triage_level', 'priority')
            ->latest('id')
            ->first();

        $this->assertNotNull($createdVisit);
        $this->assertMatchesRegularExpression('/^VIS-\d{8}-\d{4,}$/', $createdVisit->visit_number);
        $this->assertEquals('regular', $createdVisit->visit_type);
        $this->assertEquals('priority', $createdVisit->triage_level);
        $this->assertEquals('waiting', $createdVisit->status);

        // Verify consultation draft was initialized with chief complaints
        $this->assertDatabaseHas('consultations', [
            'visit_id' => $createdVisit->id,
            'chief_complaints' => 'Moderate fever (102°F) and severe joint pain for 3 days.',
        ]);

        // Verify audit log
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->receptionistUser->id,
            'event' => 'visit_created',
            'auditable_type' => Visit::class,
            'auditable_id' => $createdVisit->id,
        ]);

        $response->assertRedirect(route('portal.visits.show', $createdVisit));
        $response->assertSessionHas('success');
    }

    /**
     * Test one patient can have multiple visits and they remain linked to that same patient.
     */
    public function test_one_patient_can_have_multiple_visits(): void
    {
        Visit::whereIn('visit_number', ['VIS-20261025-0010', 'VIS-20261028-0011', 'VIS-20261031-0012'])->delete();
        Visit::where('patient_id', $this->patient->id)->delete();

        $v1 = Visit::create([
            'patient_id' => $this->patient->id,
            'visit_number' => 'VIS-20261025-0010',
            'visit_date' => now()->subDays(6),
            'visit_type' => 'regular',
            'triage_level' => 'normal',
            'status' => 'completed',
        ]);

        $v2 = Visit::create([
            'patient_id' => $this->patient->id,
            'visit_number' => 'VIS-20261028-0011',
            'visit_date' => now()->subDays(3),
            'visit_type' => 'emergency',
            'triage_level' => 'critical',
            'status' => 'completed',
        ]);

        $v3 = Visit::create([
            'patient_id' => $this->patient->id,
            'visit_number' => 'VIS-20261031-0012',
            'visit_date' => now(),
            'visit_type' => 'follow_up',
            'triage_level' => 'normal',
            'status' => 'waiting',
        ]);

        $this->patient->refresh();
        $this->assertEquals(3, $this->patient->visits()->count());
        $this->assertTrue($this->patient->visits->contains($v1));
        $this->assertTrue($this->patient->visits->contains($v2));
        $this->assertTrue($this->patient->visits->contains($v3));
    }

    /**
     * Test visits remain strictly linked to the same patient (immutability).
     */
    public function test_visits_remain_strictly_linked_to_same_patient_immutable(): void
    {
        Visit::where('visit_number', 'VIS-20261031-7703')->delete();
        Patient::where('patient_code', 'MCNH-2026-9902')->delete();

        $visit = Visit::create([
            'patient_id' => $this->patient->id,
            'visit_number' => 'VIS-20261031-7703',
            'visit_date' => now(),
            'visit_type' => 'regular',
            'triage_level' => 'normal',
            'status' => 'waiting',
        ]);

        $otherPatient = Patient::create([
            'patient_code' => 'MCNH-2026-9902',
            'name' => 'Other Patient Test',
            'date_of_birth' => '1990-01-01',
            'gender' => 'female',
            'phone' => '01719888333',
            'address' => 'Dinajpur',
            'registration_date' => now()->toDateString(),
        ]);

        // Attempting to mutate patient_id in service throws exception
        $this->expectException(\InvalidArgumentException::class);
        $this->visitService->updateVisit($visit, [
            'patient_id' => $otherPatient->id,
            'status' => 'in_consultation',
        ], $this->receptionistUser);
    }

    /**
     * Test visit distinguishes outpatient and inpatient care setting.
     */
    public function test_visit_distinguishes_outpatient_and_inpatient_with_admission(): void
    {
        Visit::where('visit_number', 'VIS-20261031-7704')->delete();
        Admission::where('admission_number', 'ADM-20261031-0001')->delete();

        $visit = Visit::create([
            'patient_id' => $this->patient->id,
            'visit_number' => 'VIS-20261031-7704',
            'visit_date' => now(),
            'visit_type' => 'regular',
            'triage_level' => 'normal',
            'status' => 'waiting',
        ]);

        // Outpatient before admission
        $this->assertTrue($visit->is_outpatient);
        $this->assertFalse($visit->is_inpatient);

        // Create admission linked to this visit
        $admission = Admission::create([
            'visit_id' => $visit->id,
            'patient_id' => $this->patient->id,
            'admission_number' => 'ADM-20261031-0001',
            'admission_date' => now(),
            'attending_doctor_id' => $this->doctorUser->id,
            'admission_reason' => 'Observation and orthopedic evaluation',
            'status' => 'admitted',
        ]);

        $visit->refresh();
        $this->assertFalse($visit->is_outpatient);
        $this->assertTrue($visit->is_inpatient);
    }

    /**
     * Test authorized staff can view visit dossier with complete clinical details.
     */
    public function test_authorized_staff_can_view_visit_dossier_with_clinical_details(): void
    {
        Visit::where('visit_number', 'VIS-20261031-7705')->delete();

        $visit = Visit::create([
            'patient_id' => $this->patient->id,
            'visit_number' => 'VIS-20261031-7705',
            'visit_date' => now(),
            'visit_type' => 'regular',
            'triage_level' => 'priority',
            'status' => 'in_consultation',
        ]);

        $consultation = Consultation::create([
            'visit_id' => $visit->id,
            'doctor_id' => $this->doctorUser->id,
            'chief_complaints' => 'Persistent cough and low grade fever for 1 week',
            'clinical_notes' => 'Chest clear, normal vesicular breath sounds',
            'status' => 'finalized',
            'finalized_at' => now(),
        ]);

        Diagnosis::create([
            'consultation_id' => $consultation->id,
            'condition_name' => 'Acute Bronchitis Test',
            'icd_code' => 'J20.9',
            'diagnosis_type' => 'provisional',
        ]);

        $prescription = Prescription::create([
            'consultation_id' => $consultation->id,
            'advice' => 'Drink warm water and rest for 3 days',
            'follow_up_date' => now()->addDays(7)->toDateString(),
        ]);

        PrescriptionItem::create([
            'prescription_id' => $prescription->id,
            'medicine_name' => 'Tab Azithromycin 500mg',
            'dosage' => '1 tablet',
            'frequency' => '1+0+0',
            'duration' => '5 days',
            'instructions' => 'Before meal',
        ]);

        $response = $this->actingAs($this->receptionistUser)->get(route('portal.visits.show', $visit->id));

        $response->assertOk();
        $response->assertViewIs('portal.visits.show');
        $response->assertSee('VIS-20261031-7705');
        $response->assertSee('Md. Tariqul Islam Test');
        $response->assertSee('MCNH-2026-9988');
        $response->assertSee('Persistent cough and low grade fever for 1 week');
        $response->assertSee('Acute Bronchitis Test');
        $response->assertSee('Tab Azithromycin 500mg');
        $response->assertSee('Drink warm water and rest for 3 days');
    }

    /**
     * Test authorized staff can update visit status and triage with audit logging.
     */
    public function test_authorized_staff_can_update_visit_status_and_triage(): void
    {
        Visit::where('visit_number', 'VIS-20261031-7706')->delete();

        $visit = Visit::create([
            'patient_id' => $this->patient->id,
            'visit_number' => 'VIS-20261031-7706',
            'visit_date' => now(),
            'visit_type' => 'regular',
            'triage_level' => 'normal',
            'status' => 'waiting',
        ]);

        $updateData = [
            'visit_date' => now()->format('Y-m-d H:i:s'),
            'visit_type' => 'emergency',
            'triage_level' => 'critical',
            'status' => 'in_consultation',
        ];

        $response = $this->actingAs($this->receptionistUser)->put(route('portal.visits.update', $visit->id), $updateData);

        $response->assertRedirect(route('portal.visits.show', $visit->id));

        $visit->refresh();
        $this->assertEquals('emergency', $visit->visit_type);
        $this->assertEquals('critical', $visit->triage_level);
        $this->assertEquals('in_consultation', $visit->status);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->receptionistUser->id,
            'event' => 'visit_updated',
            'auditable_type' => Visit::class,
            'auditable_id' => $visit->id,
        ]);
    }

    /**
     * Test viewing complete patient profile and medical record overview across all tabs.
     */
    public function test_authorized_staff_can_view_complete_patient_profile_and_medical_record_overview(): void
    {
        Visit::where('visit_number', 'VIS-20261031-7707')->delete();
        Admission::where('admission_number', 'ADM-20261031-7707')->delete();

        // 1. Visit
        $visit = Visit::create([
            'patient_id' => $this->patient->id,
            'visit_number' => 'VIS-20261031-7707',
            'visit_date' => now(),
            'visit_type' => 'regular',
            'triage_level' => 'normal',
            'status' => 'completed',
        ]);

        // 2. Consultation
        $consultation = Consultation::create([
            'visit_id' => $visit->id,
            'doctor_id' => $this->doctorUser->id,
            'chief_complaints' => 'Patient has fracture pain in left forearm',
            'status' => 'finalized',
        ]);

        // 3. Admission
        $admission = Admission::create([
            'visit_id' => $visit->id,
            'patient_id' => $this->patient->id,
            'admission_number' => 'ADM-20261031-7707',
            'admission_date' => now()->subDays(2),
            'discharge_date' => now(),
            'attending_doctor_id' => $this->doctorUser->id,
            'admission_reason' => 'Forearm fracture reduction and splinting',
            'clinical_clearance' => true,
            'financial_clearance' => true,
            'status' => 'discharged',
        ]);

        // 4. Discharge Summary
        DischargeSummary::create([
            'admission_id' => $admission->id,
            'discharge_diagnosis' => 'Closed distal radius fracture',
            'treatment_given' => 'Closed reduction under local anesthesia and POP splint',
            'condition_at_discharge' => 'improved',
            'discharge_medications' => 'Tab Paracetamol 500mg, Tab Calcium',
            'follow_up_instructions' => 'X-ray after 2 weeks in Ortho OPD',
            'authorized_by' => $this->doctorUser->id,
        ]);

        // Request patient profile view
        $response = $this->actingAs($this->receptionistUser)->get(route('portal.patients.show', $this->patient->id));

        $response->assertOk();
        $response->assertViewIs('portal.patients.show');
        $response->assertSee('Md. Tariqul Islam Test');
        $response->assertSee('MCNH-2026-9988');
        $response->assertSee('VIS-20261031-7707');
        $response->assertSee('Patient has fracture pain in left forearm');
        $response->assertSee('ADM-20261031-7707');
        $response->assertSee('Forearm fracture reduction and splinting');
        $response->assertSee('Closed distal radius fracture');
        $response->assertSee('Discharges');
    }

    /**
     * Test printable queue token.
     */
    public function test_authorized_staff_can_view_printable_visit_token(): void
    {
        Visit::where('visit_number', 'VIS-20261031-7708')->delete();

        $visit = Visit::create([
            'patient_id' => $this->patient->id,
            'visit_number' => 'VIS-20261031-7708',
            'visit_date' => now(),
            'visit_type' => 'emergency',
            'triage_level' => 'critical',
            'status' => 'waiting',
        ]);

        $response = $this->actingAs($this->receptionistUser)->get(route('portal.visits.print-token', $visit->id));

        $response->assertOk();
        $response->assertViewIs('portal.visits.print-token');
        $response->assertSee('MEGHNA CLINIC');
        $response->assertSee('PATIENT INTAKE TOKEN');
        $response->assertSee('VIS-20261031-7708');
        $response->assertSee('Md. Tariqul Islam Test');
        $response->assertSee('CRITICAL TRIAGE');
    }

    /**
     * Test printable medical record chart.
     */
    public function test_authorized_staff_can_view_printable_medical_record_chart(): void
    {
        $response = $this->actingAs($this->doctorUser)->get(route('portal.patients.medical-record', $this->patient->id));

        $response->assertOk();
        $response->assertViewIs('portal.patients.medical-record');
        $response->assertSee('MEGHNA CLINIC AND NURSING HOME');
        $response->assertSee('CONFIDENTIAL PATIENT MEDICAL RECORD');
        $response->assertSee('Md. Tariqul Islam Test');
        $response->assertSee('MCNH-2026-9988');
    }

    /**
     * Test strict authorization and access boundaries.
     */
    public function test_strict_authorization_and_data_isolation_enforced(): void
    {
        // 1. Unauthenticated guest cannot access visits
        $guestResponse = $this->get(route('portal.visits.index'));
        $guestResponse->assertRedirect(route('portal.login'));

        // 2. Patient portal realm user cannot access staff visits index
        $patientRealmResponse = $this->actingAs($this->patientUser)->get(route('portal.visits.index'));
        $patientRealmResponse->assertRedirect(route('patient.dashboard')); // Blocked by staff middleware

        // 3. Patient portal user cannot check-in visits
        $patientCreateResponse = $this->actingAs($this->patientUser)->get(route('portal.visits.create'));
        $patientCreateResponse->assertRedirect(route('patient.dashboard'));
    }
}
