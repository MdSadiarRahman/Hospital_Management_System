<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\Appointment;
use App\Models\Bed;
use App\Models\BedAssignment;
use App\Models\Bill;
use App\Models\LabReport;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class NotificationService
{
    /**
     * Send a notification to one or multiple users.
     *
     * @param User|Collection|array<User|int> $recipients
     * @return array<SystemNotification>
     */
    public function send(
        User|Collection|array $recipients,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null
    ): array {
        if ($recipients instanceof User) {
            $recipients = [$recipients];
        }

        $created = [];
        $recipientIds = [];

        foreach ($recipients as $recipient) {
            $userId = $recipient instanceof User ? $recipient->id : (int) $recipient;
            if ($userId && !in_array($userId, $recipientIds, true)) {
                $recipientIds[] = $userId;
                $created[] = SystemNotification::create([
                    'user_id' => $userId,
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'action_url' => $actionUrl,
                    'is_read' => false,
                    'read_at' => null,
                ]);
            }
        }

        return $created;
    }

    /**
     * Broadcast a notification to all active users holding specific roles.
     *
     * @param array<string>|string $roles
     * @return array<SystemNotification>
     */
    public function sendToRoles(
        array|string $roles,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null
    ): array {
        $roles = (array) $roles;

        $users = User::whereHas('roles', fn($q) => $q->whereIn('name', $roles))
            ->where('is_active', true)
            ->get();

        return $this->send($users, $type, $title, $message, $actionUrl);
    }

    /*
    |--------------------------------------------------------------------------
    | Specific Notification Triggers (Role-Appropriate)
    |--------------------------------------------------------------------------
    */

    /**
     * 1. Appointment Updates -> Doctor, Receptionist, Patient (if linked)
     */
    public function notifyAppointmentUpdate(Appointment $appointment, string $action): array
    {
        $statusLabel = ucfirst(str_replace('_', ' ', $action));
        $patientName = $appointment->patient?->name ?? 'Patient';
        $title = "Appointment {$statusLabel}";
        $message = "Appointment #{$appointment->appointment_number} for {$patientName} has been {$action}.";
        $url = route('portal.appointments.index');

        $recipients = [];

        // Attending Doctor
        if ($appointment->doctor_id) {
            $recipients[] = $appointment->doctor_id;
        }

        // Send to Doctor and Receptionists
        $staffNotifications = $this->sendToRoles('receptionist', 'appointment', $title, $message, $url);
        $doctorNotifications = $this->send($recipients, 'appointment', $title, $message, $url);

        // Notify Patient if user account exists
        if ($appointment->patient?->user_id) {
            $this->send(
                $appointment->patient->user_id,
                'appointment',
                "Appointment {$statusLabel} — Meghna Clinic",
                "Your appointment on {$appointment->appointment_date} with Dr. {$appointment->doctor?->name} is {$action}.",
                route('patient.appointments')
            );
        }

        return array_merge($staffNotifications, $doctorNotifications);
    }

    /**
     * 2. Admission Updates -> Attending Doctor, On-duty Nurses, Receptionists
     */
    public function notifyAdmissionUpdate(Admission $admission, string $action): array
    {
        $actionLabel = ucfirst($action);
        $patientName = $admission->patient?->name ?? 'Patient';
        $title = "Inpatient Admission {$actionLabel}";
        $message = "Admission #{$admission->admission_number} for {$patientName} ({$admission->department?->name}) has been {$action}.";
        $url = route('portal.admissions.show', $admission->id);

        $recipients = [];
        if ($admission->attending_doctor_id) {
            $recipients[] = $admission->attending_doctor_id;
        }

        $doctorNotifications = $this->send($recipients, 'admission', $title, $message, $url);
        $nurseAndReception = $this->sendToRoles(['nurse', 'receptionist'], 'admission', $title, $message, $url);

        return array_merge($doctorNotifications, $nurseAndReception);
    }

    /**
     * 3. Bed Assignment Updates -> Ward Nurses, Attending Doctor
     */
    public function notifyBedAssignmentUpdate(BedAssignment $assignment, string $action, ?Bed $previousBed = null): array
    {
        $patientName = $assignment->admission?->patient?->name ?? 'Inpatient';
        $bedNumber = $assignment->bed?->bed_number ?? 'Bed';
        $title = "Bed Assignment Update";

        if ($action === 'transferred' && $previousBed) {
            $message = "Patient {$patientName} transferred from Bed #{$previousBed->bed_number} to Bed #{$bedNumber}.";
        } else {
            $message = "Patient {$patientName} assigned to Bed #{$bedNumber} ({$action}).";
        }

        $url = route('portal.beds.occupancy');

        $recipients = [];
        if ($assignment->admission?->attending_doctor_id) {
            $recipients[] = $assignment->admission->attending_doctor_id;
        }

        $doctorNotifs = $this->send($recipients, 'bed_alert', $title, $message, $url);
        $nurseNotifs = $this->sendToRoles('nurse', 'bed_alert', $title, $message, $url);

        return array_merge($doctorNotifs, $nurseNotifs);
    }

    /**
     * 4. Laboratory Report Availability -> Ordering Doctor, Patient (if linked)
     */
    public function notifyLabReportReady(LabReport $report): array
    {
        $order = $report->orderItem?->labOrder;
        $patientName = $order?->patient?->name ?? 'Patient';
        $reportId = $report->id;
        $title = "Diagnostic Lab Report Available";
        $message = "Verified lab report #{$reportId} is now ready for {$patientName}.";
        $url = $order ? route('portal.lab-orders.show', $order->id) : route('portal.lab-orders.index');

        $recipients = [];
        if ($order?->doctor_id) {
            $recipients[] = $order->doctor_id;
        }

        $doctorNotifs = $this->send($recipients, 'lab_ready', $title, $message, $url);

        // Patient notification
        if ($order?->patient?->user_id) {
            $this->send(
                $order->patient->user_id,
                'lab_ready',
                "Medical Diagnostic Report Ready",
                "Your test report #{$reportId} is verified and ready for download.",
                route('patient.lab-reports')
            );
        }

        return $doctorNotifs;
    }

    /**
     * 5. Billing Updates -> Billing Staff, Cashiers, Administrators
     */
    public function notifyBillingUpdate(Bill $bill, string $action): array
    {
        $title = "Billing Update: Invoice #{$bill->invoice_number}";
        $patientName = $bill->patient?->name ?? 'Patient';
        $amount = number_format((float) $bill->total_amount, 2);
        $due = number_format((float) $bill->due_amount, 2);
        $message = "Invoice #{$bill->invoice_number} for {$patientName}: {$action}. Total: ৳{$amount}, Due: ৳{$due}.";
        $url = route('portal.billing.show', $bill->id);

        return $this->sendToRoles(['billing_staff', 'administrator'], 'billing', $title, $message, $url);
    }

    /**
     * 6. Discharge Updates -> Nurses, Billing Staff, Attending Doctor, Receptionists
     */
    public function notifyDischargeUpdate(Admission $admission, string $stage): array
    {
        $patientName = $admission->patient?->name ?? 'Patient';
        $title = "Inpatient Discharge: " . ucfirst(str_replace('_', ' ', $stage));

        $message = match ($stage) {
            'clinical_clearance' => "Doctor clinical clearance granted for {$patientName} (#{$admission->admission_number}).",
            'nursing_clearance' => "Nursing exit clearance completed for {$patientName} (#{$admission->admission_number}).",
            'financial_clearance' => "Financial clearance verified and settled for {$patientName} (#{$admission->admission_number}).",
            'approved' => "Patient {$patientName} successfully discharged. Bed released for sanitization.",
            default => "Discharge status updated for {$patientName} (#{$admission->admission_number}).",
        };

        $url = route('portal.discharges.show', $admission->id);

        $recipients = [];
        if ($admission->attending_doctor_id) {
            $recipients[] = $admission->attending_doctor_id;
        }

        $doctorNotifs = $this->send($recipients, 'discharge', $title, $message, $url);
        $staffNotifs = $this->sendToRoles(['nurse', 'billing_staff', 'receptionist'], 'discharge', $title, $message, $url);

        return array_merge($doctorNotifs, $staffNotifs);
    }

    /*
    |--------------------------------------------------------------------------
    | User Notification Management
    |--------------------------------------------------------------------------
    */

    public function getPaginatedForUser(User $user, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = SystemNotification::where('user_id', $user->id);

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['is_read']) && $filters['is_read'] !== '') {
            $query->where('is_read', (bool) $filters['is_read']);
        }

        return $query->latest('created_at')->paginate($perPage)->withQueryString();
    }

    public function getRecentUnreadForUser(User $user, int $limit = 5): Collection
    {
        return SystemNotification::where('user_id', $user->id)
            ->unread()
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    public function getUnreadCount(User $user): int
    {
        return SystemNotification::where('user_id', $user->id)
            ->unread()
            ->count();
    }

    public function markAsRead(int $notificationId, User $user): bool
    {
        $notification = SystemNotification::where('id', $notificationId)
            ->where('user_id', $user->id)
            ->first();

        if ($notification) {
            return $notification->markAsRead();
        }

        return false;
    }

    public function markAllAsRead(User $user): int
    {
        return SystemNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    public function deleteNotification(int $notificationId, User $user): bool
    {
        $notification = SystemNotification::where('id', $notificationId)
            ->where('user_id', $user->id)
            ->first();

        if ($notification) {
            return (bool) $notification->delete();
        }

        return false;
    }
}
