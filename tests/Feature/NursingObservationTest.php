<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\AuditLog;
use App\Models\Bed;
use App\Models\BedAssignment;
use App\Models\Department;
use App\Models\NursingNote;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\DepartmentAndBedSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Database\Seeders\UserSeeder;
use Tests\TestCase;

class NursingObservationTest extends TestCase
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
     * Helper to create active inpatient admission.
     */
    protected function createInpatient(): array
    {
        $unique = rand(100000, 999999);
        $dept = Department::where('is_clinical', true)->first();

        $patient = Patient::create([
            'patient_code' => "MCNH-OBS-{$unique}",
            'name' => "Observation Inpatient {$unique}",
            'date_of_birth' => '1988-08-12',
            'gender' => 'female',
            'phone' => "017{$unique}1",
            'blood_group' => 'O+',
        ]);

        $visit = Visit::create([
            'patient_id' => $patient->id,
            'visit_number' => "VST-OBS-{$unique}",
            'visit_date' => now()->subDays(2),
            'visit_type' => 'regular',
            'status' => 'completed',
        ]);

        $bed = Bed::create([
            'bed_number' => "OBS-{$unique}",
            'department_id' => $dept?->id,
            'category' => 'general',
            'daily_charge' => 800.00,
            'status' => 'occupied',
        ]);

        $admission = Admission::create([
            'visit_id' => $visit->id,
            'patient_id' => $patient->id,
            'department_id' => $dept?->id,
            'admission_number' => "ADM-OBS-{$unique}",
            'admission_date' => now()->subDays(2),
            'attending_doctor_id' => $this->doctorUser->id,
            'admission_reason' => 'Post-operative recovery and vital signs tracking',
            'status' => 'admitted',
        ]);

        BedAssignment::create([
            'admission_id' => $admission->id,
            'bed_id' => $bed->id,
            'assigned_at' => now()->subDays(2),
            'assigned_by' => $this->nurseUser->id,
        ]);

        return [$admission, $bed, $patient];
    }

    /**
     * Test: Nurse can view the Master Nursing Observations Ledger.
     */
    public function test_nurse_can_access_nursing_observations_ledger(): void
    {
        $this->actingAs($this->nurseUser);

        $response = $this->get(route('portal.nurse.observations'));

        $response->assertStatus(200);
        $response->assertViewIs('portal.nurse.observations_index');
        $response->assertSee('Clinical Nursing Observations');
        $response->assertSee('Stored Units');
    }

    /**
     * Test: Record actual measured values and verify units are stored correctly.
     */
    public function test_record_actual_measured_values_and_store_units(): void
    {
        [$admission, $bed, $patient] = $this->createInpatient();

        $this->actingAs($this->nurseUser);

        $payload = [
            'shift' => 'morning',
            'temperature' => 98.4,
            'temperature_unit' => '°F',
            'blood_pressure' => '118/76',
            'pulse_rate' => 74,
            'respiratory_rate' => 16,
            'spo2' => 99.0,
            'weight' => 64.5,
            'weight_unit' => 'kg',
            'observations' => 'Patient conscious, alert. Normal surgical wound appearance. Tolerating oral fluids.',
            'recorded_at' => now()->format('Y-m-d H:i:s'),
        ];

        $response = $this->post(route('portal.nurse.vitals.store', $admission), $payload);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('nursing_notes', [
            'admission_id' => $admission->id,
            'nurse_id' => $this->nurseUser->id,
            'shift' => 'morning',
            'temperature' => 98.4,
            'temperature_unit' => '°F',
            'blood_pressure' => '118/76',
            'blood_pressure_unit' => 'mmHg',
            'pulse_rate' => 74,
            'pulse_rate_unit' => 'bpm',
            'respiratory_rate' => 16,
            'respiratory_rate_unit' => 'breaths/min',
            'spo2' => 99.0,
            'spo2_unit' => '%',
            'weight' => 64.50,
            'weight_unit' => 'kg',
            'is_flagged_abnormal' => false,
            'clinical_review_status' => 'normal',
        ]);
    }

    /**
     * Test: Flag unusual values for authorized clinical review without automated medical diagnosis.
     */
    public function test_unusual_values_flagged_for_clinical_review_without_automated_diagnosis(): void
    {
        [$admission, $bed, $patient] = $this->createInpatient();

        $this->actingAs($this->nurseUser);

        // High fever (102.8°F), Tachycardia (118 bpm), and Hypoxia (93.5%)
        $payload = [
            'shift' => 'evening',
            'temperature' => 102.8,
            'temperature_unit' => '°F',
            'blood_pressure' => '145/95',
            'pulse_rate' => 118,
            'respiratory_rate' => 26,
            'spo2' => 93.5,
            'observations' => 'Patient flushed and shivering. Febrile spike and tachypnea. Sponging started and notified MO.',
        ];

        $response = $this->post(route('portal.nurse.vitals.store', $admission), $payload);

        $response->assertSessionHas('success');

        $note = NursingNote::where('admission_id', $admission->id)->latest('id')->first();
        $this->assertNotNull($note);
        $this->assertTrue($note->is_flagged_abnormal);
        $this->assertEquals('flagged_for_review', $note->clinical_review_status);
        $this->assertIsArray($note->clinical_flags);
        $this->assertNotEmpty($note->clinical_flags);

        // Verify parameter alerts are recorded for physician review
        $flagsString = implode(' ', $note->clinical_flags);
        $this->assertStringContainsString('Elevated Temperature', $flagsString);
        $this->assertStringContainsString('Tachycardia', $flagsString);
        $this->assertStringContainsString('Hypoxia', $flagsString);
    }

    /**
     * Test: Plausible range validation and systolic greater than diastolic enforcement.
     */
    public function test_input_validation_enforces_plausible_ranges(): void
    {
        [$admission, $bed, $patient] = $this->createInpatient();

        $this->actingAs($this->nurseUser);

        // Invalid BP (systolic < diastolic: 70/130), impossible temp (115°F), negative pulse
        $invalidPayload = [
            'shift' => 'morning',
            'temperature' => 115.0,
            'blood_pressure' => '70/130',
            'pulse_rate' => 280, // out of range
            'respiratory_rate' => 75, // out of range
            'spo2' => 45.0, // out of range
            'weight' => 500.0, // out of range
            'observations' => 'Test observation',
        ];

        $response = $this->post(route('portal.nurse.vitals.store', $admission), $invalidPayload);

        $response->assertSessionHasErrors([
            'temperature',
            'blood_pressure',
            'pulse_rate',
            'respiratory_rate',
            'spo2',
            'weight',
        ]);
    }

    /**
     * Test: Complete observation history is maintained across multiple readings.
     */
    public function test_complete_observation_history_is_maintained(): void
    {
        [$admission, $bed, $patient] = $this->createInpatient();

        $this->actingAs($this->nurseUser);

        // Create 3 sequential shift observations
        NursingNote::create([
            'admission_id' => $admission->id,
            'nurse_id' => $this->nurseUser->id,
            'shift' => 'morning',
            'temperature' => 98.4,
            'blood_pressure' => '120/80',
            'pulse_rate' => 72,
            'spo2' => 99.0,
            'observations' => 'Day 1 morning observation: Stable.',
            'recorded_at' => now()->subHours(16),
        ]);

        NursingNote::create([
            'admission_id' => $admission->id,
            'nurse_id' => $this->nurseUser->id,
            'shift' => 'evening',
            'temperature' => 99.2,
            'blood_pressure' => '124/82',
            'pulse_rate' => 84,
            'spo2' => 98.0,
            'observations' => 'Day 1 evening observation: Mild evening warmth.',
            'recorded_at' => now()->subHours(8),
        ]);

        NursingNote::create([
            'admission_id' => $admission->id,
            'nurse_id' => $this->nurseUser->id,
            'shift' => 'night',
            'temperature' => 98.6,
            'blood_pressure' => '118/78',
            'pulse_rate' => 70,
            'spo2' => 99.5,
            'observations' => 'Day 2 morning observation: Sleeping soundly.',
            'recorded_at' => now(),
        ]);

        $this->assertEquals(3, $admission->nursingNotes()->count());
    }

    /**
     * Test: Support viewing patient vital trends and trajectory.
     */
    public function test_support_viewing_patient_vital_trends_and_flowsheet(): void
    {
        [$admission, $bed, $patient] = $this->createInpatient();

        // Add 2 readings
        NursingNote::create([
            'admission_id' => $admission->id,
            'nurse_id' => $this->nurseUser->id,
            'shift' => 'morning',
            'temperature' => 98.0,
            'blood_pressure' => '115/75',
            'pulse_rate' => 68,
            'spo2' => 99.0,
            'respiratory_rate' => 16,
            'weight' => 62.0,
            'observations' => 'Initial baseline check.',
            'recorded_at' => now()->subHours(4),
        ]);

        NursingNote::create([
            'admission_id' => $admission->id,
            'nurse_id' => $this->nurseUser->id,
            'shift' => 'evening',
            'temperature' => 99.0,
            'blood_pressure' => '125/80',
            'pulse_rate' => 82,
            'spo2' => 98.0,
            'respiratory_rate' => 18,
            'weight' => 62.2,
            'observations' => 'Follow up check.',
            'recorded_at' => now(),
        ]);

        $this->actingAs($this->nurseUser);

        // View Trends
        $response = $this->get(route('portal.nurse.admissions.trends', $admission));
        $response->assertStatus(200);
        $response->assertViewIs('portal.nurse.vitals_trends');
        $response->assertSee('Patient Vital Trends');
        $response->assertSee('Body Temperature');
        $response->assertSee('Oxygen Saturation');

        // View Printable Flowsheet
        $flowsheetResponse = $this->get(route('portal.nurse.admissions.flowsheet', $admission));
        $flowsheetResponse->assertStatus(200);
        $flowsheetResponse->assertSee('MEGHNA CLINIC', false);
        $flowsheetResponse->assertSee('FLOWSHEET', false);
    }

    /**
     * Test: Authorized physician can review and verify a flagged observation.
     */
    public function test_authorized_physician_can_review_flagged_observation(): void
    {
        [$admission, $bed, $patient] = $this->createInpatient();

        $flaggedNote = NursingNote::create([
            'admission_id' => $admission->id,
            'nurse_id' => $this->nurseUser->id,
            'shift' => 'morning',
            'temperature' => 102.0,
            'blood_pressure' => '142/92',
            'pulse_rate' => 108,
            'spo2' => 94.0,
            'observations' => 'Elevated temp and pulse.',
            'is_flagged_abnormal' => true,
            'clinical_flags' => ['Elevated Temperature (Fever: 102.0°F)', 'Hypoxia (94.0%)'],
            'clinical_review_status' => 'flagged_for_review',
            'recorded_at' => now(),
        ]);

        // Doctor reviews the flagged note
        $this->actingAs($this->doctorUser);

        $response = $this->post(route('portal.nurse.observations.review', $flaggedNote), [
            'review_notes' => 'Patient examined. Administered IV paracetamol and placed on 2L O2 via nasal prongs.',
        ]);

        $response->assertSessionHas('success');

        $freshNote = $flaggedNote->fresh();
        $this->assertEquals('reviewed', $freshNote->clinical_review_status);
        $this->assertEquals($this->doctorUser->id, $freshNote->reviewed_by);
        $this->assertNotNull($freshNote->reviewed_at);
        $this->assertEquals('Patient examined. Administered IV paracetamol and placed on 2L O2 via nasal prongs.', $freshNote->review_notes);

        // Audit Trail
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->doctorUser->id,
            'event' => 'nursing_observation.reviewed',
            'auditable_id' => $flaggedNote->id,
        ]);
    }

    /**
     * Test: Patient cannot access staff nursing observations (redirected to patient dashboard).
     */
    public function test_patient_cannot_access_staff_nursing_observations(): void
    {
        $this->actingAs($this->patientUser);

        $response = $this->get(route('portal.nurse.observations'));

        $response->assertRedirect(route('patient.dashboard'));

        // Staff without clinical roles receives 403 Forbidden
        $receptionist = User::whereHas('roles', fn($q) => $q->where('name', 'receptionist'))->first();
        if ($receptionist) {
            $this->actingAs($receptionist);
            $recResponse = $this->get(route('portal.nurse.observations'));
            $recResponse->assertStatus(403);
        }
    }
}
