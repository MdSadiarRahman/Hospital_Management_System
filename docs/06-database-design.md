# 06. Detailed Database Schema Design & Data Dictionary
**Meghna Clinic and Nursing Home — Hospital Management System (HMS)**

---

## 1. Architecture & Integrity Principles
- **Engine & Collation:** MySQL InnoDB with `utf8mb4_unicode_ci` across all tables.
- **Precision:** All monetary and charge values use `DECIMAL(12,2)` (no floating point arithmetic).
- **Relational Normalization:** 3NF compliant with strict separation across all 24 logical domains.
- **Concurrency & Double-Booking Protection:**
  - Bed assignments maintain a conditional unique status using pessimistic row locking (`lockForUpdate`).
  - Active bed assignments are indexed on `(bed_id, released_at)`.
- **Clearance Separation:** Clinical clearance and Financial clearance exist as independent, auditable authorization gates on inpatient admissions.

---

## 2. Complete Schema Specifications Across 24 Logical Domains

### Domain 1: Users & Authentication
#### Table: `users`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | User identifier |
| `name` | `VARCHAR(191)` | | No | | Full name |
| `email` | `VARCHAR(191)` | `UNIQUE` | No | | Email login credential |
| `phone` | `VARCHAR(20)` | `INDEX` | Yes | `NULL` | Primary contact number |
| `password` | `VARCHAR(255)` | | No | | Bcrypt hashed password |
| `user_type` | `ENUM('staff','patient')` | `INDEX` | No | `'staff'` | User portal realm |
| `is_active` | `BOOLEAN` | `INDEX` | No | `TRUE` | Account active status |
| `remember_token` | `VARCHAR(100)` | | Yes | `NULL` | Remember me token |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Standard timestamps |

#### Table: `password_reset_tokens`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `email` | `VARCHAR(191)` | `PK` | No | | Target email address |
| `token` | `VARCHAR(255)` | | No | | Reset hash |
| `created_at` | `TIMESTAMP` | | Yes | `NULL` | Generation time |

#### Table: `sessions`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `VARCHAR(191)` | `PK` | No | | Session identifier |
| `user_id` | `BIGINT UNSIGNED` | `FK -> users.id, INDEX` | Yes | `NULL` | Authenticated user ID |
| `ip_address` | `VARCHAR(45)` | | Yes | `NULL` | Origin IP |
| `user_agent` | `TEXT` | | Yes | `NULL` | Browser client string |
| `payload` | `LONGTEXT` | | No | | Serialized session data |
| `last_activity` | `INT` | `INDEX` | No | | Timestamp of last action |

---

### Domain 2: Roles & Permissions
#### Table: `roles`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Role ID |
| `name` | `VARCHAR(50)` | `UNIQUE` | No | | Slug (e.g. `super_admin`, `doctor`, `nurse`) |
| `display_name` | `VARCHAR(100)` | | No | | Human readable label |
| `description` | `TEXT` | | Yes | `NULL` | Role purpose |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |

#### Table: `permissions`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Permission ID |
| `name` | `VARCHAR(100)` | `UNIQUE` | No | | Slug (e.g. `beds.assign`, `prescriptions.author`) |
| `display_name` | `VARCHAR(100)` | | No | | Label |
| `group` | `VARCHAR(50)` | `INDEX` | No | | Group (e.g. `clinical`, `billing`, `reception`) |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |

#### Table: `role_user`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `user_id` | `BIGINT UNSIGNED` | `FK -> users.id, CASCADE` | No | | References users |
| `role_id` | `BIGINT UNSIGNED` | `FK -> roles.id, CASCADE` | No | | References roles |
| *Composite PK:* `(user_id, role_id)`

#### Table: `permission_role`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `permission_id` | `BIGINT UNSIGNED` | `FK -> permissions.id, CASCADE` | No | | References permissions |
| `role_id` | `BIGINT UNSIGNED` | `FK -> roles.id, CASCADE` | No | | References roles |
| *Composite PK:* `(permission_id, role_id)`

---

### Domain 3: Departments & Staff
#### Table: `departments`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Department ID |
| `name` | `VARCHAR(100)` | `UNIQUE` | No | | Gynecology, Orthopedic, Surgery, Emergency |
| `code` | `VARCHAR(10)` | `UNIQUE` | No | | GYN, ORTHO, SURG, EMERG |
| `description` | `TEXT` | | Yes | `NULL` | Specialty scope |
| `is_clinical` | `BOOLEAN` | `INDEX` | No | `TRUE` | Clinical vs administrative unit |
| `is_active` | `BOOLEAN` | `INDEX` | No | `TRUE` | Active status |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |

#### Table: `staff_profiles`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Profile ID |
| `user_id` | `BIGINT UNSIGNED` | `FK -> users.id, UNIQUE, CASCADE` | No | | 1-to-1 User binding |
| `department_id` | `BIGINT UNSIGNED` | `FK -> departments.id, RESTRICT` | Yes | `NULL` | Assigned department |
| `designation` | `VARCHAR(100)` | | No | | e.g. Senior Medical Officer, Staff Nurse |
| `specialization` | `VARCHAR(191)` | | Yes | `NULL` | e.g. Obstetrician, Orthopedic Surgeon |
| `license_number` | `VARCHAR(50)` | `UNIQUE` | Yes | `NULL` | BMDC or Nursing registration number |
| `shift` | `ENUM('morning','evening','night','rotational')` | | No | `'rotational'` | Shift designation (8h shifts) |
| `status` | `ENUM('active','on_leave','inactive')` | `INDEX` | No | `'active'` | Employment state |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |

---

### Domain 4: Patients & Patient Contacts
#### Table: `patients`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Master Patient ID |
| `patient_code` | `VARCHAR(32)` | `UNIQUE` | No | | Unique Code (`MCNH-YYYY-XXXX`) |
| `user_id` | `BIGINT UNSIGNED` | `FK -> users.id, UNIQUE, SET NULL` | Yes | `NULL` | Bound patient portal account |
| `name` | `VARCHAR(191)` | `INDEX` | No | | Full legal name |
| `date_of_birth` | `DATE` | `INDEX` | No | | Birth date |
| `gender` | `ENUM('male','female','other')` | | No | | Biological sex |
| `blood_group` | `ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-')` | | Yes | `NULL` | Blood group |
| `phone` | `VARCHAR(20)` | `INDEX` | No | | Primary mobile number |
| `nid_or_birth_cert`| `VARCHAR(30)` | `INDEX` | Yes | `NULL` | National ID / Birth Certificate |
| `address` | `TEXT` | | Yes | `NULL` | Permanent/Present address |
| `is_verified` | `BOOLEAN` | `INDEX` | No | `FALSE` | Identity verified for clinical linking |
| `verified_at` | `DATETIME` | | Yes | `NULL` | Linking verification timestamp |
| `verified_by` | `BIGINT UNSIGNED` | `FK -> users.id, SET NULL` | Yes | `NULL` | Staff who performed identity verification |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |

#### Table: `patient_contacts`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Contact ID |
| `patient_id` | `BIGINT UNSIGNED` | `FK -> patients.id, CASCADE` | No | | References patient |
| `name` | `VARCHAR(191)` | | No | | Next of kin name |
| `relationship` | `VARCHAR(50)` | | No | | Father, Mother, Spouse, Guardian |
| `phone` | `VARCHAR(20)` | | No | | Emergency contact number |
| `is_primary` | `BOOLEAN` | | No | `TRUE` | Primary contact flag |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |

---

### Domain 5: Appointments & Visits
#### Table: `appointments`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Appointment ID |
| `patient_id` | `BIGINT UNSIGNED` | `FK -> patients.id, SET NULL` | Yes | `NULL` | Registered patient ID |
| `department_id` | `BIGINT UNSIGNED` | `FK -> departments.id, RESTRICT`| No | | Clinical department requested |
| `doctor_id` | `BIGINT UNSIGNED` | `FK -> users.id, SET NULL` | Yes | `NULL` | Assigned attending doctor |
| `guest_name` | `VARCHAR(191)` | | Yes | `NULL` | For public online requests |
| `guest_phone` | `VARCHAR(20)` | | Yes | `NULL` | For public online requests |
| `appointment_date`| `DATETIME` | `INDEX` | No | | Scheduled consultation time |
| `serial_number` | `INT UNSIGNED` | | Yes | `NULL` | Daily serial slot |
| `type` | `ENUM('online','walk_in','follow_up')` | | No | `'walk_in'` | Booking origin |
| `status` | `ENUM('pending','confirmed','completed','cancelled')` | `INDEX` | No | `'pending'` | Intake status |
| `reason` | `TEXT` | | Yes | `NULL` | Reason / complaints |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |

#### Table: `visits`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Visit record ID |
| `patient_id` | `BIGINT UNSIGNED` | `FK -> patients.id, RESTRICT` | No | | References patient |
| `appointment_id` | `BIGINT UNSIGNED` | `FK -> appointments.id, SET NULL`| Yes | `NULL` | Source appointment (if any) |
| `visit_number` | `VARCHAR(32)` | `UNIQUE` | No | | `VIS-YYYYMMDD-XXX` |
| `visit_date` | `DATETIME` | `INDEX` | No | | Check-in timestamp |
| `visit_type` | `ENUM('regular','emergency','follow_up')` | `INDEX` | No | `'regular'` | Clinical triage type |
| `triage_level` | `ENUM('normal','priority','critical')` | | No | `'normal'` | Acuity level |
| `status` | `ENUM('waiting','in_consultation','completed','admitted','referred_out')` | `INDEX` | No | `'waiting'` | Episode state |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |

---

### Domain 6: Admissions
#### Table: `admissions`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Inpatient Admission ID |
| `visit_id` | `BIGINT UNSIGNED` | `FK -> visits.id, RESTRICT` | No | | Originating clinical visit |
| `patient_id` | `BIGINT UNSIGNED` | `FK -> patients.id, RESTRICT` | No | | Direct patient index |
| `admission_number`| `VARCHAR(32)` | `UNIQUE` | No | | `ADM-YYYYMMDD-XXX` |
| `admission_date` | `DATETIME` | `INDEX` | No | | Intake timestamp |
| `discharge_date` | `DATETIME` | | Yes | `NULL` | Departure timestamp |
| `attending_doctor_id`| `BIGINT UNSIGNED` | `FK -> users.id, RESTRICT` | No | | In-charge Doctor |
| `admission_reason`| `TEXT` | | No | | Clinical diagnosis & indications |
| `clinical_clearance`| `BOOLEAN` | `INDEX` | No | `FALSE` | Granted by Doctor |
| `clinical_clearance_at`| `DATETIME` | | Yes | `NULL` | Timestamp of doctor approval |
| `clinical_clearance_by`| `BIGINT UNSIGNED`| `FK -> users.id, SET NULL` | Yes | `NULL` | Doctor user ID |
| `financial_clearance`| `BOOLEAN` | `INDEX` | No | `FALSE` | Granted by Cashier / Billing |
| `financial_clearance_at`| `DATETIME` | | Yes | `NULL` | Timestamp of bill settlement |
| `financial_clearance_by`| `BIGINT UNSIGNED`| `FK -> users.id, SET NULL` | Yes | `NULL` | Billing staff user ID |
| `status` | `ENUM('admitted','discharged','referred_external')` | `INDEX` | No | `'admitted'` | Active stay status |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |

---

### Domain 7: Beds & Bed Assignments
#### Table: `beds` (10 Configurable Beds)
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Bed ID |
| `department_id` | `BIGINT UNSIGNED` | `FK -> departments.id, RESTRICT`| No | | Department unit |
| `bed_number` | `VARCHAR(32)` | `UNIQUE` | No | | Code (e.g. `EM-01`, `GYN-01`) |
| `category` | `ENUM('general','department','cabin','post_op')` | | No | `'department'` | Bed category |
| `daily_charge` | `DECIMAL(12,2)` | | No | `0.00` | Rate per 24 hours |
| `status` | `ENUM('available','occupied','reserved','cleaning','maintenance')` | `INDEX` | No | `'available'` | Real-time status |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |

#### Table: `bed_assignments`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Assignment ID |
| `admission_id` | `BIGINT UNSIGNED` | `FK -> admissions.id, CASCADE` | No | | References admission |
| `bed_id` | `BIGINT UNSIGNED` | `FK -> beds.id, RESTRICT` | No | | References bed |
| `assigned_at` | `DATETIME` | `INDEX` | No | | Check-in to bed |
| `released_at` | `DATETIME` | `INDEX` | Yes | `NULL` | `NULL` while currently occupied |
| `transfer_reason` | `TEXT` | | Yes | `NULL` | Reason if moved between beds |
| `assigned_by` | `BIGINT UNSIGNED` | `FK -> users.id, SET NULL` | Yes | `NULL` | Staff executing allocation |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |
| *Composite Index:* `(bed_id, released_at)` | Enforces single active occupant validation |

---

### Domain 8: Consultations & Diagnoses
#### Table: `consultations`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Consultation ID |
| `visit_id` | `BIGINT UNSIGNED` | `FK -> visits.id, CASCADE` | No | | References visit episode |
| `doctor_id` | `BIGINT UNSIGNED` | `FK -> users.id, RESTRICT` | No | | Attending doctor |
| `chief_complaints`| `TEXT` | | No | | Presenting symptoms |
| `clinical_notes` | `TEXT` | | Yes | `NULL` | Physical exam notes |
| `status` | `ENUM('draft','finalized')` | `INDEX` | No | `'draft'` | Clinical state |
| `finalized_at` | `DATETIME` | | Yes | `NULL` | Timestamp of completion |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |

#### Table: `diagnoses`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Diagnosis ID |
| `consultation_id` | `BIGINT UNSIGNED` | `FK -> consultations.id, CASCADE`| No | | References consultation |
| `condition_name` | `VARCHAR(191)` | `INDEX` | No | | Disease / condition name |
| `icd_code` | `VARCHAR(20)` | `INDEX` | Yes | `NULL` | ICD-10/11 coding |
| `diagnosis_type` | `ENUM('provisional','final')` | | No | `'provisional'` | Clinical confidence |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |

---

### Domain 9: Prescriptions & Prescription Items
#### Table: `prescriptions`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Prescription header ID |
| `consultation_id` | `BIGINT UNSIGNED` | `FK -> consultations.id, CASCADE`| No | | References consultation |
| `advice` | `TEXT` | | Yes | `NULL` | Dietary & general instructions |
| `follow_up_date` | `DATE` | | Yes | `NULL` | Recommended next visit |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |

#### Table: `prescription_items`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Line item ID |
| `prescription_id` | `BIGINT UNSIGNED` | `FK -> prescriptions.id, CASCADE`| No | | References prescription |
| `medicine_name` | `VARCHAR(191)` | `INDEX` | No | | Generic or brand name |
| `dosage` | `VARCHAR(50)` | | No | | e.g. `500 mg`, `10 ml` |
| `frequency` | `VARCHAR(50)` | | No | | e.g. `1+0+1`, `Every 8 hours` |
| `duration` | `VARCHAR(50)` | | No | | e.g. `7 days`, `Continue` |
| `instructions` | `VARCHAR(191)` | | Yes | `NULL` | e.g. `Before meals`, `With water`|
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |

---

### Domain 10: Treatment Records
#### Table: `treatment_plans`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Plan ID |
| `admission_id` | `BIGINT UNSIGNED` | `FK -> admissions.id, CASCADE` | No | | Target inpatient stay |
| `doctor_id` | `BIGINT UNSIGNED` | `FK -> users.id, RESTRICT` | No | | Authoring doctor |
| `plan_title` | `VARCHAR(191)` | | No | | Short description of plan |
| `details` | `TEXT` | | No | | Comprehensive instructions |
| `status` | `ENUM('active','completed','discontinued')` | `INDEX` | No | `'active'` | Progress state |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |

#### Table: `treatment_records`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Record ID |
| `treatment_plan_id`| `BIGINT UNSIGNED` | `FK -> treatment_plans.id, CASCADE`| No | | References plan |
| `performed_by` | `BIGINT UNSIGNED` | `FK -> users.id, RESTRICT` | No | | Nurse / Doctor executing |
| `procedure_performed`| `VARCHAR(191)`| | No | | e.g. Wound dressing, Catheterization |
| `outcome_notes` | `TEXT` | | Yes | `NULL` | Clinical observations post-procedure |
| `performed_at` | `DATETIME` | `INDEX` | No | | Execution time |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |

---

### Domain 11: Vital Signs & Nursing Notes
#### Table: `nursing_notes`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Note ID |
| `admission_id` | `BIGINT UNSIGNED` | `FK -> admissions.id, CASCADE` | No | | References admission |
| `nurse_id` | `BIGINT UNSIGNED` | `FK -> users.id, RESTRICT` | No | | Staff recording |
| `shift` | `ENUM('morning','evening','night')` | | No | `'morning'` | 8-hour shift indicator |
| `temperature` | `DECIMAL(4,1)` | | Yes | `NULL` | Body temperature (°F) |
| `blood_pressure` | `VARCHAR(15)` | | Yes | `NULL` | Systolic/Diastolic (e.g. `120/80`) |
| `pulse_rate` | `SMALLINT UNSIGNED`| | Yes | `NULL` | Heart rate (BPM) |
| `respiratory_rate`| `SMALLINT UNSIGNED`| | Yes | `NULL` | Breaths per minute |
| `spo2` | `DECIMAL(4,1)` | | Yes | `NULL` | Oxygen saturation (%) |
| `observations` | `TEXT` | | No | | Bedside condition notes |
| `recorded_at` | `DATETIME` | `INDEX` | No | | Measurement timestamp |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |

---

### Domain 12: Medication Administration (MAR)
#### Table: `medication_administrations`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Administration event ID |
| `admission_id` | `BIGINT UNSIGNED` | `FK -> admissions.id, CASCADE` | No | | Inpatient stay |
| `prescription_item_id`| `BIGINT UNSIGNED`| `FK -> prescription_items.id, SET NULL`| Yes | `NULL` | Linked prescription line |
| `medicine_name` | `VARCHAR(191)` | | No | | Name of medication given |
| `dose_given` | `VARCHAR(50)` | | No | | Dose administered |
| `nurse_id` | `BIGINT UNSIGNED` | `FK -> users.id, RESTRICT` | No | | Attending nurse |
| `status` | `ENUM('administered','omitted','refused')` | `INDEX` | No | `'administered'` | MAR execution state |
| `remarks` | `TEXT` | | Yes | `NULL` | Reason if omitted or patient reaction |
| `administered_at` | `DATETIME` | `INDEX` | No | | Exact time dose was delivered |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |

---

### Domain 13 & 14: Laboratory Tests, Orders & External Partners
#### Table: `lab_partners` (External Diagnostic Centers)
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Partner ID |
| `name` | `VARCHAR(191)` | `UNIQUE` | No | | Center name (e.g. Popular Diagnostic) |
| `code` | `VARCHAR(20)` | `UNIQUE` | No | | Partner code |
| `contact_person` | `VARCHAR(100)` | | Yes | `NULL` | Focal contact |
| `phone` | `VARCHAR(20)` | | No | | Contact number |
| `address` | `TEXT` | | Yes | `NULL` | Facility location in Dinajpur |
| `is_active` | `BOOLEAN` | `INDEX` | No | `TRUE` | Partnership state |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |

#### Table: `lab_test_catalog`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Catalog ID |
| `name` | `VARCHAR(191)` | `INDEX` | No | | Test name (e.g. CBC, Lipid Profile) |
| `code` | `VARCHAR(20)` | `UNIQUE` | No | | Test code |
| `category` | `VARCHAR(50)` | `INDEX` | No | | Hematology, Biochemistry, Imaging |
| `standard_charge`| `DECIMAL(12,2)` | | No | `0.00` | Standard billing price |
| `default_source` | `ENUM('internal','external')` | | No | `'internal'` | In-house vs outsourced |
| `is_active` | `BOOLEAN` | `INDEX` | No | `TRUE` | Test active in catalog |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |

#### Table: `lab_orders`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Order ID |
| `consultation_id` | `BIGINT UNSIGNED` | `FK -> consultations.id, CASCADE`| No | | Ordering encounter |
| `patient_id` | `BIGINT UNSIGNED` | `FK -> patients.id, RESTRICT` | No | | Patient index |
| `order_number` | `VARCHAR(32)` | `UNIQUE` | No | | `LBO-YYYYMMDD-XXX` |
| `source` | `ENUM('internal','external')` | `INDEX` | No | `'internal'` | Processing channel |
| `lab_partner_id` | `BIGINT UNSIGNED` | `FK -> lab_partners.id, SET NULL`| Yes | `NULL` | Partner if outsourced |
| `status` | `ENUM('pending','sample_collected','processing','completed','cancelled')` | `INDEX` | No | `'pending'` | Lifecycle state |
| `clinical_indication`| `TEXT` | | Yes | `NULL` | Reason for test |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |

#### Table: `lab_order_items`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Item ID |
| `lab_order_id` | `BIGINT UNSIGNED` | `FK -> lab_orders.id, CASCADE` | No | | Grouped order |
| `lab_test_id` | `BIGINT UNSIGNED` | `FK -> lab_test_catalog.id, RESTRICT`| No | | Target test |
| `test_name` | `VARCHAR(191)` | | No | | Snapshot of test name |
| `test_charge` | `DECIMAL(12,2)` | | No | `0.00` | Billed charge |
| `status` | `ENUM('pending','sample_collected','completed','cancelled')` | `INDEX` | No | `'pending'` | Test status |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |

---

### Domain 15: Laboratory Samples & Reports
#### Table: `lab_samples`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Sample ID |
| `lab_order_id` | `BIGINT UNSIGNED` | `FK -> lab_orders.id, CASCADE` | No | | Target order |
| `sample_type` | `VARCHAR(50)` | | No | | Blood, Urine, Sputum, Tissue |
| `barcode` | `VARCHAR(50)` | `UNIQUE` | No | | Physical vial barcode |
| `collected_at` | `DATETIME` | | No | | Collection timestamp |
| `collected_by` | `BIGINT UNSIGNED` | `FK -> users.id, RESTRICT` | No | | Phlebotomist / Lab Staff |
| `status` | `ENUM('collected','received','rejected')` | | No | `'collected'` | Sample status |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |

#### Table: `lab_reports`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Report ID |
| `lab_order_item_id`| `BIGINT UNSIGNED` | `FK -> lab_order_items.id, UNIQUE, CASCADE`| No | | 1-to-1 Item result |
| `file_path` | `VARCHAR(255)` | | No | | Path in secure non-public storage |
| `findings_summary`| `TEXT` | | Yes | `NULL` | Clinical synopsis |
| `parameters` | `JSON` | | Yes | `NULL` | Numerical metrics (e.g. Hb, WBC) |
| `review_status` | `ENUM('pending_review','reviewed')` | `INDEX` | No | `'pending_review'` | Doctor validation status |
| `reviewed_by` | `BIGINT UNSIGNED` | `FK -> users.id, SET NULL` | Yes | `NULL` | Reviewing doctor |
| `reviewed_at` | `DATETIME` | | Yes | `NULL` | Verification timestamp |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |

---

### Domain 16: Operation Records (OT)
#### Table: `operations`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Operation ID |
| `admission_id` | `BIGINT UNSIGNED` | `FK -> admissions.id, RESTRICT` | No | | Inpatient stay |
| `procedure_name` | `VARCHAR(191)` | `INDEX` | No | | Name of surgery |
| `scheduled_at` | `DATETIME` | `INDEX` | No | | OT scheduled time |
| `started_at` | `DATETIME` | | Yes | `NULL` | Surgery start time |
| `ended_at` | `DATETIME` | | Yes | `NULL` | Surgery completion time |
| `primary_surgeon_id`| `BIGINT UNSIGNED`| `FK -> users.id, RESTRICT` | No | | Lead surgeon |
| `assistant_surgeon_id`| `BIGINT UNSIGNED`| `FK -> users.id, SET NULL`| Yes | `NULL` | Assistant surgeon |
| `anesthetist_name`| `VARCHAR(191)` | | Yes | `NULL` | Anesthetist |
| `operation_notes` | `LONGTEXT` | | Yes | `NULL` | Surgical notes & findings |
| `post_op_notes` | `TEXT` | | Yes | `NULL` | Immediate recovery instructions |
| `status` | `ENUM('scheduled','in_progress','completed','cancelled')` | `INDEX` | No | `'scheduled'` | OT status |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |

---

### Domain 17: Referrals
#### Table: `referrals`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Referral ID |
| `visit_id` | `BIGINT UNSIGNED` | `FK -> visits.id, SET NULL` | Yes | `NULL` | Originating visit |
| `admission_id` | `BIGINT UNSIGNED` | `FK -> admissions.id, SET NULL` | Yes | `NULL` | Originating admission |
| `referral_type` | `ENUM('icu_ccu_emergency','specialist','tertiary_transfer')` | `INDEX` | No | `'icu_ccu_emergency'` | Clinical urgency |
| `facility_name` | `VARCHAR(191)` | | No | | e.g. M. Abdur Rahim Medical College |
| `clinical_reason`| `TEXT` | | No | | Why clinic cannot treat (e.g. ICU need) |
| `transport_arranged`| `BOOLEAN` | | No | `FALSE` | Ambulance arranged |
| `status` | `ENUM('initiated','transferred','completed')` | | No | `'initiated'` | Referral progress |
| `authorized_by` | `BIGINT UNSIGNED` | `FK -> users.id, RESTRICT` | No | | Attending doctor |
| `referred_at` | `DATETIME` | `INDEX` | No | | Referral timestamp |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |

---

### Domain 18: Billing & Bill Items
#### Table: `bills`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Invoice ID |
| `admission_id` | `BIGINT UNSIGNED` | `FK -> admissions.id, SET NULL` | Yes | `NULL` | For Inpatient billing |
| `visit_id` | `BIGINT UNSIGNED` | `FK -> visits.id, SET NULL` | Yes | `NULL` | For Outpatient billing |
| `invoice_number` | `VARCHAR(32)` | `UNIQUE` | No | | `INV-YYYYMMDD-XXX` |
| `subtotal_amount`| `DECIMAL(12,2)` | | No | `0.00` | Sum of raw items |
| `discount_amount`| `DECIMAL(12,2)` | | No | `0.00` | Approved discount |
| `discount_reason`| `VARCHAR(191)` | | Yes | `NULL` | Justification |
| `total_amount` | `DECIMAL(12,2)` | | No | `0.00` | Net payable amount |
| `paid_amount` | `DECIMAL(12,2)` | | No | `0.00` | Total collected so far |
| `due_amount` | `DECIMAL(12,2)` | `INDEX` | No | `0.00` | Outstanding balance |
| `status` | `ENUM('unpaid','partial','paid')` | `INDEX` | No | `'unpaid'` | Billing settlement state |
| `created_by` | `BIGINT UNSIGNED` | `FK -> users.id, RESTRICT` | No | | Billing clerk |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |

#### Table: `bill_items`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Item ID |
| `bill_id` | `BIGINT UNSIGNED` | `FK -> bills.id, CASCADE` | No | | Linked invoice |
| `item_type` | `ENUM('bed','consultation','lab','surgery','service')` | `INDEX` | No | | Charge classification |
| `item_name` | `VARCHAR(191)` | | No | | Description (e.g. Bed EM-01 x 2 Days) |
| `unit_price` | `DECIMAL(12,2)` | | No | `0.00` | Single unit rate |
| `quantity` | `INT UNSIGNED` | | No | `1` | Units consumed |
| `total_price` | `DECIMAL(12,2)` | | No | `0.00` | `unit_price * quantity` |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |

---

### Domain 19: Payments & Refunds
#### Table: `payments`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Payment ID |
| `bill_id` | `BIGINT UNSIGNED` | `FK -> bills.id, RESTRICT` | No | | Target invoice |
| `receipt_number` | `VARCHAR(32)` | `UNIQUE` | No | | `REC-YYYYMMDD-XXX` |
| `amount` | `DECIMAL(12,2)` | | No | | Payment amount |
| `payment_method` | `ENUM('cash','card','mobile_banking')`| | No | `'cash'` | Tender type |
| `transaction_ref`| `VARCHAR(100)` | | Yes | `NULL` | bKash/Nagad/Card TxID |
| `received_by` | `BIGINT UNSIGNED` | `FK -> users.id, RESTRICT` | No | | Cashier ID |
| `status` | `ENUM('success','refunded')` | `INDEX` | No | `'success'` | Voucher status |
| `payment_date` | `DATETIME` | `INDEX` | No | | Collection timestamp |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |

#### Table: `refunds`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Refund ID |
| `payment_id` | `BIGINT UNSIGNED` | `FK -> payments.id, RESTRICT` | No | | Original payment voucher |
| `refund_voucher` | `VARCHAR(32)` | `UNIQUE` | No | | `RFD-YYYYMMDD-XXX` |
| `amount` | `DECIMAL(12,2)` | | No | | Amount reimbursed |
| `reason` | `TEXT` | | No | | Audit justification |
| `approved_by` | `BIGINT UNSIGNED` | `FK -> users.id, RESTRICT` | No | | Authorizing administrator |
| `refunded_at` | `DATETIME` | | No | | Timestamp of refund |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |

---

### Domain 20: Discharge Summaries
#### Table: `discharge_summaries`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Summary ID |
| `admission_id` | `BIGINT UNSIGNED` | `FK -> admissions.id, UNIQUE, CASCADE`| No | | Strict 1-to-1 with admission |
| `discharge_diagnosis`| `TEXT` | | No | | Final condition |
| `treatment_given`| `LONGTEXT` | | No | | Narrative of therapeutic procedures |
| `condition_at_discharge`| `ENUM('cured','improved','referred','dor')` | | No | `'improved'` | Health status upon release |
| `discharge_medications`| `LONGTEXT` | | No | | Home medications and dosage |
| `follow_up_instructions`| `TEXT` | | Yes | `NULL` | Rest, diet, next visit |
| `authorized_by` | `BIGINT UNSIGNED` | `FK -> users.id, RESTRICT` | No | | Attending doctor |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |

---

### Domain 21: Notifications
#### Table: `system_notifications`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Notification ID |
| `user_id` | `BIGINT UNSIGNED` | `FK -> users.id, CASCADE` | No | | Target recipient |
| `title` | `VARCHAR(191)` | | No | | Heading |
| `message` | `TEXT` | | No | | Alert body |
| `type` | `ENUM('appointment','lab_ready','bed_alert','billing','system')`| `INDEX` | No | `'system'` | Classification |
| `action_url` | `VARCHAR(255)` | | Yes | `NULL` | Deep link to record |
| `is_read` | `BOOLEAN` | `INDEX` | No | `FALSE` | Read receipt |
| `read_at` | `DATETIME` | | Yes | `NULL` | Read timestamp |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |

---

### Domain 22: Attachments (Polymorphic Media)
#### Table: `attachments`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Attachment ID |
| `attachable_type`| `VARCHAR(100)` | `INDEX` | No | | Model class (Patient, LabReport, etc.) |
| `attachable_id` | `BIGINT UNSIGNED` | `INDEX` | No | | Primary key of model |
| `file_name` | `VARCHAR(191)` | | No | | Original filename |
| `file_path` | `VARCHAR(255)` | | No | | Secure storage path |
| `mime_type` | `VARCHAR(100)` | | No | | e.g. `application/pdf`, `image/jpeg` |
| `file_size` | `INT UNSIGNED` | | No | | Size in bytes |
| `uploaded_by` | `BIGINT UNSIGNED` | `FK -> users.id, SET NULL` | Yes | `NULL` | Uploader ID |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |
| *Composite Index:* `(attachable_type, attachable_id)` | Fast polymorphic queries |

---

### Domain 23: Audit Logs
#### Table: `audit_logs`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Log ID |
| `user_id` | `BIGINT UNSIGNED` | `FK -> users.id, SET NULL` | Yes | `NULL` | Actor who performed event |
| `event` | `VARCHAR(50)` | `INDEX` | No | | `created`, `updated`, `deleted`, `login` |
| `auditable_type` | `VARCHAR(100)` | `INDEX` | Yes | `NULL` | Target Model |
| `auditable_id` | `BIGINT UNSIGNED` | `INDEX` | Yes | `NULL` | Target Model ID |
| `old_values` | `JSON` | | Yes | `NULL` | State prior to modification |
| `new_values` | `JSON` | | Yes | `NULL` | State after modification |
| `ip_address` | `VARCHAR(45)` | | Yes | `NULL` | Request IP |
| `user_agent` | `TEXT` | | Yes | `NULL` | Browser client info |
| `created_at` | `TIMESTAMP` | `INDEX` | No | `CURRENT_TIMESTAMP` | Event timestamp |

---

### Domain 24: System Settings
#### Table: `system_settings`
| Field | Type | Constraint | Nullable | Default | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | `PK, AUTO_INCREMENT` | No | | Setting ID |
| `key` | `VARCHAR(100)` | `UNIQUE` | No | | Config Key (e.g. `clinic_name`, `bed_billing_cycle`) |
| `value` | `LONGTEXT` | | Yes | `NULL` | Stored config value (string/json) |
| `group` | `ENUM('general','billing','clinical','security')` | `INDEX` | No | `'general'` | Config classification |
| `description` | `TEXT` | | Yes | `NULL` | Field purpose |
| `created_at`, `updated_at` | `TIMESTAMP` | | Yes | `NULL` | Timestamps |
