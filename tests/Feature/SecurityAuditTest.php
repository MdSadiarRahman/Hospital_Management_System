<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Bed;
use App\Models\Bill;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\LabReport;
use App\Models\LabTestCatalog;
use App\Models\Patient;
use App\Models\PatientContact;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\Role;
use App\Models\SystemNotification;
use App\Models\User;
use App\Models\Visit;
use App\Services\PaymentService;
use Database\Seeders\DepartmentAndBedSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityAuditTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;
    protected User $doctorUser;
    protected User $nurseUser;
    protected User $receptionistUser;
    protected User $billingUser;

    protected User $patientUserA;
    protected Patient $patientA;

    protected User $patientUserB;
    protected Patient $patientB;

    protected User $unlinkedPatientUser;

    protected Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        if (Department::count() === 0 || Bed::count() === 0) {
            $this->seed(RolePermissionSeeder::class);
            $this->seed(DepartmentAndBedSeeder::class);
            $this->seed(SystemSettingsSeeder::class);
            $this->seed(UserSeeder::class);
        }

        $this->adminUser = User::where('email', 'admin@meghnaclinic.com')->firstOrFail();
        $this->doctorUser = User::where('email', 'doctor@meghnaclinic.com')->firstOrFail();
        $this->nurseUser = User::where('email', 'nurse@meghnaclinic.com')->firstOrFail();
        $this->receptionistUser = User::where('email', 'reception@meghnaclinic.com')->firstOrFail();
        $this->billingUser = User::where('email', 'billing@meghnaclinic.com')->firstOrFail();

        $this->department = Department::first();

        // Create Patient A with linked User
        $this->patientUserA = User::create([
            'name' => 'Security Test Patient A',
            'email' => 'pat_a_' . uniqid() . '@example.com',
            'phone' => '017' . rand(10000000, 99999999),
            'password' => Hash::make('Password@123!'),
            'user_type' => 'patient',
            'is_active' => true,
        ]);
        $patientRole = Role::where('name', 'patient')->first();
        if ($patientRole) {
            $this->patientUserA->roles()->syncWithoutDetaching([$patientRole->id]);
        }

        $this->patientA = Patient::create([
            'user_id' => $this->patientUserA->id,
            'patient_code' => 'MCNH-2026-' . rand(80000, 89999),
            'name' => 'Security Test Patient A',
            'phone' => $this->patientUserA->phone,
            'date_of_birth' => '1990-05-15',
            'gender' => 'male',
            'blood_group' => 'A+',
            'address' => 'Shohid Minar Mor, Dinajpur',
            'is_verified' => true,
            'verified_by' => $this->adminUser->id,
            'verified_at' => now(),
        ]);

        // Create Patient B with linked User
        $this->patientUserB = User::create([
            'name' => 'Security Test Patient B',
            'email' => 'pat_b_' . uniqid() . '@example.com',
            'phone' => '018' . rand(10000000, 99999999),
            'password' => Hash::make('Password@123!'),
            'user_type' => 'patient',
            'is_active' => true,
        ]);
        if ($patientRole) {
            $this->patientUserB->roles()->syncWithoutDetaching([$patientRole->id]);
        }

        $this->patientB = Patient::create([
            'user_id' => $this->patientUserB->id,
            'patient_code' => 'MCNH-2026-' . rand(90000, 99999),
            'name' => 'Security Test Patient B',
            'phone' => $this->patientUserB->phone,
            'date_of_birth' => '1995-08-20',
            'gender' => 'female',
            'blood_group' => 'B+',
            'address' => 'Goneshtola, Dinajpur',
            'is_verified' => true,
            'verified_by' => $this->adminUser->id,
            'verified_at' => now(),
        ]);

        // Create an unlinked self-registered patient
        $this->unlinkedPatientUser = User::create([
            'name' => 'Unverified Self-Registered Patient',
            'email' => 'unlinked_' . uniqid() . '@example.com',
            'phone' => '019' . rand(10000000, 99999999),
            'password' => Hash::make('Password@123!'),
            'user_type' => 'patient',
            'is_active' => true,
        ]);
        if ($patientRole) {
            $this->unlinkedPatientUser->roles()->syncWithoutDetaching([$patientRole->id]);
        }
    }

    // =========================================================================
    // 1. HORIZONTAL PRIVILEGE ESCALATION / IDOR TESTS
    // =========================================================================

    /**
     * Patient A cannot view Patient B's prescription.
     */
    public function test_idor_patient_cannot_view_another_patients_prescription(): void
    {
        $visitB = Visit::create([
            'patient_id' => $this->patientB->id,
            'visit_number' => 'VST-2026-' . rand(10000, 99999),
            'visit_type' => 'regular',
            'visit_date' => now(),
            'triage_level' => 'normal',
            'status' => 'completed',
        ]);

        $consultationB = Consultation::create([
            'visit_id' => $visitB->id,
            'doctor_id' => $this->doctorUser->id,
            'chief_complaints' => 'Confidential clinical complaints for Patient B',
            'clinical_notes' => 'Confidential clinical notes for Patient B',
            'status' => 'finalized',
            'finalized_at' => now(),
        ]);

        $prescriptionB = Prescription::create([
            'consultation_id' => $consultationB->id,
            'prescription_number' => 'RX-2026-' . rand(10000, 99999),
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $this->doctorUser->id,
        ]);

        // Patient A attempts to view Patient B's prescription
        $response = $this->actingAs($this->patientUserA)
            ->get("/patient/prescriptions/{$prescriptionB->id}");

        $response->assertStatus(403);
    }

    /**
     * Patient A cannot download or stream Patient B's diagnostic lab report.
     */
    public function test_idor_patient_cannot_download_another_patients_lab_report(): void
    {
        $visitB = Visit::create([
            'patient_id' => $this->patientB->id,
            'visit_number' => 'VST-2026-' . rand(10000, 99999),
            'visit_type' => 'regular',
            'visit_date' => now(),
            'triage_level' => 'normal',
            'status' => 'completed',
        ]);

        $consultationB = Consultation::create([
            'visit_id' => $visitB->id,
            'doctor_id' => $this->doctorUser->id,
            'chief_complaints' => 'Diagnostic workup for Patient B',
            'status' => 'finalized',
            'finalized_at' => now(),
        ]);

        $labTest = LabTestCatalog::first() ?? LabTestCatalog::create([
            'name' => 'Complete Blood Count',
            'code' => 'CBC-' . rand(100, 999),
            'category' => 'Hematology',
            'cost' => 500.00,
            'is_active' => true,
        ]);

        $labOrderB = LabOrder::create([
            'patient_id' => $this->patientB->id,
            'consultation_id' => $consultationB->id,
            'order_number' => 'LAB-2026-' . rand(10000, 99999),
            'status' => 'completed',
        ]);

        $itemB = LabOrderItem::create([
            'lab_order_id' => $labOrderB->id,
            'lab_test_id' => $labTest->id,
            'test_name' => $labTest->name,
            'status' => 'completed',
        ]);

        $reportB = LabReport::create([
            'lab_order_item_id' => $itemB->id,
            'report_status' => 'received',
            'reviewed_by' => $this->doctorUser->id,
            'reviewed_at' => now(),
            'is_critical' => false,
        ]);

        // Patient A attempts to download Patient B's lab report
        $response = $this->actingAs($this->patientUserA)
            ->get("/patient/lab-reports/{$reportB->id}/download");

        $response->assertStatus(403);
    }

    /**
     * Patient A cannot print or view Patient B's appointment slip.
     */
    public function test_idor_patient_cannot_print_another_patients_appointment_slip(): void
    {
        $appointmentB = Appointment::create([
            'patient_id' => $this->patientB->id,
            'doctor_id' => $this->doctorUser->id,
            'department_id' => $this->department->id,
            'appointment_date' => now()->addDays(2),
            'type' => 'online',
            'status' => 'confirmed',
        ]);

        // Patient A attempts to print Patient B's appointment slip
        $response = $this->actingAs($this->patientUserA)
            ->get("/patient/appointments/{$appointmentB->id}/print-slip");

        $response->assertStatus(403);
    }

    /**
     * Patient A cannot reschedule Patient B's appointment.
     */
    public function test_idor_patient_cannot_reschedule_another_patients_appointment(): void
    {
        $appointmentB = Appointment::create([
            'patient_id' => $this->patientB->id,
            'doctor_id' => $this->doctorUser->id,
            'department_id' => $this->department->id,
            'appointment_date' => now()->addDays(2),
            'type' => 'online',
            'status' => 'pending',
        ]);

        // Patient A attempts to reschedule Patient B's appointment
        $response = $this->actingAs($this->patientUserA)
            ->post("/patient/appointments/{$appointmentB->id}/reschedule", [
                'new_date' => now()->addDays(5)->format('Y-m-d'),
                'new_time' => '10:00:00',
                'reschedule_reason' => 'Unauthorized reschedule attempt',
            ]);

        $response->assertStatus(403);
    }

    /**
     * Patient A cannot delete Patient B's emergency contact.
     */
    public function test_idor_patient_cannot_delete_another_patients_emergency_contact(): void
    {
        $contactB = PatientContact::create([
            'patient_id' => $this->patientB->id,
            'name' => 'Guardian of B',
            'relationship' => 'Spouse',
            'phone' => '01799887766',
            'is_primary' => true,
        ]);

        // Patient A attempts to delete Patient B's emergency contact
        $response = $this->actingAs($this->patientUserA)
            ->delete("/patient/emergency-contacts/{$contactB->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('patient_contacts', ['id' => $contactB->id]);
    }

    /**
     * Patient A cannot mark Patient B's system notifications as read.
     */
    public function test_idor_patient_cannot_manipulate_another_users_notifications(): void
    {
        $notifB = SystemNotification::create([
            'user_id' => $this->patientUserB->id,
            'type' => 'appointment_scheduled',
            'title' => 'Appointment Confirmed',
            'message' => 'Your appointment is confirmed.',
            'is_read' => false,
        ]);

        // Patient A attempts to mark Patient B's notification as read
        $response = $this->actingAs($this->patientUserA)
            ->post("/patient/notifications/{$notifB->id}/read");

        $response->assertStatus(404);
        $this->assertFalse($notifB->fresh()->is_read);
    }

    // =========================================================================
    // 2. VERTICAL PRIVILEGE ESCALATION / RBAC RESTRICTIONS
    // =========================================================================

    /**
     * Patient user cannot access internal staff portal dashboard or modules.
     */
    public function test_vertical_patient_cannot_access_staff_portal(): void
    {
        $response = $this->actingAs($this->patientUserA)->get('/portal/admin');
        $response->assertRedirect(route('patient.dashboard'));

        $doctorResponse = $this->actingAs($this->patientUserA)->get('/portal/doctor');
        $doctorResponse->assertRedirect(route('patient.dashboard'));

        $billingResponse = $this->actingAs($this->patientUserA)->get('/portal/billing');
        $billingResponse->assertRedirect(route('patient.dashboard'));
    }

    /**
     * Staff user cannot access patient self-care portal routes.
     */
    public function test_vertical_staff_cannot_access_patient_portal(): void
    {
        $response = $this->actingAs($this->doctorUser)->get('/patient/dashboard');
        $response->assertRedirect(route('portal.dashboard'));
    }

    /**
     * Front-desk Receptionist cannot access the Administrator Control Center.
     */
    public function test_vertical_receptionist_cannot_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->receptionistUser)->get('/portal/admin');
        $response->assertStatus(403);
    }

    /**
     * Medical Doctor and Nurse cannot access sensitive financial reports.
     */
    public function test_vertical_clinical_staff_cannot_access_financial_reports(): void
    {
        $docRevenue = $this->actingAs($this->doctorUser)->get('/portal/reports/revenue');
        $docRevenue->assertStatus(403);

        $docPayment = $this->actingAs($this->doctorUser)->get('/portal/reports/payment');
        $docPayment->assertStatus(403);

        $nurseDue = $this->actingAs($this->nurseUser)->get('/portal/reports/due');
        $nurseDue->assertStatus(403);

        $nurseRefund = $this->actingAs($this->nurseUser)->get('/portal/reports/refund');
        $nurseRefund->assertStatus(403);
    }

    /**
     * Non-admin staff cannot view system security audit logs.
     */
    public function test_vertical_non_admin_cannot_access_audit_logs(): void
    {
        $doctorResp = $this->actingAs($this->doctorUser)->get('/portal/audit-logs');
        $doctorResp->assertStatus(403);

        $nurseResp = $this->actingAs($this->nurseUser)->get('/portal/audit-logs');
        $nurseResp->assertStatus(403);

        $receptionResp = $this->actingAs($this->receptionistUser)->get('/portal/audit-logs');
        $receptionResp->assertStatus(403);

        $billingResp = $this->actingAs($this->billingUser)->get('/portal/audit-logs');
        $billingResp->assertStatus(403);
    }

    /**
     * Nurse cannot perform Doctor's clinical clearance on discharge.
     */
    public function test_vertical_nurse_cannot_grant_doctor_clinical_clearance(): void
    {
        $visit = Visit::create([
            'patient_id' => $this->patientA->id,
            'visit_number' => 'VST-2026-' . rand(10000, 99999),
            'visit_type' => 'emergency',
            'visit_date' => now()->subDays(2),
            'triage_level' => 'priority',
            'status' => 'admitted',
        ]);

        $admission = Admission::create([
            'patient_id' => $this->patientA->id,
            'visit_id' => $visit->id,
            'admission_number' => 'ADM-2026-' . rand(10000, 99999),
            'department_id' => $this->department->id,
            'admission_date' => now()->subDays(2),
            'admission_reason' => 'Emergency clinical observation',
            'status' => 'admitted',
            'admitted_by' => $this->adminUser->id,
            'attending_doctor_id' => $this->doctorUser->id,
        ]);

        $response = $this->actingAs($this->nurseUser)->post("/portal/discharges/{$admission->id}/clinical", [
            'final_diagnosis' => 'Clinical diagnosis',
            'discharge_condition' => 'recovered',
            'discharge_type' => 'normal',
            'discharge_notes' => 'Clinical clearance attempt by nurse',
        ]);

        $response->assertStatus(403);
    }

    // =========================================================================
    // 3. UNLINKED PATIENT ISOLATION
    // =========================================================================

    /**
     * Self-registered unlinked patient cannot access sensitive clinical records.
     */
    public function test_unlinked_patient_is_strictly_blocked_from_clinical_records(): void
    {
        $prescriptionsResponse = $this->actingAs($this->unlinkedPatientUser)->get('/patient/prescriptions');
        $prescriptionsResponse->assertRedirect(route('patient.unlinked'));

        $labResponse = $this->actingAs($this->unlinkedPatientUser)->get('/patient/lab-reports');
        $labResponse->assertRedirect(route('patient.unlinked'));

        $staysResponse = $this->actingAs($this->unlinkedPatientUser)->get('/patient/inpatient-stays');
        $staysResponse->assertRedirect(route('patient.unlinked'));

        $billsResponse = $this->actingAs($this->unlinkedPatientUser)->get('/patient/bills');
        $billsResponse->assertRedirect(route('patient.unlinked'));
    }

    // =========================================================================
    // 4. AUTHENTICATION INTEGRITY & RATE LIMITING
    // =========================================================================

    /**
     * Empty credentials fail validation without auto-populating default passwords.
     */
    public function test_empty_login_request_fails_validation(): void
    {
        $response = $this->post('/portal/login', []);
        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();

        $patientResponse = $this->post('/patient/login', []);
        $patientResponse->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    /**
     * Login endpoint locks out after 5 consecutive failed attempts.
     */
    public function test_login_rate_limiting_triggers_lockout(): void
    {
        RateLimiter::clear('admin@meghnaclinic.com|127.0.0.1');

        for ($i = 0; $i < 5; $i++) {
            $this->post('/portal/login', [
                'email' => 'admin@meghnaclinic.com',
                'password' => 'WrongPassword!',
            ]);
        }

        // 6th attempt should be rate limited
        $response = $this->post('/portal/login', [
            'email' => 'admin@meghnaclinic.com',
            'password' => 'WrongPassword!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('Too many login attempts', session('errors')->first('email'));
    }

    // =========================================================================
    // 5. FILE UPLOAD SECURITY
    // =========================================================================

    /**
     * Non-whitelisted file types (.php, .exe) are strictly rejected.
     */
    public function test_malicious_file_uploads_are_rejected(): void
    {
        Storage::fake('secure_reports');

        $visit = Visit::create([
            'patient_id' => $this->patientA->id,
            'visit_number' => 'VST-2026-' . rand(10000, 99999),
            'visit_type' => 'regular',
            'visit_date' => now(),
            'triage_level' => 'normal',
            'status' => 'completed',
        ]);

        $consultation = Consultation::create([
            'visit_id' => $visit->id,
            'doctor_id' => $this->doctorUser->id,
            'chief_complaints' => 'Biochemistry panel workup',
            'status' => 'finalized',
            'finalized_at' => now(),
        ]);

        $labTest = LabTestCatalog::first() ?? LabTestCatalog::create([
            'name' => 'Biochemistry Panel',
            'code' => 'BIO-' . rand(100, 999),
            'category' => 'Biochemistry',
            'cost' => 800.00,
            'is_active' => true,
        ]);

        $labOrder = LabOrder::create([
            'patient_id' => $this->patientA->id,
            'consultation_id' => $consultation->id,
            'order_number' => 'LAB-2026-' . rand(10000, 99999),
            'status' => 'sample_collected',
        ]);

        $item = LabOrderItem::create([
            'lab_order_id' => $labOrder->id,
            'lab_test_id' => $labTest->id,
            'test_name' => $labTest->name,
            'status' => 'sample_collected',
        ]);

        // Attempting to upload PHP script
        $phpFile = UploadedFile::fake()->create('malicious_shell.php', 100, 'application/x-php');

        $response = $this->actingAs($this->adminUser)
            ->post("/portal/lab-reports/items/{$item->id}/external", [
                'file' => $phpFile,
            ]);

        $response->assertSessionHasErrors('file');

        // Attempting to upload executable
        $exeFile = UploadedFile::fake()->create('payload.exe', 500, 'application/x-msdownload');

        $exeResponse = $this->actingAs($this->adminUser)
            ->post("/portal/lab-reports/items/{$item->id}/external", [
                'file' => $exeFile,
            ]);

        $exeResponse->assertSessionHasErrors('file');
    }

    // =========================================================================
    // 6. AUDIT LOG IMMUTABILITY & SENSITIVE DATA REDACTION
    // =========================================================================

    /**
     * Audit logs cannot be modified or deleted once created.
     */
    public function test_audit_logs_are_strictly_immutable(): void
    {
        $log = AuditLog::create([
            'user_id' => $this->adminUser->id,
            'event' => 'security.audit_test_event',
            'auditable_type' => User::class,
            'auditable_id' => $this->adminUser->id,
            'old_values' => ['name' => 'Original Name'],
            'new_values' => ['name' => 'Changed Name'],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'created_at' => now(),
        ]);

        // Attempting update must throw RuntimeException
        try {
            $log->update(['event' => 'tampered.event']);
            $this->fail('Expected RuntimeException on audit log update was not thrown.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('immutable', $e->getMessage());
        }

        // Attempting delete must throw RuntimeException
        try {
            $log->delete();
            $this->fail('Expected RuntimeException on audit log delete was not thrown.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('permanent records', $e->getMessage());
        }
    }

    /**
     * Sensitive credential keys are automatically redacted in audit logs.
     */
    public function test_sensitive_credentials_are_redacted_in_audit_logs(): void
    {
        $log = AuditLog::create([
            'user_id' => $this->adminUser->id,
            'event' => 'auth.password_update_test',
            'auditable_type' => User::class,
            'auditable_id' => $this->adminUser->id,
            'old_values' => [
                'email' => 'admin@meghnaclinic.com',
                'password' => 'PlainSecretPass123!',
                'token' => 'plain_api_token_value',
            ],
            'new_values' => [
                'password' => 'NewPlainSecretPass456!',
                'pin' => '1234',
                'cvv' => '999',
            ],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'created_at' => now(),
        ]);

        $this->assertEquals('[REDACTED]', $log->old_values['password']);
        $this->assertEquals('[REDACTED]', $log->old_values['token']);
        $this->assertEquals('[REDACTED]', $log->new_values['password']);
        $this->assertEquals('[REDACTED]', $log->new_values['pin']);
        $this->assertEquals('[REDACTED]', $log->new_values['cvv']);
        $this->assertEquals('admin@meghnaclinic.com', $log->old_values['email']);
    }

    // =========================================================================
    // 7. FINANCIAL TRANSACTION OVERPAYMENT & CONCURRENCY INTEGRITY
    // =========================================================================

    /**
     * Payment greater than invoice due balance is rejected.
     */
    public function test_financial_overpayment_prevention(): void
    {
        $visit = Visit::create([
            'patient_id' => $this->patientA->id,
            'visit_number' => 'VST-2026-' . rand(10000, 99999),
            'visit_type' => 'regular',
            'visit_date' => now(),
            'triage_level' => 'normal',
            'status' => 'completed',
        ]);

        $bill = Bill::create([
            'patient_id' => $this->patientA->id,
            'visit_id' => $visit->id,
            'invoice_number' => 'INV-' . date('Ymd') . '-' . rand(1000, 9999),
            'subtotal_amount' => 1000.00,
            'discount_amount' => 0.00,
            'total_amount' => 1000.00,
            'paid_amount' => 0.00,
            'due_amount' => 1000.00,
            'status' => 'unpaid',
            'bill_date' => now(),
            'created_by' => $this->billingUser->id,
        ]);

        $paymentService = app(PaymentService::class);

        // Attempting to record payment of 1500 on 1000 due bill
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('exceeds the invoice due balance');

        $paymentService->recordPayment($bill, [
            'amount' => 1500.00,
            'payment_method' => 'cash',
        ], $this->billingUser);
    }

    // =========================================================================
    // 8. SQL INJECTION & INPUT SANITIZATION
    // =========================================================================

    /**
     * SQL injection patterns in search/filters are handled safely without syntax errors.
     */
    public function test_sql_injection_payloads_in_search_and_filters_are_harmless(): void
    {
        $payloads = [
            "' OR '1'='1",
            "1; DROP TABLE users; --",
            "' UNION SELECT id, name, email FROM users --",
            "admin'--",
            "\" OR \"\"=\"",
        ];

        foreach ($payloads as $payload) {
            // Test patient search endpoint
            $response = $this->actingAs($this->doctorUser)
                ->get('/portal/doctor/search-patients?q=' . urlencode($payload));

            $response->assertStatus(200);

            // Test reports filter endpoint
            $reportResponse = $this->actingAs($this->adminUser)
                ->get('/portal/reports/patient_registration?search=' . urlencode($payload));

            $reportResponse->assertStatus(200);
        }
    }

    // =========================================================================
    // 9. XSS DEFENSE & BLADE AUTO-ESCAPING
    // =========================================================================

    /**
     * Script tags in user input are escaped by Blade and not rendered as executable HTML.
     */
    public function test_xss_payloads_are_safely_escaped_on_render(): void
    {
        $xssPayload = '<script>alert("XSS_ATTACK_VECTOR")</script>';

        $appointment = Appointment::create([
            'patient_id' => $this->patientA->id,
            'doctor_id' => $this->doctorUser->id,
            'department_id' => $this->department->id,
            'appointment_date' => now()->addDays(2),
            'type' => 'online',
            'status' => 'confirmed',
            'reason' => $xssPayload,
        ]);

        $response = $this->actingAs($this->patientUserA)
            ->get('/patient/appointments');

        $response->assertStatus(200);
        // The script tag must be HTML-escaped by Blade:
        $response->assertSee('&lt;script&gt;alert(&quot;XSS_ATTACK_VECTOR&quot;)&lt;/script&gt;', false);
        $response->assertDontSee('<script>alert("XSS_ATTACK_VECTOR")</script>', false);
    }

    // =========================================================================
    // 10. MASS ASSIGNMENT & ELEVATION OF PRIVILEGE PREVENTION
    // =========================================================================

    /**
     * Patient cannot change their user_type or activate elevated roles via profile update.
     */
    public function test_mass_assignment_cannot_alter_user_type_or_roles(): void
    {
        $response = $this->actingAs($this->patientUserA)
            ->put('/patient/profile', [
                'name' => 'Attempted Privilege Escalation',
                'email' => $this->patientUserA->email,
                'phone' => $this->patientUserA->phone,
                'user_type' => 'staff', // Attacker attempt to convert account to staff
                'is_active' => true,
                'role' => 'administrator',
            ]);

        $response->assertRedirect(route('patient.profile'));

        $freshUser = $this->patientUserA->fresh();
        $this->assertEquals('patient', $freshUser->user_type);
        $this->assertFalse($freshUser->hasRole('administrator'));
    }

    // =========================================================================
    // 11. SESSION INVALIDATION ON LOGOUT
    // =========================================================================

    /**
     * Session is invalidated upon logout and former session cannot access protected endpoints.
     */
    public function test_session_is_invalidated_after_logout(): void
    {
        $this->actingAs($this->doctorUser);
        $this->get('/portal/doctor')->assertStatus(200);

        // Logout
        $logoutResponse = $this->post('/portal/logout');
        $logoutResponse->assertRedirect(route('portal.login'));
        $this->assertGuest();

        // Attempting to access protected portal after logout must redirect to login
        $followUpResponse = $this->get('/portal/doctor');
        $followUpResponse->assertRedirect(route('portal.login'));
    }
}
