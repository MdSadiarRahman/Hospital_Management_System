<x-layouts.public 
    title="Our Doctors & Medical Officers"
    metaDescription="Meet the qualified medical officers, attending doctors, and nursing team at Meghna Clinic and Nursing Home, Dinajpur. 24/7 emergency medical coverage."
>
    <!-- Page Header & Breadcrumb -->
    <div class="bg-navy text-white py-5">
        <div class="container-xl">
            <x-breadcrumb :items="['Doctors' => null]" class="mb-3 text-white-50" />
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="badge bg-teal text-white px-3 py-1.5 rounded-pill fw-semibold mb-2" style="font-size: 0.85rem;">
                        Medical Roster
                    </span>
                    <h1 class="display-5 fw-bold mb-3 text-white">Our Medical Officers & Clinical Team</h1>
                    <p class="lead text-white-50 mb-0" style="max-width: 720px; line-height: 1.6;">
                        Compassionate, qualified medical practitioners providing continuous 24/7 clinical coverage, outpatient consultations, and surgical care in Dinajpur Sadar.
                    </p>
                </div>
                <div class="col-lg-4 mt-4 mt-lg-0 text-lg-end">
                    <div class="p-3 rounded-3 bg-white bg-opacity-10 d-inline-block text-start border border-white border-opacity-10">
                        <span class="d-block text-white fw-bold mb-1">
                            <i class="bi bi-clock-history text-teal me-1"></i> Continuous Rota Policy
                        </span>
                        <span class="d-block text-white-50" style="font-size: 0.85rem;">
                            Minimum 2 Doctors On Duty 24/7 &bull; 6 Shift Nurses
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- On-Duty 24/7 Doctor Protocol Notice Banner -->
    <section class="py-4 bg-teal-subtle border-bottom">
        <div class="container-xl">
            <div class="row align-items-center g-3">
                <div class="col-md-9">
                    <div class="d-flex align-items-center gap-3">
                        <span class="rounded-circle bg-teal text-white p-2 d-inline-flex align-items-center justify-content-center shrink-0" style="width: 44px; height: 44px;">
                            <i class="bi bi-shield-plus fs-4"></i>
                        </span>
                        <div>
                            <h2 class="h6 fw-bold text-navy mb-0">24/7 Guaranteed Medical Officer On-Site Presence</h2>
                            <p class="text-muted-hms mb-0" style="font-size: 0.85rem;">
                                In compliance with clinical standards, Meghna Clinic and Nursing Home maintains 2 doctors at all times: an attending clinical consultant and an emergency medical officer on site for immediate trauma and labor admissions.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 text-md-end">
                    <a href="tel:{{ $clinicHotline }}" class="btn btn-hms-teal btn-hms-sm">
                        <i class="bi bi-telephone-fill"></i> Emergency Desk
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Doctors Directory Grid -->
    <section class="py-5 bg-body">
        <div class="container-xl">
            <div class="mb-4">
                <h2 class="h4 fw-bold text-navy mb-1">Attending Doctors & Medical Officers</h2>
                <p class="text-muted-hms" style="font-size: 0.9rem;">
                    Profiles of resident doctors and consultants available for consultations and inpatient surgical care.
                </p>
            </div>

            <div class="row g-4 mb-5">
                @forelse($doctors as $doctor)
                    <div class="col-md-6 col-lg-6">
                        <div class="card h-100 border shadow-sm p-4 rounded-3 bg-white hms-card d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-start gap-3 mb-3">
                                    <div class="rounded-circle bg-navy text-white d-inline-flex align-items-center justify-content-center shrink-0" style="width: 64px; height: 64px;">
                                        <i class="bi bi-person-fill fs-2 text-teal"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h3 class="h5 fw-bold text-navy mb-0">{{ $doctor->name }}</h3>
                                            <span class="badge bg-success-subtle text-success fw-bold" style="font-size: 0.75rem;">
                                                <i class="bi bi-check-circle-fill me-1"></i> BMDC Verified
                                            </span>
                                        </div>
                                        <span class="d-block text-teal fw-semibold" style="font-size: 0.85rem;">
                                            {{ $doctor->staffProfile->designation ?? 'Medical Officer / Attending Doctor' }}
                                        </span>
                                        <span class="d-block text-muted-hms" style="font-size: 0.8rem;">
                                            {{ $doctor->staffProfile->department->name ?? 'Clinical Department' }}
                                        </span>
                                    </div>
                                </div>

                                <p class="text-muted-hms mb-3" style="font-size: 0.875rem; line-height: 1.6;">
                                    {{ $doctor->staffProfile->specialization ?? 'General medical care, maternal health, and inpatient surgical recovery observation.' }}
                                </p>

                                <div class="p-3 bg-body rounded border mb-3">
                                    <div class="row g-2" style="font-size: 0.8rem;">
                                        <div class="col-6">
                                            <span class="text-muted-hms d-block">OPD Timings:</span>
                                            <strong class="text-navy">9:00 AM – 1:00 PM</strong>
                                            <span class="d-block text-navy">5:00 PM – 9:00 PM</span>
                                        </div>
                                        <div class="col-6">
                                            <span class="text-muted-hms d-block">Shift Pattern:</span>
                                            <strong class="text-navy text-capitalize">{{ $doctor->staffProfile->shift ?? 'Rotational' }}</strong>
                                            <span class="d-block text-success">Emergency On-Call</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-3 border-top d-flex align-items-center justify-content-between">
                                <span class="text-muted-hms" style="font-size: 0.8rem;">
                                    <i class="bi bi-hospital me-1"></i> Inpatient Ward Rounds Daily
                                </span>
                                <a href="{{ route('doctors.show', $doctor->id) }}" class="btn btn-hms-outline-primary btn-hms-sm">
                                    View Full Profile <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="p-5 text-center bg-white rounded-3 border">
                            <i class="bi bi-person-badge fs-1 text-muted-hms mb-3 d-block"></i>
                            <h3 class="h5 text-navy">No Doctor Profiles Found</h3>
                            <p class="text-muted-hms">Doctor profiles are maintained dynamically in the Staff Management module.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Emergency Rota Officer Card -->
            <div class="card border shadow-sm p-4 rounded-3 bg-white mb-5">
                <div class="row align-items-center g-4">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-danger text-white px-2.5 py-1">24/7 Coverage</span>
                            <span class="badge bg-light text-navy border">Resident Duty Doctor</span>
                        </div>
                        <h3 class="h5 fw-bold text-navy mb-2">On-Duty Emergency Medical Officer (EMO)</h3>
                        <p class="text-muted-hms mb-0" style="font-size: 0.875rem; line-height: 1.6;">
                            Our dedicated emergency rota ensures a licensed MBBS doctor is physically stationed on the ground floor at all hours of the day and night. The EMO performs immediate acute trauma triage, rapid stabilization, wound closure, burn care, and initiates urgent inpatient bed admissions.
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <a href="tel:{{ $clinicHotline }}" class="btn btn-danger btn-hms-sm">
                            <i class="bi bi-telephone-fill me-1"></i> Dial Emergency Doctor
                        </a>
                    </div>
                </div>
            </div>

            <!-- Nursing Leadership & Shift Nurses Section -->
            <div class="card border shadow-sm p-4 rounded-3 bg-white">
                <div class="mb-4">
                    <span class="badge bg-teal-subtle text-teal px-3 py-1.5 rounded-pill fw-semibold mb-2" style="font-size: 0.85rem;">
                        Nursing Care
                    </span>
                    <h3 class="h4 fw-bold text-navy mb-1">Nursing Leadership & Ward Supervision</h3>
                    <p class="text-muted-hms" style="font-size: 0.875rem;">
                        Our 10-bed inpatient ward is staffed by 6 qualified shift nurses operating on 3 continuous 8-hour rotations.
                    </p>
                </div>

                <div class="row g-4">
                    <!-- Sister In-Charge -->
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border bg-body h-100 d-flex align-items-start gap-3">
                            <span class="rounded-circle bg-teal text-white p-2.5 d-inline-flex align-items-center justify-content-center shrink-0" style="width: 52px; height: 52px;">
                                <i class="bi bi-person-hearts fs-3"></i>
                            </span>
                            <div>
                                <span class="badge bg-teal-subtle text-teal mb-1">Ward In-Charge</span>
                                <h4 class="h6 fw-bold text-navy mb-1">Sister Shima Begum</h4>
                                <span class="d-block text-muted-hms" style="font-size: 0.8rem;">
                                    Senior Staff Nurse / Sister In-Charge (Diploma in Nursing & Midwifery)
                                </span>
                                <p class="text-muted-hms mt-2 mb-0" style="font-size: 0.8rem; line-height: 1.5;">
                                    Supervises ward medication administration records (MAR), post-operative vitals, patient hygiene, and aseptic dressing protocols across all 10 clinical beds.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- 8-Hour Rotation Schedule -->
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border bg-body h-100">
                            <h4 class="h6 fw-bold text-navy mb-2">3 Continuous 8-Hour Nurse Shifts</h4>
                            <div class="d-flex flex-column gap-2" style="font-size: 0.8rem;">
                                <div class="d-flex justify-content-between p-2 rounded bg-white border">
                                    <span><strong class="text-navy">Morning Shift:</strong> 8:00 AM – 4:00 PM</span>
                                    <span class="text-teal fw-semibold">2 On-Duty Nurses</span>
                                </div>
                                <div class="d-flex justify-content-between p-2 rounded bg-white border">
                                    <span><strong class="text-navy">Evening Shift:</strong> 4:00 PM – 12:00 AM</span>
                                    <span class="text-teal fw-semibold">2 On-Duty Nurses</span>
                                </div>
                                <div class="d-flex justify-content-between p-2 rounded bg-white border">
                                    <span><strong class="text-navy">Night Shift:</strong> 12:00 AM – 8:00 AM</span>
                                    <span class="text-teal fw-semibold">2 On-Duty Nurses</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Administrative Notice -->
    <section class="py-4 bg-white border-top">
        <div class="container-xl">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle fw-semibold px-2.5 py-1">
                        <i class="bi bi-gear-fill me-1"></i> Admin Controlled
                    </span>
                    <span class="text-muted-hms" style="font-size: 0.85rem;">
                        Medical officer rosters, visiting consultant schedules, and leave replacements are synchronized in the Hospital Staff Portal.
                    </span>
                </div>
                <a href="{{ route('home') }}#appointment" class="btn btn-hms-teal btn-hms-sm">
                    <i class="bi bi-calendar2-check"></i> Book Consultation
                </a>
            </div>
        </div>
    </section>
</x-layouts.public>
