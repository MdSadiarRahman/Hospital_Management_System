<x-layouts.portal role="reception" title="Patient Check-in & Visit Intake">
    <div class="container-fluid px-0">
        <!-- Breadcrumbs & Header -->
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-muted-hms" style="font-size: 0.8rem;">
                    <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}" class="text-decoration-none text-muted-hms">Portal</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('portal.visits.index') }}" class="text-decoration-none text-muted-hms">Patient Visits</a></li>
                    <li class="breadcrumb-item active text-navy fw-semibold" aria-current="page">New Patient Check-in</li>
                </ol>
            </nav>
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h1 class="h3 fw-bold text-navy mb-0">Patient Visit Check-in & Triage Intake</h1>
                    <p class="text-muted-hms mb-0 small">Meghna Clinic & Nursing Home — Shohid Minar Mor, Dinajpur</p>
                </div>
                <a href="{{ route('portal.visits.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1 shadow-xs">
                    <i class="bi bi-arrow-left"></i>
                    <span>Back to Visits</span>
                </a>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                    <strong class="text-danger">Please correct the following errors:</strong>
                </div>
                <ul class="mb-0 ps-3 small text-danger">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('portal.visits.store') }}">
            @csrf

            <div class="row g-4">
                <!-- Left Column: Patient Master Selection & Identification -->
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
                        <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <span class="rounded bg-navy bg-opacity-10 text-navy p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <i class="bi bi-person-bounding-box fs-5"></i>
                                </span>
                                <h2 class="h5 fw-bold text-navy mb-0">Patient Master Record</h2>
                            </div>
                            <span class="badge bg-light text-muted border">Separated Master Entity</span>
                        </div>

                        @if($selectedPatient)
                            <!-- Prefilled Patient Card -->
                            <input type="hidden" name="patient_id" value="{{ $selectedPatient->id }}">
                            <div class="p-3 rounded-3 bg-teal bg-opacity-10 border border-teal-subtle mb-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h3 class="h6 fw-bold text-navy mb-0">{{ $selectedPatient->name }}</h3>
                                        <span class="badge bg-navy text-white font-monospace" style="font-size: 0.8rem;">
                                            {{ $selectedPatient->patient_code }}
                                        </span>
                                    </div>
                                    @if($selectedPatient->blood_group)
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle fw-bold">
                                            {{ $selectedPatient->blood_group }}
                                        </span>
                                    @endif
                                </div>
                                <div class="row g-1 text-muted" style="font-size: 0.8rem;">
                                    <div class="col-6"><strong>Age:</strong> {{ $selectedPatient->formatted_age }}</div>
                                    <div class="col-6"><strong>Gender:</strong> {{ ucfirst($selectedPatient->gender) }}</div>
                                    <div class="col-12"><strong>Phone:</strong> <span class="font-monospace text-navy">{{ $selectedPatient->phone }}</span></div>
                                    @if($selectedPatient->primaryContact)
                                        <div class="col-12 mt-1 pt-1 border-top border-teal-subtle">
                                            <strong>Emergency Kin:</strong> {{ $selectedPatient->primaryContact->name }} ({{ $selectedPatient->primaryContact->phone }})
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="text-end">
                                <a href="{{ route('portal.visits.create') }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-arrow-repeat"></i> Change Selected Patient
                                </a>
                            </div>
                        @else
                            <!-- Patient Selector Dropdown & Search -->
                            <div class="mb-3">
                                <label for="patient_id" class="form-label fw-semibold text-navy small">
                                    Select Registered Patient <span class="text-danger">*</span>
                                </label>
                                <select name="patient_id" id="patient_id" class="form-select @error('patient_id') is-invalid @enderror" required>
                                    <option value="">— Select or Search Patient —</option>
                                    @foreach($recentPatients as $p)
                                        <option value="{{ $p->id }}" @selected(old('patient_id') == $p->id)>
                                            {{ $p->patient_code }} — {{ $p->name }} ({{ $p->phone }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('patient_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text small">
                                    Only existing master patient records can be checked in for visits.
                                </div>
                            </div>

                            <div class="p-3 rounded bg-light border text-center">
                                <p class="text-muted small mb-2">Patient not yet registered in Meghna Clinic master records?</p>
                                <a href="{{ route('portal.patients.create') }}" class="btn btn-sm btn-outline-teal">
                                    <i class="bi bi-person-plus-fill me-1"></i> Register New Patient Master
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Clinical Notice Card -->
                    <div class="card border-0 shadow-sm rounded-3 bg-white p-4">
                        <div class="d-flex align-items-center gap-2 mb-2 text-navy fw-semibold small">
                            <i class="bi bi-info-circle-fill text-teal"></i>
                            <span>Clinical Workflow Notice</span>
                        </div>
                        <p class="text-muted mb-0" style="font-size: 0.8rem;">
                            Each check-in creates an isolated visit lifecycle with a unique Visit Number. A patient may have multiple historical outpatient or inpatient visits, but historical medical documentation is preserved and traceable under this patient's master record.
                        </p>
                    </div>
                </div>

                <!-- Right Column: Visit Details, Triage Severity, and Intake Notes -->
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
                        <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <span class="rounded bg-teal bg-opacity-10 text-teal p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <i class="bi bi-ticket-detailed-fill fs-5"></i>
                                </span>
                                <h2 class="h5 fw-bold text-navy mb-0">Visit Specifications & Triage</h2>
                            </div>
                            <span class="badge bg-light text-muted border font-monospace">Auto-Assigned ID</span>
                        </div>

                        <div class="row g-3">
                            <!-- Suggested Visit Number (Read-only presentation) -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-navy small">Visit Number (Unique)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted">
                                        <i class="bi bi-hash"></i>
                                    </span>
                                    <input type="text" class="form-control bg-light font-monospace fw-bold text-navy" 
                                           value="{{ $suggestedVisitNumber }}" readonly>
                                </div>
                                <div class="form-text small">Sequential timestamped number: <code>VIS-YYYYMMDD-XXXX</code></div>
                            </div>

                            <!-- Visit Date & Time -->
                            <div class="col-md-6">
                                <label for="visit_date" class="form-label fw-semibold text-navy small">
                                    Check-in Date & Time <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" name="visit_date" id="visit_date" 
                                       class="form-control @error('visit_date') is-invalid @enderror"
                                       value="{{ old('visit_date', now()->format('Y-m-d\TH:i')) }}" required>
                                @error('visit_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Visit Type -->
                            <div class="col-md-6">
                                <label for="visit_type" class="form-label fw-semibold text-navy small">
                                    Visit Classification <span class="text-danger">*</span>
                                </label>
                                <select name="visit_type" id="visit_type" class="form-select @error('visit_type') is-invalid @enderror" required>
                                    <option value="regular" @selected(old('visit_type') === 'regular')>Regular Outpatient (OPD)</option>
                                    <option value="emergency" @selected(old('visit_type') === 'emergency')>Emergency Case (Priority Intake)</option>
                                    <option value="follow_up" @selected(old('visit_type') === 'follow_up')>Follow-up Visit</option>
                                </select>
                                @error('visit_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Triage Severity Level -->
                            <div class="col-md-6">
                                <label for="triage_level" class="form-label fw-semibold text-navy small">
                                    Triage Severity Level <span class="text-danger">*</span>
                                </label>
                                <select name="triage_level" id="triage_level" class="form-select @error('triage_level') is-invalid @enderror" required>
                                    <option value="normal" @selected(old('triage_level', 'normal') === 'normal')>Normal — Stable / Routine OPD (Green)</option>
                                    <option value="priority" @selected(old('triage_level') === 'priority')>Priority — Urgent Attention Needed (Yellow)</option>
                                    <option value="critical" @selected(old('triage_level') === 'critical')>Critical — Immediate Resuscitation / Emergency (Red)</option>
                                </select>
                                @error('triage_level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Initial Status -->
                            <div class="col-md-6">
                                <label for="status" class="form-label fw-semibold text-navy small">
                                    Initial Status <span class="text-danger">*</span>
                                </label>
                                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="waiting" @selected(old('status', 'waiting') === 'waiting')>Waiting in Lobby Queue</option>
                                    <option value="in_consultation" @selected(old('status') === 'in_consultation')>In Doctor Chamber / In Consultation</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Attending Doctor (Optional assignment) -->
                            <div class="col-md-6">
                                <label for="doctor_id" class="form-label fw-semibold text-navy small">
                                    Assigned Attending Doctor <span class="text-muted small">(Optional)</span>
                                </label>
                                <select name="doctor_id" id="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror">
                                    <option value="">— Assign Later in Consultation —</option>
                                    @foreach($doctors as $doc)
                                        <option value="{{ $doc->id }}" @selected(old('doctor_id') == $doc->id)>
                                            {{ $doc->name }} ({{ $doc->staffProfile?->department?->name ?? 'Medical Officer' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('doctor_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Chief Complaints / Intake Symptoms -->
                            <div class="col-12">
                                <label for="chief_complaints" class="form-label fw-semibold text-navy small">
                                    Chief Complaints & Presenting Symptoms <span class="text-muted small">(Optional Intake Notes)</span>
                                </label>
                                <textarea name="chief_complaints" id="chief_complaints" rows="3" 
                                          class="form-control @error('chief_complaints') is-invalid @enderror"
                                          placeholder="e.g. Acute abdominal pain for 2 days, moderate fever (102°F), nausea...">{{ old('chief_complaints') }}</textarea>
                                @error('chief_complaints')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text small">Will be transferred to the attending physician's clinical consultation record.</div>
                            </div>
                        </div>

                        <!-- Action Submit Buttons -->
                        <div class="d-flex justify-content-end align-items-center gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('portal.visits.index') }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-teal px-4 shadow-xs">
                                <i class="bi bi-check2-circle me-1"></i> Register Visit Check-in
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-layouts.portal>
