<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\AuditLog;
use App\Models\Bed;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Department;
use App\Models\Patient;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\DepartmentAndBedSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BillingFoundationTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;
    protected User $billingUser;
    protected User $receptionistUser;
    protected User $doctorUser;
    protected User $nurseUser;
    protected Patient $patient;
    protected ServiceCategory $consultationCategory;
    protected ServiceCategory $bedCategory;
    protected ServiceCategory $labCategory;

    protected function setUp(): void
    {
        parent::setUp();

        if (Department::count() === 0 || Bed::count() === 0) {
            $this->seed(RolePermissionSeeder::class);
            $this->seed(DepartmentAndBedSeeder::class);
            $this->seed(SystemSettingsSeeder::class);
            $this->seed(UserSeeder::class);
        }

        if (ServiceCategory::count() === 0) {
            $this->seed(ServiceCatalogSeeder::class);
        }

        $this->adminUser = User::whereHas('roles', fn($q) => $q->where('name', 'administrator'))->first()
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('administrator');

        $this->billingUser = User::whereHas('roles', fn($q) => $q->where('name', 'billing_staff'))->first()
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('billing_staff');

        $this->receptionistUser = User::whereHas('roles', fn($q) => $q->where('name', 'receptionist'))->first()
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('receptionist');

        $this->doctorUser = User::whereHas('roles', fn($q) => $q->where('name', 'doctor'))->first()
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('doctor');

        $this->nurseUser = User::whereHas('roles', fn($q) => $q->where('name', 'nurse'))->first()
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('nurse');

        $this->consultationCategory = ServiceCategory::firstOrCreate(
            ['code' => 'CONSULTATION'],
            ['name' => 'Consultation', 'is_active' => true]
        );

        $this->bedCategory = ServiceCategory::firstOrCreate(
            ['code' => 'BED_CHARGES'],
            ['name' => 'Bed Charges', 'is_active' => true]
        );

        $this->labCategory = ServiceCategory::firstOrCreate(
            ['code' => 'LAB_TESTS'],
            ['name' => 'Laboratory Tests', 'is_active' => true]
        );

        // Synthetic test patient
        $this->patient = Patient::create([
            'patient_code' => 'MCNH-' . date('Y') . '-' . rand(1000, 9999),
            'name' => 'Test Billing Patient ' . rand(100, 999),
            'date_of_birth' => '1988-06-15',
            'gender' => 'female',
            'phone' => '017' . rand(10000000, 99999999),
            'address' => 'Shohid Minar Mor, Dinajpur',
            'registration_date' => now()->toDateString(),
            'is_verified' => true,
        ]);
    }

    /**
     * Test 1: Service catalog and categories are seeded with correct structure.
     */
    public function test_service_categories_and_catalog_are_seeded_with_correct_structure(): void
    {
        $this->assertDatabaseHas('service_categories', ['code' => 'CONSULTATION']);
        $this->assertDatabaseHas('service_categories', ['code' => 'BED_CHARGES']);
        $this->assertDatabaseHas('service_categories', ['code' => 'LAB_TESTS']);
        $this->assertDatabaseHas('service_categories', ['code' => 'OPERATION_CHARGES']);
        $this->assertDatabaseHas('service_categories', ['code' => 'OTHER_APPROVED']);

        $service = Service::where('code', 'SRV-CONS-001')->first();
        $this->assertNotNull($service);
        $this->assertEquals('300.00', (string) $service->price);
        $this->assertTrue($service->is_active);
    }

    /**
     * Test 2: Authorized staff can view service catalog and register a new service.
     */
    public function test_authorized_staff_can_view_service_catalog_and_create_service(): void
    {
        $response = $this->actingAs($this->billingUser)
            ->get(route('portal.services.index'));

        $response->assertStatus(200);
        $response->assertSee('Service Catalog & Tariffs Master', false);

        $code = 'SRV-TEST-' . rand(100, 999);
        $createResponse = $this->actingAs($this->adminUser)
            ->post(route('portal.services.store'), [
                'category_id' => $this->consultationCategory->id,
                'name' => 'Pediatric Emergency Assessment',
                'code' => $code,
                'price' => '450.00',
                'description' => 'Urgent pediatric triage evaluation',
                'is_active' => 1,
            ]);

        $createResponse->assertRedirect(route('portal.services.index'));
        $createResponse->assertSessionHas('success');

        $this->assertDatabaseHas('services', [
            'code' => $code,
            'name' => 'Pediatric Emergency Assessment',
            'price' => '450.00',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'service.created',
            'auditable_type' => Service::class,
        ]);
    }

    /**
     * Test 3: Unauthorized user cannot create services or modify pricing.
     */
    public function test_unauthorized_user_cannot_create_or_modify_service_pricing(): void
    {
        $code = 'SRV-FORBIDDEN-' . rand(100, 999);

        // Nurse attempting to create a service
        $response = $this->actingAs($this->nurseUser)
            ->post(route('portal.services.store'), [
                'category_id' => $this->consultationCategory->id,
                'name' => 'Illegal Service',
                'code' => $code,
                'price' => '1000.00',
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('services', ['code' => $code]);
    }

    /**
     * Test 4: Modifying service price updates catalog and generates financial audit log.
     */
    public function test_price_change_updates_service_catalog_and_logs_audit_entry(): void
    {
        $service = Service::create([
            'category_id' => $this->bedCategory->id,
            'name' => 'Deluxe Suite ' . rand(100, 999),
            'code' => 'SRV-SUITE-' . rand(100, 999),
            'price' => '2000.00',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->put(route('portal.services.update', $service), [
                'price' => '2500.00',
            ]);

        $response->assertRedirect(route('portal.services.index'));
        $service->refresh();
        $this->assertEquals('2500.00', (string) $service->price);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'service.price_updated',
            'auditable_type' => Service::class,
            'auditable_id' => $service->id,
        ]);
    }

    /**
     * Test 5: Bill creation with multiple items calculates subtotal, total, and due with DECIMAL precision.
     */
    public function test_bill_creation_with_multiple_items_calculates_subtotal_and_total_accurately(): void
    {
        $srv1 = Service::where('code', 'SRV-CONS-002')->first() ?? Service::create([
            'category_id' => $this->consultationCategory->id,
            'name' => 'Specialist Consultation',
            'code' => 'SRV-CONS-002',
            'price' => '600.00',
        ]);

        $srv2 = Service::where('code', 'SRV-LAB-001')->first() ?? Service::create([
            'category_id' => $this->labCategory->id,
            'name' => 'Complete Blood Count (CBC)',
            'code' => 'SRV-LAB-001',
            'price' => '400.00',
        ]);

        $postData = [
            'patient_id' => $this->patient->id,
            'bill_date' => now()->toDateString(),
            'notes' => 'Post-op clinical checkup invoice',
            'items' => [
                [
                    'service_id' => $srv1->id,
                    'item_name' => $srv1->name,
                    'item_type' => 'consultation',
                    'unit_price' => '600.00',
                    'quantity' => 1,
                ],
                [
                    'service_id' => $srv2->id,
                    'item_name' => $srv2->name,
                    'item_type' => 'lab',
                    'unit_price' => '400.00',
                    'quantity' => 2, // 400 * 2 = 800.00
                ],
                [
                    'service_id' => null,
                    'item_name' => 'Emergency Nebulization Mask',
                    'item_type' => 'service',
                    'unit_price' => '150.00',
                    'quantity' => 1,
                ],
            ],
            'discount_amount' => '0.00',
        ];

        $response = $this->actingAs($this->billingUser)
            ->post(route('portal.billing.store'), $postData);

        $response->assertRedirect();

        $bill = Bill::where('patient_id', $this->patient->id)->latest('id')->first();
        $this->assertNotNull($bill);

        // Expected subtotal: 600.00 + (400.00 * 2) + 150.00 = 1550.00
        $this->assertEquals('1550.00', (string) $bill->subtotal_amount);
        $this->assertEquals('0.00', (string) $bill->discount_amount);
        $this->assertEquals('1550.00', (string) $bill->total_amount);
        $this->assertEquals('1550.00', (string) $bill->due_amount);
        $this->assertEquals('unpaid', $bill->status);
        $this->assertStringStartsWith('INV-' . date('Ymd') . '-', $bill->invoice_number);
        $this->assertCount(3, $bill->items);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'bill.created',
            'auditable_type' => Bill::class,
            'auditable_id' => $bill->id,
        ]);
    }

    /**
     * Test 6: Authorized discount reduces total and due amounts and records the authorizer.
     */
    public function test_authorized_discount_reduces_total_and_due_and_records_authorizer(): void
    {
        $srv = Service::first();

        $bill = Bill::create([
            'patient_id' => $this->patient->id,
            'invoice_number' => 'INV-' . date('Ymd') . '-' . rand(1000, 9999),
            'bill_date' => now(),
            'subtotal_amount' => '1000.00',
            'discount_amount' => '0.00',
            'total_amount' => '1000.00',
            'paid_amount' => '0.00',
            'due_amount' => '1000.00',
            'status' => 'unpaid',
            'created_by' => $this->billingUser->id,
        ]);

        BillItem::create([
            'bill_id' => $bill->id,
            'service_id' => $srv->id,
            'item_type' => 'service',
            'item_name' => $srv->name,
            'unit_price' => '1000.00',
            'quantity' => 1,
            'total_price' => '1000.00',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('portal.billing.discount.apply', $bill), [
                'discount_amount' => '150.00',
                'discount_reason' => 'Managing Director poverty alleviation concession',
            ]);

        $response->assertRedirect(route('portal.billing.show', $bill));
        $bill->refresh();

        $this->assertEquals('1000.00', (string) $bill->subtotal_amount);
        $this->assertEquals('150.00', (string) $bill->discount_amount);
        $this->assertEquals('850.00', (string) $bill->total_amount);
        $this->assertEquals('850.00', (string) $bill->due_amount);
        $this->assertEquals($this->adminUser->id, $bill->discount_authorized_by);
        $this->assertEquals('Managing Director poverty alleviation concession', $bill->discount_reason);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'bill.discount_applied',
            'auditable_type' => Bill::class,
            'auditable_id' => $bill->id,
        ]);
    }

    /**
     * Test 7: Unauthorized user cannot grant or approve discounts.
     */
    public function test_unauthorized_user_cannot_grant_or_approve_discount(): void
    {
        $bill = Bill::create([
            'patient_id' => $this->patient->id,
            'invoice_number' => 'INV-' . date('Ymd') . '-' . rand(1000, 9999),
            'bill_date' => now(),
            'subtotal_amount' => '500.00',
            'discount_amount' => '0.00',
            'total_amount' => '500.00',
            'paid_amount' => '0.00',
            'due_amount' => '500.00',
            'status' => 'unpaid',
            'created_by' => $this->billingUser->id,
        ]);

        // Nurse attempting to apply a discount
        $response = $this->actingAs($this->nurseUser)
            ->post(route('portal.billing.discount.apply', $bill), [
                'discount_amount' => '100.00',
                'discount_reason' => 'Unauthorized discount',
            ]);

        $response->assertStatus(403);
        $bill->refresh();
        $this->assertEquals('0.00', (string) $bill->discount_amount);
    }

    /**
     * Test 8: Historical bill item prices remain completely preserved when catalog price changes.
     */
    public function test_modifying_service_catalog_price_preserves_historical_bill_item_prices(): void
    {
        $service = Service::create([
            'category_id' => $this->consultationCategory->id,
            'name' => 'Historical Test Service ' . rand(100, 999),
            'code' => 'SRV-HIST-' . rand(100, 999),
            'price' => '500.00',
            'is_active' => true,
        ]);

        $bill = Bill::create([
            'patient_id' => $this->patient->id,
            'invoice_number' => 'INV-' . date('Ymd') . '-' . rand(1000, 9999),
            'bill_date' => now(),
            'subtotal_amount' => '500.00',
            'discount_amount' => '0.00',
            'total_amount' => '500.00',
            'paid_amount' => '0.00',
            'due_amount' => '500.00',
            'status' => 'unpaid',
            'created_by' => $this->billingUser->id,
        ]);

        $item = BillItem::create([
            'bill_id' => $bill->id,
            'service_id' => $service->id,
            'service_code' => $service->code,
            'item_type' => 'consultation',
            'item_name' => $service->name,
            'unit_price' => '500.00',
            'quantity' => 1,
            'total_price' => '500.00',
        ]);

        // Now, update catalog service price from 500.00 to 900.00
        $this->actingAs($this->adminUser)
            ->put(route('portal.services.update', $service), [
                'price' => '900.00',
            ]);

        $service->refresh();
        $this->assertEquals('900.00', (string) $service->price);

        // Historical bill item must remain UNCHANGED at 500.00
        $item->refresh();
        $this->assertEquals('500.00', (string) $item->unit_price);
        $this->assertEquals('500.00', (string) $item->total_price);

        $bill->refresh();
        $this->assertEquals('500.00', (string) $bill->subtotal_amount);
        $this->assertEquals('500.00', (string) $bill->total_amount);
    }

    /**
     * Test 9: Adding and removing bill items recalculates financial totals accurately.
     */
    public function test_adding_and_removing_bill_items_recalculates_totals_and_maintains_audit(): void
    {
        $bill = Bill::create([
            'patient_id' => $this->patient->id,
            'invoice_number' => 'INV-' . date('Ymd') . '-' . rand(1000, 9999),
            'bill_date' => now(),
            'subtotal_amount' => '0.00',
            'discount_amount' => '0.00',
            'total_amount' => '0.00',
            'paid_amount' => '0.00',
            'due_amount' => '0.00',
            'status' => 'unpaid',
            'created_by' => $this->billingUser->id,
        ]);

        // Add item 1: 300.00
        $this->actingAs($this->billingUser)
            ->post(route('portal.billing.items.add', $bill), [
                'item_name' => 'Wound Dressing Minor',
                'item_type' => 'service',
                'unit_price' => '300.00',
                'quantity' => 1,
            ]);

        $bill->refresh();
        $this->assertEquals('300.00', (string) $bill->subtotal_amount);
        $this->assertEquals('300.00', (string) $bill->total_amount);

        // Add item 2: 200.00 * 2 = 400.00
        $this->actingAs($this->billingUser)
            ->post(route('portal.billing.items.add', $bill), [
                'item_name' => 'Salbutamol Nebulization',
                'item_type' => 'service',
                'unit_price' => '200.00',
                'quantity' => 2,
            ]);

        $bill->refresh();
        $this->assertEquals('700.00', (string) $bill->subtotal_amount);
        $this->assertEquals('700.00', (string) $bill->total_amount);

        // Remove item 1
        $itemToRemove = $bill->items()->first();
        $this->actingAs($this->billingUser)
            ->delete(route('portal.billing.items.remove', [$bill, $itemToRemove]));

        $bill->refresh();
        $this->assertEquals('400.00', (string) $bill->subtotal_amount);
        $this->assertEquals('400.00', (string) $bill->total_amount);
    }

    /**
     * Test 10: Official printable invoice renders hospital details and financial breakdown.
     */
    public function test_printable_invoice_renders_with_hospital_details_and_financial_breakdown(): void
    {
        $bill = Bill::create([
            'patient_id' => $this->patient->id,
            'invoice_number' => 'INV-' . date('Ymd') . '-' . rand(1000, 9999),
            'bill_date' => now(),
            'subtotal_amount' => '1200.00',
            'discount_amount' => '200.00',
            'discount_reason' => 'Senior citizen waiver',
            'discount_authorized_by' => $this->adminUser->id,
            'total_amount' => '1000.00',
            'paid_amount' => '1000.00',
            'due_amount' => '0.00',
            'status' => 'paid',
            'created_by' => $this->billingUser->id,
        ]);

        BillItem::create([
            'bill_id' => $bill->id,
            'item_type' => 'consultation',
            'item_name' => 'Senior Surgeon Consultation',
            'unit_price' => '1200.00',
            'quantity' => 1,
            'total_price' => '1200.00',
        ]);

        $response = $this->actingAs($this->billingUser)
            ->get(route('portal.billing.print', $bill));

        $response->assertStatus(200);
        $response->assertSee('MEGHNA CLINIC & NURSING HOME', false);
        $response->assertSee('Shohid Minar Mor, Dinajpur', false);
        $response->assertSee($bill->invoice_number, false);
        $response->assertSee($this->patient->name, false);
        $response->assertSee('PAID IN FULL', false);
        $response->assertSee('1,200.00', false);
        $response->assertSee('200.00', false);
        $response->assertSee('1,000.00', false);
    }
}
