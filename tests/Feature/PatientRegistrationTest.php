<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\PatientContact;
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use App\Services\PatientRegistrationService;
use Database\Seeders\DepartmentAndBedSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Database\Seeders\UserSeeder;
use Tests\TestCase;

class PatientRegistrationTest extends TestCase
{
    protected User $adminUser;
    protected User $receptionistUser;
    protected User $patientUser;
    protected PatientRegistrationService $patientService;

    protected function setUp(): void
    {
        parent::setUp();

        if (Role::count() === 0) {
            $this->seed(RolePermissionSeeder::class);
            $this->seed(DepartmentAndBedSeeder::class);
            $this->seed(SystemSettingsSeeder::class);
            $this->seed(UserSeeder::class);
        }

        $adminRole = Role::where('name', 'administrator')->firstOrFail();
        $receptionistRole = Role::where('name', 'receptionist')->firstOrFail();
        $patientRole = Role::where('name', 'patient')->firstOrFail();

        // 1. Admin Staff User
        $this->adminUser = User::updateOrCreate(
            ['email' => 'admin.patient.test@meghnaclinic.com'],
            [
                'name' => 'Admin Patient Test Officer',
                'phone' => '01719999001',
                'password' => bcrypt('AdminPass123!'),
                'user_type' => 'staff',
                'is_active' => true,
            ]
        );
        $this->adminUser->roles()->syncWithoutDetaching([$adminRole->id]);

        // 2. Receptionist Staff User
        $this->receptionistUser = User::updateOrCreate(
            ['email' => 'reception.patient.test@meghnaclinic.com'],
            [
                'name' => 'Receptionist Intake Officer',
                'phone' => '01719999002',
                'password' => bcrypt('ReceptionPass123!'),
                'user_type' => 'staff',
                'is_active' => true,
            ]
        );
        $this->receptionistUser->roles()->syncWithoutDetaching([$receptionistRole->id]);

        // 3. Patient Portal User
        $this->patientUser = User::updateOrCreate(
            ['email' => 'regular.patient.test@meghnaclinic.com'],
            [
                'name' => 'Patient Portal User',
                'phone' => '01719999003',
                'password' => bcrypt('PatientPass123!'),
                'user_type' => 'patient',
                'is_active' => true,
            ]
        );
        $this->patientUser->roles()->syncWithoutDetaching([$patientRole->id]);

        $this->patientService = app(PatientRegistrationService::class);
    }

    /**
     * Test receptionist and admin can access patient directory with metrics and search.
     */
    public function test_authorized_staff_can_view_patient_registry_with_metrics_and_search(): void
    {
        // Safely recreate baseline test patient to ensure it has latest ID on page 1
        $existing = Patient::where('patient_code', 'MCNH-2026-9901')->first();
        if ($existing) {
            $existingVisits = Visit::where('patient_id', $existing->id)->pluck('id');
            if ($existingVisits->isNotEmpty()) {
                AuditLog::where('auditable_type', Visit::class)->whereIn('auditable_id', $existingVisits)->delete();
                Visit::whereIn('id', $existingVisits)->delete();
            }
            PatientContact::where('patient_id', $existing->id)->delete();
            AuditLog::where('auditable_type', Patient::class)->where('auditable_id', $existing->id)->delete();
            $existing->delete();
        }

        $testPatient = Patient::create([
            'patient_code' => 'MCNH-2026-9901',
            'name' => 'Test Patient For Search',
            'date_of_birth' => '1990-01-15',
            'gender' => 'female',
            'blood_group' => 'B+',
            'phone' => '01719999111',
            'email' => 'searchtest@meghnaclinic.com',
            'address' => 'Shohid Minar Mor, Dinajpur',
            'registration_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->receptionistUser)->get(route('portal.patients.index'));

        $response->assertOk();
        $response->assertViewIs('portal.patients.index');
        $response->assertSee('Hospital Patient Master Registry');
        $response->assertSee('Total Registered');
        $response->assertSee('MCNH-2026-9901');
        $response->assertSee('Test Patient For Search');

        // Test search query filter
        $searchResponse = $this->actingAs($this->receptionistUser)->get(route('portal.patients.index', ['search' => '9901']));
        $searchResponse->assertOk();
        $searchResponse->assertSee('MCNH-2026-9901');
        $searchResponse->assertSee('Test Patient For Search');
    }

    /**
     * Test authorized staff can view registration form with auto-suggested ID.
     */
    public function test_authorized_staff_can_view_registration_form_with_suggested_id(): void
    {
        $response = $this->actingAs($this->receptionistUser)->get(route('portal.patients.create'));

        $response->assertOk();
        $response->assertViewIs('portal.patients.create');
        $response->assertSee('Register New Hospital Patient');
        $response->assertSee('Patient Demographic Master Record');
        $response->assertSee('Emergency Contact');
        $response->assertSee('MCNH-' . date('Y') . '-');
    }

    /**
     * Test sequential unique Patient ID generation (MCNH-YYYY-XXXX).
     */
    public function test_unique_patient_id_auto_generation_sequence(): void
    {
        $testYear = 2099;
        Patient::where('patient_code', 'like', "MCNH-{$testYear}-%")->delete();

        $id1 = $this->patientService->generatePatientId($testYear);
        $this->assertEquals('MCNH-2099-0001', $id1);

        // Pre-create patient with high number
        Patient::create([
            'patient_code' => 'MCNH-2099-8888',
            'name' => 'High Sequence Patient',
            'date_of_birth' => '1985-05-20',
            'gender' => 'male',
            'phone' => '01719999888',
            'registration_date' => '2099-10-31',
        ]);

        $nextId = $this->patientService->generatePatientId($testYear);
        $this->assertEquals('MCNH-2099-8889', $nextId);
    }

    /**
     * Test successful patient registration with emergency contact and audit logging.
     */
    public function test_staff_can_register_new_patient_with_emergency_contact_and_audit_log(): void
    {
        Patient::where('phone', '01719999222')->delete();

        $formData = [
            'name' => 'Fatima Begum',
            'date_of_birth' => '1995-08-25',
            'gender' => 'female',
            'blood_group' => 'O+',
            'phone' => '01719999222',
            'email' => 'fatima.begum@meghnaclinic.test',
            'nid_or_birth_cert' => '1995271890001',
            'address' => 'Goneshtola, Dinajpur Sadar',
            'registration_date' => now()->toDateString(),
            'emergency_contact_name' => 'Md. Rafiqul Alam',
            'emergency_contact_relationship' => 'Spouse',
            'emergency_contact_phone' => '01719999333',
        ];

        $response = $this->actingAs($this->receptionistUser)->post(route('portal.patients.store'), $formData);

        $createdPatient = Patient::where('phone', '01719999222')->first();
        $this->assertNotNull($createdPatient);
        $this->assertEquals('Fatima Begum', $createdPatient->name);
        $this->assertMatchesRegularExpression('/^MCNH-' . date('Y') . '-\d{4,}$/', $createdPatient->patient_code);

        $response->assertRedirect(route('portal.patients.show', $createdPatient));
        $response->assertSessionHas('success');

        // Verify primary emergency contact was created
        $this->assertDatabaseHas('patient_contacts', [
            'patient_id' => $createdPatient->id,
            'name' => 'Md. Rafiqul Alam',
            'relationship' => 'Spouse',
            'phone' => '01719999333',
            'is_primary' => 1,
        ]);

        // Verify audit log history entry
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->receptionistUser->id,
            'event' => 'patient_registered',
            'auditable_type' => Patient::class,
            'auditable_id' => $createdPatient->id,
        ]);
    }

    /**
     * Test form validation rules on patient intake.
     */
    public function test_patient_registration_validation_rules_enforced(): void
    {
        $invalidData = [
            'name' => '', // Required
            'date_of_birth' => now()->addDays(5)->toDateString(), // Cannot be future date
            'gender' => 'invalid_gender', // Must be in:male,female,other
            'phone' => '', // Required
            'emergency_contact_name' => '', // Required
            'emergency_contact_relationship' => '', // Required
            'emergency_contact_phone' => '', // Required
        ];

        $response = $this->actingAs($this->receptionistUser)->post(route('portal.patients.store'), $invalidData);

        $response->assertSessionHasErrors([
            'name',
            'date_of_birth',
            'gender',
            'phone',
            'emergency_contact_name',
            'emergency_contact_relationship',
            'emergency_contact_phone',
        ]);
    }

    /**
     * Test duplicate detection warns and does not automatically merge or create without override.
     */
    public function test_duplicate_detection_warns_and_does_not_automatically_merge_or_create_without_override(): void
    {
        // 1. Existing patient in database
        Patient::where('phone', '01719999444')->delete();
        $existing = Patient::create([
            'patient_code' => 'MCNH-2026-7777',
            'name' => 'Tariqul Islam',
            'date_of_birth' => '1988-04-12',
            'gender' => 'male',
            'phone' => '01719999444',
            'registration_date' => now()->subMonths(2)->toDateString(),
        ]);

        $initialPatientCount = Patient::count();

        // 2. Attempt to register another patient with the EXACT SAME phone number without override
        $duplicateAttempt = [
            'name' => 'Tariqul Islam Jr',
            'date_of_birth' => '1988-04-12',
            'gender' => 'male',
            'phone' => '01719999444', // Duplicate phone
            'emergency_contact_name' => 'Guardian Test',
            'emergency_contact_relationship' => 'Father',
            'emergency_contact_phone' => '01719999555',
            'confirm_not_duplicate' => false,
        ];

        $response = $this->actingAs($this->receptionistUser)->post(route('portal.patients.store'), $duplicateAttempt);

        // Record must NOT be created or merged automatically
        $this->assertEquals($initialPatientCount, Patient::count());
        $response->assertSessionHas('duplicate_warning');
        $response->assertSessionHas('duplicate_patients');
    }

    /**
     * Test duplicate detection allows distinct registration when staff confirms it is not a duplicate.
     */
    public function test_duplicate_detection_allows_registration_when_explicitly_confirmed_as_distinct_patient(): void
    {
        // Existing patient with phone
        Patient::where('phone', '01719999666')->delete();
        Patient::create([
            'patient_code' => 'MCNH-2026-7778',
            'name' => 'Family Member One',
            'date_of_birth' => '1980-01-01',
            'gender' => 'female',
            'phone' => '01719999666',
            'registration_date' => now()->toDateString(),
        ]);

        $initialPatientCount = Patient::count();

        // Family member two sharing the same household mobile, staff ticks confirm_not_duplicate
        $distinctRegistration = [
            'name' => 'Family Member Two',
            'date_of_birth' => '2010-06-15',
            'gender' => 'male',
            'phone' => '01719999666', // Same phone number
            'emergency_contact_name' => 'Family Member One',
            'emergency_contact_relationship' => 'Mother',
            'emergency_contact_phone' => '01719999666',
            'confirm_not_duplicate' => true, // Explicit override
        ];

        $response = $this->actingAs($this->receptionistUser)->post(route('portal.patients.store'), $distinctRegistration);

        // New distinct patient MUST be created with their own unique ID, not merged
        $this->assertEquals($initialPatientCount + 1, Patient::count());
        $newPatient = Patient::where('name', 'Family Member Two')->first();
        $this->assertNotNull($newPatient);
        $this->assertNotEquals('MCNH-2026-7778', $newPatient->patient_code);
        $response->assertRedirect(route('portal.patients.show', $newPatient));
    }

    /**
     * Test real-time AJAX duplicate detection endpoint.
     */
    public function test_real_time_duplicate_check_api_endpoint(): void
    {
        Patient::where('phone', '01719999777')->delete();
        Patient::create([
            'patient_code' => 'MCNH-2026-7779',
            'name' => 'Duplicate API Patient',
            'date_of_birth' => '1992-03-10',
            'gender' => 'male',
            'phone' => '01719999777',
            'registration_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->receptionistUser)->postJson(route('portal.patients.check-duplicates'), [
            'phone' => '01719999777',
        ]);

        $response->assertOk();
        $response->assertJson([
            'has_duplicates' => true,
            'count' => 1,
        ]);
        $response->assertJsonFragment([
            'patient_code' => 'MCNH-2026-7779',
            'name' => 'Duplicate API Patient',
        ]);
    }

    /**
     * Test viewing patient master profile with emergency contact and audit trail.
     */
    public function test_authorized_staff_can_view_patient_master_profile_and_audit_history(): void
    {
        Patient::where('patient_code', 'MCNH-2026-7780')->delete();

        $patient = Patient::create([
            'patient_code' => 'MCNH-2026-7780',
            'name' => 'Show Patient Profile Test',
            'date_of_birth' => '1993-11-20',
            'gender' => 'female',
            'phone' => '01719999881',
            'address' => 'Shohid Minar Mor, Dinajpur',
            'registration_date' => now()->toDateString(),
        ]);

        PatientContact::create([
            'patient_id' => $patient->id,
            'name' => 'Mother Kin Test',
            'relationship' => 'Mother',
            'phone' => '01719999882',
            'is_primary' => true,
        ]);

        AuditLog::create([
            'user_id' => $this->receptionistUser->id,
            'event' => 'patient_registered',
            'auditable_type' => Patient::class,
            'auditable_id' => $patient->id,
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->receptionistUser)->get(route('portal.patients.show', $patient->id));

        $response->assertOk();
        $response->assertViewIs('portal.patients.show');
        $response->assertSee('Show Patient Profile Test');
        $response->assertSee('MCNH-2026-7780');
        $response->assertSee('Mother Kin Test');
        $response->assertSee('Patient Registered');
    }

    /**
     * Test updating patient demographics and emergency contact with audit logging.
     */
    public function test_authorized_staff_can_update_patient_details_with_audit_trail(): void
    {
        Patient::where('patient_code', 'MCNH-2026-7781')->delete();

        $patient = Patient::create([
            'patient_code' => 'MCNH-2026-7781',
            'name' => 'Original Patient Name',
            'date_of_birth' => '1991-02-14',
            'gender' => 'male',
            'phone' => '01719999883',
            'registration_date' => now()->toDateString(),
        ]);

        $updateData = [
            'name' => 'Updated Patient Name',
            'date_of_birth' => '1991-02-14',
            'gender' => 'male',
            'blood_group' => 'AB+',
            'phone' => '01719999884',
            'email' => 'updated.email@meghnaclinic.test',
            'address' => 'New Address Dinajpur',
            'emergency_contact_name' => 'New Kin Guardian',
            'emergency_contact_relationship' => 'Brother',
            'emergency_contact_phone' => '01719999885',
        ];

        $response = $this->actingAs($this->receptionistUser)->put(route('portal.patients.update', $patient->id), $updateData);

        $response->assertRedirect(route('portal.patients.show', $patient->id));

        $patient->refresh();
        $this->assertEquals('Updated Patient Name', $patient->name);
        $this->assertEquals('01719999884', $patient->phone);
        $this->assertEquals('AB+', $patient->blood_group);

        // Verify primary contact updated
        $this->assertEquals('New Kin Guardian', $patient->primaryContact->name);

        // Verify audit log recorded
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->receptionistUser->id,
            'event' => 'patient_updated',
            'auditable_type' => Patient::class,
            'auditable_id' => $patient->id,
        ]);
    }

    /**
     * Test printable registration card / slip.
     */
    public function test_authorized_staff_can_view_printable_registration_card(): void
    {
        Patient::where('patient_code', 'MCNH-2026-7782')->delete();

        $patient = Patient::create([
            'patient_code' => 'MCNH-2026-7782',
            'name' => 'Printable Card Patient',
            'date_of_birth' => '1987-07-07',
            'gender' => 'female',
            'phone' => '01719999886',
            'registration_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->receptionistUser)->get(route('portal.patients.print-card', $patient->id));

        $response->assertOk();
        $response->assertViewIs('portal.patients.print-card');
        $response->assertSee('Meghna Clinic');
        $response->assertSee('PATIENT INTAKE CARD');
        $response->assertSee('MCNH-2026-7782');
        $response->assertSee('Printable Card Patient');
    }

    /**
     * Test unauthorized users cannot manage or register patient master records.
     */
    public function test_unauthorized_users_cannot_access_or_modify_patient_master_records(): void
    {
        // 1. Unauthenticated user redirects to staff login
        $guestResponse = $this->get(route('portal.patients.index'));
        $guestResponse->assertRedirect(route('portal.login'));

        // 2. Patient portal user attempting staff intake
        $patientPortalResponse = $this->actingAs($this->patientUser)->get(route('portal.patients.create'));
        $patientPortalResponse->assertRedirect(route('patient.dashboard')); // Blocked by staff middleware
    }
}
