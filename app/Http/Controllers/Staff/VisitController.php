<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Visit\StoreVisitRequest;
use App\Http\Requests\Visit\UpdateVisitRequest;
use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use App\Services\VisitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class VisitController extends Controller
{
    public function __construct(
        protected VisitService $visitService
    ) {}

    /**
     * Display clinic-wide visit directory with search, filtering, and KPIs.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Visit::class);

        $search = $request->input('search');
        $filters = $request->only(['visit_type', 'triage_level', 'status', 'date', 'date_from', 'date_to']);

        $query = Visit::with(['patient', 'appointment', 'consultation.doctor', 'admissions.currentBedAssignment.bed'])
            ->latest('visit_date');

        if ($search) {
            $query->search($search);
        }

        if (!empty($filters)) {
            $query->filter($filters);
        }

        $visits = $query->paginate(15)->withQueryString();

        // Bento metrics
        $today = now()->toDateString();
        $metrics = [
            'total_today' => Visit::whereDate('visit_date', $today)->count(),
            'waiting' => Visit::whereDate('visit_date', $today)->where('status', 'waiting')->count(),
            'in_consultation' => Visit::whereDate('visit_date', $today)->where('status', 'in_consultation')->count(),
            'emergency_today' => Visit::whereDate('visit_date', $today)->where('visit_type', 'emergency')->count(),
            'admitted_today' => Visit::whereDate('visit_date', $today)->where('status', 'admitted')->count(),
        ];

        return view('portal.visits.index', compact('visits', 'metrics', 'search', 'filters'));
    }

    /**
     * Show the patient visit check-in form.
     */
    public function create(Request $request): View
    {
        Gate::authorize('create', Visit::class);

        $selectedPatient = null;
        if ($request->filled('patient_id')) {
            $selectedPatient = Patient::with('primaryContact')->find($request->input('patient_id'));
        }

        $recentPatients = Patient::latest('created_at')->take(10)->get();
        $doctors = User::whereHas('roles', fn($q) => $q->where('name', 'doctor'))->get();
        $suggestedVisitNumber = $this->visitService->generateVisitNumber();

        return view('portal.visits.create', compact('selectedPatient', 'recentPatients', 'doctors', 'suggestedVisitNumber'));
    }

    /**
     * Store a newly created visit in storage.
     */
    public function store(StoreVisitRequest $request): RedirectResponse
    {
        $visit = $this->visitService->createVisit($request->validated(), $request->user());

        return redirect()->route('portal.visits.show', $visit)
            ->with('success', "Patient visit {$visit->visit_number} checked-in successfully.");
    }

    /**
     * Display detailed visit dossier, clearly distinguishing patient master data from visit records.
     */
    public function show(Visit $visit): View
    {
        Gate::authorize('view', $visit);

        $visit->load([
            'patient.primaryContact',
            'appointment.doctor',
            'consultation.doctor',
            'consultation.diagnoses',
            'consultation.prescription.items',
            'consultation.labOrders.items.report.reviewedBy',
            'consultation.labOrders.labPartner',
            'admissions.currentBedAssignment.bed.department',
            'admissions.attendingDoctor',
            'admissions.dischargeSummary.authorizedBy',
            'bills.items',
            'bills.payments',
            'referral',
            'auditLogs.user',
        ]);

        return view('portal.visits.show', compact('visit'));
    }

    /**
     * Show the form for editing visit status or triage severity.
     */
    public function edit(Visit $visit): View
    {
        Gate::authorize('update', $visit);

        return view('portal.visits.edit', compact('visit'));
    }

    /**
     * Update visit record, enforcing patient immutability.
     */
    public function update(UpdateVisitRequest $request, Visit $visit): RedirectResponse
    {
        $this->visitService->updateVisit($visit, $request->validated(), $request->user());

        return redirect()->route('portal.visits.show', $visit)
            ->with('success', "Visit {$visit->visit_number} updated successfully.");
    }

    /**
     * Print patient visit queue token / check-in slip.
     */
    public function printToken(Visit $visit): View
    {
        Gate::authorize('print', $visit);

        $visit->load(['patient', 'appointment.department', 'consultation.doctor']);

        return view('portal.visits.print-token', compact('visit'));
    }

    /**
     * Cancel a visit.
     */
    public function destroy(Request $request, Visit $visit): RedirectResponse
    {
        Gate::authorize('delete', $visit);

        $visit->update(['status' => 'completed']); // Or mark status

        AuditLog::create([
            'user_id' => $request->user()->id,
            'event' => 'visit_cancelled',
            'auditable_type' => Visit::class,
            'auditable_id' => $visit->id,
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'user_agent' => $request->userAgent() ?? 'Hospital-System/1.0',
        ]);

        return redirect()->route('portal.visits.index')
            ->with('success', "Visit {$visit->visit_number} was closed / cancelled.");
    }
}
