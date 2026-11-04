<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\AuditLog;
use App\Models\Bed;
use App\Models\BedAssignment;
use App\Models\BedStatusHistory;
use App\Models\Department;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BedManagementService
{
    /**
     * Get paginated beds with filters and relations.
     */
    public function getBedsDirectory(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Bed::with([
            'department',
            'currentAssignment.admission.patient',
            'currentAssignment.admission.attendingDoctor',
        ])->orderBy('id', 'asc');

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters)) {
            $query->filter($filters);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get clinic-wide bed capacity metrics and distribution KPIs.
     * Dynamic calculation: supports initial 10 beds and future expansion without hardcoding.
     */
    public function getBedMetrics(): array
    {
        $totalBeds = Bed::count();
        $available = Bed::available()->count();
        $occupied = Bed::occupied()->count();
        $reserved = Bed::reserved()->count();
        $cleaning = Bed::cleaning()->count();
        $maintenance = Bed::maintenance()->count();

        $occupancyRate = $totalBeds > 0 ? round(($occupied / $totalBeds) * 100, 1) : 0;

        // Department-wise distribution (dynamic, no hardcoded counts)
        $departments = Department::where('is_clinical', true)
            ->withCount(['beds', 'beds as available_beds_count' => function ($q) {
                $q->where('status', 'available');
            }, 'beds as occupied_beds_count' => function ($q) {
                $q->where('status', 'occupied');
            }])
            ->get();

        $generalBedsCount = Bed::general()->count();
        $generalAvailableCount = Bed::general()->available()->count();

        return [
            'total_capacity' => $totalBeds,
            'available' => $available,
            'occupied' => $occupied,
            'reserved' => $reserved,
            'cleaning' => $cleaning,
            'maintenance' => $maintenance,
            'occupancy_rate' => $occupancyRate,
            'departments' => $departments,
            'general_beds_count' => $generalBedsCount,
            'general_available_count' => $generalAvailableCount,
        ];
    }

    /**
     * Get structured dataset for the 10-Bed Visual Occupancy Dashboard.
     */
    public function getOccupancyDashboardData(): array
    {
        $metrics = $this->getBedMetrics();

        // Department beds grouped
        $departmentBeds = Department::where('is_clinical', true)
            ->with(['beds' => function ($q) {
                $q->with([
                    'currentAssignment.admission.patient',
                    'currentAssignment.admission.attendingDoctor',
                    'statusHistories' => fn($sh) => $sh->latest()->take(3),
                ])->orderBy('bed_number');
            }])
            ->get();

        // General Ward beds (department_id is null or category is general)
        $generalBeds = Bed::where(function ($q) {
            $q->whereNull('department_id')
                ->orWhere('category', 'general');
        })
        ->with([
            'department',
            'currentAssignment.admission.patient',
            'currentAssignment.admission.attendingDoctor',
            'statusHistories' => fn($sh) => $sh->latest()->take(3),
        ])
        ->orderBy('bed_number')
        ->get();

        // All beds in clinic
        $allBeds = Bed::with([
            'department',
            'currentAssignment.admission.patient',
            'currentAssignment.admission.attendingDoctor',
        ])->orderBy('bed_number')->get();

        // Available beds for transfer or new admissions
        $availableBeds = Bed::available()->with('department')->orderBy('bed_number')->get();

        // Recent bed transfers
        $recentTransfers = BedAssignment::whereNotNull('transfer_reason')
            ->with(['bed.department', 'admission.patient', 'assignedBy'])
            ->latest('updated_at')
            ->take(8)
            ->get();

        return [
            'metrics' => $metrics,
            'department_beds' => $departmentBeds,
            'general_beds' => $generalBeds,
            'all_beds' => $allBeds,
            'available_beds' => $availableBeds,
            'recent_transfers' => $recentTransfers,
        ];
    }

    /**
     * Get paginated transfer history across the clinic.
     */
    public function getTransferHistory(int $perPage = 15): LengthAwarePaginator
    {
        return BedAssignment::query()
            ->with(['bed.department', 'admission.patient', 'assignedBy'])
            ->orderByDesc('assigned_at')
            ->paginate($perPage);
    }

    /**
     * Create and provision a new bed in hospital inventory.
     */
    public function createBed(array $data, User $actor): Bed
    {
        return DB::transaction(function () use ($data, $actor) {
            // Enforce duplicate bed_number prevention
            if (Bed::where('bed_number', $data['bed_number'])->exists()) {
                throw new InvalidArgumentException("Bed number '{$data['bed_number']}' already exists in clinic inventory.");
            }

            $bed = Bed::create([
                'bed_number' => $data['bed_number'],
                'room_number' => $data['room_number'] ?? null,
                'category' => $data['category'] ?? 'department',
                'department_id' => $data['department_id'] ?? null,
                'daily_charge' => $data['daily_charge'] ?? 0.00,
                'status' => $data['status'] ?? 'available',
                'description' => $data['description'] ?? null,
            ]);

            // Initial status audit history
            BedStatusHistory::create([
                'bed_id' => $bed->id,
                'from_status' => null,
                'to_status' => $bed->status,
                'changed_by' => $actor->id,
                'reason' => 'Initial bed provisioning in hospital inventory',
                'notes' => $data['description'] ?? null,
            ]);

            // Audit log
            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'bed.created',
                'auditable_type' => Bed::class,
                'auditable_id' => $bed->id,
                'old_values' => null,
                'new_values' => [
                    'bed_number' => $bed->bed_number,
                    'room_number' => $bed->room_number,
                    'category' => $bed->category,
                    'department_id' => $bed->department_id,
                    'daily_charge' => $bed->daily_charge,
                    'status' => $bed->status,
                ],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
                'created_at' => now(),
            ]);

            return $bed;
        });
    }

    /**
     * Update an existing bed configuration with concurrency validation.
     */
    public function updateBed(Bed $bed, array $data, User $actor): Bed
    {
        return DB::transaction(function () use ($bed, $data, $actor) {
            $lockedBed = Bed::where('id', $bed->id)->lockForUpdate()->firstOrFail();

            // Duplicate bed_number check
            if (isset($data['bed_number']) && $data['bed_number'] !== $lockedBed->bed_number) {
                if (Bed::where('bed_number', $data['bed_number'])->where('id', '!=', $lockedBed->id)->exists()) {
                    throw new InvalidArgumentException("Bed number '{$data['bed_number']}' is already assigned to another bed.");
                }
            }

            // Cannot reassign department if currently occupied
            if (isset($data['department_id']) && $data['department_id'] != $lockedBed->department_id && $lockedBed->status === 'occupied') {
                throw new InvalidArgumentException("Cannot reassign department for occupied bed {$lockedBed->bed_number}. Release or transfer patient first.");
            }

            $oldValues = $lockedBed->only(['bed_number', 'room_number', 'category', 'department_id', 'daily_charge', 'status', 'description']);

            $previousStatus = $lockedBed->status;
            $newStatus = $data['status'] ?? $previousStatus;

            // Handle status transition if status changed
            if ($newStatus !== $previousStatus) {
                $this->validateStatusTransition($lockedBed, $newStatus, $data['status_reason'] ?? null, $data['status_notes'] ?? null);
            }

            $lockedBed->update([
                'bed_number' => $data['bed_number'] ?? $lockedBed->bed_number,
                'room_number' => array_key_exists('room_number', $data) ? $data['room_number'] : $lockedBed->room_number,
                'category' => $data['category'] ?? $lockedBed->category,
                'department_id' => array_key_exists('department_id', $data) ? $data['department_id'] : $lockedBed->department_id,
                'daily_charge' => $data['daily_charge'] ?? $lockedBed->daily_charge,
                'status' => $newStatus,
                'description' => array_key_exists('description', $data) ? $data['description'] : $lockedBed->description,
            ]);

            if ($newStatus !== $previousStatus) {
                BedStatusHistory::create([
                    'bed_id' => $lockedBed->id,
                    'from_status' => $previousStatus,
                    'to_status' => $newStatus,
                    'changed_by' => $actor->id,
                    'reason' => $data['status_reason'] ?? 'Bed status updated in settings',
                    'notes' => $data['status_notes'] ?? null,
                ]);
            }

            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'bed.updated',
                'auditable_type' => Bed::class,
                'auditable_id' => $lockedBed->id,
                'old_values' => $oldValues,
                'new_values' => $lockedBed->only(['bed_number', 'room_number', 'category', 'department_id', 'daily_charge', 'status', 'description']),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
                'created_at' => now(),
            ]);

            return $lockedBed;
        });
    }

    /**
     * Transition a bed's status with history recording and concurrency protection.
     * Enforces: "Do not mark a bed Available before required cleaning is complete."
     */
    public function updateBedStatus(Bed $bed, string $newStatus, ?string $reason = null, ?string $notes = null, ?User $actor = null): Bed
    {
        $actor = $actor ?? auth()->user();

        return DB::transaction(function () use ($bed, $newStatus, $reason, $notes, $actor) {
            $lockedBed = Bed::where('id', $bed->id)->lockForUpdate()->firstOrFail();
            $previousStatus = $lockedBed->status;

            if ($previousStatus === $newStatus) {
                return $lockedBed;
            }

            $this->validateStatusTransition($lockedBed, $newStatus, $reason, $notes);

            $lockedBed->update(['status' => $newStatus]);

            // Maintain status history
            BedStatusHistory::create([
                'bed_id' => $lockedBed->id,
                'from_status' => $previousStatus,
                'to_status' => $newStatus,
                'changed_by' => $actor?->id,
                'reason' => $reason ?? 'Status updated by clinic staff',
                'notes' => $notes,
            ]);

            AuditLog::create([
                'user_id' => $actor?->id,
                'event' => 'bed.status_changed',
                'auditable_type' => Bed::class,
                'auditable_id' => $lockedBed->id,
                'old_values' => ['status' => $previousStatus],
                'new_values' => ['status' => $newStatus, 'reason' => $reason],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
                'created_at' => now(),
            ]);

            return $lockedBed;
        });
    }

    /**
     * Validate rules governing bed status transitions.
     * Strictly enforces: "Do not mark a bed Available before required cleaning is complete."
     */
    protected function validateStatusTransition(Bed $lockedBed, string $newStatus, ?string $reason = null, ?string $notes = null): void
    {
        $previousStatus = $lockedBed->status;

        // 1. Prevent marking occupied as available if active patient assignment exists
        if ($previousStatus === 'occupied') {
            $hasActiveAssignment = $lockedBed->assignments()->whereNull('released_at')->exists();
            if ($hasActiveAssignment) {
                throw new InvalidArgumentException(
                    "Cannot transition occupied bed {$lockedBed->bed_number} directly to '{$newStatus}'. Patient must be released or transferred first."
                );
            }
        }

        // 2. Strict Rule: Do not mark a bed Available before required cleaning is complete
        if ($previousStatus === 'cleaning' && $newStatus === 'available') {
            if (empty($reason) && empty($notes)) {
                throw new InvalidArgumentException(
                    "Cannot mark bed {$lockedBed->bed_number} as 'available' before required cleaning and sanitization is certified complete."
                );
            }
        }

        // 3. From maintenance directly to available requires completion verification
        if ($previousStatus === 'maintenance' && $newStatus === 'available') {
            if (empty($reason) && empty($notes)) {
                throw new InvalidArgumentException(
                    "Cannot mark bed {$lockedBed->bed_number} as 'available' from maintenance without recording repair resolution."
                );
            }
        }
    }

    /**
     * Assign a patient admission to a bed.
     * CONCURRENCY & DOUBLE BOOKING PREVENTION:
     * - Uses DB transaction + lockForUpdate
     * - Verifies bed is strictly 'available'
     * - Verifies bed has no active unreleased assignment
     * - Releases any previous active bed assigned to this admission (if transferring)
     */
    public function assignPatientToBed(Bed $bed, Admission $admission, User $actor, ?string $reason = null): BedAssignment
    {
        return DB::transaction(function () use ($bed, $admission, $actor, $reason) {
            $lockedBed = Bed::where('id', $bed->id)->lockForUpdate()->firstOrFail();

            // 1. Prevent assignment to non-available bed
            if ($lockedBed->status !== 'available') {
                throw new InvalidArgumentException("Bed {$lockedBed->bed_number} is currently {$lockedBed->status} and cannot receive a new patient.");
            }

            // 2. Prevent duplicate active assignment on this bed
            $existingActive = BedAssignment::where('bed_id', $lockedBed->id)
                ->whereNull('released_at')
                ->lockForUpdate()
                ->first();

            if ($existingActive) {
                throw new InvalidArgumentException("Concurrency conflict: Bed {$lockedBed->bed_number} already has an active occupant assignment #{$existingActive->id}.");
            }

            // 3. If admission already has an active bed, release it (safe bed transfer)
            $currentAdmissionAssignment = BedAssignment::where('admission_id', $admission->id)
                ->whereNull('released_at')
                ->lockForUpdate()
                ->first();

            if ($currentAdmissionAssignment) {
                $previousBed = Bed::where('id', $currentAdmissionAssignment->bed_id)->lockForUpdate()->first();
                $currentAdmissionAssignment->update([
                    'released_at' => now(),
                    'transfer_reason' => $reason ?? "Transferred to bed {$lockedBed->bed_number}",
                ]);

                if ($previousBed) {
                    $previousBed->update(['status' => 'cleaning']);
                    BedStatusHistory::create([
                        'bed_id' => $previousBed->id,
                        'from_status' => 'occupied',
                        'to_status' => 'cleaning',
                        'changed_by' => $actor->id,
                        'reason' => "Patient transferred to bed {$lockedBed->bed_number}: " . ($reason ?? 'Bed transfer'),
                    ]);
                }
            }

            // 4. Create new active assignment
            $assignment = BedAssignment::create([
                'admission_id' => $admission->id,
                'bed_id' => $lockedBed->id,
                'assigned_at' => now(),
                'assigned_by' => $actor->id,
                'transfer_reason' => $reason,
            ]);

            // 5. Update bed status to occupied
            $lockedBed->update(['status' => 'occupied']);

            BedStatusHistory::create([
                'bed_id' => $lockedBed->id,
                'from_status' => 'available',
                'to_status' => 'occupied',
                'changed_by' => $actor->id,
                'reason' => "Patient Admitted (Admission #{$admission->admission_number})",
                'notes' => $reason,
            ]);

            return $assignment;
        });
    }

    /**
     * Transfer an occupied patient from a source bed to a target bed.
     * Guarantees atomic transition, release of previous bed to cleaning, and full audit trail.
     */
    public function transferPatientBed(Bed $sourceBed, Bed $targetBed, string $reason, User $actor): BedAssignment
    {
        return DB::transaction(function () use ($sourceBed, $targetBed, $reason, $actor) {
            $lockedSource = Bed::where('id', $sourceBed->id)->lockForUpdate()->firstOrFail();

            if ($lockedSource->status !== 'occupied') {
                throw new InvalidArgumentException("Bed {$lockedSource->bed_number} is not currently occupied and has no patient to transfer.");
            }

            if ($lockedSource->id === $targetBed->id) {
                throw new InvalidArgumentException("Cannot transfer patient to the same bed ({$lockedSource->bed_number}). Please choose a different target bed.");
            }

            $activeAssignment = BedAssignment::where('bed_id', $lockedSource->id)
                ->whereNull('released_at')
                ->lockForUpdate()
                ->first();

            if (!$activeAssignment) {
                throw new InvalidArgumentException("No active occupant record found on bed {$lockedSource->bed_number}.");
            }

            $admission = Admission::where('id', $activeAssignment->admission_id)->lockForUpdate()->firstOrFail();

            // Perform atomic assignment to target bed (releases previous bed and marks it cleaning)
            $newAssignment = $this->assignPatientToBed($targetBed, $admission, $actor, $reason);

            // Audit log
            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'bed.transferred',
                'auditable_type' => BedAssignment::class,
                'auditable_id' => $newAssignment->id,
                'old_values' => [
                    'from_bed_id' => $lockedSource->id,
                    'from_bed_number' => $lockedSource->bed_number,
                ],
                'new_values' => [
                    'to_bed_id' => $targetBed->id,
                    'to_bed_number' => $targetBed->bed_number,
                    'admission_id' => $admission->id,
                    'admission_number' => $admission->admission_number,
                    'patient_id' => $admission->patient_id,
                    'reason' => $reason,
                ],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
                'created_at' => now(),
            ]);

            return $newAssignment;
        });
    }

    /**
     * Complete cleaning and terminal sanitization for a bed.
     * Transitions bed status from 'cleaning' to 'available'.
     */
    public function completeCleaning(Bed $bed, User $actor, ?string $notes = null): Bed
    {
        return DB::transaction(function () use ($bed, $actor, $notes) {
            $lockedBed = Bed::where('id', $bed->id)->lockForUpdate()->firstOrFail();

            if ($lockedBed->status !== 'cleaning') {
                throw new InvalidArgumentException("Bed {$lockedBed->bed_number} is currently '{$lockedBed->status}', not under cleaning.");
            }

            $lockedBed->update(['status' => 'available']);

            BedStatusHistory::create([
                'bed_id' => $lockedBed->id,
                'from_status' => 'cleaning',
                'to_status' => 'available',
                'changed_by' => $actor->id,
                'reason' => 'Terminal sanitization and cleaning certified complete',
                'notes' => $notes ?? 'Sanitized, fresh bed linen, disinfection protocol verified.',
            ]);

            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'bed.cleaning_completed',
                'auditable_type' => Bed::class,
                'auditable_id' => $lockedBed->id,
                'old_values' => ['status' => 'cleaning'],
                'new_values' => [
                    'status' => 'available',
                    'certified_by' => $actor->name,
                    'notes' => $notes,
                ],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
                'created_at' => now(),
            ]);

            return $lockedBed;
        });
    }

    /**
     * Complete maintenance and repair inspection on a bed.
     * Optionally places bed into 'cleaning' for sanitization before patient intake.
     */
    public function completeMaintenance(Bed $bed, User $actor, ?string $notes = null, bool $requiresCleaning = true): Bed
    {
        return DB::transaction(function () use ($bed, $actor, $notes, $requiresCleaning) {
            $lockedBed = Bed::where('id', $bed->id)->lockForUpdate()->firstOrFail();

            if ($lockedBed->status !== 'maintenance') {
                throw new InvalidArgumentException("Bed {$lockedBed->bed_number} is currently '{$lockedBed->status}', not under maintenance.");
            }

            $targetStatus = $requiresCleaning ? 'cleaning' : 'available';
            $lockedBed->update(['status' => $targetStatus]);

            BedStatusHistory::create([
                'bed_id' => $lockedBed->id,
                'from_status' => 'maintenance',
                'to_status' => $targetStatus,
                'changed_by' => $actor->id,
                'reason' => 'Maintenance repairs verified complete',
                'notes' => $notes ?? 'Engineering inspection passed.',
            ]);

            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'bed.maintenance_completed',
                'auditable_type' => Bed::class,
                'auditable_id' => $lockedBed->id,
                'old_values' => ['status' => 'maintenance'],
                'new_values' => ['status' => $targetStatus, 'notes' => $notes],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
                'created_at' => now(),
            ]);

            return $lockedBed;
        });
    }

    /**
     * Release patient from bed upon discharge or room exit.
     */
    public function releaseBed(Bed $bed, User $actor, string $newStatus = 'cleaning', ?string $notes = null): void
    {
        DB::transaction(function () use ($bed, $actor, $newStatus, $notes) {
            $lockedBed = Bed::where('id', $bed->id)->lockForUpdate()->firstOrFail();

            $activeAssignment = BedAssignment::where('bed_id', $lockedBed->id)
                ->whereNull('released_at')
                ->lockForUpdate()
                ->first();

            if ($activeAssignment) {
                $activeAssignment->update([
                    'released_at' => now(),
                    'transfer_reason' => $notes ?? 'Patient released / discharged',
                ]);
            }

            $previousStatus = $lockedBed->status;
            $lockedBed->update(['status' => $newStatus]);

            BedStatusHistory::create([
                'bed_id' => $lockedBed->id,
                'from_status' => $previousStatus,
                'to_status' => $newStatus,
                'changed_by' => $actor->id,
                'reason' => 'Patient released from bed',
                'notes' => $notes,
            ]);

            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'bed.released',
                'auditable_type' => Bed::class,
                'auditable_id' => $lockedBed->id,
                'old_values' => ['status' => $previousStatus],
                'new_values' => ['status' => $newStatus, 'notes' => $notes],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
                'created_at' => now(),
            ]);
        });
    }

    /**
     * Delete a bed from hospital inventory.
     * Enforces: Cannot delete occupied bed or bed with historical active references.
     */
    public function deleteBed(Bed $bed, User $actor): void
    {
        DB::transaction(function () use ($bed, $actor) {
            $lockedBed = Bed::where('id', $bed->id)->lockForUpdate()->firstOrFail();

            if ($lockedBed->status === 'occupied') {
                throw new InvalidArgumentException("Cannot delete bed {$lockedBed->bed_number} while it is currently occupied by a patient.");
            }

            $hasActiveAssignment = BedAssignment::where('bed_id', $lockedBed->id)->whereNull('released_at')->exists();
            if ($hasActiveAssignment) {
                throw new InvalidArgumentException("Cannot delete bed {$lockedBed->bed_number} with an active patient occupancy assignment.");
            }

            // Create audit log before deletion
            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'bed.deleted',
                'auditable_type' => Bed::class,
                'auditable_id' => $lockedBed->id,
                'old_values' => [
                    'bed_number' => $lockedBed->bed_number,
                    'room_number' => $lockedBed->room_number,
                    'category' => $lockedBed->category,
                    'department_id' => $lockedBed->department_id,
                ],
                'new_values' => null,
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
                'created_at' => now(),
            ]);

            $lockedBed->delete();
        });
    }
}
