<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\ProcessRefundRequest;
use App\Http\Requests\Billing\StorePaymentRequest;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\Refund;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Display payments ledger and transaction summary.
     */
    public function index(Request $request): View
    {
        $filters = [
            'status' => $request->query('status'),
            'payment_method' => $request->query('payment_method'),
            'search' => $request->query('search'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
        ];

        $payments = $this->paymentService->getPayments($filters);

        $stats = [
            'total_payments_count' => Payment::count(),
            'total_collected' => Payment::sum('amount'),
            'total_refunded' => Refund::sum('amount'),
            'net_revenue' => bcsub((string) Payment::sum('amount'), (string) Refund::sum('amount'), 2),
            'cash_total' => Payment::where('payment_method', 'cash')->sum('amount'),
            'card_total' => Payment::where('payment_method', 'card')->sum('amount'),
            'mfs_total' => Payment::where('payment_method', 'mobile_banking')->sum('amount'),
        ];

        return view('portal.billing.payments.index', compact('payments', 'stats', 'filters'));
    }

    /**
     * Collect and record a payment against an invoice.
     */
    public function store(StorePaymentRequest $request, Bill $bill): RedirectResponse
    {
        $payment = $this->paymentService->recordPayment($bill, $request->validated(), $request->user());

        return redirect()->route('portal.billing.show', $bill)
            ->with('success', "Payment of ৳ " . number_format($payment->amount, 2) . " successfully collected. Money Receipt #{$payment->receipt_number} issued.");
    }

    /**
     * Display payment transaction dossier.
     */
    public function show(Payment $payment, Request $request): View
    {
        $payment->load([
            'bill.patient',
            'receivedBy',
            'refunds.approvedBy',
            'auditLogs.user',
        ]);

        $canRefund = $this->paymentService->canAuthorizeRefund($request->user());

        return view('portal.billing.payments.show', compact('payment', 'canRefund'));
    }

    /**
     * Process an authorized refund against a payment.
     */
    public function refund(ProcessRefundRequest $request, Payment $payment): RedirectResponse
    {
        $refund = $this->paymentService->processRefund($payment, $request->validated(), $request->user());

        return redirect()->route('portal.payments.show', $payment)
            ->with('success', "Refund Voucher #{$refund->refund_voucher} for ৳ " . number_format($refund->amount, 2) . " authorized and processed.");
    }

    /**
     * Display printable money receipt for a payment transaction.
     */
    public function printReceipt(Payment $payment): View
    {
        $payment->load([
            'bill.patient',
            'bill.items',
            'receivedBy',
            'refunds.approvedBy',
        ]);

        return view('portal.billing.payments.receipt', compact('payment'));
    }

    /**
     * Display list of all refund vouchers.
     */
    public function refundsIndex(Request $request): View
    {
        $filters = [
            'search' => $request->query('search'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
        ];

        $refunds = $this->paymentService->getRefunds($filters);

        $stats = [
            'total_refunds_count' => Refund::count(),
            'total_refunded_amount' => Refund::sum('amount'),
        ];

        return view('portal.billing.refunds.index', compact('refunds', 'stats', 'filters'));
    }

    /**
     * Display printable refund voucher.
     */
    public function printRefundVoucher(Refund $refund): View
    {
        $refund->load([
            'payment.bill.patient',
            'bill.patient',
            'approvedBy',
        ]);

        return view('portal.billing.refunds.voucher', compact('refund'));
    }
}

