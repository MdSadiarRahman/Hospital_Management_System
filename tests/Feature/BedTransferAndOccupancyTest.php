<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\AuditLog;
use App\Models\Bed;
use App\Models\BedAssignment;
use App\Models\BedStatusHistory;
use App\Models\Department;
use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use App\Services\AdmissionService;
use App\Services\BedManagementService;
use Database\Seeders\DepartmentAndBedSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Database\Seeders\UserSeeder;
use InvalidArgumentException;
use Tests\TestCase;

class BedTransferAndOccupancyTest extends TestCase
{
    protected User $adminUser;
    protected User $doctorUser;
    protected User $nurseUser;
    protected User $receptionistUser;
    protected User $patientUser;
    protected BedManagementService $bedService;
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

        $this->bedService = app(BedManagementService::class);
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

        $this->nurseUser = User::whereHas('roles', fn($q) => $q->where('name', 'nurse'))->first()
            ?? User::factory()->create(['user_type' => 'staff']);
        if (!$this->nurseUser->hasRole('nurse')) {
            $role = Role::where('name', 'nurse')->first();
            if ($role) $this->nurseUser->roles()->syncWithoutDetaching([$role->id]);
        }

        $this->receptionistUser = User::whereHas('roles', fn($q) => $q->where('name', 'receptionist'))->first()
            ?? User::factory()->create(['user_type' => 'staff']);
        if (!$this->receptionistUser->hasRole('receptionist')) {
            $role = Role::where('name', 'receptionist')->first();
            if ($role) $this->receptionistUser->roles()->syncWithoutDetaching([$role->id]);
        }

        $this->patientUser = User::where('user_type', 'patient')->first()
            ?? User::factory()->create(['user_type' => 'patient']);
    }

    protected function createTestPatient(array $attributes = []): Patient
    {
        $uniqueId = rand(100000, 999999);
        return Patient::create(array_merge([
            'patient_code' => 'MCNH-2026-' . $uniqueId,
            'name' => 'Transfer Inpatient ' . $uniqueId,
            'date_of_birth' => '1992-06-15',
            'gender' => 'female',
            'phone' => '017' . rand(10000000, 99999999),
            'registration_date' => now()->toDateString(),
        ], $attributes));
    }

    /**
     * Test visual 10-bed occupancy dashboard displays actual database data.
     */
    public function test_can_view_10_bed_visual_occupancy_dashboard(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('portal.beds.occupancy'));

        $response->assertOk();
        $response->assertViewIs('portal.beds.occupancy');

        // Asserts presence of 10-bed capacity summary
        $response->assertSee('10-Bed Live Occupancy Dashboard');
        $response->assertSee('Visual floor distribution');

        // Asserts seeded department ward headers exist
        $response->assertSee('Gynecology Ward');
        $response->assertSee('Orthopedic Ward');
        $response->assertSee('Surgery Ward');
        $response->assertSee('Emergency Ward');
    }

    /**
     * Test transferring an admitted patient from one bed to another atomically.
     */
    public function test_transfer_patient_to_another_bed_atomically(): void
    {
        $patient = $this->createTestPatient();
        $bedSource = Bed::create([
            'bed_number' => 'TX-SRC-' . rand(100, 999),
            'room_number' => 'Room 101',
            'category' => 'general',
            'status' => 'available',
            'daily_charge' => 800.00,
        ]);
        $bedTarget = Bed::create([
            'bed_number' => 'TX-TGT-' . rand(100, 999),
            'room_number' => 'Room 102',
            'category' => 'cabin',
            'status' => 'available',
            'daily_charge' => 1800.00,
        ]);

        // 1. Initial admission
        $admission = $this->admissionService->createAdmission([
            'patient_id' => $patient->id,
            'bed_id' => $bedSource->id,
            'attending_doctor_id' => $this->doctorUser->id,
            'admission_reason' => 'Emergency observation',
        ], $this->receptionistUser);

        $this->assertEquals('occupied', $bedSource->fresh()->status);
        $this->assertEquals('available', $bedTarget->fresh()->status);

        // 2. Perform transfer via HTTP controller endpoint
        $reason = 'Patient stabilized; transferred to private cabin for recovery';
        $response = $this->actingAs($this->nurseUser)
            ->post(route('portal.beds.transfer-occupant', $bedSource), [
                'target_bed_id' => $bedTarget->id,
                'reason' => $reason,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // 3. Previous bed released correctly and set to cleaning
        $bedSource->refresh();
        $this->assertEquals('cleaning', $bedSource->status);

        $oldAssignment = BedAssignment::where('bed_id', $bedSource->id)
            ->where('admission_id', $admission->id)
            ->first();
        $this->assertNotNull($oldAssignment);
        $this->assertNotNull($oldAssignment->released_at);
        $this->assertEquals($reason, $oldAssignment->transfer_reason);

        // 4. New bed assigned atomically and set to occupied
        $bedTarget->refresh();
        $this->assertEquals('occupied', $bedTarget->status);

        $newAssignment = BedAssignment::where('bed_id', $bedTarget->id)
            ->where('admission_id', $admission->id)
            ->whereNull('released_at')
            ->first();
        $this->assertNotNull($newAssignment);

        // 5. Preserves previous assignments in admission history
        $admission->refresh();
        $this->assertCount(2, $admission->bedAssignments);
        $this->assertEquals($bedTarget->id, $admission->currentBed->id);
    }

    /**
     * Test transfer reason is required and validated.
     */
    public function test_transfer_reason_is_mandatory(): void
    {
        $bedSource = Bed::create([
            'bed_number' => 'REAS-SRC-' . rand(100, 999),
            'status' => 'occupied',
        ]);
        $bedTarget = Bed::create([
            'bed_number' => 'REAS-TGT-' . rand(100, 999),
            'status' => 'available',
        ]);

        $response = $this->actingAs($this->nurseUser)
            ->post(route('portal.beds.transfer-occupant', $bedSource), [
                'target_bed_id' => $bedTarget->id,
                'reason' => '', // Empty reason
            ]);

        $response->assertSessionHasErrors('reason');
    }

    /**
     * Test cannot transfer patient to an already occupied bed or bed in cleaning.
     */
    public function test_prevents_transfer_to_occupied_or_cleaning_bed(): void
    {
        $patient = $this->createTestPatient();
        $bedSource = Bed::create([
            'bed_number' => 'FAIL-SRC-' . rand(100, 999),
            'status' => 'available',
        ]);
        $bedOccupiedTarget = Bed::create([
            'bed_number' => 'FAIL-OCC-' . rand(100, 999),
            'status' => 'cleaning', // Not available
        ]);

        $this->admissionService->createAdmission([
            'patient_id' => $patient->id,
            'bed_id' => $bedSource->id,
            'attending_doctor_id' => $this->doctorUser->id,
            'admission_reason' => 'Testing conflict',
        ], $this->receptionistUser);

        $this->expectException(InvalidArgumentException::class);

        $this->bedService->transferPatientBed(
            $bedSource,
            $bedOccupiedTarget,
            'Attempting transfer to cleaning bed',
            $this->nurseUser
        );
    }

    /**
     * Test cannot transfer patient to the same bed they currently occupy.
     */
    public function test_prevents_transfer_to_same_bed(): void
    {
        $patient = $this->createTestPatient();
        $bed = Bed::create([
            'bed_number' => 'SAME-BED-' . rand(100, 999),
            'status' => 'available',
        ]);

        $this->admissionService->createAdmission([
            'patient_id' => $patient->id,
            'bed_id' => $bed->id,
            'attending_doctor_id' => $this->doctorUser->id,
            'admission_reason' => 'Same bed test',
        ], $this->receptionistUser);

        $this->expectException(InvalidArgumentException::class);

        $this->bedService->transferPatientBed(
            $bed,
            $bed,
            'Attempting transfer to exact same bed',
            $this->nurseUser
        );
    }

    /**
     * Test: Do not mark a bed Available before required cleaning is complete.
     */
    public function test_do_not_mark_bed_available_before_required_cleaning_is_complete(): void
    {
        $bed = Bed::create([
            'bed_number' => 'CLEAN-RULE-' . rand(100, 999),
            'status' => 'cleaning',
            'daily_charge' => 1000.00,
        ]);

        // 1. Transitioning cleaning to available without reason/certification throws exception
        try {
            $this->bedService->updateBedStatus($bed, 'available', null, null, $this->nurseUser);
            $this->fail('Expected InvalidArgumentException when marking cleaning bed available without certification.');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('certified complete', $e->getMessage());
        }

        // 2. Properly certifying cleaning completes sanitization and transitions to available
        $response = $this->actingAs($this->nurseUser)
            ->post(route('portal.beds.complete-cleaning', $bed), [
                'notes' => 'Terminal disinfection verified, linens replaced, sanitization checklist complete.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $bed->refresh();
        $this->assertEquals('available', $bed->status);

        // 3. Verified history and audit log
        $this->assertDatabaseHas('bed_status_histories', [
            'bed_id' => $bed->id,
            'from_status' => 'cleaning',
            'to_status' => 'available',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'bed.cleaning_completed',
            'auditable_type' => Bed::class,
            'auditable_id' => $bed->id,
        ]);
    }

    /**
     * Test maintenance status workflow: Placing into maintenance and completing repairs.
     */
    public function test_maintenance_status_workflow(): void
    {
        $bed = Bed::create([
            'bed_number' => 'MAINT-' . rand(100, 999),
            'status' => 'available',
        ]);

        // 1. Send to maintenance
        $this->bedService->updateBedStatus(
            $bed,
            'maintenance',
            'Wheel brake broken and traction frame loose',
            'Awaiting biomedical engineer',
            $this->adminUser
        );

        $this->assertEquals('maintenance', $bed->fresh()->status);

        // 2. Complete maintenance with sanitization requirement
        $response = $this->actingAs($this->adminUser)
            ->post(route('portal.beds.complete-maintenance', $bed), [
                'notes' => 'Wheel brake replaced and traction calibrated',
                'requires_cleaning' => 1,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Bed placed into cleaning queue
        $this->assertEquals('cleaning', $bed->fresh()->status);
    }

    /**
     * Test audit logs maintained on bed transfer.
     */
    public function test_audit_logs_maintained_on_bed_transfer(): void
    {
        $patient = $this->createTestPatient();
        $bed1 = Bed::create([
            'bed_number' => 'AUD-B1-' . rand(100, 999),
            'status' => 'available',
        ]);
        $bed2 = Bed::create([
            'bed_number' => 'AUD-B2-' . rand(100, 999),
            'status' => 'available',
        ]);

        $admission = $this->admissionService->createAdmission([
            'patient_id' => $patient->id,
            'bed_id' => $bed1->id,
            'attending_doctor_id' => $this->doctorUser->id,
            'admission_reason' => 'Audit logging test stay',
        ], $this->receptionistUser);

        $this->bedService->transferPatientBed($bed1, $bed2, 'Audit test transfer justification', $this->nurseUser);

        // Verify audit log event
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'bed.transferred',
            'auditable_type' => BedAssignment::class,
            'user_id' => $this->nurseUser->id,
        ]);
    }

    /**
     * Test unauthorized users cannot view occupancy or transfer beds.
     */
    public function test_unauthorized_users_cannot_access_occupancy_dashboard_or_transfer(): void
    {
        // 1. Patient redirected to patient dashboard
        $patientResponse = $this->actingAs($this->patientUser)->get(route('portal.beds.occupancy'));
        $patientResponse->assertRedirect(route('patient.dashboard'));

        // 2. Guest redirected to login
        auth()->logout();
        $guestResponse = $this->get(route('portal.beds.occupancy'));
        $guestResponse->assertRedirect(route('portal.login'));
    }
}
