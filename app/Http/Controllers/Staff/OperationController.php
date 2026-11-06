<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operation\CancelOperationRequest;
use App\Http\Requests\Operation\RecordOperationNotesRequest;
use App\Http\Requests\Operation\RescheduleOperationRequest;
use App\Http\Requests\Operation\StoreOperationRequest;
use App\Http\Requests\Operation\StorePostOpObservationRequest;
use App\Models\Admission;
use App\Models\Department;
use App\Models\Operation;
use App\Models\User;
use App\Services\OperationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OperationController extends Controller
{
    public const OT_ROOMS = [
        'OT-1 (Main Theatre)',
        'OT-2 (Minor Theatre)',
        'OT-Emergency',
        'OT-Maternity & C-Section',
    ];

    public function __construct(
        protected OperationService $operationService
    ) {}

    /**
     * Display OT dashboard & scheduled operations list.
     */
    public function index(Request $request): View
    {
        $query = Operation::query()
            ->with([
                'patient',
                'admission.currentBedAssignment.bed',
                'department',
                'primarySurgeon',
            ])
            ->latest('scheduled_at');

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by Urgency
        if ($request->filled('urgency')) {
            $query->where('urgency', $request->urgency);
        }

        // Filter by OT Room
        if ($request->filled('ot_room')) {
            $query->where('ot_room', $request->ot_room);
        }

        // Filter by Department
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Filter by Surgeon
        if ($request->filled('primary_surgeon_id')) {
            $query->where('primary_surgeon_id', $request->primary_surgeon_id);
        }

        // Filter by Date
        if ($request->filled('date')) {
            $query->whereDate('scheduled_at', $request->date);
        }

        // Search Keyword
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('operation_number', 'like', "%{$term}%")
                    ->orWhere('procedure_name', 'like', "%{$term}%")
                    ->orWhereHas('patient', function ($pq) use ($term) {
                        $pq->where('name', 'like', "%{$term}%")
                            ->orWhere('patient_code', 'like', "%{$term}%")
                            ->orWhere('phone', 'like', "%{$term}%");
                    });
            });
        }

        $operations = $query->paginate(15)->withQueryString();
        $statistics = $this->operationService->getStatistics();

        $departments = Department::where('is_clinical', true)->where('is_active', true)->orderBy('name')->get();
        $doctors = User::whereHas('roles', fn($q) => $q->where('name', 'doctor'))->orderBy('name')->get();

        return view('portal.operations.index', [
            'operations' => $operations,
            'statistics' => $statistics,
            'departments' => $departments,
            'doctors' => $doctors,
            'otRooms' => self::OT_ROOMS,
            'filters' => $request->all(),
        ]);
    }

    /**
     * Show form to schedule a new operation.
     */
    public function create(Request $request): View
    {
        $selectedAdmission = null;
        if ($request->filled('admission_id')) {
            $selectedAdmission = Admission::with(['patient', 'currentBedAssignment.bed', 'attendingDoctor', 'department'])->find($request->admission_id);
        }

        // Active admitted patients
        $admissions = Admission::query()
            ->where('status', 'admitted')
            ->with(['patient', 'currentBedAssignment.bed', 'attendingDoctor'])
            ->latest('admission_date')
            ->get();

        $departments = Department::where('is_clinical', true)->where('is_active', true)->orderBy('name')->get();
        $doctors = User::whereHas('roles', fn($q) => $q->where('name', 'doctor'))->orderBy('name')->get();

        return view('portal.operations.create', [
            'admissions' => $admissions,
            'selectedAdmission' => $selectedAdmission,
            'departments' => $departments,
            'doctors' => $doctors,
            'otRooms' => self::OT_ROOMS,
        ]);
    }

    /**
     * Store scheduled operation.
     */
    public function store(StoreOperationRequest $request): RedirectResponse
    {
        $operation = $this->operationService->scheduleOperation($request->validated(), $request->user());

        return redirect()
            ->route('portal.operations.show', $operation)
            ->with('success', "Operation {$operation->operation_number} scheduled successfully in {$operation->ot_room}.");
    }

    /**
     * Display operation details, surgical team, timeline, notes, and observations.
     */
    public function show(Operation $operation): View
    {
        $operation->load([
            'patient',
            'admission.currentBedAssignment.bed',
            'admission.attendingDoctor',
            'department',
            'primarySurgeon',
            'assistantSurgeon',
            'requestedBy',
            'rescheduledBy',
            'cancelledBy',
            'histories.changedBy',
            'postOpObservations.recordedBy',
        ]);

        $doctors = User::whereHas('roles', fn($q) => $q->where('name', 'doctor'))->orderBy('name')->get();

        return view('portal.operations.show', [
            'operation' => $operation,
            'doctors' => $doctors,
            'otRooms' => self::OT_ROOMS,
        ]);
    }

    /**
     * Reschedule operation.
     */
    public function reschedule(RescheduleOperationRequest $request, Operation $operation): RedirectResponse
    {
        $this->operationService->rescheduleOperation($operation, $request->validated(), $request->user());

        return redirect()
            ->route('portal.operations.show', $operation)
            ->with('success', "Operation {$operation->operation_number} rescheduled successfully.");
    }

    /**
     * Cancel operation.
     */
    public function cancel(CancelOperationRequest $request, Operation $operation): RedirectResponse
    {
        $this->operationService->cancelOperation($operation, $request->validated()['cancellation_reason'], $request->user());

        return redirect()
            ->route('portal.operations.show', $operation)
            ->with('success', "Operation {$operation->operation_number} has been cancelled.");
    }

    /**
     * Mark operation as in progress.
     */
    public function start(Request $request, Operation $operation): RedirectResponse
    {
        if (!$request->user()->hasAnyRole(['doctor', 'administrator', 'super_admin'])) {
            abort(403, 'Unauthorized to initiate surgical procedure.');
        }

        $this->operationService->startOperation($operation, now(), $request->user());

        return redirect()
            ->route('portal.operations.show', $operation)
            ->with('success', "Operation {$operation->operation_number} is now in progress.");
    }

    /**
     * Complete operation and record completion notes.
     */
    public function complete(Request $request, Operation $operation): RedirectResponse
    {
        if (!$request->user()->hasAnyRole(['doctor', 'administrator', 'super_admin'])) {
            abort(403, 'Unauthorized to complete surgical procedure.');
        }

        $request->validate([
            'operation_notes' => ['required', 'string', 'min:10'],
            'post_op_notes' => ['nullable', 'string'],
            'anesthetist_name' => ['nullable', 'string', 'max:191'],
            'ot_nurse_name' => ['nullable', 'string', 'max:191'],
        ]);

        $this->operationService->completeOperation($operation, $request->all(), $request->user());

        return redirect()
            ->route('portal.operations.show', $operation)
            ->with('success', "Operation {$operation->operation_number} marked as completed.");
    }

    /**
     * Record or update surgical notes.
     */
    public function recordNotes(RecordOperationNotesRequest $request, Operation $operation): RedirectResponse
    {
        $this->operationService->recordOperationNotes($operation, $request->validated(), $request->user());

        return redirect()
            ->route('portal.operations.show', $operation)
            ->with('success', 'Surgical notes updated successfully.');
    }

    /**
     * Record post-operative recovery observation.
     */
    public function storeObservation(StorePostOpObservationRequest $request, Operation $operation): RedirectResponse
    {
        $this->operationService->recordPostOpObservation($operation, $request->validated(), $request->user());

        return redirect()
            ->route('portal.operations.show', $operation)
            ->with('success', 'Post-operative bedside observation recorded successfully.');
    }

    /**
     * Visual OT Schedule Timetable Calendar.
     */
    public function calendar(Request $request): View
    {
        $selectedDate = $request->filled('date') ? Carbon::parse($request->date) : Carbon::today();

        $operations = Operation::query()
            ->with(['patient', 'primarySurgeon', 'department'])
            ->whereDate('scheduled_at', $selectedDate)
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('scheduled_at')
            ->get();

        $rooms = self::OT_ROOMS;

        return view('portal.operations.calendar', [
            'selectedDate' => $selectedDate,
            'operations' => $operations,
            'rooms' => $rooms,
        ]);
    }

    /**
     * Printable surgical record and patient OT summary.
     */
    public function printReport(Operation $operation): View
    {
        $operation->load([
            'patient',
            'admission.currentBedAssignment.bed',
            'department',
            'primarySurgeon',
            'assistantSurgeon',
            'histories.changedBy',
            'postOpObservations.recordedBy',
        ]);

        return view('portal.operations.print', [
            'operation' => $operation,
        ]);
    }
}

