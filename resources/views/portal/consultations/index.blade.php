<x-layouts.portal role="doctor" title="Doctor Clinical Consultations — Meghna Clinic & Nursing Home" activeNav="consultations">
    <!-- Header Banner -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-teal text-white px-2 py-1 font-monospace">OPD & IPD CLINICAL LEDGER</span>
                        <span class="badge bg-navy text-white px-2 py-1">LEGAL MEDICAL RECORDS</span>
                    </div>
                    <h3 class="fw-bold text-navy mb-1">Doctor Consultations Registry</h3>
                    <p class="text-muted-hms small mb-0">
                        Meghna Clinic and Nursing Home &bull; Official consultation dossiers, diagnoses, treatment plans, and medical history.
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('portal.doctor') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-speedometer2"></i>
                        <span>Doctor Workstation</span>
                    </a>
                    <a href="{{ route('portal.doctor') }}#consultation-queue" class="btn btn-teal btn-sm d-flex align-items-center gap-1 shadow-sm">
                        <i class="bi bi-person-lines-fill"></i>
                        <span>Open Patient Queue</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert / Notice Banner: Medical Responsibility Disclaimer -->
    <div class="alert alert-info border-info-subtle shadow-sm mb-4 rounded-3 d-flex align-items-center gap-3">
        <i class="bi bi-shield-check fs-3 text-info flex-shrink-0"></i>
        <div class="small">
            <strong class="text-navy">Clinical Governance & Legal Compliance:</strong>
            All medical records and clinical diagnoses documented in this registry represent direct decisions made by registered healthcare professionals (BMDC licensed doctors). This software records clinical decisions and never generates automated diagnosis without human medical oversight.
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('portal.consultations.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" class="form-control border-start-0" placeholder="Search by patient name, code, complaint, or diagnosis..." value="{{ $filters['q'] ?? '' }}">
                    </div>
                </div>

                <div class="col-6 col-md-2">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="draft" @selected(($filters['status'] ?? '') === 'draft')>Drafts Only</option>
                        <option value="finalized" @selected(($filters['status'] ?? '') === 'finalized')>Finalized Only</option>
                    </select>
                </div>

                @if(auth()->user()->hasRole(['super_admin', 'administrator']) || count($doctors) > 1)
                    <div class="col-6 col-md-3">
                        <select name="doctor_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Attending Doctors</option>
                            @foreach($doctors as $doc)
                                <option value="{{ $doc->id }}" @selected(($filters['doctor_id'] ?? '') == $doc->id)>
                                    Dr. {{ $doc->name }} ({{ $doc->staffProfile?->department?->code ?? 'DOC' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-6 col-md-2">
                    <input type="date" name="date" class="form-control form-control-sm font-monospace" value="{{ $filters['date'] ?? '' }}" onchange="this.form.submit()" title="Filter by date">
                </div>

                <div class="col-6 col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-navy w-100" title="Apply Filter">
                        <i class="bi bi-funnel"></i>
                    </button>
                    @if(!empty($filters['q']) || !empty($filters['status']) || !empty($filters['doctor_id']) || !empty($filters['date']))
                        <a href="{{ route('portal.consultations.index') }}" class="btn btn-sm btn-outline-secondary" title="Clear Filters">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Consultations Table -->
    <div class="card border-0 shadow-sm rounded-3 bg-white overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted-hms small text-uppercase">
                    <tr>
                        <th class="ps-3 py-3" style="width: 130px;">Record Ref</th>
                        <th class="py-3">Patient Details</th>
                        <th class="py-3">Visit & Triage</th>
                        <th class="py-3">Attending Doctor</th>
                        <th class="py-3">Primary Diagnosis</th>
                        <th class="py-3">Status</th>
                        <th class="py-3 text-center" style="width: 140px;">Rx / Items</th>
                        <th class="pe-3 py-3 text-end" style="width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($consultations as $consultation)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-bold font-monospace text-navy">#CNS-{{ str_pad($consultation->id, 5, '0', STR_PAD_LEFT) }}</div>
                                <div class="small text-muted font-monospace">{{ $consultation->created_at->format('d M, h:i A') }}</div>
                            </td>
                            <td>
                                @if($consultation->visit?->patient)
                                    <div class="fw-semibold text-dark">{{ $consultation->visit->patient->name }}</div>
                                    <div class="small text-muted font-monospace">
                                        {{ $consultation->visit->patient->patient_code }} &bull; 
                                        {{ $consultation->visit->patient->gender }}, {{ $consultation->visit->patient->formatted_age }}
                                    </div>
                                @else
                                    <span class="text-muted fst-italic">Anonymous/Unregistered</span>
                                @endif
                            </td>
                            <td>
                                @if($consultation->visit)
                                    <div class="font-monospace fw-semibold text-dark">{{ $consultation->visit->visit_number }}</div>
                                    <span class="badge {{ $consultation->visit->visit_type === 'emergency' ? 'bg-danger' : 'bg-primary-subtle text-primary border border-primary-subtle' }}" style="font-size: 0.7rem;">
                                        {{ ucfirst($consultation->visit->visit_type) }}
                                    </span>
                                @else
                                    <span class="text-muted">Direct</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-medium text-navy">Dr. {{ $consultation->doctor->name }}</div>
                                <div class="small text-muted-hms">{{ $consultation->doctor->staffProfile?->department?->name ?? 'Clinical Department' }}</div>
                            </td>
                            <td>
                                @if($consultation->diagnoses->isNotEmpty())
                                    <div class="fw-semibold text-dark">{{ $consultation->diagnoses->first()->condition_name }}</div>
                                    <div class="d-flex align-items-center gap-1 mt-1">
                                        <span class="badge {{ $consultation->diagnoses->first()->diagnosis_type === 'final' ? 'bg-success' : 'bg-warning text-dark' }}" style="font-size: 0.65rem;">
                                            {{ ucfirst($consultation->diagnoses->first()->diagnosis_type) }}
                                        </span>
                                        @if($consultation->diagnoses->first()->icd_code)
                                            <span class="badge bg-light text-secondary font-monospace" style="font-size: 0.65rem;">
                                                ICD: {{ $consultation->diagnoses->first()->icd_code }}
                                            </span>
                                        @endif
                                        @if($consultation->diagnoses->count() > 1)
                                            <span class="small text-muted">+{{ $consultation->diagnoses->count() - 1 }} more</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted small fst-italic">Pending Diagnostic Review</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $consultation->status_badge_class }} px-2 py-1 text-uppercase" style="font-size: 0.7rem;">
                                    {{ $consultation->status }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($consultation->prescription && $consultation->prescription->items->isNotEmpty())
                                    <span class="badge bg-teal-subtle text-teal border border-teal-subtle font-monospace px-2 py-1">
                                        <i class="bi bi-capsule me-1"></i> {{ $consultation->prescription->items->count() }} Meds
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted border px-2 py-1">None</span>
                                @endif
                            </td>
                            <td class="pe-3 text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('portal.consultations.show', $consultation) }}" class="btn btn-outline-teal" title="Open Clinical Dossier">
                                        <i class="bi bi-folder2-open"></i>
                                    </a>
                                    <a href="{{ route('portal.consultations.edit', $consultation) }}" class="btn btn-outline-secondary" title="{{ $consultation->is_finalized ? 'Amend Record (Audit Tracked)' : 'Edit Draft' }}">
                                        <i class="bi {{ $consultation->is_finalized ? 'bi-pencil-square text-warning' : 'bi-pencil' }}"></i>
                                    </a>
                                    <a href="{{ route('portal.consultations.print', $consultation) }}" target="_blank" class="btn btn-outline-primary" title="Print Prescription Slip">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <div class="mb-2"><i class="bi bi-journal-x fs-1 text-secondary opacity-50"></i></div>
                                <h6 class="fw-bold">No Consultations Found</h6>
                                <p class="small mb-3">No clinical consultation records match the active criteria.</p>
                                <a href="{{ route('portal.doctor') }}#consultation-queue" class="btn btn-sm btn-teal">
                                    <i class="bi bi-person-lines-fill me-1"></i> Go to OPD Patient Queue
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($consultations->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $consultations->links() }}
            </div>
        @endif
    </div>
</x-layouts.portal>

