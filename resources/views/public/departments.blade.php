<x-layouts.public 
    title="Clinical Departments"
    metaDescription="Explore specialized clinical departments at Meghna Clinic & Nursing Home, Dinajpur. Including Gynecology, Orthopedics, General Surgery, and 24/7 Emergency."
>
    <!-- Page Header & Breadcrumb -->
    <div class="bg-navy text-white py-5">
        <div class="container-xl">
            <x-breadcrumb :items="['Departments' => null]" class="mb-3 text-white-50" />
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="badge bg-teal text-white px-3 py-1.5 rounded-pill fw-semibold mb-2" style="font-size: 0.85rem;">
                        Clinical Organization
                    </span>
                    <h1 class="display-5 fw-bold mb-3 text-white">Specialized Clinical Departments</h1>
                    <p class="lead text-white-50 mb-0" style="max-width: 720px; line-height: 1.6;">
                        Structured medical and surgical units staffed by qualified doctors and shift nurses, supported by an autoclave-sterilized operating theatre and a 10-bed inpatient ward.
                    </p>
                </div>
                <div class="col-lg-4 mt-4 mt-lg-0 text-lg-end">
                    <div class="p-3 rounded-3 bg-white bg-opacity-10 d-inline-block text-start border border-white border-opacity-10">
                        <span class="d-block text-white fw-bold mb-1">
                            <i class="bi bi-door-open-fill text-teal me-1"></i> Inpatient Ward Status
                        </span>
                        <span class="d-block text-white-50" style="font-size: 0.85rem;">
                            {{ $totalBedCapacity }} Clinical Beds Total &bull; <span class="text-success fw-bold">{{ $availableBedsCount }} Available</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Departments Grid -->
    <section class="py-5 bg-body">
        <div class="container-xl">
            <div class="row g-4 mb-5">
                @forelse($departments as $dept)
                    <div class="col-md-6 col-lg-6">
                        <div class="card h-100 border shadow-sm p-4 rounded-3 bg-white d-flex flex-column justify-content-between hms-card">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="rounded-3 bg-teal-subtle text-teal p-3 d-inline-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                                        @if($dept->code === 'EMERG')
                                            <i class="bi bi-heart-pulse-fill fs-3"></i>
                                        @elseif($dept->code === 'GYN')
                                            <i class="bi bi-gender-female fs-3"></i>
                                        @elseif($dept->code === 'ORTHO')
                                            <i class="bi bi-bandaid-fill fs-3"></i>
                                        @elseif($dept->code === 'SURG')
                                            <i class="bi bi-scissors fs-3"></i>
                                        @else
                                            <i class="bi bi-hospital fs-3"></i>
                                        @endif
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-navy-subtle text-navy font-monospace">{{ $dept->code }}</span>
                                        @if($dept->is_clinical)
                                            <span class="badge bg-teal-subtle text-teal">Clinical Unit</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">Support Unit</span>
                                        @endif
                                    </div>
                                </div>

                                <h2 class="h4 fw-bold text-navy mb-2">{{ $dept->name }}</h2>
                                <p class="text-muted-hms mb-3" style="font-size: 0.9rem; line-height: 1.65;">
                                    {{ $dept->description ?? 'Specialized medical diagnosis, active patient observation, and consultant treatment procedures.' }}
                                </p>

                                <!-- Medical staff attached -->
                                @if($dept->staffProfiles->isNotEmpty())
                                    <div class="p-2.5 rounded bg-body border mb-3">
                                        <span class="d-block text-muted-hms fw-semibold mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                            Consulting Personnel
                                        </span>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($dept->staffProfiles as $profile)
                                                <span class="badge bg-white text-navy border" style="font-size: 0.8rem;">
                                                    <i class="bi bi-person-fill text-teal me-1"></i> {{ $profile->user->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="pt-3 border-top d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <span class="text-teal fw-semibold" style="font-size: 0.85rem;">
                                    <i class="bi bi-door-open me-1"></i> {{ $dept->beds_count }} Inpatient {{ Str::plural('Bed', $dept->beds_count) }}
                                </span>
                                <a href="{{ route('departments.show', $dept->id) }}" class="btn btn-hms-outline-primary btn-hms-sm">
                                    View Department Details <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="p-5 text-center bg-white rounded-3 border">
                            <i class="bi bi-hospital fs-1 text-muted-hms mb-3 d-block"></i>
                            <h3 class="h5 text-navy">No Clinical Departments Configured</h3>
                            <p class="text-muted-hms">Departments can be configured in the Hospital Management Portal.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Inpatient Bed Allocation Summary Table -->
            <div class="card border shadow-sm rounded-3 bg-white p-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                    <div>
                        <h3 class="h5 fw-bold text-navy mb-1">10-Bed Inpatient Allocation Across Departments</h3>
                        <p class="text-muted-hms mb-0" style="font-size: 0.875rem;">
                            Transparent allocation of beds to prevent overcrowding and ensure infection control.
                        </p>
                    </div>
                    <span class="badge bg-teal text-white px-3 py-1.5 rounded-pill">
                        Total Capacity: {{ $totalBedCapacity }} Beds
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Bed Number</th>
                                <th>Department Unit</th>
                                <th>Category</th>
                                <th>Daily Tariff</th>
                                <th>Current Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($beds as $bed)
                                <tr>
                                    <td class="fw-bold font-monospace text-navy">{{ $bed->bed_number }}</td>
                                    <td>
                                        <span class="badge bg-light text-navy border">
                                            {{ $bed->department->name ?? 'General' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-capitalize">{{ $bed->category }}</span>
                                    </td>
                                    <td class="tabular-nums fw-semibold text-navy">
                                        ৳{{ number_format($bed->daily_charge, 2) }} / day
                                    </td>
                                    <td>
                                        @if($bed->status === 'available')
                                            <span class="badge bg-success-subtle text-success fw-bold">Available</span>
                                        @elseif($bed->status === 'occupied')
                                            <span class="badge bg-danger-subtle text-danger fw-bold">Occupied</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning fw-bold">{{ ucfirst($bed->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted-hms py-3">No beds loaded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- Administrative Configuration Notice -->
    <section class="py-4 bg-white border-top">
        <div class="container-xl">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle fw-semibold px-2.5 py-1">
                        <i class="bi bi-gear-fill me-1"></i> Admin Managed
                    </span>
                    <span class="text-muted-hms" style="font-size: 0.85rem;">
                        Specialized clinic units, surgeon schedules, and bed allocations are managed in the <strong>Hospital Management Portal</strong>.
                    </span>
                </div>
                <a href="{{ route('home') }}#appointment" class="btn btn-hms-teal btn-hms-sm">
                    <i class="bi bi-calendar2-check"></i> Book Doctor Appointment
                </a>
            </div>
        </div>
    </section>
</x-layouts.public>
