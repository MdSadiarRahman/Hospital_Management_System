<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Discharge\ApproveDischargeRequest;
use App\Http\Requests\Discharge\ClinicalClearanceRequest;
use App\Http\Requests\Discharge\FinancialClearanceRequest;
use App\Http\Requests\Discharge\NursingClearanceRequest;
use App\Models\Admission;
use App\Services\DischargeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DischargeController extends Controller
{
    public function __construct(
        protected DischargeService $dischargeService
    ) {}

    /**
     * Display the Discharge Management Workbench.
     */
    public function index(Request $request): View
    {
        $filters = [
            'status' => $request->query('status'),
            'clearance_filter' => $request->query('clearance_filter'),
            'search' => $request->query('search'),
        ];

        $admissions = $this->dischargeService->getDischargeWorkbench($filters);

        $stats = [
            'active_inpatients' => Admission::where('status', 'admitted')->count(),
            'ready_for_discharge' => Admission::where('status', 'admitted')
                ->where('clinical_clearance', true)
                ->where('financial_clearance', true)
                ->count(),
            'pending_clinical' => Admission::where('status', 'admitted')->where('clinical_clearance', false)->count(),
            'pending_financial' => Admission::where('status', 'admitted')->where('financial_clearance', false)->count(),
            'discharged_today' => Admission::where('status', 'discharged')->whereDate('discharge_date', today())->count(),
        ];

        return view('portal.discharges.index', compact('admissions', 'stats', 'filters'));
    }

    /**
     * Show detailed discharge clearances dossier for an admission.
     */
    public function show(Admission $admission, Request $request): View
    {
        $admission->load([
            'patient',
            'department',
            'attendingDoctor',
            'currentBedAssignment.bed',
            'dischargeSummary.authorizedBy',
            'bill.payments',
            'bill.refunds',
            'clinicalClearanceBy',
            'nursingClearanceBy',
            'financialClearanceBy',
            'financialExceptionBy',
            'dischargedBy',
            'dischargeHistories.performedBy',
        ]);

        $canClinical = $this->dischargeService->canAuthorizeClinicalClearance($request->user());
        $canNursing = $this->dischargeService->canAuthorizeNursingClearance($request->user());
        $canFinancial = $this->dischargeService->canAuthorizeFinancialClearance($request->user());
        $canDuesException = $this->dischargeService->canAuthorizeDuesException($request->user());
        $canApprove = $this->dischargeService->canApproveDischarge($request->user());

        return view('portal.discharges.show', compact(
            'admission',
            'canClinical',
            'canNursing',
            'canFinancial',
            'canDuesException',
            'canApprove'
        ));
    }

    /**
     * Grant doctor clinical clearance and save discharge summary.
     */
    public function clinicalClearance(ClinicalClearanceRequest $request, Admission $admission): RedirectResponse
    {
        $this->dischargeService->grantClinicalClearance($admission, $request->validated(), $request->user());

        return redirect()->route('portal.discharges.show', $admission)
            ->with('success', "Doctor Clinical Clearance granted and Discharge Summary saved for {$admission->patient->name}.");
    }

    /**
     * Grant nursing exit clearance.
     */
    public function nursingClearance(NursingClearanceRequest $request, Admission $admission): RedirectResponse
    {
        $this->dischargeService->grantNursingClearance($admission, $request->validated(), $request->user());

        return redirect()->route('portal.discharges.show', $admission)
            ->with('success', "Nursing discharge clearance recorded successfully.");
    }

    /**
     * Grant cashier financial discharge clearance.
     */
    public function financialClearance(FinancialClearanceRequest $request, Admission $admission): RedirectResponse
    {
        $this->dischargeService->grantFinancialClearance($admission, $request->validated(), $request->user());

        $msg = $request->boolean('allow_dues_exception')
            ? "Financial discharge clearance granted under authorized exception policy."
            : "Financial discharge clearance granted. Inpatient charges fully settled.";

        return redirect()->route('portal.discharges.show', $admission)->with('success', $msg);
    }

    /**
     * Execute final discharge approval and bed release.
     */
    public function approve(ApproveDischargeRequest $request, Admission $admission): RedirectResponse
    {
        $this->dischargeService->approveFinalDischarge($admission, $request->validated(), $request->user());

        return redirect()->route('portal.discharges.show', $admission)
            ->with('success', "Patient #{$admission->patient->name} successfully discharged. Bed has been vacated and placed into cleaning status.");
    }

    /**
     * Revoke a clearance before final discharge.
     */
    public function revoke(Request $request, Admission $admission): RedirectResponse
    {
        $validated = $request->validate([
            'clearance_type' => ['required', 'string', 'in:clinical,financial,nursing'],
            'reason' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        $this->dischargeService->revokeClearance($admission, $validated['clearance_type'], $validated['reason'], $request->user());

        return redirect()->route('portal.discharges.show', $admission)
            ->with('success', ucfirst($validated['clearance_type']) . " clearance revoked: {$validated['reason']}");
    }

    /**
     * Display official printable Discharge Certificate & Clinical Summary.
     */
    public function printSummary(Admission $admission): View
    {
        $admission->load([
            'patient',
            'department',
            'attendingDoctor',
            'currentBedAssignment.bed',
            'dischargeSummary.authorizedBy',
            'bill',
            'clinicalClearanceBy',
            'financialClearanceBy',
            'dischargedBy',
        ]);

        return view('portal.discharges.print-summary', compact('admission'));
    }
}

