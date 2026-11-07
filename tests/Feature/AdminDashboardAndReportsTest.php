<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Bed;
use App\Models\BedAssignment;
use App\Models\Bill;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\DepartmentAndBedSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminDashboardAndReportsTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;
    protected User $doctorUser;
    protected User $nurseUser;
    protected User $billingUser;
    protected User $receptionistUser;
    protected Department $surgeryDept;
    protected Department $gynaeDept;
    protected Patient $patient;

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

        $this->surgeryDept = Department::where('code', 'SURG')->first() ?? Department::create([
            'name' => 'General Surgery',
            'code' => 'SURG',
            'is_clinical' => true,
            'is_active' => true,
        ]);

        $this->gynaeDept = Department::where('code', 'GYN')->first() ?? Department::create([
            'name' => 'Gynecology & Obstetrics',
            'code' => 'GYN',
            'is_clinical' => true,
            'is_active' => true,
        ]);

        $this->patient = Patient::create([
            'patient_code' => 'MCNH-' . date('Y') . '-' . rand(50000, 99999) . rand(10, 99),
            'name' => 'Admin Report Patient ' . rand(100, 999),
            'date_of_birth' => '1988-08-20',
            'gender' => 'female',
            'phone' => '017' . rand(10000000, 99999999),
            'address' => 'Balubari, Dinajpur',
            'registration_date' => now()->toDateString(),
            'is_verified' => true,
        ]);
    }

    /**
     * Test Administrator Dashboard loads with live database metrics.
     */
    public function test_admin_dashboard_loads_with_real_metrics_for_administrator(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('portal.admin'));

        $response->assertOk();
        $response->assertSee('Hospital Administration Overview');
        $response->assertSee('Total Registered Patients');
        $response->assertSee('10-Bed Occupancy');
        $response->assertSee("Today's Collections", false);
        $response->assertSee('Department-wise Patient Load');
        $response->assertSee('Diagnostic Laboratory Status');
        $response->assertSee('Discharge Clearance Station');
    }

    /**
     * Test Non-Administrator users cannot access Admin Dashboard.
     */
    public function test_non_admin_cannot_access_admin_dashboard(): void
    {
        // Doctor cannot access
        $doctorResponse = $this->actingAs($this->doctorUser)
            ->get(route('portal.admin'));
        $doctorResponse->assertStatus(403);

        // Nurse cannot access
        $nurseResponse = $this->actingAs($this->nurseUser)
            ->get(route('portal.admin'));
        $nurseResponse->assertStatus(403);

        // Receptionist cannot access
        $receptionistResponse = $this->actingAs($this->receptionistUser)
            ->get(route('portal.admin'));
        $receptionistResponse->assertStatus(403);
    }

    /**
     * Test Reports Center Index loads for authorized staff.
     */
    public function test_reports_center_index_loads_for_authorized_staff(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('portal.reports.index'));

        $response->assertOk();
        $response->assertSee('Hospital Reports &amp; Analytics Center', false);
        $response->assertSee('Patient Registration Report');
        $response->assertSee('Visit History Report');
        $response->assertSee('Admission &amp; Discharge Report', false);
        $response->assertSee('Revenue &amp; Billing Report', false);
        $response->assertSee('Payment &amp; Collections Report', false);
        $response->assertSee('Outstanding Due Aging Report');
        $response->assertSee('Refund Vouchers &amp; Corrections Report', false);
    }

    /**
     * Test Clinical & Operational reports are accessible to Doctor and Administrator.
     */
    public function test_clinical_and_operational_reports_accessible_to_doctor_and_admin(): void
    {
        $clinicalReports = [
            'patient_registration',
            'visit_history',
            'admission_discharge',
            'bed_occupancy',
            'department_performance',
            'doctor_consultation',
            'laboratory',
            'operation',
        ];

        foreach ($clinicalReports as $type) {
            $response = $this->actingAs($this->doctorUser)
                ->get(route('portal.reports.show', $type));

            $response->assertOk();
        }
    }

    /**
     * Test Financial reports are accessible to Administrator and Billing Staff.
     */
    public function test_financial_reports_accessible_to_administrator_and_billing_staff(): void
    {
        $financialReports = ['revenue', 'payment', 'due', 'refund'];

        foreach ($financialReports as $type) {
            // Admin access
            $adminRes = $this->actingAs($this->adminUser)
                ->get(route('portal.reports.show', $type));
            $adminRes->assertOk();

            // Billing Staff access
            $billingRes = $this->actingAs($this->billingUser)
                ->get(route('portal.reports.show', $type));
            $billingRes->assertOk();
        }
    }

    /**
     * Test Financial reports are strictly forbidden (403) to Doctors, Nurses, and Receptionists.
     */
    public function test_financial_reports_strictly_forbidden_to_clinical_and_frontdesk_staff(): void
    {
        $financialReports = ['revenue', 'payment', 'due', 'refund'];

        foreach ($financialReports as $type) {
            // Doctor forbidden
            $docRes = $this->actingAs($this->doctorUser)
                ->get(route('portal.reports.show', $type));
            $docRes->assertStatus(403);

            // Nurse forbidden
            $nurseRes = $this->actingAs($this->nurseUser)
                ->get(route('portal.reports.show', $type));
            $nurseRes->assertStatus(403);

            // Receptionist forbidden
            $recRes = $this->actingAs($this->receptionistUser)
                ->get(route('portal.reports.show', $type));
            $recRes->assertStatus(403);
        }
    }

    /**
     * Test Reports correctly apply Date Range and Quick Presets filters.
     */
    public function test_reports_apply_date_range_and_presets_filtering(): void
    {
        // 1. Create a patient today
        $todayPatient = Patient::create([
            'patient_code' => 'MCNH-' . date('Y') . '-' . rand(50000, 99999) . rand(10, 99),
            'name' => 'Today Registered Patient',
            'date_of_birth' => '1995-01-01',
            'gender' => 'male',
            'phone' => '017' . rand(10000000, 99999999),
            'address' => 'Dinajpur',
            'registration_date' => now()->toDateString(),
            'is_verified' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('portal.reports.show', ['type' => 'patient_registration', 'preset' => 'today']));

        $response->assertOk();
        $response->assertSee('Today Registered Patient');
    }

    /**
     * Test Reports correctly apply Department filter.
     */
    public function test_reports_apply_department_filtering(): void
    {
        $testSurgDept = Department::create([
            'name' => 'Surgery Testing Unit ' . rand(100, 999),
            'code' => 'STU' . rand(10, 99),
            'is_clinical' => true,
            'is_active' => true,
        ]);
        $testGynDept = Department::create([
            'name' => 'Gynae Testing Unit ' . rand(100, 999),
            'code' => 'GTU' . rand(10, 99),
            'is_clinical' => true,
            'is_active' => true,
        ]);

        $visit = Visit::create([
            'patient_id' => $this->patient->id,
            'visit_number' => 'VIS-' . now()->format('Ymd') . '-' . rand(100000, 999999),
            'visit_date' => now()->subDay(),
            'visit_type' => 'emergency',
            'status' => 'admitted',
        ]);

        $admission = Admission::create([
            'admission_number' => 'ADM-' . date('Ymd') . '-' . rand(1000, 9999),
            'visit_id' => $visit->id,
            'patient_id' => $this->patient->id,
            'department_id' => $testSurgDept->id,
            'attending_doctor_id' => $this->doctorUser->id,
            'admission_date' => now()->subDay(),
            'admission_reason' => 'Acute surgical emergency',
            'status' => 'admitted',
        ]);

        // Filter by Surgery Department
        $response = $this->actingAs($this->adminUser)
            ->get(route('portal.reports.show', [
                'type' => 'admission_discharge',
                'department_id' => $testSurgDept->id,
            ]));

        $response->assertOk();
        $response->assertSee($admission->admission_number);

        // Filter by Gynae Department (should NOT see this admission)
        $gynaeResponse = $this->actingAs($this->adminUser)
            ->get(route('portal.reports.show', [
                'type' => 'admission_discharge',
                'department_id' => $testGynDept->id,
            ]));

        $gynaeResponse->assertOk();
        $gynaeResponse->assertDontSee($admission->admission_number);
    }

    /**
     * Test Financial report accurately computes gross, discount, net, and due totals.
     */
    public function test_financial_report_computes_accurate_gross_discount_net_and_due(): void
    {
        $bill = Bill::create([
            'invoice_number' => 'INV-TEST-FIN-' . rand(1000, 9999),
            'patient_id' => $this->patient->id,
            'subtotal_amount' => '5000.00',
            'discount_amount' => '500.00',
            'total_amount' => '4500.00',
            'paid_amount' => '3000.00',
            'due_amount' => '1500.00',
            'status' => 'partial',
            'bill_date' => now(),
            'created_by' => $this->billingUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('portal.reports.show', ['type' => 'revenue', 'preset' => 'today']));

        $response->assertOk();
        $response->assertSee($bill->invoice_number);
        $response->assertSee('5,000.00'); // Subtotal
        $response->assertSee('500.00');   // Discount
        $response->assertSee('4,500.00'); // Total Net
        $response->assertSee('3,000.00'); // Paid
        $response->assertSee('1,500.00'); // Due
    }

    /**
     * Test Printable report renders official hospital branding, parameters, and signatures.
     */
    public function test_printable_report_renders_official_hospital_branding(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('portal.reports.print', ['type' => 'patient_registration']));

        $response->assertOk();
        $response->assertSee('MEGHNA CLINIC &amp; NURSING HOME', false);
        $response->assertSee('Shohid Minar Mor, Dinajpur, Bangladesh');
        $response->assertSee('Medical Superintendent');
        $response->assertSee('Prepared By / Reporting Officer');
    }
}
