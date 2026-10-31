# 04. System Sequence Diagrams
**Meghna Clinic and Nursing Home — Hospital Management System (HMS)**

---

## 1. Sequence 1: Patient Registration & Visit Intake (Reception)

```mermaid
sequenceDiagram
    autonumber
    actor Patient
    actor Receptionist
    participant UI as Reception Dashboard
    participant PC as PatientController
    participant VC as VisitController
    participant DB as MySQL Database

    Patient->>Receptionist: Arrives at Clinic (Presents Phone/Name)
    Receptionist->>UI: Search existing Patient (Phone or Code)
    UI->>PC: search(query)
    PC->>DB: SELECT * FROM patients WHERE phone = ? OR patient_code = ?

    alt Patient Exists
        DB-->>PC: Return Patient Record
        PC-->>UI: Display Patient Profile
    else New Patient
        DB-->>PC: No Record Found
        Receptionist->>UI: Enter Demographics (Name, DOB, Phone, Gender)
        UI->>PC: store(patientData)
        PC->>DB: INSERT INTO patients (patient_code, name, dob, phone)
        DB-->>PC: Patient Created (ID, Code: MCNH-YYYY-XXXX)
        PC-->>UI: Confirm Registration
    end

    Receptionist->>UI: Create Visit (Type: Regular or Emergency)
    UI->>VC: store(patient_id, visit_type)
    VC->>DB: INSERT INTO visits (patient_id, visit_number, visit_date, visit_type, status)
    DB-->>VC: Visit Created (VIS-YYYYMMDD-XXX)
    VC-->>UI: Queue Patient for Doctor Consultation
    Receptionist->>Patient: Issue Token / Send to Waiting Room
```

---

## 2. Sequence 2: Doctor Consultation, Diagnosis & Prescription

```mermaid
sequenceDiagram
    autonumber
    actor Doctor
    participant UI as Doctor Portal UI
    participant CC as ConsultationController
    participant RxService as PrescriptionService
    participant DB as MySQL Database

    Doctor->>UI: Select Patient from Consultation Queue
    UI->>CC: show(visit_id)
    CC->>DB: Fetch Patient History, Past Visits & Allergies
    DB-->>CC: Return Clinical Context
    CC-->>UI: Render Consultation Workspace

    Doctor->>UI: Record Vitals, Symptoms & Diagnoses
    Doctor->>UI: Enter Medications (Drug Name, Dosage, Frequency, Duration)
    Doctor->>UI: Add Clinical Advice & Follow-up Date
    Doctor->>UI: Click "Save & Finalize Prescription"

    UI->>CC: storeConsultation(diagnosisData, rxData)
    CC->>RxService: createPrescriptionWithItems()
    RxService->>DB: START TRANSACTION
    RxService->>DB: INSERT INTO consultations (visit_id, notes, status)
    RxService->>DB: INSERT INTO diagnoses (consultation_id, condition_name, icd_code)
    RxService->>DB: INSERT INTO prescriptions (consultation_id, advice, follow_up_date)
    RxService->>DB: INSERT INTO prescription_items (prescription_id, medicine_name, dosage, frequency, duration)
    RxService->>DB: COMMIT
    DB-->>RxService: Transaction Successful
    RxService-->>UI: Return Verified Prescription View
    Doctor->>UI: Print Prescription / Issue to Patient
```

---

## 3. Sequence 3: Inpatient Admission & Bed Allocation

```mermaid
sequenceDiagram
    autonumber
    actor Doctor
    actor Receptionist
    participant UI as Staff Portal UI
    participant BedService as BedManagementService
    participant AdminService as AdmissionService
    participant DB as MySQL Database

    Doctor->>UI: Mark Visit as "Admission Required" (Gyne/Ortho/Surg/General)
    Doctor->>UI: Submit Admission Recommendation

    Receptionist->>UI: Open Inpatient Admission Workspace
    Receptionist->>UI: Query Available Beds in Recommended Department
    UI->>BedService: getAvailableBeds(department_id)
    BedService->>DB: SELECT * FROM beds WHERE status = 'available' AND department_id = ?

    alt No Bed Available
        DB-->>BedService: 0 Beds Available
        BedService-->>UI: Alert: Ward at 100% Capacity
        Receptionist->>Doctor: Notify: Bed Unavailable (Prepare Referral or Waitlist)
    else Bed Available
        DB-->>BedService: Return Bed List (e.g. Bed EM-01, GYN-02)
        BedService-->>UI: Render Available Beds
        Receptionist->>UI: Select Bed & Confirm Admission
        UI->>AdminService: createAdmissionAndAssignBed(visit_id, bed_id)
        AdminService->>DB: START TRANSACTION
        AdminService->>DB: INSERT INTO admissions (visit_id, admission_number, status)
        AdminService->>DB: INSERT INTO bed_assignments (admission_id, bed_id, assigned_at)
        AdminService->>DB: UPDATE beds SET status = 'occupied' WHERE id = bed_id AND status = 'available'
        AdminService->>DB: COMMIT
        DB-->>AdminService: Confirmed
        AdminService-->>UI: Admission Confirmed (ADM-YYYYMMDD-XXX, Bed Assigned)
    end
```

---

## 4. Sequence 4: Internal & External Laboratory Workflow

```mermaid
sequenceDiagram
    autonumber
    actor Doctor
    actor LabStaff
    participant UI as Lab Workspace
    participant LabService as LaboratoryService
    participant Storage as File Storage
    participant DB as MySQL Database

    Doctor->>UI: Order Tests (e.g., CBC, Serum Creatinine, X-Ray)
    Doctor->>UI: Specify Source: "Internal" or "External Partner"
    UI->>LabService: createLabOrder(consultation_id, test_items, source)
    LabService->>DB: INSERT INTO lab_orders (consultation_id, source, status='pending')
    LabService->>DB: INSERT INTO lab_order_items (lab_order_id, test_name, status='pending')

    alt Internal Processing
        LabStaff->>UI: Collect Sample & Process in In-house Lab
        LabStaff->>UI: Upload Completed Report PDF & Enter Parameters
    else External Diagnostic Center Partner
        LabStaff->>UI: Select Registered Partner (e.g., Popular Diagnostic, Dinajpur)
        LabStaff->>UI: Send Sample / Coordinate External Test
        LabStaff->>UI: Receive Physical/Digital Report from Partner
        LabStaff->>UI: Upload External Report PDF
    end

    LabStaff->>UI: Submit Report Document
    UI->>LabService: storeReport(lab_order_item_id, file)
    LabService->>Storage: Store in 'storage/app/secure_medical_reports/'
    LabService->>DB: INSERT INTO lab_reports (lab_order_item_id, file_path, review_status='pending_review')
    LabService->>DB: UPDATE lab_order_items SET status = 'completed'

    Doctor->>UI: Open Patient Chart -> "Pending Lab Reviews"
    Doctor->>UI: Inspect Uploaded Report Document
    Doctor->>UI: Click "Mark as Reviewed" with Doctor Notes
    UI->>LabService: reviewReport(report_id, doctor_notes)
    LabService->>DB: UPDATE lab_reports SET review_status='reviewed', reviewed_by=doctor_id
    LabService-->>UI: Report Formally Validated & Available in Patient Portal
```

---

## 5. Sequence 5: Dual Clearance, Billing & Discharge

```mermaid
sequenceDiagram
    autonumber
    actor Doctor
    actor BillingStaff
    participant UI as Discharge Workspace
    participant DischargeService as PatientDischargeService
    participant DB as MySQL Database

    Doctor->>UI: Perform Clinical Reassessment
    Doctor->>UI: Grant "Clinical Discharge Clearance" & Authorize Discharge
    UI->>DB: UPDATE admissions SET clinical_clearance = 1 WHERE id = admission_id

    BillingStaff->>UI: Open Inpatient Billing Workspace for Admission
    UI->>DischargeService: calculateTotalBill(admission_id)
    DischargeService->>DB: Aggregate: Bed Days + Doctor Visits + Lab Tests + Service Charges
    DischargeService-->>UI: Display Itemized Bill, Paid Amount & Due Balance

    alt Outstanding Balance Exists
        BillingStaff->>UI: Receive Final Payment from Patient
        UI->>DB: INSERT INTO payments (bill_id, amount, method, status='success')
        UI->>DB: UPDATE bills SET due_amount = 0, status = 'paid'
    end

    BillingStaff->>UI: Grant "Financial Discharge Clearance"
    UI->>DB: UPDATE admissions SET financial_clearance = 1

    BillingStaff->>UI: Execute Final Discharge & Bed Release
    UI->>DischargeService: completeDischarge(admission_id)
    DischargeService->>DB: START TRANSACTION
    DischargeService->>DB: INSERT INTO discharge_summaries (admission_id, summary, medications, advice)
    DischargeService->>DB: UPDATE bed_assignments SET released_at = NOW() WHERE admission_id = ? AND released_at IS NULL
    DischargeService->>DB: UPDATE beds SET status = 'cleaning' WHERE id = bed_id
    DischargeService->>DB: UPDATE admissions SET status = 'discharged', discharge_date = NOW()
    DischargeService->>DB: COMMIT
    DB-->>DischargeService: Discharge Finalized
    DischargeService-->>UI: Print Discharge Summary & Issue Gate Pass
```
