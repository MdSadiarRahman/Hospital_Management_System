<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\PatientContact;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DepartmentAndBedSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PatientPortalFoundationTest extends TestCase
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
    }

    /**
     * Test patient self-registration and duplicate account prevention.
     */
    public function test_patient_registration_with_duplicate_prevention(): void
    {
        do {
            $uniquePhone = '017' . rand(10000000, 99999999);
        } while (User::where('phone', $uniquePhone)->exists());

        do {
            $uniqueEmail = 'patient_test_' . uniqid() . '_' . rand(1000, 9999) . '@example.com';
        } while (User::where('email', $uniqueEmail)->exists());

        // 1. Successful registration
        $response = $this->post('/patient/register', [
            'name' => 'Khadija Begum',
            'email' => $uniqueEmail,
            'phone' => $uniquePhone,
            'password' => 'SecurePass2026!',
            'password_confirmation' => 'SecurePass2026!',
            'terms' => '1',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('patient.dashboard'));

        $user = User::where('email', $uniqueEmail)->first();
        $this->assertNotNull($user);
        $this->assertEquals('patient', $user->user_type);
        $this->assertTrue($user->hasRole('patient'));

        // Strict privacy rule: self-registered account must not be linked to any clinical chart
        $this->assertNull($user->patient);

        // 2. Duplicate email prevention
        $this->post('/logout');
        $duplicateEmailResponse = $this->post('/patient/register', [
            'name' => 'Another Name',
            'email' => $uniqueEmail,
            'phone' => '01700112233',
            'password' => 'SecurePass2026!',
            'password_confirmation' => 'SecurePass2026!',
            'terms' => '1',
        ]);
        $duplicateEmailResponse->assertSessionHasErrors('email');

        // 3. Duplicate phone prevention
        $duplicatePhoneResponse = $this->post('/patient/register', [
            'name' => 'Third Name',
            'email' => 'third_' . time() . '@example.com',
            'phone' => $uniquePhone,
            'password' => 'SecurePass2026!',
            'password_confirmation' => 'SecurePass2026!',
            'terms' => '1',
        ]);
        $duplicatePhoneResponse->assertSessionHasErrors('phone');
    }

    /**
     * Test patient authentication and profile view.
     */
    public function test_patient_login_and_profile_view(): void
    {
        $patientUser = User::where('email', 'newpatient@meghnaclinic.com')->firstOrFail();

        // 1. Login with phone or email
        $response = $this->post('/patient/login', [
            'email' => '+8801711000008',
            'password' => 'Meghna@2026!',
        ]);

        $this->assertAuthenticatedAs($patientUser);
        $response->assertRedirect(route('patient.dashboard'));

        // 2. View profile as unlinked patient
        $profileResponse = $this->actingAs($patientUser)->get('/patient/profile');
        $profileResponse->assertStatus(200);
        $profileResponse->assertSee('My Account & Guardians', false);
        $profileResponse->assertSee('Pending In-Person Verification');
        $profileResponse->assertSee($patientUser->name);
        $profileResponse->assertSee($patientUser->phone);
    }

    /**
     * Test patient can update their own personal demographics and contact information.
     */
    public function test_patient_profile_update(): void
    {
        $patientUser = User::where('email', 'newpatient@meghnaclinic.com')->firstOrFail();

        $response = $this->actingAs($patientUser)->put('/patient/profile', [
            'name' => 'Tanvir Hasan (Updated)',
            'email' => 'newpatient@meghnaclinic.com',
            'phone' => '+8801711000008',
            'address' => 'Goneshtola, Dinajpur Sadar',
        ]);

        $response->assertRedirect(route('patient.profile'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('users', [
            'id' => $patientUser->id,
            'name' => 'Tanvir Hasan (Updated)',
        ]);

        // Verify audit log
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $patientUser->id,
            'event' => 'patient.profile_updated',
        ]);
    }

    /**
     * Test patient password change and validation.
     */
    public function test_patient_password_change(): void
    {
        $patientUser = User::where('email', 'newpatient@meghnaclinic.com')->firstOrFail();

        // 1. Password change fails with invalid current password
        $failResponse = $this->actingAs($patientUser)->put('/patient/password', [
            'current_password' => 'WrongCurrentPassword!',
            'password' => 'NewStrongPass2026!',
            'password_confirmation' => 'NewStrongPass2026!',
        ]);
        $failResponse->assertSessionHasErrors('current_password');

        // 2. Successful password change
        $successResponse = $this->actingAs($patientUser)->put('/patient/password', [
            'current_password' => 'Meghna@2026!',
            'password' => 'NewStrongPass2026!',
            'password_confirmation' => 'NewStrongPass2026!',
        ]);

        $successResponse->assertRedirect(route('patient.profile'));
        $successResponse->assertSessionHas('status');

        $patientUser->refresh();
        $this->assertTrue(Hash::check('NewStrongPass2026!', $patientUser->password));

        // Restore original password
        $patientUser->update(['password' => Hash::make('Meghna@2026!')]);
    }

    /**
     * Test staff identity verification and patient record linking workflow.
     */
    public function test_staff_verification_and_record_linking(): void
    {
        $receptionist = User::where('email', 'reception@meghnaclinic.com')->firstOrFail();
        $doctor = User::where('email', 'doctor@meghnaclinic.com')->firstOrFail();

        // Create an unlinked patient user
        $unlinkedUser = User::factory()->create([
            'user_type' => 'patient',
            'is_active' => true,
            'email' => 'unlinked_test_' . time() . '@example.com',
            'phone' => '0171122' . rand(1000, 9999),
        ]);
        $unlinkedUser->assignRole('patient');

        // Create an unlinked clinical record
        $clinicalPatient = Patient::create([
            'patient_code' => 'MCNH-2026-' . rand(1000, 9999),
            'user_id' => null,
            'name' => 'Clinical Record Name',
            'date_of_birth' => '1990-05-15',
            'gender' => 'female',
            'blood_group' => 'B+',
            'phone' => $unlinkedUser->phone,
            'nid_or_birth_cert' => '1990271122334455',
            'address' => 'Sadar Hospital Road, Dinajpur',
            'is_verified' => false,
        ]);

        // 1. Non-authorized staff (Doctor) cannot access patient linking desk
        $forbiddenResponse = $this->actingAs($doctor)->get('/portal/patient-linking');
        $forbiddenResponse->assertStatus(403);

        // 2. Authorized staff (Receptionist) accesses linking desk
        $linkingDeskResponse = $this->actingAs($receptionist)->get('/portal/patient-linking');
        $linkingDeskResponse->assertStatus(200);
        $linkingDeskResponse->assertSee('Verify & Link Patient Accounts', false);

        // 3. Staff links the account
        $linkResponse = $this->actingAs($receptionist)->post('/portal/patient-linking', [
            'user_id' => $unlinkedUser->id,
            'patient_id' => $clinicalPatient->id,
            'verification_method' => 'physical_in_person',
            'notes' => 'Verified with NID card at reception counter.',
        ]);

        $linkResponse->assertSessionHas('status');

        $clinicalPatient->refresh();
        $this->assertEquals($unlinkedUser->id, $clinicalPatient->user_id);
        $this->assertTrue($clinicalPatient->is_verified);
        $this->assertEquals($receptionist->id, $clinicalPatient->verified_by);
        $this->assertNotNull($clinicalPatient->verified_at);

        // 4. Verify audit log entry
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $receptionist->id,
            'event' => 'patient.account_linked_by_staff',
            'auditable_id' => $clinicalPatient->id,
        ]);

        // 5. Now the patient can view verified badge on profile
        $patientProfileResponse = $this->actingAs($unlinkedUser)->get('/patient/profile');
        $patientProfileResponse->assertStatus(200);
        $patientProfileResponse->assertSee('Verified Patient: ' . $clinicalPatient->patient_code);

        // 6. Test Staff Unlinking
        $unlinkResponse = $this->actingAs($receptionist)->post('/portal/patient-linking/' . $clinicalPatient->id . '/unlink');
        $unlinkResponse->assertSessionHas('status');

        $clinicalPatient->refresh();
        $this->assertNull($clinicalPatient->user_id);
        $this->assertFalse($clinicalPatient->is_verified);
    }

    /**
     * Test emergency contact ownership and authorization rules.
     */
    public function test_emergency_contact_ownership_enforcement(): void
    {
        // Setup Patient A (Linked)
        $userA = User::factory()->create(['user_type' => 'patient', 'is_active' => true]);
        $userA->assignRole('patient');
        $patientA = Patient::create([
            'patient_code' => 'MCNH-2026-' . rand(1000, 9999),
            'user_id' => $userA->id,
            'name' => 'Patient A',
            'date_of_birth' => '1992-01-01',
            'gender' => 'male',
            'phone' => '01700000001',
            'is_verified' => true,
        ]);

        // Setup Patient B (Linked)
        $userB = User::factory()->create(['user_type' => 'patient', 'is_active' => true]);
        $userB->assignRole('patient');
        $patientB = Patient::create([
            'patient_code' => 'MCNH-2026-' . rand(1000, 9999),
            'user_id' => $userB->id,
            'name' => 'Patient B',
            'date_of_birth' => '1995-02-02',
            'gender' => 'female',
            'phone' => '01700000002',
            'is_verified' => true,
        ]);

        // 1. Patient A adds emergency contact
        $addContactResponse = $this->actingAs($userA)->post('/patient/emergency-contacts', [
            'name' => 'Brother of Patient A',
            'relationship' => 'Brother',
            'phone' => '01788776655',
            'is_primary' => '1',
        ]);
        $addContactResponse->assertRedirect(route('patient.profile'));

        $contactA = PatientContact::where('patient_id', $patientA->id)->firstOrFail();
        $this->assertEquals('Brother of Patient A', $contactA->name);

        // 2. Patient B attempts to delete Patient A's emergency contact -> MUST BE FORBIDDEN (403)
        $unauthorizedDelete = $this->actingAs($userB)->delete('/patient/emergency-contacts/' . $contactA->id);
        $unauthorizedDelete->assertStatus(403);
        $this->assertDatabaseHas('patient_contacts', ['id' => $contactA->id]);

        // 3. Patient A deletes their own emergency contact -> ALLOWED
        $authorizedDelete = $this->actingAs($userA)->delete('/patient/emergency-contacts/' . $contactA->id);
        $authorizedDelete->assertRedirect(route('patient.profile'));
        $this->assertDatabaseMissing('patient_contacts', ['id' => $contactA->id]);
    }
}
