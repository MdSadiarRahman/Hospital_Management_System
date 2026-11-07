<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\AuditLog;
use App\Models\Bed;
use App\Models\BedAssignment;
use App\Models\BedStatusHistory;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Department;
use App\Models\DischargeHistory;
use App\Models\DischargeSummary;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\DepartmentAndBedSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DischargeManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;
    protected User $doctorUser;
    protected User $nurseUser;
    protected User $billingUser;
    protected User $receptionistUser;
    protected Patient $patient;
    protected Department $department;
    protected Bed $bed;

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

        $this->patient = Patient::create([
            'patient_code' => 'MCNH-' . date('Y') . '-' . rand(50000, 99999) . rand(10, 99),
            'name' => 'Discharge Test Patient ' . rand(100, 999),
            'date_of_birth' => '1990-05-15',
            'gender' => 'female',
            'blood_group' => 'B+',
            'phone' => '017' . rand(10000000, 99999999),
            'address' => 'Goneshtola, Dinajpur',
            'registration_date' => now()->toDateString(),
            'is_verified' => true,
        ]);

        $this->department = Department::first() ?? Department::create([
            'name' => 'General Surgery',
            'code' => 'SURG-' . rand(10, 99),
            'type' => 'clinical',
            'is_active' => true,
        ]);

        $this->bed = Bed::where('status', 'available')->first() ?? Bed::create([
            'department_id' => $this->department->id,
            'bed_number' => 'B-DISC-' . rand(100, 999),
            'category' => 'general',
            'daily_charge' => '500.00',
            'status' => 'available',
        ]);
    }

    /**
     * Helper to create an active admission with an assigned bed.
     */
    protected function createActiveAdmission(): Admission
    {
        $visit = Visit::create([
            'patient_id' => $this->patient->id,
            'visit_number' => 'VIS-' . now()->format('Ymd') . '-' . rand(100000, 999999),
            'visit_date' => now()->subDays(3),
            'visit_type' => 'emergency',
            'status' => 'admitted',
        ]);

        $admission = Admission::create([
            'admission_number' => 'ADM-' . date('Ymd') . '-' . rand(1000, 9999),
            'visit_id' => $visit->id,
            'patient_id' => $this->patient->id,
            'department_id' => $this->department->id,
            'attending_doctor_id' => $this->doctorUser->id,
            'admission_date' => now()->subDays(3),
            'admission_reason' => 'Acute lower abdominal pain requiring inpatient surgical management',
            'status' => 'admitted',
            'notes' => 'Post-op recovery and clinical observation',
        ]);

        // Create fresh occupied bed for this admission
        $this->bed = Bed::create([
            'department_id' => $this->department->id,
            'bed_number' => 'B-' . rand(1000, 9999) . '-' . rand(10, 99),
            'category' => 'general',
            'daily_charge' => '500.00',
            'status' => 'occupied',
        ]);

        BedAssignment::create([
            'bed_id' => $this->bed->id,
            'admission_id' => $admission->id,
            'assigned_by' => $this->adminUser->id,
            'assigned_at' => now()->subDays(3),
        ]);

        return $admission;
    }

    /**
     * Helper to create an admission bill.
     */
    protected function createAdmissionBill(Admission $admission, string $total = '2500.00', string $paid = '2500.00'): Bill
    {
        $due = bcsub($total, $paid, 2);
        $status = bccomp($due, '0.00', 2) <= 0 ? 'paid' : (bccomp($paid, '0.00', 2) > 0 ? 'partial' : 'unpaid');

        $bill = Bill::create([
            'invoice_number' => 'INV-' . date('Ymd') . '-' . rand(1000, 9999),
            'patient_id' => $admission->patient_id,
            'admission_id' => $admission->id,
            'subtotal_amount' => $total,
            'discount_amount' => '0.00',
            'total_amount' => $total,
            'paid_amount' => $paid,
            'due_amount' => $due,
            'status' => $status,
            'bill_date' => now(),
            'created_by' => $this->billingUser->id,
        ]);

        if (bccomp($paid, '0.00', 2) > 0) {
            Payment::create([
                'bill_id' => $bill->id,
                'receipt_number' => 'RCPT-' . date('Ymd') . '-' . rand(1000, 9999),
                'amount' => $paid,
                'payment_method' => 'cash',
                'status' => 'completed',
                'payment_date' => now(),
                'received_by' => $this->billingUser->id,
            ]);
        }

        return $bill;
    }

    /**
     * Test Doctor can grant Clinical Clearance and author Discharge Summary.
     */
    public function test_doctor_can_grant_clinical_clearance_and_author_discharge_summary(): void
    {
        $admission = $this->createActiveAdmission();

        $payload = [
            'discharge_diagnosis' => 'Post-op Laparoscopic Appendectomy (Healed)',
            'condition_at_discharge' => 'improved',
            'hospital_course' => 'Patient underwent uneventful surgery on Day 1. Afebrile, vitals stable.',
            'treatment_given' => 'Laparoscopic Appendectomy, IV Ceftriaxone 1g BD, IV Ketorolac 30mg BD',
            'investigations_summary' => 'CBC: TLC 7,800/cu mm, Hb 12.8 g/dL. USG abdomen normal.',
            'discharge_medications' => "1. Tab Cefuroxime 500mg 1+0+1 x 5 days\n2. Tab Paracetamol 500mg 1+1+1 (PRN)",
            'follow_up_instructions' => 'Follow up at Surgical OPD in 7 days for suture check.',
            'remarks' => 'Patient ambulating well. Normal bowel sounds present.',
        ];

        $response = $this->actingAs($this->doctorUser)
            ->post(route('portal.discharges.clinical-clearance', $admission), $payload);

        $response->assertRedirect(route('portal.discharges.show', $admission));
        $response->assertSessionHas('success');

        $admission->refresh();
        $this->assertTrue($admission->clinical_clearance);
        $this->assertEquals($this->doctorUser->id, $admission->clinical_clearance_by);
        $this->assertNotNull($admission->clinical_clearance_at);

        // Discharge summary verification
        $this->assertDatabaseHas('discharge_summaries', [
            'admission_id' => $admission->id,
            'discharge_diagnosis' => 'Post-op Laparoscopic Appendectomy (Healed)',
            'condition_at_discharge' => 'improved',
            'authorized_by' => $this->doctorUser->id,
        ]);

        // Discharge history verification
        $this->assertDatabaseHas('discharge_histories', [
            'admission_id' => $admission->id,
            'clearance_type' => 'clinical',
            'action' => 'clinical_clearance_granted',
            'performed_by' => $this->doctorUser->id,
        ]);
    }

    /**
     * Test unauthorized staff cannot grant clinical clearance.
     */
    public function test_unauthorized_user_cannot_grant_clinical_clearance(): void
    {
        $admission = $this->createActiveAdmission();

        $payload = [
            'discharge_diagnosis' => 'Clinical diagnosis',
            'condition_at_discharge' => 'improved',
            'treatment_given' => 'Medication',
            'discharge_medications' => 'Tab Paracetamol',
            'follow_up_instructions' => 'Visit OPD',
        ];

        $response = $this->actingAs($this->receptionistUser)
            ->post(route('portal.discharges.clinical-clearance', $admission), $payload);

        $response->assertStatus(403);
        $admission->refresh();
        $this->assertFalse($admission->clinical_clearance);
    }

    /**
     * Test Nurse can record Nursing Exit Clearance.
     */
    public function test_nurse_can_grant_nursing_clearance(): void
    {
        $admission = $this->createActiveAdmission();

        $response = $this->actingAs($this->nurseUser)
            ->post(route('portal.discharges.nursing-clearance', $admission), [
                'nursing_clearance_notes' => 'IV cannula removed without hematoma. Wound dry and clean. Belongings handed over.',
            ]);

        $response->assertRedirect(route('portal.discharges.show', $admission));
        $response->assertSessionHas('success');

        $admission->refresh();
        $this->assertTrue($admission->nursing_clearance);
        $this->assertEquals($this->nurseUser->id, $admission->nursing_clearance_by);
        $this->assertNotNull($admission->nursing_clearance_at);
        $this->assertStringContainsString('IV cannula removed', $admission->nursing_clearance_notes);

        $this->assertDatabaseHas('discharge_histories', [
            'admission_id' => $admission->id,
            'clearance_type' => 'nursing',
            'action' => 'nursing_clearance_granted',
            'performed_by' => $this->nurseUser->id,
        ]);
    }

    /**
     * Test Cashier can grant Financial Clearance when bill has zero dues.
     */
    public function test_financial_clearance_can_be_granted_when_bill_has_zero_dues(): void
    {
        $admission = $this->createActiveAdmission();
        $this->createAdmissionBill($admission, total: '3500.00', paid: '3500.00'); // Due = 0.00

        $response = $this->actingAs($this->billingUser)
            ->post(route('portal.discharges.financial-clearance', $admission), []);

        $response->assertRedirect(route('portal.discharges.show', $admission));
        $response->assertSessionHas('success');

        $admission->refresh();
        $this->assertTrue($admission->financial_clearance);
        $this->assertEquals($this->billingUser->id, $admission->financial_clearance_by);
        $this->assertFalse((bool)$admission->financial_exception_allowed);

        $this->assertDatabaseHas('discharge_histories', [
            'admission_id' => $admission->id,
            'clearance_type' => 'financial',
            'action' => 'financial_clearance_granted',
            'performed_by' => $this->billingUser->id,
        ]);
    }

    /**
     * Test Financial Clearance is strictly blocked when bill has outstanding dues without authorized exception.
     */
    public function test_financial_clearance_is_blocked_when_bill_has_outstanding_dues_without_exception(): void
    {
        $admission = $this->createActiveAdmission();
        $this->createAdmissionBill($admission, total: '5000.00', paid: '3000.00'); // Due = 2000.00

        // Attempting normal clearance without allow_dues_exception
        $response = $this->actingAs($this->billingUser)
            ->post(route('portal.discharges.financial-clearance', $admission), []);

        $response->assertSessionHasErrors('financial_clearance');

        $admission->refresh();
        $this->assertFalse($admission->financial_clearance);
    }

    /**
     * Test Administrator can grant Financial Clearance under explicit authorized dues exception policy.
     */
    public function test_authorized_user_can_grant_financial_clearance_under_explicit_dues_exception_policy(): void
    {
        $admission = $this->createActiveAdmission();
        $this->createAdmissionBill($admission, total: '6000.00', paid: '2000.00'); // Due = 4000.00

        $payload = [
            'allow_dues_exception' => '1',
            'exception_reason' => 'Approved by Medical Superintendent: Installment agreement promissory signed by patient guardian.',
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('portal.discharges.financial-clearance', $admission), $payload);

        $response->assertRedirect(route('portal.discharges.show', $admission));
        $response->assertSessionHas('success');

        $admission->refresh();
        $this->assertTrue($admission->financial_clearance);
        $this->assertTrue($admission->financial_exception_allowed);
        $this->assertEquals($this->adminUser->id, $admission->financial_exception_by);
        $this->assertStringContainsString('Medical Superintendent', $admission->financial_exception_reason);

        $this->assertDatabaseHas('discharge_histories', [
            'admission_id' => $admission->id,
            'clearance_type' => 'financial',
            'action' => 'financial_exception_granted',
            'performed_by' => $this->adminUser->id,
        ]);
    }

    /**
     * Test unauthorized user cannot authorize dues exception.
     */
    public function test_unauthorized_user_cannot_grant_financial_clearance_with_dues_exception(): void
    {
        $admission = $this->createActiveAdmission();
        $this->createAdmissionBill($admission, total: '5000.00', paid: '1000.00'); // Due = 4000.00

        $payload = [
            'allow_dues_exception' => '1',
            'exception_reason' => 'Unauthorized attempt by nurse',
        ];

        $response = $this->actingAs($this->nurseUser)
            ->post(route('portal.discharges.financial-clearance', $admission), $payload);

        $response->assertStatus(403);

        $admission->refresh();
        $this->assertFalse($admission->financial_clearance);
    }

    /**
     * Test Final Discharge is strictly blocked when clinical clearance is missing.
     */
    public function test_final_discharge_is_strictly_blocked_without_clinical_clearance(): void
    {
        $admission = $this->createActiveAdmission();
        $this->createAdmissionBill($admission, total: '1000.00', paid: '1000.00');

        // Only financial clearance granted
        $admission->update([
            'clinical_clearance' => false,
            'financial_clearance' => true,
            'financial_clearance_at' => now(),
            'financial_clearance_by' => $this->billingUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('portal.discharges.approve', $admission), [
                'discharge_type' => 'normal',
            ]);

        $response->assertSessionHasErrors('clinical_clearance');

        $admission->refresh();
        $this->assertEquals('admitted', $admission->status);
        $this->assertNull($admission->discharge_date);

        // Bed must still be occupied
        $this->bed->refresh();
        $this->assertEquals('occupied', $this->bed->status);
    }

    /**
     * Test Final Discharge is strictly blocked when financial clearance is missing.
     */
    public function test_final_discharge_is_strictly_blocked_without_financial_clearance(): void
    {
        $admission = $this->createActiveAdmission();

        // Only clinical clearance granted
        $admission->update([
            'clinical_clearance' => true,
            'clinical_clearance_at' => now(),
            'clinical_clearance_by' => $this->doctorUser->id,
            'financial_clearance' => false,
        ]);

        // Also add discharge summary
        DischargeSummary::create([
            'admission_id' => $admission->id,
            'discharge_diagnosis' => 'Recovered',
            'condition_at_discharge' => 'cured',
            'treatment_given' => 'Medication',
            'discharge_medications' => 'None',
            'follow_up_instructions' => 'Routine check',
            'authorized_by' => $this->doctorUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('portal.discharges.approve', $admission), [
                'discharge_type' => 'normal',
            ]);

        $response->assertSessionHasErrors('financial_clearance');

        $admission->refresh();
        $this->assertEquals('admitted', $admission->status);

        // Bed must still be occupied
        $this->bed->refresh();
        $this->assertEquals('occupied', $this->bed->status);
    }

    /**
     * Test Final Discharge succeeds when both clinical and financial clearances are satisfied,
     * releasing bed to cleaning status and recording audit trail.
     */
    public function test_final_discharge_succeeds_with_dual_clearances_and_releases_bed_to_cleaning(): void
    {
        $admission = $this->createActiveAdmission();
        $this->createAdmissionBill($admission, total: '2000.00', paid: '2000.00');

        // Grant both clinical and financial clearances
        $admission->update([
            'clinical_clearance' => true,
            'clinical_clearance_at' => now(),
            'clinical_clearance_by' => $this->doctorUser->id,
            'financial_clearance' => true,
            'financial_clearance_at' => now(),
            'financial_clearance_by' => $this->billingUser->id,
        ]);

        DischargeSummary::create([
            'admission_id' => $admission->id,
            'discharge_diagnosis' => 'Post-op Appendectomy Complete Recovery',
            'condition_at_discharge' => 'improved',
            'treatment_given' => 'Surgical removal & antibiotics',
            'discharge_medications' => 'Tab Cefuroxime 500mg',
            'follow_up_instructions' => 'Return in 7 days for review',
            'authorized_by' => $this->doctorUser->id,
        ]);

        $response = $this->actingAs($this->doctorUser)
            ->post(route('portal.discharges.approve', $admission), [
                'discharge_type' => 'normal',
                'notes' => 'Patient discharged in stable condition. Given prescriptions and certificate.',
            ]);

        $response->assertRedirect(route('portal.discharges.show', $admission));
        $response->assertSessionHas('success');

        // 1. Check admission status and timestamps
        $admission->refresh();
        $this->assertEquals('discharged', $admission->status);
        $this->assertNotNull($admission->discharge_date);
        $this->assertEquals($this->doctorUser->id, $admission->discharged_by);

        // 2. Check bed assignment was released
        $activeAssignment = BedAssignment::where('admission_id', $admission->id)
            ->whereNull('released_at')
            ->first();
        $this->assertNull($activeAssignment, 'Active bed assignment must be released upon discharge.');

        $releasedAssignment = BedAssignment::where('admission_id', $admission->id)
            ->whereNotNull('released_at')
            ->first();
        $this->assertNotNull($releasedAssignment, 'Bed assignment must be marked as released.');
        $this->assertNotNull($releasedAssignment->released_at);

        // 3. Bed MUST transition to cleaning status for terminal disinfection
        $this->bed->refresh();
        $this->assertEquals('cleaning', $this->bed->status, 'Bed must transition to cleaning status upon discharge.');

        // 4. Bed status history must be recorded
        $this->assertDatabaseHas('bed_status_histories', [
            'bed_id' => $this->bed->id,
            'to_status' => 'cleaning',
            'changed_by' => $this->doctorUser->id,
        ]);

        // 5. Discharge history must record final approval
        $this->assertDatabaseHas('discharge_histories', [
            'admission_id' => $admission->id,
            'clearance_type' => 'final_approval',
            'action' => 'final_discharge_approved',
            'performed_by' => $this->doctorUser->id,
        ]);

        // 6. Audit log must record event
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Admission::class,
            'auditable_id' => $admission->id,
            'event' => 'admission.discharged',
            'user_id' => $this->doctorUser->id,
        ]);
    }

    /**
     * Test authorized staff can revoke clearance prior to final departure.
     */
    public function test_authorized_user_can_revoke_clearance_before_final_discharge(): void
    {
        $admission = $this->createActiveAdmission();
        $admission->update([
            'clinical_clearance' => true,
            'clinical_clearance_at' => now(),
            'clinical_clearance_by' => $this->doctorUser->id,
        ]);

        $response = $this->actingAs($this->doctorUser)
            ->post(route('portal.discharges.revoke', $admission), [
                'clearance_type' => 'clinical',
                'reason' => 'Patient spiked post-op fever; further observation required.',
            ]);

        $response->assertRedirect(route('portal.discharges.show', $admission));
        $response->assertSessionHas('success');

        $admission->refresh();
        $this->assertFalse($admission->clinical_clearance);
        $this->assertNull($admission->clinical_clearance_at);

        $this->assertDatabaseHas('discharge_histories', [
            'admission_id' => $admission->id,
            'clearance_type' => 'reversal',
            'action' => 'clinical_clearance_revoked',
            'remarks' => 'Clearance revoked: Patient spiked post-op fever; further observation required.',
            'performed_by' => $this->doctorUser->id,
        ]);
    }

    /**
     * Test printable discharge certificate renders properly with hospital branding.
     */
    public function test_official_printable_discharge_summary_renders_with_branding_and_details(): void
    {
        $admission = $this->createActiveAdmission();

        DischargeSummary::create([
            'admission_id' => $admission->id,
            'discharge_diagnosis' => 'Acute Cholecystitis (Resolved)',
            'condition_at_discharge' => 'improved',
            'hospital_course' => 'Vitals stabilized with conservative antibiotic therapy.',
            'treatment_given' => 'IV Ceftriaxone, IV Metronidazole, Analgesics',
            'investigations_summary' => 'USG: Gallbladder wall thickening resolved.',
            'discharge_medications' => 'Tab Cefuroxime 500mg BD x 7 days',
            'follow_up_instructions' => 'Surgical OPD review in 1 week.',
            'authorized_by' => $this->doctorUser->id,
        ]);

        $response = $this->actingAs($this->doctorUser)
            ->get(route('portal.discharges.print-summary', $admission));

        $response->assertOk();
        $response->assertSee('MEGHNA CLINIC &amp; NURSING HOME', false);
        $response->assertSee($admission->patient->name);
        $response->assertSee('Acute Cholecystitis (Resolved)');
        $response->assertSee('Tab Cefuroxime 500mg BD x 7 days');
        $response->assertSee('Surgical OPD review in 1 week.');
        $response->assertSee('Attending Doctor / Surgeon');
    }
}
