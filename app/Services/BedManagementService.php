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
     * Create a new bed master configuration.
     * Accessible by Administrators.
     */
    public function createBed(array $data, User $actor): Bed
    {
        return DB::transaction(function () use ($data, $actor) {
            $initialStatus = $data['status'] ?? 'available';

            $bed = Bed::create([
                'bed_number' => strtoupper(trim($data['bed_number'])),
                'room_number' => isset($data['room_number']) ? trim($data['room_number']) : null,
                'category' => $data['category'] ?? 'department',
                'department_id' => !empty($data['department_id']) ? $data['department_id'] : null,
                'daily_charge' => $data['daily_charge'] ?? 0.00,
                'status' => $initialStatus,
                'description' => $data['description'] ?? null,
            ]);

            // Record initial status history
            BedStatusHistory::create([
                'bed_id' => $bed->id,
                'from_status' => null,
                'to_status' => $initialStatus,
                'changed_by' => $actor->id,
                'reason' => 'Bed Commissioning / Initial Creation',
                'notes' => $data['description'] ?? 'Bed provisioned in clinic master inventory.',
            ]);

            // Audit log
            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'bed.created',
                'auditable_type' => Bed::class,
                'auditable_id' => $bed->id,
                'old_values' => null,
                'new_values' => $bed->toArray(),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
                'created_at' => now(),
            ]);

            return $bed;
        });
    }

    /**
     * Update an existing bed's configuration.
     */
    public function updateBed(Bed $bed, array $data, User $actor): Bed
    {
        return DB::transaction(function () use ($bed, $data, $actor) {
            $lockedBed = Bed::where('id', $bed->id)->lockForUpdate()->firstOrFail();
            $oldValues = $lockedBed->toArray();
            $previousStatus = $lockedBed->status;

            $updatePayload = [
                'bed_number' => strtoupper(trim($data['bed_number'])),
                'room_number' => isset($data['room_number']) ? trim($data['room_number']) : null,
                'category' => $data['category'] ?? $lockedBed->category,
                'department_id' => !empty($data['department_id']) ? $data['department_id'] : null,
                'daily_charge' => $data['daily_charge'] ?? $lockedBed->daily_charge,
                'description' => $data['description'] ?? null,
            ];

            // If status is also explicitly updated
            if (isset($data['status']) && $data['status'] !== $previousStatus) {
                // If attempting to mark an occupied bed as available without releasing assignment, disallow or handle
                if ($previousStatus === 'occupied' && $data['status'] !== 'occupied') {
                    $hasActiveAssignment = $lockedBed->assignments()->whereNull('released_at')->exists();
                    if ($hasActiveAssignment) {
                        throw new InvalidArgumentException("Cannot change status from occupied while an active patient bed assignment exists. Please discharge or transfer the patient first.");
                    }
                }

                $updatePayload['status'] = $data['status'];

                BedStatusHistory::create([
                    'bed_id' => $lockedBed->id,
                    'from_status' => $previousStatus,
                    'to_status' => $data['status'],
                    'changed_by' => $actor->id,
                    'reason' => $data['status_reason'] ?? 'Bed Details Updated by Administrator',
                    'notes' => $data['status_notes'] ?? null,
                ]);
            }

            $lockedBed->update($updatePayload);

            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'bed.updated',
                'auditable_type' => Bed::class,
                'auditable_id' => $lockedBed->id,
                'old_values' => $oldValues,
                'new_values' => $lockedBed->fresh()->toArray(),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
                'created_at' => now(),
            ]);

            return $lockedBed;
        });
    }

    /**
     * Transition a bed's status with history recording and concurrency protection.
     * Allowed statuses: available, occupied, reserved, cleaning, maintenance.
     */
    public function updateBedStatus(Bed $bed, string $newStatus, ?string $reason, ?string $notes, User $actor): Bed
    {
        return DB::transaction(function () use ($bed, $newStatus, $reason, $notes, $actor) {
            $lockedBed = Bed::where('id', $bed->id)->lockForUpdate()->firstOrFail();
            $previousStatus = $lockedBed->status;

            if ($previousStatus === $newStatus) {
                return $lockedBed;
            }

            // Prevent marking occupied as available if active patient assignment exists
            if ($previousStatus === 'occupied' && in_array($newStatus, ['available', 'cleaning', 'maintenance', 'reserved'])) {
                $hasActiveAssignment = $lockedBed->assignments()->whereNull('released_at')->exists();
                if ($hasActiveAssignment) {
                    throw new InvalidArgumentException("Cannot transition occupied bed to '{$newStatus}' while patient assignment is active. Release or transfer patient first.");
                }
            }

            $lockedBed->update(['status' => $newStatus]);

            // Maintain status history
            BedStatusHistory::create([
                'bed_id' => $lockedBed->id,
                'from_status' => $previousStatus,
                'to_status' => $newStatus,
                'changed_by' => $actor->id,
                'reason' => $reason ?? 'Status updated by clinic staff',
                'notes' => $notes,
            ]);

            AuditLog::create([
                'user_id' => $actor->id,
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
                        'reason' => "Patient transferred to bed {$lockedBed->bed_number}",
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

            if ($lockedBed->assignments()->whereNull('released_at')->exists()) {
                throw new InvalidArgumentException("Cannot delete bed {$lockedBed->bed_number} because an active patient assignment is linked to it.");
            }

            $bedData = $lockedBed->toArray();
            $lockedBed->delete();

            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'bed.deleted',
                'auditable_type' => Bed::class,
                'auditable_id' => $lockedBed->id,
                'old_values' => $bedData,
                'new_values' => null,
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
                'created_at' => now(),
            ]);
        });
    }
}

