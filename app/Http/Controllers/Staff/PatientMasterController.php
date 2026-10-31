<?php

namespace App\Http\Controllers\Staff;

use App\Exceptions\DuplicatePatientException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\StorePatientMasterRequest;
use App\Http\Requests\Patient\UpdatePatientMasterRequest;
use App\Models\Patient;
use App\Services\PatientRegistrationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PatientMasterController extends Controller
{
    protected PatientRegistrationService $patientService;

    public function __construct(PatientRegistrationService $patientService)
    {
        $this->patientService = $patientService;
    }

    /**
     * Display a paginated directory of registered hospital patients with multi-parameter search & filters.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Patient::class);

        $query = Patient::with(['primaryContact', 'user']);

        // Search across patient code, name, phone, email, and NID
        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        // Gender filter
        if ($request->filled('gender')) {
            $query->where('gender', $request->input('gender'));
        }

        // Blood group filter
        if ($request->filled('blood_group')) {
            $query->where('blood_group', $request->input('blood_group'));
        }

        // Registration Date Range filter
        if ($request->filled('date_from')) {
            $query->whereDate('registration_date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('registration_date', '<=', $request->input('date_to'));
        }

        // Online account verification status
        if ($request->filled('is_verified')) {
            $query->where('is_verified', $request->boolean('is_verified'));
        }

        // Sorting
        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'oldest' => $query->orderBy('id', 'asc'),
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            'patient_code' => $query->orderBy('patient_code', 'desc'),
            default => $query->orderBy('id', 'desc'),
        };

        $patients = $query->paginate(15)->withQueryString();

        // Bento KPI Metrics
        $totalPatients = Patient::count();
        $newToday = Patient::whereDate('registration_date', Carbon::today())->count();
        $maleCount = Patient::where('gender', 'male')->count();
        $femaleCount = Patient::where('gender', 'female')->count();
        $verifiedCount = Patient::where('is_verified', true)->count();

        return view('portal.patients.index', compact(
            'patients',
            'totalPatients',
            'newToday',
            'maleCount',
            'femaleCount',
            'verifiedCount'
        ));
    }

    /**
     * Show the patient intake & registration form.
     */
    public function create(): View
    {
        Gate::authorize('create', Patient::class);

        $suggestedPatientId = $this->patientService->generatePatientId();

        return view('portal.patients.create', compact('suggestedPatientId'));
    }

    /**
     * Real-time AJAX endpoint to check for potential duplicate patient records before submitting.
     */
    public function checkDuplicates(Request $request): JsonResponse
    {
        Gate::authorize('create', Patient::class);

        $duplicates = $this->patientService->findPotentialDuplicates(
            $request->only(['phone', 'nid_or_birth_cert', 'name', 'date_of_birth', 'email']),
            $request->input('exclude_id') ? (int) $request->input('exclude_id') : null
        );

        return response()->json([
            'has_duplicates' => $duplicates->isNotEmpty(),
            'count' => $duplicates->count(),
            'duplicates' => $duplicates->map(fn ($p) => [
                'id' => $p->id,
                'patient_code' => $p->patient_code,
                'name' => $p->name,
                'phone' => $p->phone,
                'gender' => ucfirst($p->gender),
                'date_of_birth' => $p->date_of_birth?->format('d M Y'),
                'age' => $p->formatted_age,
                'blood_group' => $p->blood_group ?? 'N/A',
                'registration_date' => $p->registration_date?->format('d M Y') ?? $p->created_at->format('d M Y'),
                'url' => route('portal.patients.show', $p->id),
            ]),
        ]);
    }

    /**
     * Store a newly registered patient in the master registry.
     */
    public function store(StorePatientMasterRequest $request): RedirectResponse
    {
        $patientData = $request->only([
            'patient_code',
            'name',
            'date_of_birth',
            'gender',
            'blood_group',
            'phone',
            'email',
            'nid_or_birth_cert',
            'address',
            'registration_date',
        ]);

        $contactData = [
            'name' => $request->input('emergency_contact_name'),
            'relationship' => $request->input('emergency_contact_relationship'),
            'phone' => $request->input('emergency_contact_phone'),
        ];

        $confirmNotDuplicate = $request->boolean('confirm_not_duplicate');

        try {
            $patient = $this->patientService->registerPatient(
                $patientData,
                $contactData,
                $request->user(),
                $confirmNotDuplicate
            );

            return redirect()
                ->route('portal.patients.show', $patient)
                ->with('success', "Patient [{$patient->patient_code}] {$patient->name} successfully registered in hospital master records.");
        } catch (DuplicatePatientException $e) {
            return back()
                ->withInput()
                ->with('duplicate_patients', $e->getDuplicates())
                ->with('duplicate_warning', 'Possible matching patient records were found in the clinic database. Please verify whether the patient is already registered before creating a new duplicate profile.');
        }
    }

    /**
     * Display a comprehensive patient master profile with emergency contacts, medical record overview, and audit log.
     */
    public function show(Request $request, Patient $patient): View
    {
        Gate::authorize('view', $patient);

        $patient->load([
            'primaryContact',
            'contacts',
            'user',
            'verifiedBy',
            'auditLogs.user',
        ]);

        // Visits with search / filtering
        $visitsQuery = $patient->visits()
            ->with([
                'appointment.department',
                'consultation.doctor.staffProfile',
                'consultation.diagnoses',
                'admissions.currentBedAssignment.bed.department',
                'admissions.dischargeSummary',
            ])
            ->latest('visit_date');

        if ($request->filled('visit_type')) {
            $visitsQuery->where('visit_type', $request->input('visit_type'));
        }
        if ($request->filled('visit_search')) {
            $term = $request->input('visit_search');
            $visitsQuery->where('visit_number', 'like', "%{$term}%");
        }

        $visits = $visitsQuery->get();

        // Previous Consultations
        $consultations = $patient->consultations()
            ->with([
                'doctor.staffProfile.department',
                'visit',
                'diagnoses',
                'prescription.items',
                'labOrders.items.report',
            ])
            ->latest('created_at')
            ->get();

        // Previous Admissions
        $admissions = $patient->admissions()
            ->with([
                'visit',
                'attendingDoctor.staffProfile',
                'currentBedAssignment.bed.department',
                'bedAssignments.bed.department',
                'clinicalClearanceBy',
                'financialClearanceBy',
                'dischargeSummary.authorizedBy',
            ])
            ->latest('admission_date')
            ->get();

        // Prescriptions through patient visits & consultations
        $prescriptions = \App\Models\Prescription::whereHas('consultation.visit', function ($q) use ($patient) {
                $q->where('patient_id', $patient->id);
            })
            ->with(['consultation.doctor.staffProfile', 'consultation.visit', 'items'])
            ->latest('created_at')
            ->get();

        // Laboratory History
        $labOrders = $patient->labOrders()
            ->with([
                'consultation.doctor',
                'labPartner',
                'items.report.reviewedBy',
                'samples',
            ])
            ->latest('created_at')
            ->get();

        // Discharge History
        $dischargeSummaries = $patient->dischargeSummaries()
            ->with(['admission.attendingDoctor', 'authorizedBy'])
            ->latest('created_at')
            ->get();

        $clinicalMetrics = app(\App\Services\VisitService::class)->getPatientClinicalMetrics($patient);
        $activeTab = $request->input('tab', 'overview');

        return view('portal.patients.show', compact(
            'patient',
            'visits',
            'consultations',
            'admissions',
            'prescriptions',
            'labOrders',
            'dischargeSummaries',
            'clinicalMetrics',
            'activeTab'
        ));
    }

    /**
     * Display a dedicated full-page printable Patient Medical Record Overview / Chart.
     */
    public function medicalRecord(Request $request, Patient $patient): View
    {
        Gate::authorize('view', $patient);

        $patient->load([
            'primaryContact',
            'contacts',
            'auditLogs.user',
        ]);

        $visits = $patient->visits()
            ->with([
                'appointment.department',
                'consultation.doctor.staffProfile',
                'consultation.diagnoses',
                'admissions.currentBedAssignment.bed.department',
                'admissions.dischargeSummary',
            ])
            ->latest('visit_date')
            ->get();

        $consultations = $patient->consultations()
            ->with(['doctor.staffProfile.department', 'visit', 'diagnoses', 'prescription.items'])
            ->latest('created_at')
            ->get();

        $admissions = $patient->admissions()
            ->with(['visit', 'attendingDoctor', 'currentBedAssignment.bed.department', 'dischargeSummary.authorizedBy'])
            ->latest('admission_date')
            ->get();

        $prescriptions = \App\Models\Prescription::whereHas('consultation.visit', fn($q) => $q->where('patient_id', $patient->id))
            ->with(['consultation.doctor', 'consultation.visit', 'items'])
            ->latest('created_at')
            ->get();

        $labOrders = $patient->labOrders()
            ->with(['consultation.doctor', 'labPartner', 'items.report.reviewedBy'])
            ->latest('created_at')
            ->get();

        $dischargeSummaries = $patient->dischargeSummaries()
            ->with(['admission.attendingDoctor', 'authorizedBy'])
            ->latest('created_at')
            ->get();

        $clinicalMetrics = app(\App\Services\VisitService::class)->getPatientClinicalMetrics($patient);

        return view('portal.patients.medical-record', compact(
            'patient',
            'visits',
            'consultations',
            'admissions',
            'prescriptions',
            'labOrders',
            'dischargeSummaries',
            'clinicalMetrics'
        ));
    }

    /**
     * Show the form for editing patient demographic and emergency contact details.
     */
    public function edit(Patient $patient): View
    {
        Gate::authorize('update', $patient);

        $patient->load('primaryContact');

        return view('portal.patients.edit', compact('patient'));
    }

    /**
     * Update an existing patient master record in storage.
     */
    public function update(UpdatePatientMasterRequest $request, Patient $patient): RedirectResponse
    {
        $patientData = $request->only([
            'name',
            'date_of_birth',
            'gender',
            'blood_group',
            'phone',
            'email',
            'nid_or_birth_cert',
            'address',
            'registration_date',
        ]);

        $contactData = [
            'name' => $request->input('emergency_contact_name'),
            'relationship' => $request->input('emergency_contact_relationship'),
            'phone' => $request->input('emergency_contact_phone'),
        ];

        $updatedPatient = $this->patientService->updatePatient(
            $patient,
            $patientData,
            $contactData,
            $request->user()
        );

        return redirect()
            ->route('portal.patients.show', $updatedPatient)
            ->with('success', "Patient record [{$updatedPatient->patient_code}] successfully updated.");
    }

    /**
     * Render the printable patient registration card / slip.
     */
    public function printCard(Patient $patient): View
    {
        Gate::authorize('print', $patient);

        $patient->load(['primaryContact', 'contacts']);

        return view('portal.patients.print-card', compact('patient'));
    }
}
