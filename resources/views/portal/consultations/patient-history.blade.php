<x-layouts.portal role="doctor" title="Consultations History — {{ $patient->name }} — Meghna Clinic & Nursing Home" activeNav="consultations">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('portal.doctor') }}" class="text-teal text-decoration-none">Doctor Workstation</a></li>
            <li class="breadcrumb-item"><a href="{{ route('portal.consultations.index') }}" class="text-teal text-decoration-none">Consultations</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $patient->name }} History</li>
        </ol>
    </nav>

    <!-- Patient Header Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-navy text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 52px; height: 52px; font-size: 1.4rem;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <h4 class="fw-bold text-navy mb-0">{{ $patient->name }}</h4>
                            <span class="badge bg-teal font-monospace">{{ $patient->patient_code }}</span>
                            <span class="badge bg-light text-secondary border">{{ $patient->blood_group ?? 'Blood Unknown' }}</span>
                        </div>
                        <p class="text-muted-hms small mb-0">
                            Age: <strong>{{ $patient->formatted_age }}</strong> &bull; 
                            Gender: <strong>{{ ucfirst($patient->gender) }}</strong> &bull; 
                            Phone: <strong>{{ $patient->phone }}</strong> &bull; 
                            Total Consultations: <strong>{{ $consultations->total() }}</strong>
                        </p>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('portal.consultations.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Back to Ledger
                    </a>
                    <a href="{{ route('portal.patients.show', $patient) }}" class="btn btn-teal btn-sm shadow-sm">
                        <i class="bi bi-person-badge me-1"></i> Master Patient Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline of Consultations -->
    <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold text-navy mb-0">
                <i class="bi bi-journal-medical text-teal me-2"></i> Patient Clinical Consultations Timeline
            </h5>
        </div>
        <div class="card-body p-4">
            @forelse($consultations as $consultation)
                <div class="border rounded-3 p-4 mb-4 bg-light bg-opacity-25 hover-shadow-sm transition">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold font-monospace text-navy fs-6">#CNS-{{ str_pad($consultation->id, 5, '0', STR_PAD_LEFT) }}</span>
                            <span class="badge bg-{{ $consultation->status_badge_class }} text-uppercase">{{ $consultation->status }}</span>
                            <span class="text-muted small">&bull; Visit {{ $consultation->visit?->visit_number }} ({{ ucfirst($consultation->visit?->visit_type) }})</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted small font-monospace"><i class="bi bi-clock me-1"></i> {{ $consultation->created_at->format('d M Y, h:i A') }}</span>
                            <a href="{{ route('portal.consultations.show', $consultation) }}" class="btn btn-sm btn-outline-teal">
                                <i class="bi bi-folder2-open me-1"></i> View Record
                            </a>
                            <a href="{{ route('portal.consultations.print', $consultation) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-printer"></i>
                            </a>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="small fw-bold text-navy mb-1">Attending Doctor:</div>
                            <div class="text-dark small">Dr. {{ $consultation->doctor->name }} ({{ $consultation->doctor->staffProfile?->department?->name ?? 'Clinical Staff' }})</div>

                            <div class="small fw-bold text-navy mt-2 mb-1">Chief Complaints:</div>
                            <p class="small text-muted mb-0">{{ $consultation->chief_complaints }}</p>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="small fw-bold text-navy mb-1">Diagnoses:</div>
                            @if($consultation->diagnoses->isNotEmpty())
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    @foreach($consultation->diagnoses as $diag)
                                        <span class="badge bg-white text-dark border">
                                            {{ $diag->condition_name }} 
                                            <span class="text-muted">({{ ucfirst($diag->diagnosis_type) }})</span>
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="small text-muted">No diagnoses documented.</span>
                            @endif

                            <div class="small fw-bold text-navy mt-2 mb-1">Prescription Items:</div>
                            @if($consultation->prescription && $consultation->prescription->items->isNotEmpty())
                                <span class="badge bg-teal text-white">{{ $consultation->prescription->items->count() }} Medications Prescribed</span>
                                @if($consultation->prescription->follow_up_date)
                                    <span class="badge bg-light text-secondary border font-monospace ms-1">
                                        Review: {{ $consultation->prescription->follow_up_date->format('d M Y') }}
                                    </span>
                                @endif
                            @else
                                <span class="small text-muted">None prescribed.</span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-journal-x fs-1 text-secondary opacity-50 d-block mb-2"></i>
                    <h6 class="fw-bold">No Consultations Found</h6>
                    <p class="small mb-0">This patient does not currently have any recorded consultation records.</p>
                </div>
            @endforelse

            @if($consultations->hasPages())
                <div class="mt-4">
                    {{ $consultations->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.portal>

