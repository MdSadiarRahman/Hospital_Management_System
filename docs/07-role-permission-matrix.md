# 07. Role-Based Access Control (RBAC) & Permission Matrix
**Meghna Clinic and Nursing Home — Hospital Management System (HMS)**

---

## 1. User Roles Overview
The system implements a granular **Role-Based and Permission-Based Access Control (RBAC)** architecture. Every internal user account has an assigned staff profile and one or more roles. Patient users belong exclusively to the patient realm.

1. **Super Admin (`super_admin`):** Complete system authority, role definition, database backup, security audit log access, system configuration. Universal permission bypass.
2. **Administrator (`administrator`):** Operational supervision, staff profiles, department/bed settings, billing overview, system reports.
3. **Receptionist (`receptionist`):** Patient intake, master registration, doctor appointments, OPD visits, admission intake, bed status inquiries, patient account linking.
4. **Doctor (`doctor`):** Clinical consultations, diagnoses, prescriptions, lab orders, surgical decisions, clinical discharge clearance.
5. **Nurse/Sister (`nurse`):** Bedside vital monitoring, nursing observations, medication administration records (MAR), bed readiness.
6. **Lab Staff (`lab_staff`):** Sample handling, test order processing, partner lab coordination, test report upload and verification.
7. **Billing Staff (`billing_staff`):** Invoice compilation, payment collection, money receipt printing, partial payment records, financial discharge clearance.
8. **OT / Ward Staff (`ot_ward_staff`):** Operation theater prep, bed sanitization, bed status updates, checklist maintenance.
9. **Patient (`patient`):** Viewing personal verified profile, appointments, prescriptions, diagnostic reports, and invoices.

---

## 2. Granular Permission Actions by Module
Permission actions strictly adhere to the six core operational verbs:
- **View:** Read or list records.
- **Create:** Initiate or author new records.
- **Update:** Edit existing records within authorized status windows.
- **Delete:** Soft delete or void records.
- **Approve:** Clinical sign-off, financial clearance, or discount authorization.
- **Print:** Export official printable receipts, prescriptions, test reports, or gate passes.

---

## 3. Master Permission Matrix

| Module / Resource | Action | Super Admin | Administrator | Receptionist | Doctor | Nurse | Lab Staff | Billing Staff | OT/Ward Staff | Patient |
| :--- | :--- | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| **Patients** | View | Full | Full | Full | Full | Full | Full | Full | Full | Own |
| | Create | Yes | Yes | Yes | — | — | — | — | — | Self (Reg) |
| | Update | Yes | Yes | Yes | — | — | — | — | — | Own Profile |
| | Delete | Yes | Yes | — | — | — | — | — | — | — |
| | Print | Yes | Yes | Yes | Yes | Yes | — | Yes | — | Own Card |
| | Link Account | Yes | Yes | Yes | — | — | — | — | — | — |
| **Appointments** | View | Full | Full | Full | Own/Dept | Ward | — | — | — | Own |
| | Create | Yes | Yes | Yes | Yes | — | — | — | — | Request |
| | Update / Reschedule | Yes | Yes | Yes | Yes | — | — | — | — | Cancel Own |
| | Delete / Cancel | Yes | Yes | Yes | Yes | — | — | — | — | — |
| | Approve / Confirm | Yes | Yes | Yes | Yes | — | — | — | — | — |
| | Print Token | Yes | Yes | Yes | Yes | — | — | — | — | Own Token |
| **Visits & Admissions** | View | Full | Full | Full | Full | Full | — | Full | Full | Own |
| | Create Visit | Yes | Yes | Yes | — | — | — | — | — | — |
| | Create Admission | Yes | Yes | Yes | Yes | — | — | — | — | — |
| | Update | Yes | Yes | Yes | Yes | — | — | — | — | — |
| | Approve Discharge | Yes | Yes | — | Clinical | — | — | Financial | — | — |
| | Print Summary | Yes | Yes | Yes | Yes | Yes | — | Yes | — | Own |
| **10 Inpatient Beds** | View | Full | Full | Full | Full | Full | Full | Full | Full | — |
| | Create / Config | Yes | Yes | — | — | — | — | — | — | — |
| | Assign / Transfer | Yes | Yes | Yes | Yes | Yes | — | — | — | — |
| | Status Update (Clean) | Yes | Yes | Yes | — | Yes | — | — | Yes | — |
| **Consultations** | View | Full | Full | — | Full | Ward | — | — | — | Own |
| | Create | Yes | — | — | Yes | — | — | — | — | — |
| | Update | Yes | — | — | Yes (Draft) | — | — | — | — | — |
| | Approve / Sign | Yes | — | — | Yes | — | — | — | — | — |
| | Print Notes | Yes | Yes | — | Yes | Yes | — | — | — | Own |
| **Prescriptions & MAR**| View | Full | Full | — | Full | Full | — | View Items | — | Own |
| | Create Prescription | Yes | — | — | Yes | — | — | — | — | — |
| | Update Prescription | Yes | — | — | Yes (Draft) | — | — | — | — | — |
| | Record Nursing MAR | Yes | — | — | — | Yes | — | — | — | — |
| | Print Rx | Yes | Yes | Yes | Yes | Yes | — | Yes | — | Own |
| **Diagnostic Lab** | View | Full | Full | — | Full | Ward | Full | — | — | Own |
| | Create Order | Yes | Yes | — | Yes | — | — | — | — | — |
| | Collect Sample | Yes | — | — | — | Yes | Yes | — | — | — |
| | Upload / Record Result| Yes | — | — | — | — | Yes | — | — | — |
| | Approve / Verify Report| Yes | — | — | Doctor Sign | — | Lab Tech | — | — | — |
| | Print Report | Yes | Yes | — | Yes | Yes | Yes | — | — | Own |
| **Operation Theater (OT)**| View | Full | Full | — | Full | Ward | — | — | Full | — |
| | Schedule OT | Yes | Yes | — | Yes | — | — | — | — | — |
| | Record Notes | Yes | — | — | Surgeon | — | — | — | — | — |
| | Checklist / Status | Yes | Yes | — | Yes | Yes | — | — | Yes | — |
| **Billing & Payments** | View | Full | Full | — | View Items | — | — | Full | — | Own |
| | Create Invoice | Yes | Yes | — | — | — | — | Yes | — | — |
| | Collect Payment | Yes | Yes | — | — | — | — | Yes | — | — |
| | Approve Discount | Yes | Yes | — | — | — | — | Limited | — | — |
| | Process Refund | Yes | Yes | — | — | — | — | Yes | — | — |
| | Financial Clearance | Yes | Yes | — | — | — | — | Yes | — | — |
| | Print Receipt | Yes | Yes | Yes | — | — | — | Yes | — | Own |
| **Reports & Analytics** | View | Full | Full | — | Dept | Dept | Dept | Financial | — | — |
| | Print / Export | Yes | Yes | — | — | — | — | Financial | — | — |
| **Audit Logs** | View | Full | Full | — | — | — | — | — | — | — |
| **Settings & Roles** | View | Full | Full | — | — | — | — | — | — | — |
| | Manage Settings | Yes | Yes | — | — | — | — | — | — | — |
| | Assign Roles | Yes | Admin Only*| — | — | — | — | — | — | — |

*\*Note: Only Super Admin can assign the Super Admin or Administrator role. Anti-self-escalation policy strictly blocks self-assignment of administrative roles.*

---

## 4. Technical Enforcement in Laravel 12

### A. Route Middleware Protection
```php
// Route protected by single or multiple roles
Route::middleware(['auth', 'staff', 'role:doctor'])->group(...);
Route::middleware(['auth', 'staff', 'role:super_admin,administrator'])->group(...);

// Route protected by granular permissions
Route::middleware(['auth', 'permission:patients.create'])->post('/patients', ...);
Route::middleware(['auth', 'permission:billing.approve'])->post('/bills/{id}/approve', ...);
Route::middleware(['auth', 'permission:laboratory.print'])->get('/lab-reports/{id}/print', ...);
```

### B. Server-Side Gate & Policy Authorization
In `app/Providers/AppServiceProvider.php`, `Gate::before` intercepts authorization:
1. `super_admin` has universal bypass.
2. Checks user's direct or role-inherited permissions via `$user->hasPermission($ability)`.
3. Falls through to dedicated model policies for fine-grained resource rules.

```php
// Controller authorization
$this->authorize('update', $patient);
$this->authorize('assignRole', [$targetUser, 'administrator']);
```

### C. Strict Anti-Self-Escalation Architecture
Implemented in `RoleAssignmentService` and `UserPolicy`:
1. **Self-Escalation Defense:** Under no circumstance can any user assign themselves an administrative role (`super_admin` or `administrator`).
2. **Super Admin Guard:** Only existing `super_admin` accounts can create or promote other administrative accounts.
3. **Audit Trail:** Every role assignment, revocation, and permission change is immutably recorded in `audit_logs`.

### D. Blade Directive Integration
```blade
{{-- Role Check --}}
@role('doctor')
    <a href="/portal/doctor" class="btn btn-primary">Doctor Workstation</a>
@endrole

{{-- Granular Permission Check --}}
@permission('patients.create')
    <button class="btn btn-teal" data-bs-toggle="modal" data-bs-target="#newPatientModal">Register Patient</button>
@endpermission

@permission('prescriptions.print')
    <a href="{{ route('prescriptions.print', $prescription->id) }}" class="btn btn-outline-primary">Print Rx</a>
@endpermission
```
