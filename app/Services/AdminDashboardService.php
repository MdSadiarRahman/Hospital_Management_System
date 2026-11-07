<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Bed;
use App\Models\Bill;
use App\Models\Department;
use App\Models\LabReport;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    /**
     * Compute comprehensive, real-time administrative metrics from the database.
     * All metrics reflect actual live database records (zero fabricated data).
     */
    public function getDashboardMetrics(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        // 1. Patient Metrics
        $totalPatients = Patient::count();
        $todayPatientsCount = Patient::whereDate('created_at', $today)->count();
        $todayVisitsPatientsCount = Visit::whereDate('visit_date', $today)->distinct('patient_id')->count('patient_id');
        $todayPatients = max($todayPatientsCount, $todayVisitsPatientsCount);

        // 2. Appointment Metrics
        $todayAppointments = Appointment::whereDate('appointment_date', $today)->count();
        $todayAppointmentsBreakdown = [
            'pending' => Appointment::whereDate('appointment_date', $today)->where('status', 'pending')->count(),
            'confirmed' => Appointment::whereDate('appointment_date', $today)->where('status', 'confirmed')->count(),
            'completed' => Appointment::whereDate('appointment_date', $today)->where('status', 'completed')->count(),
            'cancelled' => Appointment::whereDate('appointment_date', $today)->where('status', 'cancelled')->count(),
        ];

        // 3. Admission & Inpatient Metrics
        $activeAdmissions = Admission::where('status', 'admitted')->count();
        $todayAdmissions = Admission::whereDate('admission_date', $today)->count();

        // 4. Bed Occupancy & Capacity Metrics (Initial 10-bed profile)
        $totalBeds = Bed::count();
        $availableBeds = Bed::where('status', 'available')->count();
        $occupiedBeds = Bed::where('status', 'occupied')->count();
        $cleaningBeds = Bed::where('status', 'cleaning')->count();
        $maintenanceBeds = Bed::where('status', 'maintenance')->count();
        $reservedBeds = Bed::where('status', 'reserved')->count();

        $occupancyRate = $totalBeds > 0 ? round(($occupiedBeds / $totalBeds) * 100, 1) : 0;

        // 5. Financial & Revenue Metrics (DECIMAL accuracy, real payment transactions)
        $todayPayments = Payment::whereDate('payment_date', $today)
            ->where('status', 'completed')
            ->sum('amount');

        $totalOutstandingDue = Bill::where('status', '!=', 'cancelled')->sum('due_amount');
        $thisMonthCollections = Payment::where('payment_date', '>=', $thisMonth)
            ->where('status', 'completed')
            ->sum('amount');

        // 6. Laboratory Status Breakdown
        $labReportStatus = [
            'total' => LabReport::count(),
            'pending' => LabReport::where('review_status', 'pending')->count(),
            'completed' => LabReport::where('report_status', 'completed')->count(),
            'verified' => LabReport::where('report_status', 'verified')->count(),
            'today_orders' => DB::table('lab_orders')->whereDate('created_at', $today)->count(),
        ];

        // 7. Discharge Statistics
        $dischargedToday = Admission::where('status', 'discharged')
            ->whereDate('discharge_date', $today)
            ->count();

        $dischargedThisMonth = Admission::where('status', 'discharged')
            ->where('discharge_date', '>=', $thisMonth)
            ->count();

        $dualClearanceReady = Admission::where('status', 'admitted')
            ->where('clinical_clearance', true)
            ->where('financial_clearance', true)
            ->count();

        $pendingClinicalClearance = Admission::where('status', 'admitted')
            ->where('clinical_clearance', false)
            ->count();

        $pendingFinancialClearance = Admission::where('status', 'admitted')
            ->where('financial_clearance', false)
            ->count();

        // 8. Department-wise Patient Statistics
        $departmentStats = Department::where('is_clinical', true)
            ->withCount([
                'admissions as total_admissions',
                'admissions as active_admissions' => function ($q) {
                    $q->where('status', 'admitted');
                },
                'appointments as today_appointments' => function ($q) use ($today) {
                    $q->whereDate('appointment_date', $today);
                },
            ])
            ->get();

        // 9. Chart Datasets (Last 7 Days Trend)
        $trends7Days = $this->getLast7DaysTrend();

        // 10. Payment Method Distribution (This Month)
        $paymentMethodsDistribution = Payment::where('payment_date', '>=', $thisMonth)
            ->where('status', 'completed')
            ->select('payment_method', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get();

        // 11. Recent Administrative & Security Audit Trail
        $recentAuditLogs = AuditLog::with('user')
            ->latest('created_at')
            ->limit(8)
            ->get();

        return [
            'total_patients' => $totalPatients,
            'today_patients' => $todayPatients,
            'today_appointments' => $todayAppointments,
            'today_appointments_breakdown' => $todayAppointmentsBreakdown,
            'active_admissions' => $activeAdmissions,
            'today_admissions' => $todayAdmissions,
            'total_beds' => $totalBeds,
            'available_beds' => $availableBeds,
            'occupied_beds' => $occupiedBeds,
            'cleaning_beds' => $cleaningBeds,
            'maintenance_beds' => $maintenanceBeds,
            'reserved_beds' => $reservedBeds,
            'occupancy_rate' => $occupancyRate,
            'today_payments' => (float) $todayPayments,
            'this_month_collections' => (float) $thisMonthCollections,
            'outstanding_due' => (float) $totalOutstandingDue,
            'lab_report_status' => $labReportStatus,
            'discharged_today' => $dischargedToday,
            'discharged_this_month' => $dischargedThisMonth,
            'dual_clearance_ready' => $dualClearanceReady,
            'pending_clinical_clearance' => $pendingClinicalClearance,
            'pending_financial_clearance' => $pendingFinancialClearance,
            'department_stats' => $departmentStats,
            'trends_7_days' => $trends7Days,
            'payment_methods_distribution' => $paymentMethodsDistribution,
            'recent_audit_logs' => $recentAuditLogs,
        ];
    }

    /**
     * Compute 7-day trend for chart visualization (Visits, Admissions, Revenue).
     */
    protected function getLast7DaysTrend(): array
    {
        $days = [];
        $visitCounts = [];
        $admissionCounts = [];
        $collections = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateStr = $date->format('Y-m-d');
            $label = $date->format('D, d M');

            $days[] = $label;
            $visitCounts[] = Visit::whereDate('visit_date', $dateStr)->count();
            $admissionCounts[] = Admission::whereDate('admission_date', $dateStr)->count();
            $collections[] = (float) Payment::whereDate('payment_date', $dateStr)
                ->where('status', 'completed')
                ->sum('amount');
        }

        return [
            'labels' => $days,
            'visits' => $visitCounts,
            'admissions' => $admissionCounts,
            'collections' => $collections,
        ];
    }
}
