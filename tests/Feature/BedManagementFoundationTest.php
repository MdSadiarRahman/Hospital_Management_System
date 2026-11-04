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
use App\Services\BedManagementService;
use Database\Seeders\DepartmentAndBedSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class BedManagementFoundationTest extends TestCase
{
    protected User $adminUser;
    protected User $doctorUser;
    protected User $nurseUser;
    protected User $receptionistUser;
    protected User $patientUser;
    protected BedManagementService $bedService;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed essential roles, settings, departments and 10 beds if not present
        if (Department::count() === 0 || Bed::count() === 0) {
            $this->seed(RolePermissionSeeder::class);
            $this->seed(DepartmentAndBedSeeder::class);
            $this->seed(SystemSettingsSeeder::class);
            $this->seed(UserSeeder::class);
        }

        $this->bedService = app(BedManagementService::class);

        // Fetch or create testing users
        $this->adminUser = User::whereHas('roles', fn($q) => $q->where('name', 'administrator'))->first()
            ?? User::factory()->create(['user_type' => 'staff']);
        if (!$this->adminUser->hasRole('administrator')) {
            $adminRole = Role::where('name', 'administrator')->first();
            if ($adminRole) {
                $this->adminUser->roles()->syncWithoutDetaching([$adminRole->id]);
            }
        }

        $this->nurseUser = User::whereHas('roles', fn($q) => $q->where('name', 'nurse'))->first()
            ?? User::factory()->create(['user_type' => 'staff']);
        if (!$this->nurseUser->hasRole('nurse')) {
            $nurseRole = Role::where('name', 'nurse')->first();
            if ($nurseRole) {
                $this->nurseUser->roles()->syncWithoutDetaching([$nurseRole->id]);
            }
        }

        $this->doctorUser = User::whereHas('roles', fn($q) => $q->where('name', 'doctor'))->first()
            ?? User::factory()->create(['user_type' => 'staff']);

        $this->receptionistUser = User::whereHas('roles', fn($q) => $q->where('name', 'receptionist'))->first()
            ?? User::factory()->create(['user_type' => 'staff']);

        $this->patientUser = User::where('user_type', 'patient')->first()
            ?? User::factory()->create(['user_type' => 'patient']);
    }

    /**
     * Test initial 10-bed capacity and support for General + Department-wise Beds.
     */
    public function test_initial_capacity_and_support_for_general_and_department_wise_beds(): void
    {
        $metrics = $this->bedService->getBedMetrics();

        // Initial capacity is at least 10 beds
        $this->assertGreaterThanOrEqual(10, $metrics['total_capacity']);

        // Supports General Beds
        $generalBeds = Bed::general()->get();
        $this->assertTrue($generalBeds->isNotEmpty());

        // Supports Department-wise Beds: Gynecology, Orthopedic, Surgery
        $gynDept = Department::where('code', 'GYN')->first();
        $orthoDept = Department::where('code', 'ORTHO')->first();
        $surgDept = Department::where('code', 'SURG')->first();

        $this->assertNotNull($gynDept);
        $this->assertNotNull($orthoDept);
        $this->assertNotNull($surgDept);

        // Bed distribution is dynamically configurable without hardcoded counts
        $this->assertTrue(Bed::where('department_id', $gynDept->id)->exists());
        $this->assertTrue(Bed::where('department_id', $orthoDept->id)->exists());
        $this->assertTrue(Bed::where('department_id', $surgDept->id)->exists());
    }

    /**
     * Test full features: Bed Number, Room Number, Category, Department, Status, Description.
     */
    public function test_bed_features_attributes_and_specifications(): void
    {
        $gynDept = Department::where('code', 'GYN')->first();

        // Create a test bed with all requested fields
        $bedNumber = 'TEST-GYN-' . rand(100, 999);
        $bed = $this->bedService->createBed([
            'bed_number' => $bedNumber,
            'room_number' => 'Cabin 204',
            'category' => 'cabin',
            'department_id' => $gynDept->id,
            'daily_charge' => 2500.00,
            'status' => 'available',
            'description' => 'Deluxe air-conditioned single cabin with central oxygen line.',
        ], $this->adminUser);

        $this->assertEquals($bedNumber, $bed->bed_number);
        $this->assertEquals('Cabin 204', $bed->room_number);
        $this->assertEquals('cabin', $bed->category);
        $this->assertEquals($gynDept->id, $bed->department_id);
        $this->assertEquals('available', $bed->status);
        $this->assertEquals('Deluxe air-conditioned single cabin with central oxygen line.', $bed->description);
        $this->assertEquals('Private Cabin', $bed->category_label);
        $this->assertFalse($bed->is_general);

        // General bed without department
        $genBedNumber = 'TEST-GEN-' . rand(100, 999);
        $genBed = $this->bedService->createBed([
            'bed_number' => $genBedNumber,
            'room_number' => 'Ward A',
            'category' => 'general',
            'department_id' => null,
            'daily_charge' => 800.00,
            'status' => 'available',
            'description' => 'General ward multi-bed unit.',
        ], $this->adminUser);

        $this->assertTrue($genBed->is_general);
        $this->assertNull($genBed->department_id);
    }

    /**
     * Test all 5 required statuses: Available, Occupied, Reserved, Cleaning, Maintenance.
     */
    public function test_all_five_bed_statuses_supported_with_proper_scopes(): void
    {
        $testBed = Bed::create([
            'bed_number' => 'STATUS-TEST-' . rand(100, 999),
            'room_number' => 'Room 101',
            'category' => 'general',
            'status' => 'available',
            'daily_charge' => 1000.00,
        ]);

        $statuses = ['available', 'occupied', 'reserved', 'cleaning', 'maintenance'];

        foreach ($statuses as $status) {
            $testBed->update(['status' => $status]);
            $this->assertEquals($status, $testBed->fresh()->status);
            $this->assertNotEmpty($testBed->status_badge_class);
        }

        // Test model scopes
        $this->assertTrue(Bed::where('id', $testBed->id)->maintenance()->exists());
    }

    /**
     * Test Administrator can configure beds (create, update, delete via HTTP endpoints).
     */
    public function test_administrator_can_configure_beds(): void
    {
        $orthoDept = Department::where('code', 'ORTHO')->first();
        $bedNumber = 'CONF-ORTHO-' . rand(100, 999);

        // 1. Create Bed
        $createResponse = $this->actingAs($this->adminUser)->post(route('portal.beds.store'), [
            'bed_number' => $bedNumber,
            'room_number' => 'Room 305',
            'category' => 'department',
            'department_id' => $orthoDept->id,
            'daily_charge' => 1500.00,
            'status' => 'available',
            'description' => 'Orthopedic traction bed.',
        ]);

        $createResponse->assertRedirect(route('portal.beds.index'));
        $createResponse->assertSessionHas('success');

        $createdBed = Bed::where('bed_number', $bedNumber)->first();
        $this->assertNotNull($createdBed);
        $this->assertEquals('Room 305', $createdBed->room_number);

        // 2. Update Bed
        $updateResponse = $this->actingAs($this->adminUser)->put(route('portal.beds.update', $createdBed), [
            'bed_number' => $bedNumber,
            'room_number' => 'Room 305-B',
            'category' => 'department',
            'department_id' => $orthoDept->id,
            'daily_charge' => 1800.00,
            'status' => 'cleaning',
            'status_reason' => 'Quarterly overhaul',
            'description' => 'Updated orthopedic traction bed.',
        ]);

        $updateResponse->assertRedirect(route('portal.beds.show', $createdBed));
        $this->assertEquals('Room 305-B', $createdBed->fresh()->room_number);
        $this->assertEquals('cleaning', $createdBed->fresh()->status);

        // 3. Delete Bed
        $deleteResponse = $this->actingAs($this->adminUser)->delete(route('portal.beds.destroy', $createdBed));
        $deleteResponse->assertRedirect(route('portal.beds.index'));
        $deleteResponse->assertSessionHas('success');

        $this->assertNull(Bed::find($createdBed->id));
    }

    /**
     * Test maintaining bed status history on every transition.
     */
    public function test_maintains_bed_status_history(): void
    {
        $bed = Bed::create([
            'bed_number' => 'HIST-BED-' . rand(100, 999),
            'room_number' => 'Room 201',
            'category' => 'general',
            'status' => 'available',
            'daily_charge' => 1000.00,
        ]);

        // Transition 1: available -> cleaning
        $this->bedService->updateBedStatus(
            $bed,
            'cleaning',
            'Patient discharged; sanitizing mattress and frame',
            'Assigned to cleaning duty roster',
            $this->nurseUser
        );

        $this->assertEquals('cleaning', $bed->fresh()->status);

        // Transition 2: cleaning -> available
        $this->bedService->updateBedStatus(
            $bed,
            'available',
            'Terminal disinfection complete; bed made',
            'Verified ready for admissions',
            $this->nurseUser
        );

        $this->assertEquals('available', $bed->fresh()->status);

        // Verify status histories table
        $histories = BedStatusHistory::where('bed_id', $bed->id)->latest('created_at')->get();
        $this->assertCount(2, $histories);
        $this->assertEquals('available', $histories->first()->to_status);
        $this->assertEquals('cleaning', $histories->first()->from_status);
        $this->assertEquals($this->nurseUser->id, $histories->first()->changed_by);
    }

    /**
     * Test concurrency & double booking prevention (Prevent duplicate active assignments).
     */
    public function test_concurrency_and_double_booking_prevention_on_active_assignments(): void
    {
        $bed = Bed::create([
            'bed_number' => 'CONC-BED-' . rand(100, 999),
            'room_number' => 'Room 108',
            'category' => 'general',
            'status' => 'available',
            'daily_charge' => 1200.00,
        ]);

        // Prepare test patient & admission
        $patient = Patient::firstOrCreate(
            ['patient_code' => 'MCNH-2026-9191'],
            [
                'name' => 'Inpatient Concurrency Test Patient',
                'date_of_birth' => '1992-04-12',
                'gender' => 'female',
                'phone' => '01711888222',
                'registration_date' => now()->toDateString(),
            ]
        );

        $visit = Visit::create([
            'patient_id' => $patient->id,
            'visit_number' => 'VIS-CONC-' . rand(1000, 9999),
            'visit_date' => now(),
            'visit_type' => 'emergency',
            'triage_level' => 'priority',
            'status' => 'admitted',
        ]);

        $admission1 = Admission::create([
            'visit_id' => $visit->id,
            'patient_id' => $patient->id,
            'admission_number' => 'ADM-CONC-1-' . rand(1000, 9999),
            'admission_date' => now(),
            'attending_doctor_id' => $this->doctorUser->id,
            'admission_reason' => 'Acute abdominal observation',
            'status' => 'admitted',
        ]);

        $admission2 = Admission::create([
            'visit_id' => $visit->id,
            'patient_id' => $patient->id,
            'admission_number' => 'ADM-CONC-2-' . rand(1000, 9999),
            'admission_date' => now(),
            'attending_doctor_id' => $this->doctorUser->id,
            'admission_reason' => 'Second patient trying to take same bed',
            'status' => 'admitted',
        ]);

        // 1. First assignment succeeds
        $assignment1 = $this->bedService->assignPatientToBed($bed, $admission1, $this->adminUser, 'Admitted from Emergency');
        $this->assertNotNull($assignment1);
        $this->assertEquals('occupied', $bed->fresh()->status);
        $this->assertEquals(1, $bed->assignments()->whereNull('released_at')->count());

        // 2. Second concurrent assignment to same bed MUST fail with InvalidArgumentException
        $this->expectException(InvalidArgumentException::class);
        $this->bedService->assignPatientToBed($bed, $admission2, $this->adminUser, 'Conflicting assignment');
    }

    /**
     * Test cannot delete an occupied bed or bed with active patient.
     */
    public function test_cannot_delete_occupied_bed(): void
    {
        $bed = Bed::create([
            'bed_number' => 'OCC-DEL-' . rand(100, 999),
            'room_number' => 'Room 105',
            'category' => 'general',
            'status' => 'occupied',
            'daily_charge' => 1200.00,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->bedService->deleteBed($bed, $this->adminUser);
    }

    /**
     * Test authorized staff can view live bed station with metrics, search and filters.
     */
    public function test_authorized_staff_can_view_live_bed_station_with_metrics(): void
    {
        $bed = Bed::first();

        $response = $this->actingAs($this->nurseUser)->get(route('portal.beds.index'));

        $response->assertOk();
        $response->assertViewIs('portal.beds.index');
        $response->assertSee('Inpatient Bed Management Station');
        $response->assertSee('Total Capacity');
        $response->assertSee('Available');
        $response->assertSee('Occupied');
        $response->assertSee($bed->bed_number);

        // Test search filter
        $searchResponse = $this->actingAs($this->nurseUser)->get(route('portal.beds.index', ['search' => $bed->bed_number]));
        $searchResponse->assertOk();
        $searchResponse->assertSee($bed->bed_number);
    }

    /**
     * Test unauthorized users (such as patients or non-staff) cannot manage beds.
     */
    public function test_unauthorized_users_cannot_manage_beds(): void
    {
        // 1. Patient cannot access staff bed portal (redirected to patient dashboard)
        $patientResponse = $this->actingAs($this->patientUser)->get(route('portal.beds.index'));
        $patientResponse->assertRedirect(route('patient.dashboard'));

        // 2. Guest cannot access beds (redirected to portal login)
        auth()->logout();
        $guestResponse = $this->get(route('portal.beds.index'));
        $guestResponse->assertRedirect(route('portal.login'));

        // 3. Nurse/Doctor cannot delete beds (Forbidden 403 by BedPolicy)
        $bed = Bed::first();
        $nurseResponse = $this->actingAs($this->nurseUser)->delete(route('portal.beds.destroy', $bed));
        $nurseResponse->assertForbidden();
    }

    /**
     * Test support for future expansion beyond initial 10 beds.
     */
    public function test_supports_future_expansion(): void
    {
        $initialCapacity = Bed::count();

        // Add 5 more beds dynamically
        for ($i = 1; $i <= 5; $i++) {
            Bed::create([
                'bed_number' => 'EXP-BED-' . $i . '-' . rand(100, 999),
                'room_number' => 'Extension Ward ' . $i,
                'category' => 'general',
                'status' => 'available',
                'daily_charge' => 1000.00,
            ]);
        }

        $metrics = $this->bedService->getBedMetrics();
        $this->assertEquals($initialCapacity + 5, $metrics['total_capacity']);
    }
}
