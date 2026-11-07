<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\AuditLog;
use App\Models\Bed;
use App\Models\BedAssignment;
use App\Models\Bill;
use App\Models\LabReport;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Core immutable audit logger with automatic data scrubbing.
     */
    public function log(
        string $event,
        Model|string|null $auditable = null,
        ?array $newValues = null,
        ?array $oldValues = null,
        ?User $actor = null,
        ?string $ip = null,
        ?string $userAgent = null
    ): AuditLog {
        $actorId = $actor ? $actor->id : Auth::id();
        $ip = $ip ?? Request::ip();
        $userAgent = $userAgent ?? Request::userAgent();

        $auditableType = null;
        $auditableId = null;

        if ($auditable instanceof Model) {
            $auditableType = get_class($auditable);
            $auditableId = $auditable->getKey();
        } elseif (is_string($auditable)) {
            $auditableType = $auditable;
        }

        return AuditLog::create([
            'user_id' => $actorId,
            'event' => $event,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'created_at' => now(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Specific Audit Event Loggers (All 12 Mandated Domains)
    |--------------------------------------------------------------------------
    */

    public function logUserLogin(User $user, bool $success = true, ?string $reason = null): AuditLog
    {
        return $this->log(
            event: $success ? 'user.login' : 'user.login_failed',
            auditable: $user,
            newValues: [
                'email' => $user->email,
                'user_type' => $user->user_type,
                'status' => $success ? 'success' : 'failed',
                'reason' => $reason,
            ],
            actor: $success ? $user : null
        );
    }

    public function logUserCreation(User $createdUser, ?User $actor = null): AuditLog
    {
        return $this->log(
            event: 'user.created',
            auditable: $createdUser,
            newValues: [
                'id' => $createdUser->id,
                'name' => $createdUser->name,
                'email' => $createdUser->email,
                'user_type' => $createdUser->user_type,
                'is_active' => $createdUser->is_active,
            ],
            actor: $actor
        );
    }

    public function logRoleChange(User $targetUser, array $newRoles, array $oldRoles, ?User $actor = null): AuditLog
    {
        return $this->log(
            event: 'role.changed',
            auditable: $targetUser,
            newValues: ['roles' => $newRoles],
            oldValues: ['roles' => $oldRoles],
            actor: $actor
        );
    }

    public function logPatientChange(Patient $patient, string $action, ?array $oldValues = null, ?array $newValues = null, ?User $actor = null): AuditLog
    {
        return $this->log(
            event: "patient.{$action}",
            auditable: $patient,
            newValues: $newValues ?? [
                'patient_code' => $patient->patient_code,
                'name' => $patient->name,
                'phone' => $patient->phone,
            ],
            oldValues: $oldValues,
            actor: $actor
        );
    }

    public function logAdmissionChange(Admission $admission, string $action, ?array $oldValues = null, ?array $newValues = null, ?User $actor = null): AuditLog
    {
        return $this->log(
            event: "admission.{$action}",
            auditable: $admission,
            newValues: $newValues ?? [
                'admission_number' => $admission->admission_number,
                'status' => $admission->status,
                'department_id' => $admission->department_id,
            ],
            oldValues: $oldValues,
            actor: $actor
        );
    }

    public function logBedTransfer(BedAssignment $assignment, Bed $fromBed, Bed $toBed, ?User $actor = null): AuditLog
    {
        return $this->log(
            event: 'bed.transferred',
            auditable: $assignment,
            newValues: [
                'admission_id' => $assignment->admission_id,
                'new_bed_id' => $toBed->id,
                'new_bed_number' => $toBed->bed_number,
                'transfer_reason' => $assignment->transfer_reason,
            ],
            oldValues: [
                'previous_bed_id' => $fromBed->id,
                'previous_bed_number' => $fromBed->bed_number,
            ],
            actor: $actor
        );
    }

    public function logClinicalRecordChange(Model $record, string $action, ?array $oldValues = null, ?array $newValues = null, ?User $actor = null): AuditLog
    {
        return $this->log(
            event: "clinical.{$action}",
            auditable: $record,
            newValues: $newValues,
            oldValues: $oldValues,
            actor: $actor
        );
    }

    public function logLabReportUpload(LabReport $report, string $action = 'uploaded', ?User $actor = null): AuditLog
    {
        return $this->log(
            event: "lab_report.{$action}",
            auditable: $report,
            newValues: [
                'report_number' => $report->report_number,
                'lab_order_id' => $report->lab_order_id,
                'report_status' => $report->report_status,
                'review_status' => $report->review_status,
            ],
            actor: $actor
        );
    }

    public function logBillingChange(Bill $bill, string $action, ?array $oldValues = null, ?array $newValues = null, ?User $actor = null): AuditLog
    {
        return $this->log(
            event: "billing.{$action}",
            auditable: $bill,
            newValues: $newValues ?? [
                'invoice_number' => $bill->invoice_number,
                'total_amount' => $bill->total_amount,
                'paid_amount' => $bill->paid_amount,
                'due_amount' => $bill->due_amount,
                'status' => $bill->status,
            ],
            oldValues: $oldValues,
            actor: $actor
        );
    }

    public function logPaymentRecord(Payment $payment, string $action = 'recorded', ?User $actor = null): AuditLog
    {
        return $this->log(
            event: "payment.{$action}",
            auditable: $payment,
            newValues: [
                'receipt_number' => $payment->receipt_number,
                'bill_id' => $payment->bill_id,
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'status' => $payment->status,
            ],
            actor: $actor
        );
    }

    public function logRefund(Refund $refund, string $action = 'issued', ?User $actor = null): AuditLog
    {
        return $this->log(
            event: "refund.{$action}",
            auditable: $refund,
            newValues: [
                'refund_voucher' => $refund->refund_voucher,
                'bill_id' => $refund->bill_id,
                'amount' => $refund->amount,
                'refund_method' => $refund->refund_method,
                'reason' => $refund->reason,
            ],
            actor: $actor
        );
    }

    public function logDischargeApproval(Admission $admission, string $stage, ?User $actor = null): AuditLog
    {
        return $this->log(
            event: "discharge.{$stage}",
            auditable: $admission,
            newValues: [
                'admission_number' => $admission->admission_number,
                'patient_id' => $admission->patient_id,
                'status' => $admission->status,
                'clinical_clearance' => $admission->clinical_clearance,
                'nursing_clearance' => $admission->nursing_clearance,
                'financial_clearance' => $admission->financial_clearance,
            ],
            actor: $actor
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Reporting & Retrieval
    |--------------------------------------------------------------------------
    */

    public function getPaginatedLogs(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return AuditLog::with('user')
            ->filter($filters)
            ->latest('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getDistinctEvents(): array
    {
        return AuditLog::select('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event')
            ->toArray();
    }

    public function getDistinctAuditableTypes(): array
    {
        return AuditLog::select('auditable_type')
            ->whereNotNull('auditable_type')
            ->distinct()
            ->orderBy('auditable_type')
            ->pluck('auditable_type')
            ->toArray();
    }
}

