<x-layouts.portal role="admin" title="Register New Department" activeNav="departments">
    <div class="container-fluid p-0">
        <!-- Page Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <x-breadcrumb :items="[
                    'Administration' => url('/previews/admin'),
                    'Departments & Wards' => route('portal.departments.index'),
                    'Register Department' => null
                ]" class="mb-1" />
                <h1 class="h3 fw-bold text-navy mb-0">Register New Department</h1>
                <p class="text-muted-hms mb-0" style="font-size: 0.875rem;">
                    Create a new clinical specialty or administrative operational unit.
                </p>
            </div>
            <div>
                <a href="{{ route('portal.departments.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                    <i class="bi bi-arrow-left"></i>
                    <span>Back to Departments</span>
                </a>
            </div>
        </div>

        <!-- Validation Error Alert -->
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
                <div class="card border-0 shadow-sm rounded-3 bg-white p-4">
                    <div class="d-flex align-items-center gap-3 pb-3 mb-4 border-bottom">
                        <div class="rounded-circle bg-navy bg-opacity-10 text-navy p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-plus-circle-fill text-teal fs-4"></i>
                        </div>
                        <div>
                            <h2 class="h5 fw-bold text-navy mb-0">Department Profile Details</h2>
                            <small class="text-muted">Fields marked with <span class="text-danger">*</span> are mandatory.</small>
                        </div>
                    </div>

                    <form action="{{ route('portal.departments.store') }}" method="POST" id="createDepartmentForm">
                        @csrf

                        <div class="row g-3">
                            <!-- Department Name -->
                            <div class="col-md-8">
                                <label for="name" class="form-label fw-semibold text-navy">
                                    Department Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="name" id="name" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       placeholder="e.g. Cardiology, Pediatrics, Ophthalmology" 
                                       value="{{ old('name') }}" 
                                       required maxlength="100">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Must be a unique descriptive title.</div>
                            </div>

                            <!-- Department Code -->
                            <div class="col-md-4">
                                <label for="code" class="form-label fw-semibold text-navy">
                                    Department Code <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="code" id="code" 
                                       class="form-control font-monospace text-uppercase @error('code') is-invalid @enderror" 
                                       placeholder="e.g. CARD, PED, OPHTH" 
                                       value="{{ old('code') }}" 
                                       required maxlength="10"
                                       style="letter-spacing: 1px;">
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Max 10 uppercase letters/numbers.</div>
                            </div>

                            <!-- Description -->
                            <div class="col-12">
                                <label for="description" class="form-label fw-semibold text-navy">
                                    Operational Description & Scope
                                </label>
                                <textarea name="description" id="description" rows="4" 
                                          class="form-control @error('description') is-invalid @enderror" 
                                          placeholder="Detailed clinical scope, clinical services offered, triage instructions, or operational guidelines...">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Flags -->
                            <div class="col-md-6">
                                <div class="card bg-light border-0 p-3 rounded-3 h-100">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" name="is_clinical" id="is_clinical" value="1" {{ old('is_clinical', '1') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold text-navy" for="is_clinical">
                                            Clinical Department
                                        </label>
                                        <p class="text-muted small mb-0 mt-1">
                                            Enable if this department provides direct patient clinical consultations, emergency care, surgeries, or inpatient bed stays.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card bg-light border-0 p-3 rounded-3 h-100">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold text-navy" for="is_active">
                                            Active Department
                                        </label>
                                        <p class="text-muted small mb-0 mt-1">
                                            Active units are accessible for doctor appointment bookings, patient admissions, and staff assignments.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('portal.departments.index') }}" class="btn btn-light px-4">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary px-4 d-inline-flex align-items-center gap-2">
                                <i class="bi bi-check2-circle"></i>
                                <span>Save Department</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.portal>
