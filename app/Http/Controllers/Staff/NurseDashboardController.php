<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Nurse\StoreMedicationAdministrationRequest;
use App\Http\Requests\Nurse\StoreVitalsRequest;
use App\Http\Requests\Nurse\UpdateBedCleaningRequest;
use App\Models\Admission;
use App\Models\Bed;
use App\Services\NurseDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NurseDashboardController extends Controller
{
    public function __construct(
        protected NurseDashboardService $nurseService
    ) {}

    /**
     * Display the Nurse Ward Station Dashboard.
     */
    public function index(Request $request): View|JsonResponse
    {
        $user = $request->user();
        if (!$user->hasPermission('nursing.view') && !$user->hasRole(['nurse', 'super_admin', 'administrator'])) {
            abort(403, 'Unauthorized access to inpatient nursing station.');
        }

        $dashboardData = $this->nurseService->getNurseDashboardData($user, $request->all());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'metrics' => $dashboardData['metrics'],
                'active_admissions_count' => $dashboardData['activeAdmissions']->count(),
                'ward_beds_count' => $dashboardData['wardBeds']->count(),
                'vitals_due_count' => count($dashboardData['pendingTasks']['vitals_due']),
                'medications_due_count' => count($dashboardData['pendingTasks']['medications_due']),
                'abnormal_vitals_count' => count($dashboardData['pendingTasks']['abnormal_vitals']),
            ]);
        }

        return view('portal.nurse.dashboard', $dashboardData);
    }

    /**
     * Record bedside patient vital signs & nursing observation note.
     */
    public function storeVitals(StoreVitalsRequest $request, Admission $admission): RedirectResponse|JsonResponse
    {
        $note = $this->nurseService->recordVitals($admission, $request->validated(), $request->user());

        $message = "Bedside vital signs and observation notes for Bed " .
            ($admission->currentBed?->bed_number ?? 'Inpatient') .
            " ({$admission->patient?->name}) recorded successfully.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $note,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Record a medication administration event (MAR).
     */
    public function storeMedication(StoreMedicationAdministrationRequest $request, Admission $admission): RedirectResponse|JsonResponse
    {
        $mar = $this->nurseService->recordMedicationAdministration($admission, $request->validated(), $request->user());

        $statusLabel = match ($mar->status) {
            'administered' => 'administered',
            'omitted' => 'omitted',
            'refused' => 'marked as refused',
            default => 'recorded',
        };

        $message = "Medication '{$mar->medicine_name}' ({$mar->dose_given}) successfully {$statusLabel} for {$admission->patient?->name}.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $mar,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Update bed cleaning / sanitization readiness status.
     */
    public function updateBedStatus(UpdateBedCleaningRequest $request, Bed $bed): RedirectResponse|JsonResponse
    {
        $updatedBed = $this->nurseService->updateBedReadiness(
            $bed,
            $request->input('status'),
            $request->user(),
            $request->input('notes')
        );

        $statusDisplay = ucfirst($updatedBed->status);
        $message = "Bed {$updatedBed->bed_number} status updated to '{$statusDisplay}'.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $updatedBed,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Display complete nursing dossier for an admission (Read-Only clinical records).
     */
    public function admissionDossier(Request $request, Admission $admission): View|JsonResponse
    {
        $user = $request->user();
        if (!$user->hasPermission('nursing.view') && !$user->hasRole(['nurse', 'super_admin', 'administrator', 'doctor'])) {
            abort(403, 'Unauthorized access to admission nursing dossier.');
        }

        $dossier = $this->nurseService->getAdmissionNursingDossier($admission, $user);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'admission_number' => $admission->admission_number,
                'patient_name' => $admission->patient?->name,
                'bed_number' => $admission->currentBed?->bed_number,
                'vitals_count' => $dossier['nursingNotes']->count(),
                'mar_count' => $dossier['medicationAdministrations']->count(),
                'prescriptions_count' => $dossier['approvedPrescriptions']->count(),
            ]);
        }

        return view('portal.nurse.dossier', $dossier);
    }

    /**
     * Live search for active inpatients by name, code, or bed number.
     */
    public function searchInpatients(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->hasPermission('nursing.view') && !$user->hasRole(['nurse', 'super_admin', 'administrator', 'doctor'])) {
            return response()->json(['results' => []], 403);
        }

        $term = trim($request->input('q', ''));
        if (strlen($term) < 2) {
            return response()->json(['results' => []]);
        }

        $results = $this->nurseService->searchInpatients($term, $user);

        return response()->json([
            'results' => $results->map(fn(Admission $a) => [
                'id' => $a->id,
                'admission_number' => $a->admission_number,
                'patient_name' => $a->patient?->name,
                'patient_code' => $a->patient?->patient_code,
                'age_gender' => $a->patient?->formatted_age . ' • ' . ucfirst($a->patient?->gender ?? ''),
                'bed_number' => $a->currentBed?->bed_number ?? 'No Bed',
                'department' => $a->department?->name ?? 'Ward',
                'doctor' => $a->attendingDoctor?->name ?? 'Unassigned',
                'latest_vitals' => $a->nursingNotes->first()?->formatted_vitals ?? 'Pending vitals',
            ]),
        ]);
    }

    /**
     * Display Master Inpatient Nursing Observations Ledger.
     */
    public function observationsIndex(Request $request): View
    {
        $user = $request->user();
        if (!$user->hasPermission('nursing.view') && !$user->hasRole(['nurse', 'doctor', 'super_admin', 'administrator'])) {
            abort(403, 'Unauthorized access to clinical nursing observations.');
        }

        $filters = $request->only(['admission_id', 'shift', 'status', 'nurse_id', 'date_from', 'date_to']);

        $query = \App\Models\NursingNote::with([
            'admission.patient',
            'admission.currentBedAssignment.bed',
            'admission.department',
            'nurse.staffProfile',
            'reviewedBy.staffProfile',
        ])
        ->filter($filters)
        ->recent();

        $observations = $query->paginate(20)->withQueryString();

        $activeAdmissions = Admission::where('status', 'admitted')->with('patient', 'currentBedAssignment.bed')->get();
        $nurses = \App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'nurse'))->get();

        return view('portal.nurse.observations_index', [
            'observations' => $observations,
            'activeAdmissions' => $activeAdmissions,
            'nurses' => $nurses,
            'filters' => $filters,
            'totalObservations' => \App\Models\NursingNote::count(),
            'flaggedCount' => \App\Models\NursingNote::where('is_flagged_abnormal', true)->count(),
            'pendingReviewCount' => \App\Models\NursingNote::where('clinical_review_status', 'flagged_for_review')->count(),
        ]);
    }

    /**
     * Patient Vital Trends Analytics & Historical Trajectory View.
     */
    public function vitalsTrends(Request $request, Admission $admission): View|JsonResponse
    {
        $user = $request->user();
        if (!$user->hasPermission('nursing.view') && !$user->hasRole(['nurse', 'doctor', 'super_admin', 'administrator'])) {
            abort(403, 'Unauthorized access to patient vital trends.');
        }

        $trendData = $this->nurseService->getPatientVitalTrends($admission);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'admission_number' => $admission->admission_number,
                'patient_name' => $admission->patient?->name,
                'stats' => $trendData['stats'],
                'timeline' => $trendData['timelineNotes']->map(fn($n) => [
                    'recorded_at' => $n->recorded_at->toIso8601String(),
                    'temperature' => $n->temperature,
                    'blood_pressure' => $n->blood_pressure,
                    'pulse_rate' => $n->pulse_rate,
                    'spo2' => $n->spo2,
                    'respiratory_rate' => $n->respiratory_rate,
                    'weight' => $n->weight,
                    'is_flagged_abnormal' => $n->is_flagged_abnormal,
                    'clinical_flags' => $n->clinical_flags,
                ]),
            ]);
        }

        return view('portal.nurse.vitals_trends', $trendData);
    }

    /**
     * Authorized clinical physician / supervisor reviews a flagged observation.
     */
    public function reviewObservation(Request $request, \App\Models\NursingNote $note): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        if (!$user->hasRole(['doctor', 'super_admin', 'administrator']) && !$user->hasPermission('consultations.approve')) {
            abort(403, 'Unauthorized. Only attending physicians or clinical administrators may verify flagged observations.');
        }

        $validated = $request->validate([
            'review_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $reviewedNote = $this->nurseService->reviewObservation(
            $note,
            $user,
            $validated['review_notes'] ?? null
        );

        $message = "Flagged observation for Bed " .
            ($note->admission?->currentBed?->bed_number ?? 'Inpatient') .
            " ({$note->admission?->patient?->name}) reviewed and marked clinically verified.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $reviewedNote,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Printable Bedside Vital Signs Flowsheet & Observation Sheet.
     */
    public function printFlowsheet(Request $request, Admission $admission): View
    {
        $user = $request->user();
        if (!$user->hasPermission('nursing.view') && !$user->hasRole(['nurse', 'doctor', 'super_admin', 'administrator'])) {
            abort(403, 'Unauthorized.');
        }

        $trendData = $this->nurseService->getPatientVitalTrends($admission);

        return view('portal.nurse.print_flowsheet', $trendData);
    }
}

