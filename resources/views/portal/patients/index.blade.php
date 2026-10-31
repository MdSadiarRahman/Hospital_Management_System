<x-layouts.portal role="reception" title="Patient Registry">
    <div class="container-fluid px-0">
        <!-- Page Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 text-muted-hms" style="font-size: 0.8rem;">
                        <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}" class="text-decoration-none text-muted-hms">Portal</a></li>
                        <li class="breadcrumb-item active text-navy fw-semibold" aria-current="page">Patient Registry</li>
                    </ol>
                </nav>
                <h1 class="h3 fw-bold text-navy mb-0">Hospital Patient Master Registry</h1>
                <p class="text-muted-hms mb-0" style="font-size: 0.875rem;">
                    Centralized patient intake, identity verification, demographic management, and clinical records linking.
                </p>
            </div>

            <div class="d-flex align-items-center gap-2">
                @can('create', App\Models\Patient::class)
                    <a href="{{ route('portal.patients.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 shadow-sm">
                        <i class="bi bi-person-plus-fill"></i>
                        <span>Register New Patient</span>
                    </a>
                @endcan
            </div>
        </div>

        <!-- Alert messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4 d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Bento KPI Metrics -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted-hms text-uppercase fw-semibold" style="font-size: 0.725rem;">Total Registered</span>
                            <h3 class="fw-bold text-navy mb-0 mt-1">{{ number_format($totalPatients) }}</h3>
                        </div>
                        <span class="rounded-3 bg-primary bg-opacity-10 text-primary p-2.5 d-inline-flex">
                            <i class="bi bi-people-fill fs-4"></i>
                        </span>
                    </div>
                    <div class="mt-2 text-muted" style="font-size: 0.75rem;">
                        Lifetime clinic master records
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted-hms text-uppercase fw-semibold" style="font-size: 0.725rem;">New Today</span>
                            <h3 class="fw-bold text-success mb-0 mt-1">{{ number_format($newToday) }}</h3>
                        </div>
                        <span class="rounded-3 bg-success bg-opacity-10 text-success p-2.5 d-inline-flex">
                            <i class="bi bi-calendar-plus fs-4"></i>
                        </span>
                    </div>
                    <div class="mt-2 text-muted" style="font-size: 0.75rem;">
                        Registered on {{ date('d M Y') }}
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted-hms text-uppercase fw-semibold" style="font-size: 0.725rem;">Gender Breakdown</span>
                            <h4 class="fw-bold text-navy mb-0 mt-1">
                                <span class="text-primary">{{ $maleCount }}</span> <span class="text-muted fw-normal" style="font-size: 0.9rem;">M</span>
                                <span class="mx-1 text-muted-hms">/</span>
                                <span class="text-danger">{{ $femaleCount }}</span> <span class="text-muted fw-normal" style="font-size: 0.9rem;">F</span>
                            </h4>
                        </div>
                        <span class="rounded-3 bg-info bg-opacity-10 text-info p-2.5 d-inline-flex">
                            <i class="bi bi-gender-ambiguous fs-4"></i>
                        </span>
                    </div>
                    <div class="mt-2 text-muted" style="font-size: 0.75rem;">
                        Male vs Female distribution
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted-hms text-uppercase fw-semibold" style="font-size: 0.725rem;">Online Accounts</span>
                            <h3 class="fw-bold text-teal mb-0 mt-1">{{ number_format($verifiedCount) }}</h3>
                        </div>
                        <span class="rounded-3 bg-teal bg-opacity-10 text-teal p-2.5 d-inline-flex">
                            <i class="bi bi-patch-check-fill fs-4"></i>
                        </span>
                    </div>
                    <div class="mt-2 text-muted" style="font-size: 0.75rem;">
                        Verified self-service portals
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar Card -->
        <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('portal.patients.index') }}" class="row g-2 align-items-center">
                    <!-- Search Input -->
                    <div class="col-12 col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   class="form-control bg-light border-start-0" 
                                   placeholder="Search by ID (MCNH-...), Name, Phone, NID...">
                        </div>
                    </div>

                    <!-- Gender Filter -->
                    <div class="col-6 col-md-2">
                        <select name="gender" class="form-select bg-light" aria-label="Filter by Gender">
                            <option value="">All Genders</option>
                            <option value="male" @selected(request('gender') === 'male')>Male</option>
                            <option value="female" @selected(request('gender') === 'female')>Female</option>
                            <option value="other" @selected(request('gender') === 'other')>Other</option>
                        </select>
                    </div>

                    <!-- Blood Group Filter -->
                    <div class="col-6 col-md-2">
                        <select name="blood_group" class="form-select bg-light" aria-label="Filter by Blood Group">
                            <option value="">All Blood Groups</option>
                            @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                                <option value="{{ $bg }}" @selected(request('blood_group') === $bg)>{{ $bg }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sort -->
                    <div class="col-6 col-md-2">
                        <select name="sort" class="form-select bg-light" aria-label="Sort Order">
                            <option value="latest" @selected(request('sort') === 'latest')>Newest First</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>Oldest First</option>
                            <option value="patient_code" @selected(request('sort') === 'patient_code')>Patient ID</option>
                            <option value="name_asc" @selected(request('sort') === 'name_asc')>Name (A-Z)</option>
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="col-6 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-teal w-100 d-inline-flex align-items-center justify-content-center gap-1">
                            <i class="bi bi-funnel-fill"></i> Filter
                        </button>
                        @if(request()->hasAny(['search', 'gender', 'blood_group', 'sort', 'date_from', 'date_to', 'is_verified']))
                            <a href="{{ route('portal.patients.index') }}" class="btn btn-outline-secondary" title="Reset Filters">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Patients Master Table Card -->
        <div class="card border-0 shadow-sm rounded-3 bg-white">
            <div class="card-body p-0">
                @if($patients->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr style="font-size: 0.775rem;" class="text-uppercase text-muted-hms">
                                    <th class="ps-3 py-3">Patient ID</th>
                                    <th class="py-3">Full Name & Demographics</th>
                                    <th class="py-3">Contact Information</th>
                                    <th class="py-3">Blood Group</th>
                                    <th class="py-3">Emergency Contact</th>
                                    <th class="py-3">Registered Date</th>
                                    <th class="text-end pe-3 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($patients as $patient)
                                    <tr>
                                        <!-- Patient ID -->
                                        <td class="ps-3">
                                            <a href="{{ route('portal.patients.show', $patient->id) }}" class="fw-bold text-navy font-monospace text-decoration-none">
                                                {{ $patient->patient_code }}
                                            </a>
                                            @if($patient->is_verified)
                                                <span class="badge bg-teal-subtle text-teal border border-teal-subtle rounded-pill ms-1" style="font-size: 0.675rem;" title="Identity Verified">
                                                    <i class="bi bi-check2-all"></i> Verified
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Name & Demographics -->
                                        <td>
                                            <div class="fw-semibold text-navy">
                                                <a href="{{ route('portal.patients.show', $patient->id) }}" class="text-navy text-decoration-none">
                                                    {{ $patient->name }}
                                                </a>
                                            </div>
                                            <div class="text-muted" style="font-size: 0.775rem;">
                                                <span>{{ $patient->formatted_age }}</span> &bull; 
                                                <span class="text-capitalize">{{ $patient->gender }}</span>
                                            </div>
                                        </td>

                                        <!-- Contact Info -->
                                        <td>
                                            <div class="d-flex align-items-center gap-1.5 font-monospace" style="font-size: 0.85rem;">
                                                <i class="bi bi-telephone text-muted"></i>
                                                <span>{{ $patient->phone }}</span>
                                            </div>
                                            @if($patient->email)
                                                <div class="text-muted text-truncate" style="font-size: 0.75rem; max-width: 170px;">
                                                    <i class="bi bi-envelope"></i> {{ $patient->email }}
                                                </div>
                                            @endif
                                        </td>

                                        <!-- Blood Group -->
                                        <td>
                                            @if($patient->blood_group)
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle fw-bold px-2 py-1">
                                                    <i class="bi bi-droplet-fill me-0.5"></i> {{ $patient->blood_group }}
                                                </span>
                                            @else
                                                <span class="text-muted" style="font-size: 0.8rem;">—</span>
                                            @endif
                                        </td>

                                        <!-- Primary Emergency Contact -->
                                        <td>
                                            @if($patient->primaryContact)
                                                <div class="fw-semibold text-navy" style="font-size: 0.825rem;">
                                                    {{ $patient->primaryContact->name }}
                                                </div>
                                                <div class="text-muted" style="font-size: 0.75rem;">
                                                    <span class="badge bg-light text-dark border">{{ $patient->primaryContact->relationship }}</span>
                                                    <span class="font-monospace ms-1">{{ $patient->primaryContact->phone }}</span>
                                                </div>
                                            @else
                                                <span class="text-muted" style="font-size: 0.775rem;">Not Registered</span>
                                            @endif
                                        </td>

                                        <!-- Registration Date -->
                                        <td>
                                            <div style="font-size: 0.825rem;" class="text-navy">
                                                {{ $patient->registration_date ? $patient->registration_date->format('d M Y') : $patient->created_at->format('d M Y') }}
                                            </div>
                                            <small class="text-muted" style="font-size: 0.725rem;">
                                                {{ $patient->created_at->diffForHumans() }}
                                            </small>
                                        </td>

                                        <!-- Actions -->
                                        <td class="text-end pe-3">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('portal.patients.show', $patient->id) }}" class="btn btn-outline-primary" title="View Patient Profile">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @can('update', $patient)
                                                    <a href="{{ route('portal.patients.edit', $patient->id) }}" class="btn btn-outline-secondary" title="Edit Demographics">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                @endcan
                                                @can('print', $patient)
                                                    <a href="{{ route('portal.patients.print-card', $patient->id) }}" target="_blank" class="btn btn-outline-teal" title="Print Clinic ID Slip">
                                                        <i class="bi bi-printer"></i>
                                                    </a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($patients->hasPages())
                        <div class="p-3 border-top">
                            {{ $patients->links() }}
                        </div>
                    @endif
                @else
                    <div class="p-5 text-center">
                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center p-4 mb-3">
                            <i class="bi bi-person-slash fs-1 text-muted"></i>
                        </div>
                        <h4 class="h5 fw-bold text-navy">No Patients Found</h4>
                        <p class="text-muted-hms mb-3" style="max-width: 480px; margin: 0 auto; font-size: 0.875rem;">
                            No patient master records matched your search parameters. Try clearing the search or register a new patient intake.
                        </p>
                        @can('create', App\Models\Patient::class)
                            <a href="{{ route('portal.patients.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
                                <i class="bi bi-person-plus-fill"></i> Register New Patient
                            </a>
                        @endcan
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.portal>
