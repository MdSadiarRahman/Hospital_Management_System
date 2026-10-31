<x-layouts.public 
    :title="$department->name . ' Department'"
    :metaDescription="'Learn about ' . $department->name . ' at Meghna Clinic & Nursing Home, Dinajpur. Providing inpatient beds, surgical care, and 24/7 medical supervision.'"
>
    <!-- Page Header & Breadcrumb -->
    <div class="bg-navy text-white py-5">
        <div class="container-xl">
            <x-breadcrumb :items="['Departments' => route('departments'), $department->name => null]" class="mb-3 text-white-50" />
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-teal text-white px-3 py-1.5 rounded-pill fw-semibold" style="font-size: 0.85rem;">
                            Department Unit &bull; {{ $department->code }}
                        </span>
                        @if($department->is_clinical)
                            <span class="badge bg-white bg-opacity-20 text-white">Clinical Service</span>
                        @endif
                    </div>
                    <h1 class="display-5 fw-bold mb-3 text-white">{{ $department->name }}</h1>
                    <p class="lead text-white-50 mb-0" style="max-width: 720px; line-height: 1.6;">
                        {{ $department->description ?? 'Specialized medical treatment, inpatient bed observation, and consulting procedures at Meghna Clinic and Nursing Home, Dinajpur.' }}
                    </p>
                </div>
                <div class="col-lg-4 mt-4 mt-lg-0 text-lg-end">
                    <div class="p-3 rounded-3 bg-white bg-opacity-10 d-inline-block text-start border border-white border-opacity-10">
                        <span class="d-block text-white fw-bold mb-1">
                            <i class="bi bi-door-open-fill text-teal me-1"></i> Dedicated Bed Count
                        </span>
                        <span class="d-block text-white-50" style="font-size: 0.85rem;">
                            {{ $department->beds->count() }} Inpatient Beds assigned to this unit
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content & Sidebar -->
    <section class="py-5 bg-body">
        <div class="container-xl">
            <div class="row g-4">
                <!-- Left: Department Clinical Scope & Beds -->
                <div class="col-lg-8">
                    <!-- Scope of Care Card -->
                    <div class="card border shadow-sm p-4 rounded-3 bg-white mb-4">
                        <h2 class="h4 fw-bold text-navy mb-3">
                            <i class="bi bi-shield-check text-teal me-2"></i> Clinical Scope & Patient Care
                        </h2>
                        <p class="text-muted-hms mb-4" style="line-height: 1.7;">
                            The <strong>{{ $department->name }}</strong> department at Meghna Clinic and Nursing Home delivers patient-focused medical care grounded in evidence-based clinical protocols. Located in Dinajpur Sadar, our facility combines dedicated observation beds with continuous nursing oversight to support patient recovery and safety.
                        </p>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="p-3 bg-body rounded border h-100">
                                    <h3 class="h6 fw-bold text-navy mb-2">
                                        <i class="bi bi-check2 text-teal me-1"></i> 24/7 Medical Oversight
                                    </h3>
                                    <p class="text-muted-hms mb-0" style="font-size: 0.85rem; line-height: 1.5;">
                                        Every patient admitted under this department receives rounds from attending doctors and regular vital evaluations by our 6 shift nurses.
                                    </p>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 bg-body rounded border h-100">
                                    <h3 class="h6 fw-bold text-navy mb-2">
                                        <i class="bi bi-check2 text-teal me-1"></i> Sterilization & Hygiene
                                    </h3>
                                    <p class="text-muted-hms mb-0" style="font-size: 0.85rem; line-height: 1.5;">
                                        All surgical dressings, instrument trays, and bed linens follow high-level autoclave sterilization and infection prevention protocols.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Assigned Inpatient Beds Table -->
                    <div class="card border shadow-sm p-4 rounded-3 bg-white mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                            <div>
                                <h3 class="h5 fw-bold text-navy mb-1">
                                    <i class="bi bi-hospital text-teal me-2"></i> Inpatient Beds in this Unit
                                </h3>
                                <p class="text-muted-hms mb-0" style="font-size: 0.85rem;">
                                    Monitored clinical beds allocated for inpatient observation and post-operative recovery.
                                </p>
                            </div>
                            <span class="badge bg-teal-subtle text-teal">
                                {{ $department->beds->count() }} Beds Assigned
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th>Bed Number</th>
                                        <th>Category</th>
                                        <th>Daily Charge</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($department->beds as $bed)
                                        <tr>
                                            <td class="fw-bold font-monospace text-navy">{{ $bed->bed_number }}</td>
                                            <td class="text-capitalize">{{ $bed->category }}</td>
                                            <td class="tabular-nums fw-semibold text-navy">৳{{ number_format($bed->daily_charge, 2) }} / day</td>
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
                                            <td colspan="4" class="text-center text-muted-hms py-3">
                                                No specific beds allocated directly to this unit. General inpatient ward beds are utilized.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Consulting Doctors for this Department -->
                    <div class="card border shadow-sm p-4 rounded-3 bg-white mb-4">
                        <h3 class="h5 fw-bold text-navy mb-3">
                            <i class="bi bi-person-badge text-teal me-2"></i> Consulting Medical Officers
                        </h3>
                        <div class="row g-3">
                            @forelse($departmentDoctors as $doctor)
                                <div class="col-md-6">
                                    <div class="p-3 rounded border bg-body d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2.5">
                                            <span class="rounded-circle bg-teal-subtle text-teal p-2 d-inline-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                                <i class="bi bi-person-fill fs-5"></i>
                                            </span>
                                            <div>
                                                <h4 class="h6 fw-bold text-navy mb-0">{{ $doctor->name }}</h4>
                                                <span class="d-block text-muted-hms" style="font-size: 0.8rem;">
                                                    {{ $doctor->staffProfile->designation ?? 'Consultant' }}
                                                </span>
                                            </div>
                                        </div>
                                        <a href="{{ route('doctors.show', $doctor->id) }}" class="btn btn-hms-outline-primary btn-hms-sm">
                                            Profile
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <p class="text-muted-hms mb-0">Covered by On-Duty 24/7 Emergency Medical Officer.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Inpatient Visiting & Attendant Guidelines -->
                    <div class="card border shadow-sm p-4 rounded-3 bg-white">
                        <h3 class="h6 fw-bold text-navy mb-2">
                            <i class="bi bi-clock-history text-teal me-2"></i> Inpatient Visiting & Attendant Policy
                        </h3>
                        <p class="text-muted-hms mb-3" style="font-size: 0.875rem; line-height: 1.6;">
                            To maintain sterile, calm, and infection-controlled ward rooms for patient healing:
                        </p>
                        <ul class="text-muted-hms mb-0 d-flex flex-column gap-1.5" style="font-size: 0.85rem;">
                            <li><strong>Visiting Hours:</strong> Strictly between 4:00 PM and 7:00 PM daily.</li>
                            <li><strong>Attendant Limit:</strong> Maximum 1 attendant allowed at bedside per patient at any time.</li>
                            <li><strong>Hygiene:</strong> Hand sanitation is mandatory before entering patient ward rooms.</li>
                        </ul>
                    </div>
                </div>

                <!-- Right: Sticky Sidebar Actions -->
                <div class="col-lg-4">
                    <!-- Book Appointment Box -->
                    <div class="card border-0 shadow-sm p-4 rounded-3 bg-navy text-white mb-4">
                        <span class="badge bg-teal text-white mb-2 px-2.5 py-1 align-self-start">Consultation Booking</span>
                        <h3 class="h5 fw-bold text-white mb-2">Consult in {{ $department->name }}</h3>
                        <p class="text-white-50 mb-3" style="font-size: 0.85rem; line-height: 1.5;">
                            Schedule an appointment or request an OPD consultation token with our attending medical officers.
                        </p>
                        <a href="{{ route('home') }}#appointment" class="btn btn-hms-teal w-100 mb-2">
                            <i class="bi bi-calendar2-check"></i> Book Consultation
                        </a>
                        <span class="d-block text-center text-white-50" style="font-size: 0.75rem;">
                            Confirmed by front desk reception desk
                        </span>
                    </div>

                    <!-- 24/7 Emergency Assistance Box -->
                    <div class="card border shadow-sm p-4 rounded-3 bg-white mb-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="rounded bg-danger-subtle text-danger p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-telephone-fill fs-5"></i>
                            </span>
                            <div>
                                <h4 class="h6 fw-bold text-navy mb-0">Emergency 24/7</h4>
                                <span class="text-muted-hms" style="font-size: 0.75rem;">Shohid Minar Mor, Dinajpur</span>
                            </div>
                        </div>
                        <p class="text-muted-hms mb-3" style="font-size: 0.85rem; line-height: 1.5;">
                            Direct hotline to our on-duty emergency medical officer for walk-in acute trauma or urgent labor care.
                        </p>
                        <a href="tel:{{ $clinicHotline }}" class="btn btn-danger w-100 fw-bold">
                            <i class="bi bi-telephone-fill me-1"></i> Call {{ $clinicHotline }}
                        </a>
                    </div>

                    <!-- Other Clinical Departments -->
                    <div class="card border shadow-sm p-4 rounded-3 bg-white">
                        <h4 class="h6 fw-bold text-navy mb-3">Other Clinical Departments</h4>
                        <div class="d-flex flex-column gap-2">
                            @foreach($allDepartments as $other)
                                <a href="{{ route('departments.show', $other->id) }}" class="text-decoration-none d-flex justify-content-between align-items-center p-2 rounded bg-body text-navy hover-teal border" style="font-size: 0.85rem;">
                                    <span class="fw-semibold">{{ $other->name }}</span>
                                    <span class="badge bg-light text-navy font-monospace">{{ $other->code }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Admin Managed Tag -->
                    <div class="mt-3 p-3 rounded bg-warning-subtle border border-warning-subtle text-center">
                        <span class="d-block fw-semibold text-warning" style="font-size: 0.8rem;">
                            <i class="bi bi-gear-fill me-1"></i> Administrator Controlled
                        </span>
                        <span class="text-muted-hms" style="font-size: 0.75rem;">
                            Department equipment catalogs and procedure tariffs can be updated via the System Settings module.
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
