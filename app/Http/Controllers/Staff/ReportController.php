<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    /**
     * Display the Reports Center Directory.
     */
    public function index(Request $request): View
    {
        $allReports = ReportService::REPORT_TYPES;
        $user = $request->user();

        // Separate reports into accessible categories
        $reports = [];
        foreach ($allReports as $key => $meta) {
            $meta['key'] = $key;
            $meta['accessible'] = $this->reportService->canAccessReport($user, $key);
            $reports[$key] = $meta;
        }

        return view('portal.reports.index', compact('reports'));
    }

    /**
     * Display a specific report with filter controls and paginated data.
     */
    public function show(Request $request, string $type): View
    {
        $filters = $request->only([
            'preset',
            'start_date',
            'end_date',
            'department_id',
            'status',
            'gender',
            'is_verified',
            'visit_type',
            'triage_level',
            'doctor_id',
            'urgency',
            'payment_method',
            'min_due',
            'search',
        ]);

        $reportData = $this->reportService->generateReport($type, $filters, $request->user(), paginate: true);
        $departments = Department::where('is_clinical', true)->get();
        $doctors = User::whereHas('roles', fn($q) => $q->where('name', 'doctor'))->get();

        return view('portal.reports.show', compact('reportData', 'departments', 'doctors'));
    }

    /**
     * Display the dedicated official printable version of the report.
     */
    public function print(Request $request, string $type): View
    {
        $filters = $request->only([
            'preset',
            'start_date',
            'end_date',
            'department_id',
            'status',
            'gender',
            'is_verified',
            'visit_type',
            'triage_level',
            'doctor_id',
            'urgency',
            'payment_method',
            'min_due',
            'search',
        ]);

        $reportData = $this->reportService->generateReport($type, $filters, $request->user(), paginate: false);

        return view('portal.reports.print', compact('reportData'));
    }
}
