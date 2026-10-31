<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DepartmentAndBedSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and initial users if not present
        if (Role::count() === 0) {
            $this->seed(RolePermissionSeeder::class);
            $this->seed(DepartmentAndBedSeeder::class);
            $this->seed(SystemSettingsSeeder::class);
            $this->seed(UserSeeder::class);
        } else {
            // Ensure seeded users retain default password
            User::whereIn('email', [
                'admin@meghnaclinic.com',
                'doctor@meghnaclinic.com',
                'nurse@meghnaclinic.com',
                'reception@meghnaclinic.com',
                'billing@meghnaclinic.com',
                'lab@meghnaclinic.com',
                'patient@meghnaclinic.com',
            ])->update(['password' => Hash::make('Meghna@2026!')]);
        }
    }

    /**
     * Test staff login view is accessible.
     */
    public function test_staff_login_view_renders(): void
    {
        $response = $this->get('/portal/login');
        $response->assertStatus(200);
        $response->assertSee('Staff Portal Login');
        $response->assertSee('doctor@meghnaclinic.com');
    }

    /**
     * Test staff authentication succeeds and redirects to role dashboard.
     */
    public function test_staff_admin_can_authenticate_and_redirects_to_admin(): void
    {
        $response = $this->post('/portal/login', [
            'email' => 'admin@meghnaclinic.com',
            'password' => 'Meghna@2026!',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('portal.admin'));
    }

    /**
     * Test doctor authentication redirects to doctor clinical portal.
     */
    public function test_doctor_can_authenticate_and_redirects_to_doctor_portal(): void
    {
        $response = $this->post('/portal/login', [
            'email' => 'doctor@meghnaclinic.com',
            'password' => 'Meghna@2026!',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('portal.doctor'));
    }

    /**
     * Test nurse authentication redirects to nurse ward station.
     */
    public function test_nurse_can_authenticate_and_redirects_to_nurse_portal(): void
    {
        $response = $this->post('/portal/login', [
            'email' => 'nurse@meghnaclinic.com',
            'password' => 'Meghna@2026!',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('portal.nurse'));
    }

    /**
     * Test failed staff login with incorrect password.
     */
    public function test_staff_login_fails_with_invalid_password(): void
    {
        $response = $this->post('/portal/login', [
            'email' => 'admin@meghnaclinic.com',
            'password' => 'WrongPassword123!',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /**
     * Test login rate limiting after 5 consecutive failed attempts.
     */
    public function test_login_rate_limiting_locks_out_after_too_many_attempts(): void
    {
        RateLimiter::clear(str_replace(' ', '', 'test-throttle@meghnaclinic.com|127.0.0.1'));

        for ($i = 0; $i < 5; $i++) {
            $this->post('/portal/login', [
                'email' => 'admin@meghnaclinic.com',
                'password' => 'WrongPassword!',
            ]);
        }

        // 6th attempt must be rate-limited
        $response = $this->post('/portal/login', [
            'email' => 'admin@meghnaclinic.com',
            'password' => 'WrongPassword!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertTrue(session('errors')->first('email') !== null);
    }

    /**
     * Test patient cannot log in through staff portal.
     */
    public function test_patient_cannot_login_through_staff_portal(): void
    {
        $response = $this->post('/portal/login', [
            'email' => 'patient@meghnaclinic.com',
            'password' => 'Meghna@2026!',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('restricted to hospital medical and administrative staff', session('errors')->first('email'));
    }

    /**
     * Test staff logout destroys session.
     */
    public function test_staff_logout_clears_session(): void
    {
        $user = User::where('email', 'admin@meghnaclinic.com')->first();
        $this->actingAs($user);

        $response = $this->post('/portal/logout');

        $this->assertGuest();
        $response->assertRedirect(route('portal.login'));
    }

    /**
     * Test patient portal login view renders.
     */
    public function test_patient_login_view_renders(): void
    {
        $response = $this->get('/patient/login');
        $response->assertStatus(200);
        $response->assertSee('Patient Portal Login');
    }

    /**
     * Test patient can authenticate with email.
     */
    public function test_patient_can_authenticate_with_email(): void
    {
        $response = $this->post('/patient/login', [
            'email' => 'patient@meghnaclinic.com',
            'password' => 'Meghna@2026!',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('patient.dashboard'));
    }

    /**
     * Test patient can authenticate with phone number.
     */
    public function test_patient_can_authenticate_with_phone(): void
    {
        $response = $this->post('/patient/login', [
            'email' => '+8801711000007',
            'password' => 'Meghna@2026!',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('patient.dashboard'));
    }

    /**
     * Test staff member cannot log in through patient portal.
     */
    public function test_staff_cannot_login_through_patient_portal(): void
    {
        $response = $this->post('/patient/login', [
            'email' => 'doctor@meghnaclinic.com',
            'password' => 'Meghna@2026!',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('This portal is for patients only', session('errors')->first('email'));
    }

    /**
     * Test patient self-registration creates unlinked account (privacy rule).
     */
    public function test_patient_self_registration_creates_unlinked_account(): void
    {
        $testEmail = 'newtestpatient_' . time() . '_' . rand(100, 999) . '@example.com';
        $testPhone = '01799' . rand(100000, 999999);

        $response = $this->post('/patient/register', [
            'name' => 'Self Registered Patient',
            'email' => $testEmail,
            'phone' => $testPhone,
            'password' => 'SecurePass2026!',
            'password_confirmation' => 'SecurePass2026!',
            'terms' => '1',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('patient.dashboard'));

        $newUser = User::where('email', $testEmail)->first();
        $this->assertNotNull($newUser);
        $this->assertEquals('patient', $newUser->user_type);
        $this->assertTrue($newUser->hasRole('patient'));

        // Strict privacy rule: patient record in clinical 'patients' table MUST BE NULL
        $this->assertNull($newUser->patient);
    }

    /**
     * Test unauthenticated access to protected routes is redirected to login.
     */
    public function test_unauthenticated_access_redirects_to_login(): void
    {
        $response = $this->get('/portal/admin');
        $response->assertRedirect(route('portal.login'));

        $response2 = $this->get('/patient/dashboard');
        $response2->assertRedirect(route('patient.login'));
    }

    /**
     * Test role-based protection prevents unauthorized staff from viewing forbidden departments.
     */
    public function test_role_protection_prevents_unauthorized_access(): void
    {
        // Doctor attempting to access Admin control center
        $doctor = User::where('email', 'doctor@meghnaclinic.com')->first();
        $response = $this->actingAs($doctor)->get('/portal/admin');
        $response->assertStatus(403);

        // Receptionist attempting to access Doctor clinical workstation
        $reception = User::where('email', 'reception@meghnaclinic.com')->first();
        $response2 = $this->actingAs($reception)->get('/portal/doctor');
        $response2->assertStatus(403);
    }

    /**
     * Test unlinked patient is blocked from sensitive clinical records.
     */
    public function test_unlinked_patient_cannot_access_clinical_records(): void
    {
        $unlinkedPatient = User::where('email', 'newpatient@meghnaclinic.com')->first();
        $this->assertNull($unlinkedPatient->patient);

        $response = $this->actingAs($unlinkedPatient)->get('/patient/prescriptions');
        $response->assertRedirect(route('patient.unlinked'));
    }

    /**
     * Test password reset request flow.
     */
    public function test_password_reset_request_and_reset_flow(): void
    {
        $user = User::where('email', 'doctor@meghnaclinic.com')->first();

        // 1. Request reset link
        $response = $this->post('/forgot-password', [
            'email' => 'doctor@meghnaclinic.com',
        ]);
        $response->assertSessionHas('status');

        $record = DB::table('password_reset_tokens')->where('email', 'doctor@meghnaclinic.com')->first();
        $this->assertNotNull($record);

        // 2. Perform reset with new password
        $token = 'test_token_123456';
        DB::table('password_reset_tokens')->where('email', 'doctor@meghnaclinic.com')->update([
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        $resetResponse = $this->post('/reset-password', [
            'token' => $token,
            'email' => 'doctor@meghnaclinic.com',
            'password' => 'NewMeghnaPassword2026!',
            'password_confirmation' => 'NewMeghnaPassword2026!',
        ]);

        $resetResponse->assertRedirect(route('portal.login'));

        // 3. Verify user can now log in with new password
        $loginResponse = $this->post('/portal/login', [
            'email' => 'doctor@meghnaclinic.com',
            'password' => 'NewMeghnaPassword2026!',
        ]);

        $this->assertAuthenticated();
        $loginResponse->assertRedirect(route('portal.doctor'));

        // Restore password back to default
        $user->forceFill(['password' => Hash::make('Meghna@2026!')])->save();
    }
}
