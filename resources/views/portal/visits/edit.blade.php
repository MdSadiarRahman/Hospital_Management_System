<x-layouts.portal role="reception" title="Update Visit #{{ $visit->visit_number }}">
    <div class="container-fluid px-0">
        <!-- Breadcrumbs & Header -->
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-muted-hms" style="font-size: 0.8rem;">
                    <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}" class="text-decoration-none text-muted-hms">Portal</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('portal.visits.index') }}" class="text-decoration-none text-muted-hms">Patient Visits</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('portal.visits.show', $visit->id) }}" class="text-decoration-none text-muted-hms">{{ $visit->visit_number }}</a></li>
                    <li class="breadcrumb-item active text-navy fw-semibold" aria-current="page">Update Status & Triage</li>
                </ol>
            </nav>
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h1 class="h3 fw-bold text-navy mb-0">Update Visit Status & Triage</h1>
                    <p class="text-muted-hms mb-0 small">Meghna Clinic & Nursing Home — Shohid Minar Mor, Dinajpur</p>
                </div>
                <a href="{{ route('portal.visits.show', $visit->id) }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1 shadow-xs">
                    <i class="bi bi-arrow-left"></i>
                    <span>Back to Visit Dossier</span>
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

        <div class="row g-4">
            <!-- Left Column: Patient & Immutable Binding Information -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
                    <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                        <h2 class="h6 fw-bold text-navy mb-0">Bound Patient Master Record</h2>
                        <span class="badge bg-light text-muted border">Immutable</span>
                    </div>

                    <div class="p-3 rounded bg-light border mb-3">
                        <div class="fw-bold text-navy">{{ $visit->patient->name }}</div>
                        <div class="font-monospace text-muted small">{{ $visit->patient->patient_code }}</div>
                        <div class="text-muted small mt-1">Phone: {{ $visit->patient->phone }}</div>
                    </div>

                    <div class="alert alert-warning border-0 p-3 mb-0" style="font-size: 0.8rem;">
                        <i class="bi bi-shield-lock-fill text-warning me-1"></i>
                        <strong>Business Rule:</strong> Visits must remain strictly linked to the same patient to preserve clinical integrity and legal medical traceability. Reassigning visits between patients is prohibited.
                    </div>
                </div>
            </div>

            <!-- Right Column: Update Form -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-3 bg-white p-4">
                    <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded bg-primary bg-opacity-10 text-primary p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-pencil-square fs-5"></i>
                            </span>
                            <h2 class="h5 fw-bold text-navy mb-0">Edit Visit Details</h2>
                        </div>
                        <span class="badge bg-navy text-white font-monospace">{{ $visit->visit_number }}</span>
                    </div>

                    <form method="POST" action="{{ route('portal.visits.update', $visit->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <!-- Visit Number (Read-only) -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-navy small">Visit Number</label>
                                <input type="text" class="form-control bg-light font-monospace fw-bold" 
                                       value="{{ $visit->visit_number }}" readonly>
                            </div>

                            <!-- Visit Date & Time -->
                            <div class="col-md-6">
                                <label for="visit_date" class="form-label fw-semibold text-navy small">
                                    Visit Timestamp <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" name="visit_date" id="visit_date" 
                                       class="form-control @error('visit_date') is-invalid @enderror"
                                       value="{{ old('visit_date', $visit->visit_date ? $visit->visit_date->format('Y-m-d\TH:i') : '') }}" required>
                                @error('visit_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Visit Type -->
                            <div class="col-md-6">
                                <label for="visit_type" class="form-label fw-semibold text-navy small">
                                    Visit Type <span class="text-danger">*</span>
                                </label>
                                <select name="visit_type" id="visit_type" class="form-select @error('visit_type') is-invalid @enderror" required>
                                    <option value="regular" @selected(old('visit_type', $visit->visit_type) === 'regular')>Regular Outpatient (OPD)</option>
                                    <option value="emergency" @selected(old('visit_type', $visit->visit_type) === 'emergency')>Emergency Case</option>
                                    <option value="follow_up" @selected(old('visit_type', $visit->visit_type) === 'follow_up')>Follow-up Visit</option>
                                </select>
                                @error('visit_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Triage Level -->
                            <div class="col-md-6">
                                <label for="triage_level" class="form-label fw-semibold text-navy small">
                                    Triage Severity Level <span class="text-danger">*</span>
                                </label>
                                <select name="triage_level" id="triage_level" class="form-select @error('triage_level') is-invalid @enderror" required>
                                    <option value="normal" @selected(old('triage_level', $visit->triage_level) === 'normal')>Normal — Stable / Routine OPD (Green)</option>
                                    <option value="priority" @selected(old('triage_level', $visit->triage_level) === 'priority')>Priority — Urgent Attention Needed (Yellow)</option>
                                    <option value="critical" @selected(old('triage_level', $visit->triage_level) === 'critical')>Critical — Immediate Resuscitation / Emergency (Red)</option>
                                </select>
                                @error('triage_level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Visit Status -->
                            <div class="col-md-12">
                                <label for="status" class="form-label fw-semibold text-navy small">
                                    Visit Lifecycle Status <span class="text-danger">*</span>
                                </label>
                                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="waiting" @selected(old('status', $visit->status) === 'waiting')>Waiting in Lobby Queue</option>
                                    <option value="in_consultation" @selected(old('status', $visit->status) === 'in_consultation')>In Consultation with Doctor</option>
                                    <option value="completed" @selected(old('status', $visit->status) === 'completed')>Completed (Consultation / Outpatient Concluded)</option>
                                    <option value="admitted" @selected(old('status', $visit->status) === 'admitted')>Admitted to Inpatient Ward (10 Beds)</option>
                                    <option value="referred_out" @selected(old('status', $visit->status) === 'referred_out')>Referred Out (Tertiary Hospital / External Facility)</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Action Submit Buttons -->
                        <div class="d-flex justify-content-end align-items-center gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('portal.visits.show', $visit->id) }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-navy px-4 shadow-xs">
                                <i class="bi bi-save me-1"></i> Save Visit Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.portal>
