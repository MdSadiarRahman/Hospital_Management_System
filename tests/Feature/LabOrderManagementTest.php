<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Consultation;
use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\LabOrderStatusHistory;
use App\Models\LabPartner;
use App\Models\LabSample;
use App\Models\LabTestCatalog;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\DepartmentAndBedSeeder;
use Database\Seeders\LabTestCatalogSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Database\Seeders\UserSeeder;
use Tests\TestCase;

class LabOrderManagementTest extends TestCase
{
    protected User $adminUser;
    protected User $doctorUser;
    protected User $labStaffUser;
    protected User $nurseUser;
    protected User $receptionistUser;
    protected User $patientUser;
    protected Patient $patient;
    protected Consultation $consultation;
    protected LabTestCatalog $testCbc;
    protected LabTestCatalog $testUrine;
    protected LabTestCatalog $testSgot;
    protected LabPartner $labPartner;

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

        $this->doctorUser = User::whereHas('roles', fn($q) => $q->where('name', 'doctor'))->first()
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('doctor');

        $this->labStaffUser = User::whereHas('roles', fn($q) => $q->where('name', 'lab_staff'))->first()
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('lab_staff');

        $this->nurseUser = User::whereHas('roles', fn($q) => $q->where('name', 'nurse'))->first()
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('nurse');

        $this->receptionistUser = User::whereHas('roles', fn($q) => $q->where('name', 'receptionist'))->first()
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('receptionist');

        $this->patientUser = User::where('user_type', 'patient')->first()
            ?? User::factory()->create(['user_type' => 'patient']);

        // Create or fetch sample patient
        $this->patient = Patient::first() ?? Patient::create([
            'patient_code' => 'MCNH-' . date('Y') . '-' . rand(100000, 999999),
            'name' => 'Synthetic Test Patient',
            'phone' => '01711' . rand(100000, 999999),
            'gender' => 'female',
            'dob' => '1995-05-15',
            'blood_group' => 'B+',
            'is_verified' => true,
        ]);

        // Ensure patient has a linked active visit & consultation
        $visit = Visit::where('patient_id', $this->patient->id)->first() ?? Visit::create([
            'visit_number' => 'VST-' . date('Ymd') . '-' . rand(1000, 9999),
            'patient_id' => $this->patient->id,
            'visit_type' => 'regular',
            'visit_date' => now(),
            'status' => 'in_consultation',
        ]);

        $this->consultation = Consultation::where('visit_id', $visit->id)->first() ?? Consultation::create([
            'visit_id' => $visit->id,
            'doctor_id' => $this->doctorUser->id,
            'status' => 'finalized',
            'chief_complaints' => 'Fever and malaise for 4 days',
        ]);

        // Fetch or create sample tests
        $this->testCbc = LabTestCatalog::where('code', 'CBC01')->first() ?? LabTestCatalog::create([
            'name' => 'Complete Blood Count (CBC)',
            'code' => 'CBC01',
            'category' => 'hematology',
            'sample_type' => 'Whole Blood (EDTA)',
            'standard_charge' => 450.00,
            'is_active' => true,
        ]);

        $this->testUrine = LabTestCatalog::where('code', 'RME01')->first() ?? LabTestCatalog::create([
            'name' => 'Urine Routine & Microscopic Exam',
            'code' => 'RME01',
            'category' => 'clinical_pathology',
            'sample_type' => 'Midstream Urine',
            'standard_charge' => 250.00,
            'is_active' => true,
        ]);

        $this->testSgot = LabTestCatalog::where('code', 'SGOT01')->first() ?? LabTestCatalog::create([
            'name' => 'SGOT / AST (Liver Function)',
            'code' => 'SGOT01',
            'category' => 'biochemistry',
            'sample_type' => 'Serum',
            'standard_charge' => 350.00,
            'is_active' => true,
        ]);

        $this->labPartner = LabPartner::first() ?? LabPartner::create([
            'name' => 'Popular Diagnostic Dinajpur',
            'code' => 'POPULAR-DNP',
            'phone' => '01712000000',
            'is_active' => true,
        ]);
    }

    /**
     * Requirement: Doctor can request multiple tests per order linked to patient and consultation.
     */
    public function test_doctor_can_create_lab_order_with_multiple_tests(): void
    {
        $testPatient = Patient::create([
            'patient_code' => 'MCNH-' . date('Y') . '-' . rand(100000, 999999),
            'name' => 'Specific Order Test Patient ' . rand(100, 999),
            'phone' => '01711' . rand(100000, 999999),
            'gender' => 'female',
            'date_of_birth' => '1995-05-15',
            'is_verified' => true,
        ]);

        $testVisit = Visit::create([
            'visit_number' => 'VST-' . date('Ymd') . '-' . rand(1000, 9999),
            'patient_id' => $testPatient->id,
            'visit_type' => 'regular',
            'visit_date' => now(),
            'status' => 'in_consultation',
        ]);

        $testConsultation = Consultation::create([
            'visit_id' => $testVisit->id,
            'doctor_id' => $this->doctorUser->id,
            'status' => 'finalized',
            'chief_complaints' => 'Fever and malaise for 4 days',
        ]);

        $payload = [
            'patient_id' => $testPatient->id,
            'consultation_id' => $testConsultation->id,
            'priority' => 'urgent',
            'source' => 'internal',
            'notes' => 'Patient has persistent high-grade fever, investigate leukocytosis and UTI.',
            'tests' => [
                $this->testCbc->id,
                $this->testUrine->id,
            ],
        ];

        $response = $this->actingAs($this->doctorUser)
            ->post(route('portal.lab-orders.store'), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        
        $order = LabOrder::where('patient_id', $testPatient->id)->latest('id')->first();
        $this->assertNotNull($order);
        $this->assertStringStartsWith('LAB-', $order->order_number);
        $this->assertEquals($this->doctorUser->id, $order->doctor_id);
        $this->assertEquals($testConsultation->id, $order->consultation_id);
        $this->assertEquals('urgent', $order->priority);
        $this->assertEquals('internal', $order->source);
        $this->assertEquals('pending', $order->status);
        $this->assertEquals(700.00, (float) $order->total_charge); // 450 + 250

        // Multiple tests created
        $this->assertCount(2, $order->items);
        $this->assertTrue($order->items->contains(fn($item) => str_contains($item->test_name, 'Complete Blood Count')));
        $this->assertTrue($order->items->contains(fn($item) => str_contains($item->test_name, 'Urine Routine')));

        // Initial status history logged
        $this->assertDatabaseHas('lab_order_status_histories', [
            'lab_order_id' => $order->id,
            'to_status' => 'pending',
            'changed_by' => $this->doctorUser->id,
        ]);
    }

    /**
     * Requirement: Link test orders to consultation and patient with validation.
     */
    public function test_order_creation_requires_valid_patient_and_at_least_one_test(): void
    {
        $response = $this->actingAs($this->doctorUser)
            ->post(route('portal.lab-orders.store'), [
                'patient_id' => 99999999, // Non-existent
                'priority' => 'routine',
                'source' => 'internal',
                'tests' => [], // Empty tests
            ]);

        $response->assertSessionHasErrors(['patient_id', 'tests']);
    }

    /**
     * Requirement: Sample collection, sample identifier (barcode), and collection date traceability.
     */
    public function test_phlebotomy_sample_collection_records_barcode_and_collection_date(): void
    {
        // Create an order first
        $order = LabOrder::create([
            'order_number' => 'LAB-' . date('Ymd') . '-' . rand(100000, 999999),
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctorUser->id,
            'consultation_id' => $this->consultation->id,
            'source' => 'internal',
            'priority' => 'stat',
            'status' => 'pending',
            'total_charge' => 450.00,
        ]);

        $item = LabOrderItem::create([
            'lab_order_id' => $order->id,
            'lab_test_id' => $this->testCbc->id,
            'test_name' => $this->testCbc->name,
            'test_charge' => $this->testCbc->standard_charge,
            'status' => 'pending',
        ]);

        $collectionTime = now()->subMinutes(15)->format('Y-m-d H:i:s');
        $samplePayload = [
            'sample_type' => 'Whole Blood (EDTA)',
            'collected_at' => $collectionTime,
            'notes' => 'Venipuncture performed on right arm, 3ml collected.',
        ];

        $response = $this->actingAs($this->labStaffUser)
            ->post(route('portal.lab-orders.samples.store', $order->id), $samplePayload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // Sample created with traceability
        $sample = LabSample::where('lab_order_id', $order->id)->first();
        $this->assertNotNull($sample);
        $this->assertStringStartsWith('SMP-', $sample->barcode);
        $this->assertEquals('Whole Blood (EDTA)', $sample->sample_type);
        $this->assertEquals($this->labStaffUser->id, $sample->collected_by);
        $this->assertEquals('collected', $sample->status);

        // Order and item status progressed
        $order->refresh();
        $item->refresh();
        $this->assertEquals('sample_collected', $order->status);
        $this->assertEquals('sample_collected', $item->status);
        $this->assertEquals($sample->id, $item->lab_sample_id);

        // Status history recorded
        $this->assertDatabaseHas('lab_order_status_histories', [
            'lab_order_id' => $order->id,
            'from_status' => 'pending',
            'to_status' => 'sample_collected',
            'changed_by' => $this->labStaffUser->id,
        ]);
    }

    /**
     * Requirement: Track each test separately and auto-complete order when all tests finish.
     */
    public function test_each_test_can_be_tracked_separately_within_an_order(): void
    {
        $order = LabOrder::create([
            'order_number' => 'LAB-' . date('Ymd') . '-' . rand(100000, 999999),
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctorUser->id,
            'consultation_id' => $this->consultation->id,
            'source' => 'internal',
            'priority' => 'routine',
            'status' => 'processing',
            'total_charge' => 700.00,
        ]);

        $item1 = LabOrderItem::create([
            'lab_order_id' => $order->id,
            'lab_test_id' => $this->testCbc->id,
            'test_name' => $this->testCbc->name,
            'test_charge' => 450.00,
            'status' => 'processing',
        ]);

        $item2 = LabOrderItem::create([
            'lab_order_id' => $order->id,
            'lab_test_id' => $this->testUrine->id,
            'test_name' => $this->testUrine->name,
            'test_charge' => 250.00,
            'status' => 'processing',
        ]);

        // Complete test 1 first
        $response1 = $this->actingAs($this->labStaffUser)
            ->post(route('portal.lab-orders.items.update-status', $item1->id), [
                'status' => 'completed',
                'notes' => 'CBC analyzer result verified by technologist.',
            ]);

        $response1->assertRedirect();
        $item1->refresh();
        $item2->refresh();
        $order->refresh();

        $this->assertEquals('completed', $item1->status);
        $this->assertEquals('processing', $item2->status);
        // Order remains in processing because item 2 is not completed yet
        $this->assertEquals('processing', $order->status);

        // Complete test 2
        $response2 = $this->actingAs($this->labStaffUser)
            ->post(route('portal.lab-orders.items.update-status', $item2->id), [
                'status' => 'completed',
                'notes' => 'Urine routine examination microscopy completed.',
            ]);

        $response2->assertRedirect();
        $item2->refresh();
        $order->refresh();

        $this->assertEquals('completed', $item2->status);
        // Now all items completed -> parent order automatically transitions to completed
        $this->assertEquals('completed', $order->status);

        // Status history recorded
        $this->assertDatabaseHas('lab_order_status_histories', [
            'lab_order_id' => $order->id,
            'from_status' => 'processing',
            'to_status' => 'completed',
        ]);
    }

    /**
     * Requirement: External laboratory assignment for outsourced diagnostic tests.
     */
    public function test_external_laboratory_assignment(): void
    {
        $order = LabOrder::create([
            'order_number' => 'LAB-' . date('Ymd') . '-' . rand(100000, 999999),
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctorUser->id,
            'consultation_id' => $this->consultation->id,
            'source' => 'external',
            'lab_partner_id' => null,
            'priority' => 'routine',
            'status' => 'pending',
            'total_charge' => 1200.00,
        ]);

        $response = $this->actingAs($this->labStaffUser)
            ->post(route('portal.lab-orders.assign-partner', $order->id), [
                'lab_partner_id' => $this->labPartner->id,
                'notes' => 'Dispatched to reference lab via courier service.',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $order->refresh();

        $this->assertEquals($this->labPartner->id, $order->lab_partner_id);
        $this->assertEquals($this->labPartner->name, $order->labPartner?->name);

        // Status history tracks assignment notes
        $latestHistory = $order->statusHistories()->latest('id')->first();
        $this->assertNotNull($latestHistory);
        $this->assertStringContainsString($this->labPartner->name, $latestHistory->notes);
    }

    /**
     * Requirement: Prevent unauthorized result and order modifications.
     */
    public function test_unauthorized_user_cannot_create_or_modify_lab_orders(): void
    {
        // Patient cannot access staff portal to create lab orders
        $patientResponse = $this->actingAs($this->patientUser)
            ->post(route('portal.lab-orders.store'), [
                'patient_id' => $this->patient->id,
                'consultation_id' => $this->consultation->id,
                'priority' => 'routine',
                'source' => 'internal',
                'tests' => [$this->testCbc->id],
            ]);

        $patientResponse->assertRedirect(route('patient.dashboard'));

        // Unauthorized staff (receptionist without lab.create permission) cannot author test orders
        $staffResponse = $this->actingAs($this->receptionistUser)
            ->post(route('portal.lab-orders.store'), [
                'patient_id' => $this->patient->id,
                'consultation_id' => $this->consultation->id,
                'priority' => 'routine',
                'source' => 'internal',
                'tests' => [$this->testCbc->id],
            ]);

        $staffResponse->assertForbidden();

        // Completed orders cannot be modified arbitrarily
        $completedOrder = LabOrder::create([
            'order_number' => 'LAB-' . date('Ymd') . '-' . rand(100000, 999999),
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctorUser->id,
            'consultation_id' => $this->consultation->id,
            'source' => 'internal',
            'priority' => 'routine',
            'status' => 'completed',
            'total_charge' => 450.00,
        ]);

        $editResponse = $this->actingAs($this->labStaffUser)
            ->post(route('portal.lab-orders.update-status', $completedOrder->id), [
                'status' => 'pending',
                'notes' => 'Attempting to reopen finalized order',
            ]);

        $editResponse->assertSessionHas('error');
        $completedOrder->refresh();
        $this->assertEquals('completed', $completedOrder->status);
    }

    /**
     * Requirement: Printable requisition slip can be viewed by authorized clinical staff.
     */
    public function test_authorized_staff_can_view_printable_requisition_slip(): void
    {
        $order = LabOrder::create([
            'order_number' => 'LAB-' . date('Ymd') . '-' . rand(100000, 999999),
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctorUser->id,
            'consultation_id' => $this->consultation->id,
            'source' => 'internal',
            'priority' => 'stat',
            'status' => 'pending',
            'total_charge' => 450.00,
        ]);

        LabOrderItem::create([
            'lab_order_id' => $order->id,
            'lab_test_id' => $this->testCbc->id,
            'test_name' => $this->testCbc->name,
            'test_charge' => 450.00,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->doctorUser)
            ->get(route('portal.lab-orders.print-slip', $order->id));

        $response->assertOk();
        $response->assertSee('MEGHNA CLINIC & NURSING HOME', false);
        $response->assertSee($order->order_number);
        $response->assertSee('Complete Blood Count');
    }
}
