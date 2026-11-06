<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lab\ReviewLabReportRequest;
use App\Http\Requests\Lab\StoreInternalResultRequest;
use App\Http\Requests\Lab\UploadExternalReportRequest;
use App\Models\LabOrderItem;
use App\Models\LabPartner;
use App\Models\LabReport;
use App\Services\LabReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LabReportController extends Controller
{
    public function __construct(
        protected LabReportService $labReportService
    ) {}

    /**
     * Display the laboratory reports workbench queue.
     */
    public function index(Request $request): View|JsonResponse
    {
        $user = $request->user();
        if (!$user->hasPermission('lab.view') && !$user->hasRole(['super_admin', 'administrator', 'lab_staff', 'doctor', 'nurse'])) {
            abort(403, 'Unauthorized access to laboratory reports workbench.');
        }

        $filters = $request->only(['q', 'source', 'review_status', 'is_critical', 'partner_id']);
        $reports = $this->labReportService->getReportsList($filters, 15);
        $statistics = $this->labReportService->getReportStatistics();
        $partners = LabPartner::where('is_active', true)->orderBy('name')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $reports,
                'stats' => $statistics,
            ]);
        }

        return view('portal.lab.reports.index', [
            'reports' => $reports,
            'statistics' => $statistics,
            'partners' => $partners,
            'filters' => $filters,
        ]);
    }

    /**
     * Show internal result entry form for a specific test order item.
     */
    public function createInternal(Request $request, LabOrderItem $labOrderItem): View
    {
        $user = $request->user();
        if (!$user->hasPermission('lab.create') && !$user->hasRole(['super_admin', 'administrator', 'doctor', 'lab_staff'])) {
            abort(403, 'Unauthorized to enter diagnostic results.');
        }

        $labOrderItem->loadMissing('labOrder.patient', 'labOrder.doctor', 'labTest', 'sample', 'report');

        return view('portal.lab.reports.create_internal', [
            'item' => $labOrderItem,
            'existingReport' => $labOrderItem->report,
        ]);
    }

    /**
     * Store internal laboratory results with structured parameters.
     */
    public function storeInternal(StoreInternalResultRequest $request, LabOrderItem $labOrderItem): RedirectResponse|JsonResponse
    {
        try {
            $report = $this->labReportService->recordInternalResult(
                $labOrderItem,
                $request->validated(),
                $request->user()
            );
        } catch (\InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }

        $message = "Internal results for '{$labOrderItem->test_name}' recorded successfully. Status: Report Received (Pending Doctor Review).";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $report,
            ], 201);
        }

        return redirect()->route('portal.lab-reports.show', $report->id)->with('success', $message);
    }

    /**
     * Show external report upload form for outsourced tests.
     */
    public function createExternal(Request $request, LabOrderItem $labOrderItem): View
    {
        $user = $request->user();
        if (!$user->hasPermission('lab.create') && !$user->hasRole(['super_admin', 'administrator', 'doctor', 'lab_staff'])) {
            abort(403, 'Unauthorized to upload diagnostic reports.');
        }

        $labOrderItem->loadMissing('labOrder.patient', 'labOrder.doctor', 'labTest', 'labOrder.labPartner', 'report');
        $partners = LabPartner::where('is_active', true)->orderBy('name')->get();

        return view('portal.lab.reports.create_external', [
            'item' => $labOrderItem,
            'partners' => $partners,
            'existingReport' => $labOrderItem->report,
        ]);
    }

    /**
     * Store uploaded external laboratory report file.
     */
    public function storeExternal(UploadExternalReportRequest $request, LabOrderItem $labOrderItem): RedirectResponse|JsonResponse
    {
        try {
            $report = $this->labReportService->uploadExternalReport(
                $labOrderItem,
                $request->validated(),
                $request->file('file'),
                $request->user()
            );
        } catch (\InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }

        $message = "External diagnostic report file uploaded successfully. Status: Report Received (Pending Doctor Review).";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $report,
            ], 201);
        }

        return redirect()->route('portal.lab-reports.show', $report->id)->with('success', $message);
    }

    /**
     * Display report dossier with parameters, uploaded documents, and history timeline.
     */
    public function show(Request $request, LabReport $labReport): View|JsonResponse
    {
        $this->labReportService->authorizeReportAccess($labReport, $request->user());

        $labReport->loadMissing([
            'orderItem.labOrder.patient',
            'orderItem.labOrder.doctor',
            'orderItem.labTest',
            'labPartner',
            'uploadedBy',
            'reviewedBy',
            'histories.changedBy',
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $labReport,
            ]);
        }

        return view('portal.lab.reports.show', [
            'report' => $labReport,
        ]);
    }

    /**
     * Clinician reviews and validates diagnostic laboratory report.
     */
    public function review(ReviewLabReportRequest $request, LabReport $labReport): RedirectResponse|JsonResponse
    {
        try {
            $updatedReport = $this->labReportService->reviewReport(
                $labReport,
                $request->validated(),
                $request->user()
            );
        } catch (\InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }

        $message = "Diagnostic report for '{$updatedReport->test_name}' has been verified and marked as Medically Reviewed by Dr. {$request->user()->name}.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $updatedReport,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Stream non-public report document (PDF/Image) inline for authorized users.
     */
    public function stream(Request $request, LabReport $labReport): BinaryFileResponse|StreamedResponse
    {
        return $this->labReportService->getReportStream($labReport, $request->user(), false);
    }

    /**
     * Download non-public report file for authorized users.
     */
    public function download(Request $request, LabReport $labReport): BinaryFileResponse|StreamedResponse
    {
        return $this->labReportService->getReportStream($labReport, $request->user(), true);
    }

    /**
     * Official printable report slip for patient and physical records.
     */
    public function printReport(Request $request, LabReport $labReport): View
    {
        $this->labReportService->authorizeReportAccess($labReport, $request->user());

        $labReport->loadMissing([
            'orderItem.labOrder.patient',
            'orderItem.labOrder.doctor',
            'orderItem.labTest',
            'labPartner',
            'uploadedBy',
            'reviewedBy',
        ]);

        return view('portal.lab.reports.print_report', [
            'report' => $labReport,
        ]);
    }
}

