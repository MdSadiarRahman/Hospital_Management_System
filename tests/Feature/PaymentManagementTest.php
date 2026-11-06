<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Bed;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Department;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use Database\Seeders\DepartmentAndBedSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PaymentManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;
    protected User $billingUser;
    protected User $receptionistUser;
    protected User $doctorUser;
    protected User $nurseUser;
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
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('administrator');

        $this->billingUser = User::whereHas('roles', fn($q) => $q->where('name', 'billing_staff'))->first()
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('billing_staff');

        $this->receptionistUser = User::whereHas('roles', fn($q) => $q->where('name', 'receptionist'))->first()
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('receptionist');

        $this->doctorUser = User::whereHas('roles', fn($q) => $q->where('name', 'doctor'))->first()
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('doctor');

        $this->nurseUser = User::whereHas('roles', fn($q) => $q->where('name', 'nurse'))->first()
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('nurse');

        $this->patient = Patient::create([
            'patient_code' => 'MCNH-' . date('Y') . '-' . rand(50000, 99999) . rand(10, 99),
            'name' => 'Payment Test Patient ' . rand(100, 999),
            'date_of_birth' => '1992-04-10',
            'gender' => 'male',
            'phone' => '017' . rand(10000000, 99999999),
            'address' => 'Station Road, Dinajpur',
            'registration_date' => now()->toDateString(),
            'is_verified' => true,
        ]);
    }

    /**
     * Helper to create synthetic bill with line items.
     */
    protected function createBill(string $total = '1000.00'): Bill
    {
        $bill = Bill::create([
            'patient_id' => $this->patient->id,
            'invoice_number' => 'INV-' . date('Ymd') . '-' . rand(1000, 9999),
            'bill_date' => now(),
            'subtotal_amount' => $total,
            'discount_amount' => '0.00',
            'total_amount' => $total,
            'paid_amount' => '0.00',
            'due_amount' => $total,
            'status' => 'unpaid',
            'created_by' => $this->billingUser->id,
        ]);

        BillItem::create([
            'bill_id' => $bill->id,
            'item_type' => 'service',
            'item_name' => 'Clinical Hospital Service Charge',
            'unit_price' => $total,
            'quantity' => 1,
            'total_price' => $total,
        ]);

        return $bill;
    }

    /**
     * Test 1: Record full payment in cash updates bill to paid in full and logs audit entry.
     */
    public function test_record_payment_with_cash_updates_bill_paid_and_due_and_status(): void
    {
        $bill = $this->createBill('1200.00');

        $response = $this->actingAs($this->billingUser)
            ->post(route('portal.billing.payments.store', $bill), [
                'amount' => '1200.00',
                'payment_method' => 'cash',
                'notes' => 'Received full payment at cashier counter',
            ]);

        $response->assertRedirect(route('portal.billing.show', $bill));
        $response->assertSessionHas('success');

        $bill->refresh();
        $this->assertEquals('1200.00', (string) $bill->paid_amount);
        $this->assertEquals('0.00', (string) $bill->due_amount);
        $this->assertEquals('paid', $bill->status);

        $payment = Payment::where('bill_id', $bill->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('1200.00', (string) $payment->amount);
        $this->assertEquals('cash', $payment->payment_method);
        $this->assertEquals('success', $payment->status);
        $this->assertStringStartsWith('RCPT-' . date('Ymd') . '-', $payment->receipt_number);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'payment.recorded',
            'auditable_type' => Payment::class,
            'auditable_id' => $payment->id,
        ]);
    }

    /**
     * Test 2: One bill supports multiple partial payments without overwriting transaction history.
     */
    public function test_multiple_partial_payments_on_single_bill(): void
    {
        $bill = $this->createBill('1500.00');

        // Payment 1: 500.00 Cash installment
        $this->actingAs($this->billingUser)
            ->post(route('portal.billing.payments.store', $bill), [
                'amount' => '500.00',
                'payment_method' => 'cash',
                'notes' => '1st installment',
            ]);

        $bill->refresh();
        $this->assertEquals('500.00', (string) $bill->paid_amount);
        $this->assertEquals('1000.00', (string) $bill->due_amount);
        $this->assertEquals('partial', $bill->status);

        // Payment 2: 600.00 bKash mobile banking installment
        $this->actingAs($this->billingUser)
            ->post(route('portal.billing.payments.store', $bill), [
                'amount' => '600.00',
                'payment_method' => 'mobile_banking',
                'transaction_ref' => '9K28ALM90P',
                'notes' => '2nd installment via bKash',
            ]);

        $bill->refresh();
        $this->assertEquals('1100.00', (string) $bill->paid_amount);
        $this->assertEquals('400.00', (string) $bill->due_amount);
        $this->assertEquals('partial', $bill->status);

        // Payment 3: 400.00 Final clearance
        $this->actingAs($this->billingUser)
            ->post(route('portal.billing.payments.store', $bill), [
                'amount' => '400.00',
                'payment_method' => 'cash',
                'notes' => 'Final due settlement',
            ]);

        $bill->refresh();
        $this->assertEquals('1500.00', (string) $bill->paid_amount);
        $this->assertEquals('0.00', (string) $bill->due_amount);
        $this->assertEquals('paid', $bill->status);

        // 3 distinct payments must exist with independent receipt numbers
        $payments = Payment::where('bill_id', $bill->id)->orderBy('id')->get();
        $this->assertCount(3, $payments);
        $this->assertEquals('500.00', (string) $payments[0]->amount);
        $this->assertEquals('600.00', (string) $payments[1]->amount);
        $this->assertEquals('400.00', (string) $payments[2]->amount);
    }

    /**
     * Test 3: Overpayment beyond current bill due amount is strictly prevented.
     */
    public function test_overpayment_is_prevented(): void
    {
        $bill = $this->createBill('800.00');

        // Attempt to pay 1000.00 when due is only 800.00
        $response = $this->actingAs($this->billingUser)
            ->post(route('portal.billing.payments.store', $bill), [
                'amount' => '1000.00',
                'payment_method' => 'cash',
            ]);

        $bill->refresh();
        $this->assertEquals('0.00', (string) $bill->paid_amount);
        $this->assertEquals('800.00', (string) $bill->due_amount);
        $this->assertEquals('unpaid', $bill->status);
        $this->assertEquals(0, Payment::where('bill_id', $bill->id)->count());
    }

    /**
     * Test 4: Mobile banking payment saves transaction reference and method.
     */
    public function test_mobile_banking_payment_records_transaction_reference(): void
    {
        $bill = $this->createBill('500.00');

        $response = $this->actingAs($this->billingUser)
            ->post(route('portal.billing.payments.store', $bill), [
                'amount' => '500.00',
                'payment_method' => 'mobile_banking',
                'transaction_ref' => 'BKASH-TRX-88231',
            ]);

        $response->assertRedirect();
        $payment = Payment::where('bill_id', $bill->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('mobile_banking', $payment->payment_method);
        $this->assertEquals('BKASH-TRX-88231', $payment->transaction_ref);
    }

    /**
     * Test 5: Authorized partial refund updates payment status, refunded amount, and bill due balance.
     */
    public function test_authorized_partial_refund_updates_payment_and_bill_balances(): void
    {
        $bill = $this->createBill('1000.00');

        // Pay in full
        $this->actingAs($this->billingUser)
            ->post(route('portal.billing.payments.store', $bill), [
                'amount' => '1000.00',
                'payment_method' => 'cash',
            ]);

        $bill->refresh();
        $payment = Payment::where('bill_id', $bill->id)->first();

        // Administrator authorizes partial refund of 300.00
        $response = $this->actingAs($this->adminUser)
            ->post(route('portal.payments.refund', $payment), [
                'amount' => '300.00',
                'reason' => 'Patient discharged early; returned unused medicine/service deposit',
                'refund_method' => 'cash',
            ]);

        $response->assertRedirect(route('portal.payments.show', $payment));
        $response->assertSessionHas('success');

        $payment->refresh();
        $this->assertEquals('300.00', (string) $payment->refunded_amount);
        $this->assertEquals('700.00', (string) $payment->net_amount);
        $this->assertEquals('partially_refunded', $payment->status);

        $bill->refresh();
        // Paid reduced to 700.00, Due increased to 300.00, Status becomes partial
        $this->assertEquals('700.00', (string) $bill->paid_amount);
        $this->assertEquals('300.00', (string) $bill->due_amount);
        $this->assertEquals('partial', $bill->status);

        $refund = Refund::where('payment_id', $payment->id)->first();
        $this->assertNotNull($refund);
        $this->assertEquals('300.00', (string) $refund->amount);
        $this->assertStringStartsWith('RFND-' . date('Ymd') . '-', $refund->refund_voucher);
        $this->assertEquals($this->adminUser->id, $refund->approved_by);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'payment.refunded',
            'auditable_type' => Refund::class,
            'auditable_id' => $refund->id,
        ]);
    }

    /**
     * Test 6: Authorized full refund marks payment as refunded and bill as unpaid.
     */
    public function test_authorized_full_refund_marks_payment_and_reconciles_bill(): void
    {
        $bill = $this->createBill('600.00');

        $this->actingAs($this->billingUser)
            ->post(route('portal.billing.payments.store', $bill), [
                'amount' => '600.00',
                'payment_method' => 'cash',
            ]);

        $payment = Payment::where('bill_id', $bill->id)->first();

        // Process full refund of 600.00
        $this->actingAs($this->adminUser)
            ->post(route('portal.payments.refund', $payment), [
                'amount' => '600.00',
                'reason' => 'Duplicate deposit voucher cancellation',
                'refund_method' => 'cash',
            ]);

        $payment->refresh();
        $this->assertEquals('600.00', (string) $payment->refunded_amount);
        $this->assertEquals('0.00', (string) $payment->net_amount);
        $this->assertEquals('refunded', $payment->status);
        $this->assertTrue($payment->is_fully_refunded);

        $bill->refresh();
        $this->assertEquals('0.00', (string) $bill->paid_amount);
        $this->assertEquals('600.00', (string) $bill->due_amount);
        $this->assertEquals('unpaid', $bill->status);
    }

    /**
     * Test 7: Unauthorized staff cannot process or approve financial refunds.
     */
    public function test_unauthorized_user_cannot_authorize_refund(): void
    {
        $bill = $this->createBill('500.00');

        $this->actingAs($this->billingUser)
            ->post(route('portal.billing.payments.store', $bill), [
                'amount' => '500.00',
                'payment_method' => 'cash',
            ]);

        $payment = Payment::where('bill_id', $bill->id)->first();

        // Nurse attempting to approve a refund
        $response = $this->actingAs($this->nurseUser)
            ->post(route('portal.payments.refund', $payment), [
                'amount' => '100.00',
                'reason' => 'Unauthorized nurse refund',
            ]);

        $response->assertStatus(403);
        $payment->refresh();
        $this->assertEquals('0.00', (string) $payment->refunded_amount);
        $this->assertEquals('success', $payment->status);
    }

    /**
     * Test 8: Refund cannot exceed the remaining refundable balance of a payment.
     */
    public function test_refund_amount_cannot_exceed_available_payment_balance(): void
    {
        $bill = $this->createBill('500.00');

        $this->actingAs($this->billingUser)
            ->post(route('portal.billing.payments.store', $bill), [
                'amount' => '500.00',
                'payment_method' => 'cash',
            ]);

        $payment = Payment::where('bill_id', $bill->id)->first();

        // 1st refund of 400.00
        $this->actingAs($this->adminUser)
            ->post(route('portal.payments.refund', $payment), [
                'amount' => '400.00',
                'reason' => 'First partial refund',
            ]);

        $payment->refresh();
        $this->assertEquals('100.00', (string) $payment->net_amount);

        // Attempting 2nd refund of 150.00 (exceeds available 100.00)
        $response = $this->actingAs($this->adminUser)
            ->post(route('portal.payments.refund', $payment), [
                'amount' => '150.00',
                'reason' => 'Excess refund attempt',
            ]);

        $payment->refresh();
        // Balance remains 100.00 and refunded remains 400.00
        $this->assertEquals('400.00', (string) $payment->refunded_amount);
        $this->assertEquals('100.00', (string) $payment->net_amount);
    }

    /**
     * Test 9: Printable money receipt renders official clinic header, amounts, and signatures.
     */
    public function test_printable_money_receipt_renders_official_clinic_details(): void
    {
        $bill = $this->createBill('750.00');

        $payment = Payment::create([
            'bill_id' => $bill->id,
            'receipt_number' => 'RCPT-' . date('Ymd') . '-9999',
            'amount' => '750.00',
            'refunded_amount' => '0.00',
            'payment_method' => 'cash',
            'received_by' => $this->billingUser->id,
            'status' => 'success',
            'payment_date' => now(),
        ]);

        $response = $this->actingAs($this->billingUser)
            ->get(route('portal.payments.receipt', $payment));

        $response->assertStatus(200);
        $response->assertSee('MEGHNA CLINIC & NURSING HOME', false);
        $response->assertSee('Shohid Minar Mor, Dinajpur', false);
        $response->assertSee($payment->receipt_number, false);
        $response->assertSee($this->patient->name, false);
        $response->assertSee('750.00', false);
        $response->assertSee('RECEIVED WITH THANKS', false);
    }

    /**
     * Test 10: Printable refund voucher renders official clinic details and justification reason.
     */
    public function test_printable_refund_voucher_renders_official_details(): void
    {
        $bill = $this->createBill('500.00');

        $payment = Payment::create([
            'bill_id' => $bill->id,
            'receipt_number' => 'RCPT-' . date('Ymd') . '-8888',
            'amount' => '500.00',
            'refunded_amount' => '200.00',
            'payment_method' => 'cash',
            'received_by' => $this->billingUser->id,
            'status' => 'partially_refunded',
            'payment_date' => now(),
        ]);

        $refund = Refund::create([
            'payment_id' => $payment->id,
            'bill_id' => $bill->id,
            'refund_voucher' => 'RFND-' . date('Ymd') . '-7777',
            'amount' => '200.00',
            'refund_method' => 'cash',
            'reason' => 'Patient lab test discontinued by physician',
            'approved_by' => $this->adminUser->id,
            'refunded_at' => now(),
        ]);

        $response = $this->actingAs($this->billingUser)
            ->get(route('portal.refunds.voucher', $refund));

        $response->assertStatus(200);
        $response->assertSee('MEGHNA CLINIC & NURSING HOME', false);
        $response->assertSee('REFUND VOUCHER', false);
        $response->assertSee($refund->refund_voucher, false);
        $response->assertSee('Patient lab test discontinued by physician', false);
        $response->assertSee('200.00', false);
    }
}
