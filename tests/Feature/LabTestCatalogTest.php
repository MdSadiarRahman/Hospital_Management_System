<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\LabPartner;
use App\Models\LabTestCatalog;
use App\Models\User;
use Database\Seeders\DepartmentAndBedSeeder;
use Database\Seeders\LabTestCatalogSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Database\Seeders\UserSeeder;
use Tests\TestCase;

class LabTestCatalogTest extends TestCase
{
    protected User $adminUser;
    protected User $labStaffUser;
    protected User $doctorUser;
    protected User $nurseUser;
    protected User $receptionistUser;
    protected User $patientUser;

    protected function setUp(): void
    {
        parent::setUp();

        if (LabTestCatalog::count() === 0) {
            $this->seed(RolePermissionSeeder::class);
            $this->seed(DepartmentAndBedSeeder::class);
            $this->seed(SystemSettingsSeeder::class);
            $this->seed(UserSeeder::class);
            $this->seed(LabTestCatalogSeeder::class);
        }

        $this->adminUser = User::whereHas('roles', fn($q) => $q->where('name', 'administrator'))->first()
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('administrator');

        $this->labStaffUser = User::whereHas('roles', fn($q) => $q->where('name', 'lab_staff'))->first()
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('lab_staff');

        $this->doctorUser = User::whereHas('roles', fn($q) => $q->where('name', 'doctor'))->first()
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('doctor');

        $this->nurseUser = User::whereHas('roles', fn($q) => $q->where('name', 'nurse'))->first()
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('nurse');

        $this->receptionistUser = User::whereHas('roles', fn($q) => $q->where('name', 'receptionist'))->first()
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('receptionist');

        $this->patientUser = User::where('user_type', 'patient')->first()
            ?? User::factory()->create(['user_type' => 'patient']);
    }

    /**
     * Requirement: Configurable test catalog accessible to authorized staff.
     */
    public function test_authorized_staff_can_view_lab_test_catalog(): void
    {
        // 1. Lab staff access
        $labResponse = $this->actingAs($this->labStaffUser)
            ->get(route('portal.lab-catalog.index', ['q' => 'CBC01']));

        $labResponse->assertStatus(200);
        $labResponse->assertSee('Diagnostic Laboratory Test Catalog');
        $labResponse->assertSee('CBC01');
        $labResponse->assertSee('Complete Blood Count');
        $labResponse->assertSee('Hematology');
        $labResponse->assertSee('৳ 450.00');

        // 2. Administrator access
        $adminResponse = $this->actingAs($this->adminUser)
            ->get(route('portal.lab-catalog.index'));
        $adminResponse->assertStatus(200);

        // 3. Doctor access (to check tariffs and sample types)
        $doctorResponse = $this->actingAs($this->doctorUser)
            ->get(route('portal.lab-catalog.index'));
        $doctorResponse->assertStatus(200);
    }

    /**
     * Requirement: Creation form accessible to lab staff and admin.
     */
    public function test_lab_staff_and_admin_can_access_create_form(): void
    {
        $response = $this->actingAs($this->labStaffUser)
            ->get(route('portal.lab-catalog.create'));

        $response->assertStatus(200);
        $response->assertSee('Configure New Diagnostic Test');
        $response->assertSee('Unique Test Code');
        $response->assertSee('Standard Charge / Price');
    }

    /**
     * Requirement: Create test with all required fields (Name, Code, Category, Price, Sample Type, Turnaround, Source).
     */
    public function test_lab_staff_can_create_new_diagnostic_test(): void
    {
        $code = 'URIC' . rand(10, 99);
        $payload = [
            'name' => 'Serum Uric Acid Test',
            'code' => $code,
            'category' => 'Biochemistry',
            'description' => 'Photometric enzymatic uricase assay for hyperuricemia, gout, and renal calculi.',
            'standard_charge' => 350.00,
            'sample_type' => 'Serum',
            'turnaround_time' => '3 hours',
            'instructions' => 'Fasting preferred; avoid high purine foods 24 hours prior.',
            'default_source' => 'internal',
            'is_active' => 1,
        ];

        $response = $this->actingAs($this->labStaffUser)
            ->post(route('portal.lab-catalog.store'), $payload);

        $response->assertRedirect(route('portal.lab-catalog.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('lab_test_catalog', [
            'name' => 'Serum Uric Acid Test',
            'code' => $code,
            'category' => 'Biochemistry',
            'standard_charge' => '350.00',
            'sample_type' => 'Serum',
            'turnaround_time' => '3 hours',
            'default_source' => 'internal',
            'is_active' => 1,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->labStaffUser->id,
            'event' => 'lab_test.created',
        ]);
    }

    /**
     * Requirement: Avoid duplicate test codes.
     */
    public function test_validation_prevents_duplicate_test_codes(): void
    {
        // 'CBC01' already exists in seeded catalog
        $payload = [
            'name' => 'Duplicate CBC Test',
            'code' => 'CBC01', // Duplicate code!
            'category' => 'Hematology',
            'standard_charge' => 500.00,
            'sample_type' => 'Blood',
            'default_source' => 'internal',
        ];

        $response = $this->actingAs($this->labStaffUser)
            ->post(route('portal.lab-catalog.store'), $payload);

        $response->assertSessionHasErrors(['code']);
    }

    /**
     * Requirement: Input validation enforces mandatory fields and positive pricing.
     */
    public function test_validation_enforces_required_fields_and_valid_pricing(): void
    {
        $response = $this->actingAs($this->labStaffUser)
            ->post(route('portal.lab-catalog.store'), [
                'name' => '',
                'code' => '',
                'category' => '',
                'standard_charge' => -50.00, // Invalid negative price
                'default_source' => 'invalid_source',
            ]);

        $response->assertSessionHasErrors(['name', 'code', 'category', 'standard_charge', 'default_source']);
    }

    /**
     * Requirement: View diagnostic test specifications.
     */
    public function test_staff_can_view_test_specifications(): void
    {
        $test = LabTestCatalog::where('code', 'CBC01')->firstOrFail();

        $response = $this->actingAs($this->labStaffUser)
            ->get(route('portal.lab-catalog.show', $test->id));

        $response->assertStatus(200);
        $response->assertSee('Complete Blood Count (CBC) with ESR');
        $response->assertSee('CBC01');
        $response->assertSee('৳ 450.00');
        $response->assertSee('Whole Blood (EDTA)');
        $response->assertSee('2 hours');
    }

    /**
     * Requirement: Keep pricing and configurations editable with unique code enforcement.
     */
    public function test_staff_can_edit_and_update_test_configuration(): void
    {
        $test = LabTestCatalog::where('code', 'RBS01')->firstOrFail();

        // 1. View edit form
        $editFormResponse = $this->actingAs($this->labStaffUser)
            ->get(route('portal.lab-catalog.edit', $test->id));
        $editFormResponse->assertStatus(200);

        // 2. Submit update with adjusted price and instructions
        $updateResponse = $this->actingAs($this->labStaffUser)
            ->put(route('portal.lab-catalog.update', $test->id), [
                'name' => 'Random Blood Sugar (RBS) Updated',
                'code' => 'RBS01', // Keeps same code (must pass unique ignore rule)
                'category' => 'Biochemistry',
                'description' => 'Updated clinical description for glucose test.',
                'standard_charge' => 150.00, // Price updated from 120 to 150
                'sample_type' => 'Fluoride Plasma',
                'turnaround_time' => '45 mins',
                'default_source' => 'internal',
                'is_active' => 1,
            ]);

        $updateResponse->assertRedirect(route('portal.lab-catalog.show', $test->id));
        $updateResponse->assertSessionHas('success');

        $test->refresh();
        $this->assertEquals('Random Blood Sugar (RBS) Updated', $test->name);
        $this->assertEquals('150.00', $test->standard_charge);
        $this->assertEquals('45 mins', $test->turnaround_time);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->labStaffUser->id,
            'event' => 'lab_test.updated',
            'auditable_id' => $test->id,
        ]);
    }

    /**
     * Requirement: Active/Inactive Status toggling.
     */
    public function test_staff_can_toggle_test_active_status(): void
    {
        $test = LabTestCatalog::where('code', 'URINE01')->firstOrFail();
        $this->assertTrue($test->is_active);

        // Deactivate test
        $response = $this->actingAs($this->labStaffUser)
            ->post(route('portal.lab-catalog.toggle-status', $test->id));

        $response->assertRedirect();
        $test->refresh();
        $this->assertFalse($test->is_active);

        // Re-activate test
        $response2 = $this->actingAs($this->labStaffUser)
            ->post(route('portal.lab-catalog.toggle-status', $test->id));

        $response2->assertRedirect();
        $test->refresh();
        $this->assertTrue($test->is_active);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->labStaffUser->id,
            'event' => 'lab_test.status_toggled',
            'auditable_id' => $test->id,
        ]);
    }

    /**
     * Requirement: Search and category filters.
     */
    public function test_catalog_search_and_filters_functionality(): void
    {
        // 1. Search by keyword
        $searchResponse = $this->actingAs($this->labStaffUser)
            ->get(route('portal.lab-catalog.index', ['q' => 'Creatinine']));

        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('CREAT01');
        $searchResponse->assertDontSee('CBC01');

        // 2. Filter by category
        $categoryResponse = $this->actingAs($this->labStaffUser)
            ->get(route('portal.lab-catalog.index', ['category' => 'Radiology & Imaging']));

        $categoryResponse->assertStatus(200);
        $categoryResponse->assertSee('CXR01');
        $categoryResponse->assertDontSee('CBC01');

        // 3. Filter by external source
        $sourceResponse = $this->actingAs($this->labStaffUser)
            ->get(route('portal.lab-catalog.index', ['source' => 'external']));

        $sourceResponse->assertStatus(200);
        $sourceResponse->assertSee('HISTO01');
    }

    /**
     * Requirement: Auto-complete search API for order creation.
     */
    public function test_api_search_returns_json_results(): void
    {
        $response = $this->actingAs($this->doctorUser)
            ->getJson(route('portal.api.lab-catalog.search', ['q' => 'Blood']));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'results' => [
                '*' => ['id', 'name', 'code', 'category', 'price', 'formatted_price', 'sample_type', 'source'],
            ],
        ]);
    }

    /**
     * Requirement: Support future laboratory expansion with outsourced partner reference labs.
     */
    public function test_support_partner_reference_laboratory_outsourcing(): void
    {
        $partner = LabPartner::firstOrCreate(
            ['code' => 'EXP-DNP-01'],
            [
                'name' => 'Apex Specialized Reference Lab',
                'phone' => '01799887766',
                'is_active' => true,
            ]
        );

        $tbCode = 'TBPCR_' . rand(100, 999);
        $payload = [
            'name' => 'PCR Gene Expert Tuberculosis Panel',
            'code' => $tbCode,
            'category' => 'Microbiology & Serology',
            'standard_charge' => 3200.00,
            'sample_type' => 'Sputum',
            'turnaround_time' => '48 hours',
            'default_source' => 'external',
            'lab_partner_id' => $partner->id,
            'is_active' => 1,
        ];

        $response = $this->actingAs($this->labStaffUser)
            ->post(route('portal.lab-catalog.store'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('lab_test_catalog', [
            'code' => $tbCode,
            'lab_partner_id' => $partner->id,
            'default_source' => 'external',
        ]);
    }

    /**
     * Requirement: Apply appropriate staff permissions.
     */
    public function test_unauthorized_users_are_denied_access(): void
    {
        // 1. Unauthenticated guest -> redirects to login
        $guestResponse = $this->get(route('portal.lab-catalog.index'));
        $guestResponse->assertRedirect(route('portal.login'));

        // 2. Patient user -> denied access to staff portal
        $patientResponse = $this->actingAs($this->patientUser)
            ->get(route('portal.lab-catalog.index'));
        $patientResponse->assertRedirect(route('patient.dashboard'));

        // 3. Nurse attempting to create a test -> 403 Forbidden
        $nurseCreate = $this->actingAs($this->nurseUser)
            ->get(route('portal.lab-catalog.create'));
        $nurseCreate->assertStatus(403);

        // 4. Receptionist attempting to edit a test -> 403 Forbidden
        $test = LabTestCatalog::first();
        $receptionistEdit = $this->actingAs($this->receptionistUser)
            ->get(route('portal.lab-catalog.edit', $test->id));
        $receptionistEdit->assertStatus(403);
    }
}
