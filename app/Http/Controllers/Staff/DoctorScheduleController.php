<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Appointment\DoctorScheduleRequest;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\DoctorSchedule;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DoctorScheduleController extends Controller
{
    /**
     * Display a listing of doctor consultation schedules.
     */
    public function index(Request $request): View
    {
        $currentUser = Auth::user();
        $isDoctor = $currentUser->hasRole('doctor');

        $query = DoctorSchedule::with(['doctor.staffProfile', 'department']);

        // Doctors who are not admins only see their own schedules
        if ($isDoctor && !$currentUser->hasAnyRole(['administrator', 'super_admin'])) {
            $query->where('doctor_id', $currentUser->id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->filled('day_of_week')) {
            $query->where('day_of_week', $request->day_of_week);
        }

        $schedules = $query->orderBy('day_of_week')
            ->orderBy('start_time')
            ->paginate(20)
            ->withQueryString();

        $departments = Department::where('is_active', true)->orderBy('name')->get();

        $doctors = User::where('user_type', 'staff')
            ->where('is_active', true)
            ->whereHas('roles', fn($q) => $q->where('name', 'doctor'))
            ->with(['staffProfile.department'])
            ->orderBy('name')
            ->get();

        return view('portal.appointments.schedules', compact(
            'schedules',
            'departments',
            'doctors'
        ));
    }

    /**
     * Store a newly created doctor consultation schedule.
     */
    public function store(DoctorScheduleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        // Check for duplicate start time on same day for same doctor
        $exists = DoctorSchedule::where('doctor_id', $data['doctor_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->where('start_time', $data['start_time'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'start_time' => 'A schedule already exists for this doctor on the selected day and start time.',
            ]);
        }

        $schedule = DoctorSchedule::create($data);

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'doctor_schedule.created',
            'auditable_type' => DoctorSchedule::class,
            'auditable_id' => $schedule->id,
            'old_values' => null,
            'new_values' => $schedule->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('portal.doctor-schedules.index')->with('status', "Consultation schedule for {$schedule->doctor?->name} on " . ucfirst($schedule->day_of_week) . " successfully created.");
    }

    /**
     * Update the specified doctor consultation schedule.
     */
    public function update(DoctorScheduleRequest $request, DoctorSchedule $doctorSchedule): RedirectResponse
    {
        $oldValues = $doctorSchedule->toArray();
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        // Check duplicate start time if changed
        $duplicate = DoctorSchedule::where('doctor_id', $data['doctor_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->where('start_time', $data['start_time'])
            ->where('id', '!=', $doctorSchedule->id)
            ->exists();

        if ($duplicate) {
            return back()->withInput()->withErrors([
                'start_time' => 'Another schedule already exists for this doctor on the selected day and start time.',
            ]);
        }

        $doctorSchedule->update($data);

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'doctor_schedule.updated',
            'auditable_type' => DoctorSchedule::class,
            'auditable_id' => $doctorSchedule->id,
            'old_values' => $oldValues,
            'new_values' => $doctorSchedule->fresh()->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('portal.doctor-schedules.index')->with('status', "Consultation schedule updated successfully.");
    }

    /**
     * Remove the specified doctor consultation schedule.
     */
    public function destroy(DoctorSchedule $doctorSchedule): RedirectResponse
    {
        $doctorName = $doctorSchedule->doctor?->name;
        $day = ucfirst($doctorSchedule->day_of_week);

        $doctorSchedule->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'doctor_schedule.deleted',
            'auditable_type' => DoctorSchedule::class,
            'auditable_id' => $doctorSchedule->id,
            'old_values' => ['doctor' => $doctorName, 'day' => $day],
            'new_values' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('portal.doctor-schedules.index')->with('status', "Schedule for {$doctorName} on {$day} has been removed.");
    }
}
