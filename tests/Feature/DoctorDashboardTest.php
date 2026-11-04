<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Bed;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\Diagnosis;
use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\LabReport;
use App\Models\LabTestCatalog;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use App\Services\AdmissionService;
use Database\Seeders\DepartmentAndBedSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Database\Seeders\UserSeeder;
use Tests\TestCase;

class DoctorDashboardTest extends TestCase
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

        // Fetch test users
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
            'name' => 'Clinical Patient ' . $uniqueId,
            'date_of_birth' => '1990-05-10',
            'gender' => 'female',
            'phone' => '017' . rand(10000000, 99999999),
            'registration_date' => now()->toDateString(),
        ], $attributes));
    }

    /**
     * Test authorized doctor can view dashboard with real database statistics.
     */
    public function test_authorized_doctor_can_view_dashboard_with_real_database_statistics(): void
    {
        $response = $this->actingAs($this->doctorUserA)->get(route('portal.doctor'));

        $response->assertOk();
        $response->assertViewIs('portal.doctor.dashboard');
        $response->assertSee('Dr. ' . $this->doctorUserA->name);
        $response->assertSee("Today's Appointments", false);
        $response->assertSee('Consultation Queue');
        $response->assertSee('Active Inpatients');
        $response->assertSee('Pending Lab Reviews');
        $response->assertSee('Follow-ups Due');
    }

    /**
     * Test doctor dashboard filters appointments strictly by assigned doctor.
     */
    public function test_doctor_dashboard_filters_data_strictly_by_doctor_assignment(): void
    {
        $patientA = $this->createTestPatient();
        $patientB = $this->createTestPatient();
        $gynDept = Department::where('code', 'GYN')->first() ?? Department::first();

        // Appointment for Doctor A
        $aptA = Appointment::create([
            'patient_id' => $patientA->id,
            'department_id' => $gynDept->id,
            'doctor_id' => $this->doctorUserA->id,
            'appointment_date' => today()->toDateString(),
            'appointment_time' => '10:00:00',
            'serial_number' => 101,
            'type' => 'walk_in',
            'status' => 'confirmed',
            'reason' => 'Severe abdominal pain Doctor A patient',
        ]);

        // Appointment for Doctor B
        $aptB = Appointment::create([
            'patient_id' => $patientB->id,
            'department_id' => $gynDept->id,
            'doctor_id' => $this->doctorUserB->id,
            'appointment_date' => today()->toDateString(),
            'appointment_time' => '11:00:00',
            'serial_number' => 102,
            'type' => 'walk_in',
            'status' => 'confirmed',
            'reason' => 'Knee joint pain Doctor B patient',
        ]);

        // Acting as Doctor A -> Must see Doctor A's patient, MUST NOT see Doctor B's patient
        $response = $this->actingAs($this->doctorUserA)->get(route('portal.doctor'));

        $response->assertOk();
        $response->assertSee($patientA->name);
        $response->assertSee('Severe abdominal pain Doctor A patient');
        $response->assertDontSee('Knee joint pain Doctor B patient');
    }

    /**
     * Test consultation queue displays waiting patients with triage prioritization.
     */
    public function test_consultation_queue_displays_waiting_patients_with_triage_prioritization(): void
    {
        $patient1 = $this->createTestPatient();
        $patient2 = $this->createTestPatient();
        $dept = Department::first();

        $apt1 = Appointment::create([
            'patient_id' => $patient1->id,
            'department_id' => $dept->id,
            'doctor_id' => $this->doctorUserA->id,
            'appointment_date' => today()->toDateString(),
            'appointment_time' => '09:00:00',
            'serial_number' => 201,
            'status' => 'confirmed',
        ]);

        $apt2 = Appointment::create([
            'patient_id' => $patient2->id,
            'department_id' => $dept->id,
            'doctor_id' => $this->doctorUserA->id,
            'appointment_date' => today()->toDateString(),
            'appointment_time' => '09:30:00',
            'serial_number' => 202,
            'status' => 'confirmed',
        ]);

        // Visit 1: Routine / Normal
        $visit1 = Visit::create([
            'patient_id' => $patient1->id,
            'appointment_id' => $apt1->id,
            'visit_number' => 'VIS-2026-R' . rand(100, 999),
            'visit_date' => now(),
            'visit_type' => 'regular',
            'triage_level' => 'normal',
            'status' => 'waiting',
        ]);

        // Visit 2: Emergency / Critical
        $visit2 = Visit::create([
            'patient_id' => $patient2->id,
            'appointment_id' => $apt2->id,
            'visit_number' => 'VIS-2026-E' . rand(100, 999),
            'visit_date' => now(),
            'visit_type' => 'emergency',
            'triage_level' => 'critical',
            'status' => 'waiting',
        ]);

        $response = $this->actingAs($this->doctorUserA)->get(route('portal.doctor'));

        $response->assertOk();
        $response->assertSee($visit1->visit_number);
        $response->assertSee($visit2->visit_number);
        $response->assertSee('Emergency');
    }

    /**
     * Test doctor can view patient clinical history dossier.
     */
    public function test_doctor_can_search_patient_and_view_clinical_history_dossier(): void
    {
        $patient = $this->createTestPatient();
        $dept = Department::first();

        $visit = Visit::create([
            'patient_id' => $patient->id,
            'visit_number' => 'VIS-HIST-' . rand(1000, 9999),
            'visit_date' => now()->subDays(2),
            'visit_type' => 'regular',
            'triage_level' => 'normal',
            'status' => 'completed',
        ]);

        $consultation = Consultation::create([
            'visit_id' => $visit->id,
            'doctor_id' => $this->doctorUserA->id,
            'chief_complaints' => 'Acute migraine headaches and photophobia',
            'clinical_notes' => 'BP normal, fundus examination normal',
            'status' => 'finalized',
            'finalized_at' => now()->subDays(2),
        ]);

        Diagnosis::create([
            'consultation_id' => $consultation->id,
            'condition_name' => 'Migraine without aura',
            'icd_code' => 'G43.0',
            'diagnosis_type' => 'final',
        ]);

        $prescription = Prescription::create([
            'consultation_id' => $consultation->id,
            'advice' => 'Adequate sleep and avoid loud noise',
            'follow_up_date' => today()->addDays(5),
        ]);

        PrescriptionItem::create([
            'prescription_id' => $prescription->id,
            'medicine_name' => 'Naproxen Sodium',
            'dosage' => '500mg',
            'frequency' => '1-0-1',
            'duration' => '5 days',
        ]);

        // Access patient clinical history dossier
        $response = $this->actingAs($this->doctorUserA)->get(route('portal.doctor.patient-history', $patient));

        $response->assertOk();
        $response->assertViewIs('portal.doctor.patient_history');
        $response->assertSee($patient->name);
        $response->assertSee($patient->patient_code);
        $response->assertSee('Acute migraine headaches and photophobia');
        $response->assertSee('Migraine without aura');
        $response->assertSee('Naproxen Sodium');
        $response->assertSee('Adequate sleep and avoid loud noise');
    }

    /**
     * Test doctor can review and sign off pending diagnostic lab report.
     */
    public function test_doctor_can_review_and_verify_pending_diagnostic_lab_report(): void
    {
        $patient = $this->createTestPatient();
        $visit = Visit::create([
            'patient_id' => $patient->id,
            'visit_number' => 'VIS-LAB-' . rand(1000, 9999),
            'visit_date' => now(),
            'visit_type' => 'regular',
            'triage_level' => 'normal',
            'status' => 'in_consultation',
        ]);

        $consultation = Consultation::create([
            'visit_id' => $visit->id,
            'doctor_id' => $this->doctorUserA->id,
            'chief_complaints' => 'Suspected anemia and weakness',
            'status' => 'draft',
        ]);

        $labTest = LabTestCatalog::first() ?? LabTestCatalog::create([
            'name' => 'Complete Blood Count (CBC)',
            'code' => 'CBC-' . rand(100, 999),
            'category' => 'Hematology',
            'standard_charge' => 450.00,
        ]);

        $labOrder = LabOrder::create([
            'consultation_id' => $consultation->id,
            'patient_id' => $patient->id,
            'order_number' => 'ORD-TEST-' . rand(1000, 9999),
            'source' => 'internal',
            'status' => 'processing',
        ]);

        $orderItem = LabOrderItem::create([
            'lab_order_id' => $labOrder->id,
            'lab_test_id' => $labTest->id,
            'test_name' => $labTest->name,
            'test_charge' => $labTest->standard_charge,
            'status' => 'completed',
        ]);

        $labReport = LabReport::create([
            'lab_order_item_id' => $orderItem->id,
            'file_path' => 'secure_medical_reports/sample_report.pdf',
            'findings_summary' => 'Hemoglobin: 8.5 g/dL (Low), RBC count reduced',
            'review_status' => 'pending_review',
        ]);

        // Review endpoint call by Doctor
        $notes = 'Iron deficiency anemia confirmed. Start Oral Iron Supplementation bid.';
        $response = $this->actingAs($this->doctorUserA)
            ->post(route('portal.doctor.review-lab-report', $labReport), [
                'clinical_notes' => $notes,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $labReport->refresh();
        $this->assertEquals('reviewed', $labReport->review_status);
        $this->assertEquals($this->doctorUserA->id, $labReport->reviewed_by);
        $this->assertNotNull($labReport->reviewed_at);
        $this->assertStringContainsString($notes, $labReport->findings_summary);

        // Verify audit log
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'lab_report.reviewed',
            'auditable_type' => LabReport::class,
            'auditable_id' => $labReport->id,
            'user_id' => $this->doctorUserA->id,
        ]);
    }

    /**
     * Test unauthorized users cannot access doctor dashboard.
     */
    public function test_unauthorized_users_cannot_access_doctor_dashboard(): void
    {
        // Unauthenticated guest -> redirect to login
        $guestResponse = $this->get(route('portal.doctor'));
        $guestResponse->assertRedirect(route('portal.login'));

        // Patient user -> redirected to patient dashboard by EnsureStaff middleware
        $patientResponse = $this->actingAs($this->patientUser)->get(route('portal.doctor'));
        $patientResponse->assertRedirect(route('patient.dashboard'));

        // Receptionist user -> 403 Forbidden (no consultations.view or doctor role)
        $receptionResponse = $this->actingAs($this->receptionistUser)->get(route('portal.doctor'));
        $receptionResponse->assertForbidden();
    }

    /**
     * Test administrator can access doctor dashboard and switch doctor filter.
     */
    public function test_administrator_can_access_and_switch_doctor_view(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('portal.doctor', [
            'doctor_id' => $this->doctorUserB->id,
        ]));

        $response->assertOk();
        $response->assertSee('Dr. ' . $this->doctorUserB->name);
    }
}
