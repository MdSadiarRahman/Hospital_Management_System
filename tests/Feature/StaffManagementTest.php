<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Role;
use App\Models\ShiftSchedule;
use App\Models\StaffProfile;
use App\Models\User;
use Database\Seeders\DepartmentAndBedSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Database\Seeders\UserSeeder;
use Tests\TestCase;

class StaffManagementTest extends TestCase
{
    protected User $adminUser;
    protected User $doctorUser;
    protected User $colleagueDoctorUser;
    protected User $nurseUser;
    protected User $patientUser;

    protected function setUp(): void
    {
        parent::setUp();

        if (Role::count() === 0 || Department::count() === 0) {
            $this->seed(RolePermissionSeeder::class);
            $this->seed(DepartmentAndBedSeeder::class);
            $this->seed(SystemSettingsSeeder::class);
            $this->seed(UserSeeder::class);
        }

        $adminRole = Role::where('name', 'administrator')->firstOrFail();
        $doctorRole = Role::where('name', 'doctor')->firstOrFail();
        $nurseRole = Role::where('name', 'nurse')->firstOrFail();
        $patientRole = Role::where('name', 'patient')->firstOrFail();

        // Admin User
        $this->adminUser = User::updateOrCreate(
            ['email' => 'admin.test@meghnaclinic.com'],
            [
                'name' => 'Admin Controller Test',
                'phone' => '01718888001',
                'password' => bcrypt('AdminPass123!'),
                'user_type' => 'staff',
                'is_active' => true,
            ]
        );
        $this->adminUser->roles()->syncWithoutDetaching([$adminRole->id]);

        // Doctor User 1
        $this->doctorUser = User::firstOrCreate(
            ['email' => 'doctor1.test@meghnaclinic.com'],
            [
                'name' => 'Dr. Test Surgeon One',
                'phone' => '01718888002',
                'password' => bcrypt('DoctorPass123!'),
                'user_type' => 'staff',
                'is_active' => true,
            ]
        );
        $this->doctorUser->roles()->syncWithoutDetaching([$doctorRole->id]);

        $gyn = Department::where('code', 'GYN')->first();
        $this->doctorProfile = StaffProfile::updateOrCreate(
            ['user_id' => $this->doctorUser->id],
            [
                'employee_id' => 'MCNH-DOC-TEST-01',
                'department_id' => $gyn?->id,
                'designation' => 'Associate Surgeon',
                'specialization' => 'Gynecological Surgery',
                'license_number' => 'BMDC-TEST-001',
                'shift' => 'morning',
                'salary' => 70000.00,
                'nid_number' => '1985271890999',
                'status' => 'active',
            ]
        );

        // Doctor User 2 (Colleague)
        $this->colleagueDoctorUser = User::firstOrCreate(
            ['email' => 'doctor2.test@meghnaclinic.com'],
            [
                'name' => 'Dr. Test Surgeon Two',
                'phone' => '01718888003',
                'password' => bcrypt('DoctorPass123!'),
                'user_type' => 'staff',
                'is_active' => true,
            ]
        );
        $this->colleagueDoctorUser->roles()->syncWithoutDetaching([$doctorRole->id]);

        // Nurse User
        $this->nurseUser = User::firstOrCreate(
            ['email' => 'nurse1.test@meghnaclinic.com'],
            [
                'name' => 'Sister Test Nurse',
                'phone' => '01718888004',
                'password' => bcrypt('NursePass123!'),
                'user_type' => 'staff',
                'is_active' => true,
            ]
        );
        $this->nurseUser->roles()->syncWithoutDetaching([$nurseRole->id]);

        // Patient User
        $this->patientUser = User::firstOrCreate(
            ['email' => 'patient1.test@meghnaclinic.com'],
            [
                'name' => 'Test Portal Patient',
                'phone' => '01718888005',
                'password' => bcrypt('PatientPass123!'),
                'user_type' => 'patient',
                'is_active' => true,
            ]
        );
        $this->patientUser->roles()->syncWithoutDetaching([$patientRole->id]);
    }

    /**
     * Test admin can view staff directory with metrics and search filters.
     */
    public function test_admin_can_view_staff_directory_with_metrics_and_filters(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('portal.staff.index'));

        $response->assertOk();
        $response->assertViewIs('portal.staff.index');
        $response->assertSee('Hospital Staff Management');
        $response->assertSee('Total Staff');
        $response->assertSee('Active Doctors');
        $response->assertSee('Nursing Team');

        // Test search filter
        $searchResponse = $this->actingAs($this->adminUser)->get(route('portal.staff.index', ['search' => 'Surgeon One']));
        $searchResponse->assertOk();
        $searchResponse->assertSee('Dr. Test Surgeon One');

        // Test role filter
        $roleResponse = $this->actingAs($this->adminUser)->get(route('portal.staff.index', ['role' => 'doctor']));
        $roleResponse->assertOk();
        $roleResponse->assertSee('Dr. Test Surgeon One');
    }

    /**
     * Test admin can view staff registration form.
     */
    public function test_admin_can_view_register_staff_form(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('portal.staff.create'));

        $response->assertOk();
        $response->assertViewIs('portal.staff.create');
        $response->assertSee('Register New Staff Member');
        $response->assertSee('Hospital Roles');
        $response->assertSee('Protected Employment Information');
    }

    /**
     * Test admin can register a new staff member with multi-department allocations.
     */
    public function test_admin_can_register_new_staff_member_with_multiple_departments(): void
    {
        $gyn = Department::where('code', 'GYN')->firstOrFail();
        $surg = Department::where('code', 'SURG')->firstOrFail();
        $nurseRole = Role::where('name', 'nurse')->firstOrFail();

        // Clean any pre-existing
        User::where('email', 'new.sister@meghnaclinic.com')->delete();

        $formData = [
            'name' => 'Sister Amina Begum',
            'email' => 'new.sister@meghnaclinic.com',
            'phone' => '01719998811',
            'password' => 'SisterPass123!',
            'role_ids' => [$nurseRole->id],
            'department_id' => $gyn->id,
            'secondary_department_ids' => [$surg->id],
            'designation' => 'Staff Nurse (Gynecology & Surgery)',
            'specialization' => 'Post-Operative Recovery',
            'license_number' => 'BNMC-RN-TEST-99',
            'shift' => 'morning',
            'status' => 'active',
            'employment_type' => 'full_time',
            'joining_date' => '2026-01-15',
            'qualification' => 'Diploma in Nursing Science',
            'salary' => 28000.00,
            'nid_number' => '1995271890987',
            'emergency_contact_name' => 'Abdul Karim (Father)',
            'emergency_contact_phone' => '01711223344',
            'address' => 'Balubari, Dinajpur',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('portal.staff.store'), $formData);

        $createdUser = User::where('email', 'new.sister@meghnaclinic.com')->first();
        $this->assertNotNull($createdUser);
        $this->assertTrue($createdUser->hasRole('nurse'));

        $profile = $createdUser->staffProfile;
        $this->assertNotNull($profile);
        $this->assertEquals($gyn->id, $profile->department_id);
        $this->assertEquals('BNMC-RN-TEST-99', $profile->license_number);
        $this->assertEquals('28000.00', $profile->salary);

        // Assert multi-department allocation pivot
        $this->assertTrue($profile->departments->contains($gyn->id));
        $this->assertTrue($profile->departments->contains($surg->id));

        $response->assertRedirect(route('portal.staff.show', $profile));
        $response->assertSessionHas('status');

        $this->assertTrue(AuditLog::where('event', 'staff.registered')
            ->where('auditable_id', $profile->id)
            ->exists());
    }

    /**
     * Test staff registration validation rules for uniqueness and required fields.
     */
    public function test_staff_registration_validation_rules_enforced(): void
    {
        $duplicateData = [
            'name' => '', // Required
            'email' => $this->adminUser->email, // Duplicate
            'phone' => $this->adminUser->phone, // Duplicate
            'role_ids' => [], // Required
            'designation' => '', // Required
            'shift' => 'invalid_shift',
            'status' => 'invalid_status',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('portal.staff.store'), $duplicateData);
        $response->assertSessionHasErrors(['name', 'email', 'phone', 'role_ids', 'designation', 'shift', 'status']);
    }

    /**
     * Test sensitive employment data (salary, NID) is protected from unauthorized staff.
     */
    public function test_sensitive_employment_information_protection(): void
    {
        // 1. Admin views doctor profile: sees salary and NID
        $adminView = $this->actingAs($this->adminUser)->get(route('portal.staff.show', $this->doctorProfile));
        $adminView->assertOk();
        $adminView->assertSee('70,000.00');
        $adminView->assertSee('1985271890999');

        // 2. Doctor views own profile: can view own salary and NID
        $ownerView = $this->actingAs($this->doctorUser)->get(route('portal.staff.show', $this->doctorProfile));
        $ownerView->assertOk();
        $ownerView->assertSee('70,000.00');
        $ownerView->assertSee('1985271890999');

        // 3. Colleague doctor views doctor profile: sensitive salary & NID are masked!
        $colleagueView = $this->actingAs($this->colleagueDoctorUser)->get(route('portal.staff.show', $this->doctorProfile));
        $colleagueView->assertOk();
        $colleagueView->assertDontSee('70,000.00');
        $colleagueView->assertDontSee('1985271890999');
        $colleagueView->assertSee('Confidential Information Masked');
    }

    /**
     * Test admin can update staff profile and reallocate departments.
     */
    public function test_admin_can_update_staff_profile(): void
    {
        $ortho = Department::where('code', 'ORTHO')->firstOrFail();
        $nurseRole = Role::where('name', 'nurse')->firstOrFail();

        $updateData = [
            'name' => 'Dr. Test Surgeon One (Promoted)',
            'email' => $this->doctorUser->email,
            'phone' => $this->doctorUser->phone,
            'designation' => 'Senior Surgeon & Head of Clinical Services',
            'department_id' => $ortho->id,
            'secondary_department_ids' => [],
            'shift' => 'rotational',
            'status' => 'active',
            'employment_type' => 'full_time',
            'salary' => 85000.00,
            'license_number' => $this->doctorProfile->license_number,
        ];

        $response = $this->actingAs($this->adminUser)->put(route('portal.staff.update', $this->doctorProfile), $updateData);

        $response->assertRedirect(route('portal.staff.show', $this->doctorProfile));
        $this->doctorProfile->refresh();
        $this->assertEquals('Senior Surgeon & Head of Clinical Services', $this->doctorProfile->designation);
        $this->assertEquals('85000.00', $this->doctorProfile->salary);
        $this->assertEquals($ortho->id, $this->doctorProfile->department_id);

        $this->assertTrue(AuditLog::where('event', 'staff.updated')
            ->where('auditable_id', $this->doctorProfile->id)
            ->exists());
    }

    /**
     * Test admin can toggle staff active/inactive status.
     */
    public function test_admin_can_toggle_staff_status(): void
    {
        $this->assertEquals('active', $this->doctorProfile->status);
        $this->assertTrue((bool)$this->doctorUser->is_active);

        // Toggle to inactive
        $response1 = $this->actingAs($this->adminUser)->post(route('portal.staff.toggle-status', $this->doctorProfile));
        $response1->assertRedirect();
        $this->doctorProfile->refresh();
        $this->doctorUser->refresh();
        $this->assertEquals('inactive', $this->doctorProfile->status);
        $this->assertFalse((bool)$this->doctorUser->is_active);

        // Toggle back to active
        $response2 = $this->actingAs($this->adminUser)->post(route('portal.staff.toggle-status', $this->doctorProfile));
        $response2->assertRedirect();
        $this->doctorProfile->refresh();
        $this->doctorUser->refresh();
        $this->assertEquals('active', $this->doctorProfile->status);
        $this->assertTrue((bool)$this->doctorUser->is_active);

        $this->assertTrue(AuditLog::where('event', 'staff.status_toggled')
            ->where('auditable_id', $this->doctorProfile->id)
            ->exists());
    }

    /**
     * Test scheduling an 8-hour shift and maintaining shift history.
     */
    public function test_admin_can_schedule_eight_hour_shift(): void
    {
        $gyn = Department::where('code', 'GYN')->firstOrFail();
        $targetDate = '2026-11-05';

        // Clean
        ShiftSchedule::where('staff_profile_id', $this->doctorProfile->id)
            ->where('shift_date', $targetDate)
            ->delete();

        $shiftData = [
            'staff_profile_id' => $this->doctorProfile->id,
            'department_id' => $gyn->id,
            'shift_date' => $targetDate,
            'shift_type' => 'morning',
            'notes' => 'OPD Consultation & Inpatient Rounds',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('portal.staff.schedule-shift'), $shiftData);
        $response->assertRedirect();
        $response->assertSessionHas('status');

        $scheduled = ShiftSchedule::where('staff_profile_id', $this->doctorProfile->id)
            ->where('shift_date', $targetDate)
            ->where('shift_type', 'morning')
            ->first();

        $this->assertNotNull($scheduled);
        $this->assertEquals('08:00:00', $scheduled->start_time);
        $this->assertEquals('16:00:00', $scheduled->end_time);
        $this->assertEquals('scheduled', $scheduled->status);

        $this->assertTrue(AuditLog::where('event', 'staff.shift_scheduled')
            ->where('auditable_id', $scheduled->id)
            ->exists());
    }

    /**
     * Test cannot schedule duplicate overlapping shifts for the same staff member.
     */
    public function test_cannot_schedule_overlapping_incompatible_shifts(): void
    {
        $gyn = Department::where('code', 'GYN')->firstOrFail();
        $targetDate = '2026-11-06';

        ShiftSchedule::firstOrCreate(
            [
                'staff_profile_id' => $this->doctorProfile->id,
                'shift_date' => $targetDate,
                'shift_type' => 'evening',
            ],
            [
                'department_id' => $gyn->id,
                'start_time' => '16:00:00',
                'end_time' => '00:00:00',
                'status' => 'scheduled',
            ]
        );

        // Attempt duplicate evening shift on the same day
        $duplicateData = [
            'staff_profile_id' => $this->doctorProfile->id,
            'department_id' => $gyn->id,
            'shift_date' => $targetDate,
            'shift_type' => 'evening',
            'notes' => 'Duplicate attempt',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('portal.staff.schedule-shift'), $duplicateData);
        $response->assertSessionHasErrors(['shift_type']);
    }

    /**
     * Test cancelling a scheduled shift.
     */
    public function test_admin_can_cancel_scheduled_shift(): void
    {
        $gyn = Department::where('code', 'GYN')->firstOrFail();

        $shift = ShiftSchedule::create([
            'staff_profile_id' => $this->doctorProfile->id,
            'department_id' => $gyn->id,
            'shift_date' => '2026-11-07',
            'shift_type' => 'night',
            'start_time' => '00:00:00',
            'end_time' => '08:00:00',
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($this->adminUser)->delete(route('portal.staff.delete-shift', $shift));
        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->assertNull(ShiftSchedule::find($shift->id));

        $this->assertTrue(AuditLog::where('event', 'staff.shift_deleted')
            ->where('auditable_id', $shift->id)
            ->exists());
    }

    /**
     * Test 24/7 clinic shift scheduling board displays 8h shifts and compliance rules.
     */
    public function test_admin_can_view_clinic_wide_shift_board_and_rules(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('portal.staff.shifts'));

        $response->assertOk();
        $response->assertViewIs('portal.staff.shifts');
        $response->assertSee('24/7 Hospital Shift Scheduling Board');
        $response->assertSee('Morning Shift');
        $response->assertSee('Evening Shift');
        $response->assertSee('Night Shift');
        $response->assertSee('Clinical Rule: 2 Doctors 24/7 Availability');
        $response->assertSee('Clinical Rule: 8-Hour Nursing Shift Coverage');
    }

    /**
     * Test unauthorized users (Patient, unauthorized staff) cannot manage staff.
     */
    public function test_unauthorized_users_cannot_manage_staff(): void
    {
        // Patient cannot access staff directory
        $patientAccess = $this->actingAs($this->patientUser)->get(route('portal.staff.index'));
        $this->assertTrue(in_array($patientAccess->status(), [302, 403]));

        // Colleague doctor without staff.create cannot access create staff form
        $doctorCreate = $this->actingAs($this->colleagueDoctorUser)->get(route('portal.staff.create'));
        $doctorCreate->assertForbidden();

        // Colleague doctor cannot toggle another staff member's status
        $doctorToggle = $this->actingAs($this->colleagueDoctorUser)->post(route('portal.staff.toggle-status', $this->doctorProfile));
        $doctorToggle->assertForbidden();
    }
}
