<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $roles = [
            ['name' => 'super_admin', 'display_name' => 'Super Administrator', 'description' => 'Complete system authority and root settings access', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'administrator', 'display_name' => 'Administrator', 'description' => 'Hospital operational and staff management', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'receptionist', 'display_name' => 'Receptionist', 'description' => 'Patient intake, appointment booking, and visit creation', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'doctor', 'display_name' => 'Doctor / Medical Officer', 'description' => 'Clinical consultations, prescriptions, and discharge clearance', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'nurse', 'display_name' => 'Nurse / Sister', 'description' => 'Inpatient monitoring, vital signs, and medication administration', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'lab_staff', 'display_name' => 'Laboratory Staff', 'description' => 'Diagnostic test orders and report management', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'billing_staff', 'display_name' => 'Billing & Cashier', 'description' => 'Invoicing, payment collection, and financial clearance', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'ot_ward_staff', 'display_name' => 'OT & Ward Staff', 'description' => 'Operation theater prep and bed sanitation', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'patient', 'display_name' => 'Patient', 'description' => 'Self-care portal access for appointments, prescriptions, and reports', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('roles')->insertOrIgnore($roles);

        $permissions = [
            // 1. Patient Records
            ['name' => 'patients.view', 'display_name' => 'View Patients', 'group' => 'patients', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'patients.create', 'display_name' => 'Register Patients', 'group' => 'patients', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'patients.update', 'display_name' => 'Update Patient Details', 'group' => 'patients', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'patients.delete', 'display_name' => 'Delete Patient Record', 'group' => 'patients', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'patients.print', 'display_name' => 'Print Patient ID Card / Summary', 'group' => 'patients', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'patients.link_account', 'display_name' => 'Verify & Link Patient Account', 'group' => 'patients', 'created_at' => $now, 'updated_at' => $now],

            // 2. Appointments
            ['name' => 'appointments.view', 'display_name' => 'View Appointments', 'group' => 'appointments', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'appointments.create', 'display_name' => 'Create Appointment', 'group' => 'appointments', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'appointments.update', 'display_name' => 'Reschedule Appointment', 'group' => 'appointments', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'appointments.delete', 'display_name' => 'Cancel Appointment', 'group' => 'appointments', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'appointments.approve', 'display_name' => 'Confirm / Approve Appointment', 'group' => 'appointments', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'appointments.print', 'display_name' => 'Print Appointment Slip', 'group' => 'appointments', 'created_at' => $now, 'updated_at' => $now],

            // 3. Visits (OPD / Emergency)
            ['name' => 'visits.view', 'display_name' => 'View Visits', 'group' => 'visits', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'visits.create', 'display_name' => 'Check-in Patient / Create Visit', 'group' => 'visits', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'visits.update', 'display_name' => 'Update Visit Information', 'group' => 'visits', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'visits.delete', 'display_name' => 'Cancel Visit', 'group' => 'visits', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'visits.print', 'display_name' => 'Print Visit Token / Receipt', 'group' => 'visits', 'created_at' => $now, 'updated_at' => $now],

            // 4. Inpatient Admissions
            ['name' => 'admissions.view', 'display_name' => 'View Inpatient Admissions', 'group' => 'admissions', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'admissions.create', 'display_name' => 'Admit Inpatient', 'group' => 'admissions', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'admissions.update', 'display_name' => 'Update Admission Record', 'group' => 'admissions', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'admissions.delete', 'display_name' => 'Cancel Admission', 'group' => 'admissions', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'admissions.approve', 'display_name' => 'Approve Admission', 'group' => 'admissions', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'admissions.print', 'display_name' => 'Print Admission Form / Wristband', 'group' => 'admissions', 'created_at' => $now, 'updated_at' => $now],

            // 4b. Departments & Wards
            ['name' => 'departments.view', 'display_name' => 'View Departments & Wards', 'group' => 'departments', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'departments.create', 'display_name' => 'Create New Hospital Department', 'group' => 'departments', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'departments.update', 'display_name' => 'Update Department Details & Status', 'group' => 'departments', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'departments.delete', 'display_name' => 'Decommission Department', 'group' => 'departments', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'departments.manage_staff', 'display_name' => 'Assign & Transfer Department Staff', 'group' => 'departments', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'departments.configure_beds', 'display_name' => 'Configure Department Beds & Tariffs', 'group' => 'departments', 'created_at' => $now, 'updated_at' => $now],

            // 5. Beds & Ward Map
            ['name' => 'beds.view', 'display_name' => 'View Beds & Occupancy', 'group' => 'beds', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'beds.create', 'display_name' => 'Create New Bed', 'group' => 'beds', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'beds.update', 'display_name' => 'Update Bed Details & Tariffs', 'group' => 'beds', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'beds.delete', 'display_name' => 'Decommission Bed', 'group' => 'beds', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'beds.assign', 'display_name' => 'Assign / Transfer Inpatient Bed', 'group' => 'beds', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'beds.status_update', 'display_name' => 'Update Bed Status (Clean/Available)', 'group' => 'beds', 'created_at' => $now, 'updated_at' => $now],

            // 6. Consultations & Clinical Diagnoses
            ['name' => 'consultations.view', 'display_name' => 'View Consultations', 'group' => 'consultations', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'consultations.create', 'display_name' => 'Conduct Consultation', 'group' => 'consultations', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'consultations.update', 'display_name' => 'Update Clinical Notes', 'group' => 'consultations', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'consultations.delete', 'display_name' => 'Delete Consultation', 'group' => 'consultations', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'consultations.approve', 'display_name' => 'Sign Off Clinical Notes', 'group' => 'consultations', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'consultations.print', 'display_name' => 'Print Consultation Summary', 'group' => 'consultations', 'created_at' => $now, 'updated_at' => $now],

            // 7. Prescriptions
            ['name' => 'prescriptions.view', 'display_name' => 'View Prescriptions', 'group' => 'prescriptions', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'prescriptions.create', 'display_name' => 'Author Prescription', 'group' => 'prescriptions', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'prescriptions.update', 'display_name' => 'Update Prescription', 'group' => 'prescriptions', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'prescriptions.delete', 'display_name' => 'Revoke Prescription', 'group' => 'prescriptions', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'prescriptions.approve', 'display_name' => 'Finalize / Sign Prescription', 'group' => 'prescriptions', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'prescriptions.print', 'display_name' => 'Print Official Rx Slip', 'group' => 'prescriptions', 'created_at' => $now, 'updated_at' => $now],

            // 8. Nursing Station & MAR
            ['name' => 'nursing.view', 'display_name' => 'View Nursing Notes & Vitals', 'group' => 'nursing', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'nursing.create', 'display_name' => 'Add Nursing Entry', 'group' => 'nursing', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'nursing.update', 'display_name' => 'Update Nursing Entry', 'group' => 'nursing', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'nursing.delete', 'display_name' => 'Delete Nursing Entry', 'group' => 'nursing', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'nursing.record', 'display_name' => 'Record Vital Signs & Administer Medication (MAR)', 'group' => 'nursing', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'nursing.print', 'display_name' => 'Print Vitals Flowsheet', 'group' => 'nursing', 'created_at' => $now, 'updated_at' => $now],

            // 9. Diagnostic Laboratory
            ['name' => 'lab.view', 'display_name' => 'View Lab Orders & Reports', 'group' => 'laboratory', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'lab.create', 'display_name' => 'Create / Order Lab Test', 'group' => 'laboratory', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'lab.update', 'display_name' => 'Update Lab Order / Sample Status', 'group' => 'laboratory', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'lab.delete', 'display_name' => 'Cancel Lab Order', 'group' => 'laboratory', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'lab.approve', 'display_name' => 'Review & Validate Lab Report', 'group' => 'laboratory', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'lab.upload_report', 'display_name' => 'Upload Secure PDF Report', 'group' => 'laboratory', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'lab.print', 'display_name' => 'Print Diagnostic Report', 'group' => 'laboratory', 'created_at' => $now, 'updated_at' => $now],

            // 10. Operation Theater (OT)
            ['name' => 'ot.view', 'display_name' => 'View OT Schedule', 'group' => 'ot', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'ot.create', 'display_name' => 'Schedule Surgical Procedure', 'group' => 'ot', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'ot.update', 'display_name' => 'Update OT Notes & Times', 'group' => 'ot', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'ot.delete', 'display_name' => 'Cancel Scheduled Surgery', 'group' => 'ot', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'ot.approve', 'display_name' => 'Approve Surgical Booking', 'group' => 'ot', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'ot.print', 'display_name' => 'Print OT Schedule List', 'group' => 'ot', 'created_at' => $now, 'updated_at' => $now],

            // 11. Billing & Financial
            ['name' => 'billing.view', 'display_name' => 'View Invoices & Payments', 'group' => 'billing', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'billing.create', 'display_name' => 'Create Inpatient / OPD Invoice', 'group' => 'billing', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'billing.update', 'display_name' => 'Update Invoice / Apply Discount', 'group' => 'billing', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'billing.delete', 'display_name' => 'Void / Cancel Invoice', 'group' => 'billing', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'billing.approve', 'display_name' => 'Approve Special Discount / Refund', 'group' => 'billing', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'billing.collect', 'display_name' => 'Collect Cash / MFS Payment', 'group' => 'billing', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'billing.print', 'display_name' => 'Print Money Receipt & Final Invoice', 'group' => 'billing', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'billing.financial_clearance', 'display_name' => 'Grant Financial Discharge Clearance', 'group' => 'billing', 'created_at' => $now, 'updated_at' => $now],

            // 12. Discharge Clearance
            ['name' => 'clinical.clearance', 'display_name' => 'Grant Clinical Discharge Clearance', 'group' => 'clinical', 'created_at' => $now, 'updated_at' => $now],

            // 13. Reports & Analytics
            ['name' => 'reports.view', 'display_name' => 'View Financial & Census Reports', 'group' => 'reports', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'reports.print', 'display_name' => 'Export / Print Audit & Financial Reports', 'group' => 'reports', 'created_at' => $now, 'updated_at' => $now],

            // 14. Governance & Auditing
            ['name' => 'audit.view', 'display_name' => 'View Security Audit Logs', 'group' => 'governance', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'audit.print', 'display_name' => 'Export Audit Trails', 'group' => 'governance', 'created_at' => $now, 'updated_at' => $now],

            // 15. System Settings
            ['name' => 'settings.view', 'display_name' => 'View System Settings', 'group' => 'governance', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'settings.update', 'display_name' => 'Update System Settings', 'group' => 'governance', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'settings.manage', 'display_name' => 'Manage System Infrastructure', 'group' => 'governance', 'created_at' => $now, 'updated_at' => $now],

            // 16. User & Role Management
            ['name' => 'users.view', 'display_name' => 'View Users', 'group' => 'governance', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'users.create', 'display_name' => 'Create User', 'group' => 'governance', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'users.update', 'display_name' => 'Update User', 'group' => 'governance', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'users.delete', 'display_name' => 'Deactivate User', 'group' => 'governance', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'users.assign_role', 'display_name' => 'Assign Roles to User', 'group' => 'governance', 'created_at' => $now, 'updated_at' => $now],

            // 17. Staff & Shift Scheduling
            ['name' => 'staff.view', 'display_name' => 'View Staff Directory & Profiles', 'group' => 'staff', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'staff.create', 'display_name' => 'Register New Staff Member', 'group' => 'staff', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'staff.update', 'display_name' => 'Update Staff Profile & Designations', 'group' => 'staff', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'staff.delete', 'display_name' => 'Deactivate Staff Member', 'group' => 'staff', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'staff.view_sensitive', 'display_name' => 'View Sensitive Employment & Salary Data', 'group' => 'staff', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'staff.schedule_shifts', 'display_name' => 'Schedule 8h Nursing & Doctor Shifts', 'group' => 'staff', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('permissions')->insertOrIgnore($permissions);

        // Map Permissions to Roles
        $allPermissions = DB::table('permissions')->pluck('id', 'name');
        $rolesByName = DB::table('roles')->pluck('id', 'name');

        $rolePermissionsMap = [
            // 1. Super Admin: All permissions
            'super_admin' => $allPermissions->keys()->all(),

            // 2. Administrator: All operations, oversight, user assignment
            'administrator' => $allPermissions->keys()->filter(fn($p) => $p !== 'settings.manage')->all(),

            // 3. Receptionist: Patient intake, visits, bed inquiry, printing
            'receptionist' => [
                'patients.view', 'patients.create', 'patients.update', 'patients.print', 'patients.link_account',
                'appointments.view', 'appointments.create', 'appointments.update', 'appointments.delete', 'appointments.approve', 'appointments.print',
                'visits.view', 'visits.create', 'visits.update', 'visits.print',
                'admissions.view', 'admissions.create', 'admissions.print',
                'beds.view', 'beds.assign',
                'billing.view', 'billing.print',
            ],

            // 4. Doctor: Consultations, Prescriptions, Lab orders & approvals, Surgery, Clinical clearance
            'doctor' => [
                'patients.view',
                'appointments.view', 'appointments.approve',
                'visits.view',
                'admissions.view', 'admissions.approve',
                'beds.view', 'beds.assign',
                'consultations.view', 'consultations.create', 'consultations.update', 'consultations.approve', 'consultations.print',
                'prescriptions.view', 'prescriptions.create', 'prescriptions.update', 'prescriptions.approve', 'prescriptions.print',
                'nursing.view',
                'lab.view', 'lab.create', 'lab.approve', 'lab.print',
                'ot.view', 'ot.create', 'ot.update', 'ot.approve', 'ot.print',
                'clinical.clearance',
            ],

            // 5. Nurse: 10 beds, vital monitoring, MAR administration, shift notes
            'nurse' => [
                'patients.view',
                'visits.view',
                'admissions.view',
                'beds.view', 'beds.assign', 'beds.status_update',
                'nursing.view', 'nursing.create', 'nursing.update', 'nursing.record', 'nursing.print',
                'prescriptions.view',
                'lab.view', 'lab.upload_report',
            ],

            // 6. Lab Staff: Diagnostic testing, barcode samples, report uploads
            'lab_staff' => [
                'patients.view',
                'lab.view', 'lab.create', 'lab.update', 'lab.delete', 'lab.approve', 'lab.upload_report', 'lab.print',
                'prescriptions.view',
            ],

            // 7. Billing Staff: Invoices, payment collection, refunds, financial discharge clearance
            'billing_staff' => [
                'patients.view',
                'admissions.view',
                'billing.view', 'billing.create', 'billing.update', 'billing.delete', 'billing.approve', 'billing.collect', 'billing.print', 'billing.financial_clearance',
                'reports.view', 'reports.print',
            ],

            // 8. OT & Ward Staff: Bed cleaning & sanitation, OT prep
            'ot_ward_staff' => [
                'beds.view', 'beds.status_update',
                'admissions.view',
                'ot.view', 'ot.update',
            ],

            // 9. Patient: Self-care view of own records and appointment requests
            'patient' => [
                'appointments.view', 'appointments.create',
                'prescriptions.view', 'prescriptions.print',
                'lab.view', 'lab.print',
                'billing.view', 'billing.print',
            ],
        ];

        // Clear existing permission_role bindings to re-seed cleanly
        DB::table('permission_role')->truncate();

        $pivotRows = [];
        foreach ($rolePermissionsMap as $roleName => $permNames) {
            if (isset($rolesByName[$roleName])) {
                $roleId = $rolesByName[$roleName];
                foreach ($permNames as $pName) {
                    if (isset($allPermissions[$pName])) {
                        $pivotRows[] = [
                            'role_id' => $roleId,
                            'permission_id' => $allPermissions[$pName],
                        ];
                    }
                }
            }
        }

        DB::table('permission_role')->insert($pivotRows);
    }
}
