<x-layouts.portal role="lab" title="Diagnostic Order Dossier — #{{ $order->order_number }}" activeNav="lab-orders">
    <!-- Header -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-teal text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 58px; height: 58px; font-size: 1.6rem;">
                        <i class="bi bi-folder2-open"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h4 class="fw-bold text-navy mb-0">Order #{{ $order->order_number }}</h4>
                            <span class="badge {{ $order->status_badge_class }}">{{ $order->status_label }}</span>
                            @if($order->priority === 'stat')
                                <span class="badge bg-danger text-white"><i class="bi bi-lightning-fill"></i> STAT</span>
                            @elseif($order->priority === 'urgent')
                                <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-circle-fill"></i> Urgent</span>
                            @endif
                        </div>
                        <p class="text-muted-hms small mb-0 mt-1">
                            Requisition placed on {{ $order->created_at->format('d M Y, h:i A') }} &bull; Source: 
                            <strong class="text-navy text-capitalize">{{ $order->source === 'internal' ? 'In-House Meghna Lab' : 'External Referral' }}</strong>
                        </p>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a href="{{ route('portal.lab-orders.print-slip', $order->id) }}" target="_blank" class="btn btn-sm btn-outline-navy d-flex align-items-center gap-1 shadow-sm">
                        <i class="bi bi-printer"></i>
                        <span>Print Requisition Slip</span>
                    </a>
                    @if(in_array($order->status, ['pending', 'sample_collected']))
                        <button type="button" class="btn btn-sm btn-teal d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#collectSampleModal">
                            <i class="bi bi-eyedropper"></i>
                            <span>Collect Specimen</span>
                        </button>
                    @endif
                    @if($order->is_editable)
                        <button type="button" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#updateOrderStatusModal">
                            <i class="bi bi-arrow-repeat"></i>
                            <span>Update Order Status</span>
                        </button>
                    @endif
                    <a href="{{ route('portal.lab-orders.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Orders Workbench
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-check-circle-fill text-success fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <ul class="mb-0 small ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Left Column: Patient, Clinical Context, Tests Grid, Specimen Traceability -->
        <div class="col-lg-8">
            <!-- Patient & Clinical Details Bento -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-person-lines-fill text-teal"></i> Patient & Prescriber Details
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <!-- Patient -->
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Patient Name & Code:</span>
                            @if($order->patient)
                                <a href="{{ route('portal.patients.show', $order->patient_id) }}" class="fw-bold text-navy text-decoration-none">
                                    {{ $order->patient->name }}
                                </a>
                                <div class="small text-muted font-monospace">
                                    {{ $order->patient->patient_code }} &bull; {{ $order->patient->gender ?? '' }}, {{ $order->patient->age ? $order->patient->age . ' yrs' : '' }}
                                </div>
                                <div class="small text-muted">
                                    <i class="bi bi-telephone me-1"></i> {{ $order->patient->phone ?? 'N/A' }}
                                </div>
                            @else
                                <span class="text-muted">Unlinked Patient</span>
                            @endif
                        </div>

                        <!-- Prescribing Doctor -->
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Prescribing Doctor:</span>
                            @if($order->doctor)
                                <div class="fw-bold text-navy">Dr. {{ $order->doctor->name }}</div>
                                <div class="small text-muted">
                                    {{ $order->doctor->staffProfile?->specialization ?? 'Medical Officer' }}
                                </div>
                                <div class="small text-muted">
                                    Staff ID: {{ $order->doctor->staffProfile?->employee_code ?? 'EMP-' . $order->doctor->id }}
                                </div>
                            @else
                                <span class="text-muted">Direct Laboratory Request</span>
                            @endif
                        </div>

                        <!-- Consultation Link -->
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Linked Consultation:</span>
                            @if($order->consultation)
                                <a href="{{ route('portal.consultations.show', $order->consultation_id) }}" class="fw-bold text-teal text-decoration-none">
                                    Consultation #{{ $order->consultation_id }}
                                </a>
                                <div class="small text-muted">
                                    Date: {{ $order->consultation->created_at->format('d M Y') }}
                                </div>
                            @else
                                <span class="text-muted">No Consultation Linked</span>
                            @endif
                        </div>

                        <!-- Clinical Indication / Notes -->
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Clinical Indication:</span>
                            <div class="small text-dark fst-italic">
                                {{ $order->notes ?: 'No special clinical notes provided.' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ordered Diagnostic Tests (Separate Tracking per Test) -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-card-checklist text-teal"></i> Ordered Diagnostic Tests ({{ $order->items->count() }})
                        </h6>
                        <small class="text-muted">Each test is tracked and transitioned independently.</small>
                    </div>
                    <span class="badge bg-navy text-white px-3 py-2">
                        Total Tariff: {{ $order->formatted_total_charge }}
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Test Code & Name</th>
                                <th>Category</th>
                                <th>Specimen</th>
                                <th>Sample Barcode</th>
                                <th>Test Status</th>
                                <th class="text-end">Tariff</th>
                                <th class="text-end pe-4">Update Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($order->items as $item)
                                <tr>
                                    <!-- Test Code & Name -->
                                    <td class="ps-4">
                                        <div class="fw-bold text-navy">{{ $item->test_name }}</div>
                                        <div class="small text-muted font-monospace">{{ $item->test_code }}</div>
                                    </td>

                                    <!-- Category -->
                                    <td>
                                        <span class="badge bg-light text-secondary border text-capitalize">
                                            {{ $item->labTest?->category ?? 'General' }}
                                        </span>
                                    </td>

                                    <!-- Specimen -->
                                    <td>
                                        <span class="small text-dark">
                                            <i class="bi bi-eyedropper text-teal me-1"></i> {{ $item->labTest?->sample_type ?? 'Specimen' }}
                                        </span>
                                    </td>

                                    <!-- Linked Sample Barcode -->
                                    <td>
                                        @if($item->sample)
                                            <span class="badge bg-light text-navy border font-monospace">
                                                <i class="bi bi-upc-scan text-teal me-1"></i> {{ $item->sample->barcode }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-muted small">Not accessioned</span>
                                        @endif
                                    </td>

                                    <!-- Status -->
                                    <td>
                                        <span class="badge {{ $item->status_badge_class }}">
                                            {{ $item->status_label }}
                                        </span>
                                    </td>

                                    <!-- Tariff -->
                                    <td class="text-end fw-bold text-navy font-monospace">
                                        {{ $item->formatted_charge }}
                                    </td>

                                    <!-- Update Status Action -->
                                    <td class="text-end pe-4">
                                        @if($order->is_editable && $item->status !== 'completed' && $item->status !== 'cancelled')
                                            <button type="button" class="btn btn-sm btn-outline-teal" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#updateItemStatusModal_{{ $item->id }}">
                                                <i class="bi bi-pencil-square"></i> Update
                                            </button>

                                            <!-- Modal for updating individual test item status -->
                                            <div class="modal fade" id="updateItemStatusModal_{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content border-0 shadow">
                                                        <form action="{{ route('portal.lab-orders.items.update-status', $item->id) }}" method="POST">
                                                            @csrf
                                                            <div class="modal-header bg-navy text-white">
                                                                <h6 class="modal-title fw-bold">Update Test: {{ $item->test_name }}</h6>
                                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body text-start p-4">
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-semibold text-navy">Target Test Status</label>
                                                                    <select name="status" class="form-select" required>
                                                                        <option value="pending" {{ $item->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                                        <option value="sample_collected" {{ $item->status === 'sample_collected' ? 'selected' : '' }}>Sample Collected</option>
                                                                        <option value="processing" {{ $item->status === 'processing' ? 'selected' : '' }}>In Processing</option>
                                                                        <option value="completed" {{ $item->status === 'completed' ? 'selected' : '' }}>Completed (Result Ready)</option>
                                                                        <option value="cancelled" {{ $item->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-semibold text-navy">Remarks / Notes</label>
                                                                    <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Analyzer run completed, within reference range..."></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer bg-light">
                                                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-sm btn-teal">Save Test Status</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted small">&bull; Finalized &bull;</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        No tests recorded in this order.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Specimen Collection & Barcode Traceability Section -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white" id="specimen-section">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-upc-scan text-teal"></i> Specimen Collection & Traceability
                    </h6>
                    @if(in_array($order->status, ['pending', 'sample_collected']))
                        <button type="button" class="btn btn-sm btn-outline-teal" data-bs-toggle="modal" data-bs-target="#collectSampleModal">
                            <i class="bi bi-plus-circle me-1"></i> Accession New Specimen
                        </button>
                    @endif
                </div>
                <div class="card-body p-4">
                    @if($order->samples->isNotEmpty())
                        <div class="row g-3">
                            @foreach($order->samples as $sample)
                                <div class="col-md-6">
                                    <div class="border rounded-3 p-3 bg-light-subtle h-100">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <span class="badge bg-navy text-white font-monospace fs-6">
                                                    <i class="bi bi-barcode"></i> {{ $sample->barcode }}
                                                </span>
                                                <div class="fw-bold text-teal mt-1">
                                                    {{ $sample->sample_type }}
                                                </div>
                                            </div>
                                            <span class="badge {{ $sample->status === 'collected' ? 'bg-success' : 'bg-secondary' }} text-capitalize">
                                                {{ $sample->status }}
                                            </span>
                                        </div>

                                        <div class="small text-muted mt-2">
                                            <div><i class="bi bi-clock me-1"></i> Collected: {{ $sample->collected_at ? $sample->collected_at->format('d M Y, h:i A') : 'N/A' }}</div>
                                            <div><i class="bi bi-person me-1"></i> Phlebotomist: {{ $sample->collectedBy?->name ?? 'Staff Phlebotomist' }}</div>
                                            @if($sample->notes)
                                                <div class="mt-1 text-dark fst-italic"><i class="bi bi-chat-left-text me-1"></i> {{ $sample->notes }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-eyedropper fs-2 text-warning d-block mb-2"></i>
                            <h6 class="fw-bold text-dark mb-1">No Specimen Accessioned Yet</h6>
                            <p class="small text-muted mb-3">Phlebotomy has not recorded biological sample collection for this order.</p>
                            @if(in_array($order->status, ['pending', 'sample_collected']))
                                <button type="button" class="btn btn-sm btn-teal" data-bs-toggle="modal" data-bs-target="#collectSampleModal">
                                    <i class="bi bi-eyedropper me-1"></i> Collect Specimen & Generate Barcode
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column: Status Progression, External Partner Assignment, Audit Timeline -->
        <div class="col-lg-4">
            <!-- External Partner Referral Card -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-buildings text-teal"></i> Laboratory Assignment
                    </h6>
                    <span class="badge {{ $order->source === 'internal' ? 'bg-teal-subtle text-teal' : 'bg-warning-subtle text-dark' }} text-capitalize">
                        {{ $order->source }}
                    </span>
                </div>
                <div class="card-body p-4">
                    @if($order->source === 'external')
                        @if($order->labPartner)
                            <div class="mb-3">
                                <span class="text-muted small d-block">Assigned Partner Lab:</span>
                                <h6 class="fw-bold text-navy mb-0">{{ $order->labPartner->name }}</h6>
                                <span class="badge bg-light text-navy border font-monospace mt-1">{{ $order->labPartner->code }}</span>
                                <div class="small text-muted mt-2">
                                    <div><i class="bi bi-geo-alt me-1"></i> {{ $order->labPartner->address ?? 'Dinajpur' }}</div>
                                    <div><i class="bi bi-telephone me-1"></i> {{ $order->labPartner->phone ?? 'N/A' }}</div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning py-2 small mb-3">
                                <i class="bi bi-exclamation-triangle me-1"></i> External referral required, but no partner lab assigned yet.
                            </div>
                        @endif

                        @if($order->is_editable)
                            <button type="button" class="btn btn-sm btn-outline-warning w-100" data-bs-toggle="modal" data-bs-target="#assignPartnerModal">
                                <i class="bi bi-arrow-left-right me-1"></i> {{ $order->labPartner ? 'Reassign Partner Lab' : 'Assign Partner Lab' }}
                            </button>
                        @endif
                    @else
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-teal-subtle text-teal p-3 fs-4">
                                <i class="bi bi-hospital"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-navy mb-0">Meghna Clinic Central Lab</h6>
                                <p class="small text-muted mb-0">Processed on-site with local automated analyzers.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Complete Status History Timeline -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-clock-history text-teal"></i> Status Audit History
                    </h6>
                </div>
                <div class="card-body p-4">
                    @if($order->statusHistories->isNotEmpty())
                        <div class="position-relative ps-3" style="border-left: 2px solid #E2E8F0;">
                            @foreach($order->statusHistories as $history)
                                <div class="mb-4 position-relative">
                                    <div class="position-absolute" style="left: -23px; top: 0;">
                                        <span class="badge rounded-circle bg-teal p-1 d-inline-block" style="width: 12px; height: 12px;"></span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-light text-navy border text-capitalize">
                                            {{ str_replace('_', ' ', $history->to_status) }}
                                        </span>
                                        <span class="small text-muted font-monospace" style="font-size: 0.75rem;">
                                            {{ $history->created_at->format('d M, h:i A') }}
                                        </span>
                                    </div>
                                    <div class="small text-muted mt-1">
                                        <i class="bi bi-person me-1"></i> {{ $history->changedBy?->name ?? 'System' }}
                                    </div>
                                    @if($history->notes)
                                        <div class="small text-dark bg-light rounded p-2 mt-2 fst-italic">
                                            {{ $history->notes }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted small mb-0">No prior status transitions recorded.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Collect Specimen -->
    <div class="modal fade" id="collectSampleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('portal.lab-orders.samples.store', $order->id) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-teal text-white">
                        <h6 class="modal-title fw-bold">
                            <i class="bi bi-eyedropper me-1"></i> Collect Phlebotomy Specimen
                        </h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-navy">Specimen Type <span class="text-danger">*</span></label>
                            <input type="text" name="sample_type" class="form-control" 
                                value="{{ $order->items->first()?->labTest?->sample_type ?? 'Whole Blood (EDTA)' }}" required>
                            <div class="form-text small">e.g. Whole Blood, Serum, Plasma, Midstream Urine, Throat Swab.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-navy">Barcode / Specimen Identifier</label>
                            <input type="text" name="barcode" class="form-control font-monospace" 
                                placeholder="Auto-generated if left blank (e.g. SMP-{{ date('Ymd') }}-XXXX)">
                            <div class="form-text small">Leave blank to automatically assign the next sequential accession code.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-navy">Collection Timestamp <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="collected_at" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-navy">Phlebotomy Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Fasting confirmed, drawn from left antecubital vein..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-teal btn-sm px-3">
                            <i class="bi bi-check2-circle me-1"></i> Confirm Specimen Collection
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Update Order-Level Status -->
    <div class="modal fade" id="updateOrderStatusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('portal.lab-orders.update-status', $order->id) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-navy text-white">
                        <h6 class="modal-title fw-bold">
                            <i class="bi bi-arrow-repeat me-1"></i> Update Diagnostic Order Status
                        </h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-navy">New Order Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending Collection</option>
                                <option value="sample_collected" {{ $order->status === 'sample_collected' ? 'selected' : '' }}>Sample Collected</option>
                                <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>In Processing</option>
                                <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completed (All Tests Finalized)</option>
                                <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-navy">Reason / Remarks</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Reason for transition or clinical notes..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-navy btn-sm px-3">Update Order Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Assign Partner Lab -->
    <div class="modal fade" id="assignPartnerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('portal.lab-orders.assign-partner', $order->id) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-warning text-dark">
                        <h6 class="modal-title fw-bold">
                            <i class="bi bi-buildings me-1"></i> Assign External Reference Partner
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-navy">Select Reference Partner <span class="text-danger">*</span></label>
                            <select name="lab_partner_id" class="form-select" required>
                                <option value="">-- Choose Partner Lab --</option>
                                @foreach($partners as $partner)
                                    <option value="{{ $partner->id }}" {{ $order->lab_partner_id == $partner->id ? 'selected' : '' }}>
                                        {{ $partner->name }} ({{ $partner->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-navy">Routing Instructions</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Courier dispatch pickup requested, cold chain required..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning btn-sm px-3">Confirm Partner Assignment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.portal>

