<x-layouts.portal role="admin" title="Edit Department: {{ $department->name }}" activeNav="departments">
    <div class="container-fluid p-0">
        <!-- Page Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <x-breadcrumb :items="[
                    'Administration' => url('/previews/admin'),
                    'Departments & Wards' => route('portal.departments.index'),
                    $department->name => route('portal.departments.show', $department),
                    'Edit Details' => null
                ]" class="mb-1" />
                <h1 class="h3 fw-bold text-navy mb-0">Edit Department Details</h1>
                <p class="text-muted-hms mb-0" style="font-size: 0.875rem;">
                    Update operational configuration, clinical classification, and status for <strong>{{ $department->name }}</strong>.
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('portal.departments.show', $department) }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                    <i class="bi bi-arrow-left"></i>
                    <span>Back to Details</span>
                </a>
            </div>
        </div>

        <!-- Flash & Validation Errors -->
        @if($errors->any())
            <x-alert type="danger" class="mb-4" dismissible>
                <strong class="d-block mb-1">Please correct the following errors:</strong>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
                    <div class="d-flex align-items-center gap-3 pb-3 mb-4 border-bottom">
                        <div class="rounded-circle bg-navy bg-opacity-10 text-navy p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-pencil-square text-teal fs-4"></i>
                        </div>
                        <div>
                            <h2 class="h5 fw-bold text-navy mb-0">Modify Department Specification</h2>
                            <small class="text-muted">Fields marked with <span class="text-danger">*</span> are mandatory.</small>
                        </div>
                    </div>

                    <form action="{{ route('portal.departments.update', $department) }}" method="POST" id="editDepartmentForm">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <!-- Department Name -->
                            <div class="col-md-8">
                                <label for="name" class="form-label fw-semibold text-navy">
                                    Department Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="name" id="name" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $department->name) }}" 
                                       required maxlength="100">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Department Code -->
                            <div class="col-md-4">
                                <label for="code" class="form-label fw-semibold text-navy">
                                    Department Code <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="code" id="code" 
                                       class="form-control font-monospace text-uppercase @error('code') is-invalid @enderror" 
                                       value="{{ old('code', $department->code) }}" 
                                       required maxlength="10"
                                       style="letter-spacing: 1px;">
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="col-12">
                                <label for="description" class="form-label fw-semibold text-navy">
                                    Operational Description & Scope
                                </label>
                                <textarea name="description" id="description" rows="4" 
                                          class="form-control @error('description') is-invalid @enderror">{{ old('description', $department->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Flags -->
                            <div class="col-md-6">
                                <div class="card bg-light border-0 p-3 rounded-3 h-100">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" name="is_clinical" id="is_clinical" value="1" {{ old('is_clinical', $department->is_clinical ? '1' : '0') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold text-navy" for="is_clinical">
                                            Clinical Department
                                        </label>
                                        <p class="text-muted small mb-0 mt-1">
                                            Provides direct outpatient consultations, surgeries, or inpatient bed stays.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card bg-light border-0 p-3 rounded-3 h-100">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="is_active" value="1" {{ old('is_active', $department->is_active ? '1' : '0') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold text-navy" for="is_active">
                                            Active Department
                                        </label>
                                        <p class="text-muted small mb-0 mt-1">
                                            Inactive units cannot receive new appointment bookings or patient admissions.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex align-items-center justify-content-between gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('portal.departments.show', $department) }}" class="btn btn-light px-4">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary px-4 d-inline-flex align-items-center gap-2">
                                <i class="bi bi-save"></i>
                                <span>Update Department</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Danger Zone (Delete / Decommission) -->
                @can('delete', $department)
                    <div class="card border border-danger-subtle shadow-sm rounded-3 bg-white p-4">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div>
                                <h3 class="h6 fw-bold text-danger mb-1">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Danger Zone &bull; Decommission Department
                                </h3>
                                <p class="text-muted small mb-0">
                                    Departments can only be permanently removed if they have no active inpatient admissions, configured beds, or assigned staff.
                                </p>
                            </div>
                            <div>
                                <form action="{{ route('portal.departments.destroy', $department) }}" method="POST"
                                      onsubmit="return confirm('CRITICAL: Are you sure you want to permanently delete the {{ $department->name }} department? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm px-3 d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-trash"></i>
                                        <span>Delete Department</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endcan
            </div>
        </div>
    </div>
</x-layouts.portal>
