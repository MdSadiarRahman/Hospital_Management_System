<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Role;
use App\Models\ShiftSchedule;
use App\Models\StaffProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class StaffManagementService
{
    /**
     * Standard 8-hour shift time windows.
     */
    public const SHIFT_TIMES = [
        'morning' => ['start' => '08:00:00', 'end' => '16:00:00'],
        'evening' => ['start' => '16:00:00', 'end' => '00:00:00'],
        'night'   => ['start' => '00:00:00', 'end' => '08:00:00'],
    ];

    /**
     * Register a new staff member and bind user, roles, profile, and department allocations.
     *
     * @param array $userData
     * @param array $profileData
     * @param array<int> $roleIds
     * @param array<int> $secondaryDepartmentIds
     * @return StaffProfile
     */
    public function registerStaff(
        array $userData,
        array $profileData,
        array $roleIds,
        array $secondaryDepartmentIds = []
    ): StaffProfile {
        return DB::transaction(function () use ($userData, $profileData, $roleIds, $secondaryDepartmentIds) {
            // 1. Create User Account
            $user = User::create([
                'name' => trim($userData['name']),
                'email' => strtolower(trim($userData['email'])),
                'phone' => trim($userData['phone']),
                'password' => Hash::make($userData['password'] ?? 'Meghna@' . date('Y')),
                'user_type' => 'staff',
                'is_active' => $userData['is_active'] ?? true,
            ]);

            // 2. Attach Roles (enforce RBAC rules)
            $roles = Role::whereIn('id', $roleIds)->get();
            $user->roles()->sync($roles->pluck('id'));

            // 3. Generate unique Employee ID if not supplied
            $employeeId = $profileData['employee_id'] ?? $this->generateEmployeeId($roles->first()?->name ?? 'staff');

            // 4. Create StaffProfile
            $profile = StaffProfile::create([
                'user_id' => $user->id,
                'employee_id' => $employeeId,
                'department_id' => $profileData['department_id'] ?? null,
                'designation' => $profileData['designation'],
                'specialization' => $profileData['specialization'] ?? null,
                'license_number' => $profileData['license_number'] ?? null,
                'shift' => $profileData['shift'] ?? 'rotational',
                'employment_type' => $profileData['employment_type'] ?? 'full_time',
                'joining_date' => $profileData['joining_date'] ?? now()->toDateString(),
                'qualification' => $profileData['qualification'] ?? null,
                'salary' => $profileData['salary'] ?? null,
                'nid_number' => $profileData['nid_number'] ?? null,
                'emergency_contact_name' => $profileData['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $profileData['emergency_contact_phone'] ?? null,
                'address' => $profileData['address'] ?? null,
                'status' => $profileData['status'] ?? 'active',
            ]);

            // 5. Sync Multi-Department Allocation
            $this->syncDepartmentAllocations($profile, $profile->department_id, $secondaryDepartmentIds);

            // 6. Record Audit Log
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'staff.registered',
                'auditable_type' => StaffProfile::class,
                'auditable_id' => $profile->id,
                'old_values' => null,
                'new_values' => [
                    'employee_id' => $profile->employee_id,
                    'user_name' => $user->name,
                    'roles' => $roles->pluck('name')->all(),
                    'primary_department_id' => $profile->department_id,
                    'secondary_departments' => $secondaryDepartmentIds,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);

            return $profile->load(['user.roles', 'department', 'departments']);
        });
    }

    /**
     * Update an existing staff profile, user credentials, roles, and department allocations.
     */
    public function updateStaffProfile(
        StaffProfile $profile,
        array $userData,
        array $profileData,
        ?array $roleIds = null,
        ?array $secondaryDepartmentIds = null
    ): StaffProfile {
        return DB::transaction(function () use ($profile, $userData, $profileData, $roleIds, $secondaryDepartmentIds) {
            $user = $profile->user;

            $oldValues = [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'designation' => $profile->designation,
                'department_id' => $profile->department_id,
                'status' => $profile->status,
                'shift' => $profile->shift,
            ];

            // 1. Update User
            $userUpdate = [
                'name' => trim($userData['name']),
                'email' => strtolower(trim($userData['email'])),
                'phone' => trim($userData['phone']),
            ];

            if (isset($userData['is_active'])) {
                $userUpdate['is_active'] = (bool)$userData['is_active'];
            }

            if (!empty($userData['password'])) {
                $userUpdate['password'] = Hash::make($userData['password']);
            }

            $user->update($userUpdate);

            // 2. Sync Roles if provided
            if ($roleIds !== null) {
                $roles = Role::whereIn('id', $roleIds)->get();
                $user->roles()->sync($roles->pluck('id'));
            }

            // 3. Update StaffProfile
            $profile->update([
                'employee_id' => $profileData['employee_id'] ?? $profile->employee_id,
                'department_id' => $profileData['department_id'] ?? $profile->department_id,
                'designation' => $profileData['designation'] ?? $profile->designation,
                'specialization' => $profileData['specialization'] ?? $profile->specialization,
                'license_number' => $profileData['license_number'] ?? $profile->license_number,
                'shift' => $profileData['shift'] ?? $profile->shift,
                'employment_type' => $profileData['employment_type'] ?? $profile->employment_type,
                'joining_date' => $profileData['joining_date'] ?? $profile->joining_date,
                'qualification' => $profileData['qualification'] ?? $profile->qualification,
                'salary' => array_key_exists('salary', $profileData) ? $profileData['salary'] : $profile->salary,
                'nid_number' => array_key_exists('nid_number', $profileData) ? $profileData['nid_number'] : $profile->nid_number,
                'emergency_contact_name' => $profileData['emergency_contact_name'] ?? $profile->emergency_contact_name,
                'emergency_contact_phone' => $profileData['emergency_contact_phone'] ?? $profile->emergency_contact_phone,
                'address' => $profileData['address'] ?? $profile->address,
                'status' => $profileData['status'] ?? $profile->status,
            ]);

            // 4. Sync Multi-Department Allocation if provided
            if ($secondaryDepartmentIds !== null) {
                $this->syncDepartmentAllocations($profile, $profile->department_id, $secondaryDepartmentIds);
            }

            // 5. Audit Log
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'staff.updated',
                'auditable_type' => StaffProfile::class,
                'auditable_id' => $profile->id,
                'old_values' => $oldValues,
                'new_values' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'designation' => $profile->designation,
                    'department_id' => $profile->department_id,
                    'status' => $profile->status,
                    'shift' => $profile->shift,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);

            return $profile->fresh(['user.roles', 'department', 'departments']);
        });
    }

    /**
     * Schedule an 8-hour shift for a staff member with overlap protection.
     */
    public function scheduleShift(StaffProfile $profile, array $data, ?int $scheduledByUserId = null): ShiftSchedule
    {
        $shiftDate = Carbon::parse($data['shift_date'])->toDateString();
        $shiftType = $data['shift_type'];
        $departmentId = $data['department_id'] ?? $profile->department_id;

        if (!$departmentId) {
            throw ValidationException::withMessages([
                'department_id' => 'Please select an assigned department for this shift schedule.',
            ]);
        }

        // 1. Conflict Check: Strict check against same shift type on same day
        $existingSameShift = ShiftSchedule::where('staff_profile_id', $profile->id)
            ->where('shift_date', $shiftDate)
            ->where('shift_type', $shiftType)
            ->where('status', '!=', 'cancelled')
            ->first();

        if ($existingSameShift) {
            throw ValidationException::withMessages([
                'shift_type' => "Staff member {$profile->user->name} is already scheduled for the {$shiftType} shift on {$shiftDate}.",
            ]);
        }

        // 2. Conflict Check: Verify not scheduling conflicting consecutive double shifts without rest
        // (A staff member should not work back-to-back 16 continuous hours unless explicitly documented)
        $sameDayShifts = ShiftSchedule::where('staff_profile_id', $profile->id)
            ->where('shift_date', $shiftDate)
            ->where('status', '!=', 'cancelled')
            ->count();

        if ($sameDayShifts >= 2) {
            throw ValidationException::withMessages([
                'shift_date' => "Staff member {$profile->user->name} already has {$sameDayShifts} shifts on {$shiftDate}. Maximum daily limit reached to ensure clinical safety.",
            ]);
        }

        $times = self::SHIFT_TIMES[$shiftType] ?? ['start' => '08:00:00', 'end' => '16:00:00'];

        $shift = ShiftSchedule::create([
            'staff_profile_id' => $profile->id,
            'department_id' => $departmentId,
            'shift_date' => $shiftDate,
            'shift_type' => $shiftType,
            'start_time' => $data['start_time'] ?? $times['start'],
            'end_time' => $data['end_time'] ?? $times['end'],
            'status' => $data['status'] ?? 'scheduled',
            'notes' => $data['notes'] ?? null,
            'created_by' => $scheduledByUserId ?? Auth::id(),
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'staff.shift_scheduled',
            'auditable_type' => ShiftSchedule::class,
            'auditable_id' => $shift->id,
            'old_values' => null,
            'new_values' => [
                'staff_name' => $profile->user->name,
                'shift_date' => $shiftDate,
                'shift_type' => $shiftType,
                'department_id' => $departmentId,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        return $shift;
    }

    /**
     * Synchronize primary and secondary department allocations for multi-department coverage.
     */
    protected function syncDepartmentAllocations(StaffProfile $profile, ?int $primaryDeptId, array $secondaryDeptIds): void
    {
        $syncData = [];

        if ($primaryDeptId) {
            $syncData[$primaryDeptId] = [
                'is_primary' => true,
                'role_in_department' => 'Primary Department',
            ];
        }

        foreach ($secondaryDeptIds as $deptId) {
            if ($deptId && $deptId != $primaryDeptId) {
                $syncData[$deptId] = [
                    'is_primary' => false,
                    'role_in_department' => 'Cross-Department Coverage',
                ];
            }
        }

        $profile->departments()->sync($syncData);
    }

    /**
     * Generate structured employee code: MCNH-ROLE-YYYY-XXXX.
     */
    public function generateEmployeeId(string $role = 'staff'): string
    {
        $prefix = match ($role) {
            'doctor' => 'DOC',
            'nurse' => 'NUR',
            'receptionist' => 'RCP',
            'billing_staff' => 'BIL',
            'lab_staff' => 'LAB',
            'ot_ward_staff' => 'OTW',
            'administrator', 'super_admin' => 'ADM',
            default => 'STF',
        };

        $year = date('Y');
        $random = strtoupper(bin2hex(random_bytes(2)));

        return "MCNH-{$prefix}-{$year}-{$random}";
    }

    /**
     * Get clinic-wide shift coverage roster for a given date.
     */
    public function getDailyCoverageSummary(string $date): array
    {
        $shifts = ShiftSchedule::with(['staffProfile.user.roles', 'department'])
            ->where('shift_date', $date)
            ->where('status', '!=', 'cancelled')
            ->get();

        $morningShifts = $shifts->where('shift_type', 'morning');
        $eveningShifts = $shifts->where('shift_type', 'evening');
        $nightShifts   = $shifts->where('shift_type', 'night');

        // Check doctors on duty
        $doctorsOnDuty = $shifts->filter(fn ($s) => $s->staffProfile->isDoctor());
        $nursesOnDuty  = $shifts->filter(fn ($s) => $s->staffProfile->isNurse());

        return [
            'date' => $date,
            'total_scheduled' => $shifts->count(),
            'morning' => $morningShifts,
            'evening' => $eveningShifts,
            'night' => $nightShifts,
            'doctors_count' => $doctorsOnDuty->pluck('staff_profile_id')->unique()->count(),
            'nurses_count' => $nursesOnDuty->pluck('staff_profile_id')->unique()->count(),
            'meets_doctor_rule' => $doctorsOnDuty->pluck('staff_profile_id')->unique()->count() >= 2,
            'meets_nurse_rule' => $nursesOnDuty->pluck('staff_profile_id')->unique()->count() >= 3,
        ];
    }
}
