<x-layouts.portal title="Initiate Emergency Referral — Meghna Clinic">
    <div class="container-fluid py-4">

        {{-- Breadcrumb & Header --}}
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}" class="text-decoration-none">Portal</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('portal.referrals.index') }}" class="text-decoration-none">Emergency Referrals</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Initiate Referral</li>
                </ol>
            </nav>
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h1 class="h3 fw-bold text-navy mb-0">Initiate Emergency Referral / ICU Transfer</h1>
                    <p class="text-muted small mb-0">Record clinical emergency assessment, authorize tertiary handover, and arrange emergency transit.</p>
                </div>
                <a href="{{ route('portal.referrals.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to Roster
                </a>
            </div>
        </div>

        {{-- Regulatory & Policy Notices --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-lg-6">
                <div class="alert alert-warning border-0 shadow-sm rounded-3 h-100 d-flex align-items-start mb-0">
                    <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-warning"></i>
                    <div>
                        <strong class="d-block text-dark">No On-Site ICU/CCU Policy Notice:</strong>
                        <span class="small text-muted">
                            Meghna Clinic is a secondary healthcare facility (10 inpatient beds) and <strong>does not possess on-site ICU or CCU infrastructure</strong>. Patients requiring invasive ventilation, cardiac cat-lab, or advanced critical care must be stabilized and urgently transferred to M. Abdur Rahim Medical College Hospital or tertiary institutions.
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="alert alert-info border-0 shadow-sm rounded-3 h-100 d-flex align-items-start mb-0">
                    <i class="bi bi-shield-check fs-4 me-3 text-info"></i>
                    <div>
                        <strong class="d-block text-navy">Clinical Responsibility & Non-Automation Notice:</strong>
                        <span class="small text-muted">
                            Medical decisions, triage classifications, and transfer authorizations are <strong>exclusively made and signed off by the attending registered physician</strong>. The system does not implement automated clinical overrides.
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Errors Alert --}}
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-4 rounded-3" role="alert">
                <div class="d-flex align-items-center mb-1">
                    <i class="bi bi-exclamation-octagon-fill fs-5 me-2"></i>
                    <strong>Cannot Initiate Referral:</strong>
                </div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('portal.referrals.store') }}" method="POST" id="emergencyReferralForm">
            @csrf

            <div class="row g-4">
                {{-- Left Column: Patient, Assessment, & Clinical Reason --}}
                <div class="col-12 col-lg-7">
                    {{-- 1. Patient & Inpatient Stay Selection --}}
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title fw-bold text-navy mb-0">
                                <i class="bi bi-person-heart text-danger me-2"></i>1. Patient & Case Selection
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            @if($selectedAdmission)
                                <input type="hidden" name="admission_id" value="{{ $selectedAdmission->id }}">
                                <input type="hidden" name="patient_id" value="{{ $selectedAdmission->patient_id }}">
                                <div class="p-3 bg-light rounded border mb-0">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-danger text-white">Inpatient Admission: {{ $selectedAdmission->admission_number }}</span>
                                        <span class="badge bg-light text-dark border">Bed: {{ $selectedAdmission->currentBedAssignment?->bed?->bed_number ?? 'N/A' }}</span>
                                    </div>
                                    <h5 class="fw-bold text-navy mb-1">{{ $selectedAdmission->patient?->name }}</h5>
                                    <div class="small text-muted">
                                        Code: <strong>{{ $selectedAdmission->patient?->patient_code }}</strong> | 
                                        {{ $selectedAdmission->patient?->formatted_age }}, {{ ucfirst($selectedAdmission->patient?->gender ?? '') }} | 
                                        Blood Group: <strong class="text-danger">{{ $selectedAdmission->patient?->blood_group ?? 'N/A' }}</strong>
                                    </div>
                                </div>
                            @elseif($selectedVisit)
                                <input type="hidden" name="visit_id" value="{{ $selectedVisit->id }}">
                                <input type="hidden" name="patient_id" value="{{ $selectedVisit->patient_id }}">
                                <div class="p-3 bg-light rounded border mb-0">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-danger text-white">Emergency Visit: {{ $selectedVisit->visit_number }}</span>
                                        <span class="badge bg-warning text-dark">Triage: {{ ucfirst($selectedVisit->triage_level) }}</span>
                                    </div>
                                    <h5 class="fw-bold text-navy mb-1">{{ $selectedVisit->patient?->name }}</h5>
                                    <div class="small text-muted">
                                        Code: <strong>{{ $selectedVisit->patient?->patient_code }}</strong> | 
                                        {{ $selectedVisit->patient?->formatted_age }}, {{ ucfirst($selectedVisit->patient?->gender ?? '') }} | 
                                        Blood Group: <strong class="text-danger">{{ $selectedVisit->patient?->blood_group ?? 'N/A' }}</strong>
                                    </div>
                                </div>
                            @else
                                <div class="mb-3">
                                    <label for="admission_id" class="form-label fw-semibold text-dark">
                                        Select Patient / Active Inpatient Stay <span class="text-danger">*</span>
                                    </label>
                                    <select name="admission_id" id="admission_id" class="form-select @error('admission_id') is-invalid @enderror">
                                        <option value="">-- Choose Admitted Inpatient --</option>
                                        @foreach($activeAdmissions as $adm)
                                            <option value="{{ $adm->id }}"
                                                data-patient-id="{{ $adm->patient_id }}"
                                                data-patient-name="{{ $adm->patient?->name }}"
                                                data-patient-code="{{ $adm->patient?->patient_code }}"
                                                data-patient-age="{{ $adm->patient?->formatted_age }}"
                                                data-patient-gender="{{ ucfirst($adm->patient?->gender ?? '') }}"
                                                data-patient-blood="{{ $adm->patient?->blood_group ?? 'Unknown' }}"
                                                data-bed-number="{{ $adm->currentBedAssignment?->bed?->bed_number ?? 'N/A' }}"
                                                {{ old('admission_id') == $adm->id ? 'selected' : '' }}>
                                                {{ $adm->patient?->name }} ({{ $adm->patient?->patient_code }}) — Bed: {{ $adm->currentBedAssignment?->bed?->bed_number ?? 'N/A' }} (Adm: {{ $adm->admission_number }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('admission_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">If patient is not admitted, you can select from emergency visits below.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="visit_id" class="form-label fw-semibold text-dark">
                                        Or Select Emergency OPD Visit
                                    </label>
                                    <select name="visit_id" id="visit_id" class="form-select @error('visit_id') is-invalid @enderror">
                                        <option value="">-- None / Select Inpatient Above --</option>
                                        @foreach($emergencyVisits as $v)
                                            <option value="{{ $v->id }}"
                                                data-patient-id="{{ $v->patient_id }}"
                                                data-patient-name="{{ $v->patient?->name }}"
                                                data-patient-code="{{ $v->patient?->patient_code }}"
                                                data-patient-age="{{ $v->patient?->formatted_age }}"
                                                data-patient-gender="{{ ucfirst($v->patient?->gender ?? '') }}"
                                                data-patient-blood="{{ $v->patient?->blood_group ?? 'Unknown' }}"
                                                data-bed-number="Emergency Triage"
                                                {{ old('visit_id') == $v->id ? 'selected' : '' }}>
                                                {{ $v->patient?->name }} ({{ $v->patient?->patient_code }}) — Visit: {{ $v->visit_number }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('visit_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Live Patient Preview Card --}}
                                <div id="patientInfoCard" class="p-3 rounded-3 bg-light border d-none">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="badge bg-teal text-white" id="patientInfoCode">MCNH-...</span>
                                        <span class="badge bg-light text-dark border" id="patientInfoBed">Bed: --</span>
                                    </div>
                                    <h6 class="fw-bold text-navy mb-1" id="patientInfoName">Patient Name</h6>
                                    <div class="small text-muted">
                                        <span id="patientInfoAge">-- yrs</span>, 
                                        <span id="patientInfoGender">--</span> | 
                                        Blood Group: <strong class="text-danger" id="patientInfoBlood">--</strong>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- 2. Emergency Assessment & Vital Signs --}}
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title fw-bold text-navy mb-0">
                                <i class="bi bi-heart-pulse-fill text-danger me-2"></i>2. Emergency Clinical Assessment & Vitals
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-6">
                                    <label for="airway_status" class="form-label fw-semibold text-dark">Airway & Breathing Status</label>
                                    <select name="airway_status" id="airway_status" class="form-select">
                                        <option value="patent" {{ old('airway_status') === 'patent' ? 'selected' : '' }}>Patent (Clear)</option>
                                        <option value="oxygen_needed" {{ old('airway_status') === 'oxygen_needed' ? 'selected' : '' }}>SpO2 Compromised (Oxygen Needed)</option>
                                        <option value="compromised" {{ old('airway_status') === 'compromised' ? 'selected' : '' }}>Compromised / Stridor / Obstructed</option>
                                        <option value="intubated" {{ old('airway_status') === 'intubated' ? 'selected' : '' }}>Intubated (Bag-Valve-Mask / ET Tube)</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="consciousness_level" class="form-label fw-semibold text-dark">Consciousness Level (AVPU)</label>
                                    <select name="consciousness_level" id="consciousness_level" class="form-select">
                                        <option value="alert" {{ old('consciousness_level') === 'alert' ? 'selected' : '' }}>Alert & Oriented</option>
                                        <option value="voice" {{ old('consciousness_level') === 'voice' ? 'selected' : '' }}>Responds to Voice</option>
                                        <option value="pain" {{ old('consciousness_level') === 'pain' ? 'selected' : '' }}>Responds to Pain</option>
                                        <option value="unresponsive" {{ old('consciousness_level') === 'unresponsive' ? 'selected' : '' }}>Unresponsive / Comatose</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Vital Signs Grid --}}
                            <h6 class="fw-bold text-navy small text-uppercase mb-2">Bedside Vital Signs Snapshot</h6>
                            <div class="row g-2 mb-3 p-3 bg-light rounded-3">
                                <div class="col-6 col-md-3">
                                    <label class="form-label small text-muted mb-1">BP Systolic</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" name="blood_pressure_systolic" class="form-control" placeholder="120" value="{{ old('blood_pressure_systolic') }}">
                                        <span class="input-group-text">mmHg</span>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label small text-muted mb-1">BP Diastolic</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" name="blood_pressure_diastolic" class="form-control" placeholder="80" value="{{ old('blood_pressure_diastolic') }}">
                                        <span class="input-group-text">mmHg</span>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label small text-muted mb-1">Heart Rate</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" name="heart_rate" class="form-control" placeholder="78" value="{{ old('heart_rate') }}">
                                        <span class="input-group-text">bpm</span>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label small text-muted mb-1">SpO2 Oxygen</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" name="spo2_percent" class="form-control" placeholder="98" min="50" max="100" value="{{ old('spo2_percent') }}">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4 mt-2">
                                    <label class="form-label small text-muted mb-1">Respiratory Rate</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" name="respiratory_rate" class="form-control" placeholder="18" value="{{ old('respiratory_rate') }}">
                                        <span class="input-group-text">/min</span>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4 mt-2">
                                    <label class="form-label small text-muted mb-1">Temperature</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" step="0.1" name="temperature_celsius" class="form-control" placeholder="37.0" value="{{ old('temperature_celsius') }}">
                                        <span class="input-group-text">°C</span>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4 mt-2">
                                    <label class="form-label small text-muted mb-1">GCS Score (3-15)</label>
                                    <input type="number" name="gcs_score" class="form-control form-control-sm" placeholder="15" min="3" max="15" value="{{ old('gcs_score') }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="emergency_assessment" class="form-label fw-semibold text-dark">
                                    Emergency Assessment Findings & Condition Summary
                                </label>
                                <textarea name="emergency_assessment" id="emergency_assessment" rows="3" 
                                    class="form-control @error('emergency_assessment') is-invalid @enderror" 
                                    placeholder="e.g. Acute myocardial infarction with cardiogenic shock; refractory hypotension despite fluid challenge; persistent chest pain radiating to left arm...">{{ old('emergency_assessment') }}</textarea>
                                @error('emergency_assessment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-0">
                                <label for="interventions_given" class="form-label fw-semibold text-dark">
                                    Immediate Interventions Administered at Meghna Clinic
                                </label>
                                <textarea name="interventions_given" id="interventions_given" rows="3" 
                                    class="form-control @error('interventions_given') is-invalid @enderror" 
                                    placeholder="e.g. High-flow O2 via non-rebreather mask (10 L/min), IV Normal Saline 500ml bolus, Loading dose Aspirin 300mg + Clopidogrel 300mg + Atorvastatin 80mg, Sublingual Nitroglycerin...">{{ old('interventions_given') }}</textarea>
                                @error('interventions_given')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Destination, Justification, & Transport --}}
                <div class="col-12 col-lg-5">
                    {{-- 3. Referral Destination & Urgency --}}
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title fw-bold text-navy mb-0">
                                <i class="bi bi-hospital text-danger me-2"></i>3. Destination & Urgency
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label for="facility_name" class="form-label fw-semibold text-dark">
                                    Destination Hospital / Center <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="facility_name" id="facility_name" 
                                    class="form-control @error('facility_name') is-invalid @enderror" 
                                    list="destinationsList" 
                                    value="{{ old('facility_name', 'M. Abdur Rahim Medical College Hospital, Dinajpur') }}" required>
                                <datalist id="destinationsList">
                                    @foreach($commonDestinations as $dest)
                                        <option value="{{ $dest }}"></option>
                                    @endforeach
                                </datalist>
                                @error('facility_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="destination_department" class="form-label fw-semibold text-dark">
                                    Receiving Department / Unit
                                </label>
                                <input type="text" name="destination_department" id="destination_department" 
                                    class="form-control @error('destination_department') is-invalid @enderror" 
                                    value="{{ old('destination_department', 'Intensive Care Unit (ICU)') }}" 
                                    placeholder="e.g. Medical ICU, CCU, Emergency Trauma Center, NICU">
                                @error('destination_department')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="referral_type" class="form-label fw-semibold text-dark">
                                    Referral Classification <span class="text-danger">*</span>
                                </label>
                                <select name="referral_type" id="referral_type" class="form-select @error('referral_type') is-invalid @enderror" required>
                                    <option value="icu_ccu_emergency" {{ old('referral_type', 'icu_ccu_emergency') === 'icu_ccu_emergency' ? 'selected' : '' }}>
                                        External ICU/CCU Critical Emergency
                                    </option>
                                    <option value="tertiary_transfer" {{ old('referral_type') === 'tertiary_transfer' ? 'selected' : '' }}>
                                        Tertiary Medical College Transfer
                                    </option>
                                    <option value="specialist" {{ old('referral_type') === 'specialist' ? 'selected' : '' }}>
                                        Specialist / Super-Specialty Outsource
                                    </option>
                                </select>
                                @error('referral_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Critical & ICU Flags --}}
                            <div class="p-3 bg-light rounded-3 mb-3 border">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_critical" id="is_critical" value="1" {{ old('is_critical', true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-danger" for="is_critical">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i>Flag Critical Life-Threatening Condition
                                    </label>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" name="requires_icu" id="requires_icu" value="1" {{ old('requires_icu', true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-navy" for="requires_icu">
                                        <i class="bi bi-heart-pulse-fill me-1"></i>Requires Intensive Care (ICU / CCU)
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="clinical_reason" class="form-label fw-semibold text-dark">
                                    Reason for Referral <span class="text-danger">*</span>
                                </label>
                                <textarea name="clinical_reason" id="clinical_reason" rows="3" 
                                    class="form-control @error('clinical_reason') is-invalid @enderror" 
                                    placeholder="Explain why Meghna Clinic cannot treat (e.g. Lack of ventilator, emergency coronary angioplasty, massive blood transfusion)..." required>{{ old('clinical_reason') }}</textarea>
                                @error('clinical_reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-0">
                                <label for="receiving_doctor_contact" class="form-label fw-semibold text-dark">
                                    Receiving Hospital Doctor / Desk Contact
                                </label>
                                <input type="text" name="receiving_doctor_contact" id="receiving_doctor_contact" 
                                    class="form-control @error('receiving_doctor_contact') is-invalid @enderror" 
                                    placeholder="e.g. Dr. M. Karim (Duty RMO) / 01700-112233" 
                                    value="{{ old('receiving_doctor_contact') }}">
                                @error('receiving_doctor_contact')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- 4. Transport & Ambulance Logistics --}}
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title fw-bold text-navy mb-0">
                                <i class="bi bi-truck text-danger me-2"></i>4. Transport & Ambulance Logistics
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="transport_arranged" id="transport_arranged" value="1" {{ old('transport_arranged', true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold text-dark" for="transport_arranged">
                                    Emergency Ambulance Transport Arranged
                                </label>
                            </div>

                            <div class="mb-3">
                                <label for="transport_type" class="form-label fw-semibold text-dark">Ambulance / Vehicle Type</label>
                                <select name="transport_type" id="transport_type" class="form-select">
                                    <option value="Clinic Ambulance" {{ old('transport_type') === 'Clinic Ambulance' ? 'selected' : '' }}>Clinic Ambulance (Basic Life Support)</option>
                                    <option value="ICU Ambulance with Ventilator" {{ old('transport_type') === 'ICU Ambulance with Ventilator' ? 'selected' : '' }}>ICU Ambulance (Advanced Life Support / Ventilator)</option>
                                    <option value="Govt 999 Ambulance Service" {{ old('transport_type') === 'Govt 999 Ambulance Service' ? 'selected' : '' }}>Govt 999 Emergency Ambulance</option>
                                    <option value="Patient / Family Arranged" {{ old('transport_type') === 'Patient / Family Arranged' ? 'selected' : '' }}>Patient / Family Arranged Vehicle</option>
                                </select>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label for="ambulance_driver_contact" class="form-label small text-muted">Driver Contact</label>
                                    <input type="text" name="ambulance_driver_contact" id="ambulance_driver_contact" class="form-control form-control-sm" placeholder="017xxxxxxxx" value="{{ old('ambulance_driver_contact') }}">
                                </div>
                                <div class="col-6">
                                    <label for="accompanying_staff" class="form-label small text-muted">Accompanying Staff</label>
                                    <input type="text" name="accompanying_staff" id="accompanying_staff" class="form-control form-control-sm" placeholder="Nurse / Ward Boy" value="{{ old('accompanying_staff', 'Duty Nurse + Ward Boy') }}">
                                </div>
                            </div>

                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" name="oxygen_support_transit" id="oxygen_support_transit" value="1" {{ old('oxygen_support_transit', true) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="oxygen_support_transit">
                                    Continuous Oxygen Cylinder Support Equipped for Transit
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-danger py-3 fw-bold fs-6 text-white shadow-sm">
                            <i class="bi bi-box-arrow-up-right me-2"></i>Authorize & Initiate Emergency Referral
                        </button>
                        <a href="{{ route('portal.referrals.index') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>

    </div>

    {{-- Script for Patient Info card update --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const admissionSelect = document.getElementById('admission_id');
            const visitSelect = document.getElementById('visit_id');
            const patientCard = document.getElementById('patientInfoCard');
            const nameEl = document.getElementById('patientInfoName');
            const codeEl = document.getElementById('patientInfoCode');
            const bedEl = document.getElementById('patientInfoBed');
            const ageEl = document.getElementById('patientInfoAge');
            const genderEl = document.getElementById('patientInfoGender');
            const bloodEl = document.getElementById('patientInfoBlood');

            function updateCardFromSelect(sel) {
                if (!sel) return;
                const selected = sel.options[sel.selectedIndex];
                if (selected && selected.value) {
                    nameEl.textContent = selected.getAttribute('data-patient-name') || 'N/A';
                    codeEl.textContent = selected.getAttribute('data-patient-code') || 'MCNH-...';
                    bedEl.textContent = selected.getAttribute('data-bed-number') || 'N/A';
                    ageEl.textContent = selected.getAttribute('data-patient-age') || '--';
                    genderEl.textContent = selected.getAttribute('data-patient-gender') || '--';
                    bloodEl.textContent = selected.getAttribute('data-patient-blood') || '--';
                    patientCard.classList.remove('d-none');
                } else {
                    patientCard.classList.add('d-none');
                }
            }

            if (admissionSelect) {
                admissionSelect.addEventListener('change', function () {
                    if (this.value && visitSelect) visitSelect.value = '';
                    updateCardFromSelect(this);
                });
            }

            if (visitSelect) {
                visitSelect.addEventListener('change', function () {
                    if (this.value && admissionSelect) admissionSelect.value = '';
                    updateCardFromSelect(this);
                });
            }

            if (admissionSelect && admissionSelect.value) {
                updateCardFromSelect(admissionSelect);
            } else if (visitSelect && visitSelect.value) {
                updateCardFromSelect(visitSelect);
            }
        });
    </script>
</x-layouts.portal>

