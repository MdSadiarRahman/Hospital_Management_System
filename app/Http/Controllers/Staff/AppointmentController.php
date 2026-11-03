<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Appointment\BookAppointmentRequest;
use App\Http\Requests\Appointment\CancelAppointmentRequest;
use App\Http\Requests\Appointment\RescheduleAppointmentRequest;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use App\Models\User;
use App\Services\AppointmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function __construct(
        protected AppointmentService $appointmentService
    ) {}

    /**
     * Display a listing of appointments in the Staff Portal.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Appointment::class);

        $currentUser = Auth::user();
        $isDoctor = $currentUser->hasRole('doctor');

        $query = Appointment::with([
            'patient',
            'department',
            'doctor.staffProfile',
            'confirmedBy',
        ]);

        // If user is a doctor and not an admin/receptionist, scope to their own appointments
        if ($isDoctor && !$currentUser->hasAnyRole(['administrator', 'super_admin', 'receptionist'])) {
            $query->where('doctor_id', $currentUser->id);
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('guest_name', 'like', "%{$search}%")
                  ->orWhere('guest_phone', 'like', "%{$search}%")
                  ->orWhereHas('patient', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%")
                         ->orWhere('patient_code', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $appointments = $query->orderBy('appointment_date', 'asc')
            ->paginate(15)
            ->withQueryString();

        // Status counters for tab badges
        $countsQuery = Appointment::query();
        if ($isDoctor && !$currentUser->hasAnyRole(['administrator', 'super_admin', 'receptionist'])) {
            $countsQuery->where('doctor_id', $currentUser->id);
        }

        $counts = [
            'total' => (clone $countsQuery)->count(),
            'pending' => (clone $countsQuery)->where('status', Appointment::STATUS_PENDING)->count(),
            'confirmed' => (clone $countsQuery)->where('status', Appointment::STATUS_CONFIRMED)->count(),
            'rescheduled' => (clone $countsQuery)->where('status', Appointment::STATUS_RESCHEDULED)->count(),
            'completed' => (clone $countsQuery)->where('status', Appointment::STATUS_COMPLETED)->count(),
            'cancelled' => (clone $countsQuery)->where('status', Appointment::STATUS_CANCELLED)->count(),
            'no_show' => (clone $countsQuery)->where('status', Appointment::STATUS_NO_SHOW)->count(),
        ];

        $departments = Department::where('is_active', true)->orderBy('name')->get();

        $doctors = User::where('user_type', 'staff')
            ->where('is_active', true)
            ->whereHas('roles', fn($q) => $q->where('name', 'doctor'))
            ->orderBy('name')
            ->get();

        return view('portal.appointments.index', compact(
            'appointments',
            'counts',
            'departments',
            'doctors'
        ));
    }

    /**
     * Show the form for creating a new appointment at the Reception Desk.
     */
    public function create(Request $request): View
    {
        Gate::authorize('create', Appointment::class);

        $departments = Department::where('is_active', true)->orderBy('name')->get();

        $selectedPatient = null;
        if ($request->filled('patient_id')) {
            $selectedPatient = Patient::find($request->patient_id);
        }

        $doctors = User::where('user_type', 'staff')
            ->where('is_active', true)
            ->whereHas('roles', fn($q) => $q->where('name', 'doctor'))
            ->with(['staffProfile.department'])
            ->orderBy('name')
            ->get();

        return view('portal.appointments.create', compact(
            'departments',
            'doctors',
            'selectedPatient'
        ));
    }

    /**
     * Store a newly created appointment in storage.
     */
    public function store(BookAppointmentRequest $request): RedirectResponse
    {
        Gate::authorize('create', Appointment::class);

        $appointment = $this->appointmentService->bookAppointment($request->validated(), Auth::user());

        $statusMsg = $appointment->isConfirmed()
            ? "Appointment #{$appointment->id} for {$appointment->patient_display_name} successfully booked and confirmed. Serial: #{$appointment->serial_number}."
            : "Appointment request #{$appointment->id} for {$appointment->patient_display_name} successfully recorded (Pending approval).";

        return redirect()->route('portal.appointments.show', $appointment)
            ->with('status', $statusMsg);
    }

    /**
     * Display the specified appointment and its history audit timeline.
     */
    public function show(Appointment $appointment): View
    {
        Gate::authorize('view', $appointment);

        $appointment->load([
            'patient.contacts',
            'department',
            'doctor.staffProfile.department',
            'confirmedBy',
            'cancelledBy',
            'rescheduledFrom',
            'histories.changedBy',
            'visit',
        ]);

        return view('portal.appointments.show', compact('appointment'));
    }

    /**
     * Approve / Confirm a pending appointment.
     */
    public function confirm(Request $request, Appointment $appointment): RedirectResponse
    {
        Gate::authorize('approve', $appointment);

        $appointment = $this->appointmentService->confirmAppointment(
            $appointment,
            Auth::user(),
            $request->input('notes')
        );

        return back()->with('status', "Appointment #{$appointment->id} confirmed successfully. Serial: #{$appointment->serial_number}.");
    }

    /**
     * Reschedule an appointment to a new date and time slot.
     */
    public function reschedule(RescheduleAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        Gate::authorize('update', $appointment);

        $appointment = $this->appointmentService->rescheduleAppointment(
            $appointment,
            $request->new_date,
            $request->new_time,
            Auth::user(),
            $request->reschedule_reason
        );

        return back()->with('status', "Appointment #{$appointment->id} successfully rescheduled to " . $appointment->appointment_date->format('d M Y h:i A') . ". New Serial: #{$appointment->serial_number}.");
    }

    /**
     * Cancel an appointment with mandatory cancellation reason.
     */
    public function cancel(CancelAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        Gate::authorize('delete', $appointment);

        $appointment = $this->appointmentService->cancelAppointment(
            $appointment,
            Auth::user(),
            $request->cancellation_reason
        );

        return back()->with('status', "Appointment #{$appointment->id} has been cancelled.");
    }

    /**
     * Mark an appointment as Completed or No Show.
     */
    public function markStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:completed,no_show'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        Gate::authorize('update', $appointment);

        $appointment = $this->appointmentService->markStatus(
            $appointment,
            $request->status,
            Auth::user(),
            $request->notes
        );

        $label = $request->status === Appointment::STATUS_COMPLETED ? 'completed' : 'marked as No Show';
        return back()->with('status', "Appointment #{$appointment->id} has been {$label}.");
    }

    /**
     * Print appointment token slip.
     */
    public function printSlip(Appointment $appointment): View
    {
        Gate::authorize('print', $appointment);

        $appointment->load([
            'patient',
            'department',
            'doctor.staffProfile.department',
            'confirmedBy',
        ]);

        return view('portal.appointments.print-slip', compact('appointment'));
    }
}
