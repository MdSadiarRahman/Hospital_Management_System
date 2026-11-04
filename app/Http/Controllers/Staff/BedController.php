<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bed\CompleteCleaningRequest;
use App\Http\Requests\Bed\CompleteMaintenanceRequest;
use App\Http\Requests\Bed\StoreBedRequest;
use App\Http\Requests\Bed\TransferOccupantRequest;
use App\Http\Requests\Bed\UpdateBedRequest;
use App\Http\Requests\Bed\UpdateBedStatusRequest;
use App\Models\Bed;
use App\Models\Department;
use App\Services\BedManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use InvalidArgumentException;

class BedController extends Controller
{
    public function __construct(
        protected BedManagementService $bedService
    ) {}

    /**
     * Display live ward bed station with KPI metrics, live visual grid, and filters.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Bed::class);

        $filters = $request->only(['search', 'department_id', 'category', 'status']);
        $beds = $this->bedService->getBedsDirectory($filters, 15);
        $metrics = $this->bedService->getBedMetrics();
        $departments = Department::where('is_clinical', true)->orderBy('name')->get();

        return view('portal.beds.index', compact('beds', 'metrics', 'departments', 'filters'));
    }

    /**
     * Display the 10-Bed Visual Occupancy Dashboard mapped by wards and live status.
     */
    public function occupancy(): View
    {
        Gate::authorize('viewAny', Bed::class);

        $dashboardData = $this->bedService->getOccupancyDashboardData();

        return view('portal.beds.occupancy', $dashboardData);
    }

    /**
     * Display chronological bed transfer & movement records across clinic.
     */
    public function transferHistory(): View
    {
        Gate::authorize('viewAny', Bed::class);

        $transfers = $this->bedService->getTransferHistory(15);
        $metrics = $this->bedService->getBedMetrics();

        return view('portal.beds.transfers', compact('transfers', 'metrics'));
    }

    /**
     * Show form to provision a new bed.
     */
    public function create(): View
    {
        Gate::authorize('create', Bed::class);

        $departments = Department::where('is_clinical', true)->orderBy('name')->get();

        return view('portal.beds.create', compact('departments'));
    }

    /**
     * Store newly configured bed in hospital inventory.
     */
    public function store(StoreBedRequest $request): RedirectResponse
    {
        $bed = $this->bedService->createBed($request->validated(), $request->user());

        return redirect()->route('portal.beds.index')
            ->with('success', "Bed {$bed->bed_number} was successfully provisioned in ward inventory.");
    }

    /**
     * Display comprehensive bed dossier, specifications, occupant history, and status timeline.
     */
    public function show(Bed $bed): View
    {
        Gate::authorize('view', $bed);

        $bed->load([
            'department',
            'currentAssignment.admission.patient',
            'currentAssignment.admission.attendingDoctor',
            'assignments.admission.patient',
            'assignments.assignedBy',
            'statusHistories.changedBy',
        ]);

        $availableBeds = Bed::available()->where('id', '!=', $bed->id)->with('department')->get();

        return view('portal.beds.show', compact('bed', 'availableBeds'));
    }

    /**
     * Show form for editing bed master parameters.
     */
    public function edit(Bed $bed): View
    {
        Gate::authorize('update', $bed);

        $departments = Department::where('is_clinical', true)->orderBy('name')->get();

        return view('portal.beds.edit', compact('bed', 'departments'));
    }

    /**
     * Update bed specifications and optional status.
     */
    public function update(UpdateBedRequest $request, Bed $bed): RedirectResponse
    {
        try {
            $this->bedService->updateBed($bed, $request->validated(), $request->user());

            return redirect()->route('portal.beds.show', $bed)
                ->with('success', "Bed {$bed->bed_number} parameters were updated successfully.");
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['status' => $e->getMessage()]);
        }
    }

    /**
     * Transfer patient occupying this bed to another target bed.
     */
    public function transferOccupant(TransferOccupantRequest $request, Bed $bed): RedirectResponse
    {
        $targetBed = Bed::findOrFail($request->validated('target_bed_id'));
        $reason = $request->validated('reason');

        try {
            $this->bedService->transferPatientBed($bed, $targetBed, $reason, $request->user());

            return back()->with('success', "Patient transferred from Bed {$bed->bed_number} to Bed {$targetBed->bed_number} successfully. Previous bed marked for cleaning.");
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['target_bed_id' => $e->getMessage()]);
        }
    }

    /**
     * Mark terminal cleaning and sanitization certified complete.
     */
    public function completeCleaning(CompleteCleaningRequest $request, Bed $bed): RedirectResponse
    {
        try {
            $this->bedService->completeCleaning($bed, $request->user(), $request->validated('notes'));

            return back()->with('success', "Sanitization verified complete. Bed {$bed->bed_number} is now Available for inpatient admission.");
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['cleaning' => $e->getMessage()]);
        }
    }

    /**
     * Complete maintenance on a bed and transition to cleaning or available.
     */
    public function completeMaintenance(CompleteMaintenanceRequest $request, Bed $bed): RedirectResponse
    {
        try {
            $requiresCleaning = $request->boolean('requires_cleaning', true);
            $this->bedService->completeMaintenance($bed, $request->user(), $request->validated('notes'), $requiresCleaning);

            $statusText = $requiresCleaning ? 'placed in Cleaning queue' : 'marked Available';

            return back()->with('success', "Maintenance repairs completed. Bed {$bed->bed_number} has been {$statusText}.");
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['maintenance' => $e->getMessage()]);
        }
    }

    /**
     * Fast status transition (Available, Cleaning, Maintenance, Reserved).
     */
    public function updateStatus(UpdateBedStatusRequest $request, Bed $bed): RedirectResponse
    {
        try {
            $this->bedService->updateBedStatus(
                $bed,
                $request->input('status'),
                $request->input('reason'),
                $request->input('notes'),
                $request->user()
            );

            return back()->with('success', "Bed {$bed->bed_number} status transitioned to " . ucfirst($request->input('status')) . ".");
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }
    }

    /**
     * Delete bed from hospital inventory.
     */
    public function destroy(Bed $bed, Request $request): RedirectResponse
    {
        Gate::authorize('delete', $bed);

        try {
            $this->bedService->deleteBed($bed, $request->user());

            return redirect()->route('portal.beds.index')
                ->with('success', "Bed {$bed->bed_number} was successfully decommissioned and removed.");
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
