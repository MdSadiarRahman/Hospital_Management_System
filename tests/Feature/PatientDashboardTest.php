<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Bill;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\Diagnosis;
use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\LabReport;
use App\Models\LabTestCatalog;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Role;
use App\Models\SystemNotification;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\DepartmentAndBedSeeder;
use Database\Seeders\PatientClinicalDataSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Database\Seeders\UserSeeder;
use Tests\TestCase;

class PatientDashboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (Role::count() === 0) {
            $this->seed(RolePermissionSeeder::class);
            $this->seed(DepartmentAndBedSeeder::class);
            $this->seed(SystemSettingsSeeder::class);
            $this->seed(UserSeeder::class);
            $this->seed(PatientClinicalDataSeeder::class);
        } else {
            $this->seed(PatientClinicalDataSeeder::class);
        }
    }

    /**
     * Test unauthenticated access to patient dashboard redirects to login.
     */
    public function test_unauthenticated_user_cannot_access_patient_dashboard(): void
    {
        $response = $this->get('/patient/dashboard');
        $response->assertRedirect(route('patient.login'));
    }

    /**
     * Test staff users cannot access patient dashboard.
     */
    public function test_staff_user_cannot_access_patient_dashboard(): void
    {
        $doctor = User::where('email', 'doctor@meghnaclinic.com')->firstOrFail();

        $response = $this->actingAs($doctor)->get('/patient/dashboard');
        $response->assertRedirect(route('portal.dashboard'));
    }

    /**
     * Test linked patient dashboard renders all sections with real database records.
     */
    public function test_linked_patient_dashboard_renders_all_sections_with_real_database_records(): void
    {
        $patientUser = User::where('email', 'patient@meghnaclinic.com')->firstOrFail();

        SystemNotification::where('user_id', $patientUser->id)->delete();
        SystemNotification::create([
            'user_id' => $patientUser->id,
            'title' => 'Upcoming Appointment Confirmed',
            'message' => 'Your antenatal follow-up appointment with Dr. Nasreen Sultana is confirmed.',
            'type' => 'appointment',
            'action_url' => route('patient.dashboard') . '#appointments-section',
            'is_read' => false,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($patientUser)->get('/patient/dashboard');

        $response->assertStatus(200);

        // 1. Welcome Section & Identity
        $response->assertSee('Welcome back, ' . $patientUser->name);
        $response->assertSee('Verified Patient: MCNH-2026-0042');
        $response->assertSee('Shohid Minar Mor, Dinajpur');

        // 2. Profile Summary
        $response->assertSee('MCNH-2026-0042');
        $response->assertSee('O+ &bull; Female', false);

        // 3. Upcoming Appointments
        $response->assertSee('My Upcoming Doctor Appointments');
        $response->assertSee('Dr. Nasreen Sultana');
        $response->assertSee('Serial #05');

        // 4. Recent Visits
        $response->assertSee('Recent Consultation Visits');
        $response->assertSee('VST-2026-0042');

        // 5. Available Prescriptions
        $response->assertSee('Available Prescriptions');
        $response->assertSee('Tab. Fefol-Z (Iron + Folic Acid)');
        $response->assertSee('Tab. Calbo-D (Calcium 500mg + Vitamin D3)');

        // 6. Diagnostic Laboratory Reports
        $response->assertSee('Diagnostic Laboratory Reports');
        $response->assertSee('Complete Blood Count (CBC) with ESR');
        $response->assertSee('LAB-2026-0042');

        // 7. Billing Summary & Invoices
        $response->assertSee('Billing Summary');
        $response->assertSee('INV-2026-0042');
        $response->assertSee('1,050.00');

        // 8. Payment Status & Receipts
        $response->assertSee('Payment Transaction Receipts');
        $response->assertSee('REC-2026-0042');
        $response->assertSee('mobile banking');

        // 9. Notifications
        $response->assertSee('Hospital Notifications');
        $response->assertSee('Upcoming Appointment Confirmed');
    }

    /**
     * Test unlinked patient dashboard renders unlinked warning notice and empty states.
     */
    public function test_unlinked_patient_dashboard_renders_unlinked_warning_and_empty_states(): void
    {
        $unlinkedUser = User::where('email', 'newpatient@meghnaclinic.com')->firstOrFail();

        $response = $this->actingAs($unlinkedUser)->get('/patient/dashboard');

        $response->assertStatus(200);

        // Warning and Unlinked status
        $response->assertSee('Pending In-Person Verification');
        $response->assertSee('In-Person Clinical Record Linking Required');
        $response->assertSee('Shohid Minar Mor, Dinajpur');

        // Empty states for clinical sections
        $response->assertSee('No Scheduled Appointments');
        $response->assertSee('No Recorded Consultation Visits');
        $response->assertSee('No Active Prescriptions');
        $response->assertSee('No Diagnostic Lab Reports');
        $response->assertSee('No Invoices Issued');
        $response->assertSee('No Payment Receipts Found');
    }

    /**
     * Test patient can only access their own prescription (patient isolation).
     */
    public function test_patient_can_only_access_their_own_prescription(): void
    {
        $patientA = User::where('email', 'patient@meghnaclinic.com')->firstOrFail();

        // Setup Patient B
        $userB = User::factory()->create(['user_type' => 'patient', 'is_active' => true]);
        $userB->assignRole('patient');
        $clinicalB = Patient::create([
            'patient_code' => 'MCNH-2026-' . rand(5000, 9999),
            'user_id' => $userB->id,
            'name' => 'Patient B Test',
            'date_of_birth' => '1995-01-01',
            'gender' => 'female',
            'phone' => '01722' . rand(100000, 999999),
            'is_verified' => true,
        ]);

        $prescriptionA = Prescription::whereHas('consultation.visit', fn ($q) => $q->where('patient_id', $patientA->patient->id))->firstOrFail();

        // 1. Patient A can view their own prescription
        $authorizedResponse = $this->actingAs($patientA)->get('/patient/prescriptions/' . $prescriptionA->id);
        $authorizedResponse->assertStatus(200);
        $authorizedResponse->assertSee($prescriptionA->consultation->doctor->name);
        $authorizedResponse->assertSee('Digital Prescription');

        // 2. Patient B attempts to view Patient A's prescription -> MUST BE 403 FORBIDDEN
        $unauthorizedResponse = $this->actingAs($userB)->get('/patient/prescriptions/' . $prescriptionA->id);
        $unauthorizedResponse->assertStatus(403);
    }

    /**
     * Test patient can only access their own lab report (patient isolation).
     */
    public function test_patient_can_only_access_their_own_lab_report(): void
    {
        $patientA = User::where('email', 'patient@meghnaclinic.com')->firstOrFail();

        // Setup Patient B
        $userB = User::factory()->create(['user_type' => 'patient', 'is_active' => true]);
        $userB->assignRole('patient');
        $clinicalB = Patient::create([
            'patient_code' => 'MCNH-2026-' . rand(100000, 999999),
            'user_id' => $userB->id,
            'name' => 'Patient B Lab',
            'date_of_birth' => '1995-01-01',
            'gender' => 'female',
            'phone' => '01733' . rand(100000, 999999),
            'is_verified' => true,
        ]);

        $reportA = LabReport::whereHas('orderItem.labOrder', fn ($q) => $q->where('patient_id', $patientA->patient->id))->firstOrFail();

        // 1. Patient A can access their own lab report
        $authorizedResponse = $this->actingAs($patientA)->get('/patient/lab-reports/' . $reportA->id . '/download');
        $authorizedResponse->assertOk();

        // 2. Patient B attempts to access Patient A's lab report -> MUST BE 403 FORBIDDEN
        $unauthorizedResponse = $this->actingAs($userB)->get('/patient/lab-reports/' . $reportA->id . '/download');
        $unauthorizedResponse->assertStatus(403);
    }

    /**
     * Test patient can mark notifications as read.
     */
    public function test_patient_can_mark_notifications_as_read(): void
    {
        $patientUser = User::where('email', 'patient@meghnaclinic.com')->firstOrFail();

        $notification = SystemNotification::create([
            'user_id' => $patientUser->id,
            'title' => 'Test Unread Notification',
            'message' => 'Please check your medication timing.',
            'type' => 'appointment',
            'is_read' => false,
        ]);

        // 1. Mark single notification as read
        $response = $this->actingAs($patientUser)->post('/patient/notifications/' . $notification->id . '/read');
        $response->assertSessionHas('status');

        $notification->refresh();
        $this->assertTrue($notification->is_read);
        $this->assertNotNull($notification->read_at);

        // 2. Mark all as read
        // Create another unread
        SystemNotification::create([
            'user_id' => $patientUser->id,
            'title' => 'Another Unread Notification',
            'message' => 'Your doctor appointment has been confirmed.',
            'type' => 'appointment',
            'is_read' => false,
        ]);

        $readAllResponse = $this->actingAs($patientUser)->post('/patient/notifications/read-all');
        $readAllResponse->assertSessionHas('status');

        $this->assertEquals(0, SystemNotification::where('user_id', $patientUser->id)->unread()->count());
    }
}
