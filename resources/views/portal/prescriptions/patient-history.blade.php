<x-layouts.portal role="doctor" title="Prescription History — {{ $patient->name }} — Meghna Clinic & Nursing Home" activeNav="prescriptions">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('portal.doctor') }}" class="text-teal text-decoration-none">Doctor Workstation</a></li>
            <li class="breadcrumb-item"><a href="{{ route('portal.prescriptions.index') }}" class="text-teal text-decoration-none">Prescriptions</a></li>
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
                            Total Prescriptions: <strong>{{ $prescriptions->total() }}</strong>
                        </p>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('portal.prescriptions.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Back to Ledger
                    </a>
                    <a href="{{ route('portal.patients.show', $patient) }}" class="btn btn-teal btn-sm shadow-sm">
                        <i class="bi bi-person-badge me-1"></i> Master Patient Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline of Prescriptions -->
    <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold text-navy mb-0">
                <i class="bi bi-capsule text-teal me-2"></i> Patient Prescriptions Timeline
            </h5>
        </div>
        <div class="card-body p-4">
            @forelse($prescriptions as $rx)
                <div class="border rounded-3 p-4 mb-4 bg-light bg-opacity-25 hover-shadow-sm transition">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold font-monospace text-teal fs-6">{{ $rx->prescription_number }}</span>
                            <span class="badge bg-{{ $rx->status_badge_class }} text-uppercase">{{ $rx->status }}</span>
                            <span class="text-muted small">&bull; Visit {{ $rx->consultation?->visit?->visit_number }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted small font-monospace"><i class="bi bi-clock me-1"></i> {{ $rx->created_at->format('d M Y, h:i A') }}</span>
                            <a href="{{ route('portal.prescriptions.show', $rx) }}" class="btn btn-sm btn-outline-teal">
                                <i class="bi bi-folder2-open me-1"></i> View Record
                            </a>
                            <a href="{{ route('portal.prescriptions.print', $rx) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-printer"></i>
                            </a>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="small fw-bold text-navy mb-1">Prescribing Doctor:</div>
                            <div class="text-dark small">Dr. {{ $rx->doctor?->name }} ({{ $rx->doctor?->staffProfile?->department?->name ?? 'Clinical Staff' }})</div>

                            @if($rx->advice)
                                <div class="small fw-bold text-navy mt-2 mb-1">Advice:</div>
                                <p class="small text-muted mb-0">{{ Str::limit($rx->advice, 120) }}</p>
                            @endif
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="small fw-bold text-navy mb-1">Prescribed Medicines:</div>
                            @if($rx->items->isNotEmpty())
                                <ul class="list-unstyled mb-0 small">
                                    @foreach($rx->items as $item)
                                        <li class="py-1 border-bottom d-flex justify-content-between">
                                            <span class="fw-semibold text-dark">{{ $item->medicine_name }}</span>
                                            <span class="font-monospace text-muted">{{ $item->dosage }} &bull; {{ $item->frequency }} ({{ $item->duration }})</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="small text-muted">No medicines prescribed.</span>
                            @endif

                            @if($rx->follow_up_date)
                                <div class="small text-muted font-monospace mt-2">
                                    <i class="bi bi-calendar2-check text-teal me-1"></i> Next Review: {{ $rx->follow_up_date->format('d M Y') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-capsule fs-1 text-secondary opacity-50 d-block mb-2"></i>
                    <h6 class="fw-bold">No Prescriptions Found</h6>
                    <p class="small mb-0">This patient does not currently have any prescription records.</p>
                </div>
            @endforelse

            @if($prescriptions->hasPages())
                <div class="mt-4">
                    {{ $prescriptions->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.portal>

