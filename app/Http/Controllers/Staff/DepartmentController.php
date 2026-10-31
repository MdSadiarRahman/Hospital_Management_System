<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Department\AssignDepartmentStaffRequest;
use App\Http\Requests\Department\ConfigureDepartmentBedRequest;
use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Models\AuditLog;
use App\Models\Bed;
use App\Models\Department;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of hospital departments with metrics and filters.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Department::class);

        $query = Department::with([
            'beds',
            'staffProfiles.user.roles',
        ]);

        // Search by name or code
        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('code', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%");
            });
        }

        // Filter by clinical type
        if ($request->filled('type')) {
            if ($request->type === 'clinical') {
                $query->where('is_clinical', true);
            } elseif ($request->type === 'non_clinical') {
                $query->where('is_clinical', false);
            }
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $departments = $query->orderBy('is_clinical', 'desc')
            ->orderBy('name', 'asc')
            ->get();

        // System overview metrics
        $totalDepartments = Department::count();
        $totalClinical = Department::where('is_clinical', true)->count();
        $totalBeds = Bed::count();
        $totalDoctors = StaffProfile::whereHas('user.roles', fn ($q) => $q->where('name', 'doctor'))
            ->whereNotNull('department_id')
            ->count();

        return view('portal.departments.index', compact(
            'departments',
            'totalDepartments',
            'totalClinical',
            'totalBeds',
            'totalDoctors'
        ));
    }

    /**
     * Show the form for creating a new department.
     */
    public function create(): View
    {
        $this->authorize('create', Department::class);

        return view('portal.departments.create');
    }

    /**
     * Store a newly created department in storage.
     */
    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        $department = Department::create($request->validated());

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'department.created',
            'auditable_type' => Department::class,
            'auditable_id' => $department->id,
            'old_values' => null,
            'new_values' => $department->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('portal.departments.show', $department->id)
            ->with('status', "Department '{$department->name}' ({$department->code}) successfully registered.");
    }

    /**
     * Display the specified department details, staff, doctors, and beds.
     */
    public function show(Department $department): View
    {
        $this->authorize('view', $department);

        $department->load([
            'beds.currentAssignment.patient',
            'staffProfiles.user.roles',
        ]);

        // Segregate doctors from general clinical & nursing staff
        $doctors = $department->staffProfiles->filter(function ($profile) {
            return $profile->user && $profile->user->hasRole('doctor');
        });

        $otherStaff = $department->staffProfiles->reject(function ($profile) {
            return $profile->user && $profile->user->hasRole('doctor');
        });

        // Bed statistics
        $totalBeds = $department->beds->count();
        $availableBeds = $department->beds->where('status', 'available')->count();
        $occupiedBeds = $department->beds->where('status', 'occupied')->count();
        $maintenanceBeds = $department->beds->whereIn('status', ['cleaning', 'maintenance', 'reserved'])->count();

        // Potential staff members for assignment modal
        $assignableStaff = User::where('user_type', 'staff')
            ->where('is_active', true)
            ->with(['roles', 'staffProfile'])
            ->orderBy('name')
            ->get();

        return view('portal.departments.show', compact(
            'department',
            'doctors',
            'otherStaff',
            'totalBeds',
            'availableBeds',
            'occupiedBeds',
            'maintenanceBeds',
            'assignableStaff'
        ));
    }

    /**
     * Show the form for editing the department.
     */
    public function edit(Department $department): View
    {
        $this->authorize('update', $department);

        return view('portal.departments.edit', compact('department'));
    }

    /**
     * Update the specified department in storage.
     */
    public function update(UpdateDepartmentRequest $request, Department $department): RedirectResponse
    {
        $oldValues = $department->only(['name', 'code', 'description', 'is_clinical', 'is_active']);

        $department->update($request->validated());

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'department.updated',
            'auditable_type' => Department::class,
            'auditable_id' => $department->id,
            'old_values' => $oldValues,
            'new_values' => $department->only(['name', 'code', 'description', 'is_clinical', 'is_active']),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('portal.departments.show', $department->id)
            ->with('status', "Department '{$department->name}' details have been updated.");
    }

    /**
     * Toggle the department active/inactive status.
     */
    public function toggleStatus(Request $request, Department $department): RedirectResponse
    {
        $this->authorize('update', $department);

        $newStatus = !$department->is_active;
        $department->update(['is_active' => $newStatus]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'department.status_toggled',
            'auditable_type' => Department::class,
            'auditable_id' => $department->id,
            'old_values' => ['is_active' => !$newStatus],
            'new_values' => ['is_active' => $newStatus],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        $statusLabel = $newStatus ? 'activated' : 'deactivated';
        return back()->with('status', "Department '{$department->name}' has been {$statusLabel}.");
    }

    /**
     * Assign or transfer a staff member/doctor to this department.
     */
    public function assignStaff(AssignDepartmentStaffRequest $request, Department $department): RedirectResponse
    {
        $validated = $request->validated();
        $user = User::findOrFail($validated['user_id']);

        $profile = StaffProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'department_id' => $department->id,
                'designation' => $validated['designation'],
                'specialization' => $validated['specialization'] ?? null,
                'license_number' => $validated['license_number'] ?? null,
                'shift' => $validated['shift'],
                'status' => $validated['status'],
            ]
        );

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'department.staff_assigned',
            'auditable_type' => StaffProfile::class,
            'auditable_id' => $profile->id,
            'old_values' => null,
            'new_values' => [
                'department_id' => $department->id,
                'department_name' => $department->name,
                'user_name' => $user->name,
                'designation' => $profile->designation,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return back()->with('status', "Staff member '{$user->name}' assigned to {$department->name}.");
    }

    /**
     * Remove or detach a staff member from this department.
     */
    public function removeStaff(Department $department, int $staffProfileId): RedirectResponse
    {
        $this->authorize('manageStaff', $department);

        $profile = StaffProfile::where('department_id', $department->id)->findOrFail($staffProfileId);
        $userName = $profile->user?->name ?? 'Staff Member';

        $profile->update(['department_id' => null]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'department.staff_removed',
            'auditable_type' => StaffProfile::class,
            'auditable_id' => $profile->id,
            'old_values' => ['department_id' => $department->id],
            'new_values' => ['department_id' => null],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        return back()->with('status', "Staff member '{$userName}' unassigned from {$department->name}.");
    }

    /**
     * Configure / create a new inpatient bed for this department.
     */
    public function storeBed(ConfigureDepartmentBedRequest $request, Department $department): RedirectResponse
    {
        $bed = Bed::create([
            'department_id' => $department->id,
            'bed_number' => $request->bed_number,
            'category' => $request->category,
            'daily_charge' => $request->daily_charge,
            'status' => $request->status,
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'department.bed_configured',
            'auditable_type' => Bed::class,
            'auditable_id' => $bed->id,
            'old_values' => null,
            'new_values' => $bed->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return back()->with('status', "Bed '{$bed->bed_number}' successfully configured in {$department->name}.");
    }

    /**
     * Update an existing bed configuration within this department.
     */
    public function updateBed(ConfigureDepartmentBedRequest $request, Department $department, Bed $bed): RedirectResponse
    {
        if ($bed->department_id !== $department->id) {
            abort(404, 'Bed not found in this department.');
        }

        $oldValues = $bed->only(['bed_number', 'category', 'daily_charge', 'status']);

        $bed->update([
            'bed_number' => $request->bed_number,
            'category' => $request->category,
            'daily_charge' => $request->daily_charge,
            'status' => $request->status,
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'department.bed_updated',
            'auditable_type' => Bed::class,
            'auditable_id' => $bed->id,
            'old_values' => $oldValues,
            'new_values' => $bed->only(['bed_number', 'category', 'daily_charge', 'status']),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return back()->with('status', "Bed '{$bed->bed_number}' configuration updated.");
    }

    /**
     * Decommission / remove a bed from this department.
     */
    public function deleteBed(Department $department, Bed $bed): RedirectResponse
    {
        $this->authorize('configureBeds', $department);

        if ($bed->department_id !== $department->id) {
            abort(404, 'Bed not found in this department.');
        }

        if ($bed->status === 'occupied' || $bed->currentAssignment()->exists()) {
            return back()->with('error', "Cannot decommission Bed '{$bed->bed_number}' while it is occupied by an admitted patient.");
        }

        $bedNumber = $bed->bed_number;
        $bed->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'department.bed_decommissioned',
            'auditable_type' => Bed::class,
            'auditable_id' => $bed->id,
            'old_values' => ['bed_number' => $bedNumber, 'department_id' => $department->id],
            'new_values' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        return back()->with('status', "Bed '{$bedNumber}' decommissioned.");
    }

    /**
     * Decommission / delete a department if safe.
     */
    public function destroy(Department $department): RedirectResponse
    {
        $this->authorize('delete', $department);

        // Safety check: Prevent deletion if there are active beds or occupied beds
        if ($department->beds()->where('status', 'occupied')->exists()) {
            return back()->with('error', "Cannot delete department '{$department->name}' while it has active admitted inpatients.");
        }

        // Safety check: Prevent deletion if there are beds or staff assigned
        if ($department->beds()->count() > 0 || $department->staffProfiles()->count() > 0) {
            return back()->with('error', "Department '{$department->name}' has configured beds or assigned staff. Please reassign or decommission them first, or deactivate the department instead.");
        }

        $deptName = $department->name;
        $deptId = $department->id;

        $department->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'department.deleted',
            'auditable_type' => Department::class,
            'auditable_id' => $deptId,
            'old_values' => ['name' => $deptName],
            'new_values' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('portal.departments.index')
            ->with('status', "Department '{$deptName}' has been removed.");
    }
}
