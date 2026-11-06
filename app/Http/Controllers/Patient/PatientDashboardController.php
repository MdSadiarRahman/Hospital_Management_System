<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\Appointment;
use App\Models\Bill;
use App\Models\LabReport;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\SystemNotification;
use App\Models\Visit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PatientDashboardController extends Controller
{
    /**
     * Display the Patient Portal health dashboard.
     * Accessible by both linked and unlinked patients.
     * Strictly scopes all clinical queries to the authenticated patient's records.
     */
    public function index(): View
    {
        $user = Auth::user();
        $patient = $user->patient?->load(['contacts', 'verifiedBy']);
        $isLinked = !is_null($patient) && $patient->is_verified;

        // Initialize variables
        $upcomingAppointments = collect();
        $recentVisits = collect();
        $prescriptions = collect();
        $labReports = collect();
        $recentBills = collect();
        $recentPayments = collect();
        $totalBilled = 0.00;
        $totalPaid = 0.00;
        $totalDue = 0.00;
        $currentAdmission = null;

        // 1. Upcoming Appointments (available to linked and unlinked self-registered patients)
        $apptQuery = Appointment::with(['doctor.staffProfile.department', 'department'])
            ->where('appointment_date', '>=', now()->startOfDay())
            ->whereNotIn('status', ['cancelled']);

        if ($patient) {
            $apptQuery->where('patient_id', $patient->id);
        } else {
            $apptQuery->where(function ($q) use ($user) {
                $q->where('guest_email', $user->email)
                  ->orWhere('guest_phone', $user->phone);
            });
        }
        $upcomingAppointments = $apptQuery->orderBy('appointment_date', 'asc')->take(5)->get();

        if ($isLinked) {

            // 2. Recent Visits (Outpatient / Emergency)
            $recentVisits = Visit::where('patient_id', $patient->id)
                ->with(['consultation.doctor.staffProfile.department', 'appointment'])
                ->orderBy('visit_date', 'desc')
                ->take(5)
                ->get();

            // 3. Available Authorized Prescriptions Only
            $prescriptions = Prescription::whereHas('consultation.visit', fn ($q) => $q->where('patient_id', $patient->id))
                ->where('status', 'approved')
                ->with(['consultation.doctor.staffProfile.department', 'consultation.diagnoses', 'items'])
                ->latest()
                ->take(6)
                ->get();

            // 4. Diagnostic Laboratory Reports
            $labReports = LabReport::whereHas('orderItem.labOrder', fn ($q) => $q->where('patient_id', $patient->id))
                ->with(['orderItem.labTest', 'orderItem.labOrder', 'reviewedBy.staffProfile'])
                ->latest()
                ->take(6)
                ->get();

            // 5. Billing Summary & Invoices
            $bills = Bill::where(function ($query) use ($patient) {
                    $query->whereHas('visit', fn ($q) => $q->where('patient_id', $patient->id))
                          ->orWhereHas('admission', fn ($q) => $q->where('patient_id', $patient->id));
                })
                ->with(['payments'])
                ->latest()
                ->get();

            $totalBilled = (float) $bills->sum('total_amount');
            $totalPaid = (float) $bills->sum('paid_amount');
            $totalDue = (float) $bills->sum('due_amount');
            $recentBills = $bills->take(5);

            // 6. Payment Status & Receipts
            $recentPayments = Payment::whereHas('bill', function ($query) use ($patient) {
                    $query->whereHas('visit', fn ($q) => $q->where('patient_id', $patient->id))
                          ->orWhereHas('admission', fn ($q) => $q->where('patient_id', $patient->id));
                })
                ->with('bill')
                ->latest('payment_date')
                ->take(5)
                ->get();

            // 7. Active Inpatient Admission (if currently in bed)
            $currentAdmission = Admission::where('patient_id', $patient->id)
                ->where('status', 'admitted')
                ->with(['currentBedAssignment.bed.department', 'attendingDoctor.staffProfile'])
                ->first();
        }

        // 8. System Notifications for this authenticated User
        $notifications = SystemNotification::where('user_id', $user->id)
            ->latest()
            ->take(6)
            ->get();

        $unreadNotificationsCount = SystemNotification::where('user_id', $user->id)
            ->unread()
            ->count();

        return view('patient.dashboard', compact(
            'user',
            'patient',
            'isLinked',
            'upcomingAppointments',
            'recentVisits',
            'prescriptions',
            'labReports',
            'recentBills',
            'recentPayments',
            'totalBilled',
            'totalPaid',
            'totalDue',
            'currentAdmission',
            'notifications',
            'unreadNotificationsCount'
        ));
    }

    /**
     * Secure view of a prescription with strict patient ownership check.
     */
    public function showPrescription(int $id): View
    {
        $prescription = Prescription::with([
            'consultation.doctor.staffProfile.department',
            'consultation.visit.patient',
            'consultation.diagnoses',
            'items',
        ])->findOrFail($id);

        $patient = Auth::user()->patient;

        if (!$patient || $prescription->consultation->visit->patient_id !== $patient->id || !$prescription->is_approved) {
            abort(403, 'Unauthorized access: You can only view your approved and authorized prescriptions.');
        }

        return view('patient.prescription-modal', compact('prescription', 'patient'));
    }

    /**
     * Secure download / view of a diagnostic lab report.
     * Strictly verifies report belongs to authenticated patient before streaming.
     */
    public function downloadLabReport(int $id): BinaryFileResponse|View
    {
        $report = LabReport::with([
            'orderItem.labOrder.patient',
            'orderItem.labTest',
            'reviewedBy.staffProfile',
        ])->findOrFail($id);

        $patient = Auth::user()->patient;

        if (!$patient || $report->orderItem->labOrder->patient_id !== $patient->id) {
            abort(403, 'Unauthorized access: You can only access your own diagnostic reports.');
        }

        // Check if physical report PDF exists in secure non-public storage
        if ($report->file_path && \Illuminate\Support\Facades\Storage::disk('secure_reports')->exists($report->file_path)) {
            $fileName = 'MeghnaClinic_' . ($report->orderItem->test_name ?? 'LabReport') . '_' . $report->id . '.pdf';
            return response()->download(\Illuminate\Support\Facades\Storage::disk('secure_reports')->path($report->file_path), $fileName);
        }

        $filePath = storage_path('app/secure_medical_reports/' . $report->file_path);
        if ($report->file_path && file_exists($filePath)) {
            $fileName = 'MeghnaClinic_' . ($report->orderItem->test_name ?? 'LabReport') . '_' . $report->id . '.pdf';
            return response()->download($filePath, $fileName);
        }

        // Printable secure report view if physical file is not on disk
        return view('patient.report-view', compact('report', 'patient'));
    }

    /**
     * Mark a system notification as read.
     */
    public function markNotificationAsRead(int $id): RedirectResponse
    {
        $notification = SystemNotification::where('user_id', Auth::id())->findOrFail($id);

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return back()->with('status', 'Notification marked as read.');
    }

    /**
     * Mark all notifications for authenticated patient as read.
     */
    public function markAllNotificationsAsRead(): RedirectResponse
    {
        SystemNotification::where('user_id', Auth::id())
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return back()->with('status', 'All notifications marked as read.');
    }
}
