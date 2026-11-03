<x-layouts.public title="Meghna Clinic and Nursing Home — 24/7 Healthcare & Specialized Surgery in Dinajpur">

    <!-- 1 & 2. Hero Section -->
    <section id="top" class="py-5 bg-white border-bottom hero-section">
        <div class="container-xl">
            <div class="row align-items-center g-4 g-lg-5">
                <div class="col-lg-7">
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill bg-teal-subtle text-teal fw-semibold mb-3 border border-teal-subtle" style="font-size: 0.85rem;">
                        <i class="bi bi-geo-alt-fill"></i>
                        <span>Shohid Minar Mor, Dinajpur &bull; 24/7 Healthcare Facility</span>
                    </div>

                    <h1 class="display-5 fw-bold text-navy lh-sm mb-3">
                        Specialized Clinical Care & <span class="text-teal">24/7 Emergency Medicine</span> in Dinajpur
                    </h1>

                    <p class="lead text-muted-hms mb-4" style="font-size: 1.125rem; line-height: 1.65;">
                        {{ $clinicName }} delivers trusted gynecological care, orthopedic trauma surgery, round-the-clock emergency doctor consultation, and dedicated 10-bed inpatient nursing observation at the heart of Dinajpur.
                    </p>

                    <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                        <a href="tel:{{ $clinicHotline }}" class="btn btn-hms-danger btn-hms-lg shadow-sm">
                            <i class="bi bi-telephone-fill"></i>
                            <span>Emergency Call: {{ $clinicHotline }}</span>
                        </a>

                        <a href="#appointment" class="btn btn-hms-teal btn-hms-lg shadow-sm">
                            <i class="bi bi-calendar2-check"></i>
                            <span>Book Appointment</span>
                        </a>

                        <a href="{{ route('patient.login') }}" class="btn btn-hms-outline-primary btn-hms-lg">
                            <i class="bi bi-person"></i>
                            <span>Patient Portal</span>
                        </a>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-4 text-muted-hms pt-2 border-top" style="font-size: 0.875rem;">
                        <span class="d-flex align-items-center gap-1.5">
                            <i class="bi bi-check-circle-fill text-success"></i> 2 Doctors On Duty 24/7
                        </span>
                        <span class="d-flex align-items-center gap-1.5">
                            <i class="bi bi-check-circle-fill text-success"></i> 10 Inpatient Beds
                        </span>
                        <span class="d-flex align-items-center gap-1.5">
                            <i class="bi bi-check-circle-fill text-success"></i> 6 Shift Nurses (8h Care)
                        </span>
                    </div>
                </div>

                <div class="col-lg-5">
                    <!-- Live Facility Status Bento Widget -->
                    <div class="card border shadow-sm p-4 rounded-3 bg-body">
                        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="rounded bg-teal text-white p-2 d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                    <i class="bi bi-activity"></i>
                                </span>
                                <div>
                                    <span class="d-block fw-bold text-navy lh-1">Live Facility Status</span>
                                    <span class="text-muted-hms" style="font-size: 0.75rem;">Meghna Clinic Roster</span>
                                </div>
                            </div>
                            <x-badge status="success" :dot="true">Live 24/7</x-badge>
                        </div>

                        <div class="d-flex flex-column gap-2.5">
                            <div class="d-flex justify-content-between align-items-center p-2.5 rounded bg-white border">
                                <span class="d-flex align-items-center gap-2">
                                    <i class="bi bi-hospital text-teal fs-5"></i>
                                    <div>
                                        <span class="d-block fw-semibold text-navy" style="font-size: 0.9rem;">Inpatient Ward</span>
                                        <span class="text-muted-hms" style="font-size: 0.75rem;">10 Clinical Beds ({{ $availableBedsCount }} Available)</span>
                                    </div>
                                </span>
                                <span class="badge bg-primary-subtle text-primary fw-bold px-2.5 py-1">
                                    {{ $availableBedsCount }} Available
                                </span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center p-2.5 rounded bg-white border">
                                <span class="d-flex align-items-center gap-2">
                                    <i class="bi bi-person-badge text-teal fs-5"></i>
                                    <div>
                                        <span class="d-block fw-semibold text-navy" style="font-size: 0.9rem;">Emergency Medical Officer</span>
                                        <span class="text-muted-hms" style="font-size: 0.75rem;">Resident Doctor on Shift</span>
                                    </div>
                                </span>
                                <span class="badge bg-success-subtle text-success fw-bold px-2.5 py-1">
                                    24/7 Present
                                </span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center p-2.5 rounded bg-white border">
                                <span class="d-flex align-items-center gap-2">
                                    <i class="bi bi-heart-pulse text-teal fs-5"></i>
                                    <div>
                                        <span class="d-block fw-semibold text-navy" style="font-size: 0.9rem;">Nursing Station</span>
                                        <span class="text-muted-hms" style="font-size: 0.75rem;">6 Nurses in 8-Hour Shifts</span>
                                    </div>
                                </span>
                                <span class="badge bg-teal-subtle text-teal fw-bold px-2.5 py-1">
                                    On Active Duty
                                </span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center p-2.5 rounded bg-white border">
                                <span class="d-flex align-items-center gap-2">
                                    <i class="bi bi-virus text-teal fs-5"></i>
                                    <div>
                                        <span class="d-block fw-semibold text-navy" style="font-size: 0.9rem;">Diagnostic Pathology</span>
                                        <span class="text-muted-hms" style="font-size: 0.75rem;">Internal & Partner Labs</span>
                                    </div>
                                </span>
                                <span class="badge bg-info-subtle text-info fw-bold px-2.5 py-1">
                                    Operational
                                </span>
                            </div>
                        </div>

                        <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center text-muted-hms" style="font-size: 0.75rem;">
                            <span><i class="bi bi-shield-check text-success me-1"></i> Infection-Controlled Ward</span>
                            <span>Shohid Minar Mor</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. Clinic Introduction Section -->
    <section id="about" class="py-5 bg-body">
        <div class="container-xl">
            <div class="row g-4 align-items-center">
                <div class="col-lg-6">
                    <span class="badge bg-teal-subtle text-teal px-3 py-1.5 rounded-pill fw-semibold mb-2" style="font-size: 0.85rem;">
                        About Our Institution
                    </span>
                    <h2 class="h2 fw-bold text-navy mb-3">
                        A Trusted Healthcare Landmark in Dinajpur
                    </h2>
                    <p class="text-muted-hms mb-3" style="line-height: 1.7;">
                        Established at the prominent Shohid Minar Mor intersection in Dinajpur, <strong>{{ $clinicName }}</strong> provides accessible, ethical, and clinically rigorous healthcare to our local community.
                    </p>
                    <p class="text-muted-hms mb-4" style="line-height: 1.7;">
                        With 10 inpatient beds, a team of around 14 healthcare personnel, 2 doctors available at all times (including a dedicated 24-hour Emergency Medical Officer), and 6 shift nurses, we provide immediate stabilization, specialized surgeries, and safe maternal delivery in a caring, family-oriented environment.
                    </p>

                    <div class="d-flex flex-column gap-2.5 mb-4">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-check2-circle text-teal fs-5 mt-0.5"></i>
                            <div>
                                <strong class="text-navy">24/7 Continuous Emergency Triage:</strong> Immediate clinical evaluation with emergency oxygen cylinders, monitoring equipment, and acute wound care.
                            </div>
                        </div>
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-check2-circle text-teal fs-5 mt-0.5"></i>
                            <div>
                                <strong class="text-navy">Autoclave-Sterilized Operation Theatre:</strong> High-grade surgical hygiene standards for cesarean sections, orthopedic reductions, and general surgeries.
                            </div>
                        </div>
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-check2-circle text-teal fs-5 mt-0.5"></i>
                            <div>
                                <strong class="text-navy">Digital Clinical Documentation:</strong> Computerized doctor prescriptions, secure diagnostic report management, and clear transparent invoicing.
                            </div>
                        </div>
                    </div>

                    <a href="#appointment" class="btn btn-hms-primary btn-hms-md">
                        <i class="bi bi-calendar2-check"></i> Request a Consultation
                    </a>
                </div>

                <div class="col-lg-6">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="card border-0 shadow-sm p-4 text-center h-100 rounded-3 bg-white">
                                <span class="d-block display-5 fw-bold text-navy tabular-nums mb-1">{{ $totalBedCapacity }}</span>
                                <span class="fw-semibold text-navy d-block mb-1">Inpatient Beds</span>
                                <span class="text-muted-hms" style="font-size: 0.8rem;">General, GYN, Surgery & Emergency</span>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="card border-0 shadow-sm p-4 text-center h-100 rounded-3 bg-white">
                                <span class="d-block display-5 fw-bold text-teal tabular-nums mb-1">14</span>
                                <span class="fw-semibold text-navy d-block mb-1">Hospital Staff</span>
                                <span class="text-muted-hms" style="font-size: 0.8rem;">Doctors, Nurses, Reception & Support</span>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="card border-0 shadow-sm p-4 text-center h-100 rounded-3 bg-white">
                                <span class="d-block display-5 fw-bold text-success tabular-nums mb-1">24/7</span>
                                <span class="fw-semibold text-navy d-block mb-1">2 Doctors Present</span>
                                <span class="text-muted-hms" style="font-size: 0.8rem;">Continuous On-Duty Emergency Rota</span>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="card border-0 shadow-sm p-4 text-center h-100 rounded-3 bg-white">
                                <span class="d-block display-5 fw-bold text-primary tabular-nums mb-1">6</span>
                                <span class="fw-semibold text-navy d-block mb-1">Shift Nurses</span>
                                <span class="text-muted-hms" style="font-size: 0.8rem;">3 Continuous 8-Hour Rotations</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. Clinical Departments Section -->
    <section id="departments" class="py-5 bg-white border-top border-bottom">
        <div class="container-xl">
            <div class="text-center max-w-700 mx-auto mb-5">
                <span class="badge bg-teal-subtle text-teal px-3 py-1.5 rounded-pill fw-semibold mb-2" style="font-size: 0.85rem;">
                    Core Clinical Units
                </span>
                <h2 class="h2 fw-bold text-navy">Specialized Clinical Departments</h2>
                <p class="text-muted-hms" style="font-size: 1rem;">
                    Organized clinical care delivering specialized consultations, surgical interventions, and observation beds.
                </p>
            </div>

            <div class="row g-4">
                <!-- Gynecology Department -->
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border shadow-sm p-4 rounded-3 hms-card bg-body d-flex flex-column justify-content-between">
                        <div>
                            <div class="rounded-3 bg-teal-subtle text-teal p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 52px; height: 52px;">
                                <i class="bi bi-gender-female fs-3"></i>
                            </div>
                            <h3 class="h5 fw-bold text-navy mb-2">Gynecology & Obstetrics</h3>
                            <p class="text-muted-hms mb-3" style="font-size: 0.875rem; line-height: 1.6;">
                                Maternal checkups, antenatal care, safe institutional normal delivery, cesarean section operations, and female reproductive health treatments.
                            </p>
                        </div>
                        <div class="pt-3 border-top d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                            <span class="text-teal fw-semibold"><i class="bi bi-door-open me-1"></i> 2 Inpatient Beds</span>
                            <span class="badge bg-teal-subtle text-teal">Clinical Care</span>
                        </div>
                    </div>
                </div>

                <!-- Orthopedic Department -->
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border shadow-sm p-4 rounded-3 hms-card bg-body d-flex flex-column justify-content-between">
                        <div>
                            <div class="rounded-3 bg-teal-subtle text-teal p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 52px; height: 52px;">
                                <i class="bi bi-activity fs-3"></i>
                            </div>
                            <h3 class="h5 fw-bold text-navy mb-2">Orthopedic Trauma</h3>
                            <p class="text-muted-hms mb-3" style="font-size: 0.875rem; line-height: 1.6;">
                                Bone fracture reductions, joint management, post-accident emergency stabilization, clinical casting, and musculoskeletal pain treatment.
                            </p>
                        </div>
                        <div class="pt-3 border-top d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                            <span class="text-teal fw-semibold"><i class="bi bi-bandaid me-1"></i> Ward Supported</span>
                            <span class="badge bg-teal-subtle text-teal">Trauma Care</span>
                        </div>
                    </div>
                </div>

                <!-- General Surgery Department -->
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border shadow-sm p-4 rounded-3 hms-card bg-body d-flex flex-column justify-content-between">
                        <div>
                            <div class="rounded-3 bg-teal-subtle text-teal p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 52px; height: 52px;">
                                <i class="bi bi-scissors fs-3"></i>
                            </div>
                            <h3 class="h5 fw-bold text-navy mb-2">General Surgery</h3>
                            <p class="text-muted-hms mb-3" style="font-size: 0.875rem; line-height: 1.6;">
                                Autoclave sterile minor and major surgical procedures, hernia repairs, appendectomy, soft tissue excisions, and surgical wound debridement.
                            </p>
                        </div>
                        <div class="pt-3 border-top d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                            <span class="text-teal fw-semibold"><i class="bi bi-door-open me-1"></i> 2 Inpatient Beds</span>
                            <span class="badge bg-teal-subtle text-teal">Sterile OT</span>
                        </div>
                    </div>
                </div>

                <!-- Emergency Unit -->
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border border-danger-subtle shadow-sm p-4 rounded-3 hms-card bg-body d-flex flex-column justify-content-between">
                        <div>
                            <div class="rounded-3 bg-danger-subtle text-danger p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 52px; height: 52px;">
                                <i class="bi bi-shield-plus fs-3"></i>
                            </div>
                            <h3 class="h5 fw-bold text-navy mb-2">24/7 Emergency Unit</h3>
                            <p class="text-muted-hms mb-3" style="font-size: 0.875rem; line-height: 1.6;">
                                Round-the-clock emergency doctor present with oxygen manifold, acute stabilization, immediate triage, and observation beds.
                            </p>
                        </div>
                        <div class="pt-3 border-top d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                            <span class="text-danger fw-semibold"><i class="bi bi-telephone-fill me-1"></i> 24/7 Walk-in</span>
                            <span class="badge bg-danger text-white">2 Beds (EM-01, 02)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. Doctor & Medical Team Information Section -->
    <section id="doctors" class="py-5 bg-body">
        <div class="container-xl">
            <div class="text-center max-w-700 mx-auto mb-5">
                <span class="badge bg-teal-subtle text-teal px-3 py-1.5 rounded-pill fw-semibold mb-2" style="font-size: 0.85rem;">
                    Verified Clinical Roster
                </span>
                <h2 class="h2 fw-bold text-navy">Our Medical Officers & Nursing Team</h2>
                <p class="text-muted-hms" style="font-size: 1rem;">
                    Experienced medical practitioners dedicated to ethical clinical care and 24/7 bedside observation.
                </p>
            </div>

            <div class="row g-4 justify-content-center">
                <!-- Attending Doctor: Dr. Nasreen Sultana -->
                <div class="col-md-6 col-lg-4">
                    <div class="card border shadow-sm p-4 rounded-3 bg-white h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="rounded-circle bg-teal text-white d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 60px; height: 60px;">
                                    NS
                                </div>
                                <div>
                                    <h3 class="h5 fw-bold text-navy mb-0">Dr. Nasreen Sultana</h3>
                                    <span class="badge bg-teal-subtle text-teal mt-1">Medical Officer</span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <span class="d-block text-muted-hms fw-semibold" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Clinical Focus</span>
                                <span class="text-navy fw-medium" style="font-size: 0.95rem;">Gynecology, Obstetrics & General Clinical Practice</span>
                            </div>

                            <p class="text-muted-hms mb-3" style="font-size: 0.875rem; line-height: 1.6;">
                                Providing comprehensive antenatal care, safe normal deliveries, surgical gynecological consultations, and outpatient general clinical consultations.
                            </p>
                        </div>

                        <div class="pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center mb-3" style="font-size: 0.8rem;">
                                <span class="text-muted-hms"><i class="bi bi-clock text-teal me-1"></i> Consultation Hours:</span>
                                <span class="fw-semibold text-navy">Daily OPD & Rounds</span>
                            </div>
                            <a href="#appointment" class="btn btn-hms-outline-teal w-100 btn-hms-sm">
                                <i class="bi bi-calendar-plus"></i> Book Consultation
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 24/7 Emergency Medical Officer -->
                <div class="col-md-6 col-lg-4">
                    <div class="card border shadow-sm p-4 rounded-3 bg-white h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 60px; height: 60px;">
                                    <i class="bi bi-heart-pulse"></i>
                                </div>
                                <div>
                                    <h3 class="h5 fw-bold text-navy mb-0">Emergency Medical Officer</h3>
                                    <span class="badge bg-danger text-white mt-1">24/7 On-Duty Resident</span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <span class="d-block text-muted-hms fw-semibold" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Clinical Focus</span>
                                <span class="text-navy fw-medium" style="font-size: 0.95rem;">Acute Trauma, Resuscitation & Emergency Triage</span>
                            </div>

                            <p class="text-muted-hms mb-3" style="font-size: 0.875rem; line-height: 1.6;">
                                Available 24 hours daily on continuous rotational roster to handle urgent walk-ins, road accident trauma stabilization, acute distress, and inpatient rounds.
                            </p>
                        </div>

                        <div class="pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center mb-3" style="font-size: 0.8rem;">
                                <span class="text-muted-hms"><i class="bi bi-clock text-danger me-1"></i> Availability:</span>
                                <span class="fw-bold text-danger">24 Hours / 7 Days</span>
                            </div>
                            <a href="tel:{{ $clinicHotline }}" class="btn btn-hms-danger w-100 btn-hms-sm">
                                <i class="bi bi-telephone-fill"></i> Call Emergency Duty
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Nursing Leadership & Shift Sisters -->
                <div class="col-md-6 col-lg-4">
                    <div class="card border shadow-sm p-4 rounded-3 bg-white h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 60px; height: 60px;">
                                    <i class="bi bi-person-heart"></i>
                                </div>
                                <div>
                                    <h3 class="h5 fw-bold text-navy mb-0">Sister Shima Begum</h3>
                                    <span class="badge bg-primary-subtle text-primary mt-1">Senior Staff Nurse / Sister</span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <span class="d-block text-muted-hms fw-semibold" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Nursing Care</span>
                                <span class="text-navy fw-medium" style="font-size: 0.95rem;">Inpatient Ward Care & Medication Administration</span>
                            </div>

                            <p class="text-muted-hms mb-3" style="font-size: 0.875rem; line-height: 1.6;">
                                Leading our team of 6 shift nurses across 8-hour rotations. Ensuring vital signs monitoring, medication records (MAR), post-operative dressing, and compassionate bedside care.
                            </p>
                        </div>

                        <div class="pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center mb-3" style="font-size: 0.8rem;">
                                <span class="text-muted-hms"><i class="bi bi-arrow-repeat text-primary me-1"></i> Shift Rotation:</span>
                                <span class="fw-semibold text-navy">Morning, Evening & Night (8h)</span>
                            </div>
                            <div class="p-2 rounded bg-light text-center text-muted-hms" style="font-size: 0.8rem;">
                                <i class="bi bi-shield-check text-success me-1"></i> Dedicated 10-Bed Inpatient Care
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 6. Healthcare Services Section -->
    <section id="services" class="py-5 bg-white border-top border-bottom">
        <div class="container-xl">
            <div class="text-center max-w-700 mx-auto mb-5">
                <span class="badge bg-teal-subtle text-teal px-3 py-1.5 rounded-pill fw-semibold mb-2" style="font-size: 0.85rem;">
                    Full Clinical Services
                </span>
                <h2 class="h2 fw-bold text-navy">Comprehensive Healthcare Services</h2>
                <p class="text-muted-hms" style="font-size: 1rem;">
                    Equipped facilities and dedicated nursing care to address acute and planned medical requirements.
                </p>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="p-4 rounded-3 border bg-body h-100">
                        <div class="rounded-3 bg-teal text-white p-2.5 d-inline-flex align-items-center justify-content-center mb-3" style="width: 44px; height: 44px;">
                            <i class="bi bi-hospital fs-5"></i>
                        </div>
                        <h3 class="h6 fw-bold text-navy mb-2">10-Bed Inpatient Ward</h3>
                        <p class="text-muted-hms mb-0" style="font-size: 0.875rem; line-height: 1.6;">
                            Clean, temperature-controlled inpatient recovery beds with bedside nursing observation, oxygen cylinders, and attendant accommodation.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="p-4 rounded-3 border bg-body h-100">
                        <div class="rounded-3 bg-teal text-white p-2.5 d-inline-flex align-items-center justify-content-center mb-3" style="width: 44px; height: 44px;">
                            <i class="bi bi-scissors fs-5"></i>
                        </div>
                        <h3 class="h6 fw-bold text-navy mb-2">Operation Theatre (OT)</h3>
                        <p class="text-muted-hms mb-0" style="font-size: 0.875rem; line-height: 1.6;">
                            Autoclaved surgical suite equipped for cesarean delivery, hernia repair, appendectomy, orthopedic reductions, and surgical wound closures.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="p-4 rounded-3 border bg-body h-100">
                        <div class="rounded-3 bg-teal text-white p-2.5 d-inline-flex align-items-center justify-content-center mb-3" style="width: 44px; height: 44px;">
                            <i class="bi bi-virus fs-5"></i>
                        </div>
                        <h3 class="h6 fw-bold text-navy mb-2">Diagnostic Pathology & Partners</h3>
                        <p class="text-muted-hms mb-0" style="font-size: 0.875rem; line-height: 1.6;">
                            Routine laboratory testing including complete blood count (CBC), blood grouping, urine routine, and partnered ultrasonography and X-ray services.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="p-4 rounded-3 border bg-body h-100">
                        <div class="rounded-3 bg-danger text-white p-2.5 d-inline-flex align-items-center justify-content-center mb-3" style="width: 44px; height: 44px;">
                            <i class="bi bi-heart-pulse fs-5"></i>
                        </div>
                        <h3 class="h6 fw-bold text-navy mb-2">Emergency Triage & Oxygen</h3>
                        <p class="text-muted-hms mb-0" style="font-size: 0.875rem; line-height: 1.6;">
                            Rapid patient assessment, oxygen therapy, emergency medication administration, asthma nebulization, and acute post-trauma wound dressings.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="p-4 rounded-3 border bg-body h-100">
                        <div class="rounded-3 bg-teal text-white p-2.5 d-inline-flex align-items-center justify-content-center mb-3" style="width: 44px; height: 44px;">
                            <i class="bi bi-gender-female fs-5"></i>
                        </div>
                        <h3 class="h6 fw-bold text-navy mb-2">Antenatal & Maternal Care</h3>
                        <p class="text-muted-hms mb-0" style="font-size: 0.875rem; line-height: 1.6;">
                            Scheduled pregnancy checkups, fetal heart monitoring, nutritional guidance, safe delivery planning, and postnatal mother-child support.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="p-4 rounded-3 border bg-body h-100">
                        <div class="rounded-3 bg-teal text-white p-2.5 d-inline-flex align-items-center justify-content-center mb-3" style="width: 44px; height: 44px;">
                            <i class="bi bi-receipt fs-5"></i>
                        </div>
                        <h3 class="h6 fw-bold text-navy mb-2">Transparent Inpatient Billing</h3>
                        <p class="text-muted-hms mb-0" style="font-size: 0.875rem; line-height: 1.6;">
                            Itemized daily bed charges, test receipts, and medicine reconciliation without surprise final settlement charges upon discharge.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 7. Why Choose Us Section -->
    <section id="why-us" class="py-5 bg-body">
        <div class="container-xl">
            <div class="text-center max-w-700 mx-auto mb-5">
                <span class="badge bg-teal-subtle text-teal px-3 py-1.5 rounded-pill fw-semibold mb-2" style="font-size: 0.85rem;">
                    Our Clinical Edge
                </span>
                <h2 class="h2 fw-bold text-navy">Why Choose Meghna Clinic & Nursing Home?</h2>
                <p class="text-muted-hms" style="font-size: 1rem;">
                    Institutional commitments that make us a dependable choice for healthcare in Dinajpur.
                </p>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm p-4 rounded-3 bg-white h-100">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="rounded bg-teal-subtle text-teal p-2 d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-person-check fs-4"></i>
                            </span>
                            <h3 class="h6 fw-bold text-navy mb-0">24/7 Doctor On Site</h3>
                        </div>
                        <p class="text-muted-hms mb-0" style="font-size: 0.875rem; line-height: 1.6;">
                            A licensed medical officer is physically present around the clock, guaranteeing immediate clinical evaluation rather than on-call delays.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm p-4 rounded-3 bg-white h-100">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="rounded bg-teal-subtle text-teal p-2 d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-heart-pulse fs-4"></i>
                            </span>
                            <h3 class="h6 fw-bold text-navy mb-0">Dedicated Nurse Ratio</h3>
                        </div>
                        <p class="text-muted-hms mb-0" style="font-size: 0.875rem; line-height: 1.6;">
                            With 6 shift nurses attending to our 10-bed ward across 8-hour rotations, patients receive attentive, prompt bedside attention and timed medication.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm p-4 rounded-3 bg-white h-100">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="rounded bg-teal-subtle text-teal p-2 d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-cash-stack fs-4"></i>
                            </span>
                            <h3 class="h6 fw-bold text-navy mb-0">Transparent Tariffs</h3>
                        </div>
                        <p class="text-muted-hms mb-0" style="font-size: 0.875rem; line-height: 1.6;">
                            Daily bed charges and procedure fees are clearly disclosed in advance with no hidden costs or unexpected charges at discharge clearance.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm p-4 rounded-3 bg-white h-100">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="rounded bg-teal-subtle text-teal p-2 d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-geo-alt fs-4"></i>
                            </span>
                            <h3 class="h6 fw-bold text-navy mb-0">Prime Central Location</h3>
                        </div>
                        <p class="text-muted-hms mb-0" style="font-size: 0.875rem; line-height: 1.6;">
                            Conveniently located at Shohid Minar Mor, Dinajpur, minimizing crucial transit time for emergency ambulances and family members.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm p-4 rounded-3 bg-white h-100">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="rounded bg-teal-subtle text-teal p-2 d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-shield-check fs-4"></i>
                            </span>
                            <h3 class="h6 fw-bold text-navy mb-0">Infection Control Protocols</h3>
                        </div>
                        <p class="text-muted-hms mb-0" style="font-size: 0.875rem; line-height: 1.6;">
                            Dedicated housekeeping team ensuring sanitized ward floors, fresh linens, disposable syringes, and sterilized autoclave surgical instruments.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm p-4 rounded-3 bg-white h-100">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="rounded bg-teal-subtle text-teal p-2 d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-laptop fs-4"></i>
                            </span>
                            <h3 class="h6 fw-bold text-navy mb-0">Digital Patient Records</h3>
                        </div>
                        <p class="text-muted-hms mb-0" style="font-size: 0.875rem; line-height: 1.6;">
                            Integrated hospital software maintaining digital prescriptions, patient self-care portal access, and verified diagnostic laboratory results.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Transparent Bed Rates Table Section -->
    <section id="beds" class="py-5 bg-white border-top border-bottom">
        <div class="container-xl">
            <div class="row g-4 align-items-center">
                <div class="col-lg-6">
                    <span class="badge bg-teal-subtle text-teal px-3 py-1.5 rounded-pill fw-semibold mb-2" style="font-size: 0.85rem;">
                        Transparent Pricing
                    </span>
                    <h2 class="h2 fw-bold text-navy mb-3">10-Bed Inpatient Ward Tariffs</h2>
                    <p class="text-muted-hms mb-4" style="line-height: 1.65;">
                        We believe in transparent healthcare financing. Inpatient daily charges include regular nursing observation, vital checks, and standard ward amenities.
                    </p>

                    <div class="table-responsive">
                        <table class="table table-hms table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Bed Category</th>
                                    <th>Beds Allocated</th>
                                    <th>Daily Rate (BDT)</th>
                                    <th>Included Amenities</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bedCategories as $category)
                                    <tr>
                                        <td class="fw-semibold text-navy">{{ $category['category'] }}</td>
                                        <td>{{ $category['beds'] }}</td>
                                        <td class="fw-bold tabular-nums text-teal">৳ {{ number_format($category['rate'], 0) }} / day</td>
                                        <td class="text-muted-hms" style="font-size: 0.8rem;">{{ $category['amenities'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="p-4 rounded-3 border bg-body">
                        <h3 class="h5 fw-bold text-navy mb-3">
                            <i class="bi bi-info-circle text-teal me-2"></i> Inpatient Admission Guidelines
                        </h3>
                        <p class="text-muted-hms mb-3" style="font-size: 0.875rem; line-height: 1.65;">
                            Inpatient beds are assigned following attending doctor recommendation or emergency stabilization. Bed availability is managed in real-time through our front desk.
                        </p>
                        <div class="d-flex flex-column gap-2 mb-4" style="font-size: 0.85rem;">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <span>Emergency admissions receive immediate bed placement prior to routine formalities.</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <span>One patient attendant pass is provided per admitted bed.</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <span>Discharge summaries require dual clinical clearance from the attending doctor and financial clearance.</span>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <a href="#appointment" class="btn btn-hms-teal btn-hms-sm">
                                <i class="bi bi-calendar2-check"></i> Schedule an OPD Visit
                            </a>
                            <a href="tel:{{ $clinicHotline }}" class="btn btn-hms-outline-primary btn-hms-sm">
                                <i class="bi bi-telephone"></i> Inquire Bed Status
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 8. Emergency Contact Section -->
    <section id="emergency" class="py-5 bg-danger text-white">
        <div class="container-xl">
            <div class="row align-items-center g-4 justify-content-between">
                <div class="col-lg-8">
                    <span class="badge bg-white text-danger px-3 py-1.5 rounded-pill fw-bold mb-2">
                        <i class="bi bi-shield-plus me-1"></i> Immediate Medical Response 24/7
                    </span>
                    <h2 class="display-6 fw-bold mb-2">Need Urgent Medical Attention in Dinajpur?</h2>
                    <p class="lead mb-0 text-white-50" style="font-size: 1.1rem; line-height: 1.6;">
                        Our 24-hour Emergency Medical Officer and nursing staff are on continuous duty at Shohid Minar Mor. Direct walk-ins for trauma, delivery, and acute illness are received immediately without appointment.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="d-flex flex-column align-items-lg-end gap-2">
                        <a href="tel:{{ $clinicHotline }}" class="btn btn-white text-danger btn-hms-lg fw-bold shadow">
                            <i class="bi bi-telephone-fill me-1"></i> Call Hotline: {{ $clinicHotline }}
                        </a>
                        <span class="text-white-50" style="font-size: 0.85rem;">
                            <i class="bi bi-clock-fill me-1"></i> Average Emergency Triage: Immediate
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 9. Appointment Call-to-Action Section -->
    <section id="appointment" class="py-5 bg-body">
        <div class="container-xl">
            <div class="row g-4 justify-content-center">
                <div class="col-lg-8">
                    <div class="card border shadow-sm p-4 p-md-5 rounded-3 bg-white">
                        <div class="text-center mb-4">
                            <span class="badge bg-teal-subtle text-teal px-3 py-1.5 rounded-pill fw-semibold mb-2" style="font-size: 0.85rem;">
                                OPD Consultation
                            </span>
                            <h2 class="h2 fw-bold text-navy">Book a Doctor Appointment</h2>
                            <p class="text-muted-hms mb-0" style="font-size: 0.95rem;">
                                Select your doctor, preferred date, and consultation time slot. Our reception desk will confirm your serial number.
                            </p>
                        </div>

                        <!-- Success Alert with Serial -->
                        @if(session('appointment_success'))
                            <div class="alert alert-success border-0 shadow-sm rounded-3 p-4 text-center mb-4">
                                <i class="bi bi-check-circle-fill text-success fs-1 d-block mb-2"></i>
                                <h4 class="fw-bold text-navy mb-1">Appointment Request Submitted!</h4>
                                <p class="text-muted small mb-3">{{ session('status') }}</p>
                                <div class="bg-white p-3 rounded-2 border d-inline-block text-start small mb-3">
                                    <div><strong>Consulting Doctor:</strong> Dr. {{ session('appointment_doctor') }}</div>
                                    <div><strong>Date & Time:</strong> {{ session('appointment_date') }}</div>
                                    @if(session('appointment_serial'))
                                    <div><strong>Daily Serial:</strong> <span class="badge bg-navy text-white">#{{ session('appointment_serial') }}</span></div>
                                    @endif
                                </div>
                                <div class="small text-muted">
                                    Reference ID: <strong>MCNH-APT-{{ str_pad(session('appointment_id'), 5, '0', STR_PAD_LEFT) }}</strong>
                                </div>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4">
                                <strong class="small d-block mb-1"><i class="bi bi-exclamation-circle me-1"></i> Please check the form errors:</strong>
                                <ul class="small mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Public Appointment Form -->
                        <form action="{{ route('appointments.store') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-navy" for="apt_patient_name" style="font-size: 0.875rem;">
                                        Patient Full Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" id="apt_patient_name" name="guest_name" class="form-control" placeholder="e.g. Most. Rokeya Akhter" value="{{ old('guest_name') }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-navy" for="apt_phone" style="font-size: 0.875rem;">
                                        Mobile Phone Number <span class="text-danger">*</span>
                                    </label>
                                    <input type="tel" id="apt_phone" name="guest_phone" class="form-control" placeholder="e.g. 017XXXXXXXX" value="{{ old('guest_phone') }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-navy" for="apt_department" style="font-size: 0.875rem;">
                                        Clinical Department <span class="text-danger">*</span>
                                    </label>
                                    <select id="apt_department" name="department_id" class="form-select" required onchange="onPublicDeptChange()">
                                        <option value="" selected disabled>Select Department</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-navy" for="pub_apt_doctor" style="font-size: 0.875rem;">
                                        Consulting Doctor <span class="text-danger">*</span>
                                    </label>
                                    <select id="pub_apt_doctor" name="doctor_id" class="form-select" required onchange="onPublicDocOrDateChange()">
                                        <option value="" selected disabled>Select Doctor</option>
                                        @foreach($doctors as $doc)
                                            <option value="{{ $doc->id }}" {{ old('doctor_id') == $doc->id ? 'selected' : '' }}>
                                                {{ $doc->name }} ({{ $doc->staffProfile?->department?->name ?? 'OPD' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-navy" for="pub_apt_date" style="font-size: 0.875rem;">
                                        Preferred Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" id="pub_apt_date" name="appointment_date" class="form-control" min="{{ date('Y-m-d') }}" value="{{ old('appointment_date', date('Y-m-d')) }}" required onchange="onPublicDocOrDateChange()">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-navy" for="pub_apt_email" style="font-size: 0.875rem;">
                                        Email Address (Optional)
                                    </label>
                                    <input type="email" id="pub_apt_email" name="guest_email" class="form-control" placeholder="name@example.com" value="{{ old('guest_email') }}">
                                </div>

                                <!-- Time Slot Chips Selection -->
                                <div class="col-12">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label class="form-label fw-semibold text-navy mb-0" style="font-size: 0.875rem;">
                                            Available Consultation Slot <span class="text-danger">*</span>
                                        </label>
                                        <span id="pub_slot_spinner" class="spinner-border spinner-border-sm text-teal d-none"></span>
                                    </div>

                                    <input type="hidden" name="appointment_time" id="pub_apt_time" value="{{ old('appointment_time') }}" required>

                                    <div id="pub_slots_box" class="border rounded-2 p-3 bg-light">
                                        <p class="text-muted small text-center mb-0" id="pub_slots_empty">
                                            <i class="bi bi-clock me-1"></i> Select department, doctor, and date to view available consultation slots.
                                        </p>
                                        <div id="pub_slots_grid" class="d-flex flex-wrap gap-2 d-none">
                                            <!-- Injected Slots -->
                                        </div>
                                    </div>
                                    <div id="pub_slots_note" class="form-text small"></div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold text-navy" for="pub_apt_concern" style="font-size: 0.875rem;">
                                        Brief Medical Concern (Optional)
                                    </label>
                                    <textarea id="pub_apt_concern" name="reason" class="form-control" rows="2" placeholder="Describe symptoms or reasons for consultation...">{{ old('reason') }}</textarea>
                                </div>

                                <div class="col-12 text-center pt-2">
                                    <button type="submit" class="btn btn-hms-teal btn-hms-lg w-100 shadow-sm">
                                        <i class="bi bi-calendar2-check"></i> Submit Appointment Request
                                    </button>
                                    <span class="d-block text-muted-hms mt-2" style="font-size: 0.8rem;">
                                        <i class="bi bi-telephone text-teal me-1"></i> Or call our reception desk directly: <strong>+880 1711-000004</strong>
                                    </span>
                                </div>
                            </div>
                        </form>

                        <div class="mt-4 pt-3 border-top text-center">
                            <span class="text-muted-hms" style="font-size: 0.875rem;">Already have an account?</span>
                            <a href="{{ route('patient.login') }}" class="fw-semibold text-teal text-decoration-none ms-1">
                                Sign in to Patient Portal &rarr;
                            </a>
                        </div>
                    </div>

<script>
function onPublicDeptChange() {
    const deptElem = document.getElementById('apt_department') || document.getElementById('pub_apt_dept');
    const deptId = deptElem ? deptElem.value : '';
    const docSelect = document.getElementById('pub_apt_doctor');
    if (!deptId) return;

    fetch(`{{ route('api.appointments.doctors') }}?department_id=${deptId}`)
        .then(res => res.json())
        .then(data => {
            docSelect.innerHTML = '<option value="" selected disabled>Select Doctor</option>';
            data.doctors.forEach(doc => {
                const opt = document.createElement('option');
                opt.value = doc.id;
                opt.textContent = `${doc.name} (${doc.designation})`;
                docSelect.appendChild(opt);
            });
            onPublicDocOrDateChange();
        });
}

function onPublicDocOrDateChange() {
    const docId = document.getElementById('pub_apt_doctor').value;
    const date = document.getElementById('pub_apt_date').value;
    const grid = document.getElementById('pub_slots_grid');
    const emptyMsg = document.getElementById('pub_slots_empty');
    const note = document.getElementById('pub_slots_note');
    const spinner = document.getElementById('pub_slot_spinner');
    const timeHidden = document.getElementById('pub_apt_time');

    if (!docId || !date) return;

    spinner.classList.remove('d-none');
    emptyMsg.textContent = 'Checking doctor consultation slots...';
    emptyMsg.classList.remove('d-none');
    grid.classList.add('d-none');

    fetch(`{{ route('api.appointments.slots') }}?doctor_id=${docId}&date=${date}`)
        .then(res => res.json())
        .then(data => {
            spinner.classList.add('d-none');

            if (!data.is_scheduled || !data.slots || data.slots.length === 0) {
                emptyMsg.innerHTML = `<span class="text-danger"><i class="bi bi-x-circle me-1"></i> ${data.message || 'No slots available on this date.'}</span>`;
                emptyMsg.classList.remove('d-none');
                grid.classList.add('d-none');
                note.textContent = '';
                return;
            }

            emptyMsg.classList.add('d-none');
            grid.classList.remove('d-none');
            grid.innerHTML = '';

            let available = 0;
            data.slots.forEach(slot => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-sm py-1.5 px-2.5 rounded-2 text-center ';

                if (slot.is_available) {
                    available++;
                    btn.className += (timeHidden.value === slot.start) ? 'btn-hms-teal shadow-sm' : 'btn-outline-secondary';
                    btn.innerHTML = `<span class="fw-semibold" style="font-size: 0.82rem;">${slot.formatted}</span>`;
                    btn.onclick = function() {
                        timeHidden.value = slot.start;
                        document.querySelectorAll('#pub_slots_grid button').forEach(b => {
                            if (b.classList.contains('btn-hms-teal')) {
                                b.classList.remove('btn-hms-teal', 'shadow-sm');
                                b.classList.add('btn-outline-secondary');
                            }
                        });
                        btn.classList.remove('btn-outline-secondary');
                        btn.classList.add('btn-hms-teal', 'shadow-sm');
                    };
                } else {
                    btn.className += 'btn-light text-muted border opacity-50';
                    btn.disabled = true;
                    btn.innerHTML = `<span class="text-decoration-line-through small">${slot.formatted}</span>`;
                }

                grid.appendChild(btn);
            });

            note.textContent = `${available} slots available for ${data.day_of_week}.`;
        })
        .catch(() => {
            spinner.classList.add('d-none');
            emptyMsg.innerHTML = '<span class="text-danger">Error loading slots.</span>';
        });
}

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('pub_apt_doctor').value && document.getElementById('pub_apt_date').value) {
        onPublicDocOrDateChange();
    }
});
</script>
                </div>
            </div>
        </div>
    </section>

    <!-- 10 & 11. Contact Information & Location Section -->
    <section id="contact" class="py-5 bg-white border-top">
        <div class="container-xl">
            <div class="text-center max-w-700 mx-auto mb-5">
                <span class="badge bg-teal-subtle text-teal px-3 py-1.5 rounded-pill fw-semibold mb-2" style="font-size: 0.85rem;">
                    Get In Touch
                </span>
                <h2 class="h2 fw-bold text-navy">Contact & Location Guide</h2>
                <p class="text-muted-hms" style="font-size: 1rem;">
                    Centrally located in Dinajpur town with convenient transport links and emergency access.
                </p>
            </div>

            <div class="row g-4 align-items-stretch">
                <!-- Contact Details Bento Cards -->
                <div class="col-lg-5">
                    <div class="d-flex flex-column gap-3 h-100">
                        <div class="card border p-3 rounded-3 bg-body">
                            <div class="d-flex align-items-start gap-3">
                                <span class="rounded bg-danger text-white p-2.5 d-inline-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                    <i class="bi bi-telephone-fill fs-5"></i>
                                </span>
                                <div>
                                    <span class="d-block fw-bold text-navy">24/7 Emergency Line</span>
                                    <a href="tel:{{ $clinicHotline }}" class="text-danger fw-bold text-decoration-none fs-5 tabular-nums">
                                        {{ $clinicHotline }}
                                    </a>
                                    <span class="d-block text-muted-hms" style="font-size: 0.8rem;">Direct connection to On-Duty Emergency Doctor</span>
                                </div>
                            </div>
                        </div>

                        <div class="card border p-3 rounded-3 bg-body">
                            <div class="d-flex align-items-start gap-3">
                                <span class="rounded bg-teal text-white p-2.5 d-inline-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                    <i class="bi bi-headset fs-5"></i>
                                </span>
                                <div>
                                    <span class="d-block fw-bold text-navy">Reception Desk & Appointments</span>
                                    <a href="tel:+8801711000004" class="text-teal fw-semibold text-decoration-none tabular-nums">
                                        +880 1711-000004
                                    </a>
                                    <span class="d-block text-muted-hms" style="font-size: 0.8rem;">OPD Tokens & Inpatient Inquiries</span>
                                </div>
                            </div>
                        </div>

                        <div class="card border p-3 rounded-3 bg-body">
                            <div class="d-flex align-items-start gap-3">
                                <span class="rounded bg-primary text-white p-2.5 d-inline-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                    <i class="bi bi-envelope-fill fs-5"></i>
                                </span>
                                <div>
                                    <span class="d-block fw-bold text-navy">Official Email</span>
                                    <a href="mailto:info@meghnaclinic.com" class="text-navy fw-medium text-decoration-none">
                                        info@meghnaclinic.com
                                    </a>
                                    <span class="d-block text-muted-hms" style="font-size: 0.8rem;">General queries & official administration</span>
                                </div>
                            </div>
                        </div>

                        <div class="card border p-3 rounded-3 bg-body">
                            <div class="d-flex align-items-start gap-3">
                                <span class="rounded bg-success text-white p-2.5 d-inline-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                    <i class="bi bi-clock-fill fs-5"></i>
                                </span>
                                <div>
                                    <span class="d-block fw-bold text-navy">Visiting & Clinical Hours</span>
                                    <div class="text-muted-hms" style="font-size: 0.85rem;">
                                        <div><strong>Emergency & Inpatient Ward:</strong> 24 Hours / 7 Days</div>
                                        <div><strong>OPD Consultations:</strong> 9:00 AM – 1:00 PM & 5:00 PM – 9:00 PM</div>
                                        <div><strong>Patient Visiting Hours:</strong> 4:00 PM – 7:00 PM</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 11. Location Map & Directions Card -->
                <div class="col-lg-7" id="location">
                    <div class="card border shadow-sm p-4 rounded-3 bg-body h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-geo-alt-fill text-danger fs-4"></i>
                                    <h3 class="h5 fw-bold text-navy mb-0">Physical Clinic Location</h3>
                                </div>
                                <span class="badge bg-teal-subtle text-teal">Dinajpur Sadar</span>
                            </div>

                            <p class="text-navy fw-semibold mb-2" style="font-size: 1.05rem;">
                                {{ $clinicAddress }}
                            </p>
                            <p class="text-muted-hms mb-4" style="font-size: 0.875rem; line-height: 1.65;">
                                Meghna Clinic and Nursing Home is located directly at the central <strong>Shohid Minar Mor</strong> roundabout in Dinajpur town. The clinic is easily accessible by ambulance, battery-powered auto-rickshaw (Easybike), and personal transport from all quarters of Dinajpur.
                            </p>

                            <!-- Route & Landmark Directions -->
                            <div class="row g-3 mb-4">
                                <div class="col-sm-6">
                                    <div class="p-3 bg-white rounded border">
                                        <span class="d-block fw-bold text-navy mb-1" style="font-size: 0.85rem;">
                                            <i class="bi bi-train-front text-teal me-1"></i> From Railway Station
                                        </span>
                                        <span class="text-muted-hms" style="font-size: 0.8rem;">
                                            Approx. 5–7 minutes by auto-rickshaw via Sadar Hospital Road.
                                        </span>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="p-3 bg-white rounded border">
                                        <span class="d-block fw-bold text-navy mb-1" style="font-size: 0.85rem;">
                                            <i class="bi bi-bus-front text-teal me-1"></i> From Central Bus Terminal
                                        </span>
                                        <span class="text-muted-hms" style="font-size: 0.8rem;">
                                            Approx. 10–12 minutes direct route to Shohid Minar roundabout.
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Stylized Map Landmark Canvas -->
                        <div class="p-4 rounded-3 border bg-white text-center">
                            <div class="d-inline-flex p-3 rounded-circle bg-danger-subtle text-danger mb-2">
                                <i class="bi bi-hospital fs-1"></i>
                            </div>
                            <h4 class="h6 fw-bold text-navy mb-1">Meghna Clinic and Nursing Home</h4>
                            <p class="text-muted-hms mb-3" style="font-size: 0.8rem;">
                                Shohid Minar Mor, Dinajpur, Bangladesh &bull; Ground Floor Emergency Intake
                            </p>
                            <a href="https://maps.google.com/?q=Shohid+Minar+Mor+Dinajpur" target="_blank" rel="noopener noreferrer" class="btn btn-hms-outline-primary btn-hms-sm">
                                <i class="bi bi-box-arrow-up-right"></i> Open Location in Google Maps
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

</x-layouts.public>
