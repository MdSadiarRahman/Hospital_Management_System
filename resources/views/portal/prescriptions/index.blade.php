<x-layouts.portal role="doctor" title="Digital Prescriptions Registry — Meghna Clinic & Nursing Home" activeNav="prescriptions">
    <!-- Header Banner -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-teal text-white px-2 py-1 font-monospace">DIGITAL PRESCRIPTIONS</span>
                        <span class="badge bg-navy text-white px-2 py-1">CLINICAL REGISTRY</span>
                    </div>
                    <h3 class="fw-bold text-navy mb-1">Prescriptions Management</h3>
                    <p class="text-muted-hms small mb-0">
                        Meghna Clinic and Nursing Home &bull; Authorized medical directives, pharmacological regimens, and dosage instructions.
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('portal.doctor') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-speedometer2"></i>
                        <span>Doctor Workstation</span>
                    </a>
                    <a href="{{ route('portal.consultations.index') }}" class="btn btn-teal btn-sm d-flex align-items-center gap-1 shadow-sm">
                        <i class="bi bi-journal-medical"></i>
                        <span>Consultations Ledger</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert / Notice Banner: Clinical Scope -->
    <div class="alert alert-info border-info-subtle shadow-sm mb-4 rounded-3 d-flex align-items-center gap-3">
        <i class="bi bi-info-circle-fill fs-3 text-info flex-shrink-0"></i>
        <div class="small">
            <strong class="text-navy">Clinical Governance & Scope Policy:</strong>
            Prescriptions recorded here represent approved clinical orders authored by licensed doctors. Commercial pharmacy inventory, stock logistics, and point-of-sale transactions are deferred to a future phase.
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('portal.prescriptions.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" class="form-control border-start-0 font-monospace" placeholder="Search by Rx #, medicine, patient name or code..." value="{{ $filters['q'] ?? '' }}">
                    </div>
                </div>

                <div class="col-6 col-md-2">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="draft" @selected(($filters['status'] ?? '') === 'draft')>Drafts Only</option>
                        <option value="approved" @selected(($filters['status'] ?? '') === 'approved')>Approved Only</option>
                    </select>
                </div>

                @if(auth()->user()->hasRole(['super_admin', 'administrator']) || count($doctors) > 1)
                    <div class="col-6 col-md-3">
                        <select name="doctor_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Prescribing Doctors</option>
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
                        <a href="{{ route('portal.prescriptions.index') }}" class="btn btn-sm btn-outline-secondary" title="Clear Filters">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Prescriptions Table -->
    <div class="card border-0 shadow-sm rounded-3 bg-white overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted-hms small text-uppercase">
                    <tr>
                        <th class="ps-3 py-3" style="width: 150px;">Prescription #</th>
                        <th class="py-3">Patient Details</th>
                        <th class="py-3">Consultation / Visit</th>
                        <th class="py-3">Prescribing Doctor</th>
                        <th class="py-3">Medicines & Regimen</th>
                        <th class="py-3" style="width: 120px;">Review Date</th>
                        <th class="py-3" style="width: 100px;">Status</th>
                        <th class="pe-3 py-3 text-end" style="width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($prescriptions as $rx)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-bold font-monospace text-teal">{{ $rx->prescription_number ?? '#RX-' . str_pad($rx->id, 5, '0', STR_PAD_LEFT) }}</div>
                                <div class="small text-muted font-monospace">{{ $rx->created_at->format('d M, h:i A') }}</div>
                            </td>
                            <td>
                                @if($rx->patient)
                                    <div class="fw-semibold text-dark">{{ $rx->patient->name }}</div>
                                    <div class="small text-muted font-monospace">
                                        {{ $rx->patient->patient_code }} &bull; {{ $rx->patient->gender }}, {{ $rx->patient->formatted_age }}
                                    </div>
                                @else
                                    <span class="text-muted fst-italic">Anonymous/Unregistered</span>
                                @endif
                            </td>
                            <td>
                                @if($rx->consultation)
                                    <a href="{{ route('portal.consultations.show', $rx->consultation) }}" class="text-decoration-none fw-medium text-navy small d-block">
                                        #CNS-{{ str_pad($rx->consultation->id, 5, '0', STR_PAD_LEFT) }}
                                    </a>
                                    <span class="font-monospace text-muted small">
                                        {{ $rx->consultation->visit?->visit_number }}
                                    </span>
                                @else
                                    <span class="text-muted small">&mdash;</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-medium text-navy">Dr. {{ $rx->doctor?->name ?? 'Clinical Staff' }}</div>
                                <div class="small text-muted-hms">{{ $rx->doctor?->staffProfile?->department?->name ?? 'Department' }}</div>
                            </td>
                            <td>
                                @if($rx->items->isNotEmpty())
                                    <div class="fw-semibold text-dark small">{{ $rx->items->first()->medicine_name }}</div>
                                    <div class="small text-muted font-monospace">
                                        {{ $rx->items->first()->dosage }} &bull; {{ $rx->items->first()->frequency }}
                                        @if($rx->items->count() > 1)
                                            <span class="badge bg-light text-secondary border font-sans ms-1">+{{ $rx->items->count() - 1 }} more</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted small fst-italic">No medications prescribed</span>
                                @endif
                            </td>
                            <td>
                                @if($rx->follow_up_date)
                                    <span class="badge bg-light text-navy border font-monospace">
                                        {{ $rx->follow_up_date->format('d M Y') }}
                                    </span>
                                @else
                                    <span class="text-muted small">&mdash;</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $rx->status_badge_class }} px-2 py-1 text-uppercase" style="font-size: 0.7rem;">
                                    {{ $rx->status }}
                                </span>
                            </td>
                            <td class="pe-3 text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('portal.prescriptions.show', $rx) }}" class="btn btn-outline-teal" title="View Prescription Dossier">
                                        <i class="bi bi-folder2-open"></i>
                                    </a>
                                    <a href="{{ route('portal.prescriptions.edit', $rx) }}" class="btn btn-outline-secondary" title="{{ $rx->is_approved ? 'Amend Rx (Audit Tracked)' : 'Edit Draft' }}">
                                        <i class="bi {{ $rx->is_approved ? 'bi-pencil-square text-warning' : 'bi-pencil' }}"></i>
                                    </a>
                                    <a href="{{ route('portal.prescriptions.print', $rx) }}" target="_blank" class="btn btn-outline-primary" title="Print Prescription Slip">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <div class="mb-2"><i class="bi bi-capsule fs-1 text-secondary opacity-50"></i></div>
                                <h6 class="fw-bold">No Prescriptions Found</h6>
                                <p class="small mb-0">No prescription records match the active criteria.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($prescriptions->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $prescriptions->links() }}
            </div>
        @endif
    </div>
</x-layouts.portal>

