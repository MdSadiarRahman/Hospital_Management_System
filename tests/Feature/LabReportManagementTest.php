<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Consultation;
use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\LabPartner;
use App\Models\LabReport;
use App\Models\LabReportHistory;
use App\Models\LabSample;
use App\Models\LabTestCatalog;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use App\Services\LabReportService;
use Database\Seeders\DepartmentAndBedSeeder;
use Database\Seeders\LabTestCatalogSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LabReportManagementTest extends TestCase
{
    protected User $adminUser;
    protected User $doctorUser;
    protected User $labStaffUser;
    protected User $nurseUser;
    protected User $receptionistUser;
    protected User $patientUser;
    protected User $otherPatientUser;
    protected Patient $patient;
    protected Patient $otherPatient;
    protected Consultation $consultation;
    protected LabTestCatalog $testCbc;
    protected LabTestCatalog $testMri;
    protected LabPartner $labPartner;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(LabReportService::STORAGE_DISK);

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

        // Main Patient & User
        $this->patientUser = User::where('user_type', 'patient')->first()
            ?? User::factory()->create(['user_type' => 'patient']);

        $this->patient = Patient::where('user_id', $this->patientUser->id)->first() ?? Patient::create([
            'user_id' => $this->patientUser->id,
            'patient_code' => 'MCNH-' . date('Y') . '-' . rand(100000, 999999),
            'name' => 'Report Test Patient',
            'phone' => '01711' . rand(100000, 999999),
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'blood_group' => 'O+',
            'is_verified' => true,
        ]);

        // Other Patient & User (for privacy/access restriction tests)
        $this->otherPatientUser = User::factory()->create(['user_type' => 'patient']);
        $this->otherPatient = Patient::create([
            'user_id' => $this->otherPatientUser->id,
            'patient_code' => 'MCNH-' . date('Y') . '-' . rand(100000, 999999),
            'name' => 'Unrelated Patient',
            'phone' => '01722' . rand(100000, 999999),
            'gender' => 'female',
            'date_of_birth' => '1992-02-02',
            'blood_group' => 'A+',
            'is_verified' => true,
        ]);

        // Visit and consultation
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
            'chief_complaints' => 'Severe headache and fatigue',
        ]);

        // Lab tests
        $this->testCbc = LabTestCatalog::where('code', 'CBC01')->first() ?? LabTestCatalog::create([
            'name' => 'Complete Blood Count (CBC)',
            'code' => 'CBC01',
            'category' => 'hematology',
            'sample_type' => 'Whole Blood (EDTA)',
            'standard_charge' => 450.00,
            'is_active' => true,
        ]);

        $this->testMri = LabTestCatalog::where('code', 'MRI01')->first() ?? LabTestCatalog::create([
            'name' => 'MRI of Brain with Contrast',
            'code' => 'MRI01',
            'category' => 'radiology',
            'sample_type' => 'Radiological Scan',
            'standard_charge' => 7500.00,
            'default_source' => 'external',
            'is_active' => true,
        ]);

        // Lab partner
        $this->labPartner = LabPartner::first() ?? LabPartner::create([
            'name' => 'Ibn Sina Diagnostic & Reference Center, Dinajpur',
            'code' => 'IBN-SINA-DNP',
            'phone' => '01713000000',
            'is_active' => true,
        ]);
    }

    /**
     * Helper to create a fresh internal order and item.
     */
    protected function createInternalOrderItem(): LabOrderItem
    {
        $order = LabOrder::create([
            'order_number' => 'LAB-' . date('Ymd') . '-' . rand(100000, 999999),
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctorUser->id,
            'consultation_id' => $this->consultation->id,
            'source' => 'internal',
            'priority' => 'routine',
            'status' => 'processing',
            'total_charge' => 450.00,
        ]);

        return LabOrderItem::create([
            'lab_order_id' => $order->id,
            'lab_test_id' => $this->testCbc->id,
            'test_name' => $this->testCbc->name,
            'test_charge' => 450.00,
            'status' => 'processing',
        ]);
    }

    /**
     * Helper to create a fresh external outsourced order and item.
     */
    protected function createExternalOrderItem(): LabOrderItem
    {
        $order = LabOrder::create([
            'order_number' => 'LAB-' . date('Ymd') . '-' . rand(100000, 999999),
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctorUser->id,
            'consultation_id' => $this->consultation->id,
            'source' => 'external',
            'lab_partner_id' => $this->labPartner->id,
            'priority' => 'urgent',
            'status' => 'processing',
            'total_charge' => 7500.00,
        ]);

        return LabOrderItem::create([
            'lab_order_id' => $order->id,
            'lab_test_id' => $this->testMri->id,
            'test_name' => $this->testMri->name,
            'test_charge' => 7500.00,
            'status' => 'processing',
        ]);
    }

    /**
     * Test Internal Lab Workflow:
     * - Internal Sample Processing
     * - Result Entry
     * - Separate Report Received vs Report Reviewed status
     * - Result Review by Doctor
     */
    public function test_internal_lab_result_entry_and_doctor_review_workflow(): void
    {
        $orderItem = $this->createInternalOrderItem();

        // 1. Result Entry by Lab Technologist
        $resultPayload = [
            'findings_summary' => 'Blood picture shows mild hypochromic microcytic anemia, normal platelets.',
            'is_critical' => false,
            'parameters' => [
                [
                    'parameter' => 'Hemoglobin',
                    'value' => '10.5',
                    'unit' => 'g/dL',
                    'reference_range' => '13.0 - 17.0',
                    'flag' => 'low',
                ],
                [
                    'parameter' => 'Total WBC Count',
                    'value' => '7200',
                    'unit' => '/cumm',
                    'reference_range' => '4000 - 11000',
                    'flag' => 'normal',
                ],
                [
                    'parameter' => 'Platelet Count',
                    'value' => '250000',
                    'unit' => '/cumm',
                    'reference_range' => '150000 - 450000',
                    'flag' => 'normal',
                ],
            ],
        ];

        $response = $this->actingAs($this->labStaffUser)
            ->post(route('portal.lab-reports.store-internal', $orderItem->id), $resultPayload);

        $response->assertRedirect();

        $report = LabReport::where('lab_order_item_id', $orderItem->id)->first();
        $this->assertNotNull($report);
        $this->assertEquals('internal', $report->source);
        $this->assertEquals('received', $report->report_status); // Report Received
        $this->assertEquals('pending_review', $report->review_status); // Uploading/entering results must NOT automatically mark it reviewed
        $this->assertEquals($this->labStaffUser->id, $report->uploaded_by);
        $this->assertNotNull($report->uploaded_at);
        $this->assertNull($report->reviewed_by);
        $this->assertNull($report->reviewed_at);
        $this->assertCount(3, $report->parameters);

        // History recorded
        $this->assertDatabaseHas('lab_report_histories', [
            'lab_report_id' => $report->id,
            'action' => 'result_entered',
            'changed_by' => $this->labStaffUser->id,
            'new_status' => 'received',
        ]);

        // 2. Doctor Medical Review
        $reviewPayload = [
            'doctor_notes' => 'Mild iron deficiency pattern; initiate dietary iron and re-evaluate in 3 weeks.',
            'findings_summary' => $report->findings_summary,
            'is_critical' => false,
        ];

        $reviewResponse = $this->actingAs($this->doctorUser)
            ->post(route('portal.lab-reports.review', $report->id), $reviewPayload);

        $reviewResponse->assertRedirect();
        $report->refresh();
        $orderItem->refresh();

        $this->assertEquals('reviewed', $report->report_status);
        $this->assertEquals('reviewed', $report->review_status);
        $this->assertEquals($this->doctorUser->id, $report->reviewed_by);
        $this->assertNotNull($report->reviewed_at);
        $this->assertEquals('Mild iron deficiency pattern; initiate dietary iron and re-evaluate in 3 weeks.', $report->doctor_notes);
        $this->assertEquals('completed', $orderItem->status);

        // Lifecycle History recorded
        $this->assertDatabaseHas('lab_report_histories', [
            'lab_report_id' => $report->id,
            'action' => 'doctor_reviewed',
            'changed_by' => $this->doctorUser->id,
            'new_status' => 'reviewed',
        ]);
    }

    /**
     * Test External Lab Workflow:
     * - External Diagnostic Center Selection
     * - Referral/Outsource Tracking
     * - Secure Report PDF/Image Upload
     * - Stored outside publicly accessible locations
     * - Uploading does NOT automatically mark it reviewed
     * - Reviewing doctor records notes and marks reviewed
     */
    public function test_external_lab_report_upload_and_review_workflow(): void
    {
        $orderItem = $this->createExternalOrderItem();

        // Fake PDF file upload
        $fakePdf = UploadedFile::fake()->create('MRI_Brain_Scan.pdf', 1024, 'application/pdf');

        $uploadPayload = [
            'lab_partner_id' => $this->labPartner->id,
            'external_reference_number' => 'REF-POP-2026-9988',
            'findings_summary' => 'No focal brain lesion or intracranial hemorrhage detected. Ventricles are normal.',
            'is_critical' => false,
            'file' => $fakePdf,
        ];

        $response = $this->actingAs($this->labStaffUser)
            ->post(route('portal.lab-reports.store-external', $orderItem->id), $uploadPayload);

        $response->assertRedirect();

        $report = LabReport::where('lab_order_item_id', $orderItem->id)->first();
        $this->assertNotNull($report);
        $this->assertEquals('external', $report->source);
        $this->assertEquals($this->labPartner->id, $report->lab_partner_id);
        $this->assertEquals('REF-POP-2026-9988', $report->external_reference_number);
        $this->assertEquals('MRI_Brain_Scan.pdf', $report->original_file_name);
        $this->assertEquals('application/pdf', $report->file_type);

        // Security Requirement: Stored outside publicly accessible locations (private disk)
        $this->assertTrue(Storage::disk(LabReportService::STORAGE_DISK)->exists($report->file_path));

        // Requirement: Uploading a report must NOT automatically mark it medically reviewed
        $this->assertEquals('received', $report->report_status);
        $this->assertEquals('pending_review', $report->review_status);
        $this->assertEquals($this->labStaffUser->id, $report->uploaded_by);
        $this->assertNull($report->reviewed_by);

        // Doctor reviews external scan
        $reviewPayload = [
            'doctor_notes' => 'Brain MRI confirms benign findings; continue symptomatic headache management.',
        ];

        $reviewResponse = $this->actingAs($this->doctorUser)
            ->post(route('portal.lab-reports.review', $report->id), $reviewPayload);

        $reviewResponse->assertRedirect();
        $report->refresh();

        $this->assertEquals('reviewed', $report->report_status);
        $this->assertEquals('reviewed', $report->review_status);
        $this->assertEquals($this->doctorUser->id, $report->reviewed_by);
        $this->assertNotNull($report->reviewed_at);
        $this->assertStringContainsString('benign findings', $report->doctor_notes);
    }

    /**
     * Requirement: Validate file types and sizes.
     */
    public function test_external_upload_rejects_unsupported_file_types_and_oversized_files(): void
    {
        $orderItem = $this->createExternalOrderItem();

        // 1. Rejected File Type (e.g. .exe / executable or .txt)
        $invalidFile = UploadedFile::fake()->create('malicious.exe', 500, 'application/x-msdownload');
        $response = $this->actingAs($this->labStaffUser)
            ->post(route('portal.lab-reports.store-external', $orderItem->id), [
                'file' => $invalidFile,
                'lab_partner_id' => $this->labPartner->id,
            ]);

        $response->assertSessionHasErrors(['file']);

        // 2. Oversized File (> 10MB, e.g. 15MB = 15360 KB)
        $oversizedFile = UploadedFile::fake()->create('huge_scan.pdf', 15360, 'application/pdf');
        $response2 = $this->actingAs($this->labStaffUser)
            ->post(route('portal.lab-reports.store-external', $orderItem->id), [
                'file' => $oversizedFile,
                'lab_partner_id' => $this->labPartner->id,
            ]);

        $response2->assertSessionHasErrors(['file']);
    }

    /**
     * Requirement: Restrict report access (Non-public storage, only authorized users can access).
     */
    public function test_report_access_is_restricted_and_confidential(): void
    {
        $orderItem = $this->createExternalOrderItem();
        $fakeFile = UploadedFile::fake()->create('confidential_scan.pdf', 512, 'application/pdf');

        $report = LabReport::create([
            'lab_order_item_id' => $orderItem->id,
            'source' => 'external',
            'report_status' => 'received',
            'review_status' => 'pending_review',
            'uploaded_by' => $this->labStaffUser->id,
            'uploaded_at' => now(),
            'file_path' => $fakeFile->storeAs('reports/test', 'confidential.pdf', LabReportService::STORAGE_DISK),
            'original_file_name' => 'confidential_scan.pdf',
            'file_type' => 'application/pdf',
            'file_size' => 512,
        ]);

        // 1. Attending Doctor & Lab Staff can stream/download via portal
        $doctorResponse = $this->actingAs($this->doctorUser)
            ->get(route('portal.lab-reports.stream', $report->id));
        $doctorResponse->assertOk();

        $labStaffResponse = $this->actingAs($this->labStaffUser)
            ->get(route('portal.lab-reports.download', $report->id));
        $labStaffResponse->assertOk();

        // 2. Verified Patient who owns the report can download via patient portal
        $patientResponse = $this->actingAs($this->patientUser)
            ->get(route('patient.lab-reports.download', $report->id));
        $patientResponse->assertOk();

        // 3. Unrelated Patient attempting to access this report is Forbidden (403)
        $unrelatedPatientResponse = $this->actingAs($this->otherPatientUser)
            ->get(route('patient.lab-reports.download', $report->id));
        $unrelatedPatientResponse->assertForbidden();

        // 4. Patient attempting to access staff portal route is redirected
        $portalAccessResponse = $this->actingAs($this->otherPatientUser)
            ->get(route('portal.lab-reports.download', $report->id));
        $portalAccessResponse->assertRedirect(route('patient.dashboard'));

        // 5. Unauthenticated guest is redirected to login
        auth()->logout();
        $guestResponse = $this->get(route('portal.lab-reports.download', $report->id));
        $guestResponse->assertRedirect(route('portal.login'));
    }

    /**
     * Requirement: Printable diagnostic report slip viewable by authorized clinicians.
     */
    public function test_authorized_clinician_can_view_printable_clinical_report(): void
    {
        $orderItem = $this->createInternalOrderItem();

        $report = LabReport::create([
            'lab_order_item_id' => $orderItem->id,
            'source' => 'internal',
            'report_status' => 'reviewed',
            'review_status' => 'reviewed',
            'uploaded_by' => $this->labStaffUser->id,
            'uploaded_at' => now(),
            'reviewed_by' => $this->doctorUser->id,
            'reviewed_at' => now(),
            'doctor_notes' => 'Patient advised iron rich diet and follow up.',
            'parameters' => [
                ['parameter' => 'Hemoglobin', 'value' => '13.8', 'unit' => 'g/dL', 'reference_range' => '13.0 - 17.0', 'flag' => 'normal']
            ],
        ]);

        $response = $this->actingAs($this->doctorUser)
            ->get(route('portal.lab-reports.print', $report->id));

        $response->assertOk();
        $response->assertSee('MEGHNA CLINIC & NURSING HOME', false);
        $response->assertSee('Hemoglobin');
        $response->assertSee('13.8');
        $response->assertSee('Patient advised iron rich diet');
    }
}
