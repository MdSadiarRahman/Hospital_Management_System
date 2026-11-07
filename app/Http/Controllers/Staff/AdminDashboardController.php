<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Services\AdminDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __construct(
        protected AdminDashboardService $dashboardService
    ) {}

    /**
     * Display the real-time Administrator Control Center Dashboard.
     */
    public function index(Request $request): View
    {
        $metrics = $this->dashboardService->getDashboardMetrics();

        return view('portal.admin.dashboard', compact('metrics'));
    }
}

