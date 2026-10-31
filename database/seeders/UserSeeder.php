<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Role;
use App\Models\ShiftSchedule;
use App\Models\StaffProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('Meghna@2026!');

        $users = [
            // 1. Leadership & Administration
            [
                'name' => 'System Director (Super Admin)',
                'email' => 'superadmin@meghnaclinic.com',
                'phone' => '+8801711000000',
                'user_type' => 'staff',
                'is_active' => true,
                'role' => 'super_admin',
                'employee_id' => 'MCNH-ADM-2026-0001',
                'designation' => 'Managing Director / Super Admin',
                'shift' => 'rotational',
                'employment_type' => 'full_time',
                'salary' => 85000.00,
                'nid_number' => '1982271890001',
                'qualification' => 'M.B.B.S, M.H.A',
            ],
            [
                'name' => 'Md. Sadiar Rahman (Admin)',
                'email' => 'admin@meghnaclinic.com',
                'phone' => '+8801711000001',
                'user_type' => 'staff',
                'is_active' => true,
                'role' => 'administrator',
                'employee_id' => 'MCNH-ADM-2026-0002',
                'designation' => 'Hospital Administrator',
                'shift' => 'morning',
                'employment_type' => 'full_time',
                'salary' => 55000.00,
                'nid_number' => '1988271890002',
                'qualification' => 'MBA in Healthcare Management',
            ],

            // 2. Doctors (24/7 Coverage - 2 Doctors available at all times)
            [
                'name' => 'Dr. Nasreen Sultana',
                'email' => 'doctor@meghnaclinic.com',
                'phone' => '+8801711000002',
                'user_type' => 'staff',
                'is_active' => true,
                'role' => 'doctor',
                'employee_id' => 'MCNH-DOC-2026-0010',
                'designation' => 'Senior Consultant (Gynecology & Obstetrics)',
                'specialization' => 'Gynecology, Obstetrics & Maternal Care',
                'license_number' => 'BMDC-A-41289',
                'shift' => 'morning',
                'employment_type' => 'full_time',
                'dept_code' => 'GYN',
                'secondary_dept_codes' => ['SURG'],
                'salary' => 75000.00,
                'nid_number' => '1984271890010',
                'qualification' => 'MBBS (DMC), FCPS (Gynae & Obs)',
            ],
            [
                'name' => 'Dr. M. A. Samad',
                'email' => 'doctor.ortho@meghnaclinic.com',
                'phone' => '+8801711000011',
                'user_type' => 'staff',
                'is_active' => true,
                'role' => 'doctor',
                'employee_id' => 'MCNH-DOC-2026-0011',
                'designation' => 'Consultant Orthopedic Surgeon',
                'specialization' => 'Trauma, Joint Replacement & Bone Surgery',
                'license_number' => 'BMDC-A-38712',
                'shift' => 'evening',
                'employment_type' => 'full_time',
                'dept_code' => 'ORTHO',
                'secondary_dept_codes' => ['SURG', 'EMERG'],
                'salary' => 78000.00,
                'nid_number' => '1981271890011',
                'qualification' => 'MBBS, MS (Orthopedics), FICS',
            ],
            [
                'name' => 'Dr. Farhana Yasmin',
                'email' => 'doctor.emerg@meghnaclinic.com',
                'phone' => '+8801711000012',
                'user_type' => 'staff',
                'is_active' => true,
                'role' => 'doctor',
                'employee_id' => 'MCNH-DOC-2026-0012',
                'designation' => 'Resident Medical Officer (Emergency & Surgery)',
                'specialization' => 'Emergency Triage & Surgical Resuscitation',
                'license_number' => 'BMDC-A-52904',
                'shift' => 'night',
                'employment_type' => 'full_time',
                'dept_code' => 'EMERG',
                'secondary_dept_codes' => ['SURG'],
                'salary' => 62000.00,
                'nid_number' => '1992271890012',
                'qualification' => 'MBBS (RU), CCD (BIRDEM)',
            ],

            // 3. Nursing Team (6 Nurses / Sisters covering 3 x 8-hour shifts)
            [
                'name' => 'Sister Shima Begum',
                'email' => 'nurse@meghnaclinic.com',
                'phone' => '+8801711000003',
                'user_type' => 'staff',
                'is_active' => true,
                'role' => 'nurse',
                'employee_id' => 'MCNH-NUR-2026-0020',
                'designation' => 'Senior In-Charge Sister',
                'specialization' => 'Inpatient Ward & Post-operative Care',
                'license_number' => 'BNMC-RN-18920',
                'shift' => 'morning',
                'employment_type' => 'full_time',
                'dept_code' => 'GYN',
                'secondary_dept_codes' => ['SURG'],
                'salary' => 32000.00,
                'nid_number' => '1987271890020',
                'qualification' => 'B.Sc in Nursing (DU)',
            ],
            [
                'name' => 'Sister Parveen Akhter',
                'email' => 'nurse2@meghnaclinic.com',
                'phone' => '+8801711000021',
                'user_type' => 'staff',
                'is_active' => true,
                'role' => 'nurse',
                'employee_id' => 'MCNH-NUR-2026-0021',
                'designation' => 'Staff Nurse (Morning Shift)',
                'specialization' => 'Maternal & Newborn Care',
                'license_number' => 'BNMC-RN-22410',
                'shift' => 'morning',
                'employment_type' => 'full_time',
                'dept_code' => 'GYN',
                'salary' => 26000.00,
                'nid_number' => '1994271890021',
                'qualification' => 'Diploma in Midwifery & Nursing',
            ],
            [
                'name' => 'Sister Ruma Chowdhury',
                'email' => 'nurse3@meghnaclinic.com',
                'phone' => '+8801711000022',
                'user_type' => 'staff',
                'is_active' => true,
                'role' => 'nurse',
                'employee_id' => 'MCNH-NUR-2026-0022',
                'designation' => 'Staff Nurse (Evening Shift)',
                'specialization' => 'Surgical Ward & Orthopedic Care',
                'license_number' => 'BNMC-RN-25102',
                'shift' => 'evening',
                'employment_type' => 'full_time',
                'dept_code' => 'ORTHO',
                'salary' => 26000.00,
                'nid_number' => '1995271890022',
                'qualification' => 'Diploma in Nursing Science',
            ],
            [
                'name' => 'Sister Taslima Khatun',
                'email' => 'nurse4@meghnaclinic.com',
                'phone' => '+8801711000023',
                'user_type' => 'staff',
                'is_active' => true,
                'role' => 'nurse',
                'employee_id' => 'MCNH-NUR-2026-0023',
                'designation' => 'Staff Nurse (Evening Shift)',
                'specialization' => 'Emergency Triage & Vital Monitoring',
                'license_number' => 'BNMC-RN-26488',
                'shift' => 'evening',
                'employment_type' => 'full_time',
                'dept_code' => 'EMERG',
                'salary' => 26000.00,
                'nid_number' => '1996271890023',
                'qualification' => 'Diploma in Nursing Science',
            ],
            [
                'name' => 'Sister Maya Rani Paul',
                'email' => 'nurse5@meghnaclinic.com',
                'phone' => '+8801711000024',
                'user_type' => 'staff',
                'is_active' => true,
                'role' => 'nurse',
                'employee_id' => 'MCNH-NUR-2026-0024',
                'designation' => 'Night Duty Sister',
                'specialization' => 'Inpatient Overnight Surveillance & Medication (MAR)',
                'license_number' => 'BNMC-RN-21045',
                'shift' => 'night',
                'employment_type' => 'full_time',
                'dept_code' => 'SURG',
                'salary' => 28000.00,
                'nid_number' => '1991271890024',
                'qualification' => 'B.Sc in Nursing',
            ],
            [
                'name' => 'Sister Beauty Roy',
                'email' => 'nurse6@meghnaclinic.com',
                'phone' => '+8801711000025',
                'user_type' => 'staff',
                'is_active' => true,
                'role' => 'nurse',
                'employee_id' => 'MCNH-NUR-2026-0025',
                'designation' => 'Night Duty Staff Nurse',
                'specialization' => 'Post-op Monitoring & Bedside Nursing',
                'license_number' => 'BNMC-RN-27801',
                'shift' => 'night',
                'employment_type' => 'full_time',
                'dept_code' => 'GYN',
                'salary' => 27000.00,
                'nid_number' => '1997271890025',
                'qualification' => 'Diploma in Nursing Science',
            ],

            // 4. Clinical Support & Administrative Personnel
            [
                'name' => 'Kowshik Roy',
                'email' => 'reception@meghnaclinic.com',
                'phone' => '+8801711000004',
                'user_type' => 'staff',
                'is_active' => true,
                'role' => 'receptionist',
                'employee_id' => 'MCNH-RCP-2026-0030',
                'designation' => 'Front Desk Receptionist',
                'shift' => 'morning',
                'employment_type' => 'full_time',
                'salary' => 22000.00,
                'nid_number' => '1993271890030',
                'qualification' => 'B.A (Hons), Diploma in IT',
            ],
            [
                'name' => 'Aftab Ahmed',
                'email' => 'billing@meghnaclinic.com',
                'phone' => '+8801711000005',
                'user_type' => 'staff',
                'is_active' => true,
                'role' => 'billing_staff',
                'employee_id' => 'MCNH-BIL-2026-0031',
                'designation' => 'Billing & Cashier Officer',
                'shift' => 'rotational',
                'employment_type' => 'full_time',
                'salary' => 25000.00,
                'nid_number' => '1990271890031',
                'qualification' => 'B.Com (Accounting)',
            ],
            [
                'name' => 'Md. Faruk Hossain',
                'email' => 'lab@meghnaclinic.com',
                'phone' => '+8801711000006',
                'user_type' => 'staff',
                'is_active' => true,
                'role' => 'lab_staff',
                'employee_id' => 'MCNH-LAB-2026-0032',
                'designation' => 'Senior Medical Technologist (Lab)',
                'shift' => 'morning',
                'employment_type' => 'full_time',
                'salary' => 30000.00,
                'nid_number' => '1989271890032',
                'qualification' => 'B.Sc in Medical Laboratory Technology',
            ],
            [
                'name' => 'Md. Rafiqul Islam',
                'email' => 'otward@meghnaclinic.com',
                'phone' => '+8801711000009',
                'user_type' => 'staff',
                'is_active' => true,
                'role' => 'ot_ward_staff',
                'employee_id' => 'MCNH-OTW-2026-0033',
                'designation' => 'OT & Inpatient Ward Attendant',
                'shift' => 'rotational',
                'employment_type' => 'full_time',
                'dept_code' => 'SURG',
                'secondary_dept_codes' => ['ORTHO'],
                'salary' => 18000.00,
                'nid_number' => '1991271890033',
                'qualification' => 'H.S.C, Trained OT Technician',
            ],

            // 5. Patient Users
            [
                'name' => 'Most. Rokeya Akhter',
                'email' => 'patient@meghnaclinic.com',
                'phone' => '+8801711000007',
                'user_type' => 'patient',
                'is_active' => true,
                'role' => 'patient',
            ],
            [
                'name' => 'Tanvir Hasan (Unlinked)',
                'email' => 'newpatient@meghnaclinic.com',
                'phone' => '+8801711000008',
                'user_type' => 'patient',
                'is_active' => true,
                'role' => 'patient',
            ],
        ];

        foreach ($users as $userData) {
            $roleName = $userData['role'];
            $deptCode = $userData['dept_code'] ?? null;
            $secondaryCodes = $userData['secondary_dept_codes'] ?? [];
            $employeeId = $userData['employee_id'] ?? null;
            $designation = $userData['designation'] ?? 'Staff Member';
            $specialization = $userData['specialization'] ?? null;
            $licenseNumber = $userData['license_number'] ?? null;
            $shift = $userData['shift'] ?? 'rotational';
            $employmentType = $userData['employment_type'] ?? 'full_time';
            $salary = $userData['salary'] ?? null;
            $nidNumber = $userData['nid_number'] ?? null;
            $qualification = $userData['qualification'] ?? null;

            unset(
                $userData['role'],
                $userData['dept_code'],
                $userData['secondary_dept_codes'],
                $userData['employee_id'],
                $userData['designation'],
                $userData['specialization'],
                $userData['license_number'],
                $userData['shift'],
                $userData['employment_type'],
                $userData['salary'],
                $userData['nid_number'],
                $userData['qualification']
            );

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, ['password' => $defaultPassword])
            );

            $role = Role::where('name', $roleName)->first();
            if ($role && !$user->roles->contains($role->id)) {
                $user->roles()->attach($role->id);
            }

            if ($user->user_type === 'staff') {
                $primaryDept = $deptCode ? Department::where('code', $deptCode)->first() : null;

                $profile = StaffProfile::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'employee_id' => $employeeId,
                        'department_id' => $primaryDept?->id,
                        'designation' => $designation,
                        'specialization' => $specialization,
                        'license_number' => $licenseNumber,
                        'shift' => $shift,
                        'employment_type' => $employmentType,
                        'joining_date' => '2025-01-01',
                        'qualification' => $qualification,
                        'salary' => $salary,
                        'nid_number' => $nidNumber,
                        'emergency_contact_name' => 'Family Contact',
                        'emergency_contact_phone' => '01711999888',
                        'address' => 'Shohid Minar Mor, Dinajpur Sadar, Dinajpur',
                        'status' => 'active',
                    ]
                );

                // Multi-department allocation
                $syncData = [];
                if ($primaryDept) {
                    $syncData[$primaryDept->id] = [
                        'is_primary' => true,
                        'role_in_department' => 'Primary Department',
                    ];
                }

                foreach ($secondaryCodes as $sCode) {
                    $sDept = Department::where('code', $sCode)->first();
                    if ($sDept && $sDept->id !== $primaryDept?->id) {
                        $syncData[$sDept->id] = [
                            'is_primary' => false,
                            'role_in_department' => 'Cross-Department Coverage',
                        ];
                    }
                }

                if (!empty($syncData)) {
                    $profile->departments()->sync($syncData);
                }

                // Seed Today's 8-hour Shift Schedule if not already scheduled
                $today = now()->toDateString();
                $shiftType = in_array($shift, ['morning', 'evening', 'night']) ? $shift : 'morning';
                $assignedDeptId = $primaryDept?->id ?? Department::where('is_clinical', true)->first()?->id;

                if ($assignedDeptId && in_array($roleName, ['doctor', 'nurse', 'receptionist', 'ot_ward_staff'])) {
                    ShiftSchedule::firstOrCreate(
                        [
                            'staff_profile_id' => $profile->id,
                            'shift_date' => $today,
                            'shift_type' => $shiftType,
                        ],
                        [
                            'department_id' => $assignedDeptId,
                            'start_time' => match ($shiftType) {
                                'morning' => '08:00:00',
                                'evening' => '16:00:00',
                                'night' => '00:00:00',
                            },
                            'end_time' => match ($shiftType) {
                                'morning' => '16:00:00',
                                'evening' => '00:00:00',
                                'night' => '08:00:00',
                            },
                            'status' => 'scheduled',
                            'notes' => 'Regular 8-hour clinical shift assignment',
                            'created_by' => null,
                        ]
                    );
                }
            }
        }
    }
}
