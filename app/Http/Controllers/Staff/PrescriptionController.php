<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Prescription\StorePrescriptionRequest;
use App\Http\Requests\Prescription\UpdatePrescriptionRequest;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use App\Services\PrescriptionService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrescriptionController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected PrescriptionService $prescriptionService
    ) {}

    /**
     * Display a listing of prescriptions.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Prescription::class);

        $currentUser = $request->user();
        $filters = $request->only(['status', 'doctor_id', 'date', 'q']);

        if ($currentUser->hasRole('doctor') && !$currentUser->hasRole(['super_admin', 'administrator']) && !isset($filters['doctor_id'])) {
            $filters['doctor_id'] = $currentUser->id;
        }

        $prescriptions = $this->prescriptionService->getPrescriptionsList($filters, 15);
        $doctors = User::whereHas('roles', fn($q) => $q->where('name', 'doctor'))
            ->with('staffProfile.department')
            ->get();

        return view('portal.prescriptions.index', [
            'prescriptions' => $prescriptions,
            'filters' => $filters,
            'doctors' => $doctors,
        ]);
    }

    /**
     * Show the form for creating a new prescription for a consultation.
     */
    public function create(Request $request, Consultation $consultation): View|RedirectResponse
    {
        $this->authorize('create', Prescription::class);

        if ($consultation->prescription()->exists()) {
            return redirect()->route('portal.prescriptions.show', $consultation->prescription)
                ->with('info', "Prescription {$consultation->prescription->prescription_number} is already active for Consultation #{$consultation->id}.");
        }

        $consultation->loadMissing([
            'visit.patient.contacts',
            'visit.patient.admissions.currentBedAssignment.bed',
            'diagnoses',
            'doctor.staffProfile.department',
        ]);

        return view('portal.prescriptions.create', [
            'consultation' => $consultation,
            'patient' => $consultation->visit?->patient,
            'diagnoses' => $consultation->diagnoses,
            'doctor' => $consultation->doctor,
        ]);
    }

    /**
     * Store a newly created prescription in storage.
     */
    public function store(StorePrescriptionRequest $request): RedirectResponse
    {
        $consultation = Consultation::findOrFail($request->input('consultation_id'));

        if ($consultation->prescription()->exists()) {
            return redirect()->route('portal.prescriptions.show', $consultation->prescription)
                ->with('error', "Consultation #{$consultation->id} already has a prescription.");
        }

        $prescription = $this->prescriptionService->createPrescription(
            $consultation,
            $request->validated(),
            $request->user()
        );

        $msg = $prescription->is_approved
            ? "Prescription {$prescription->prescription_number} created and approved successfully."
            : "Prescription {$prescription->prescription_number} saved as draft.";

        return redirect()->route('portal.prescriptions.show', $prescription)->with('success', $msg);
    }

    /**
     * Display the specified prescription dossier.
     */
    public function show(Prescription $prescription): View
    {
        $this->authorize('view', $prescription);

        $dossier = $this->prescriptionService->getPrescriptionDossier($prescription);

        return view('portal.prescriptions.show', $dossier);
    }

    /**
     * Show the form for editing the specified prescription.
     */
    public function edit(Prescription $prescription): View
    {
        $this->authorize('update', $prescription);

        $dossier = $this->prescriptionService->getPrescriptionDossier($prescription);

        return view('portal.prescriptions.edit', $dossier);
    }

    /**
     * Update the specified prescription in storage.
     */
    public function update(UpdatePrescriptionRequest $request, Prescription $prescription): RedirectResponse
    {
        $updated = $this->prescriptionService->updatePrescription(
            $prescription,
            $request->validated(),
            $request->user(),
            $request->input('correction_reason')
        );

        $msg = $updated->is_approved && !$prescription->is_approved
            ? "Prescription {$updated->prescription_number} has been approved."
            : ($prescription->is_approved
                ? "Prescription {$updated->prescription_number} amended with documented audit correction."
                : "Prescription draft updated successfully.");

        return redirect()->route('portal.prescriptions.show', $updated)->with('success', $msg);
    }

    /**
     * One-click approval of draft prescription by authorized doctor.
     */
    public function approve(Request $request, Prescription $prescription): RedirectResponse
    {
        $this->authorize('approve', $prescription);

        try {
            $this->prescriptionService->approvePrescription($prescription, $request->user());
            return redirect()->route('portal.prescriptions.show', $prescription)
                ->with('success', "Prescription {$prescription->prescription_number} is now officially approved.");
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Print prescription slip with security features and watermark.
     */
    public function print(Prescription $prescription): View
    {
        $this->authorize('print', $prescription);

        $dossier = $this->prescriptionService->getPrescriptionDossier($prescription);

        return view('portal.prescriptions.print', $dossier);
    }

    /**
     * View complete historical prescriptions timeline for a specific patient.
     */
    public function patientHistory(Request $request, Patient $patient): View
    {
        $this->authorize('viewAny', Prescription::class);

        $prescriptions = $this->prescriptionService->getPatientPrescriptions($patient, false, 15);

        return view('portal.prescriptions.patient-history', [
            'patient' => $patient,
            'prescriptions' => $prescriptions,
        ]);
    }
}

