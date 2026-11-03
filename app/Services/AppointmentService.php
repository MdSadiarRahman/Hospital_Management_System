<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentHistory;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use App\Models\SystemNotification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentService
{
    /**
     * Get available time slots for a doctor on a specific date based on their configured schedule.
     *
     * @return array{
     *     doctor: User,
     *     date: string,
     *     day_of_week: string,
     *     is_scheduled: bool,
     *     message: string,
     *     slots: array<int, array{start: string, end: string, formatted: string, is_available: bool, is_past: bool, reason: ?string}>
     * }
     */
    public function getAvailableSlots(int $doctorId, string $date, ?int $ignoreAppointmentId = null): array
    {
        $doctor = User::with('staffProfile.department')->findOrFail($doctorId);
        $carbonDate = Carbon::parse($date)->startOfDay();
        $isToday = $carbonDate->isToday();
        $isPast = $carbonDate->lt(now()->startOfDay());

        if ($isPast) {
            return [
                'doctor' => $doctor,
                'date' => $carbonDate->toDateString(),
                'day_of_week' => strtolower($carbonDate->format('l')),
                'is_scheduled' => false,
                'message' => 'Cannot book appointments for past dates.',
                'slots' => [],
            ];
        }

        $dayOfWeek = strtolower($carbonDate->format('l'));

        // Query active configured schedules for this doctor on this day
        $schedules = DoctorSchedule::where('doctor_id', $doctorId)
            ->where('is_active', true)
            ->where('day_of_week', $dayOfWeek)
            ->orderBy('start_time')
            ->get();

        if ($schedules->isEmpty()) {
            return [
                'doctor' => $doctor,
                'date' => $carbonDate->toDateString(),
                'day_of_week' => $dayOfWeek,
                'is_scheduled' => false,
                'message' => "Dr. {$doctor->name} does not have consultation hours configured on {$carbonDate->format('l')}s.",
                'slots' => [],
            ];
        }

        // Fetch existing bookings for this doctor on this date that would conflict
        $existingBookings = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $carbonDate->toDateString())
            ->whereIn('status', [
                Appointment::STATUS_PENDING,
                Appointment::STATUS_CONFIRMED,
                Appointment::STATUS_RESCHEDULED,
            ])
            ->when($ignoreAppointmentId, fn($q) => $q->where('id', '!=', $ignoreAppointmentId))
            ->get();

        $bookedTimes = $existingBookings->map(function ($apt) {
            return $apt->appointment_time
                ? Carbon::createFromTimeString($apt->appointment_time)->format('H:i')
                : $apt->appointment_date->format('H:i');
        })->toArray();

        $allSlots = [];

        foreach ($schedules as $schedule) {
            $slots = $schedule->generateSlots();
            foreach ($slots as $slot) {
                $slotTime = $slot['start'];
                $isBooked = in_array($slotTime, $bookedTimes, true);
                $isSlotPast = false;

                if ($isToday) {
                    $slotDateTime = Carbon::createFromTimeString($slotTime);
                    if ($slotDateTime->lt(now())) {
                        $isSlotPast = true;
                    }
                }

                $reason = null;
                $isAvailable = true;

                if ($isBooked) {
                    $isAvailable = false;
                    $reason = 'Already booked';
                } elseif ($isSlotPast) {
                    $isAvailable = false;
                    $reason = 'Time passed';
                }

                $allSlots[] = [
                    'start' => $slot['start'],
                    'end' => $slot['end'],
                    'formatted' => $slot['formatted'],
                    'is_available' => $isAvailable,
                    'is_past' => $isSlotPast,
                    'reason' => $reason,
                    'room_number' => $schedule->room_number,
                ];
            }
        }

        return [
            'doctor' => $doctor,
            'date' => $carbonDate->toDateString(),
            'day_of_week' => $dayOfWeek,
            'is_scheduled' => true,
            'message' => count($allSlots) > 0 ? 'Available slots loaded.' : 'No slots available for this date.',
            'slots' => $allSlots,
        ];
    }

    /**
     * Book a new appointment with strict concurrency protection and schedule validation.
     *
     * @throws ValidationException
     */
    public function bookAppointment(array $data, ?User $actor = null): Appointment
    {
        return DB::transaction(function () use ($data, $actor) {
            $doctorId = (int) $data['doctor_id'];
            $departmentId = (int) $data['department_id'];
            $date = Carbon::parse($data['appointment_date'])->toDateString();
            $time = Carbon::createFromTimeString($data['appointment_time'])->format('H:i:s');
            $fullDateTime = Carbon::parse("{$date} {$time}");

            // 1. Verify doctor belongs to department
            $doctor = User::with('staffProfile.department')->findOrFail($doctorId);
            $department = Department::findOrFail($departmentId);

            // 2. Validate doctor schedule for this day and time
            $dayOfWeek = strtolower($fullDateTime->format('l'));
            $schedule = DoctorSchedule::where('doctor_id', $doctorId)
                ->where('department_id', $departmentId)
                ->where('day_of_week', $dayOfWeek)
                ->where('is_active', true)
                ->where('start_time', '<=', $time)
                ->where('end_time', '>=', $time)
                ->first();

            // If not found with department, check general active schedule for doctor on that day
            if (!$schedule) {
                $schedule = DoctorSchedule::where('doctor_id', $doctorId)
                    ->where('day_of_week', $dayOfWeek)
                    ->where('is_active', true)
                    ->where('start_time', '<=', $time)
                    ->where('end_time', '>=', $time)
                    ->first();
            }

            if (!$schedule) {
                throw ValidationException::withMessages([
                    'appointment_date' => ["Dr. {$doctor->name} does not have active consultation hours on {$fullDateTime->format('l')}s."],
                    'appointment_time' => ["Dr. {$doctor->name} does not have active consultation hours at {$fullDateTime->format('h:i A')} on {$fullDateTime->format('l')}s."],
                ]);
            }

            // 3. Concurrency check & lock against existing conflicting active appointments
            $conflict = Appointment::where('doctor_id', $doctorId)
                ->whereDate('appointment_date', $date)
                ->where(function ($q) use ($time, $fullDateTime) {
                    $q->where('appointment_time', $time)
                      ->orWhere('appointment_date', $fullDateTime);
                })
                ->whereIn('status', [
                    Appointment::STATUS_PENDING,
                    Appointment::STATUS_CONFIRMED,
                    Appointment::STATUS_RESCHEDULED,
                ])
                ->lockForUpdate()
                ->first();

            if ($conflict) {
                throw ValidationException::withMessages([
                    'appointment_time' => ['This time slot is already reserved or booked. Please select another available slot.'],
                ]);
            }

            // 4. Calculate next daily serial number for the doctor on that date (only if confirmed)
            $nextSerial = (Appointment::where('doctor_id', $doctorId)
                ->whereDate('appointment_date', $date)
                ->max('serial_number') ?? 0) + 1;

            // 5. Determine initial status
            $isStaff = $actor && $actor->user_type === 'staff';
            $autoConfirm = !empty($data['auto_confirm']) || ($data['status'] ?? null) === Appointment::STATUS_CONFIRMED;
            $status = ($isStaff && $autoConfirm) ? Appointment::STATUS_CONFIRMED : Appointment::STATUS_PENDING;

            $patientId = !empty($data['patient_id']) ? (int) $data['patient_id'] : null;

            // If actor is patient, automatically bind patient_id if linked
            if ($actor && $actor->user_type === 'patient' && !$patientId) {
                $patient = $actor->patient;
                $patientId = $patient?->id;
            }

            $appointment = Appointment::create([
                'patient_id' => $patientId,
                'department_id' => $departmentId,
                'doctor_id' => $doctorId,
                'guest_name' => $data['guest_name'] ?? null,
                'guest_phone' => $data['guest_phone'] ?? null,
                'guest_email' => $data['guest_email'] ?? null,
                'appointment_date' => $fullDateTime,
                'appointment_time' => $time,
                'serial_number' => ($status === Appointment::STATUS_CONFIRMED) ? $nextSerial : null,
                'type' => $data['type'] ?? ($isStaff ? 'walk_in' : 'online'),
                'status' => $status,
                'reason' => $data['reason'] ?? null,
                'slot_duration_minutes' => $schedule->slot_duration_minutes ?? 20,
                'confirmed_by' => ($status === Appointment::STATUS_CONFIRMED && $actor) ? $actor->id : null,
                'confirmed_at' => ($status === Appointment::STATUS_CONFIRMED) ? now() : null,
            ]);

            // 6. Record Initial History Log
            AppointmentHistory::create([
                'appointment_id' => $appointment->id,
                'action' => ($status === Appointment::STATUS_CONFIRMED) ? 'confirmed' : 'requested',
                'previous_status' => null,
                'new_status' => $status,
                'previous_date' => null,
                'new_date' => $fullDateTime,
                'note' => $status === Appointment::STATUS_CONFIRMED
                    ? 'Appointment booked and immediately confirmed by staff.'
                    : 'Initial appointment request submitted.',
                'changed_by' => $actor?->id,
            ]);

            // 7. Send Notifications
            $this->notifyOnBooking($appointment, $actor);

            // 8. Audit Log
            AuditLog::create([
                'user_id' => $actor?->id,
                'event' => 'appointment.created',
                'auditable_type' => Appointment::class,
                'auditable_id' => $appointment->id,
                'old_values' => null,
                'new_values' => $appointment->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);

            return $appointment;
        });
    }

    /**
     * Confirm a pending or rescheduled appointment (Staff approval).
     *
     * @throws ValidationException
     */
    public function confirmAppointment(Appointment $appointment, User $staff, ?string $notes = null): Appointment
    {
        return DB::transaction(function () use ($appointment, $staff, $notes) {
            $appointment = Appointment::where('id', $appointment->id)->lockForUpdate()->firstOrFail();

            if (!$appointment->canBeConfirmed()) {
                throw ValidationException::withMessages([
                    'status' => ["Appointment cannot be confirmed in its current status ({$appointment->status})."],
                ]);
            }

            // Check if any conflicting confirmed booking exists
            $conflict = Appointment::where('doctor_id', $appointment->doctor_id)
                ->where('id', '!=', $appointment->id)
                ->whereDate('appointment_date', $appointment->appointment_date->toDateString())
                ->where(function ($q) use ($appointment) {
                    $q->where('appointment_time', $appointment->appointment_time)
                      ->orWhere('appointment_date', $appointment->appointment_date);
                })
                ->where('status', Appointment::STATUS_CONFIRMED)
                ->lockForUpdate()
                ->first();

            if ($conflict) {
                throw ValidationException::withMessages([
                    'appointment_time' => ['Another appointment has already been confirmed for this time slot. Please reschedule before confirming.'],
                ]);
            }

            $previousStatus = $appointment->status;

            // Assign serial number if missing
            if (!$appointment->serial_number) {
                $appointment->serial_number = (Appointment::where('doctor_id', $appointment->doctor_id)
                    ->whereDate('appointment_date', $appointment->appointment_date->toDateString())
                    ->max('serial_number') ?? 0) + 1;
            }

            $appointment->status = Appointment::STATUS_CONFIRMED;
            $appointment->confirmed_by = $staff->id;
            $appointment->confirmed_at = now();
            $appointment->save();

            // History Log
            AppointmentHistory::create([
                'appointment_id' => $appointment->id,
                'action' => 'confirmed',
                'previous_status' => $previousStatus,
                'new_status' => Appointment::STATUS_CONFIRMED,
                'previous_date' => $appointment->appointment_date,
                'new_date' => $appointment->appointment_date,
                'note' => $notes ?: 'Appointment approved and confirmed by reception/staff.',
                'changed_by' => $staff->id,
            ]);

            // Notify patient if user account exists
            $this->notifyPatient($appointment, 'Appointment Confirmed', "Your appointment with Dr. {$appointment->doctor?->name} on " . $appointment->appointment_date->format('d M Y h:i A') . " has been confirmed. Serial: #{$appointment->serial_number}.");

            AuditLog::create([
                'user_id' => $staff->id,
                'event' => 'appointment.confirmed',
                'auditable_type' => Appointment::class,
                'auditable_id' => $appointment->id,
                'old_values' => ['status' => $previousStatus],
                'new_values' => ['status' => Appointment::STATUS_CONFIRMED, 'confirmed_by' => $staff->id],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);

            return $appointment;
        });
    }

    /**
     * Reschedule an appointment to a new date and time slot.
     *
     * @throws ValidationException
     */
    public function rescheduleAppointment(
        Appointment $appointment,
        string $newDate,
        string $newTime,
        User $actor,
        ?string $reason = null
    ): Appointment {
        return DB::transaction(function () use ($appointment, $newDate, $newTime, $actor, $reason) {
            $appointment = Appointment::where('id', $appointment->id)->lockForUpdate()->firstOrFail();

            if (!$appointment->canBeRescheduled()) {
                throw ValidationException::withMessages([
                    'status' => ["Appointment cannot be rescheduled in its current status ({$appointment->status})."],
                ]);
            }

            $carbonNewDate = Carbon::parse($newDate)->toDateString();
            $carbonNewTime = Carbon::createFromTimeString($newTime)->format('H:i:s');
            $fullNewDateTime = Carbon::parse("{$carbonNewDate} {$carbonNewTime}");

            if ($fullNewDateTime->lt(now())) {
                throw ValidationException::withMessages([
                    'new_date' => ['Cannot reschedule to a past date/time.'],
                ]);
            }

            // Verify doctor schedule for new slot
            $dayOfWeek = strtolower($fullNewDateTime->format('l'));
            $schedule = DoctorSchedule::where('doctor_id', $appointment->doctor_id)
                ->where('day_of_week', $dayOfWeek)
                ->where('is_active', true)
                ->where('start_time', '<=', $carbonNewTime)
                ->where('end_time', '>=', $carbonNewTime)
                ->first();

            if (!$schedule) {
                throw ValidationException::withMessages([
                    'new_time' => ["Dr. {$appointment->doctor?->name} does not have active consultation hours on {$fullNewDateTime->format('l')}s at {$fullNewDateTime->format('h:i A')}."],
                ]);
            }

            // Check for conflict at new slot
            $conflict = Appointment::where('doctor_id', $appointment->doctor_id)
                ->where('id', '!=', $appointment->id)
                ->whereDate('appointment_date', $carbonNewDate)
                ->where(function ($q) use ($carbonNewTime, $fullNewDateTime) {
                    $q->where('appointment_time', $carbonNewTime)
                      ->orWhere('appointment_date', $fullNewDateTime);
                })
                ->whereIn('status', [
                    Appointment::STATUS_PENDING,
                    Appointment::STATUS_CONFIRMED,
                    Appointment::STATUS_RESCHEDULED,
                ])
                ->lockForUpdate()
                ->first();

            if ($conflict) {
                throw ValidationException::withMessages([
                    'new_time' => ['The selected new time slot is already booked. Please choose another slot.'],
                ]);
            }

            $previousStatus = $appointment->status;
            $previousDate = $appointment->appointment_date;

            // Calculate new serial for that date
            $newSerial = (Appointment::where('doctor_id', $appointment->doctor_id)
                ->whereDate('appointment_date', $carbonNewDate)
                ->max('serial_number') ?? 0) + 1;

            $appointment->appointment_date = $fullNewDateTime;
            $appointment->appointment_time = $carbonNewTime;
            $appointment->serial_number = $newSerial;
            $appointment->status = Appointment::STATUS_RESCHEDULED;
            $appointment->save();

            // History Log
            AppointmentHistory::create([
                'appointment_id' => $appointment->id,
                'action' => 'rescheduled',
                'previous_status' => $previousStatus,
                'new_status' => Appointment::STATUS_RESCHEDULED,
                'previous_date' => $previousDate,
                'new_date' => $fullNewDateTime,
                'note' => $reason ?: 'Appointment rescheduled to a new date and time slot.',
                'changed_by' => $actor->id,
            ]);

            // Notifications
            $this->notifyPatient($appointment, 'Appointment Rescheduled', "Your appointment with Dr. {$appointment->doctor?->name} has been rescheduled to " . $fullNewDateTime->format('d M Y h:i A') . ". New Serial: #{$newSerial}. Reason: " . ($reason ?: 'Schedule update.'));

            // If rescheduled by patient, notify receptionists
            if ($actor->user_type === 'patient') {
                $this->notifyStaff("Appointment Rescheduled by Patient", "Patient {$appointment->patient_display_name} rescheduled appointment #{$appointment->id} to " . $fullNewDateTime->format('d M Y h:i A'), route('portal.appointments.show', $appointment->id));
            }

            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'appointment.rescheduled',
                'auditable_type' => Appointment::class,
                'auditable_id' => $appointment->id,
                'old_values' => ['appointment_date' => (string) $previousDate, 'status' => $previousStatus],
                'new_values' => ['appointment_date' => (string) $fullNewDateTime, 'status' => Appointment::STATUS_RESCHEDULED, 'reason' => $reason],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);

            return $appointment;
        });
    }

    /**
     * Cancel an appointment with mandatory cancellation reason.
     *
     * @throws ValidationException
     */
    public function cancelAppointment(Appointment $appointment, User $actor, string $reason): Appointment
    {
        return DB::transaction(function () use ($appointment, $actor, $reason) {
            $appointment = Appointment::where('id', $appointment->id)->lockForUpdate()->firstOrFail();

            if (!$appointment->canBeCancelled()) {
                throw ValidationException::withMessages([
                    'status' => ["Appointment cannot be cancelled in its current status ({$appointment->status})."],
                ]);
            }

            if (empty(trim($reason))) {
                throw ValidationException::withMessages([
                    'cancellation_reason' => ['A cancellation reason is required.'],
                ]);
            }

            $previousStatus = $appointment->status;

            $appointment->status = Appointment::STATUS_CANCELLED;
            $appointment->cancellation_reason = trim($reason);
            $appointment->cancelled_by = $actor->id;
            $appointment->cancelled_at = now();
            $appointment->save();

            // History Log
            AppointmentHistory::create([
                'appointment_id' => $appointment->id,
                'action' => 'cancelled',
                'previous_status' => $previousStatus,
                'new_status' => Appointment::STATUS_CANCELLED,
                'previous_date' => $appointment->appointment_date,
                'new_date' => $appointment->appointment_date,
                'note' => $reason,
                'changed_by' => $actor->id,
            ]);

            // Notifications
            $this->notifyPatient($appointment, 'Appointment Cancelled', "Your appointment on " . $appointment->appointment_date->format('d M Y h:i A') . " has been cancelled. Reason: {$reason}");

            if ($actor->user_type === 'patient') {
                $this->notifyStaff("Appointment Cancelled by Patient", "Patient {$appointment->patient_display_name} cancelled appointment #{$appointment->id}. Reason: {$reason}", route('portal.appointments.show', $appointment->id));
            }

            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'appointment.cancelled',
                'auditable_type' => Appointment::class,
                'auditable_id' => $appointment->id,
                'old_values' => ['status' => $previousStatus],
                'new_values' => ['status' => Appointment::STATUS_CANCELLED, 'reason' => $reason, 'cancelled_by' => $actor->id],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);

            return $appointment;
        });
    }

    /**
     * Mark an appointment as Completed or No Show.
     *
     * @throws ValidationException
     */
    public function markStatus(Appointment $appointment, string $status, User $actor, ?string $notes = null): Appointment
    {
        return DB::transaction(function () use ($appointment, $status, $actor, $notes) {
            $appointment = Appointment::where('id', $appointment->id)->lockForUpdate()->firstOrFail();

            if (!in_array($status, [Appointment::STATUS_COMPLETED, Appointment::STATUS_NO_SHOW], true)) {
                throw ValidationException::withMessages([
                    'status' => ['Invalid status transition requested.'],
                ]);
            }

            if ($status === Appointment::STATUS_COMPLETED && !$appointment->canBeMarkedCompleted()) {
                throw ValidationException::withMessages([
                    'status' => ["Appointment cannot be marked as completed in its current status ({$appointment->status})."],
                ]);
            }

            if ($status === Appointment::STATUS_NO_SHOW && !$appointment->canBeMarkedNoShow()) {
                throw ValidationException::withMessages([
                    'status' => ["Appointment cannot be marked as no-show in its current status ({$appointment->status})."],
                ]);
            }

            $previousStatus = $appointment->status;
            $appointment->status = $status;
            $appointment->save();

            // History Log
            AppointmentHistory::create([
                'appointment_id' => $appointment->id,
                'action' => $status,
                'previous_status' => $previousStatus,
                'new_status' => $status,
                'previous_date' => $appointment->appointment_date,
                'new_date' => $appointment->appointment_date,
                'note' => $notes ?: ($status === Appointment::STATUS_COMPLETED ? 'Consultation completed.' : 'Patient did not attend (No Show).'),
                'changed_by' => $actor->id,
            ]);

            AuditLog::create([
                'user_id' => $actor->id,
                'event' => "appointment.{$status}",
                'auditable_type' => Appointment::class,
                'auditable_id' => $appointment->id,
                'old_values' => ['status' => $previousStatus],
                'new_values' => ['status' => $status, 'notes' => $notes],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);

            return $appointment;
        });
    }

    /**
     * Notify relevant parties when an appointment is booked.
     */
    protected function notifyOnBooking(Appointment $appointment, ?User $actor): void
    {
        $doctor = $appointment->doctor;
        $patientName = $appointment->patient_display_name;
        $dateFormatted = $appointment->appointment_date->format('d M Y h:i A');

        // Always notify receptionists of new booking
        $this->notifyStaff(
            'New Appointment Booking',
            "Appointment #{$appointment->id} booked for {$patientName} with Dr. {$doctor?->name} on {$dateFormatted} (Status: {$appointment->status_label}).",
            route('portal.appointments.show', $appointment->id)
        );

        // If patient is a registered user, send confirmation/request received notification
        if ($appointment->patient?->user_id) {
            $title = $appointment->isConfirmed() ? 'Appointment Confirmed' : 'Appointment Request Received';
            $msg = $appointment->isConfirmed()
                ? "Your appointment with Dr. {$doctor?->name} on {$dateFormatted} is confirmed. Serial: #{$appointment->serial_number}."
                : "Your appointment request with Dr. {$doctor?->name} on {$dateFormatted} has been received. Our reception desk will review and confirm your slot.";

            $this->notifyUser($appointment->patient->user_id, $title, $msg, route('patient.appointments.index'));
        }
    }

    /**
     * Notify the patient associated with an appointment.
     */
    protected function notifyPatient(Appointment $appointment, string $title, string $message): void
    {
        $userId = $appointment->patient?->user_id;
        if ($userId) {
            $this->notifyUser($userId, $title, $message, route('patient.appointments.index'));
        }
    }

    /**
     * Notify all receptionists and clinic administrators.
     */
    protected function notifyStaff(string $title, string $message, ?string $actionUrl = null): void
    {
        $staffUserIds = User::where('user_type', 'staff')
            ->whereHas('roles', fn($q) => $q->whereIn('name', ['receptionist', 'administrator', 'super_admin']))
            ->pluck('id');

        foreach ($staffUserIds as $userId) {
            $this->notifyUser($userId, $title, $message, $actionUrl);
        }
    }

    /**
     * Helper to insert a SystemNotification record.
     */
    protected function notifyUser(int $userId, string $title, string $message, ?string $actionUrl = null): void
    {
        try {
            SystemNotification::create([
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => 'appointment',
                'action_url' => $actionUrl,
                'is_read' => false,
            ]);
        } catch (\Throwable $e) {
            // Log and avoid blocking primary transaction
            report($e);
        }
    }
}
