<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\AuditLog;
use App\Models\Operation;
use App\Models\OperationHistory;
use App\Models\PostOpObservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OperationService
{
    /**
     * Generate unique Operation number (e.g. OT-20261107-0001).
     */
    public function generateOperationNumber(): string
    {
        $datePrefix = 'OT-' . date('Ymd') . '-';

        $lastOperation = Operation::query()
            ->where('operation_number', 'like', $datePrefix . '%')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        if ($lastOperation && preg_match('/' . preg_quote($datePrefix, '/') . '(\d{4})$/', $lastOperation->operation_number, $matches)) {
            $nextSeq = intval($matches[1]) + 1;
        } else {
            $nextSeq = 1;
        }

        return $datePrefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Check schedule conflicts for OT Room, Surgeon, and Patient.
     *
     * @throws ValidationException
     */
    public function checkConflicts(
        string $otRoom,
        int $primarySurgeonId,
        int $patientId,
        Carbon $startAt,
        Carbon $endAt,
        ?int $ignoreOperationId = null
    ): void {
        // 1. Check OT Room double booking
        $roomConflict = Operation::query()
            ->whereNotIn('status', ['cancelled'])
            ->where('ot_room', $otRoom)
            ->when($ignoreOperationId, fn($q) => $q->where('id', '!=', $ignoreOperationId))
            ->where('scheduled_at', '<', $endAt)
            ->where('scheduled_end_at', '>', $startAt)
            ->first();

        if ($roomConflict) {
            $existingStart = $roomConflict->scheduled_at->format('h:i A');
            $existingEnd = $roomConflict->scheduled_end_at ? $roomConflict->scheduled_end_at->format('h:i A') : 'TBD';
            throw ValidationException::withMessages([
                'ot_room' => "Operating Theatre '{$otRoom}' is already booked from {$existingStart} to {$existingEnd} on {$roomConflict->scheduled_at->format('d M Y')} for procedure '{$roomConflict->procedure_name}'.",
            ]);
        }

        // 2. Check Primary Surgeon double booking
        $surgeonConflict = Operation::query()
            ->whereNotIn('status', ['cancelled'])
            ->where('primary_surgeon_id', $primarySurgeonId)
            ->when($ignoreOperationId, fn($q) => $q->where('id', '!=', $ignoreOperationId))
            ->where('scheduled_at', '<', $endAt)
            ->where('scheduled_end_at', '>', $startAt)
            ->with('primarySurgeon')
            ->first();

        if ($surgeonConflict) {
            $docName = $surgeonConflict->primarySurgeon?->name ?? 'Assigned Doctor';
            $existingStart = $surgeonConflict->scheduled_at->format('h:i A');
            $existingEnd = $surgeonConflict->scheduled_end_at ? $surgeonConflict->scheduled_end_at->format('h:i A') : 'TBD';
            throw ValidationException::withMessages([
                'primary_surgeon_id' => "Surgeon Dr. {$docName} already has an operation ('{$surgeonConflict->procedure_name}') scheduled during this time window ({$existingStart} - {$existingEnd}).",
            ]);
        }

        // 3. Check Patient double booking
        $patientConflict = Operation::query()
            ->whereNotIn('status', ['cancelled'])
            ->where('patient_id', $patientId)
            ->when($ignoreOperationId, fn($q) => $q->where('id', '!=', $ignoreOperationId))
            ->where('scheduled_at', '<', $endAt)
            ->where('scheduled_end_at', '>', $startAt)
            ->first();

        if ($patientConflict) {
            throw ValidationException::withMessages([
                'scheduled_at' => "The patient already has another operation ('{$patientConflict->procedure_name}') scheduled during this time window.",
            ]);
        }
    }

    /**
     * Schedule a new surgical operation.
     */
    public function scheduleOperation(array $data, User $actor): Operation
    {
        return DB::transaction(function () use ($data, $actor) {
            $admission = Admission::with('patient')->findOrFail($data['admission_id']);
            $patientId = $data['patient_id'] ?? $admission->patient_id;

            $scheduledAt = Carbon::parse($data['scheduled_at']);
            $durationMinutes = (int) ($data['estimated_duration_minutes'] ?? 60);
            $scheduledEndAt = $scheduledAt->copy()->addMinutes($durationMinutes);
            $otRoom = $data['ot_room'] ?? 'OT-1 (Main Theatre)';
            $surgeonId = (int) $data['primary_surgeon_id'];

            // Prevent conflicting schedules
            $this->checkConflicts($otRoom, $surgeonId, $patientId, $scheduledAt, $scheduledEndAt);

            $operationNumber = $this->generateOperationNumber();

            $status = $data['status'] ?? 'scheduled';

            $operation = Operation::create([
                'operation_number' => $operationNumber,
                'patient_id' => $patientId,
                'admission_id' => $admission->id,
                'procedure_name' => $data['procedure_name'],
                'department_id' => $data['department_id'] ?? $admission->department_id,
                'ot_room' => $otRoom,
                'urgency' => $data['urgency'] ?? 'elective',
                'indication' => $data['indication'] ?? null,
                'scheduled_at' => $scheduledAt,
                'estimated_duration_minutes' => $durationMinutes,
                'scheduled_end_at' => $scheduledEndAt,
                'primary_surgeon_id' => $surgeonId,
                'assistant_surgeon_id' => $data['assistant_surgeon_id'] ?? null,
                'requested_by' => $actor->id,
                'anesthetist_name' => $data['anesthetist_name'] ?? null,
                'ot_nurse_name' => $data['ot_nurse_name'] ?? null,
                'operation_notes' => $data['operation_notes'] ?? null,
                'post_op_notes' => $data['post_op_notes'] ?? null,
                'status' => $status,
            ]);

            // Create initial history record
            OperationHistory::create([
                'operation_id' => $operation->id,
                'action' => $status === 'requested' ? 'requested' : 'scheduled',
                'changed_by' => $actor->id,
                'previous_status' => null,
                'new_status' => $status,
                'previous_scheduled_at' => null,
                'new_scheduled_at' => $scheduledAt,
                'reason' => 'Initial scheduling',
                'notes' => "Scheduled in {$otRoom} for {$durationMinutes} minutes.",
                'metadata' => [
                    'ot_room' => $otRoom,
                    'primary_surgeon_id' => $surgeonId,
                    'procedure_name' => $operation->procedure_name,
                ],
            ]);

            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'operation_scheduled',
                'auditable_type' => Operation::class,
                'auditable_id' => $operation->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'old_values' => null,
                'new_values' => $operation->only(['operation_number', 'procedure_name', 'scheduled_at', 'ot_room', 'status']),
            ]);

            return $operation->fresh(['patient', 'admission', 'department', 'primarySurgeon', 'assistantSurgeon', 'histories']);
        });
    }

    /**
     * Reschedule an existing operation with conflict verification.
     */
    public function rescheduleOperation(Operation $operation, array $data, User $actor): Operation
    {
        return DB::transaction(function () use ($operation, $data, $actor) {
            if (!$operation->canBeRescheduled()) {
                throw ValidationException::withMessages([
                    'status' => "Cannot reschedule an operation in '{$operation->status}' status.",
                ]);
            }

            $oldScheduledAt = $operation->scheduled_at;
            $oldRoom = $operation->ot_room;

            $newScheduledAt = Carbon::parse($data['scheduled_at']);
            $durationMinutes = (int) ($data['estimated_duration_minutes'] ?? $operation->estimated_duration_minutes ?? 60);
            $newScheduledEndAt = $newScheduledAt->copy()->addMinutes($durationMinutes);
            $newRoom = $data['ot_room'] ?? $operation->ot_room;
            $surgeonId = (int) ($data['primary_surgeon_id'] ?? $operation->primary_surgeon_id);

            // Prevent conflicting schedules, ignoring the current operation
            $this->checkConflicts($newRoom, $surgeonId, $operation->patient_id, $newScheduledAt, $newScheduledEndAt, $operation->id);

            $reason = $data['rescheduled_reason'] ?? 'Schedule adjusted by clinical team';

            $operation->update([
                'scheduled_at' => $newScheduledAt,
                'estimated_duration_minutes' => $durationMinutes,
                'scheduled_end_at' => $newScheduledEndAt,
                'ot_room' => $newRoom,
                'primary_surgeon_id' => $surgeonId,
                'assistant_surgeon_id' => $data['assistant_surgeon_id'] ?? $operation->assistant_surgeon_id,
                'rescheduled_reason' => $reason,
                'rescheduled_at' => now(),
                'rescheduled_by' => $actor->id,
                'status' => 'rescheduled',
            ]);

            OperationHistory::create([
                'operation_id' => $operation->id,
                'action' => 'rescheduled',
                'changed_by' => $actor->id,
                'previous_status' => $operation->getOriginal('status'),
                'new_status' => 'rescheduled',
                'previous_scheduled_at' => $oldScheduledAt,
                'new_scheduled_at' => $newScheduledAt,
                'reason' => $reason,
                'notes' => "Moved from {$oldScheduledAt?->format('d M Y, h:i A')} ({$oldRoom}) to {$newScheduledAt->format('d M Y, h:i A')} ({$newRoom}).",
                'metadata' => [
                    'old_scheduled_at' => $oldScheduledAt?->toDateTimeString(),
                    'new_scheduled_at' => $newScheduledAt->toDateTimeString(),
                    'old_room' => $oldRoom,
                    'new_room' => $newRoom,
                    'reason' => $reason,
                ],
            ]);

            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'operation_rescheduled',
                'auditable_type' => Operation::class,
                'auditable_id' => $operation->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'old_values' => ['scheduled_at' => $oldScheduledAt?->toDateTimeString(), 'ot_room' => $oldRoom],
                'new_values' => ['scheduled_at' => $newScheduledAt->toDateTimeString(), 'ot_room' => $newRoom, 'reason' => $reason],
            ]);

            return $operation->fresh();
        });
    }

    /**
     * Cancel a scheduled operation with reason, freeing the OT room slot.
     */
    public function cancelOperation(Operation $operation, string $reason, User $actor): Operation
    {
        return DB::transaction(function () use ($operation, $reason, $actor) {
            if (!$operation->canBeCancelled()) {
                throw ValidationException::withMessages([
                    'status' => "Cannot cancel an operation with status '{$operation->status}'.",
                ]);
            }

            $prevStatus = $operation->status;

            $operation->update([
                'status' => 'cancelled',
                'cancellation_reason' => $reason,
                'cancelled_at' => now(),
                'cancelled_by' => $actor->id,
            ]);

            OperationHistory::create([
                'operation_id' => $operation->id,
                'action' => 'cancelled',
                'changed_by' => $actor->id,
                'previous_status' => $prevStatus,
                'new_status' => 'cancelled',
                'previous_scheduled_at' => $operation->scheduled_at,
                'new_scheduled_at' => null,
                'reason' => $reason,
                'notes' => "Cancelled by {$actor->name}: {$reason}",
            ]);

            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'operation_cancelled',
                'auditable_type' => Operation::class,
                'auditable_id' => $operation->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'old_values' => ['status' => $prevStatus],
                'new_values' => ['status' => 'cancelled', 'reason' => $reason],
            ]);

            return $operation->fresh();
        });
    }

    /**
     * Start operation (patient in theatre, incision/anesthesia begun).
     */
    public function startOperation(Operation $operation, ?Carbon $startedAt, User $actor): Operation
    {
        return DB::transaction(function () use ($operation, $startedAt, $actor) {
            if (!$operation->canBeStarted()) {
                throw ValidationException::withMessages([
                    'status' => "Cannot start an operation with status '{$operation->status}'.",
                ]);
            }

            $startTime = $startedAt ?? now();
            $prevStatus = $operation->status;

            $operation->update([
                'status' => 'in_progress',
                'started_at' => $startTime,
            ]);

            OperationHistory::create([
                'operation_id' => $operation->id,
                'action' => 'started',
                'changed_by' => $actor->id,
                'previous_status' => $prevStatus,
                'new_status' => 'in_progress',
                'notes' => "Surgical procedure commenced at {$startTime->format('h:i A')}.",
            ]);

            return $operation->fresh();
        });
    }

    /**
     * Complete operation with procedure notes, end timestamp, and recovery instructions.
     */
    public function completeOperation(Operation $operation, array $data, User $actor): Operation
    {
        return DB::transaction(function () use ($operation, $data, $actor) {
            $endTime = isset($data['ended_at']) ? Carbon::parse($data['ended_at']) : now();
            $prevStatus = $operation->status;

            $operation->update([
                'status' => 'completed',
                'ended_at' => $endTime,
                'operation_notes' => $data['operation_notes'] ?? $operation->operation_notes,
                'post_op_notes' => $data['post_op_notes'] ?? $operation->post_op_notes,
                'anesthetist_name' => $data['anesthetist_name'] ?? $operation->anesthetist_name,
                'ot_nurse_name' => $data['ot_nurse_name'] ?? $operation->ot_nurse_name,
            ]);

            OperationHistory::create([
                'operation_id' => $operation->id,
                'action' => 'completed',
                'changed_by' => $actor->id,
                'previous_status' => $prevStatus,
                'new_status' => 'completed',
                'notes' => "Procedure successfully completed at {$endTime->format('h:i A')}. Surgical notes recorded.",
            ]);

            return $operation->fresh();
        });
    }

    /**
     * Update surgical operation notes.
     */
    public function recordOperationNotes(Operation $operation, array $data, User $actor): Operation
    {
        return DB::transaction(function () use ($operation, $data, $actor) {
            $operation->update([
                'operation_notes' => $data['operation_notes'],
                'post_op_notes' => $data['post_op_notes'] ?? $operation->post_op_notes,
                'anesthetist_name' => $data['anesthetist_name'] ?? $operation->anesthetist_name,
                'ot_nurse_name' => $data['ot_nurse_name'] ?? $operation->ot_nurse_name,
            ]);

            OperationHistory::create([
                'operation_id' => $operation->id,
                'action' => 'notes_recorded',
                'changed_by' => $actor->id,
                'notes' => 'Operation and post-operative instructions updated by ' . $actor->name,
            ]);

            return $operation->fresh();
        });
    }

    /**
     * Record post-operative bedside observation.
     */
    public function recordPostOpObservation(Operation $operation, array $data, User $actor): PostOpObservation
    {
        return DB::transaction(function () use ($operation, $data, $actor) {
            $observation = PostOpObservation::create([
                'operation_id' => $operation->id,
                'recorded_by' => $actor->id,
                'observed_at' => isset($data['observed_at']) ? Carbon::parse($data['observed_at']) : now(),
                'blood_pressure_systolic' => $data['blood_pressure_systolic'] ?? null,
                'blood_pressure_diastolic' => $data['blood_pressure_diastolic'] ?? null,
                'heart_rate' => $data['heart_rate'] ?? null,
                'respiratory_rate' => $data['respiratory_rate'] ?? null,
                'temperature_celsius' => $data['temperature_celsius'] ?? null,
                'spo2_percent' => $data['spo2_percent'] ?? null,
                'consciousness_level' => $data['consciousness_level'] ?? 'alert',
                'pain_score' => $data['pain_score'] ?? null,
                'surgical_site_condition' => $data['surgical_site_condition'] ?? null,
                'drain_output_ml' => $data['drain_output_ml'] ?? null,
                'urine_output_ml' => $data['urine_output_ml'] ?? null,
                'observation_notes' => $data['observation_notes'],
                'discharge_criteria_met' => (bool) ($data['discharge_criteria_met'] ?? false),
            ]);

            OperationHistory::create([
                'operation_id' => $operation->id,
                'action' => 'observation_recorded',
                'changed_by' => $actor->id,
                'notes' => "Post-op observation recorded by {$actor->name} ({$observation->observed_at->format('h:i A')}). Condition: {$observation->consciousness_level}.",
            ]);

            return $observation;
        });
    }

    /**
     * Compute statistics for the OT dashboard.
     */
    public function getStatistics(): array
    {
        $today = Carbon::today();

        return [
            'total_today' => Operation::query()->whereDate('scheduled_at', $today)->count(),
            'scheduled' => Operation::query()->whereIn('status', ['scheduled', 'rescheduled'])->count(),
            'in_progress' => Operation::query()->where('status', 'in_progress')->count(),
            'completed_today' => Operation::query()->where('status', 'completed')->whereDate('ended_at', $today)->count(),
            'cancelled' => Operation::query()->where('status', 'cancelled')->count(),
            'emergency' => Operation::query()->where('urgency', 'emergency')->whereNotIn('status', ['completed', 'cancelled'])->count(),
        ];
    }
}
