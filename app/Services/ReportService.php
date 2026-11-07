<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Bill;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\LabOrder;
use App\Models\LabReport;
use App\Models\Operation;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ReportService
{
    /**
     * All supported report types in Meghna Clinic & Nursing Home.
     */
    public const REPORT_TYPES = [
        'patient_registration' => [
            'name' => 'Patient Registration Report',
            'category' => 'clinical',
            'description' => 'New patient intakes, demographic distribution, and verification status.',
            'icon' => 'person-lines-fill',
        ],
        'visit_history' => [
            'name' => 'Visit History Report',
            'category' => 'clinical',
            'description' => 'Outpatient visits, emergency intakes, triage severity, and dispositions.',
            'icon' => 'box-arrow-in-right',
        ],
        'admission_discharge' => [
            'name' => 'Admission & Discharge Report',
            'category' => 'clinical',
            'description' => 'Inpatient bed admissions, lengths of stay, dual clearances, and discharges.',
            'icon' => 'hospital',
        ],
        'bed_occupancy' => [
            'name' => 'Bed Occupancy & Tariff Report',
            'category' => 'operations',
            'description' => 'Capacity utilization across 10 inpatient beds, turnaround, and disinfection.',
            'icon' => 'door-open',
        ],
        'department_performance' => [
            'name' => 'Department Performance Report',
            'category' => 'operations',
            'description' => 'Clinical department workload, consultations, admissions, and procedures.',
            'icon' => 'diagram-3',
        ],
        'doctor_consultation' => [
            'name' => 'Doctor Consultation Report',
            'category' => 'clinical',
            'description' => 'Medical officer consultations, patient volume, and diagnostic orders.',
            'icon' => 'clipboard2-pulse',
        ],
        'laboratory' => [
            'name' => 'Laboratory & Diagnostics Report',
            'category' => 'clinical',
            'description' => 'Diagnostic test orders, sample turn-around, verified pathology reports.',
            'icon' => 'file-earmark-medical',
        ],
        'operation' => [
            'name' => 'Operation Theatre (OT) Report',
            'category' => 'clinical',
            'description' => 'Surgical procedures performed, emergency vs elective OT cases.',
            'icon' => 'scissors',
        ],
        'revenue' => [
            'name' => 'Revenue & Billing Report',
            'category' => 'financial',
            'restricted' => true,
            'description' => 'Gross billings, discounts, net receivable revenue, and settlements.',
            'icon' => 'graph-up-arrow',
        ],
        'payment' => [
            'name' => 'Payment & Collections Report',
            'category' => 'financial',
            'restricted' => true,
            'description' => 'Cashier transaction logs, Cash vs MFS (bKash/Nagad) receipts.',
            'icon' => 'cash-stack',
        ],
        'due' => [
            'name' => 'Outstanding Due Aging Report',
            'category' => 'financial',
            'restricted' => true,
            'description' => 'Unpaid invoices, patient dues, and post-discharge receivables.',
            'icon' => 'exclamation-triangle',
        ],
        'refund' => [
            'name' => 'Refund Vouchers & Corrections Report',
            'category' => 'financial',
            'restricted' => true,
            'description' => 'Approved patient refunds, reversal logs, and financial adjustments.',
            'icon' => 'arrow-counterclockwise',
        ],
    ];

    /**
     * Check if a report type is classified as a sensitive financial report.
     */
    public function isFinancialReport(string $type): bool
    {
        return in_array($type, ['revenue', 'payment', 'due', 'refund']);
    }

    /**
     * Validate whether the actor has authorization to access the specific report.
     */
    public function canAccessReport(User $user, string $type): bool
    {
        if ($this->isFinancialReport($type)) {
            return $user->hasRole(['administrator', 'super_admin', 'billing_staff']) ||
                   $user->hasPermission('financial.reports');
        }

        return $user->hasRole(['administrator', 'super_admin', 'doctor', 'nurse', 'receptionist', 'billing_staff']);
    }

    /**
     * Resolve date filters from request inputs.
     */
    public function parseDateRange(array $filters): array
    {
        $preset = $filters['preset'] ?? null;

        if ($preset) {
            return match ($preset) {
                'today' => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay(), 'Today'],
                'yesterday' => [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay(), 'Yesterday'],
                'last_7_days' => [Carbon::today()->subDays(6)->startOfDay(), Carbon::today()->endOfDay(), 'Last 7 Days'],
                'this_month' => [Carbon::now()->startOfMonth()->startOfDay(), Carbon::now()->endOfMonth()->endOfDay(), 'This Month'],
                'last_month' => [Carbon::now()->subMonth()->startOfMonth()->startOfDay(), Carbon::now()->subMonth()->endOfMonth()->endOfDay(), 'Last Month'],
                'this_year' => [Carbon::now()->startOfYear()->startOfDay(), Carbon::now()->endOfYear()->endOfDay(), 'This Year'],
                default => [Carbon::today()->subDays(29)->startOfDay(), Carbon::today()->endOfDay(), 'Last 30 Days'],
            };
        }

        $startDate = !empty($filters['start_date'])
            ? Carbon::parse($filters['start_date'])->startOfDay()
            : Carbon::today()->subDays(29)->startOfDay();

        $endDate = !empty($filters['end_date'])
            ? Carbon::parse($filters['end_date'])->endOfDay()
            : Carbon::today()->endOfDay();

        $label = $startDate->format('d M Y') . ' — ' . $endDate->format('d M Y');

        return [$startDate, $endDate, $label];
    }

    /**
     * Generate report data with pagination, summary KPIs, and database records.
     */
    public function generateReport(string $type, array $filters, User $actor, bool $paginate = true): array
    {
        if (!isset(self::REPORT_TYPES[$type])) {
            throw new InvalidArgumentException("Report type '{$type}' does not exist.");
        }

        if (!$this->canAccessReport($actor, $type)) {
            throw new AuthorizationException("You are not authorized to view the {$type} report.");
        }

        [$startDate, $endDate, $rangeLabel] = $this->parseDateRange($filters);

        $method = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $type))) . 'Data';

        if (method_exists($this, $method)) {
            $data = $this->$method($startDate, $endDate, $filters, $paginate);
        } else {
            throw new InvalidArgumentException("Handler for report type '{$type}' is not implemented.");
        }

        return array_merge($data, [
            'type' => $type,
            'meta' => self::REPORT_TYPES[$type],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'range_label' => $rangeLabel,
            'filters' => $filters,
            'generated_at' => Carbon::now(),
            'generated_by' => $actor->name,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Specific Report Data Generators
    |--------------------------------------------------------------------------
    */

    protected function getPatientRegistrationData(Carbon $start, Carbon $end, array $filters, bool $paginate): array
    {
        $query = Patient::whereBetween('created_at', [$start, $end]);

        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }
        if (isset($filters['is_verified']) && $filters['is_verified'] !== '') {
            $query->where('is_verified', (bool) $filters['is_verified']);
        }
        if (!empty($filters['search'])) {
            $s = trim($filters['search']);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('patient_code', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        $summary = [
            'total_registered' => (clone $query)->count(),
            'verified' => (clone $query)->where('is_verified', true)->count(),
            'male' => (clone $query)->where('gender', 'male')->count(),
            'female' => (clone $query)->where('gender', 'female')->count(),
        ];

        $records = $paginate
            ? $query->latest('created_at')->paginate(20)->withQueryString()
            : $query->latest('created_at')->get();

        return ['summary' => $summary, 'records' => $records];
    }

    protected function getVisitHistoryData(Carbon $start, Carbon $end, array $filters, bool $paginate): array
    {
        $query = Visit::with(['patient', 'appointment.department'])
            ->whereBetween('visit_date', [$start, $end]);

        if (!empty($filters['visit_type'])) {
            $query->where('visit_type', $filters['visit_type']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['triage_level'])) {
            $query->where('triage_level', $filters['triage_level']);
        }

        $summary = [
            'total_visits' => (clone $query)->count(),
            'regular' => (clone $query)->where('visit_type', 'regular')->count(),
            'emergency' => (clone $query)->where('visit_type', 'emergency')->count(),
            'admitted_count' => (clone $query)->where('status', 'admitted')->count(),
        ];

        $records = $paginate
            ? $query->latest('visit_date')->paginate(20)->withQueryString()
            : $query->latest('visit_date')->get();

        return ['summary' => $summary, 'records' => $records];
    }

    protected function getAdmissionDischargeData(Carbon $start, Carbon $end, array $filters, bool $paginate): array
    {
        $query = Admission::with(['patient', 'department', 'attendingDoctor', 'currentBedAssignment.bed'])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('admission_date', [$start, $end])
                    ->orWhereBetween('discharge_date', [$start, $end]);
            });

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $discharges = (clone $query)->where('status', 'discharged')->get();
        $totalStayDays = 0;
        foreach ($discharges as $d) {
            if ($d->admission_date && $d->discharge_date) {
                $totalStayDays += $d->admission_date->diffInDays($d->discharge_date) ?: 1;
            }
        }
        $avgStay = count($discharges) > 0 ? round($totalStayDays / count($discharges), 1) : 0;

        $summary = [
            'total_admissions' => (clone $query)->whereBetween('admission_date', [$start, $end])->count(),
            'active_inpatients' => (clone $query)->where('status', 'admitted')->count(),
            'total_discharges' => (clone $query)->where('status', 'discharged')->count(),
            'avg_stay_days' => $avgStay,
        ];

        $records = $paginate
            ? $query->latest('admission_date')->paginate(20)->withQueryString()
            : $query->latest('admission_date')->get();

        return ['summary' => $summary, 'records' => $records];
    }

    protected function getBedOccupancyData(Carbon $start, Carbon $end, array $filters, bool $paginate): array
    {
        $query = Bed::with(['department', 'currentAssignment.admission.patient']);

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        $allBeds = $query->get();
        $total = $allBeds->count();
        $occupied = $allBeds->where('status', 'occupied')->count();
        $available = $allBeds->where('status', 'available')->count();
        $cleaning = $allBeds->where('status', 'cleaning')->count();
        $maintenance = $allBeds->where('status', 'maintenance')->count();

        $occupancyRate = $total > 0 ? round(($occupied / $total) * 100, 1) : 0;

        $summary = [
            'total_beds' => $total,
            'occupied' => $occupied,
            'available' => $available,
            'cleaning' => $cleaning,
            'maintenance' => $maintenance,
            'occupancy_rate' => $occupancyRate,
        ];

        return ['summary' => $summary, 'records' => $allBeds];
    }

    protected function getDepartmentPerformanceData(Carbon $start, Carbon $end, array $filters, bool $paginate): array
    {
        $departments = Department::where('is_clinical', true)->get();

        $rows = [];
        $totalVisits = 0;
        $totalAdmissions = 0;
        $totalAppointments = 0;
        $totalOperations = 0;

        foreach ($departments as $dept) {
            $visitsCount = Visit::whereHas('appointment', fn($q) => $q->where('department_id', $dept->id))
                ->whereBetween('visit_date', [$start, $end])
                ->count();

            $admissionsCount = Admission::where('department_id', $dept->id)
                ->whereBetween('admission_date', [$start, $end])
                ->count();

            $appointmentsCount = Appointment::where('department_id', $dept->id)
                ->whereBetween('appointment_date', [$start, $end])
                ->count();

            $operationsCount = Operation::where('department_id', $dept->id)
                ->whereBetween('scheduled_at', [$start, $end])
                ->count();

            $totalVisits += $visitsCount;
            $totalAdmissions += $admissionsCount;
            $totalAppointments += $appointmentsCount;
            $totalOperations += $operationsCount;

            $rows[] = (object) [
                'department_id' => $dept->id,
                'name' => $dept->name,
                'code' => $dept->code,
                'visits_count' => $visitsCount,
                'admissions_count' => $admissionsCount,
                'appointments_count' => $appointmentsCount,
                'operations_count' => $operationsCount,
            ];
        }

        $summary = [
            'total_departments' => count($departments),
            'total_visits' => $totalVisits,
            'total_admissions' => $totalAdmissions,
            'total_appointments' => $totalAppointments,
            'total_operations' => $totalOperations,
        ];

        return ['summary' => $summary, 'records' => collect($rows)];
    }

    protected function getDoctorConsultationData(Carbon $start, Carbon $end, array $filters, bool $paginate): array
    {
        $query = Consultation::with(['doctor', 'visit.patient'])
            ->whereBetween('created_at', [$start, $end]);

        if (!empty($filters['doctor_id'])) {
            $query->where('doctor_id', $filters['doctor_id']);
        }

        $summary = [
            'total_consultations' => (clone $query)->count(),
            'unique_patients' => (clone $query)->distinct('visit_id')->count('visit_id'),
            'finalized' => (clone $query)->whereNotNull('finalized_at')->count(),
        ];

        $records = $paginate
            ? $query->latest('created_at')->paginate(20)->withQueryString()
            : $query->latest('created_at')->get();

        return ['summary' => $summary, 'records' => $records];
    }

    protected function getLaboratoryData(Carbon $start, Carbon $end, array $filters, bool $paginate): array
    {
        $query = LabOrder::with(['patient', 'items.labTest'])
            ->whereBetween('created_at', [$start, $end]);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $summary = [
            'total_orders' => (clone $query)->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'pending' => (clone $query)->whereIn('status', ['ordered', 'sample_collected', 'in_progress'])->count(),
            'total_tests_billed' => (float) (clone $query)->sum('total_charge'),
        ];

        $records = $paginate
            ? $query->latest('created_at')->paginate(20)->withQueryString()
            : $query->latest('created_at')->get();

        return ['summary' => $summary, 'records' => $records];
    }

    protected function getOperationData(Carbon $start, Carbon $end, array $filters, bool $paginate): array
    {
        $query = Operation::with(['patient', 'department', 'primarySurgeon'])
            ->whereBetween('scheduled_at', [$start, $end]);

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['urgency'])) {
            $query->where('urgency', $filters['urgency']);
        }

        $summary = [
            'total_operations' => (clone $query)->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'emergency' => (clone $query)->where('urgency', 'emergency')->count(),
            'elective' => (clone $query)->where('urgency', 'elective')->count(),
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
        ];

        $records = $paginate
            ? $query->latest('scheduled_at')->paginate(20)->withQueryString()
            : $query->latest('scheduled_at')->get();

        return ['summary' => $summary, 'records' => $records];
    }

    /*
    |--------------------------------------------------------------------------
    | Financial Reports (Restricted Access)
    |--------------------------------------------------------------------------
    */

    protected function getRevenueData(Carbon $start, Carbon $end, array $filters, bool $paginate): array
    {
        $query = Bill::with(['patient', 'admission', 'items'])
            ->whereBetween('bill_date', [$start, $end])
            ->where('status', '!=', 'cancelled');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $grossBilled = (float) (clone $query)->sum('subtotal_amount');
        $discounts = (float) (clone $query)->sum('discount_amount');
        $netBilled = (float) (clone $query)->sum('total_amount');
        $paid = (float) (clone $query)->sum('paid_amount');
        $due = (float) (clone $query)->sum('due_amount');

        $summary = [
            'total_invoices' => (clone $query)->count(),
            'gross_billed' => $grossBilled,
            'discounts' => $discounts,
            'net_billed' => $netBilled,
            'paid_amount' => $paid,
            'due_amount' => $due,
        ];

        $records = $paginate
            ? $query->latest('bill_date')->paginate(20)->withQueryString()
            : $query->latest('bill_date')->get();

        return ['summary' => $summary, 'records' => $records];
    }

    protected function getPaymentData(Carbon $start, Carbon $end, array $filters, bool $paginate): array
    {
        $query = Payment::with(['bill.patient', 'receivedBy'])
            ->whereBetween('payment_date', [$start, $end])
            ->where('status', '!=', 'failed');

        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        $totalCollected = (float) (clone $query)->sum('amount');
        $totalRefunded = (float) (clone $query)->sum('refunded_amount');
        $netCollected = $totalCollected - $totalRefunded;

        $methodsBreakdown = (clone $query)
            ->select('payment_method', DB::raw('SUM(amount) as sum_amount'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->pluck('sum_amount', 'payment_method')
            ->toArray();

        $summary = [
            'total_transactions' => (clone $query)->count(),
            'gross_collected' => $totalCollected,
            'total_refunded' => $totalRefunded,
            'net_collected' => $netCollected,
            'methods_breakdown' => $methodsBreakdown,
        ];

        $records = $paginate
            ? $query->latest('payment_date')->paginate(20)->withQueryString()
            : $query->latest('payment_date')->get();

        return ['summary' => $summary, 'records' => $records];
    }

    protected function getDueData(Carbon $start, Carbon $end, array $filters, bool $paginate): array
    {
        $query = Bill::with(['patient', 'admission'])
            ->where('due_amount', '>', 0)
            ->where('status', '!=', 'cancelled');

        if (!empty($filters['min_due'])) {
            $query->where('due_amount', '>=', (float) $filters['min_due']);
        }
        if (!empty($filters['search'])) {
            $s = trim($filters['search']);
            $query->where(function ($q) use ($s) {
                $q->where('invoice_number', 'like', "%{$s}%")
                    ->orWhereHas('patient', function ($pq) use ($s) {
                        $pq->where('name', 'like', "%{$s}%")
                            ->orWhere('patient_code', 'like', "%{$s}%")
                            ->orWhere('phone', 'like', "%{$s}%");
                    });
            });
        }

        $totalDue = (float) (clone $query)->sum('due_amount');
        $dueBillsCount = (clone $query)->count();

        $summary = [
            'due_bills_count' => $dueBillsCount,
            'total_due_amount' => $totalDue,
            'average_due' => $dueBillsCount > 0 ? round($totalDue / $dueBillsCount, 2) : 0,
        ];

        $records = $paginate
            ? $query->orderByDesc('due_amount')->paginate(20)->withQueryString()
            : $query->orderByDesc('due_amount')->get();

        return ['summary' => $summary, 'records' => $records];
    }

    protected function getRefundData(Carbon $start, Carbon $end, array $filters, bool $paginate): array
    {
        $query = Refund::with(['bill.patient', 'payment', 'approvedBy'])
            ->whereBetween('refunded_at', [$start, $end]);

        if (!empty($filters['refund_method'])) {
            $query->where('refund_method', $filters['refund_method']);
        }

        $totalRefunded = (float) (clone $query)->sum('amount');
        $totalVouchers = (clone $query)->count();

        $summary = [
            'total_refund_vouchers' => $totalVouchers,
            'total_refunded_amount' => $totalRefunded,
        ];

        $records = $paginate
            ? $query->latest('refunded_at')->paginate(20)->withQueryString()
            : $query->latest('refunded_at')->get();

        return ['summary' => $summary, 'records' => $records];
    }
}
