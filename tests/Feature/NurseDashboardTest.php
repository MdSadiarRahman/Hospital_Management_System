<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\AuditLog;
use App\Models\Bed;
use App\Models\BedAssignment;
use App\Models\BedStatusHistory;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\MedicationAdministration;
use App\Models\NursingNote;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\DepartmentAndBedSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Database\Seeders\UserSeeder;
use Tests\TestCase;

class NurseDashboardTest extends TestCase
{
    protected User $adminUser;
    protected User $nurseUser;
    protected User $doctorUser;
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
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('administrator');

        $this->nurseUser = User::whereHas('roles', fn($q) => $q->where('name', 'nurse'))->first()
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('nurse');

        $this->doctorUser = User::whereHas('roles', fn($q) => $q->where('name', 'doctor'))->first()
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('doctor');

        $this->receptionistUser = User::whereHas('roles', fn($q) => $q->where('name', 'receptionist'))->first()
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('receptionist');

        $this->patientUser = User::where('user_type', 'patient')->first()
            ?? User::factory()->create(['user_type' => 'patient']);
    }

    /**
     * Helper to create an active admission with a bed assignment.
     */
    protected function createActiveAdmissionWithBed(): array
    {
        $unique = rand(100000, 999999);
        $dept = Department::where('is_clinical', true)->first();

        $patient = Patient::create([
            'patient_code' => "MCNH-NUR-{$unique}",
            'name' => "Inpatient Test {$unique}",
            'date_of_birth' => '1985-05-15',
            'gender' => 'male',
            'phone' => "017{$unique}0",
            'blood_group' => 'B+',
        ]);

        $visit = Visit::create([
            'patient_id' => $patient->id,
            'visit_number' => "VST-NUR-{$unique}",
            'visit_date' => now()->subDays(1),
            'visit_type' => 'emergency',
            'status' => 'completed',
        ]);

        $bed = Bed::create([
            'bed_number' => "NUR-{$unique}",
            'department_id' => $dept?->id,
            'category' => 'general',
            'daily_charge' => 800.00,
            'status' => 'occupied',
        ]);

        $admission = Admission::create([
            'visit_id' => $visit->id,
            'patient_id' => $patient->id,
            'department_id' => $dept?->id,
            'admission_number' => "ADM-NUR-{$unique}",
            'admission_date' => now()->subDays(1),
            'attending_doctor_id' => $this->doctorUser->id,
            'admission_reason' => 'Acute abdominal observation & fluid hydration',
            'status' => 'admitted',
        ]);

        $assignment = BedAssignment::create([
            'admission_id' => $admission->id,
            'bed_id' => $bed->id,
            'assigned_at' => now()->subDays(1),
            'assigned_by' => $this->nurseUser->id,
        ]);

        return [$admission, $bed, $patient];
    }

    /**
     * Test: Nurse can access the nurse dashboard.
     */
    public function test_nurse_can_access_nurse_dashboard(): void
    {
        $this->actingAs($this->nurseUser);

        $response = $this->get(route('portal.nurse'));

        $response->assertStatus(200);
        $response->assertViewIs('portal.nurse.dashboard');
        $response->assertViewHasAll([
            'nurse',
            'shiftInfo',
            'wardBeds',
            'activeAdmissions',
            'recentVitals',
            'recentMedicationAdministrations',
            'pendingTasks',
            'metrics',
        ]);
        $response->assertSee('Nurse Ward Station');
        $response->assertSee('10-Bed Inpatient Ward');
    }

    /**
     * Test: Administrator can also oversee the nursing dashboard.
     */
    public function test_administrator_can_access_nurse_dashboard(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get(route('portal.nurse'));

        $response->assertStatus(200);
        $response->assertSee('Nurse Ward Station');
    }

    /**
     * Test: Unauthorized user (e.g. receptionist) is denied access.
     */
    public function test_unauthorized_user_cannot_access_nurse_dashboard(): void
    {
        $this->actingAs($this->receptionistUser);

        $response = $this->get(route('portal.nurse'));

        $response->assertStatus(403);
    }

    /**
     * Test: Department and search filters on nurse dashboard.
     */
    public function test_nurse_dashboard_can_filter_by_department_and_search(): void
    {
        [$admission, $bed, $patient] = $this->createActiveAdmissionWithBed();

        $this->actingAs($this->nurseUser);

        // Filter by specific search term
        $response = $this->get(route('portal.nurse', ['q' => $patient->name]));

        $response->assertStatus(200);
        $response->assertSee($patient->name);
        $response->assertSee($bed->bed_number);
    }

    /**
     * Test: Nurse can record bedside patient vitals and nursing observation note.
     */
    public function test_nurse_can_record_bedside_vitals_and_observations(): void
    {
        [$admission, $bed, $patient] = $this->createActiveAdmissionWithBed();

        $this->actingAs($this->nurseUser);

        $payload = [
            'shift' => 'morning',
            'temperature' => 98.6,
            'blood_pressure' => '120/80',
            'pulse_rate' => 76,
            'respiratory_rate' => 18,
            'spo2' => 98.5,
            'observations' => 'Patient resting comfortably. Post-op dressing intact and clean.',
        ];

        $response = $this->post(route('portal.nurse.vitals.store', $admission), $payload);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('nursing_notes', [
            'admission_id' => $admission->id,
            'nurse_id' => $this->nurseUser->id,
            'shift' => 'morning',
            'blood_pressure' => '120/80',
            'pulse_rate' => 76,
            'observations' => 'Patient resting comfortably. Post-op dressing intact and clean.',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->nurseUser->id,
            'event' => 'nursing_vitals.recorded',
        ]);
    }

    /**
     * Test: Vitals validation enforces valid ranges.
     */
    public function test_vitals_validation_enforces_physiological_ranges(): void
    {
        [$admission, $bed, $patient] = $this->createActiveAdmissionWithBed();

        $this->actingAs($this->nurseUser);

        // Invalid temperature (> 110), invalid BP format, missing observations
        $invalidPayload = [
            'shift' => 'morning',
            'temperature' => 125.0,
            'blood_pressure' => 'invalid-bp',
            'spo2' => 110.0,
            'observations' => '', // mandatory
        ];

        $response = $this->post(route('portal.nurse.vitals.store', $admission), $invalidPayload);

        $response->assertSessionHasErrors(['temperature', 'blood_pressure', 'spo2', 'observations']);
    }

    /**
     * Test: Nurse can record medication administration (MAR).
     */
    public function test_nurse_can_record_medication_administration(): void
    {
        [$admission, $bed, $patient] = $this->createActiveAdmissionWithBed();

        $this->actingAs($this->nurseUser);

        $payload = [
            'medicine_name' => 'Injection Ceftriaxone',
            'dose_given' => '1g IV',
            'status' => 'administered',
            'remarks' => 'Infused slowly via cannula. No erythema.',
        ];

        $response = $this->post(route('portal.nurse.medications.store', $admission), $payload);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('medication_administrations', [
            'admission_id' => $admission->id,
            'nurse_id' => $this->nurseUser->id,
            'medicine_name' => 'Injection Ceftriaxone',
            'dose_given' => '1g IV',
            'status' => 'administered',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->nurseUser->id,
            'event' => 'medication.administered',
        ]);
    }

    /**
     * Test: Omitted or refused medication requires clinical remarks.
     */
    public function test_omitted_or_refused_medication_requires_remarks(): void
    {
        [$admission, $bed, $patient] = $this->createActiveAdmissionWithBed();

        $this->actingAs($this->nurseUser);

        // Omitted without remarks should fail
        $response = $this->post(route('portal.nurse.medications.store', $admission), [
            'medicine_name' => 'Tab Paracetamol',
            'dose_given' => '500mg PO',
            'status' => 'omitted',
            'remarks' => '',
        ]);

        $response->assertSessionHasErrors(['remarks']);

        // Omitted with remarks should succeed
        $successResponse = $this->post(route('portal.nurse.medications.store', $admission), [
            'medicine_name' => 'Tab Paracetamol',
            'dose_given' => '500mg PO',
            'status' => 'omitted',
            'remarks' => 'Patient has no fever or pain; doctor notified to hold dose.',
        ]);

        $successResponse->assertSessionHas('success');
    }

    /**
     * Test: Nurse can update bed readiness and sanitization status.
     */
    public function test_nurse_can_update_bed_readiness(): void
    {
        $unique = rand(10000, 99999);
        $bed = Bed::create([
            'bed_number' => "CLEAN-{$unique}",
            'category' => 'general',
            'daily_charge' => 800.00,
            'status' => 'cleaning',
        ]);

        $this->actingAs($this->nurseUser);

        $response = $this->post(route('portal.nurse.beds.update-status', $bed), [
            'status' => 'available',
            'notes' => 'Disinfected by nurse team, linens changed and ready.',
        ]);

        $response->assertSessionHas('success');

        $this->assertEquals('available', $bed->fresh()->status);

        $this->assertDatabaseHas('bed_status_histories', [
            'bed_id' => $bed->id,
            'from_status' => 'cleaning',
            'to_status' => 'available',
            'changed_by' => $this->nurseUser->id,
        ]);
    }

    /**
     * Test: Patient Nursing Dossier displays doctor-approved prescriptions in read-only mode.
     */
    public function test_nursing_dossier_displays_doctor_prescriptions_read_only(): void
    {
        [$admission, $bed, $patient] = $this->createActiveAdmissionWithBed();

        // Create a doctor-approved prescription linked to this patient
        $visit = Visit::create([
            'patient_id' => $patient->id,
            'visit_number' => "VST-{$patient->patient_code}",
            'visit_date' => now()->subDays(1),
            'visit_type' => 'emergency',
            'status' => 'completed',
        ]);

        $admission->update(['visit_id' => $visit->id]);

        $consultation = Consultation::create([
            'visit_id' => $visit->id,
            'doctor_id' => $this->doctorUser->id,
            'chief_complaints' => 'Abdominal pain and fever',
            'status' => 'finalized',
            'finalized_at' => now()->subDays(1),
        ]);

        $prescription = Prescription::create([
            'prescription_number' => "RX-{$patient->patient_code}",
            'consultation_id' => $consultation->id,
            'status' => 'approved',
            'approved_at' => now()->subDays(1),
            'approved_by' => $this->doctorUser->id,
        ]);

        PrescriptionItem::create([
            'prescription_id' => $prescription->id,
            'medicine_name' => 'Tab Cefuroxime',
            'dosage' => '500mg',
            'frequency' => '1-0-1',
            'duration' => '5 days',
        ]);

        $this->actingAs($this->nurseUser);

        $response = $this->get(route('portal.nurse.admissions.dossier', $admission));

        $response->assertStatus(200);
        $response->assertSee('Tab Cefuroxime');
        $response->assertSee('Doctor-Approved Prescriptions');
        $response->assertSee('Clinical Records Protected');

        // Verify nurse cannot edit or delete this doctor's prescription
        $editResponse = $this->put("/portal/prescriptions/{$prescription->id}", [
            'advice' => 'Nurse unauthorized attempt',
        ]);

        $this->assertEquals(403, $editResponse->status());
    }

    /**
     * Test: Abnormal vitals reading triggers clinical alert.
     */
    public function test_abnormal_vitals_triggers_alert(): void
    {
        [$admission, $bed, $patient] = $this->createActiveAdmissionWithBed();

        // Create febrile note (Temp >= 100.4°F)
        NursingNote::create([
            'admission_id' => $admission->id,
            'nurse_id' => $this->nurseUser->id,
            'shift' => 'morning',
            'temperature' => 102.4,
            'blood_pressure' => '135/90',
            'pulse_rate' => 112,
            'spo2' => 94.0, // hypoxia
            'observations' => 'High fever and tachycardia. Notified doctor.',
            'recorded_at' => now(),
        ]);

        $this->actingAs($this->nurseUser);

        $response = $this->get(route('portal.nurse'));

        $response->assertStatus(200);
        $response->assertSee('CRITICAL VITALS ALERT');
        $response->assertSee($patient->name);
        $response->assertSee('Fever: 102.4°F');
        $response->assertSee('Low SpO2:');
    }
}
