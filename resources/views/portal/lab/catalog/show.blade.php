<x-layouts.portal role="lab" title="{{ $test->name }} [{{ $test->code }}] — Meghna Clinic" activeNav="lab-catalog">
    <!-- Header -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-teal text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 58px; height: 58px; font-size: 1.6rem;">
                        <i class="bi bi-file-earmark-medical-fill"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h4 class="fw-bold text-navy mb-0">{{ $test->name }}</h4>
                            <span class="badge bg-navy font-monospace fs-6">{{ $test->code }}</span>
                            <span class="badge bg-{{ $test->status_badge_class }} px-2 py-1">{{ $test->status_label }}</span>
                        </div>
                        <p class="text-muted-hms small mb-0 mt-1">
                            Discipline: <strong>{{ $test->category }}</strong> &bull; Specimen: <strong>{{ $test->sample_type ?? 'Whole Blood' }}</strong> &bull; Turnaround: <strong>{{ $test->turnaround_time ?? '4 hours' }}</strong>
                        </p>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a href="{{ route('portal.lab-catalog.edit', $test->id) }}" class="btn btn-sm btn-teal">
                        <i class="bi bi-pencil me-1"></i> Edit Specifications
                    </a>
                    <form method="POST" action="{{ route('portal.lab-catalog.toggle-status', $test->id) }}" class="d-inline" onsubmit="return confirm('Change status for {{ $test->name }}?');">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-{{ $test->is_active ? 'warning' : 'success' }}">
                            <i class="bi bi-{{ $test->is_active ? 'pause-circle' : 'play-circle' }} me-1"></i>
                            {{ $test->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                    <a href="{{ route('portal.lab-catalog.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Catalog Index
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-check-circle-fill text-success fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Main Specifications Bento -->
        <div class="col-lg-8">
            <!-- 1. Clinical Description & Utility -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="fw-bold text-navy mb-0">
                        <i class="bi bi-info-circle text-teal me-2"></i>Clinical Description & Indications
                    </h6>
                </div>
                <div class="card-body p-4">
                    @if($test->description)
                        <p class="text-dark mb-0 leading-relaxed">{{ $test->description }}</p>
                    @else
                        <p class="text-muted fst-italic mb-0">No detailed clinical description configured for this test.</p>
                    @endif
                </div>
            </div>

            <!-- 2. Specimen Handling & Preparation -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="fw-bold text-navy mb-0">
                        <i class="bi bi-eyedropper text-teal me-2"></i>Specimen Collection & Patient Instructions
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 rounded bg-light border">
                                <div class="small text-muted text-uppercase fw-semibold mb-1">Required Specimen</div>
                                <div class="fw-bold text-navy fs-6">
                                    <i class="bi bi-droplet-half text-danger me-1"></i>{{ $test->sample_type ?? 'Whole Blood' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 rounded bg-light border">
                                <div class="small text-muted text-uppercase fw-semibold mb-1">Turnaround / Processing Time</div>
                                <div class="fw-bold text-navy fs-6">
                                    <i class="bi bi-clock-history text-teal me-1"></i>{{ $test->turnaround_time ?? '4 hours' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="p-3 rounded bg-light-subtle border">
                                <div class="small text-muted text-uppercase fw-semibold mb-1">Patient Preparation Instructions</div>
                                @if($test->instructions)
                                    <div class="text-dark small mb-0">{{ $test->instructions }}</div>
                                @else
                                    <div class="text-muted small fst-italic mb-0">Standard diagnostic collection protocol. No special fasting or dietary restriction required.</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Laboratory Routing & Execution -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="fw-bold text-navy mb-0">
                        <i class="bi bi-diagram-3 text-teal me-2"></i>Laboratory Execution & Referral Partner
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between p-3 rounded bg-light border mb-3">
                        <div>
                            <span class="small text-muted text-uppercase fw-semibold d-block">Default Processing Facility</span>
                            <span class="badge bg-{{ $test->availability_badge_class }} px-2 py-1 mt-1 fs-6">
                                {{ $test->availability_label }}
                            </span>
                        </div>
                        <span class="badge bg-light text-navy border px-3 py-2">
                            <i class="bi bi-shield-check text-teal me-1"></i> Meghna Clinic Diagnostic Lab
                        </span>
                    </div>

                    @if($test->labPartner)
                        <div class="border rounded p-3 bg-light-subtle">
                            <div class="fw-bold text-navy mb-1">
                                <i class="bi bi-buildings text-teal me-1"></i>Referral Partner: {{ $test->labPartner->name }}
                            </div>
                            <div class="small text-muted">
                                <strong>Partner Code:</strong> {{ $test->labPartner->code }} &bull; 
                                <strong>Contact:</strong> {{ $test->labPartner->contact_person ?? 'Representative' }} &bull; 
                                <strong>Phone:</strong> {{ $test->labPartner->phone }}
                            </div>
                            @if($test->labPartner->address)
                                <div class="small text-muted mt-1">{{ $test->labPartner->address }}</div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar Bento -->
        <div class="col-lg-4">
            <!-- Tariff / Price Card -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="fw-bold text-navy mb-0">
                        <i class="bi bi-cash-stack text-teal me-2"></i>Official Diagnostic Tariff
                    </h6>
                </div>
                <div class="card-body p-4 text-center">
                    <div class="small text-muted text-uppercase fw-semibold mb-1">Standard Patient Price</div>
                    <div class="fw-bold text-teal display-6 mb-2 tabular-nums">
                        {{ $test->formatted_price }}
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">
                        Active Hospital Rate
                    </span>
                    <p class="small text-muted mt-3 mb-0" style="font-size: 0.8rem;">
                        This charge is automatically billed to OPD vouchers and Inpatient bills when ordered.
                    </p>
                </div>
            </div>

            <!-- Meta Information Card -->
            <div class="card border-0 shadow-sm rounded-3 bg-white">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="fw-bold text-navy mb-0">
                        <i class="bi bi-clock text-teal me-2"></i>Record Metadata
                    </h6>
                </div>
                <div class="card-body p-4">
                    <ul class="list-unstyled mb-0 small">
                        <li class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Test ID:</span>
                            <span class="fw-bold text-navy">#{{ $test->id }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Test Code:</span>
                            <span class="font-monospace fw-bold text-navy">{{ $test->code }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Discipline:</span>
                            <span class="fw-semibold text-dark">{{ $test->category }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Registered On:</span>
                            <span class="text-dark">{{ $test->created_at?->format('d M Y, h:i A') }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-1">
                            <span class="text-muted">Last Updated:</span>
                            <span class="text-dark">{{ $test->updated_at?->format('d M Y, h:i A') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-layouts.portal>

