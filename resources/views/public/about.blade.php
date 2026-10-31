<x-layouts.public 
    title="About Us"
    metaDescription="Learn about Meghna Clinic and Nursing Home in Shohid Minar Mor, Dinajpur. Serving the community with 10 inpatient beds, 14 medical personnel, 2 doctors 24/7, and 6 shift nurses."
>
    <!-- Page Header & Breadcrumb -->
    <div class="bg-navy text-white py-5">
        <div class="container-xl">
            <x-breadcrumb :items="['About Us' => null]" class="mb-3 text-white-50" />
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="badge bg-teal text-white px-3 py-1.5 rounded-pill fw-semibold mb-2" style="font-size: 0.85rem;">
                        Institutional Overview
                    </span>
                    <h1 class="display-5 fw-bold mb-3 text-white">About Meghna Clinic & Nursing Home</h1>
                    <p class="lead text-white-50 mb-0" style="max-width: 720px; line-height: 1.6;">
                        A dependable healthcare landmark located at the central Shohid Minar Mor intersection in Dinajpur Sadar, delivering round-the-clock emergency care, specialized surgery, and compassionate inpatient nursing.
                    </p>
                </div>
                <div class="col-lg-4 mt-4 mt-lg-0 text-lg-end">
                    <div class="p-3 rounded-3 bg-white bg-opacity-10 d-inline-block text-start border border-white border-opacity-10">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-geo-alt-fill text-teal fs-5"></i>
                            <span class="fw-bold text-white">Shohid Minar Mor, Dinajpur</span>
                        </div>
                        <span class="d-block text-white-50" style="font-size: 0.8rem;">Govt. Reg. Private Healthcare Facility</span>
                        <span class="d-block text-teal fw-semibold mt-1" style="font-size: 0.85rem;">
                            <i class="bi bi-telephone-fill me-1"></i> Hotline: {{ $clinicHotline }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Facility Overview Bento Metrics -->
    <section class="py-5 bg-white border-bottom">
        <div class="container-xl">
            <div class="row g-4">
                <div class="col-6 col-md-3">
                    <div class="card h-100 border shadow-sm p-4 text-center rounded-3 bg-body">
                        <span class="display-5 fw-bold text-navy tabular-nums mb-1">{{ $totalBedCapacity }}</span>
                        <span class="fw-bold text-navy d-block">Inpatient Beds</span>
                        <span class="text-muted-hms" style="font-size: 0.8rem;">General, Maternity, Surgical & Emergency</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card h-100 border shadow-sm p-4 text-center rounded-3 bg-body">
                        <span class="display-5 fw-bold text-teal tabular-nums mb-1">{{ $totalStaffEstimate }}</span>
                        <span class="fw-bold text-navy d-block">Staff Members</span>
                        <span class="text-muted-hms" style="font-size: 0.8rem;">Clinical, Nursing, Admin & Support</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card h-100 border shadow-sm p-4 text-center rounded-3 bg-body">
                        <span class="display-5 fw-bold text-success tabular-nums mb-1">24/7</span>
                        <span class="fw-bold text-navy d-block">2 Doctors Present</span>
                        <span class="text-muted-hms" style="font-size: 0.8rem;">Consultant & Emergency Officer on duty</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card h-100 border shadow-sm p-4 text-center rounded-3 bg-body">
                        <span class="display-5 fw-bold text-primary tabular-nums mb-1">6</span>
                        <span class="fw-bold text-navy d-block">Shift Nurses</span>
                        <span class="text-muted-hms" style="font-size: 0.8rem;">Continuous 3 x 8-Hour Rotations</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main About Content -->
    <section class="py-5 bg-body">
        <div class="container-xl">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <span class="badge bg-teal-subtle text-teal px-3 py-1.5 rounded-pill fw-semibold mb-2" style="font-size: 0.85rem;">
                        Our Foundation
                    </span>
                    <h2 class="h2 fw-bold text-navy mb-3">Serving the People of Dinajpur with Integrity</h2>
                    <p class="text-muted-hms mb-3" style="line-height: 1.7;">
                        <strong>{{ $clinicName }}</strong> is a dedicated private healthcare facility situated at Shohid Minar Mor, Dinajpur. Founded with a vision to make clinical care accessible, prompt, and ethical, our clinic provides focused inpatient hospital stays, sterile surgical operations, and 24/7 emergency medical stabilization.
                    </p>
                    <p class="text-muted-hms mb-4" style="line-height: 1.7;">
                        Unlike large overcrowded regional hospitals where patients often experience long waiting times, our 10-bed facility provides personalized medical attention. With 2 resident doctors accessible around the clock and 6 qualified shift nurses under the direct supervision of Sister Shima Begum, every patient receives watchful observation and hygienic care.
                    </p>

                    <div class="card border p-4 rounded-3 bg-white shadow-sm mb-4">
                        <h3 class="h6 fw-bold text-navy mb-3">
                            <i class="bi bi-check2-circle text-teal me-2"></i> Core Clinical Highlights
                        </h3>
                        <ul class="list-unstyled mb-0 d-flex flex-column gap-2 text-muted-hms" style="font-size: 0.9rem;">
                            <li><i class="bi bi-dot text-teal fs-4 align-middle"></i> <strong>Gynecology & Obstetrics:</strong> Antenatal counseling, safe normal delivery, and cesarean sections directed by Dr. Nasreen Sultana.</li>
                            <li><i class="bi bi-dot text-teal fs-4 align-middle"></i> <strong>Orthopedic Surgery & Trauma:</strong> Emergency bone fracture stabilization, closed reduction, and joint care.</li>
                            <li><i class="bi bi-dot text-teal fs-4 align-middle"></i> <strong>Inpatient Observation Ward:</strong> 10 monitored clinical beds with dedicated oxygen cylinders, vital monitors, and suction apparatus.</li>
                            <li><i class="bi bi-dot text-teal fs-4 align-middle"></i> <strong>24/7 Emergency Triage:</strong> Immediate treatment of acute medical distress, wound debridement, and continuous medical oversight.</li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="p-4 rounded-3 border bg-white shadow-sm">
                        <h3 class="h5 fw-bold text-navy mb-4">Our Mission & Principles</h3>

                        <div class="d-flex align-items-start gap-3 mb-4">
                            <span class="rounded bg-teal-subtle text-teal p-3 d-inline-flex align-items-center justify-content-center shrink-0" style="width: 48px; height: 48px;">
                                <i class="bi bi-bullseye fs-4"></i>
                            </span>
                            <div>
                                <h4 class="h6 fw-bold text-navy mb-1">Our Mission</h4>
                                <p class="text-muted-hms mb-0" style="font-size: 0.875rem; line-height: 1.6;">
                                    To provide high-quality, compassionate, and affordable clinical services to families in Dinajpur through ethical medical practices, modern sterilization, and responsive bedside nursing.
                                </p>
                            </div>
                        </div>

                        <div class="d-flex align-items-start gap-3 mb-4">
                            <span class="rounded bg-primary-subtle text-primary p-3 d-inline-flex align-items-center justify-content-center shrink-0" style="width: 48px; height: 48px;">
                                <i class="bi bi-eye fs-4"></i>
                            </span>
                            <div>
                                <h4 class="h6 fw-bold text-navy mb-1">Our Vision</h4>
                                <p class="text-muted-hms mb-0" style="font-size: 0.875rem; line-height: 1.6;">
                                    To stand as the most trusted private community clinic and nursing home in Dinajpur Sadar, recognized for clean wards, medical competence, and prompt emergency intervention.
                                </p>
                            </div>
                        </div>

                        <div class="d-flex align-items-start gap-3">
                            <span class="rounded bg-success-subtle text-success p-3 d-inline-flex align-items-center justify-content-center shrink-0" style="width: 48px; height: 48px;">
                                <i class="bi bi-shield-check fs-4"></i>
                            </span>
                            <div>
                                <h4 class="h6 fw-bold text-navy mb-1">Core Healthcare Values</h4>
                                <p class="text-muted-hms mb-0" style="font-size: 0.875rem; line-height: 1.6;">
                                    Patient dignity, rigorous sterilization of surgical equipment, complete transparency in medical billing, and strict clinical confidentiality.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Administrative Notice & Governance Block -->
    <section class="py-5 bg-white border-top border-bottom">
        <div class="container-xl">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle fw-semibold px-2.5 py-1">
                            <i class="bi bi-gear-fill me-1"></i> Admin Managed Content
                        </span>
                        <span class="text-muted-hms" style="font-size: 0.8rem;">System Governance Module</span>
                    </div>
                    <h3 class="h5 fw-bold text-navy mb-2">Institutional Licensing & Governance Records</h3>
                    <p class="text-muted-hms mb-0" style="font-size: 0.9rem; line-height: 1.6;">
                        Meghna Clinic and Nursing Home operates in full compliance with the guidelines established by the Directorate General of Health Services (DGHS) and the Bangladesh Medical & Dental Council (BMDC). Institutional accreditation certificates, renewal audits, and official historical archives are managed by the Super Administrator via the Hospital Management System governance panel.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="{{ route('departments') }}" class="btn btn-hms-teal me-2 mb-2">
                        <i class="bi bi-hospital"></i> Explore Departments
                    </a>
                    <a href="{{ route('contact') }}" class="btn btn-hms-outline-primary mb-2">
                        <i class="bi bi-geo-alt"></i> Visiting & Directions
                    </a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
