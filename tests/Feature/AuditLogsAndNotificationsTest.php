<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Bed;
use App\Models\BedAssignment;
use App\Models\Bill;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\DoctorSchedule;
use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\LabReport;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Role;
use App\Models\SystemNotification;
use App\Models\User;
use App\Models\Visit;
use App\Services\AuditService;
use App\Services\NotificationService;
use Database\Seeders\DepartmentAndBedSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuditLogsAndNotificationsTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;
    protected User $doctorUser;
    protected User $nurseUser;
    protected User $billingUser;
    protected User $receptionistUser;
    protected Patient $patient;
    protected Department $department;
    protected Bed $bed1;
    protected Bed $bed2;
    protected \App\Models\LabTestCatalog $labCatalog;
    protected AuditService $auditService;
    protected NotificationService $notificationService;

    protected function setUp(): void
    {
        parent::setUp();

        if (Department::count() === 0 || Bed::count() === 0) {
            $this->seed(RolePermissionSeeder::class);
            $this->seed(DepartmentAndBedSeeder::class);
            $this->seed(SystemSettingsSeeder::class);
            $this->seed(UserSeeder::class);
        }

        $this->adminUser = User::whereHas('roles', fn($q) => $q->where('name', 'administrator'))->first()
            ?? User::factory()->create(['user_type' => 'staff']);
        if (!$this->adminUser->hasRole('administrator')) {
            $role = Role::where('name', 'administrator')->first();
            if ($role) $this->adminUser->roles()->syncWithoutDetaching([$role->id]);
        }

        $this->doctorUser = User::whereHas('roles', fn($q) => $q->where('name', 'doctor'))->first()
            ?? User::factory()->create(['user_type' => 'staff']);
        if (!$this->doctorUser->hasRole('doctor')) {
            $role = Role::where('name', 'doctor')->first();
            if ($role) $this->doctorUser->roles()->syncWithoutDetaching([$role->id]);
        }

        $this->nurseUser = User::whereHas('roles', fn($q) => $q->where('name', 'nurse'))->first()
            ?? User::factory()->create(['user_type' => 'staff']);
        if (!$this->nurseUser->hasRole('nurse')) {
            $role = Role::where('name', 'nurse')->first();
            if ($role) $this->nurseUser->roles()->syncWithoutDetaching([$role->id]);
        }

        $this->billingUser = User::whereHas('roles', fn($q) => $q->where('name', 'billing_staff'))->first()
            ?? User::factory()->create(['user_type' => 'staff']);
        if (!$this->billingUser->hasRole('billing_staff')) {
            $role = Role::where('name', 'billing_staff')->first();
            if ($role) $this->billingUser->roles()->syncWithoutDetaching([$role->id]);
        }

        $this->receptionistUser = User::whereHas('roles', fn($q) => $q->where('name', 'receptionist'))->first()
            ?? User::factory()->create(['user_type' => 'staff']);
        if (!$this->receptionistUser->hasRole('receptionist')) {
            $role = Role::where('name', 'receptionist')->first();
            if ($role) $this->receptionistUser->roles()->syncWithoutDetaching([$role->id]);
        }

        $this->department = Department::first() ?? Department::create([
            'name' => 'General Medicine',
            'code' => 'MED',
            'is_clinical' => true,
            'is_active' => true,
        ]);

        $this->bed1 = Bed::first() ?? Bed::create([
            'bed_number' => 'TEST-01',
            'department_id' => $this->department->id,
            'category' => 'general',
            'daily_charge' => '1000.00',
            'status' => 'available',
        ]);

        $this->bed2 = Bed::skip(1)->first() ?? Bed::create([
            'bed_number' => 'TEST-02',
            'department_id' => $this->department->id,
            'category' => 'cabin',
            'daily_charge' => '2500.00',
            'status' => 'available',
        ]);

        $this->patient = Patient::create([
            'patient_code' => 'MCNH-' . date('Y') . '-' . rand(50000, 99999) . rand(10, 99),
            'name' => 'Audit Test Patient ' . rand(100, 999),
            'date_of_birth' => '1990-05-15',
            'gender' => 'male',
            'phone' => '017' . rand(10000000, 99999999),
            'address' => 'Shohid Minar Mor, Dinajpur',
            'registration_date' => now()->toDateString(),
            'is_verified' => true,
        ]);

        $this->labCatalog = \App\Models\LabTestCatalog::first() ?? \App\Models\LabTestCatalog::create([
            'name' => 'Complete Blood Count',
            'code' => 'CBC-' . rand(100, 999),
            'category' => 'hematology',
            'standard_charge' => '500.00',
            'sample_type' => 'blood',
            'turnaround_time' => '4 hours',
            'default_source' => 'internal',
            'is_active' => true,
        ]);

        $this->auditService = app(AuditService::class);
        $this->notificationService = app(NotificationService::class);
    }

    /**
     * Test Audit Logs are strictly immutable and cannot be updated or deleted.
     */
    public function test_audit_logs_are_immutable_and_cannot_be_updated_or_deleted(): void
    {
        $log = AuditLog::create([
            'user_id' => $this->adminUser->id,
            'event' => 'security.immutable_test',
            'auditable_type' => User::class,
            'auditable_id' => $this->adminUser->id,
            'old_values' => ['role' => 'doctor'],
            'new_values' => ['role' => 'administrator'],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'created_at' => now(),
        ]);

        $this->assertDatabaseHas('audit_logs', ['id' => $log->id]);

        // Attempt update -> must throw RuntimeException
        try {
            $log->update(['event' => 'tampered.event']);
            $this->fail('Expected RuntimeException on updating audit log was not thrown.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('immutable', $e->getMessage());
        }

        // Attempt delete -> must throw RuntimeException
        try {
            $log->delete();
            $this->fail('Expected RuntimeException on deleting audit log was not thrown.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('cannot be deleted', $e->getMessage());
        }
    }

    /**
     * Test Passwords and sensitive credentials are automatically redacted in audit logs.
     */
    public function test_sensitive_credentials_and_passwords_are_automatically_redacted_from_audit_logs(): void
    {
        $payloadWithSecrets = [
            'username' => 'test_doctor',
            'password' => 'SuperSecretPassword123!',
            'password_confirmation' => 'SuperSecretPassword123!',
            'token' => 'bearer_token_xyz987',
            'cvv' => '123',
            'pin' => '4321',
            'normal_field' => 'Safe Medical Note',
        ];

        $log = $this->auditService->log(
            event: 'user.sensitive_action',
            auditable: $this->adminUser,
            newValues: $payloadWithSecrets,
            actor: $this->adminUser
        );

        $log->refresh();

        $this->assertEquals('[REDACTED]', $log->new_values['password']);
        $this->assertEquals('[REDACTED]', $log->new_values['password_confirmation']);
        $this->assertEquals('[REDACTED]', $log->new_values['token']);
        $this->assertEquals('[REDACTED]', $log->new_values['cvv']);
        $this->assertEquals('[REDACTED]', $log->new_values['pin']);
        $this->assertEquals('Safe Medical Note', $log->new_values['normal_field']);
    }

    /**
     * Test unauthorized staff cannot access audit logs ledger (403 Forbidden).
     */
    public function test_unauthorized_staff_cannot_access_audit_logs(): void
    {
        // Doctor forbidden
        $this->actingAs($this->doctorUser)
            ->get(route('portal.audit-logs.index'))
            ->assertStatus(403);

        // Nurse forbidden
        $this->actingAs($this->nurseUser)
            ->get(route('portal.audit-logs.index'))
            ->assertStatus(403);

        // Receptionist forbidden
        $this->actingAs($this->receptionistUser)
            ->get(route('portal.audit-logs.index'))
            ->assertStatus(403);

        // Billing forbidden
        $this->actingAs($this->billingUser)
            ->get(route('portal.audit-logs.index'))
            ->assertStatus(403);

        // Administrator authorized
        $this->actingAs($this->adminUser)
            ->get(route('portal.audit-logs.index'))
            ->assertOk()
            ->assertSee('Security Audit Ledger');
    }

    /**
     * Test all 12 high-priority audit events are accurately logged via AuditService.
     */
    public function test_all_twelve_core_audit_events_are_recorded_accurately(): void
    {
        // 1. User Login
        $loginLog = $this->auditService->logUserLogin($this->doctorUser, true);
        $this->assertEquals('user.login', $loginLog->event);
        $this->assertEquals($this->doctorUser->id, $loginLog->user_id);

        // 2. User Creation
        $createdUser = User::factory()->create(['user_type' => 'staff']);
        $userLog = $this->auditService->logUserCreation($createdUser, $this->adminUser);
        $this->assertEquals('user.created', $userLog->event);

        // 3. Role Change
        $roleLog = $this->auditService->logRoleChange($createdUser, ['nurse'], ['receptionist'], $this->adminUser);
        $this->assertEquals('role.changed', $roleLog->event);

        // 4. Patient Record Change
        $patientLog = $this->auditService->logPatientChange($this->patient, 'updated', ['phone' => '01711111111'], ['phone' => '01722222222'], $this->adminUser);
        $this->assertEquals('patient.updated', $patientLog->event);

        // 5. Admission Change
        $visit = Visit::create([
            'patient_id' => $this->patient->id,
            'visit_number' => 'VIS-' . now()->format('Ymd') . '-' . rand(100000, 999999),
            'visit_date' => now(),
            'visit_type' => 'emergency',
            'status' => 'admitted',
        ]);
        $admission = Admission::create([
            'admission_number' => 'ADM-' . date('Ymd') . '-' . rand(1000, 9999),
            'visit_id' => $visit->id,
            'patient_id' => $this->patient->id,
            'department_id' => $this->department->id,
            'attending_doctor_id' => $this->doctorUser->id,
            'admission_date' => now(),
            'admission_reason' => 'Severe acute abdominal condition',
            'status' => 'admitted',
        ]);
        $admissionLog = $this->auditService->logAdmissionChange($admission, 'created', null, null, $this->adminUser);
        $this->assertEquals('admission.created', $admissionLog->event);

        // 6. Bed Transfer
        $assignment = BedAssignment::create([
            'admission_id' => $admission->id,
            'bed_id' => $this->bed2->id,
            'assigned_at' => now(),
            'is_current' => true,
        ]);
        $bedTransferLog = $this->auditService->logBedTransfer($assignment, $this->bed1, $this->bed2, $this->adminUser);
        $this->assertEquals('bed.transferred', $bedTransferLog->event);

        // 7. Clinical Record Change
        $consultation = Consultation::create([
            'visit_id' => $visit->id,
            'doctor_id' => $this->doctorUser->id,
            'chief_complaints' => 'Acute lower abdominal pain',
            'created_at' => now(),
        ]);
        $clinicalLog = $this->auditService->logClinicalRecordChange($consultation, 'consultation_finalized', ['status' => 'draft'], ['status' => 'finalized'], $this->doctorUser);
        $this->assertEquals('clinical.consultation_finalized', $clinicalLog->event);

        // 8. Laboratory Report Upload
        $labOrder = LabOrder::create([
            'consultation_id' => $consultation->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctorUser->id,
            'order_number' => 'LAB-' . date('Ymd') . '-' . rand(1000, 9999),
            'source' => 'internal',
            'status' => 'completed',
            'priority' => 'urgent',
            'total_charge' => '1200.00',
        ]);
        $orderItem = LabOrderItem::create([
            'lab_order_id' => $labOrder->id,
            'lab_test_id' => $this->labCatalog->id,
            'test_name' => 'Complete Blood Count (CBC)',
            'test_charge' => '1200.00',
            'status' => 'completed',
        ]);
        $labReport = LabReport::create([
            'lab_order_item_id' => $orderItem->id,
            'source' => 'internal',
            'report_status' => 'received',
            'review_status' => 'reviewed',
            'file_path' => 'secure_medical_reports/sample.pdf',
        ]);
        $labLog = $this->auditService->logLabReportUpload($labReport, 'verified', $this->doctorUser);
        $this->assertEquals('lab_report.verified', $labLog->event);

        // 9. Billing Changes
        $bill = Bill::create([
            'invoice_number' => 'INV-' . date('Ymd') . '-' . rand(1000, 9999),
            'patient_id' => $this->patient->id,
            'subtotal_amount' => '4000.00',
            'discount_amount' => '500.00',
            'total_amount' => '3500.00',
            'paid_amount' => '2000.00',
            'due_amount' => '1500.00',
            'status' => 'partial',
            'created_by' => $this->billingUser->id,
        ]);
        $billLog = $this->auditService->logBillingChange($bill, 'discount_applied', ['discount' => '0.00'], ['discount' => '500.00'], $this->billingUser);
        $this->assertEquals('billing.discount_applied', $billLog->event);

        // 10. Payment Records
        $payment = Payment::create([
            'bill_id' => $bill->id,
            'receipt_number' => 'REC-' . date('Ymd') . '-' . rand(1000, 9999),
            'amount' => '2000.00',
            'payment_method' => 'cash',
            'status' => 'completed',
            'received_by' => $this->billingUser->id,
            'payment_date' => now(),
        ]);
        $paymentLog = $this->auditService->logPaymentRecord($payment, 'completed', $this->billingUser);
        $this->assertEquals('payment.completed', $paymentLog->event);

        // 11. Refunds
        $refund = Refund::create([
            'bill_id' => $bill->id,
            'payment_id' => $payment->id,
            'refund_voucher' => 'REF-' . date('Ymd') . '-' . rand(1000, 9999),
            'amount' => '500.00',
            'refund_method' => 'cash',
            'reason' => 'Billing error correction',
            'approved_by' => $this->adminUser->id,
            'refunded_at' => now(),
        ]);
        $refundLog = $this->auditService->logRefund($refund, 'authorized', $this->adminUser);
        $this->assertEquals('refund.authorized', $refundLog->event);

        // 12. Discharge Approval
        $dischargeLog = $this->auditService->logDischargeApproval($admission, 'approved', $this->adminUser);
        $this->assertEquals('discharge.approved', $dischargeLog->event);
    }

    /**
     * Test Audit Ledger filtering by Event and Printable layout rendering.
     */
    public function test_audit_ledger_filters_and_printable_view(): void
    {
        $uniqueEvent = 'custom.event_' . rand(1000, 9999);
        $this->auditService->log($uniqueEvent, $this->adminUser, ['test' => true]);

        // Filter by unique event
        $response = $this->actingAs($this->adminUser)
            ->get(route('portal.audit-logs.index', ['event' => $uniqueEvent]));

        $response->assertOk();
        $response->assertSee($uniqueEvent);

        // Printable version
        $printResponse = $this->actingAs($this->adminUser)
            ->get(route('portal.audit-logs.print', ['event' => $uniqueEvent]));

        $printResponse->assertOk();
        $printResponse->assertSee('MEGHNA CLINIC &amp; NURSING HOME', false);
        $printResponse->assertSee('INFORMATION SECURITY &amp; COMPLIANCE AUDIT TRAIL', false);
        $printResponse->assertSee($uniqueEvent);
    }

    /**
     * Test Role-Appropriate Notifications for all 6 Domains.
     */
    public function test_role_appropriate_notifications_for_all_six_domains(): void
    {
        // 1. Appointment Notification -> Doctor & Receptionist
        $appointment = Appointment::create([
            'appointment_number' => 'APP-TEST-' . rand(1000, 9999),
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctorUser->id,
            'department_id' => $this->department->id,
            'appointment_date' => now()->addDay()->toDateString(),
            'appointment_time' => '10:00:00',
            'status' => 'confirmed',
            'type' => 'walk_in',
        ]);
        $notifs = $this->notificationService->notifyAppointmentUpdate($appointment, 'confirmed');
        $this->assertNotEmpty($notifs);
        $this->assertDatabaseHas('system_notifications', [
            'user_id' => $this->doctorUser->id,
            'type' => 'appointment',
        ]);
        $this->assertDatabaseHas('system_notifications', [
            'user_id' => $this->receptionistUser->id,
            'type' => 'appointment',
        ]);

        // 2. Admission Notification -> Doctor, Nurse, Receptionist
        $visit = Visit::create([
            'patient_id' => $this->patient->id,
            'visit_number' => 'VIS-NOTIF-' . rand(1000, 9999),
            'visit_date' => now(),
            'visit_type' => 'emergency',
            'status' => 'admitted',
        ]);
        $admission = Admission::create([
            'admission_number' => 'ADM-NOTIF-' . rand(1000, 9999),
            'visit_id' => $visit->id,
            'patient_id' => $this->patient->id,
            'department_id' => $this->department->id,
            'attending_doctor_id' => $this->doctorUser->id,
            'admission_date' => now(),
            'admission_reason' => 'Emergency observation',
            'status' => 'admitted',
        ]);
        $this->notificationService->notifyAdmissionUpdate($admission, 'created');
        $this->assertDatabaseHas('system_notifications', [
            'user_id' => $this->doctorUser->id,
            'type' => 'admission',
        ]);
        $this->assertDatabaseHas('system_notifications', [
            'user_id' => $this->nurseUser->id,
            'type' => 'admission',
        ]);

        // 3. Bed Assignment Notification -> Nurse & Doctor
        $assignment = BedAssignment::create([
            'admission_id' => $admission->id,
            'bed_id' => $this->bed1->id,
            'assigned_at' => now(),
            'is_current' => true,
        ]);
        $this->notificationService->notifyBedAssignmentUpdate($assignment, 'transferred', $this->bed2);
        $this->assertDatabaseHas('system_notifications', [
            'user_id' => $this->nurseUser->id,
            'type' => 'bed_alert',
        ]);

        // 4. Lab Report Notification -> Ordering Doctor
        $labConsultation = Consultation::create([
            'visit_id' => $visit->id,
            'doctor_id' => $this->doctorUser->id,
            'chief_complaints' => 'Diagnostic check',
        ]);
        $labOrder = LabOrder::create([
            'consultation_id' => $labConsultation->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctorUser->id,
            'order_number' => 'LAB-NOTIF-' . rand(1000, 9999),
            'source' => 'internal',
            'status' => 'completed',
            'priority' => 'routine',
            'total_charge' => '850.00',
        ]);
        $orderItem = LabOrderItem::create([
            'lab_order_id' => $labOrder->id,
            'lab_test_id' => $this->labCatalog->id,
            'test_name' => 'Serum Creatinine',
            'test_charge' => '850.00',
            'status' => 'completed',
        ]);
        $labReport = LabReport::create([
            'lab_order_item_id' => $orderItem->id,
            'source' => 'internal',
            'report_status' => 'received',
            'review_status' => 'reviewed',
            'file_path' => 'secure_medical_reports/test.pdf',
        ]);
        $this->notificationService->notifyLabReportReady($labReport);
        $this->assertDatabaseHas('system_notifications', [
            'user_id' => $this->doctorUser->id,
            'type' => 'lab_ready',
        ]);

        // 5. Billing Notification -> Billing Staff & Administrator
        $bill = Bill::create([
            'invoice_number' => 'INV-NOTIF-' . rand(1000, 9999),
            'patient_id' => $this->patient->id,
            'subtotal_amount' => '2500.00',
            'discount_amount' => '0.00',
            'total_amount' => '2500.00',
            'paid_amount' => '0.00',
            'due_amount' => '2500.00',
            'status' => 'unpaid',
            'created_by' => $this->billingUser->id,
        ]);
        $this->notificationService->notifyBillingUpdate($bill, 'generated');
        $this->assertDatabaseHas('system_notifications', [
            'user_id' => $this->billingUser->id,
            'type' => 'billing',
        ]);

        // 6. Discharge Notification -> Nurses, Billing, Doctor
        $this->notificationService->notifyDischargeUpdate($admission, 'clinical_clearance');
        $this->assertDatabaseHas('system_notifications', [
            'user_id' => $this->nurseUser->id,
            'type' => 'discharge',
        ]);
        $this->assertDatabaseHas('system_notifications', [
            'user_id' => $this->billingUser->id,
            'type' => 'discharge',
        ]);
    }

    /**
     * Test Staff Notification Center: View, Mark as Read, Mark All as Read, and JSON Polling API.
     */
    public function test_staff_notification_center_management(): void
    {
        // Create 2 unread notifications for Doctor
        $notif1 = SystemNotification::create([
            'user_id' => $this->doctorUser->id,
            'title' => 'Test Alert 1',
            'message' => 'First test message',
            'type' => 'admission',
            'is_read' => false,
        ]);

        $notif2 = SystemNotification::create([
            'user_id' => $this->doctorUser->id,
            'title' => 'Test Alert 2',
            'message' => 'Second test message',
            'type' => 'appointment',
            'is_read' => false,
        ]);

        // 1. Doctor views Notification Center
        $response = $this->actingAs($this->doctorUser)
            ->get(route('portal.notifications.index'));

        $response->assertOk();
        $response->assertSee('Staff Notifications Center');
        $response->assertSee('Test Alert 1');
        $response->assertSee('Test Alert 2');

        // 2. Unread Count JSON API
        $countResponse = $this->actingAs($this->doctorUser)
            ->getJson(route('portal.notifications.unread-count'));

        $countResponse->assertOk();
        $this->assertGreaterThanOrEqual(2, $countResponse->json('count'));

        // 3. Mark single notification as read
        $readResponse = $this->actingAs($this->doctorUser)
            ->post(route('portal.notifications.read', $notif1->id));

        $readResponse->assertRedirect();
        $notif1->refresh();
        $this->assertTrue($notif1->is_read);
        $this->assertNotNull($notif1->read_at);

        // 4. Mark all as read
        $markAllResponse = $this->actingAs($this->doctorUser)
            ->post(route('portal.notifications.mark-all-read'));

        $markAllResponse->assertRedirect();
        $notif2->refresh();
        $this->assertTrue($notif2->is_read);

        // 5. Delete/Dismiss notification
        $deleteResponse = $this->actingAs($this->doctorUser)
            ->delete(route('portal.notifications.destroy', $notif1->id));

        $deleteResponse->assertRedirect();
        $this->assertDatabaseMissing('system_notifications', ['id' => $notif1->id]);
    }
}
