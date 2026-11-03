<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\Appointment\BookAppointmentRequest;
use App\Http\Requests\Appointment\CancelAppointmentRequest;
use App\Http\Requests\Appointment\RescheduleAppointmentRequest;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\User;
use App\Services\AppointmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PatientAppointmentController extends Controller
{
    public function __construct(
        protected AppointmentService $appointmentService
    ) {}

    /**
     * Display a listing of personal appointments for the logged-in patient.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $patient = $user->patient;

        // Appointments can be linked via patient_id or email
        $query = Appointment::with([
            'department',
            'doctor.staffProfile.department',
            'histories.changedBy',
        ]);

        if ($patient) {
            $query->where('patient_id', $patient->id);
        } else {
            // For unlinked self-registered user
            $query->where(function ($q) use ($user) {
                $q->where('guest_email', $user->email)
                  ->orWhere('guest_phone', $user->phone);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $appointments = $query->orderBy('appointment_date', 'desc')
            ->paginate(10)
            ->withQueryString();

        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('patient.appointments.index', compact(
            'appointments',
            'departments',
            'patient'
        ));
    }

    /**
     * Show the appointment request form for registered patient.
     */
    public function create(): View
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        $doctors = User::where('user_type', 'staff')
            ->where('is_active', true)
            ->whereHas('roles', fn($q) => $q->where('name', 'doctor'))
            ->with(['staffProfile.department'])
            ->orderBy('name')
            ->get();

        return view('patient.appointments.create', compact('departments', 'doctors'));
    }

    /**
     * Store an appointment request from registered patient.
     */
    public function store(BookAppointmentRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $data = $request->validated();

        if ($user->patient) {
            $data['patient_id'] = $user->patient->id;
        } else {
            $data['guest_name'] = $user->name;
            $data['guest_phone'] = $user->phone;
            $data['guest_email'] = $user->email;
        }

        $data['type'] = 'online';

        $appointment = $this->appointmentService->bookAppointment($data, $user);

        return redirect()->route('patient.appointments.index')
            ->with('status', "Your appointment request (#{$appointment->id}) with Dr. {$appointment->doctor?->name} has been submitted! Our reception desk will confirm your appointment.");
    }

    /**
     * Patient reschedules their appointment.
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

        return back()->with('status', "Your appointment (#{$appointment->id}) has been rescheduled to " . $appointment->appointment_date->format('d M Y h:i A') . ".");
    }

    /**
     * Patient cancels their appointment.
     */
    public function cancel(CancelAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        Gate::authorize('delete', $appointment);

        $appointment = $this->appointmentService->cancelAppointment(
            $appointment,
            Auth::user(),
            $request->cancellation_reason
        );

        return back()->with('status', "Your appointment (#{$appointment->id}) has been cancelled.");
    }

    /**
     * Patient prints their appointment slip.
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
