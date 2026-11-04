<x-layouts.portal role="admin" title="Edit Bed — {{ $bed->bed_number }}">
    <div class="container-fluid px-0">
        <!-- Breadcrumb & Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 text-muted-hms" style="font-size: 0.8rem;">
                        <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}" class="text-decoration-none text-muted-hms">Portal</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('portal.beds.index') }}" class="text-decoration-none text-muted-hms">Bed Management</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('portal.beds.show', $bed) }}" class="text-decoration-none text-muted-hms">{{ $bed->bed_number }}</a></li>
                        <li class="breadcrumb-item active text-navy fw-semibold" aria-current="page">Edit</li>
                    </ol>
                </nav>
                <h1 class="h3 fw-bold text-navy mb-0">Edit Bed Specifications</h1>
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('portal.beds.show', $bed) }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                    <i class="bi bi-arrow-left"></i> Cancel
                </a>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <ul class="mb-0 ps-3 small">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-3 bg-white p-4" style="max-width: 800px;">
            <form method="POST" action="{{ route('portal.beds.update', $bed) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-navy fw-semibold small" for="bed_number">
                            Bed Number <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="bed_number" id="bed_number" class="form-control text-uppercase font-monospace @error('bed_number') is-invalid @enderror" value="{{ old('bed_number', $bed->bed_number) }}" required>
                        @error('bed_number')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-navy fw-semibold small" for="room_number">
                            Room Number
                        </label>
                        <input type="text" name="room_number" id="room_number" class="form-control font-monospace @error('room_number') is-invalid @enderror" value="{{ old('room_number', $bed->room_number) }}" placeholder="e.g. Room 102">
                        @error('room_number')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-navy fw-semibold small" for="department_id">
                            Department Assignment
                        </label>
                        <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror">
                            <option value="">General Ward (Common / Unassigned to Dept)</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id', $bed->department_id) == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }} ({{ $dept->code }})
                                </option>
                            @endforeach
                        </select>
                        <span class="text-muted small mt-1 d-block" style="font-size: 0.725rem;">
                            General beds can be used by any department or unassigned patients.
                        </span>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-navy fw-semibold small" for="category">
                            Bed Category <span class="text-danger">*</span>
                        </label>
                        <select name="category" id="category" class="form-select @error('category') is-invalid @enderror" required>
                            <option value="general" {{ old('category', $bed->category) === 'general' ? 'selected' : '' }}>General Bed</option>
                            <option value="department" {{ old('category', $bed->category) === 'department' ? 'selected' : '' }}>Department Specialized Bed</option>
                            <option value="cabin" {{ old('category', $bed->category) === 'cabin' ? 'selected' : '' }}>Private Cabin</option>
                            <option value="post_op" {{ old('category', $bed->category) === 'post_op' ? 'selected' : '' }}>Post-Operative Recovery</option>
                            <option value="emergency" {{ old('category', $bed->category) === 'emergency' ? 'selected' : '' }}>Emergency Observation</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-navy fw-semibold small" for="daily_charge">
                            Daily Tariff (৳) <span class="text-danger">*</span>
                        </label>
                        <input type="number" step="0.01" min="0" name="daily_charge" id="daily_charge" class="form-control font-monospace @error('daily_charge') is-invalid @enderror" value="{{ old('daily_charge', $bed->daily_charge) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-navy fw-semibold small" for="status">
                            Current Status
                        </label>
                        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="available" {{ old('status', $bed->status) === 'available' ? 'selected' : '' }}>Available</option>
                            <option value="occupied" {{ old('status', $bed->status) === 'occupied' ? 'selected' : '' }} {{ $bed->status === 'occupied' ? '' : 'disabled' }}>Occupied (Managed via Admissions)</option>
                            <option value="reserved" {{ old('status', $bed->status) === 'reserved' ? 'selected' : '' }}>Reserved</option>
                            <option value="cleaning" {{ old('status', $bed->status) === 'cleaning' ? 'selected' : '' }}>Cleaning</option>
                            <option value="maintenance" {{ old('status', $bed->status) === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label text-navy fw-semibold small" for="description">
                            Description & Equipment Notes
                        </label>
                        <textarea name="description" id="description" class="form-control" rows="3" placeholder="Medical equipment, oxygen points, suction mechanism, mattress type...">{{ old('description', $bed->description) }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('portal.beds.show', $bed) }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-navy">
                        <i class="bi bi-check2 me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.portal>

