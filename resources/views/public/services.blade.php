<x-layouts.public 
    title="Healthcare Services"
    metaDescription="Comprehensive healthcare services at Meghna Clinic and Nursing Home, Dinajpur. 10-bed inpatient ward, sterile OT, 24/7 emergency care, and diagnostic pathology."
>
    <!-- Page Header & Breadcrumb -->
    <div class="bg-navy text-white py-5">
        <div class="container-xl">
            <x-breadcrumb :items="['Healthcare Services' => null]" class="mb-3 text-white-50" />
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="badge bg-teal text-white px-3 py-1.5 rounded-pill fw-semibold mb-2" style="font-size: 0.85rem;">
                        Clinical Capabilities
                    </span>
                    <h1 class="display-5 fw-bold mb-3 text-white">Comprehensive Healthcare Services</h1>
                    <p class="lead text-white-50 mb-0" style="max-width: 720px; line-height: 1.6;">
                        Delivering reliable 24/7 emergency response, specialized surgical procedures, comfortable inpatient beds, and diagnostic laboratory care at Shohid Minar Mor, Dinajpur.
                    </p>
                </div>
                <div class="col-lg-4 mt-4 mt-lg-0 text-lg-end">
                    <div class="p-3 rounded-3 bg-white bg-opacity-10 d-inline-block text-start border border-white border-opacity-10">
                        <span class="d-block text-white fw-bold mb-1">
                            <i class="bi bi-shield-check text-teal me-1"></i> Patient Care Guarantee
                        </span>
                        <span class="d-block text-white-50" style="font-size: 0.85rem;">
                            10 Monitored Clinical Beds &bull; 6 Shift Nurses
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 6 Core Healthcare Services Detailed Grid -->
    <section class="py-5 bg-body">
        <div class="container-xl">
            <div class="text-center max-w-700 mx-auto mb-5">
                <span class="badge bg-teal-subtle text-teal px-3 py-1.5 rounded-pill fw-semibold mb-2" style="font-size: 0.85rem;">
                    Full Care Spectrum
                </span>
                <h2 class="h2 fw-bold text-navy">Our Specialized Clinical Offerings</h2>
                <p class="text-muted-hms" style="font-size: 1rem;">
                    Focused healthcare interventions built around patient safety, sterile environments, and prompt medical stabilization.
                </p>
            </div>

            <div class="row g-4 mb-5">
                <!-- 1. 10-Bed Inpatient Ward -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border shadow-sm p-4 rounded-3 bg-white hms-card d-flex flex-column justify-content-between">
                        <div>
                            <div class="rounded-3 bg-teal-subtle text-teal p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 52px; height: 52px;">
                                <i class="bi bi-hospital fs-3"></i>
                            </div>
                            <h3 class="h5 fw-bold text-navy mb-2">10-Bed Inpatient Ward</h3>
                            <p class="text-muted-hms mb-3" style="font-size: 0.875rem; line-height: 1.65;">
                                Clean, comfortable, and infection-controlled hospital accommodation. Distributed across General Ward (4 beds), Maternity Ward (2 beds), Surgical Ward (2 beds), and Emergency Acute Observation (2 beds).
                            </p>
                            <ul class="text-muted-hms mb-0 d-flex flex-column gap-1.5" style="font-size: 0.8rem;">
                                <li><i class="bi bi-check2 text-teal me-1"></i> Dedicated oxygen cylinders & suction apparatus</li>
                                <li><i class="bi bi-check2 text-teal me-1"></i> 24-hour bedside nursing care & vital monitoring</li>
                                <li><i class="bi bi-check2 text-teal me-1"></i> Attendant chair & hygienic washroom facilities</li>
                            </ul>
                        </div>
                        <div class="pt-3 border-top mt-3">
                            <span class="text-teal fw-semibold" style="font-size: 0.85rem;">From ৳800 / day &bull; Transparent Tariffs</span>
                        </div>
                    </div>
                </div>

                <!-- 2. 24/7 Emergency Triage & Resuscitation -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border shadow-sm p-4 rounded-3 bg-white hms-card d-flex flex-column justify-content-between">
                        <div>
                            <div class="rounded-3 bg-danger-subtle text-danger p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 52px; height: 52px;">
                                <i class="bi bi-heart-pulse-fill fs-3"></i>
                            </div>
                            <h3 class="h5 fw-bold text-navy mb-2">24/7 Emergency Triage</h3>
                            <p class="text-muted-hms mb-3" style="font-size: 0.875rem; line-height: 1.65;">
                                Immediate walk-in medical evaluation by our resident On-Duty Emergency Medical Officer. Equipped for rapid stabilization of acute asthma, burn dressings, accidental lacerations, and sudden collapse.
                            </p>
                            <ul class="text-muted-hms mb-0 d-flex flex-column gap-1.5" style="font-size: 0.8rem;">
                                <li><i class="bi bi-check2 text-danger me-1"></i> Ground-floor direct ambulance access</li>
                                <li><i class="bi bi-check2 text-danger me-1"></i> Continuous high-flow oxygen & nebulizer units</li>
                                <li><i class="bi bi-check2 text-danger me-1"></i> Acute suture repairs & antiseptic trauma dressings</li>
                            </ul>
                        </div>
                        <div class="pt-3 border-top mt-3">
                            <span class="text-danger fw-semibold" style="font-size: 0.85rem;">Always Open &bull; 24 Hours / 7 Days</span>
                        </div>
                    </div>
                </div>

                <!-- 3. Sterile Operation Theatre (OT) -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border shadow-sm p-4 rounded-3 bg-white hms-card d-flex flex-column justify-content-between">
                        <div>
                            <div class="rounded-3 bg-primary-subtle text-primary p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 52px; height: 52px;">
                                <i class="bi bi-scissors fs-3"></i>
                            </div>
                            <h3 class="h5 fw-bold text-navy mb-2">Sterile Operation Theatre</h3>
                            <p class="text-muted-hms mb-3" style="font-size: 0.875rem; line-height: 1.65;">
                                A fully equipped surgical suite maintained under strict autoclave sterilization protocols. Dedicated to obstetric cesarean sections, orthopedic trauma interventions, and general surgeries.
                            </p>
                            <ul class="text-muted-hms mb-0 d-flex flex-column gap-1.5" style="font-size: 0.8rem;">
                                <li><i class="bi bi-check2 text-primary me-1"></i> Shadowless LED surgical lighting & diathermy</li>
                                <li><i class="bi bi-check2 text-primary me-1"></i> Autoclave sterilization of all surgical packs</li>
                                <li><i class="bi bi-check2 text-primary me-1"></i> Dedicated recovery bay for post-op emergence</li>
                            </ul>
                        </div>
                        <div class="pt-3 border-top mt-3">
                            <span class="text-primary fw-semibold" style="font-size: 0.85rem;">Surgical Hygiene Standards</span>
                        </div>
                    </div>
                </div>

                <!-- 4. Antenatal Care & Institutional Delivery -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border shadow-sm p-4 rounded-3 bg-white hms-card d-flex flex-column justify-content-between">
                        <div>
                            <div class="rounded-3 bg-info-subtle text-info p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 52px; height: 52px;">
                                <i class="bi bi-gender-female fs-3"></i>
                            </div>
                            <h3 class="h5 fw-bold text-navy mb-2">Maternity & Safe Delivery</h3>
                            <p class="text-muted-hms mb-3" style="font-size: 0.875rem; line-height: 1.65;">
                                Complete maternity care led by Dr. Nasreen Sultana. Offering prenatal consultations, normal vaginal delivery room, emergency cesarean section backup, and immediate neonatal vital assessment.
                            </p>
                            <ul class="text-muted-hms mb-0 d-flex flex-column gap-1.5" style="font-size: 0.8rem;">
                                <li><i class="bi bi-check2 text-info me-1"></i> Antenatal screening & fetal Doppler monitoring</li>
                                <li><i class="bi bi-check2 text-info me-1"></i> Normal delivery room with privacy curtains</li>
                                <li><i class="bi bi-check2 text-info me-1"></i> Female nursing & midwifery attendants 24/7</li>
                            </ul>
                        </div>
                        <div class="pt-3 border-top mt-3">
                            <span class="text-info fw-semibold" style="font-size: 0.85rem;">Compassionate Maternal Care</span>
                        </div>
                    </div>
                </div>

                <!-- 5. Diagnostic Pathology & Partner Labs -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border shadow-sm p-4 rounded-3 bg-white hms-card d-flex flex-column justify-content-between">
                        <div>
                            <div class="rounded-3 bg-warning-subtle text-warning p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 52px; height: 52px;">
                                <i class="bi bi-eyedropper fs-3"></i>
                            </div>
                            <h3 class="h5 fw-bold text-navy mb-2">Diagnostic Pathology</h3>
                            <p class="text-muted-hms mb-3" style="font-size: 0.875rem; line-height: 1.65;">
                                Routine clinical pathology services for admitted patients and outpatients. Essential blood and urine investigations performed on site, alongside verified referral links with leading Dinajpur laboratories.
                            </p>
                            <ul class="text-muted-hms mb-0 d-flex flex-column gap-1.5" style="font-size: 0.8rem;">
                                <li><i class="bi bi-check2 text-warning me-1"></i> CBC, Blood grouping & cross-matching for surgery</li>
                                <li><i class="bi bi-check2 text-warning me-1"></i> Blood glucose, serum creatinine, urine routine</li>
                                <li><i class="bi bi-check2 text-warning me-1"></i> Secure digital report access via Patient Portal</li>
                            </ul>
                        </div>
                        <div class="pt-3 border-top mt-3">
                            <span class="text-warning fw-semibold" style="font-size: 0.85rem;">Internal & Partner Testing</span>
                        </div>
                    </div>
                </div>

                <!-- 6. Post-Operative Nursing Care -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border shadow-sm p-4 rounded-3 bg-white hms-card d-flex flex-column justify-content-between">
                        <div>
                            <div class="rounded-3 bg-success-subtle text-success p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 52px; height: 52px;">
                                <i class="bi bi-clipboard2-pulse fs-3"></i>
                            </div>
                            <h3 class="h5 fw-bold text-navy mb-2">Post-Operative Nursing Care</h3>
                            <p class="text-muted-hms mb-3" style="font-size: 0.875rem; line-height: 1.65;">
                                Continuous patient observation directed by Sister Shima Begum. 6 shift nurses maintain regular vital charts, medication administration records (MAR), aseptic dressing changes, and pain management.
                            </p>
                            <ul class="text-muted-hms mb-0 d-flex flex-column gap-1.5" style="font-size: 0.8rem;">
                                <li><i class="bi bi-check2 text-success me-1"></i> 3 continuous 8-hour shift nursing coverage</li>
                                <li><i class="bi bi-check2 text-success me-1"></i> IV cannula care, fluid infusion & catheterization</li>
                                <li><i class="bi bi-check2 text-success me-1"></i> Direct doctor communication upon vital alerts</li>
                            </ul>
                        </div>
                        <div class="pt-3 border-top mt-3">
                            <span class="text-success fw-semibold" style="font-size: 0.85rem;">Attentive Bedside Care</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inpatient Bed Tariffs Transparency Table -->
            <div class="card border shadow-sm rounded-3 bg-white p-4 mb-5">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                    <div>
                        <span class="badge bg-teal-subtle text-teal px-3 py-1 rounded-pill fw-semibold mb-2" style="font-size: 0.8rem;">
                            Financial Transparency
                        </span>
                        <h3 class="h4 fw-bold text-navy mb-1">Standard Inpatient Bed Tariffs</h3>
                        <p class="text-muted-hms mb-0" style="font-size: 0.875rem;">
                            No hidden costs. All bed rates include 24/7 nursing duty, linen changes, and standard ward amenities.
                        </p>
                    </div>
                    <a href="{{ route('home') }}#appointment" class="btn btn-hms-teal btn-hms-sm">
                        <i class="bi bi-calendar2-check"></i> Book Admission / Consultation
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Category</th>
                                <th>Allocated Beds</th>
                                <th>Daily Tariff</th>
                                <th>Amenities Included</th>
                                <th>Care Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bedCategories as $category)
                                <tr>
                                    <td class="fw-bold text-navy">{{ $category['category'] }}</td>
                                    <td class="font-monospace text-muted-hms">{{ $category['beds'] }}</td>
                                    <td class="tabular-nums fw-bold text-navy" style="font-size: 1rem;">
                                        ৳{{ number_format($category['rate'], 2) }}
                                        <span class="text-muted-hms fw-normal" style="font-size: 0.75rem;">/ 24 hrs</span>
                                    </td>
                                    <td class="text-muted-hms">{{ $category['amenities'] }}</td>
                                    <td>
                                        <span class="badge bg-light text-navy border">
                                            {{ $category['care_level'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Diagnostic Pathology Collection & Report Access -->
            <div class="card border shadow-sm p-4 rounded-3 bg-white">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <h3 class="h5 fw-bold text-navy mb-2">
                            <i class="bi bi-file-earmark-medical text-teal me-2"></i> How Diagnostic Reports Are Handled
                        </h3>
                        <p class="text-muted-hms mb-3" style="font-size: 0.875rem; line-height: 1.6;">
                            Specimens collected at our laboratory desk are verified by qualified technicians. For admitted patients, diagnostic reports are delivered directly to the attending doctor's digital chart. Outpatients can collect printed receipts from the reception counter or log in to the <strong>Patient Self-Care Portal</strong> for secure digital downloads.
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-body text-navy border"><i class="bi bi-check2 text-teal me-1"></i> Sealed physical reports</span>
                            <span class="badge bg-body text-navy border"><i class="bi bi-check2 text-teal me-1"></i> Encrypted server storage</span>
                            <span class="badge bg-body text-navy border"><i class="bi bi-check2 text-teal me-1"></i> Fast delivery for emergency cases</span>
                        </div>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <a href="{{ route('patient.login') }}" class="btn btn-hms-outline-teal">
                            <i class="bi bi-person"></i> Access Patient Portal
                        </a>
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
                        Medical test pricing catalogs, OT procedure tariffs, and bed rates are maintained by the Hospital Administrator in the Billing module.
                    </span>
                </div>
                <a href="{{ route('contact') }}" class="btn btn-hms-outline-primary btn-hms-sm">
                    <i class="bi bi-geo-alt"></i> Contact Reception Desk
                </a>
            </div>
        </div>
    </section>
</x-layouts.public>
