<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admission\CancelAdmissionRequest;
use App\Http\Requests\Admission\StoreAdmissionRequest;
use App\Http\Requests\Admission\TransferBedRequest;
use App\Models\Admission;
use App\Models\Bed;
use App\Models\Department;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use App\Services\AdmissionService;
use App\Services\BedManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class AdmissionController extends Controller
{
    public function __construct(
        protected AdmissionService $admissionService,
        protected BedManagementService $bedManagementService
    ) {}

    /**
     * Display a listing of inpatient admissions.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Admission::class);

        $filters = $request->only(['search', 'status', 'department_id', 'doctor_id', 'date_from', 'date_to']);
        $admissions = $this->admissionService->getAdmissionsDirectory($filters, 15);
        $metrics = $this->admissionService->getAdmissionMetrics();

        $departments = Department::orderBy('name')->get();
        $doctors = User::where('user_type', 'staff')
            ->whereHas('roles', fn($q) => $q->where('name', 'doctor'))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('portal.admissions.index', compact('admissions', 'metrics', 'departments', 'doctors', 'filters'));
    }

    /**
     * Show the form for admitting an inpatient.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', Admission::class);

        $selectedPatientId = $request->query('patient_id');
        $selectedVisitId = $request->query('visit_id');

        $patients = Patient::orderBy('name')
            ->get(['id', 'patient_code', 'name', 'phone', 'gender', 'blood_group']);

        $departments = Department::where('is_active', true)->orderBy('name')->get();

        $doctors = User::where('user_type', 'staff')
            ->whereHas('roles', fn($q) => $q->where('name', 'doctor'))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $availableBeds = $this->admissionService->getAvailableBeds();

        // If patient selected, fetch their visits
        $patientVisits = collect();
        if ($selectedPatientId) {
            $patientVisits = Visit::where('patient_id', $selectedPatientId)
                ->orderByDesc('visit_date')
                ->get();
        }

        return view('portal.admissions.create', compact(
            'patients',
            'departments',
            'doctors',
            'availableBeds',
            'selectedPatientId',
            'selectedVisitId',
            'patientVisits'
        ));
    }

    /**
     * Store a newly created admission in storage.
     */
    public function store(StoreAdmissionRequest $request): RedirectResponse
    {
        try {
            $admission = $this->admissionService->createAdmission($request->validated(), $request->user());

            $bedNumber = $admission->currentBed?->bed_number ?? 'assigned';

            return redirect()->route('portal.admissions.show', $admission)
                ->with('success', "Inpatient admission {$admission->admission_number} successfully registered. Bed {$bedNumber} allocated.");
        } catch (InvalidArgumentException $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['bed_id' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified admission dossier and bed history.
     */
    public function show(Admission $admission): View
    {
        $this->authorize('view', $admission);

        $admission->load([
            'patient.emergencyContacts',
            'visit.consultation',
            'department',
            'attendingDoctor',
            'cancelledBy',
            'currentBedAssignment.bed.department',
            'bedAssignments.bed.department',
            'bedAssignments.assignedBy',
            'nursingNotes.author',
            'treatmentPlans.author',
            'bill',
            'dischargeSummary',
        ]);

        $availableBeds = $this->admissionService->getAvailableBeds();

        return view('portal.admissions.show', compact('admission', 'availableBeds'));
    }

    /**
     * Transfer patient to a different bed.
     */
    public function transferBed(TransferBedRequest $request, Admission $admission): RedirectResponse
    {
        $targetBed = Bed::findOrFail($request->validated('target_bed_id'));
        $reason = $request->validated('reason');

        try {
            $this->admissionService->transferBed($admission, $targetBed, $reason, $request->user());

            return redirect()->route('portal.admissions.show', $admission)
                ->with('success', "Patient successfully transferred to Bed {$targetBed->bed_number}.");
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withErrors(['target_bed_id' => $e->getMessage()]);
        }
    }

    /**
     * Cancel an active admission.
     */
    public function cancel(CancelAdmissionRequest $request, Admission $admission): RedirectResponse
    {
        $reason = $request->validated('cancellation_reason');

        try {
            $this->admissionService->cancelAdmission($admission, $reason, $request->user());

            return redirect()->route('portal.admissions.show', $admission)
                ->with('success', "Admission #{$admission->admission_number} has been cancelled and bed released.");
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withErrors(['cancellation_reason' => $e->getMessage()]);
        }
    }

    /**
     * AJAX endpoint: Get available beds filtered by department (or General beds).
     */
    public function getAvailableBedsApi(Request $request): JsonResponse
    {
        $departmentId = $request->query('department_id') ? (int) $request->query('department_id') : null;
        $beds = $this->admissionService->getAvailableBeds($departmentId);

        return response()->json([
            'beds' => $beds->map(fn($bed) => [
                'id' => $bed->id,
                'bed_number' => $bed->bed_number,
                'room_number' => $bed->room_number ?? 'Ward Room',
                'category' => $bed->category,
                'category_label' => $bed->category_label,
                'department_id' => $bed->department_id,
                'department_name' => $bed->department?->name ?? 'General Ward',
                'daily_charge' => number_format((float)$bed->daily_charge, 2),
                'description' => $bed->description,
            ]),
        ]);
    }

    /**
     * AJAX endpoint: Get visits for a specific patient.
     */
    public function getPatientVisitsApi(Patient $patient): JsonResponse
    {
        $visits = Visit::where('patient_id', $patient->id)
            ->orderByDesc('visit_date')
            ->get(['id', 'visit_number', 'visit_date', 'visit_type', 'status']);

        return response()->json([
            'visits' => $visits,
        ]);
    }
}

