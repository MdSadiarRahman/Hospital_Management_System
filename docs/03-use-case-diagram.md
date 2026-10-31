# 03. Use Case Specifications & Diagrams
**Meghna Clinic and Nursing Home — Hospital Management System (HMS)**

---

## 1. System Actors & Primary Responsibilities

| Actor | Category | Primary Operational Scope |
| :--- | :--- | :--- |
| **Guest / Visitor** | External | Browsing public hospital services, doctors, and submitting online appointment requests |
| **Patient** | External | Accessing personal verified medical visits, prescriptions, lab reports, and billing |
| **Receptionist** | Internal Staff | Patient registration, visit intake, offline/online appointment scheduling, bed inquiries |
| **Doctor** | Clinical Staff | Clinical consultations, triage, diagnoses, prescriptions, lab orders, OT scheduling, discharge clearance |
| **Nurse / Sister** | Clinical Staff | Bedside vital recording, nursing observation notes, medication administration (MAR), bed readiness |
| **Lab Staff** | Diagnostic Staff | Internal test processing, external lab partner order management, report upload |
| **Billing Staff** | Administrative | Bill itemization, payment receipts, partial payment handling, financial clearance |
| **OT / Ward Staff** | Operational | Operation theater preparation, bed sanitation/cleaning, patient transportation |
| **Administrator** | Governance | Staff roster management, department/bed settings, overall operational supervision |
| **Super Admin** | Governance | Complete system configuration, role/permission management, audit trail inspection, database backup |

---

## 2. Public Website & Patient Portal Use Cases

```mermaid
graph LR
    Visitor((Guest Visitor))
    Patient((Registered Patient))

    subgraph "Public Website"
        UC_ViewInfo[View Clinic Profile, Doctors & Services]
        UC_ReqAppt[Submit Online Appointment Request]
        UC_Contact[View Location & Emergency Hotlines]
    end

    subgraph "Patient Portal"
        UC_PReg[Register Patient Account]
        UC_PLogin[Secure Login & Profile Update]
        UC_PLink[Request Clinical Record Linking]
        UC_PAppt[Track Appointment Status]
        UC_PVisits[View Personal Visit History]
        UC_PRx[View/Download Verified Prescriptions]
        UC_PLab[View/Download Verified Lab Reports]
        UC_PBill[View Invoice & Payment Status]
    end

    Visitor --> UC_ViewInfo
    Visitor --> UC_ReqAppt
    Visitor --> UC_Contact
    Visitor --> UC_PReg

    Patient --> UC_PLogin
    Patient --> UC_PLink
    Patient --> UC_PAppt
    Patient --> UC_PVisits
    Patient --> UC_PRx
    Patient --> UC_PLab
    Patient --> UC_PBill
```

---

## 3. Clinical & Inpatient Operations Use Cases

```mermaid
graph LR
    Receptionist((Receptionist))
    Doctor((Doctor))
    Nurse((Nurse / Sister))

    subgraph "Patient Intake & OPD"
        UC_SearchPt[Search / Register Patient]
        UC_CreateVisit[Create Patient Visit OPD/Emergency]
        UC_ApproveAppt[Review & Confirm Appointment Request]
    end

    subgraph "Doctor Clinical Care"
        UC_Consult[Conduct Patient Consultation]
        UC_Triage[Perform Emergency Assessment & Referral]
        UC_Diagnose[Record Clinical Diagnosis & Notes]
        UC_Rx[Generate Electronic Prescription]
        UC_OrderLab[Order Diagnostic Tests Internal/External]
        UC_AdmitReq[Recommend Inpatient Admission]
        UC_OTReq[Schedule Operation & Record OT Notes]
        UC_ClinClear[Grant Clinical Discharge Clearance]
    end

    subgraph "Nursing & Bedside Care"
        UC_Vitals[Record Vital Signs & Patient Monitoring]
        UC_NurseNotes[Record Nursing Observations]
        UC_MedAdmin[Record Medication Administration MAR]
        UC_BedStatus[Update Bed Sanitation / Cleaning Status]
    end

    Receptionist --> UC_SearchPt
    Receptionist --> UC_CreateVisit
    Receptionist --> UC_ApproveAppt

    Doctor --> UC_Consult
    Doctor --> UC_Triage
    Doctor --> UC_Diagnose
    Doctor --> UC_Rx
    Doctor --> UC_OrderLab
    Doctor --> UC_AdmitReq
    Doctor --> UC_OTReq
    Doctor --> UC_ClinClear

    Nurse --> UC_Vitals
    Nurse --> UC_NurseNotes
    Nurse --> UC_MedAdmin
    Nurse --> UC_BedStatus
```

---

## 4. Diagnostics, Bed Management, Billing & Administration Use Cases

```mermaid
graph LR
    LabStaff((Lab Staff))
    BillingStaff((Billing Staff))
    Admin((Administrator / Super Admin))

    subgraph "Diagnostic & Lab Operations"
        UC_ProcLab[Process Internal Lab Order]
        UC_ExtPartner[Assign to External Diagnostic Partner]
        UC_UploadRep[Upload Verified Lab Report PDF]
    end

    subgraph "Bed & Inpatient Management"
        UC_ManageBeds[Configure 10 Hospital Beds]
        UC_AssignBed[Assign Bed to Inpatient Admission]
        UC_TransferBed[Transfer Patient between Beds]
        UC_ReleaseBed[Release Bed upon Discharge]
    end

    subgraph "Billing & Financial Operations"
        UC_GenBill[Generate Itemized Hospital Bill]
        UC_RecvPay[Record Payment Partial/Full]
        UC_FinClear[Grant Financial Discharge Clearance]
        UC_PrintRec[Issue Printed Cash Receipt / Invoice]
    end

    subgraph "Administration & System Governance"
        UC_StaffMgmt[Manage Staff Credentials & Profiles]
        UC_DeptMgmt[Manage Clinical Departments]
        UC_Audit[Inspect System Audit Logs]
        UC_Backup[Trigger System & Database Backup]
    end

    LabStaff --> UC_ProcLab
    LabStaff --> UC_ExtPartner
    LabStaff --> UC_UploadRep

    Receptionist -.-> UC_AssignBed
    Admin --> UC_ManageBeds
    Admin --> UC_StaffMgmt
    Admin --> UC_DeptMgmt
    Admin --> UC_Audit
    Admin --> UC_Backup

    BillingStaff --> UC_GenBill
    BillingStaff --> UC_RecvPay
    BillingStaff --> UC_FinClear
    BillingStaff --> UC_PrintRec
    BillingStaff --> UC_ReleaseBed
```
