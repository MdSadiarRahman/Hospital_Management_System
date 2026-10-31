<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Bed;
use App\Models\Department;
use App\Models\Role;
use App\Models\StaffProfile;
use App\Models\User;
use Database\Seeders\DepartmentAndBedSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Database\Seeders\UserSeeder;
use Tests\TestCase;

class DepartmentManagementTest extends TestCase
{
    protected User $adminUser;
    protected User $doctorUser;
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

        // Set up representative users
        $adminRole = Role::firstOrCreate(['name' => 'administrator'], ['title' => 'Administrator', 'user_type' => 'staff']);
        $doctorRole = Role::firstOrCreate(['name' => 'doctor'], ['title' => 'Doctor', 'user_type' => 'staff']);
        $nurseRole = Role::firstOrCreate(['name' => 'nurse'], ['title' => 'Nurse', 'user_type' => 'staff']);
        $patientRole = Role::firstOrCreate(['name' => 'patient'], ['title' => 'Patient', 'user_type' => 'patient']);

        $this->adminUser = User::firstOrCreate(
            ['email' => 'admin.test@meghnaclinic.com'],
            [
                'name' => 'Test Admin',
                'phone' => '01719999001',
                'password' => bcrypt('AdminPass123!'),
                'user_type' => 'staff',
                'is_active' => true,
            ]
        );
        $this->adminUser->roles()->syncWithoutDetaching([$adminRole->id]);

        $this->doctorUser = User::firstOrCreate(
            ['email' => 'doctor.test@meghnaclinic.com'],
            [
                'name' => 'Dr. Test Surgeon',
                'phone' => '01719999002',
                'password' => bcrypt('DoctorPass123!'),
                'user_type' => 'staff',
                'is_active' => true,
            ]
        );
        $this->doctorUser->roles()->syncWithoutDetaching([$doctorRole->id]);

        $this->nurseUser = User::firstOrCreate(
            ['email' => 'nurse.test@meghnaclinic.com'],
            [
                'name' => 'Test Sister Nurse',
                'phone' => '01719999003',
                'password' => bcrypt('NursePass123!'),
                'user_type' => 'staff',
                'is_active' => true,
            ]
        );
        $this->nurseUser->roles()->syncWithoutDetaching([$nurseRole->id]);

        $this->patientUser = User::firstOrCreate(
            ['email' => 'patient.test@meghnaclinic.com'],
            [
                'name' => 'Test Portal Patient',
                'phone' => '01719999004',
                'password' => bcrypt('PatientPass123!'),
                'user_type' => 'patient',
                'is_active' => true,
            ]
        );
        $this->patientUser->roles()->syncWithoutDetaching([$patientRole->id]);
    }

    /**
     * Test admin can view departments list with metrics and search filter.
     */
    public function test_admin_can_view_department_index_with_metrics_and_filters(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('portal.departments.index'));

        $response->assertOk();
        $response->assertViewIs('portal.departments.index');
        $response->assertSee('Gynecology');
        $response->assertSee('Orthopedic');
        $response->assertSee('Surgery');
        $response->assertSee('Department & Ward Management');

        // Test search filter
        $searchResponse = $this->actingAs($this->adminUser)->get(route('portal.departments.index', ['search' => 'Orthopedic']));
        $searchResponse->assertOk();
        $searchResponse->assertSee('Orthopedic');
        $searchResponse->assertDontSee('Gynecology');

        // Test classification filter
        $clinicalResponse = $this->actingAs($this->adminUser)->get(route('portal.departments.index', ['type' => 'clinical']));
        $clinicalResponse->assertOk();
        $clinicalResponse->assertSee('Surgery');
    }

    /**
     * Test admin can access the create department form.
     */
    public function test_admin_can_access_create_department_form(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('portal.departments.create'));

        $response->assertOk();
        $response->assertViewIs('portal.departments.create');
        $response->assertSee('Register New Department');
        $response->assertSee('Department Code');
    }

    /**
     * Test admin can successfully create a new department.
     */
    public function test_admin_can_create_new_department_with_valid_data(): void
    {
        // Unique code and name
        $deptData = [
            'name' => 'Pediatrics and Neonatology',
            'code' => 'PED',
            'description' => 'Comprehensive child healthcare and neonatology unit.',
            'is_clinical' => 1,
            'is_active' => 1,
        ];

        // Ensure clean state
        Department::where('code', 'PED')->delete();

        $response = $this->actingAs($this->adminUser)->post(route('portal.departments.store'), $deptData);

        $createdDept = Department::where('code', 'PED')->first();
        $this->assertNotNull($createdDept);
        $this->assertEquals('Pediatrics and Neonatology', $createdDept->name);
        $this->assertTrue((bool)$createdDept->is_clinical);
        $this->assertTrue((bool)$createdDept->is_active);

        $response->assertRedirect(route('portal.departments.show', $createdDept->id));
        $response->assertSessionHas('status');

        // Assert audit log was recorded
        $this->assertTrue(AuditLog::where('event', 'department.created')
            ->where('auditable_type', Department::class)
            ->where('auditable_id', $createdDept->id)
            ->exists());
    }

    /**
     * Test duplicate department name and code are strictly prevented.
     */
    public function test_cannot_create_duplicate_department_name_or_code(): void
    {
        // Gynecology already exists with code GYN
        $duplicateNameData = [
            'name' => 'Gynecology',
            'code' => 'GYN2',
            'description' => 'Duplicate name test',
            'is_clinical' => 1,
            'is_active' => 1,
        ];

        $response1 = $this->actingAs($this->adminUser)->post(route('portal.departments.store'), $duplicateNameData);
        $response1->assertSessionHasErrors(['name']);

        $duplicateCodeData = [
            'name' => 'Different Department Name',
            'code' => 'GYN',
            'description' => 'Duplicate code test',
            'is_clinical' => 1,
            'is_active' => 1,
        ];

        $response2 = $this->actingAs($this->adminUser)->post(route('portal.departments.store'), $duplicateCodeData);
        $response2->assertSessionHasErrors(['code']);
    }

    /**
     * Test department validation rules for length and required fields.
     */
    public function test_department_validation_rules_enforced(): void
    {
        $invalidData = [
            'name' => '', // Required
            'code' => 'VERYLONGCODE123', // Max 10 chars
            'description' => str_repeat('a', 1500), // Max 1000
        ];

        $response = $this->actingAs($this->adminUser)->post(route('portal.departments.store'), $invalidData);
        $response->assertSessionHasErrors(['name', 'code', 'description']);
    }

    /**
     * Test department details view displays doctors, staff, and beds.
     */
    public function test_admin_can_view_department_details_with_doctors_staff_and_beds(): void
    {
        $gyn = Department::where('code', 'GYN')->firstOrFail();

        $response = $this->actingAs($this->adminUser)->get(route('portal.departments.show', $gyn));

        $response->assertOk();
        $response->assertViewIs('portal.departments.show');
        $response->assertSee($gyn->name);
        $response->assertSee($gyn->code);
        $response->assertSee('Department Inpatient Bed Configuration');
        $response->assertSee('Assigned Medical Doctors');
    }

    /**
     * Test admin can update department details.
     */
    public function test_admin_can_update_department_details(): void
    {
        $dept = Department::firstOrCreate(
            ['code' => 'PHYSIO'],
            ['name' => 'Physiotherapy Unit', 'description' => 'Initial desc', 'is_clinical' => true, 'is_active' => true]
        );

        $updateData = [
            'name' => 'Physical Therapy & Rehabilitation',
            'code' => 'PHYSIO',
            'description' => 'Updated physiotherapy and rehabilitation unit.',
            'is_clinical' => 1,
            'is_active' => 1,
        ];

        $response = $this->actingAs($this->adminUser)->put(route('portal.departments.update', $dept), $updateData);

        $response->assertRedirect(route('portal.departments.show', $dept->id));
        $dept->refresh();
        $this->assertEquals('Physical Therapy & Rehabilitation', $dept->name);
        $this->assertEquals('Updated physiotherapy and rehabilitation unit.', $dept->description);

        $this->assertTrue(AuditLog::where('event', 'department.updated')
            ->where('auditable_id', $dept->id)
            ->exists());
    }

    /**
     * Test admin can toggle active/inactive status of a department.
     */
    public function test_admin_can_toggle_department_status(): void
    {
        $dept = Department::firstOrCreate(
            ['code' => 'STATUS_T'],
            ['name' => 'Status Toggle Test Dept', 'description' => 'Test', 'is_clinical' => false, 'is_active' => true]
        );

        $this->assertTrue((bool)$dept->is_active);

        // Toggle to inactive
        $response1 = $this->actingAs($this->adminUser)->post(route('portal.departments.toggle-status', $dept));
        $response1->assertRedirect();
        $dept->refresh();
        $this->assertFalse((bool)$dept->is_active);

        // Toggle back to active
        $response2 = $this->actingAs($this->adminUser)->post(route('portal.departments.toggle-status', $dept));
        $response2->assertRedirect();
        $dept->refresh();
        $this->assertTrue((bool)$dept->is_active);

        $this->assertTrue(AuditLog::where('event', 'department.status_toggled')
            ->where('auditable_id', $dept->id)
            ->exists());
    }

    /**
     * Test assigning a doctor or staff member to a department and removing them.
     */
    public function test_admin_can_assign_and_remove_staff_from_department(): void
    {
        $dept = Department::where('code', 'GYN')->firstOrFail();

        // Assign doctor
        $assignData = [
            'user_id' => $this->doctorUser->id,
            'designation' => 'Associate Consultant (Gynecology)',
            'specialization' => 'Obstetrics and Fetal Medicine',
            'license_number' => 'BMDC-DOC-7788',
            'shift' => 'morning',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('portal.departments.staff.assign', $dept), $assignData);
        $response->assertRedirect();
        $response->assertSessionHas('status');

        $profile = StaffProfile::where('user_id', $this->doctorUser->id)->first();
        $this->assertNotNull($profile);
        $this->assertEquals($dept->id, $profile->department_id);
        $this->assertEquals('Associate Consultant (Gynecology)', $profile->designation);

        // Remove / unassign doctor from department
        $removeResponse = $this->actingAs($this->adminUser)->delete(route('portal.departments.staff.remove', [$dept, $profile->id]));
        $removeResponse->assertRedirect();
        $profile->refresh();
        $this->assertNull($profile->department_id);
    }

    /**
     * Test admin can configure, update and decommission beds in a department.
     */
    public function test_admin_can_configure_update_and_delete_bed_in_department(): void
    {
        $dept = Department::where('code', 'SURG')->firstOrFail();

        // Ensure bed identifier is clean
        Bed::where('bed_number', 'SURG-UNIT-BED-01')->delete();

        // 1. Configure new bed
        $bedData = [
            'bed_number' => 'SURG-UNIT-BED-01',
            'category' => 'department',
            'daily_charge' => 750.00,
            'status' => 'available',
        ];

        $storeResponse = $this->actingAs($this->adminUser)->post(route('portal.departments.beds.store', $dept), $bedData);
        $storeResponse->assertRedirect();

        $bed = Bed::where('bed_number', 'SURG-UNIT-BED-01')->first();
        $this->assertNotNull($bed);
        $this->assertEquals($dept->id, $bed->department_id);
        $this->assertEquals('750.00', $bed->daily_charge);

        // 2. Update bed tariff and category
        $updateData = [
            'bed_number' => 'SURG-UNIT-BED-01',
            'category' => 'post_op',
            'daily_charge' => 1200.00,
            'status' => 'available',
        ];

        $updateResponse = $this->actingAs($this->adminUser)->put(route('portal.departments.beds.update', [$dept, $bed]), $updateData);
        $updateResponse->assertRedirect();

        $bed->refresh();
        $this->assertEquals('post_op', $bed->category);
        $this->assertEquals('1200.00', $bed->daily_charge);

        // 3. Decommission bed
        $deleteResponse = $this->actingAs($this->adminUser)->delete(route('portal.departments.beds.destroy', [$dept, $bed]));
        $deleteResponse->assertRedirect();

        $this->assertNull(Bed::where('bed_number', 'SURG-UNIT-BED-01')->first());
    }

    /**
     * Test cannot delete a bed that is occupied.
     */
    public function test_cannot_delete_bed_occupied_by_patient(): void
    {
        $dept = Department::where('code', 'ORTHO')->firstOrFail();

        Bed::where('bed_number', 'OCCUPIED-BED-99')->delete();

        $bed = Bed::create([
            'department_id' => $dept->id,
            'bed_number' => 'OCCUPIED-BED-99',
            'category' => 'cabin',
            'daily_charge' => 1500.00,
            'status' => 'occupied',
        ]);

        $response = $this->actingAs($this->adminUser)->delete(route('portal.departments.beds.destroy', [$dept, $bed]));
        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertNotNull(Bed::where('bed_number', 'OCCUPIED-BED-99')->first());

        // Cleanup
        $bed->delete();
    }

    /**
     * Test cannot delete a department if it has configured beds or assigned staff.
     */
    public function test_cannot_delete_department_with_configured_beds(): void
    {
        $gyn = Department::where('code', 'GYN')->firstOrFail();

        // GYN has beds configured by seeder
        $this->assertGreaterThan(0, $gyn->beds()->count());

        $response = $this->actingAs($this->adminUser)->delete(route('portal.departments.destroy', $gyn));
        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertNotNull(Department::where('code', 'GYN')->first());
    }

    /**
     * Test admin can delete an empty, decommissioned department.
     */
    public function test_admin_can_delete_empty_decommissioned_department(): void
    {
        Department::where('code', 'TEMP_DEL')->orWhere('name', 'Temporary Decommissioned Unit')->delete();

        $emptyDept = Department::create([
            'name' => 'Temporary Decommissioned Unit',
            'code' => 'TEMP_DEL',
            'description' => 'To be deleted cleanly',
            'is_clinical' => false,
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->adminUser)->delete(route('portal.departments.destroy', $emptyDept));
        $response->assertRedirect(route('portal.departments.index'));
        $response->assertSessionHas('status');

        $this->assertNull(Department::where('code', 'TEMP_DEL')->first());
    }

    /**
     * Test unauthorized users (Doctor, Nurse, Patient) cannot modify departments.
     */
    public function test_unauthorized_users_cannot_manage_departments(): void
    {
        // Doctor cannot view create page or post new department
        $docCreate = $this->actingAs($this->doctorUser)->get(route('portal.departments.create'));
        $docCreate->assertForbidden();

        $docStore = $this->actingAs($this->doctorUser)->post(route('portal.departments.store'), [
            'name' => 'Doctor Unauthorized Dept',
            'code' => 'DOC_NO',
        ]);
        $docStore->assertForbidden();

        // Nurse cannot manage staff or beds
        $dept = Department::where('code', 'SURG')->firstOrFail();
        $nurseBed = $this->actingAs($this->nurseUser)->post(route('portal.departments.beds.store', $dept), [
            'bed_number' => 'NURSE_BED',
            'category' => 'general',
            'daily_charge' => 500,
            'status' => 'available',
        ]);
        $nurseBed->assertForbidden();

        // Patient cannot access any department management portal routes
        $patientAccess = $this->actingAs($this->patientUser)->get(route('portal.departments.index'));
        // Staff middleware redirects non-staff users or aborts 403
        $this->assertTrue(in_array($patientAccess->status(), [302, 403]));
    }
}
