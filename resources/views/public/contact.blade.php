<x-layouts.public 
    title="Contact & Location"
    metaDescription="Contact Meghna Clinic and Nursing Home at Shohid Minar Mor, Dinajpur. 24/7 emergency hotline, OPD token desk, visiting hours, and transport directions."
>
    <!-- Page Header & Breadcrumb -->
    <div class="bg-navy text-white py-5">
        <div class="container-xl">
            <x-breadcrumb :items="['Contact & Location' => null]" class="mb-3 text-white-50" />
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="badge bg-teal text-white px-3 py-1.5 rounded-pill fw-semibold mb-2" style="font-size: 0.85rem;">
                        Communication & Access
                    </span>
                    <h1 class="display-5 fw-bold mb-3 text-white">Contact & Location Guide</h1>
                    <p class="lead text-white-50 mb-0" style="max-width: 720px; line-height: 1.6;">
                        Centrally located at Shohid Minar Mor in Dinajpur Sadar with immediate emergency ambulance access and dedicated reception desks.
                    </p>
                </div>
                <div class="col-lg-4 mt-4 mt-lg-0 text-lg-end">
                    <div class="p-3 rounded-3 bg-white bg-opacity-10 d-inline-block text-start border border-white border-opacity-10">
                        <span class="d-block text-white fw-bold mb-1">
                            <i class="bi bi-clock-fill text-teal me-1"></i> Emergency Intake 24/7
                        </span>
                        <span class="d-block text-white-50" style="font-size: 0.85rem;">
                            Direct Line: <strong class="text-teal">{{ $clinicHotline }}</strong>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Cards & Form -->
    <section class="py-5 bg-body">
        <div class="container-xl">
            <!-- 4 Bento Cards for Contact Methods -->
            <div class="row g-4 mb-5">
                <!-- 1. Emergency 24/7 -->
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border shadow-sm p-4 rounded-3 bg-white text-center">
                        <div class="rounded-circle bg-danger-subtle text-danger p-3 d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 56px; height: 56px;">
                            <i class="bi bi-telephone-fill fs-3"></i>
                        </div>
                        <h2 class="h6 fw-bold text-navy mb-1">24/7 Emergency Line</h2>
                        <a href="tel:{{ $clinicHotline }}" class="d-block fw-bold text-danger text-decoration-none fs-5 mb-1">
                            {{ $clinicHotline }}
                        </a>
                        <span class="text-muted-hms" style="font-size: 0.8rem;">Immediate connection to On-Duty Doctor</span>
                    </div>
                </div>

                <!-- 2. Reception & Appointments -->
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border shadow-sm p-4 rounded-3 bg-white text-center">
                        <div class="rounded-circle bg-teal-subtle text-teal p-3 d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 56px; height: 56px;">
                            <i class="bi bi-calendar2-check fs-3"></i>
                        </div>
                        <h2 class="h6 fw-bold text-navy mb-1">Reception Desk</h2>
                        <span class="d-block fw-bold text-navy fs-5 mb-1">
                            +880 1711-000004
                        </span>
                        <span class="text-muted-hms" style="font-size: 0.8rem;">OPD Tokens & Inpatient Admissions</span>
                    </div>
                </div>

                <!-- 3. Official Email -->
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border shadow-sm p-4 rounded-3 bg-white text-center">
                        <div class="rounded-circle bg-primary-subtle text-primary p-3 d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 56px; height: 56px;">
                            <i class="bi bi-envelope-fill fs-3"></i>
                        </div>
                        <h2 class="h6 fw-bold text-navy mb-1">Official Email</h2>
                        <a href="mailto:info@meghnaclinic.com" class="d-block fw-semibold text-teal text-decoration-none mb-1">
                            info@meghnaclinic.com
                        </a>
                        <span class="text-muted-hms" style="font-size: 0.8rem;">Administrative & billing correspondence</span>
                    </div>
                </div>

                <!-- 4. Visiting Hours -->
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border shadow-sm p-4 rounded-3 bg-white text-center">
                        <div class="rounded-circle bg-success-subtle text-success p-3 d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 56px; height: 56px;">
                            <i class="bi bi-clock-history fs-3"></i>
                        </div>
                        <h2 class="h6 fw-bold text-navy mb-1">Visiting Hours</h2>
                        <strong class="d-block text-navy fs-5 mb-1">
                            4:00 PM – 7:00 PM
                        </strong>
                        <span class="text-muted-hms" style="font-size: 0.8rem;">Daily ward visiting policy</span>
                    </div>
                </div>
            </div>

            <!-- Two-Column Form & Transport Guide -->
            <div class="row g-5">
                <!-- Left: Public Inquiry Form -->
                <div class="col-lg-6">
                    <div class="card border shadow-sm p-4 rounded-3 bg-white">
                        <h2 class="h4 fw-bold text-navy mb-2">Send an Inquiry to Reception</h2>
                        <p class="text-muted-hms mb-4" style="font-size: 0.875rem;">
                            For non-urgent queries regarding doctor chambers, bed availability, or diagnostic services, fill out the form below.
                        </p>

                        <form action="{{ route('contact') }}" method="GET" class="d-flex flex-column gap-3">
                            <div>
                                <label for="contact_name" class="form-label text-navy fw-semibold" style="font-size: 0.85rem;">Full Name</label>
                                <input type="text" id="contact_name" name="name" class="form-control" placeholder="e.g. Abdul Karim" required>
                            </div>

                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label for="contact_phone" class="form-label text-navy fw-semibold" style="font-size: 0.85rem;">Phone Number</label>
                                    <input type="tel" id="contact_phone" name="phone" class="form-control" placeholder="017XXXXXXXX" required>
                                </div>
                                <div class="col-sm-6">
                                    <label for="contact_email" class="form-label text-navy fw-semibold" style="font-size: 0.85rem;">Email Address (Optional)</label>
                                    <input type="email" id="contact_email" name="email" class="form-control" placeholder="name@example.com">
                                </div>
                            </div>

                            <div>
                                <label for="contact_dept" class="form-label text-navy fw-semibold" style="font-size: 0.85rem;">Department of Interest</label>
                                <select id="contact_dept" name="department" class="form-select">
                                    <option value="">General Inquiries & Admissions</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="contact_msg" class="form-label text-navy fw-semibold" style="font-size: 0.85rem;">Message</label>
                                <textarea id="contact_msg" name="message" class="form-control" rows="4" placeholder="How can our clinic team assist you?" required></textarea>
                            </div>

                            <button type="submit" class="btn btn-hms-teal w-100 py-2.5">
                                <i class="bi bi-send-fill me-1"></i> Send Inquiry to Clinic
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Right: Physical Clinic Location & Transport Guidelines -->
                <div class="col-lg-6">
                    <div class="card border shadow-sm p-4 rounded-3 bg-white h-100 d-flex flex-column justify-content-between">
                        <div>
                            <span class="badge bg-teal-subtle text-teal px-3 py-1.5 rounded-pill fw-semibold mb-2" style="font-size: 0.85rem;">
                                Physical Location
                            </span>
                            <h2 class="h4 fw-bold text-navy mb-2">Central Dinajpur Landmark</h2>
                            <p class="fw-semibold text-navy mb-2">
                                <i class="bi bi-geo-alt-fill text-teal me-1"></i> {{ $clinicAddress }}
                            </p>
                            <p class="text-muted-hms mb-4" style="font-size: 0.875rem; line-height: 1.65;">
                                Meghna Clinic and Nursing Home is located directly at the central <strong>Shohid Minar Mor</strong> roundabout in Dinajpur town. The clinic has direct ground-floor stretcher and ambulance access.
                            </p>

                            <!-- Route & Landmark Directions -->
                            <div class="row g-3 mb-4">
                                <div class="col-sm-6">
                                    <div class="p-3 bg-body rounded border h-100">
                                        <span class="d-block fw-bold text-navy mb-1" style="font-size: 0.85rem;">
                                            <i class="bi bi-train-front text-teal me-1"></i> From Railway Station
                                        </span>
                                        <span class="text-muted-hms" style="font-size: 0.8rem; line-height: 1.5;">
                                            Approx. 5–7 minutes by battery auto-rickshaw (Easybike) via Sadar Hospital Road.
                                        </span>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="p-3 bg-body rounded border h-100">
                                        <span class="d-block fw-bold text-navy mb-1" style="font-size: 0.85rem;">
                                            <i class="bi bi-bus-front text-teal me-1"></i> From Bus Terminal
                                        </span>
                                        <span class="text-muted-hms" style="font-size: 0.8rem; line-height: 1.5;">
                                            Approx. 10–12 minutes direct route to the central Shohid Minar roundabout.
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Stylized Map Landmark Canvas -->
                        <div class="p-4 rounded-3 border bg-body text-center">
                            <div class="d-inline-flex p-3 rounded-circle bg-danger-subtle text-danger mb-2">
                                <i class="bi bi-hospital fs-1"></i>
                            </div>
                            <h3 class="h6 fw-bold text-navy mb-1">Meghna Clinic and Nursing Home</h3>
                            <p class="text-muted-hms mb-3" style="font-size: 0.8rem;">
                                Shohid Minar Mor, Dinajpur &bull; Ground Floor Emergency Intake
                            </p>
                            <a href="https://maps.google.com/?q=Shohid+Minar+Mor+Dinajpur" target="_blank" rel="noopener noreferrer" class="btn btn-hms-outline-primary btn-hms-sm">
                                <i class="bi bi-box-arrow-up-right me-1"></i> Open Location in Google Maps
                            </a>
                        </div>
                    </div>
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
                        Official telephone numbers, visiting hour schedules, and physical location metadata are configured via <strong>System Settings</strong>.
                    </span>
                </div>
                <a href="{{ route('home') }}#appointment" class="btn btn-hms-teal btn-hms-sm">
                    <i class="bi bi-calendar2-check"></i> Book Doctor Appointment
                </a>
            </div>
        </div>
    </section>
</x-layouts.public>
