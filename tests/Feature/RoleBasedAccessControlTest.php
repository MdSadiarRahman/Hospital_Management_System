<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Bed;
use App\Models\Bill;
use App\Models\Consultation;
use App\Models\LabOrder;
use App\Models\Patient;
use App\Models\Permission;
use App\Models\Prescription;
use App\Models\Role;
use App\Models\User;
use App\Services\RoleAssignmentService;
use Database\Seeders\DepartmentAndBedSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleBasedAccessControlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (Role::count() === 0) {
            $this->seed(RolePermissionSeeder::class);
            $this->seed(DepartmentAndBedSeeder::class);
            $this->seed(SystemSettingsSeeder::class);
            $this->seed(UserSeeder::class);
        }

        // Register testing routes for permission middleware validation
        Route::middleware(['web', 'auth', 'permission:patients.create'])->get('/test-route/patients-create', function () {
            return response()->json(['message' => 'Allowed: patient create access']);
        });

        Route::middleware(['web', 'auth', 'permission:billing.approve'])->get('/test-route/billing-approve', function () {
            return response()->json(['message' => 'Allowed: billing approve access']);
        });

        Route::middleware(['web', 'auth', 'permission:lab.approve'])->get('/test-route/lab-approve', function () {
            return response()->json(['message' => 'Allowed: lab approve access']);
        });
    }

    /**
     * Test all 9 approved roles exist in the system.
     */
    public function test_all_nine_approved_roles_exist(): void
    {
        $approvedRoles = [
            'super_admin',
            'administrator',
            'receptionist',
            'doctor',
            'nurse',
            'lab_staff',
            'billing_staff',
            'ot_ward_staff',
            'patient',
        ];

        foreach ($approvedRoles as $roleName) {
            $this->assertDatabaseHas('roles', ['name' => $roleName]);
        }

        $this->assertGreaterThanOrEqual(9, Role::count());
    }

    /**
     * Test permissions include View, Create, Update, Delete, Approve, Print actions.
     */
    public function test_permissions_include_standard_actions(): void
    {
        $requiredActions = ['view', 'create', 'update', 'delete', 'approve', 'print'];

        foreach ($requiredActions as $action) {
            $matchingPermissions = Permission::where('name', 'like', "%.$action%")->count();
            $this->assertGreaterThan(0, $matchingPermissions, "Missing granular permission for action: {$action}");
        }
    }

    /**
     * Test User model role and permission helper methods.
     */
    public function test_user_role_and_permission_helpers(): void
    {
        $doctor = User::where('email', 'doctor@meghnaclinic.com')->firstOrFail();

        $this->assertTrue($doctor->hasRole('doctor'));
        $this->assertTrue($doctor->hasRole(['doctor', 'nurse']));
        $this->assertFalse($doctor->hasRole('billing_staff'));
        $this->assertFalse($doctor->isAdministrative());

        $this->assertTrue($doctor->hasPermission('consultations.create'));
        $this->assertTrue($doctor->hasPermission('prescriptions.create'));
        $this->assertFalse($doctor->hasPermission('billing.approve'));

        $superAdmin = User::where('email', 'superadmin@meghnaclinic.com')->firstOrFail();
        $this->assertTrue($superAdmin->hasRole('super_admin'));
        $this->assertTrue($superAdmin->isAdministrative());
        // Super admin bypasses all permission checks
        $this->assertTrue($superAdmin->hasPermission('any.arbitrary.permission'));
    }

    /**
     * Test server-side Gate authorization.
     */
    public function test_server_side_gate_authorization(): void
    {
        $doctor = User::where('email', 'doctor@meghnaclinic.com')->firstOrFail();
        $receptionist = User::where('email', 'reception@meghnaclinic.com')->firstOrFail();

        $this->actingAs($doctor);
        $this->assertTrue(Gate::allows('consultations.create'));
        $this->assertTrue(Gate::allows('prescriptions.approve'));
        $this->assertFalse(Gate::allows('billing.approve'));

        $this->actingAs($receptionist);
        $this->assertTrue(Gate::allows('patients.create'));
        $this->assertFalse(Gate::allows('consultations.create'));
        $this->assertFalse(Gate::allows('prescriptions.create'));
    }

    /**
     * Test role-based middleware blocks unauthorized users (403 Forbidden).
     */
    public function test_role_middleware_blocks_unauthorized_staff(): void
    {
        $doctor = User::where('email', 'doctor@meghnaclinic.com')->firstOrFail();
        $nurse = User::where('email', 'nurse@meghnaclinic.com')->firstOrFail();
        $receptionist = User::where('email', 'reception@meghnaclinic.com')->firstOrFail();
        $billing = User::where('email', 'billing@meghnaclinic.com')->firstOrFail();
        $lab = User::where('email', 'lab@meghnaclinic.com')->firstOrFail();

        // Doctor is unauthorized for Admin control center
        $this->actingAs($doctor)->get('/portal/admin')->assertStatus(403);

        // Receptionist is unauthorized for Doctor workstation
        $this->actingAs($receptionist)->get('/portal/doctor')->assertStatus(403);

        // Nurse is unauthorized for Billing desk
        $this->actingAs($nurse)->get('/portal/billing')->assertStatus(403);

        // Billing staff is unauthorized for Diagnostic Lab workstation
        $this->actingAs($billing)->get('/portal/lab')->assertStatus(403);

        // Lab staff is unauthorized for Billing desk
        $this->actingAs($lab)->get('/portal/billing')->assertStatus(403);
    }

    /**
     * Test role-based middleware allows authorized staff to access their respective workstations.
     */
    public function test_role_middleware_allows_authorized_staff(): void
    {
        $admin = User::where('email', 'admin@meghnaclinic.com')->firstOrFail();
        $doctor = User::where('email', 'doctor@meghnaclinic.com')->firstOrFail();
        $nurse = User::where('email', 'nurse@meghnaclinic.com')->firstOrFail();
        $receptionist = User::where('email', 'reception@meghnaclinic.com')->firstOrFail();
        $billing = User::where('email', 'billing@meghnaclinic.com')->firstOrFail();
        $lab = User::where('email', 'lab@meghnaclinic.com')->firstOrFail();
        $otWard = User::where('email', 'otward@meghnaclinic.com')->firstOrFail();

        $this->actingAs($admin)->get('/portal/admin')->assertStatus(200);
        $this->actingAs($doctor)->get('/portal/doctor')->assertStatus(200);
        $this->actingAs($nurse)->get('/portal/nurse')->assertStatus(200);
        $this->actingAs($receptionist)->get('/portal/reception')->assertStatus(200);
        $this->actingAs($billing)->get('/portal/billing')->assertStatus(200);
        $this->actingAs($lab)->get('/portal/lab')->assertStatus(200);
        $this->actingAs($otWard)->get('/portal/ot-ward')->assertStatus(200);
    }

    /**
     * Test Super Admin universal access bypass on all role routes.
     */
    public function test_super_admin_has_universal_access_to_all_workstations(): void
    {
        $superAdmin = User::where('email', 'superadmin@meghnaclinic.com')->firstOrFail();

        $this->actingAs($superAdmin);
        $this->get('/portal/admin')->assertStatus(200);
        $this->get('/portal/doctor')->assertStatus(200);
        $this->get('/portal/nurse')->assertStatus(200);
        $this->get('/portal/reception')->assertStatus(200);
        $this->get('/portal/billing')->assertStatus(200);
        $this->get('/portal/lab')->assertStatus(200);
        $this->get('/portal/ot-ward')->assertStatus(200);
    }

    /**
     * Test patient user is blocked from staff portal by EnsureStaff middleware.
     */
    public function test_patient_user_cannot_access_staff_portal(): void
    {
        $patient = User::where('email', 'patient@meghnaclinic.com')->firstOrFail();

        $this->actingAs($patient)->get('/portal/admin')->assertRedirect(route('patient.dashboard'));
        $this->actingAs($patient)->get('/portal/doctor')->assertRedirect(route('patient.dashboard'));
        $this->actingAs($patient)->get('/portal/dashboard')->assertRedirect(route('patient.dashboard'));
    }

    /**
     * Test permission middleware enforces granular access and blocks unauthorized requests.
     */
    public function test_permission_middleware_protection(): void
    {
        $receptionist = User::where('email', 'reception@meghnaclinic.com')->firstOrFail();
        $billing = User::where('email', 'billing@meghnaclinic.com')->firstOrFail();
        $lab = User::where('email', 'lab@meghnaclinic.com')->firstOrFail();

        // Receptionist has patients.create permission -> 200 OK
        $this->actingAs($receptionist)->get('/test-route/patients-create')->assertStatus(200);

        // Receptionist lacks billing.approve permission -> 403 Forbidden
        $this->actingAs($receptionist)->get('/test-route/billing-approve')->assertStatus(403);

        // Billing staff has billing.approve permission -> 200 OK
        $this->actingAs($billing)->get('/test-route/billing-approve')->assertStatus(200);

        // Lab staff has laboratory.approve permission -> 200 OK
        $this->actingAs($lab)->get('/test-route/lab-approve')->assertStatus(200);

        // Billing staff lacks laboratory.approve permission -> 403 Forbidden
        $this->actingAs($billing)->get('/test-route/lab-approve')->assertStatus(403);
    }

    /**
     * Test UserPolicy prevents users from assigning themselves administrative roles.
     */
    public function test_user_cannot_assign_themselves_administrative_roles(): void
    {
        $admin = User::where('email', 'admin@meghnaclinic.com')->firstOrFail();
        $doctor = User::where('email', 'doctor@meghnaclinic.com')->firstOrFail();

        // Doctor cannot assign themselves administrator or super_admin
        $this->assertFalse(Gate::forUser($doctor)->allows('assignRole', [$doctor, 'administrator']));
        $this->assertFalse(Gate::forUser($doctor)->allows('assignRole', [$doctor, 'super_admin']));

        // Even an administrator cannot assign themselves super_admin (anti-self-escalation)
        $this->assertFalse(Gate::forUser($admin)->allows('assignRole', [$admin, 'super_admin']));
        $this->assertFalse(Gate::forUser($admin)->allows('assignRole', [$admin, 'administrator']));
    }

    /**
     * Test RoleAssignmentService strictly enforces anti-self-escalation exception.
     */
    public function test_role_assignment_service_blocks_self_administrative_assignment(): void
    {
        $service = new RoleAssignmentService();
        $doctor = User::where('email', 'doctor@meghnaclinic.com')->firstOrFail();

        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('Users are strictly forbidden from assigning themselves administrative roles');

        $service->assignRole($doctor, $doctor, 'administrator');
    }

    /**
     * Test non-super-admin cannot assign administrative roles to other users.
     */
    public function test_non_super_admin_cannot_assign_administrative_roles(): void
    {
        $service = new RoleAssignmentService();
        $admin = User::where('email', 'admin@meghnaclinic.com')->firstOrFail();
        $doctor = User::where('email', 'doctor@meghnaclinic.com')->firstOrFail();

        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('Only a Super Admin may grant administrative roles');

        $service->assignRole($admin, $doctor, 'super_admin');
    }

    /**
     * Test Super Admin can successfully assign roles and creates an audit log entry.
     */
    public function test_super_admin_can_assign_role_with_audit_trail(): void
    {
        $service = new RoleAssignmentService();
        $superAdmin = User::where('email', 'superadmin@meghnaclinic.com')->firstOrFail();
        $nurse = User::where('email', 'nurse@meghnaclinic.com')->firstOrFail();

        $initialAuditCount = AuditLog::where('event', 'role.assigned')->count();

        // Assign ot_ward_staff role to nurse as a secondary assignment
        $service->assignRole($superAdmin, $nurse, 'ot_ward_staff');

        $this->assertTrue($nurse->fresh()->hasRole('ot_ward_staff'));
        $this->assertGreaterThan($initialAuditCount, AuditLog::where('event', 'role.assigned')->count());

        // Revoke the role and verify audit trail
        $service->removeRole($superAdmin, $nurse, 'ot_ward_staff');
        $this->assertFalse($nurse->fresh()->hasRole('ot_ward_staff'));
    }

    /**
     * Test PatientPolicy patient privacy rules.
     */
    public function test_patient_policy_enforcement(): void
    {
        $doctor = User::where('email', 'doctor@meghnaclinic.com')->firstOrFail();
        $patientUser = User::where('email', 'patient@meghnaclinic.com')->firstOrFail();
        $receptionist = User::where('email', 'reception@meghnaclinic.com')->firstOrFail();

        $patient = Patient::first();
        if (!$patient) {
            $patient = Patient::create([
                'patient_code' => 'MCNH-2026-9999',
                'name' => 'Test Patient',
                'phone' => '+8801700000000',
                'gender' => 'female',
                'date_of_birth' => '1995-05-15',
                'is_verified' => true,
            ]);
        }

        // Doctor and receptionist can view patient
        $this->assertTrue(Gate::forUser($doctor)->allows('view', $patient));
        $this->assertTrue(Gate::forUser($receptionist)->allows('view', $patient));

        // Patient user cannot view another patient's records
        $this->assertFalse(Gate::forUser($patientUser)->allows('view', $patient));

        // Receptionist can create new patient
        $this->assertTrue(Gate::forUser($receptionist)->allows('create', Patient::class));

        // Doctor cannot create new patient (intake is receptionist role)
        $this->assertFalse(Gate::forUser($doctor)->allows('create', Patient::class));
    }

    /**
     * Test BedPolicy access rules.
     */
    public function test_bed_policy_enforcement(): void
    {
        $nurse = User::where('email', 'nurse@meghnaclinic.com')->firstOrFail();
        $otWard = User::where('email', 'otward@meghnaclinic.com')->firstOrFail();
        $receptionist = User::where('email', 'reception@meghnaclinic.com')->firstOrFail();
        $patient = User::where('email', 'patient@meghnaclinic.com')->firstOrFail();

        $bed = Bed::first();
        if (!$bed) {
            $bed = Bed::create([
                'number' => 'B-99',
                'name' => 'Test Bed 99',
                'type' => 'general',
                'daily_rate' => 1200.00,
                'status' => 'available',
            ]);
        }

        // Nurse and OT/Ward staff can update bed status (cleaning/ready)
        $this->assertTrue(Gate::forUser($nurse)->allows('statusUpdate', $bed));
        $this->assertTrue(Gate::forUser($otWard)->allows('statusUpdate', $bed));

        // Receptionist can assign beds
        $this->assertTrue(Gate::forUser($receptionist)->allows('assign', $bed));

        // Patient cannot update or assign beds
        $this->assertFalse(Gate::forUser($patient)->allows('statusUpdate', $bed));
        $this->assertFalse(Gate::forUser($patient)->allows('assign', $bed));
    }
}
