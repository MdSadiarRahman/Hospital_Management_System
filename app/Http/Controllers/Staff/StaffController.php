<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\ScheduleShiftRequest;
use App\Http\Requests\Staff\StoreStaffRequest;
use App\Http\Requests\Staff\UpdateStaffRequest;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Role;
use App\Models\ShiftSchedule;
use App\Models\StaffProfile;
use App\Services\StaffManagementService;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class StaffController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected StaffManagementService $staffService
    ) {}

    /**
     * Display a listing of hospital staff with search and filtering.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', StaffProfile::class);

        $query = StaffProfile::with([
            'user.roles',
            'department',
            'departments',
        ]);

        // Search by name, email, phone, employee ID, license number, or designation
        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('employee_id', 'like', "%{$term}%")
                  ->orWhere('designation', 'like', "%{$term}%")
                  ->orWhere('license_number', 'like', "%{$term}%")
                  ->orWhere('specialization', 'like', "%{$term}%")
                  ->orWhereHas('user', function ($uq) use ($term) {
                      $uq->where('name', 'like', "%{$term}%")
                         ->orWhere('email', 'like', "%{$term}%")
                         ->orWhere('phone', 'like', "%{$term}%");
                  });
            });
        }

        // Filter by Department (primary or secondary)
        if ($request->filled('department_id')) {
            $deptId = (int)$request->department_id;
            $query->where(function ($q) use ($deptId) {
                $q->where('department_id', $deptId)
                  ->orWhereHas('departments', fn ($dq) => $dq->where('departments.id', $deptId));
            });
        }

        // Filter by Role (doctor, nurse, receptionist, etc.)
        if ($request->filled('role')) {
            $roleName = $request->role;
            $query->whereHas('user.roles', fn ($rq) => $rq->where('name', $roleName));
        }

        // Filter by Status (active, on_leave, inactive)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by Shift (morning, evening, night, rotational)
        if ($request->filled('shift')) {
            $query->where('shift', $request->shift);
        }

        $staffList = $query->orderBy('status', 'asc')
            ->orderBy('designation', 'asc')
            ->paginate(15)
            ->withQueryString();

        // System Staff Metrics
        $totalStaff = StaffProfile::count();
        $totalDoctors = StaffProfile::whereHas('user.roles', fn ($q) => $q->where('name', 'doctor'))
            ->where('status', 'active')
            ->count();
        $totalNurses = StaffProfile::whereHas('user.roles', fn ($q) => $q->where('name', 'nurse'))
            ->where('status', 'active')
            ->count();
        $onLeave = StaffProfile::where('status', 'on_leave')->count();

        // Dropdown options for filter panel
        $departments = Department::orderBy('name')->get();
        $roles = Role::where('name', '!=', 'patient')->orderBy('display_name')->get();

        return view('portal.staff.index', compact(
            'staffList',
            'departments',
            'roles',
            'totalStaff',
            'totalDoctors',
            'totalNurses',
            'onLeave'
        ));
    }

    /**
     * Show the form for registering a new staff member.
     */
    public function create(): View
    {
        Gate::authorize('create', StaffProfile::class);

        $departments = Department::orderBy('name')->get();
        $roles = Role::where('name', '!=', 'patient')->orderBy('display_name')->get();

        return view('portal.staff.create', compact('departments', 'roles'));
    }

    /**
     * Store a newly registered staff member.
     */
    public function store(StoreStaffRequest $request): RedirectResponse
    {
        $userData = $request->only(['name', 'email', 'phone', 'password', 'is_active']);
        $profileData = $request->only([
            'employee_id',
            'department_id',
            'designation',
            'specialization',
            'license_number',
            'shift',
            'status',
            'employment_type',
            'joining_date',
            'qualification',
            'salary',
            'nid_number',
            'emergency_contact_name',
            'emergency_contact_phone',
            'address',
        ]);

        $roleIds = $request->input('role_ids', []);
        $secondaryDeptIds = $request->input('secondary_department_ids', []);

        $profile = $this->staffService->registerStaff($userData, $profileData, $roleIds, $secondaryDeptIds);

        return redirect()->route('portal.staff.show', $profile)
            ->with('status', "Staff member '{$profile->user->name}' ({$profile->employee_id}) successfully registered.");
    }

    /**
     * Display the specified staff profile details, department allocations, and shift history.
     */
    public function show(StaffProfile $staffProfile): View
    {
        Gate::authorize('view', $staffProfile);

        $staffProfile->load([
            'user.roles',
            'department',
            'departments',
            'shiftSchedules.department',
            'shiftSchedules.createdBy',
        ]);

        $canViewSensitive = Auth::user()->can('viewSensitive', $staffProfile);

        $upcomingShifts = $staffProfile->shiftSchedules()
            ->with('department')
            ->where('shift_date', '>=', now()->toDateString())
            ->orderBy('shift_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->take(10)
            ->get();

        $recentShifts = $staffProfile->shiftSchedules()
            ->with('department')
            ->where('shift_date', '<', now()->toDateString())
            ->orderBy('shift_date', 'desc')
            ->take(10)
            ->get();

        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('portal.staff.show', compact(
            'staffProfile',
            'canViewSensitive',
            'upcomingShifts',
            'recentShifts',
            'departments'
        ));
    }

    /**
     * Show the form for editing the staff profile.
     */
    public function edit(StaffProfile $staffProfile): View
    {
        Gate::authorize('update', $staffProfile);

        $staffProfile->load(['user.roles', 'departments']);
        $departments = Department::orderBy('name')->get();
        $roles = Role::where('name', '!=', 'patient')->orderBy('display_name')->get();

        return view('portal.staff.edit', compact('staffProfile', 'departments', 'roles'));
    }

    /**
     * Update the specified staff member in storage.
     */
    public function update(UpdateStaffRequest $request, StaffProfile $staffProfile): RedirectResponse
    {
        $userData = $request->only(['name', 'email', 'phone', 'password', 'is_active']);
        $profileData = $request->only([
            'employee_id',
            'department_id',
            'designation',
            'specialization',
            'license_number',
            'shift',
            'status',
            'employment_type',
            'joining_date',
            'qualification',
            'salary',
            'nid_number',
            'emergency_contact_name',
            'emergency_contact_phone',
            'address',
        ]);

        $roleIds = $request->input('role_ids');
        $secondaryDeptIds = $request->input('secondary_department_ids', []);

        $profile = $this->staffService->updateStaffProfile($staffProfile, $userData, $profileData, $roleIds, $secondaryDeptIds);

        return redirect()->route('portal.staff.show', $profile)
            ->with('status', "Staff profile for '{$profile->user->name}' has been updated.");
    }

    /**
     * Toggle the active/inactive status of a staff member.
     */
    public function toggleStatus(StaffProfile $staffProfile): RedirectResponse
    {
        Gate::authorize('update', $staffProfile);

        $newStatus = $staffProfile->status === 'active' ? 'inactive' : 'active';
        $staffProfile->update(['status' => $newStatus]);
        $staffProfile->user->update(['is_active' => $newStatus === 'active']);

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'staff.status_toggled',
            'auditable_type' => StaffProfile::class,
            'auditable_id' => $staffProfile->id,
            'old_values' => ['status' => $newStatus === 'active' ? 'inactive' : 'active'],
            'new_values' => ['status' => $newStatus],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        $label = $newStatus === 'active' ? 'activated' : 'deactivated';
        return back()->with('status', "Staff member '{$staffProfile->user->name}' has been {$label}.");
    }

    /**
     * Schedule an 8-hour shift for a staff member.
     */
    public function scheduleShift(ScheduleShiftRequest $request): RedirectResponse
    {
        $profile = StaffProfile::findOrFail($request->staff_profile_id);

        $shift = $this->staffService->scheduleShift($profile, $request->validated(), Auth::id());

        return back()->with('status', "8-hour {$shift->shift_type} shift scheduled for {$profile->user->name} on " . $shift->shift_date->format('d M Y') . ".");
    }

    /**
     * Delete / cancel a scheduled shift.
     */
    public function deleteShift(ShiftSchedule $shiftSchedule): RedirectResponse
    {
        Gate::authorize('scheduleShifts', StaffProfile::class);

        $staffName = $shiftSchedule->staffProfile->user->name;
        $date = $shiftSchedule->shift_date->format('d M Y');
        $shiftType = $shiftSchedule->shift_type;

        $shiftSchedule->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'staff.shift_deleted',
            'auditable_type' => ShiftSchedule::class,
            'auditable_id' => $shiftSchedule->id,
            'old_values' => ['staff_name' => $staffName, 'shift_date' => $date, 'shift_type' => $shiftType],
            'new_values' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        return back()->with('status', "Scheduled {$shiftType} shift for {$staffName} on {$date} removed.");
    }

    /**
     * Clinic-wide 24/7 Shift Scheduling & Roster Board.
     */
    public function shifts(Request $request): View
    {
        Gate::authorize('viewAny', StaffProfile::class);

        $targetDate = $request->query('date', now()->toDateString());
        $summary = $this->staffService->getDailyCoverageSummary($targetDate);

        $activeStaff = StaffProfile::with(['user.roles', 'department'])
            ->where('status', 'active')
            ->orderBy('designation')
            ->get();

        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('portal.staff.shifts', compact(
            'summary',
            'targetDate',
            'activeStaff',
            'departments'
        ));
    }
}
