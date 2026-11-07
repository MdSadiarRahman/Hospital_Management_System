<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\AuditLog;
use App\Models\Bed;
use App\Models\BedAssignment;
use App\Models\Bill;
use App\Models\DischargeHistory;
use App\Models\DischargeSummary;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DischargeService
{
    public function __construct(
        protected BedManagementService $bedManagementService,
        protected BillingService $billingService
    ) {}

    /**
     * Grant doctor clinical clearance and author/update discharge summary.
     * Enforces clinical authority and records complete audit trail.
     */
    public function grantClinicalClearance(Admission $admission, array $data, User $doctor): Admission
    {
        if (!$this->canAuthorizeClinicalClearance($doctor)) {
            throw new AuthorizationException("You are not authorized to grant clinical discharge clearance.");
        }

        return DB::transaction(function () use ($admission, $data, $doctor) {
            /** @var Admission $lockedAdmission */
            $lockedAdmission = Admission::where('id', $admission->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedAdmission->status !== 'admitted') {
                throw new InvalidArgumentException("Cannot grant clinical clearance: Admission #{$lockedAdmission->admission_number} is already '{$lockedAdmission->status}'.");
            }

            // Create or update Discharge Summary
            $summary = DischargeSummary::updateOrCreate(
                ['admission_id' => $lockedAdmission->id],
                [
                    'discharge_diagnosis' => trim($data['discharge_diagnosis']),
                    'treatment_given' => trim($data['treatment_given']),
                    'hospital_course' => !empty($data['hospital_course']) ? trim($data['hospital_course']) : null,
                    'investigations_summary' => !empty($data['investigations_summary']) ? trim($data['investigations_summary']) : null,
                    'condition_at_discharge' => $data['condition_at_discharge'] ?? 'improved',
                    'discharge_medications' => trim($data['discharge_medications']),
                    'follow_up_instructions' => !empty($data['follow_up_instructions']) ? trim($data['follow_up_instructions']) : null,
                    'authorized_by' => $doctor->id,
                ]
            );

            $lockedAdmission->update([
                'clinical_clearance' => true,
                'clinical_clearance_at' => Carbon::now(),
                'clinical_clearance_by' => $doctor->id,
            ]);

            DischargeHistory::create([
                'admission_id' => $lockedAdmission->id,
                'patient_id' => $lockedAdmission->patient_id,
                'clearance_type' => 'clinical',
                'action' => 'clinical_clearance_granted',
                'status_before' => $lockedAdmission->status,
                'status_after' => $lockedAdmission->status,
                'remarks' => "Clinical clearance granted. Condition: {$summary->condition_at_discharge}. Diagnosis: {$summary->discharge_diagnosis}",
                'performed_by' => $doctor->id,
                'created_at' => Carbon::now(),
            ]);

            AuditLog::create([
                'user_id' => $doctor->id,
                'event' => 'discharge.clinical_clearance_granted',
                'auditable_type' => Admission::class,
                'auditable_id' => $lockedAdmission->id,
                'old_values' => ['clinical_clearance' => false],
                'new_values' => [
                    'clinical_clearance' => true,
                    'condition_at_discharge' => $summary->condition_at_discharge,
                    'doctor_id' => $doctor->id,
                ],
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'created_at' => Carbon::now(),
            ]);

            return $lockedAdmission->fresh(['dischargeSummary', 'clinicalClearanceBy', 'dischargeHistories.performedBy']);
        });
    }

    /**
     * Grant nursing discharge clearance (cannula/catheter removed, vitals stable, discharge MAR checked).
     */
    public function grantNursingClearance(Admission $admission, array $data, User $nurse): Admission
    {
        if (!$this->canAuthorizeNursingClearance($nurse)) {
            throw new AuthorizationException("You are not authorized to grant nursing discharge clearance.");
        }

        return DB::transaction(function () use ($admission, $data, $nurse) {
            /** @var Admission $lockedAdmission */
            $lockedAdmission = Admission::where('id', $admission->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedAdmission->status !== 'admitted') {
                throw new InvalidArgumentException("Cannot grant nursing clearance: Admission #{$lockedAdmission->admission_number} is already '{$lockedAdmission->status}'.");
            }

            $notes = !empty($data['notes'])
                ? trim($data['notes'])
                : 'Nursing discharge checklist verified: IV cannula removed, catheter removed, stable vitals, belongings handed over.';

            $lockedAdmission->update([
                'nursing_clearance' => true,
                'nursing_clearance_at' => Carbon::now(),
                'nursing_clearance_by' => $nurse->id,
                'nursing_clearance_notes' => $notes,
            ]);

            DischargeHistory::create([
                'admission_id' => $lockedAdmission->id,
                'patient_id' => $lockedAdmission->patient_id,
                'clearance_type' => 'nursing',
                'action' => 'nursing_clearance_granted',
                'status_before' => $lockedAdmission->status,
                'status_after' => $lockedAdmission->status,
                'remarks' => $notes,
                'performed_by' => $nurse->id,
                'created_at' => Carbon::now(),
            ]);

            AuditLog::create([
                'user_id' => $nurse->id,
                'event' => 'discharge.nursing_clearance_granted',
                'auditable_type' => Admission::class,
                'auditable_id' => $lockedAdmission->id,
                'old_values' => ['nursing_clearance' => false],
                'new_values' => [
                    'nursing_clearance' => true,
                    'nurse_id' => $nurse->id,
                    'notes' => $notes,
                ],
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'created_at' => Carbon::now(),
            ]);

            return $lockedAdmission->fresh(['nursingClearanceBy', 'dischargeHistories.performedBy']);
        });
    }

    /**
     * Grant financial discharge clearance.
     * Enforces payment reconciliation and strict dues exception policy.
     */
    public function grantFinancialClearance(Admission $admission, array $data, User $cashier): Admission
    {
        if (!$this->canAuthorizeFinancialClearance($cashier)) {
            throw new AuthorizationException("You are not authorized to grant financial discharge clearance.");
        }

        return DB::transaction(function () use ($admission, $data, $cashier) {
            /** @var Admission $lockedAdmission */
            $lockedAdmission = Admission::with(['bill.payments', 'bill.refunds'])
                ->where('id', $admission->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedAdmission->status !== 'admitted') {
                throw new InvalidArgumentException("Cannot grant financial clearance: Admission #{$lockedAdmission->admission_number} is already '{$lockedAdmission->status}'.");
            }

            $bill = $lockedAdmission->bill;

            if (!$bill) {
                throw new InvalidArgumentException("Cannot grant financial clearance: Final inpatient bill has not been generated yet for this admission.");
            }

            // Ensure bill totals are freshly reconciled
            $bill->recalculateTotals();
            $bill->save();

            $dueAmount = (string) $bill->due_amount;
            $hasDue = bccomp($dueAmount, '0.00', 2) > 0;

            $exceptionAllowed = false;
            $exceptionReason = null;

            if ($hasDue) {
                // Outstanding dues must follow an explicitly authorized exception policy, not an automatic bypass.
                $requestedException = !empty($data['allow_dues_exception']) && (bool) $data['allow_dues_exception'];

                if (!$requestedException) {
                    throw new InvalidArgumentException("Financial clearance blocked: Invoice #{$bill->invoice_number} has an outstanding due balance of BDT {$dueAmount}. All dues must be paid or an authorized exception must be granted.");
                }

                if (!$this->canAuthorizeDuesException($cashier)) {
                    throw new AuthorizationException("You do not have administrative authority to grant a financial dues exception for patient discharge.");
                }

                if (empty($data['exception_reason']) || trim($data['exception_reason']) === '') {
                    throw new InvalidArgumentException("A valid administrative justification reason is required to permit discharge with outstanding dues.");
                }

                $exceptionAllowed = true;
                $exceptionReason = trim($data['exception_reason']);
            }

            $lockedAdmission->update([
                'financial_clearance' => true,
                'financial_clearance_at' => Carbon::now(),
                'financial_clearance_by' => $cashier->id,
                'financial_exception_allowed' => $exceptionAllowed,
                'financial_exception_reason' => $exceptionReason,
                'financial_exception_by' => $exceptionAllowed ? $cashier->id : null,
            ]);

            $action = $exceptionAllowed ? 'financial_exception_granted' : 'financial_clearance_granted';
            $remarks = $exceptionAllowed
                ? "Discharge financial clearance granted under authorized exception for outstanding due of BDT {$dueAmount}. Reason: {$exceptionReason}"
                : "Financial clearance granted. All charges fully paid and settled.";

            DischargeHistory::create([
                'admission_id' => $lockedAdmission->id,
                'patient_id' => $lockedAdmission->patient_id,
                'clearance_type' => 'financial',
                'action' => $action,
                'status_before' => $lockedAdmission->status,
                'status_after' => $lockedAdmission->status,
                'remarks' => $remarks,
                'performed_by' => $cashier->id,
                'created_at' => Carbon::now(),
            ]);

            AuditLog::create([
                'user_id' => $cashier->id,
                'event' => 'discharge.' . $action,
                'auditable_type' => Admission::class,
                'auditable_id' => $lockedAdmission->id,
                'old_values' => ['financial_clearance' => false],
                'new_values' => [
                    'financial_clearance' => true,
                    'exception_allowed' => $exceptionAllowed,
                    'due_amount' => $dueAmount,
                    'cashier_id' => $cashier->id,
                ],
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'created_at' => Carbon::now(),
            ]);

            return $lockedAdmission->fresh(['bill', 'financialClearanceBy', 'financialExceptionBy', 'dischargeHistories.performedBy']);
        });
    }

    /**
     * Approve final patient discharge, release occupied bed to 'cleaning', and finalize encounter.
     * Strictly requires both Clinical Clearance (with Discharge Summary) and Financial Clearance.
     */
    public function approveFinalDischarge(Admission $admission, array $data, User $actor): Admission
    {
        if (!$this->canApproveDischarge($actor)) {
            throw new AuthorizationException("You are not authorized to execute final discharge approval.");
        }

        return DB::transaction(function () use ($admission, $data, $actor) {
            /** @var Admission $lockedAdmission */
            $lockedAdmission = Admission::with(['dischargeSummary', 'currentBedAssignment.bed', 'bill'])
                ->where('id', $admission->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedAdmission->status !== 'admitted') {
                throw new InvalidArgumentException("Cannot approve discharge: Admission #{$lockedAdmission->admission_number} is already '{$lockedAdmission->status}'.");
            }

            // Rule 1: Clinical clearance must be verified
            if (!$lockedAdmission->clinical_clearance || !$lockedAdmission->dischargeSummary) {
                throw new InvalidArgumentException("Cannot execute discharge: Authorized Clinical Clearance and complete Discharge Summary are mandatory.");
            }

            // Rule 2: Financial clearance must be verified separately
            if (!$lockedAdmission->financial_clearance) {
                throw new InvalidArgumentException("Cannot execute discharge: Financial Clearance must be completed and authorized before patient exit.");
            }

            // Bed Release: Transactional bed release placing bed into 'cleaning' status
            $currentAssignment = BedAssignment::where('admission_id', $lockedAdmission->id)
                ->whereNull('released_at')
                ->lockForUpdate()
                ->first();

            $releasedBedNumber = null;
            if ($currentAssignment) {
                $bed = Bed::where('id', $currentAssignment->bed_id)->lockForUpdate()->first();
                if ($bed) {
                    $releasedBedNumber = $bed->bed_number;
                    // Release bed and place directly into 'cleaning' status for terminal sanitization
                    $this->bedManagementService->releaseBed(
                        $bed,
                        $actor,
                        'cleaning',
                        "Patient discharged from admission #{$lockedAdmission->admission_number}. Terminal sanitization required."
                    );
                }
            }

            $lockedAdmission->update([
                'status' => 'discharged',
                'discharge_date' => Carbon::now(),
                'discharged_by' => $actor->id,
                'notes' => !empty($data['notes']) ? trim($data['notes']) : $lockedAdmission->notes,
            ]);

            $bedInfo = $releasedBedNumber ? "Bed {$releasedBedNumber} vacated and transitioned to 'cleaning' status." : "No bed assignment active.";
            $remarks = "Final discharge executed and approved. " . $bedInfo;

            DischargeHistory::create([
                'admission_id' => $lockedAdmission->id,
                'patient_id' => $lockedAdmission->patient_id,
                'clearance_type' => 'final_approval',
                'action' => 'final_discharge_approved',
                'status_before' => 'admitted',
                'status_after' => 'discharged',
                'remarks' => $remarks,
                'performed_by' => $actor->id,
                'created_at' => Carbon::now(),
            ]);

            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'admission.discharged',
                'auditable_type' => Admission::class,
                'auditable_id' => $lockedAdmission->id,
                'old_values' => ['status' => 'admitted'],
                'new_values' => [
                    'status' => 'discharged',
                    'discharge_date' => $lockedAdmission->discharge_date,
                    'discharged_by' => $actor->id,
                    'released_bed' => $releasedBedNumber,
                ],
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'created_at' => Carbon::now(),
            ]);

            return $lockedAdmission->fresh([
                'patient',
                'attendingDoctor',
                'department',
                'dischargeSummary',
                'bill',
                'dischargedBy',
                'dischargeHistories.performedBy',
            ]);
        });
    }

    /**
     * Revoke a clearance before final discharge in case of clinical change or new billing items.
     */
    public function revokeClearance(Admission $admission, string $type, string $reason, User $actor): Admission
    {
        return DB::transaction(function () use ($admission, $type, $reason, $actor) {
            /** @var Admission $lockedAdmission */
            $lockedAdmission = Admission::where('id', $admission->id)->lockForUpdate()->firstOrFail();

            if ($lockedAdmission->status !== 'admitted') {
                throw new InvalidArgumentException("Cannot revoke clearance on already discharged or cancelled admission.");
            }

            if ($type === 'clinical') {
                if (!$this->canAuthorizeClinicalClearance($actor)) {
                    throw new AuthorizationException("You are not authorized to revoke clinical clearance.");
                }
                $lockedAdmission->update([
                    'clinical_clearance' => false,
                    'clinical_clearance_at' => null,
                    'clinical_clearance_by' => null,
                ]);
            } elseif ($type === 'financial') {
                if (!$this->canAuthorizeFinancialClearance($actor)) {
                    throw new AuthorizationException("You are not authorized to revoke financial clearance.");
                }
                $lockedAdmission->update([
                    'financial_clearance' => false,
                    'financial_clearance_at' => null,
                    'financial_clearance_by' => null,
                    'financial_exception_allowed' => false,
                    'financial_exception_reason' => null,
                    'financial_exception_by' => null,
                ]);
            } elseif ($type === 'nursing') {
                if (!$this->canAuthorizeNursingClearance($actor)) {
                    throw new AuthorizationException("You are not authorized to revoke nursing clearance.");
                }
                $lockedAdmission->update([
                    'nursing_clearance' => false,
                    'nursing_clearance_at' => null,
                    'nursing_clearance_by' => null,
                    'nursing_clearance_notes' => null,
                ]);
            } else {
                throw new InvalidArgumentException("Invalid clearance type '{$type}'.");
            }

            DischargeHistory::create([
                'admission_id' => $lockedAdmission->id,
                'patient_id' => $lockedAdmission->patient_id,
                'clearance_type' => 'reversal',
                'action' => "{$type}_clearance_revoked",
                'status_before' => 'admitted',
                'status_after' => 'admitted',
                'remarks' => "Clearance revoked: " . trim($reason),
                'performed_by' => $actor->id,
                'created_at' => Carbon::now(),
            ]);

            AuditLog::create([
                'user_id' => $actor->id,
                'event' => "discharge.{$type}_clearance_revoked",
                'auditable_type' => Admission::class,
                'auditable_id' => $lockedAdmission->id,
                'old_values' => ["{$type}_clearance" => true],
                'new_values' => [
                    "{$type}_clearance" => false,
                    'reason' => $reason,
                ],
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'created_at' => Carbon::now(),
            ]);

            return $lockedAdmission->fresh();
        });
    }

    /**
     * Get paginated discharge workbench with clearance tracking.
     */
    public function getDischargeWorkbench(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Admission::with([
            'patient',
            'department',
            'attendingDoctor',
            'currentBedAssignment.bed',
            'bill',
            'dischargeSummary',
            'clinicalClearanceBy',
            'nursingClearanceBy',
            'financialClearanceBy',
        ]);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        } else {
            // Default to admitted and recently discharged
            $query->whereIn('status', ['admitted', 'discharged']);
        }

        if (!empty($filters['clearance_filter'])) {
            if ($filters['clearance_filter'] === 'ready') {
                $query->where('status', 'admitted')
                    ->where('clinical_clearance', true)
                    ->where('financial_clearance', true);
            } elseif ($filters['clearance_filter'] === 'pending_clinical') {
                $query->where('status', 'admitted')->where('clinical_clearance', false);
            } elseif ($filters['clearance_filter'] === 'pending_financial') {
                $query->where('status', 'admitted')->where('financial_clearance', false);
            }
        }

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        return $query->latest('admission_date')->paginate($perPage)->withQueryString();
    }

    /*
    |--------------------------------------------------------------------------
    | Authorization Checks
    |--------------------------------------------------------------------------
    */

    public function canAuthorizeClinicalClearance(User $user): bool
    {
        return $user->hasRole(['doctor', 'administrator', 'super_admin']) ||
            $user->hasPermission('clinical.clearance');
    }

    public function canAuthorizeNursingClearance(User $user): bool
    {
        return $user->hasRole(['nurse', 'doctor', 'administrator', 'super_admin']) ||
            $user->hasPermission('nursing.record');
    }

    public function canAuthorizeFinancialClearance(User $user): bool
    {
        return $user->hasRole(['billing_staff', 'administrator', 'super_admin']) ||
            $user->hasPermission('billing.financial_clearance');
    }

    public function canAuthorizeDuesException(User $user): bool
    {
        return $user->hasRole(['administrator', 'super_admin']) ||
            $user->hasPermission('billing.approve');
    }

    public function canApproveDischarge(User $user): bool
    {
        return $user->hasRole(['doctor', 'administrator', 'super_admin', 'billing_staff']);
    }
}

