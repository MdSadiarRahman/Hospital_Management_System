<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PaymentService
{
    /**
     * Generate unique payment receipt number: RCPT-YYYYMMDD-XXXX
     */
    public function generateReceiptNumber(): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "RCPT-{$today}-";

        return DB::transaction(function () use ($prefix) {
            $latest = Payment::where('receipt_number', 'like', "{$prefix}%")
                ->lockForUpdate()
                ->orderBy('receipt_number', 'desc')
                ->value('receipt_number');

            if ($latest) {
                $lastSequence = (int) substr($latest, -4);
                $nextSequence = str_pad($lastSequence + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $nextSequence = '0001';
            }

            return "{$prefix}{$nextSequence}";
        });
    }

    /**
     * Generate unique refund voucher number: RFND-YYYYMMDD-XXXX
     */
    public function generateRefundVoucher(): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "RFND-{$today}-";

        return DB::transaction(function () use ($prefix) {
            $latest = Refund::where('refund_voucher', 'like', "{$prefix}%")
                ->lockForUpdate()
                ->orderBy('refund_voucher', 'desc')
                ->value('refund_voucher');

            if ($latest) {
                $lastSequence = (int) substr($latest, -4);
                $nextSequence = str_pad($lastSequence + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $nextSequence = '0001';
            }

            return "{$prefix}{$nextSequence}";
        });
    }

    /**
     * Check if a user is authorized to approve/grant refunds.
     */
    public function canAuthorizeRefund(User $user): bool
    {
        if ($user->hasRole(['super_admin', 'administrator'])) {
            return true;
        }

        return $user->hasPermission('billing.approve');
    }

    /**
     * Record a new payment against an existing invoice.
     * Prevents overpayment, maintains append-only payment history, and reconciles bill totals.
     */
    public function recordPayment(Bill $bill, array $data, User $collector): Payment
    {
        return DB::transaction(function () use ($bill, $data, $collector) {
            // Lock the bill record to guarantee race condition protection
            /** @var Bill $lockedBill */
            $lockedBill = Bill::with(['items', 'payments', 'refunds'])
                ->where('id', $bill->id)
                ->lockForUpdate()
                ->firstOrFail();

            $amount = isset($data['amount']) ? number_format((float) $data['amount'], 2, '.', '') : '0.00';

            if (bccomp($amount, '0.00', 2) <= 0) {
                throw new InvalidArgumentException("Payment amount must be greater than zero.");
            }

            // Prevent overpayment unless explicitly authorized
            if (bccomp($amount, (string) $lockedBill->due_amount, 2) > 0) {
                throw new InvalidArgumentException("Payment amount (BDT {$amount}) exceeds the invoice due balance of BDT {$lockedBill->due_amount}.");
            }

            $receiptNumber = $this->generateReceiptNumber();
            $paymentMethod = $data['payment_method'] ?? 'cash';
            $paymentDate = !empty($data['payment_date']) ? Carbon::parse($data['payment_date']) : Carbon::now();

            $payment = Payment::create([
                'bill_id' => $lockedBill->id,
                'receipt_number' => $receiptNumber,
                'amount' => $amount,
                'refunded_amount' => '0.00',
                'payment_method' => $paymentMethod,
                'transaction_ref' => !empty($data['transaction_ref']) ? trim($data['transaction_ref']) : null,
                'received_by' => $collector->id,
                'status' => 'success',
                'payment_date' => $paymentDate,
                'notes' => !empty($data['notes']) ? trim($data['notes']) : null,
            ]);

            // Reconcile the bill totals
            $newPaid = bcadd((string) $lockedBill->paid_amount, $amount, 2);
            $newDue = bcsub((string) $lockedBill->due_amount, $amount, 2);

            if (bccomp($newDue, '0.00', 2) <= 0) {
                $newDue = '0.00';
                $status = 'paid';
            } else {
                $status = 'partial';
            }

            $lockedBill->paid_amount = $newPaid;
            $lockedBill->due_amount = $newDue;
            $lockedBill->status = $status;
            $lockedBill->save();

            // Record Financial Audit Log
            AuditLog::create([
                'user_id' => $collector->id,
                'event' => 'payment.recorded',
                'auditable_type' => Payment::class,
                'auditable_id' => $payment->id,
                'old_values' => null,
                'new_values' => [
                    'receipt_number' => $payment->receipt_number,
                    'bill_id' => $lockedBill->id,
                    'invoice_number' => $lockedBill->invoice_number,
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'transaction_ref' => $payment->transaction_ref,
                    'new_bill_paid' => $lockedBill->paid_amount,
                    'new_bill_due' => $lockedBill->due_amount,
                    'new_bill_status' => $lockedBill->status,
                ],
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'created_at' => Carbon::now(),
            ]);

            try {
                app(\App\Services\NotificationService::class)->notifyBillingUpdate($lockedBill, "payment of BDT {$payment->amount} recorded");
            } catch (\Throwable $e) {
                report($e);
            }

            return $payment->fresh(['bill.patient', 'receivedBy']);
        });
    }

    /**
     * Process an authorized refund against a previous payment.
     * Enforces strict authorization, prevents excess refunds, and reconciles invoice balance.
     */
    public function processRefund(Payment $payment, array $data, User $authorizer): Refund
    {
        return DB::transaction(function () use ($payment, $data, $authorizer) {
            if (!$this->canAuthorizeRefund($authorizer)) {
                throw new AuthorizationException("You are not authorized to approve financial refunds or vouchers.");
            }

            if (empty($data['reason']) || trim($data['reason']) === '') {
                throw new InvalidArgumentException("A valid administrative or clinical reason is required to process a refund.");
            }

            /** @var Payment $lockedPayment */
            $lockedPayment = Payment::where('id', $payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            /** @var Bill $lockedBill */
            $lockedBill = Bill::where('id', $lockedPayment->bill_id)
                ->lockForUpdate()
                ->firstOrFail();

            $refundableBalance = bcsub((string) $lockedPayment->amount, (string) $lockedPayment->refunded_amount, 2);
            $requestedAmount = isset($data['amount']) ? number_format((float) $data['amount'], 2, '.', '') : '0.00';

            if (bccomp($requestedAmount, '0.00', 2) <= 0) {
                throw new InvalidArgumentException("Refund amount must be greater than zero.");
            }

            if (bccomp($requestedAmount, $refundableBalance, 2) > 0) {
                throw new InvalidArgumentException("Refund amount (BDT {$requestedAmount}) exceeds the available refundable balance of BDT {$refundableBalance} on this payment receipt.");
            }

            $refundVoucher = $this->generateRefundVoucher();
            $refundMethod = $data['refund_method'] ?? $lockedPayment->payment_method;

            $refund = Refund::create([
                'payment_id' => $lockedPayment->id,
                'bill_id' => $lockedBill->id,
                'refund_voucher' => $refundVoucher,
                'amount' => $requestedAmount,
                'refund_method' => $refundMethod,
                'transaction_ref' => !empty($data['transaction_ref']) ? trim($data['transaction_ref']) : null,
                'reason' => trim($data['reason']),
                'approved_by' => $authorizer->id,
                'refunded_at' => Carbon::now(),
            ]);

            // Update Payment record
            $newRefundedOnPayment = bcadd((string) $lockedPayment->refunded_amount, $requestedAmount, 2);
            $lockedPayment->refunded_amount = $newRefundedOnPayment;

            if (bccomp($newRefundedOnPayment, (string) $lockedPayment->amount, 2) >= 0) {
                $lockedPayment->status = 'refunded';
            } else {
                $lockedPayment->status = 'partially_refunded';
            }
            $lockedPayment->save();

            // Reconcile Invoice totals
            $newPaidOnBill = bcsub((string) $lockedBill->paid_amount, $requestedAmount, 2);
            if (bccomp($newPaidOnBill, '0.00', 2) < 0) {
                $newPaidOnBill = '0.00';
            }

            $newDueOnBill = bcadd((string) $lockedBill->due_amount, $requestedAmount, 2);
            if (bccomp($newDueOnBill, (string) $lockedBill->total_amount, 2) > 0) {
                $newDueOnBill = (string) $lockedBill->total_amount;
            }

            if (bccomp($newPaidOnBill, '0.00', 2) <= 0) {
                $status = 'unpaid';
            } elseif (bccomp($newPaidOnBill, (string) $lockedBill->total_amount, 2) >= 0) {
                $status = 'paid';
            } else {
                $status = 'partial';
            }

            $lockedBill->paid_amount = $newPaidOnBill;
            $lockedBill->due_amount = $newDueOnBill;
            $lockedBill->status = $status;
            $lockedBill->save();

            // Record Financial Audit Log
            AuditLog::create([
                'user_id' => $authorizer->id,
                'event' => 'payment.refunded',
                'auditable_type' => Refund::class,
                'auditable_id' => $refund->id,
                'old_values' => [
                    'payment_refunded_amount' => bcsub($newRefundedOnPayment, $requestedAmount, 2),
                    'bill_paid_amount' => bcadd($newPaidOnBill, $requestedAmount, 2),
                ],
                'new_values' => [
                    'refund_voucher' => $refund->refund_voucher,
                    'refund_amount' => $refund->amount,
                    'payment_id' => $lockedPayment->id,
                    'receipt_number' => $lockedPayment->receipt_number,
                    'bill_id' => $lockedBill->id,
                    'new_bill_paid' => $lockedBill->paid_amount,
                    'new_bill_due' => $lockedBill->due_amount,
                    'new_bill_status' => $lockedBill->status,
                    'approved_by' => $authorizer->id,
                ],
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'created_at' => Carbon::now(),
            ]);

            return $refund->fresh(['payment', 'bill.patient', 'approvedBy']);
        });
    }

    /**
     * Get paginated payment transactions with search and filters.
     */
    public function getPayments(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Payment::with(['bill.patient', 'receivedBy', 'refunds'])
            ->latest('payment_date');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('payment_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('payment_date', '<=', $filters['date_to']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get paginated refunds with search and filters.
     */
    public function getRefunds(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Refund::with(['payment', 'bill.patient', 'approvedBy'])
            ->latest('refunded_at');

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('refunded_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('refunded_at', '<=', $filters['date_to']);
        }

        return $query->paginate($perPage)->withQueryString();
    }
}

