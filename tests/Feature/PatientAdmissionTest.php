<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Bed;
use App\Models\BedAssignment;
use App\Models\BedStatusHistory;
use App\Models\Department;
use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use App\Services\AdmissionService;
use Database\Seeders\DepartmentAndBedSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Database\Seeders\UserSeeder;
use InvalidArgumentException;
use Tests\TestCase;

class PatientAdmissionTest extends TestCase
{
    protected User $adminUser;
    protected User $doctorUser;
    protected User $receptionistUser;
    protected User $nurseUser;
    protected User $patientUser;
    protected AdmissionService $admissionService;

    protected function setUp(): void
    {
        parent::setUp();

        if (Department::count() === 0 || Bed::count() === 0) {
            $this->seed(RolePermissionSeeder::class);
            $this->seed(DepartmentAndBedSeeder::class);
            $this->seed(SystemSettingsSeeder::class);
            $this->seed(UserSeeder::class);
        }

        $this->admissionService = app(AdmissionService::class);

        // Fetch test users
        $this->adminUser = User::whereHas('roles', fn($q) => $q->where('name', 'administrator'))->first()
            ?? User::factory()->create(['user_type' => 'staff']);
        if (!$this->adminUser->hasRole('administrator')) {
            $role = Role::where('name', 'administrator')->first();
            if ($role) $this->adminUser->roles()->syncWithoutDetaching([$role->id]);
        }

        $this->doctorUser = User::whereHas('roles', fn($q) => $q->where('name', 'doctor'))->first()
            ?? User::factory()->create(['user_type' => 'staff']);
        if (!$this->doctorUser->hasRole('doctor')) {
            $role = Role::where('name', 'doctor')->first();
            if ($role) $this->doctorUser->roles()->syncWithoutDetaching([$role->id]);
        }

        $this->receptionistUser = User::whereHas('roles', fn($q) => $q->where('name', 'receptionist'))->first()
            ?? User::factory()->create(['user_type' => 'staff']);
        if (!$this->receptionistUser->hasRole('receptionist')) {
            $role = Role::where('name', 'receptionist')->first();
            if ($role) $this->receptionistUser->roles()->syncWithoutDetaching([$role->id]);
        }

        $this->nurseUser = User::whereHas('roles', fn($q) => $q->where('name', 'nurse'))->first()
            ?? User::factory()->create(['user_type' => 'staff']);
        if (!$this->nurseUser->hasRole('nurse')) {
            $role = Role::where('name', 'nurse')->first();
            if ($role) $this->nurseUser->roles()->syncWithoutDetaching([$role->id]);
        }

        $this->patientUser = User::where('user_type', 'patient')->first()
            ?? User::factory()->create(['user_type' => 'patient']);
    }

    protected function createTestPatient(array $attributes = []): Patient
    {
        $uniqueId = rand(100000, 999999);
        return Patient::create(array_merge([
            'patient_code' => 'MCNH-2026-' . $uniqueId,
            'name' => 'Test Inpatient ' . $uniqueId,
            'date_of_birth' => '1990-01-01',
            'gender' => 'female',
            'phone' => '017' . rand(10000000, 99999999),
            'registration_date' => now()->toDateString(),
        ], $attributes));
    }

    protected function createTestVisit(Patient $patient, array $attributes = []): Visit
    {
        $uniqueId = rand(100000, 999999);
        return Visit::create(array_merge([
            'patient_id' => $patient->id,
            'visit_number' => 'VIS-' . now()->format('Ymd') . '-' . $uniqueId,
            'visit_date' => now(),
            'visit_type' => 'emergency',
            'triage_level' => 'priority',
            'status' => 'admitted',
        ], $attributes));
    }

    /**
     * Test creating an admission for a registered patient with a linked visit.
     */
    public function test_can_create_admission_for_registered_patient_with_linked_visit(): void
    {
        $patient = $this->createTestPatient();
        $visit = $this->createTestVisit($patient);
        $bed = Bed::where('status', 'available')->first() ?? Bed::create([
            'bed_number' => 'TEST-ADM-BED-1',
            'room_number' => 'Ward 101',
            'category' => 'general',
            'status' => 'available',
            'daily_charge' => 500.00,
        ]);

        $payload = [
            'patient_id' => $patient->id,
            'visit_id' => $visit->id,
            'bed_id' => $bed->id,
            'attending_doctor_id' => $this->doctorUser->id,
            'admission_date' => now()->format('Y-m-d H:i:s'),
            'admission_reason' => 'Acute lower abdominal pain requiring observation and surgical consult',
            'notes' => 'Monitor vital signs every 4 hours, NPO post-midnight',
        ];

        $response = $this->actingAs($this->receptionistUser)
            ->post(route('portal.admissions.store'), $payload);

        $response->assertRedirect();

        // 1. Verify admission record exists
        $this->assertDatabaseHas('admissions', [
            'patient_id' => $patient->id,
            'visit_id' => $visit->id,
            'attending_doctor_id' => $this->doctorUser->id,
            'status' => 'admitted',
        ]);

        $admission = Admission::where('patient_id', $patient->id)->latest()->first();
        $this->assertNotNull($admission);

        // 2. Admission number follows ADM-YYYYMMDD-XXX format
        $todayPrefix = 'ADM-' . now()->format('Ymd') . '-';
        $this->assertStringStartsWith($todayPrefix, $admission->admission_number);

        // 3. Target bed status updated to 'occupied'
        $bed->refresh();
        $this->assertEquals('occupied', $bed->status);

        // 4. BedAssignment created
        $this->assertDatabaseHas('bed_assignments', [
            'admission_id' => $admission->id,
            'bed_id' => $bed->id,
            'released_at' => null,
        ]);

        // 5. Bed status history recorded
        $this->assertDatabaseHas('bed_status_histories', [
            'bed_id' => $bed->id,
            'from_status' => 'available',
            'to_status' => 'occupied',
        ]);
    }

    /**
     * Test admission automatically creates an intake visit if no visit_id provided.
     */
    public function test_auto_creates_inpatient_visit_if_no_visit_id_provided(): void
    {
        $patient = $this->createTestPatient();
        $bed = Bed::where('status', 'available')->first() ?? Bed::create([
            'bed_number' => 'TEST-ADM-BED-AUTO',
            'room_number' => 'Ward 102',
            'category' => 'general',
            'status' => 'available',
            'daily_charge' => 500.00,
        ]);

        $admission = $this->admissionService->createAdmission([
            'patient_id' => $patient->id,
            'bed_id' => $bed->id,
            'attending_doctor_id' => $this->doctorUser->id,
            'admission_reason' => 'Direct emergency trauma admission',
        ], $this->receptionistUser);

        $this->assertNotNull($admission->visit_id);
        $this->assertDatabaseHas('visits', [
            'id' => $admission->visit_id,
            'patient_id' => $patient->id,
        ]);
    }

    /**
     * Test cannot create admission for an unregistered patient.
     */
    public function test_cannot_create_admission_for_unregistered_patient(): void
    {
        $bed = Bed::where('status', 'available')->first();

        $payload = [
            'patient_id' => 999999, // non-existent
            'bed_id' => $bed ? $bed->id : 1,
            'attending_doctor_id' => $this->doctorUser->id,
            'admission_reason' => 'Some test indication',
        ];

        $response = $this->actingAs($this->receptionistUser)
            ->post(route('portal.admissions.store'), $payload);

        $response->assertSessionHasErrors('patient_id');
    }

    /**
     * Test cannot create admission linking visit of a different patient.
     */
    public function test_cannot_link_visit_belonging_to_another_patient(): void
    {
        $patientA = $this->createTestPatient();
        $patientB = $this->createTestPatient();
        $visitOfB = $this->createTestVisit($patientB);
        $bed = Bed::where('status', 'available')->first();

        $this->expectException(InvalidArgumentException::class);

        $this->admissionService->createAdmission([
            'patient_id' => $patientA->id,
            'visit_id' => $visitOfB->id,
            'bed_id' => $bed->id,
            'attending_doctor_id' => $this->doctorUser->id,
            'admission_reason' => 'Test invalid visit link',
        ], $this->receptionistUser);
    }

    /**
     * Test patient cannot have multiple simultaneous active admissions.
     */
    public function test_prevents_multiple_simultaneous_active_admissions_for_same_patient(): void
    {
        $patient = $this->createTestPatient();
        do {
            $bedNum1 = 'MULT-BED-1-' . rand(10000, 99999);
        } while (Bed::where('bed_number', $bedNum1)->exists());

        do {
            $bedNum2 = 'MULT-BED-2-' . rand(10000, 99999);
        } while (Bed::where('bed_number', $bedNum2)->exists());

        $bed1 = Bed::create([
            'bed_number' => $bedNum1,
            'category' => 'general',
            'status' => 'available',
            'daily_charge' => 500.00,
        ]);
        $bed2 = Bed::create([
            'bed_number' => $bedNum2,
            'category' => 'general',
            'status' => 'available',
            'daily_charge' => 500.00,
        ]);

        // First admission succeeds
        $this->admissionService->createAdmission([
            'patient_id' => $patient->id,
            'bed_id' => $bed1->id,
            'attending_doctor_id' => $this->doctorUser->id,
            'admission_reason' => 'First admission',
        ], $this->receptionistUser);

        // Second admission for the same patient must throw an exception
        $this->expectException(InvalidArgumentException::class);

        $this->admissionService->createAdmission([
            'patient_id' => $patient->id,
            'bed_id' => $bed2->id,
            'attending_doctor_id' => $this->doctorUser->id,
            'admission_reason' => 'Attempted second admission while still admitted',
        ], $this->receptionistUser);
    }

    /**
     * Test verification of bed availability before allocation.
     */
    public function test_verifies_bed_availability_before_allocation(): void
    {
        $patient = $this->createTestPatient();
        $cleaningBed = Bed::create([
            'bed_number' => 'CLEAN-BED-' . rand(100, 999),
            'category' => 'general',
            'status' => 'cleaning',
            'daily_charge' => 500.00,
        ]);

        $this->expectException(InvalidArgumentException::class);

        $this->admissionService->createAdmission([
            'patient_id' => $patient->id,
            'bed_id' => $cleaningBed->id,
            'attending_doctor_id' => $this->doctorUser->id,
            'admission_reason' => 'Attempting allocation of cleaning bed',
        ], $this->receptionistUser);
    }

    /**
     * Test supports both General and Department-wise bed allocation.
     */
    public function test_supports_both_general_and_department_wise_bed_allocation(): void
    {
        $gynDept = Department::where('code', 'GYN')->first() ?? Department::create([
            'name' => 'Gynecology & Obstetrics',
            'code' => 'GYN',
            'is_active' => true,
        ]);

        // 1. General Bed (no department_id)
        $patient1 = $this->createTestPatient();
        $generalBed = Bed::create([
            'bed_number' => 'GEN-ALLOC-' . rand(100, 999),
            'category' => 'general',
            'department_id' => null,
            'status' => 'available',
            'daily_charge' => 600.00,
        ]);

        $adm1 = $this->admissionService->createAdmission([
            'patient_id' => $patient1->id,
            'bed_id' => $generalBed->id,
            'attending_doctor_id' => $this->doctorUser->id,
            'admission_reason' => 'General observation',
        ], $this->receptionistUser);

        $this->assertEquals('occupied', $generalBed->fresh()->status);
        $this->assertEquals($generalBed->id, $adm1->currentBed->id);

        // 2. Department-wise Bed (Gynecology)
        $patient2 = $this->createTestPatient();
        $gynBed = Bed::create([
            'bed_number' => 'GYN-ALLOC-' . rand(100, 999),
            'category' => 'department',
            'department_id' => $gynDept->id,
            'status' => 'available',
            'daily_charge' => 1200.00,
        ]);

        $adm2 = $this->admissionService->createAdmission([
            'patient_id' => $patient2->id,
            'department_id' => $gynDept->id,
            'bed_id' => $gynBed->id,
            'attending_doctor_id' => $this->doctorUser->id,
            'admission_reason' => 'Gynecological post-op inpatient stay',
        ], $this->receptionistUser);

        $this->assertEquals('occupied', $gynBed->fresh()->status);
        $this->assertEquals($gynBed->id, $adm2->currentBed->id);
        $this->assertEquals($gynDept->id, $adm2->department_id);
    }

    /**
     * Test concurrency and double bed allocation prevention.
     */
    public function test_concurrency_and_prevents_double_booking(): void
    {
        $patient1 = $this->createTestPatient();
        $patient2 = $this->createTestPatient();

        $bed = Bed::create([
            'bed_number' => 'CONCUR-BED-' . rand(100, 999),
            'category' => 'general',
            'status' => 'available',
            'daily_charge' => 700.00,
        ]);

        // First allocation succeeds
        $this->admissionService->createAdmission([
            'patient_id' => $patient1->id,
            'bed_id' => $bed->id,
            'attending_doctor_id' => $this->doctorUser->id,
            'admission_reason' => 'First patient to claim bed',
        ], $this->receptionistUser);

        // Second allocation attempting the exact same bed immediately throws concurrency error
        $this->expectException(InvalidArgumentException::class);

        $this->admissionService->createAdmission([
            'patient_id' => $patient2->id,
            'bed_id' => $bed->id,
            'attending_doctor_id' => $this->doctorUser->id,
            'admission_reason' => 'Second patient conflicting attempt',
        ], $this->receptionistUser);
    }

    /**
     * Test bed transfer and complete transfer history maintenance.
     */
    public function test_patient_bed_transfer_and_history_maintenance(): void
    {
        $patient = $this->createTestPatient();
        $bedA = Bed::create([
            'bed_number' => 'BED-A-' . rand(100, 999),
            'room_number' => 'Ward Room 1',
            'category' => 'general',
            'status' => 'available',
            'daily_charge' => 500.00,
        ]);
        $bedB = Bed::create([
            'bed_number' => 'BED-B-' . rand(100, 999),
            'room_number' => 'Cabin 2',
            'category' => 'cabin',
            'status' => 'available',
            'daily_charge' => 1500.00,
        ]);

        // 1. Initial admission onto Bed A
        $admission = $this->admissionService->createAdmission([
            'patient_id' => $patient->id,
            'bed_id' => $bedA->id,
            'attending_doctor_id' => $this->doctorUser->id,
            'admission_reason' => 'Post-op recovery',
        ], $this->receptionistUser);

        $this->assertEquals($bedA->id, $admission->currentBed->id);
        $this->assertEquals('occupied', $bedA->fresh()->status);

        // 2. Transfer patient to Bed B
        $newAssignment = $this->admissionService->transferBed(
            $admission,
            $bedB,
            'Patient requested private cabin with family attendance',
            $this->nurseUser
        );

        $admission->refresh();

        // 3. Current bed is now Bed B
        $this->assertEquals($bedB->id, $admission->currentBed->id);
        $this->assertEquals('occupied', $bedB->fresh()->status);

        // 4. Old bed A was released and set to cleaning
        $this->assertEquals('cleaning', $bedA->fresh()->status);

        // 5. Admission maintains both assignments in history
        $this->assertCount(2, $admission->bedAssignments);
        $oldAssignment = $admission->bedAssignments->where('bed_id', $bedA->id)->first();
        $this->assertNotNull($oldAssignment->released_at);
        $this->assertEquals('Patient requested private cabin with family attendance', $oldAssignment->transfer_reason);

        // 6. Bed status history recorded for both beds
        $this->assertDatabaseHas('bed_status_histories', [
            'bed_id' => $bedA->id,
            'from_status' => 'occupied',
            'to_status' => 'cleaning',
        ]);
        $this->assertDatabaseHas('bed_status_histories', [
            'bed_id' => $bedB->id,
            'from_status' => 'available',
            'to_status' => 'occupied',
        ]);
    }

    /**
     * Test admission cancellation with authorization and bed release.
     */
    public function test_admission_cancellation_releases_bed(): void
    {
        $patient = $this->createTestPatient();
        $bed = Bed::create([
            'bed_number' => 'CANCEL-BED-' . rand(100, 999),
            'category' => 'general',
            'status' => 'available',
            'daily_charge' => 500.00,
        ]);

        $admission = $this->admissionService->createAdmission([
            'patient_id' => $patient->id,
            'bed_id' => $bed->id,
            'attending_doctor_id' => $this->doctorUser->id,
            'admission_reason' => 'Admission for elective procedure',
        ], $this->receptionistUser);

        $this->assertEquals('occupied', $bed->fresh()->status);

        // Cancel admission via HTTP endpoint as Doctor
        $response = $this->actingAs($this->doctorUser)
            ->post(route('portal.admissions.cancel', $admission), [
                'cancellation_reason' => 'Patient decided against surgery and left hospital before procedure.',
            ]);

        $response->assertRedirect();

        $admission->refresh();
        $this->assertEquals('cancelled', $admission->status);
        $this->assertEquals($this->doctorUser->id, $admission->cancelled_by);
        $this->assertNotNull($admission->cancelled_at);
        $this->assertStringContainsString('Patient decided against surgery', $admission->cancellation_reason);

        // Occupied bed must be released back to available
        $this->assertEquals('available', $bed->fresh()->status);
    }

    /**
     * Test unauthorized users cannot manage or cancel admissions.
     */
    public function test_unauthorized_users_cannot_manage_admissions(): void
    {
        // 1. Patient cannot access staff admissions portal
        $patientResponse = $this->actingAs($this->patientUser)->get(route('portal.admissions.index'));
        $patientResponse->assertRedirect(route('patient.dashboard'));

        // 2. Guest cannot access admissions
        auth()->logout();
        $guestResponse = $this->get(route('portal.admissions.index'));
        $guestResponse->assertRedirect(route('portal.login'));

        // 3. Nurse cannot cancel admission (policy allows admin and doctor)
        $patient = $this->createTestPatient();
        $bed = Bed::create([
            'bed_number' => 'NURSE-C-BED-' . rand(100, 999),
            'status' => 'available',
        ]);
        $admission = $this->admissionService->createAdmission([
            'patient_id' => $patient->id,
            'bed_id' => $bed->id,
            'attending_doctor_id' => $this->doctorUser->id,
            'admission_reason' => 'Testing nurse authorization barrier',
        ], $this->adminUser);

        $nurseCancel = $this->actingAs($this->nurseUser)
            ->post(route('portal.admissions.cancel', $admission), [
                'cancellation_reason' => 'Nurse unauthorized attempt',
            ]);

        $nurseCancel->assertForbidden();
    }
}
