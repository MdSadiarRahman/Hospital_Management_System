<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Consultation\StoreConsultationRequest;
use App\Http\Requests\Consultation\UpdateConsultationRequest;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use App\Services\ConsultationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConsultationController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected ConsultationService $consultationService
    ) {}

    /**
     * Display a listing of consultations.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Consultation::class);

        $currentUser = $request->user();
        $filters = $request->only(['status', 'doctor_id', 'date', 'q']);

        // Doctors default to viewing their own consultations unless explicit filter or administrator
        if ($currentUser->hasRole('doctor') && !$currentUser->hasRole(['super_admin', 'administrator']) && !isset($filters['doctor_id'])) {
            $filters['doctor_id'] = $currentUser->id;
        }

        $consultations = $this->consultationService->getConsultationsList($filters, 15);
        $doctors = User::whereHas('roles', fn($q) => $q->where('name', 'doctor'))
            ->with('staffProfile.department')
            ->get();

        return view('portal.consultations.index', [
            'consultations' => $consultations,
            'filters' => $filters,
            'doctors' => $doctors,
        ]);
    }

    /**
     * Show the form for creating a new clinical consultation for a visit.
     */
    public function create(Request $request, Visit $visit): View|RedirectResponse
    {
        $this->authorize('create', Consultation::class);

        // If a consultation already exists for this visit, redirect to its detail or edit view
        if ($visit->consultation()->exists()) {
            $existing = $visit->consultation;
            return redirect()->route('portal.consultations.show', $existing)
                ->with('info', "A consultation record (#{$existing->id}) is already active for Visit {$visit->visit_number}.");
        }

        $visit->loadMissing([
            'patient.contacts',
            'patient.admissions.currentBedAssignment.bed.department',
            'appointment',
        ]);

        $patient = $visit->patient;

        // Past clinical consultations for this patient
        $pastConsultations = Consultation::whereHas('visit', fn($q) => $q->where('patient_id', $patient->id))
            ->with(['doctor', 'diagnoses', 'prescription.items'])
            ->latest('created_at')
            ->take(5)
            ->get();

        // Past visits
        $pastVisits = $patient->visits()
            ->where('id', '!=', $visit->id)
            ->with(['consultation.diagnoses', 'admissions'])
            ->latest('visit_date')
            ->take(5)
            ->get();

        return view('portal.consultations.create', [
            'visit' => $visit,
            'patient' => $patient,
            'pastConsultations' => $pastConsultations,
            'pastVisits' => $pastVisits,
        ]);
    }

    /**
     * Store a newly created consultation in storage.
     */
    public function store(StoreConsultationRequest $request): RedirectResponse
    {
        $visit = Visit::findOrFail($request->input('visit_id'));

        // Prevent duplicate consultation for same visit
        if ($visit->consultation()->exists()) {
            return redirect()->route('portal.consultations.show', $visit->consultation)
                ->with('error', "Visit {$visit->visit_number} already has an active consultation.");
        }

        $consultation = $this->consultationService->createConsultation(
            $visit,
            $request->user(),
            $request->validated()
        );

        $statusMessage = $consultation->status === 'finalized'
            ? "Clinical consultation #{$consultation->id} finalized and recorded successfully."
            : "Clinical consultation #{$consultation->id} saved as draft.";

        return redirect()->route('portal.consultations.show', $consultation)
            ->with('success', $statusMessage);
    }

    /**
     * Display the specified consultation dossier.
     */
    public function show(Consultation $consultation): View
    {
        $this->authorize('view', $consultation);

        $dossier = $this->consultationService->getConsultationDossier($consultation);

        return view('portal.consultations.show', $dossier);
    }

    /**
     * Show the form for editing the specified consultation.
     */
    public function edit(Consultation $consultation): View
    {
        $this->authorize('update', $consultation);

        $dossier = $this->consultationService->getConsultationDossier($consultation);

        return view('portal.consultations.edit', $dossier);
    }

    /**
     * Update the specified consultation in storage.
     */
    public function update(UpdateConsultationRequest $request, Consultation $consultation): RedirectResponse
    {
        $validated = $request->validated();
        $correctionReason = $request->input('correction_reason');

        $updated = $this->consultationService->updateConsultation(
            $consultation,
            $request->user(),
            $validated,
            $correctionReason
        );

        $message = $updated->status === 'finalized' && $consultation->status !== 'finalized'
            ? "Consultation #{$updated->id} has been finalized."
            : ($consultation->status === 'finalized'
                ? "Clinical correction saved and audit logged for Consultation #{$updated->id}."
                : "Consultation draft updated successfully.");

        return redirect()->route('portal.consultations.show', $updated)
            ->with('success', $message);
    }

    /**
     * Direct one-click finalization of draft consultation.
     */
    public function finalize(Request $request, Consultation $consultation): RedirectResponse
    {
        $this->authorize('approve', $consultation);

        try {
            $this->consultationService->finalizeConsultation($consultation, $request->user());
            return redirect()->route('portal.consultations.show', $consultation)
                ->with('success', "Consultation #{$consultation->id} is now permanently finalized.");
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Print preview / paper slip for prescription and consultation report.
     */
    public function print(Consultation $consultation): View
    {
        $this->authorize('print', $consultation);

        $dossier = $this->consultationService->getConsultationDossier($consultation);

        return view('portal.consultations.print', $dossier);
    }

    /**
     * View complete historical consultations timeline for a specific patient.
     */
    public function patientHistory(Request $request, Patient $patient): View
    {
        $this->authorize('viewAny', Consultation::class);

        $consultations = Consultation::whereHas('visit', fn($q) => $q->where('patient_id', $patient->id))
            ->with(['doctor.staffProfile.department', 'diagnoses', 'prescription.items', 'visit'])
            ->latest('created_at')
            ->paginate(15);

        return view('portal.consultations.patient-history', [
            'patient' => $patient,
            'consultations' => $consultations,
        ]);
    }
}
