<?php

namespace App\Http\Controllers;

use App\Http\Requests\Appointment\BookAppointmentRequest;
use App\Models\Department;
use App\Models\User;
use App\Services\AppointmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicAppointmentController extends Controller
{
    public function __construct(
        protected AppointmentService $appointmentService
    ) {}

    /**
     * Store an appointment request from public visitors.
     */
    public function store(BookAppointmentRequest $request): RedirectResponse
    {
        $appointment = $this->appointmentService->bookAppointment($request->validated(), null);

        return redirect()->to(url()->previous() . '#appointment')
            ->with('appointment_success', true)
            ->with('appointment_id', $appointment->id)
            ->with('appointment_doctor', $appointment->doctor?->name)
            ->with('appointment_date', $appointment->appointment_date->format('d M Y h:i A'))
            ->with('appointment_serial', $appointment->serial_number)
            ->with('status', "Your appointment request (#{$appointment->id}) has been submitted successfully! Our reception desk will review and confirm your slot.");
    }

    /**
     * AJAX endpoint to return available time slots for a doctor on a specific date.
     */
    public function getAvailableSlots(Request $request): JsonResponse
    {
        $request->validate([
            'doctor_id' => ['required', 'integer', 'exists:users,id'],
            'date' => ['required', 'date'],
            'ignore_appointment_id' => ['nullable', 'integer'],
        ]);

        $slotsData = $this->appointmentService->getAvailableSlots(
            (int) $request->doctor_id,
            $request->date,
            $request->ignore_appointment_id ? (int) $request->ignore_appointment_id : null
        );

        return response()->json($slotsData);
    }

    /**
     * AJAX endpoint to return doctors assigned to a specific department.
     */
    public function getDoctorsByDepartment(Request $request): JsonResponse
    {
        $request->validate([
            'department_id' => ['required', 'integer', 'exists:departments,id'],
        ]);

        $doctors = User::where('user_type', 'staff')
            ->where('is_active', true)
            ->whereHas('roles', fn($q) => $q->where('name', 'doctor'))
            ->where(function ($query) use ($request) {
                $query->whereHas('staffProfile', fn($q) => $q->where('department_id', $request->department_id))
                    ->orWhereHas('staffProfile.secondaryDepartments', fn($q) => $q->where('departments.id', $request->department_id));
            })
            ->with(['staffProfile.department'])
            ->get()
            ->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'name' => $doc->name,
                    'designation' => $doc->staffProfile?->designation ?? 'Consultant',
                    'specialization' => $doc->staffProfile?->specialization ?? '',
                    'room_number' => $doc->staffProfile?->room_number ?? '',
                ];
            });

        return response()->json([
            'department_id' => (int) $request->department_id,
            'doctors' => $doctors,
        ]);
    }
}
