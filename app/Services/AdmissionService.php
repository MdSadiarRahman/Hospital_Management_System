<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\AuditLog;
use App\Models\Bed;
use App\Models\BedAssignment;
use App\Models\Department;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AdmissionService
{
    public function __construct(
        protected BedManagementService $bedManagementService,
        protected VisitService $visitService
    ) {}

    /**
     * Generate the next unique Admission Number for a given date in format ADM-YYYYMMDD-XXX.
     */
    public function generateAdmissionNumber(?Carbon $date = null): string
    {
        $targetDate = $date ? $date->copy() : now();
        $datePrefix = 'ADM-' . $targetDate->format('Ymd') . '-';

        $lastAdmission = Admission::where('admission_number', 'like', $datePrefix . '%')
            ->orderByRaw('CAST(SUBSTRING(admission_number, 14) AS UNSIGNED) DESC')
            ->lockForUpdate()
            ->first();

        $nextSeq = 1;
        if ($lastAdmission) {
            $lastSeq = (int) substr($lastAdmission->admission_number, 13);
            $nextSeq = $lastSeq + 1;
        }

        $admissionNumber = $datePrefix . sprintf('%03d', $nextSeq);

        while (Admission::where('admission_number', $admissionNumber)->exists()) {
            $nextSeq++;
            $admissionNumber = $datePrefix . sprintf('%03d', $nextSeq);
        }

        return $admissionNumber;
    }

    /**
     * Create a new inpatient admission and allocate bed within an atomic transaction.
     *
     * Rules enforced:
     * - Must belong to a registered patient.
     * - Patient cannot have multiple simultaneous active admissions.
     * - Must link to a valid visit (or auto-create an inpatient visit).
     * - Verified bed availability (General or Department-wise).
     * - Concurrency & double booking prevention via row locks.
     * - Full audit logging.
     */
    public function createAdmission(array $data, User $actor): Admission
    {
        return DB::transaction(function () use ($data, $actor) {
            // 1. Verify registered patient
            $patient = Patient::findOrFail($data['patient_id']);

            // 2. Prevent duplicate active admissions for the same patient
            $activeAdmission = Admission::where('patient_id', $patient->id)
                ->where('status', 'admitted')
                ->lockForUpdate()
                ->first();

            if ($activeAdmission) {
                throw new InvalidArgumentException(
                    "Patient {$patient->name} ({$patient->patient_code}) already has an active admission (#{$activeAdmission->admission_number}). Discharge or cancel the existing admission first."
                );
            }

            // 3. Resolve and link Visit
            $visit = null;
            if (!empty($data['visit_id'])) {
                $visit = Visit::where('id', $data['visit_id'])->lockForUpdate()->firstOrFail();

                if ($visit->patient_id !== $patient->id) {
                    throw new InvalidArgumentException(
                        "The selected visit #{$visit->visit_number} belongs to another patient, not {$patient->name}."
                    );
                }
            } else {
                // Auto-create an inpatient intake visit if not explicitly chosen
                $visit = $this->visitService->createVisit([
                    'patient_id' => $patient->id,
                    'visit_type' => 'emergency',
                    'visit_date' => now(),
                    'triage_level' => 'priority',
                    'status' => 'admitted',
                ], $actor);
            }

            // 4. Verify & lock target bed (General or Department-wise)
            $bedId = $data['bed_id'];
            $lockedBed = Bed::where('id', $bedId)->lockForUpdate()->firstOrFail();

            if ($lockedBed->status !== 'available') {
                throw new InvalidArgumentException(
                    "Selected bed {$lockedBed->bed_number} is currently {$lockedBed->status} and cannot receive a new inpatient."
                );
            }

            // Prevent double bed assignment
            $activeAssignment = BedAssignment::where('bed_id', $lockedBed->id)
                ->whereNull('released_at')
                ->lockForUpdate()
                ->first();

            if ($activeAssignment) {
                throw new InvalidArgumentException(
                    "Concurrency conflict: Bed {$lockedBed->bed_number} already has an active occupant."
                );
            }

            // 5. Generate unique admission number
            $admissionDate = !empty($data['admission_date'])
                ? Carbon::parse($data['admission_date'])
                : now();
            $admissionNumber = $this->generateAdmissionNumber($admissionDate);

            // 6. Create Admission record
            $departmentId = $data['department_id'] ?? $lockedBed->department_id;

            $admission = Admission::create([
                'visit_id' => $visit->id,
                'patient_id' => $patient->id,
                'department_id' => $departmentId,
                'admission_number' => $admissionNumber,
                'admission_date' => $admissionDate,
                'attending_doctor_id' => $data['attending_doctor_id'],
                'admission_reason' => $data['admission_reason'],
                'notes' => $data['notes'] ?? null,
                'status' => 'admitted',
                'clinical_clearance' => false,
                'financial_clearance' => false,
            ]);

            // 7. Allocate Bed to Admission
            $this->bedManagementService->assignPatientToBed(
                $lockedBed,
                $admission,
                $actor,
                "Initial admission allocation: {$admission->admission_number}"
            );

            // 8. Audit log
            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'admission.created',
                'auditable_type' => Admission::class,
                'auditable_id' => $admission->id,
                'old_values' => null,
                'new_values' => [
                    'admission_number' => $admission->admission_number,
                    'patient_id' => $patient->id,
                    'patient_code' => $patient->patient_code,
                    'bed_id' => $lockedBed->id,
                    'bed_number' => $lockedBed->bed_number,
                    'department_id' => $departmentId,
                    'attending_doctor_id' => $admission->attending_doctor_id,
                ],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
                'created_at' => now(),
            ]);

            try {
                app(\App\Services\NotificationService::class)->notifyAdmissionUpdate($admission, 'created');
            } catch (\Throwable $e) {
                report($e);
            }

            return $admission->fresh(['patient', 'visit', 'department', 'attendingDoctor', 'currentBedAssignment.bed']);
        });
    }

    /**
     * Transfer an admitted patient to another bed with complete audit and transfer history.
     */
    public function transferBed(Admission $admission, Bed $targetBed, string $reason, User $actor): BedAssignment
    {
        return DB::transaction(function () use ($admission, $targetBed, $reason, $actor) {
            $lockedAdmission = Admission::where('id', $admission->id)->lockForUpdate()->firstOrFail();

            if ($lockedAdmission->status !== 'admitted') {
                throw new InvalidArgumentException(
                    "Cannot transfer bed for admission #{$lockedAdmission->admission_number} because its status is {$lockedAdmission->status}."
                );
            }

            $currentAssignment = BedAssignment::where('admission_id', $lockedAdmission->id)
                ->whereNull('released_at')
                ->lockForUpdate()
                ->first();

            if ($currentAssignment && $currentAssignment->bed_id === $targetBed->id) {
                throw new InvalidArgumentException(
                    "Patient is already occupying bed {$targetBed->bed_number}."
                );
            }

            // Assign new bed (this automatically releases previous bed and sets it to cleaning)
            $newAssignment = $this->bedManagementService->assignPatientToBed(
                $targetBed,
                $lockedAdmission,
                $actor,
                $reason
            );

            // Audit log
            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'admission.bed_transferred',
                'auditable_type' => Admission::class,
                'auditable_id' => $lockedAdmission->id,
                'old_values' => [
                    'previous_bed_id' => $currentAssignment?->bed_id,
                ],
                'new_values' => [
                    'new_bed_id' => $targetBed->id,
                    'new_bed_number' => $targetBed->bed_number,
                    'transfer_reason' => $reason,
                ],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
                'created_at' => now(),
            ]);

            return $newAssignment;
        });
    }

    /**
     * Cancel an admission (e.g. admitted in error, patient did not stay).
     * Releases any occupied bed and marks admission cancelled.
     */
    public function cancelAdmission(Admission $admission, string $reason, User $actor): Admission
    {
        return DB::transaction(function () use ($admission, $reason, $actor) {
            $lockedAdmission = Admission::where('id', $admission->id)->lockForUpdate()->firstOrFail();

            if ($lockedAdmission->status !== 'admitted') {
                throw new InvalidArgumentException(
                    "Cannot cancel admission #{$lockedAdmission->admission_number} because its status is already '{$lockedAdmission->status}'."
                );
            }

            // Release active bed if currently occupied
            $currentAssignment = BedAssignment::where('admission_id', $lockedAdmission->id)
                ->whereNull('released_at')
                ->lockForUpdate()
                ->first();

            if ($currentAssignment) {
                $bed = Bed::where('id', $currentAssignment->bed_id)->lockForUpdate()->first();
                if ($bed) {
                    $this->bedManagementService->releaseBed(
                        $bed,
                        $actor,
                        'available',
                        "Admission #{$lockedAdmission->admission_number} cancelled: {$reason}"
                    );
                }
            }

            $lockedAdmission->update([
                'status' => 'cancelled',
                'cancelled_by' => $actor->id,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ]);

            // Audit log
            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'admission.cancelled',
                'auditable_type' => Admission::class,
                'auditable_id' => $lockedAdmission->id,
                'old_values' => ['status' => 'admitted'],
                'new_values' => [
                    'status' => 'cancelled',
                    'cancellation_reason' => $reason,
                    'cancelled_by' => $actor->id,
                ],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
                'created_at' => now(),
            ]);

            try {
                app(\App\Services\NotificationService::class)->notifyAdmissionUpdate($lockedAdmission, 'cancelled');
            } catch (\Throwable $e) {
                report($e);
            }

            return $lockedAdmission->fresh();
        });
    }

    /**
     * Get paginated admissions directory with search and filtering.
     */
    public function getAdmissionsDirectory(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Admission::query()
            ->with(['patient', 'visit', 'department', 'attendingDoctor', 'currentBedAssignment.bed'])
            ->search($filters['search'] ?? null)
            ->filter($filters)
            ->latest('admission_date')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get real-time admission and inpatient metrics for Bento dashboard cards.
     */
    public function getAdmissionMetrics(): array
    {
        $totalAdmissions = Admission::count();
        $activeInpatients = Admission::where('status', 'admitted')->count();
        $dischargedCount = Admission::where('status', 'discharged')->count();
        $cancelledCount = Admission::where('status', 'cancelled')->count();
        $admittedToday = Admission::whereDate('admission_date', today())->count();

        $totalBeds = Bed::count();
        $occupancyRate = $totalBeds > 0 ? round(($activeInpatients / $totalBeds) * 100, 1) : 0;

        return [
            'total_admissions' => $totalAdmissions,
            'active_inpatients' => $activeInpatients,
            'discharged_count' => $dischargedCount,
            'cancelled_count' => $cancelledCount,
            'admitted_today' => $admittedToday,
            'total_beds' => $totalBeds,
            'occupancy_rate' => $occupancyRate,
        ];
    }

    /**
     * Query available beds for admission selection.
     * Supports both General beds and Department-wise beds.
     */
    public function getAvailableBeds(?int $departmentId = null, ?string $category = null): Collection
    {
        return Bed::query()
            ->where('status', 'available')
            ->when($departmentId, function ($query) use ($departmentId) {
                // Include beds belonging to this department, OR General beds
                $query->where(function ($sub) use ($departmentId) {
                    $sub->where('department_id', $departmentId)
                        ->orWhereNull('department_id')
                        ->orWhere('category', 'general');
                });
            })
            ->when($category, fn($q) => $q->where('category', $category))
            ->with('department')
            ->orderBy('bed_number')
            ->get();
    }
}
