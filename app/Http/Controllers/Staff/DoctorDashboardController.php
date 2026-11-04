<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\LabReport;
use App\Models\Patient;
use App\Models\User;
use App\Services\DoctorDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoctorDashboardController extends Controller
{
    public function __construct(
        protected DoctorDashboardService $doctorService
    ) {}

    /**
     * Display the Doctor Clinical Workstation Dashboard.
     */
    public function index(Request $request): View|JsonResponse
    {
        $user = $request->user();
        if (!$user->hasPermission('consultations.view') && !$user->hasRole(['doctor', 'super_admin', 'administrator'])) {
            abort(403, 'Unauthorized access to clinical doctor dashboard.');
        }

        $dashboardData = $this->doctorService->getDoctorDashboardData($user, $request->all());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'metrics' => $dashboardData['metrics'],
                'today_appointments_count' => $dashboardData['todayAppointments']->count(),
                'consultation_queue_count' => $dashboardData['consultationQueue']->count(),
                'active_admissions_count' => $dashboardData['activeAdmissions']->count(),
                'pending_lab_reports_count' => $dashboardData['pendingLabReports']->count(),
            ]);
        }

        return view('portal.doctor.dashboard', $dashboardData);
    }

    /**
     * Quick Patient Clinical History Dossier.
     */
    public function patientHistory(Request $request, Patient $patient): View|JsonResponse
    {
        $user = $request->user();
        if (!$user->hasPermission('patients.view') && !$user->hasRole(['doctor', 'super_admin', 'administrator', 'nurse'])) {
            abort(403, 'Unauthorized access to patient clinical history.');
        }

        $historyData = $this->doctorService->getPatientClinicalHistory($patient, $user);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'patient' => [
                    'id' => $patient->id,
                    'patient_code' => $patient->patient_code,
                    'name' => $patient->name,
                    'gender' => $patient->gender,
                    'age' => $patient->formatted_age,
                    'phone' => $patient->phone,
                    'blood_group' => $patient->blood_group,
                    'allergies' => $patient->allergies,
                ],
                'visits_count' => $historyData['visits']->count(),
                'admissions_count' => $historyData['admissions']->count(),
                'prescriptions_count' => $historyData['prescriptions']->count(),
                'lab_orders_count' => $historyData['labOrders']->count(),
            ]);
        }

        return view('portal.doctor.patient_history', $historyData);
    }

    /**
     * Doctor reviews and validates a pending diagnostic lab report.
     */
    public function reviewLabReport(Request $request, LabReport $labReport): RedirectResponse
    {
        $user = $request->user();
        if (!$user->hasPermission('lab.approve') && !$user->hasRole(['doctor', 'super_admin', 'administrator'])) {
            abort(403, 'Unauthorized to review diagnostic reports.');
        }

        $validated = $request->validate([
            'clinical_notes' => ['nullable', 'string', 'max:1000'],
            'findings_summary' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->doctorService->reviewLabReport(
            $labReport,
            $user,
            $validated['findings_summary'] ?? null,
            $validated['clinical_notes'] ?? null
        );

        return back()->with('success', "Diagnostic report for {$labReport->orderItem?->test_name} reviewed and verified successfully.");
    }

    /**
     * Live Patient Search API for Quick Consultation Lookup.
     */
    public function searchPatients(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->hasPermission('patients.view') && !$user->hasRole(['doctor', 'super_admin', 'administrator', 'nurse'])) {
            return response()->json(['results' => []], 403);
        }

        $term = trim($request->input('q', ''));
        if (strlen($term) < 2) {
            return response()->json(['results' => []]);
        }

        $results = $this->doctorService->searchPatients($term, $user);

        return response()->json([
            'results' => $results->map(fn(Patient $p) => [
                'id' => $p->id,
                'patient_code' => $p->patient_code,
                'name' => $p->name,
                'age' => $p->formatted_age,
                'gender' => ucfirst($p->gender),
                'phone' => $p->phone,
                'active_bed' => $p->activeAdmission?->currentBed?->bed_number,
                'history_url' => route('portal.doctor.patient-history', $p),
            ]),
        ]);
    }
}

