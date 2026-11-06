<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Referral\CancelReferralRequest;
use App\Http\Requests\Referral\CompleteTransferRequest;
use App\Http\Requests\Referral\DispatchTransitRequest;
use App\Http\Requests\Referral\StoreReferralRequest;
use App\Http\Requests\Referral\UpdateAssessmentRequest;
use App\Models\Admission;
use App\Models\Patient;
use App\Models\Referral;
use App\Models\User;
use App\Models\Visit;
use App\Services\EmergencyReferralService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReferralController extends Controller
{
    public function __construct(
        protected EmergencyReferralService $referralService
    ) {}

    /**
     * Display Emergency Referral workbench & ledger.
     */
    public function index(Request $request): View
    {
        $query = Referral::query()
            ->with([
                'patient',
                'visit',
                'admission.currentBedAssignment.bed',
                'authorizedBy',
            ])
            ->latest('referred_at');

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by Referral Type
        if ($request->filled('referral_type')) {
            $query->where('referral_type', $request->referral_type);
        }

        // Filter by Critical Condition
        if ($request->boolean('is_critical')) {
            $query->where('is_critical', true);
        }

        // Filter by ICU/CCU requirement
        if ($request->boolean('requires_icu')) {
            $query->where(function ($q) {
                $q->where('requires_icu', true)
                  ->orWhere('referral_type', 'icu_ccu_emergency');
            });
        }

        // Filter by Destination Facility
        if ($request->filled('facility_name')) {
            $query->where('facility_name', 'like', "%{$request->facility_name}%");
        }

        // Filter by Date
        if ($request->filled('date')) {
            $query->whereDate('referred_at', $request->date);
        }

        // Keyword Search
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('referral_number', 'like', "%{$term}%")
                    ->orWhere('facility_name', 'like', "%{$term}%")
                    ->orWhere('clinical_reason', 'like', "%{$term}%")
                    ->orWhereHas('patient', function ($pq) use ($term) {
                        $pq->where('name', 'like', "%{$term}%")
                            ->orWhere('patient_code', 'like', "%{$term}%")
                            ->orWhere('phone', 'like', "%{$term}%");
                    });
            });
        }

        $referrals = $query->paginate(15)->withQueryString();
        $statistics = $this->referralService->getStatistics();

        return view('portal.referrals.index', [
            'referrals' => $referrals,
            'statistics' => $statistics,
            'destinations' => EmergencyReferralService::COMMON_DESTINATIONS,
            'filters' => $request->all(),
        ]);
    }

    /**
     * Show form to initiate an emergency referral.
     */
    public function create(Request $request): View
    {
        $selectedPatient = null;
        $selectedAdmission = null;
        $selectedVisit = null;

        if ($request->filled('admission_id')) {
            $selectedAdmission = Admission::with(['patient', 'currentBedAssignment.bed', 'attendingDoctor'])->find($request->admission_id);
            $selectedPatient = $selectedAdmission?->patient;
        } elseif ($request->filled('visit_id')) {
            $selectedVisit = Visit::with('patient')->find($request->visit_id);
            $selectedPatient = $selectedVisit?->patient;
        } elseif ($request->filled('patient_id')) {
            $selectedPatient = Patient::find($request->patient_id);
        }

        $activeAdmissions = Admission::query()
            ->where('status', 'admitted')
            ->with(['patient', 'currentBedAssignment.bed'])
            ->latest('admission_date')
            ->get();

        $emergencyVisits = Visit::query()
            ->whereIn('status', ['waiting', 'in_consultation'])
            ->with('patient')
            ->latest('visit_date')
            ->get();

        return view('portal.referrals.create', [
            'selectedPatient' => $selectedPatient,
            'selectedAdmission' => $selectedAdmission,
            'selectedVisit' => $selectedVisit,
            'activeAdmissions' => $activeAdmissions,
            'emergencyVisits' => $emergencyVisits,
            'commonDestinations' => EmergencyReferralService::COMMON_DESTINATIONS,
        ]);
    }

    /**
     * Store and authorize emergency referral.
     */
    public function store(StoreReferralRequest $request): RedirectResponse
    {
        $referral = $this->referralService->createEmergencyReferral($request->validated(), $request->user());

        return redirect()
            ->route('portal.referrals.show', $referral)
            ->with('success', "Emergency Referral {$referral->referral_number} initiated to {$referral->facility_name}.");
    }

    /**
     * Show referral clinical dossier, handover details, and audit history.
     */
    public function show(Referral $referral): View
    {
        $referral->load([
            'patient',
            'visit',
            'admission.currentBedAssignment.bed',
            'authorizedBy',
            'cancelledBy',
            'histories.changedBy',
        ]);

        return view('portal.referrals.show', [
            'referral' => $referral,
        ]);
    }

    /**
     * Mark patient dispatched in transit via ambulance.
     */
    public function dispatch(DispatchTransitRequest $request, Referral $referral): RedirectResponse
    {
        $this->referralService->dispatchInTransit($referral, $request->validated(), $request->user());

        return redirect()
            ->route('portal.referrals.show', $referral)
            ->with('success', "Patient marked in transit to {$referral->facility_name}.");
    }

    /**
     * Complete clinical handover at destination tertiary center.
     */
    public function complete(CompleteTransferRequest $request, Referral $referral): RedirectResponse
    {
        $this->referralService->completeTransfer($referral, $request->validated(), $request->user());

        return redirect()
            ->route('portal.referrals.show', $referral)
            ->with('success', "Clinical handover confirmed and admission completed at {$referral->facility_name}.");
    }

    /**
     * Cancel emergency referral.
     */
    public function cancel(CancelReferralRequest $request, Referral $referral): RedirectResponse
    {
        $this->referralService->cancelReferral($referral, $request->validated()['cancellation_reason'], $request->user());

        return redirect()
            ->route('portal.referrals.show', $referral)
            ->with('success', "Emergency referral {$referral->referral_number} has been cancelled.");
    }

    /**
     * Update emergency assessment and bedside interventions.
     */
    public function updateAssessment(UpdateAssessmentRequest $request, Referral $referral): RedirectResponse
    {
        $this->referralService->updateAssessment($referral, $request->validated(), $request->user());

        return redirect()
            ->route('portal.referrals.show', $referral)
            ->with('success', 'Emergency assessment findings updated successfully.');
    }

    /**
     * Printable formal Emergency Referral & Clinical Handover Slip.
     */
    public function printSlip(Referral $referral): View
    {
        $referral->load([
            'patient',
            'visit',
            'admission.currentBedAssignment.bed',
            'authorizedBy',
            'histories.changedBy',
        ]);

        return view('portal.referrals.print_slip', [
            'referral' => $referral,
        ]);
    }
}

