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
}

