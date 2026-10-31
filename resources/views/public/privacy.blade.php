<x-layouts.public 
    title="Privacy & Data Policy"
    metaDescription="Healthcare data privacy, clinical confidentiality, and security standards at Meghna Clinic and Nursing Home, Dinajpur. BMDC compliance and patient data protection."
>
    <!-- Page Header & Breadcrumb -->
    <div class="bg-navy text-white py-5">
        <div class="container-xl">
            <x-breadcrumb :items="['Privacy Policy' => null]" class="mb-3 text-white-50" />
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="badge bg-teal text-white px-3 py-1.5 rounded-pill fw-semibold mb-2" style="font-size: 0.85rem;">
                        Medical Data Governance
                    </span>
                    <h1 class="display-5 fw-bold mb-3 text-white">Healthcare Privacy & Security Policy</h1>
                    <p class="lead text-white-50 mb-0" style="max-width: 720px; line-height: 1.6;">
                        How patient medical records, diagnostic test reports, and digital consultations are protected under clinical ethics and data security protocols at Meghna Clinic & Nursing Home, Dinajpur.
                    </p>
                </div>
                <div class="col-lg-4 mt-4 mt-lg-0 text-lg-end">
                    <div class="p-3 rounded-3 bg-white bg-opacity-10 d-inline-block text-start border border-white border-opacity-10">
                        <span class="d-block text-white fw-bold mb-1">
                            <i class="bi bi-shield-lock-fill text-teal me-1"></i> BMDC Ethical Standard
                        </span>
                        <span class="d-block text-white-50" style="font-size: 0.85rem;">
                            Role-Based Clinical Access &bull; Encrypted Storage
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Policy Content -->
    <section class="py-5 bg-body">
        <div class="container-xl" style="max-width: 960px;">
            <div class="card border shadow-sm p-4 p-md-5 rounded-3 bg-white">
                <!-- 1. Introduction & Ethical Code -->
                <div class="mb-5">
                    <span class="badge bg-teal-subtle text-teal px-3 py-1.5 rounded-pill fw-semibold mb-2" style="font-size: 0.85rem;">
                        Section 1
                    </span>
                    <h2 class="h4 fw-bold text-navy mb-3">1. Medical Confidentiality & Commitment</h2>
                    <p class="text-muted-hms mb-3" style="line-height: 1.75;">
                        At <strong>{{ $clinicName }}</strong>, patient privacy and medical confidentiality are sacred obligations. In accordance with the ethical codes established by the Bangladesh Medical & Dental Council (BMDC) and international healthcare information security standards, we maintain strict safeguards over all physical and electronic health records.
                    </p>
                    <p class="text-muted-hms mb-0" style="line-height: 1.75;">
                        This policy outlines our procedures regarding the collection, clinical usage, retention, and protection of personal and medical data for patients treated at our facility at Shohid Minar Mor, Dinajpur.
                    </p>
                </div>

                <hr class="my-4">

                <!-- 2. Patient Self-Registration Rule (Mandatory) -->
                <div class="mb-5">
                    <div class="p-3.5 rounded-3 bg-danger-subtle border border-danger-subtle mb-3">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-shield-exclamation text-danger fs-5"></i>
                            <h3 class="h6 fw-bold text-danger mb-0">Critical Patient Privacy Rule</h3>
                        </div>
                        <p class="text-muted-hms mb-0" style="font-size: 0.875rem; line-height: 1.6;">
                            Accounts created through the online <strong>Patient Self-Care Portal</strong> are strictly isolated and do <strong>NOT</strong> automatically link to or reveal any existing hospital medical records, prescriptions, or laboratory tests.
                        </p>
                    </div>

                    <h2 class="h4 fw-bold text-navy mb-3">2. Identity Verification for Clinical Health Records</h2>
                    <p class="text-muted-hms mb-3" style="line-height: 1.75;">
                        To prevent unauthorized access or identity theft of sensitive medical data, linking an online account with physical hospital charts requires <strong>in-person physical verification</strong> at the Meghna Clinic reception desk.
                    </p>
                    <p class="text-muted-hms mb-0" style="line-height: 1.75;">
                        Our front desk staff verifies the patient's identity (matching National ID or physical phone verification) before authorizing digital record viewing in the Patient Portal. Until physical verification is completed, an online account remains unlinked and cannot access historical medical charts.
                    </p>
                </div>

                <hr class="my-4">

                <!-- 3. Protected Diagnostic Storage -->
                <div class="mb-5">
                    <span class="badge bg-teal-subtle text-teal px-3 py-1.5 rounded-pill fw-semibold mb-2" style="font-size: 0.85rem;">
                        Section 3
                    </span>
                    <h2 class="h4 fw-bold text-navy mb-3">3. Diagnostic Reports & Electronic Health Records Storage</h2>
                    <p class="text-muted-hms mb-3" style="line-height: 1.75;">
                        All diagnostic pathology reports, surgical operative notes, and ultrasound imaging summaries are housed in secure, non-public storage directories (<code>storage/app/secure_medical_reports/</code>).
                    </p>
                    <ul class="text-muted-hms d-flex flex-column gap-2" style="line-height: 1.65;">
                        <li><strong>No Public URL Exposure:</strong> Files are never directly accessible via web links.</li>
                        <li><strong>Authorized Stream Only:</strong> Diagnostic reports can only be streamed to an authenticated session where the user has explicit permission to view that specific record.</li>
                        <li><strong>Session Expiration:</strong> Active sessions automatically expire following periods of inactivity to protect unattended devices.</li>
                    </ul>
                </div>

                <hr class="my-4">

                <!-- 4. Role-Based Access Control -->
                <div class="mb-5">
                    <span class="badge bg-teal-subtle text-teal px-3 py-1.5 rounded-pill fw-semibold mb-2" style="font-size: 0.85rem;">
                        Section 4
                    </span>
                    <h2 class="h4 fw-bold text-navy mb-3">4. Role-Based Access Control (RBAC) & Staff Restraints</h2>
                    <p class="text-muted-hms mb-3" style="line-height: 1.75;">
                        Our hospital management system implements granular Role-Based Access Control across all 9 hospital roles:
                    </p>
                    <div class="row g-2 mb-3" style="font-size: 0.85rem;">
                        <div class="col-sm-6">
                            <div class="p-2.5 rounded bg-body border">
                                <strong class="text-navy">Attending Doctors:</strong> Full clinical chart access for assigned patients.
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-2.5 rounded bg-body border">
                                <strong class="text-navy">Shift Nurses:</strong> Medication administration (MAR) & vitals recording.
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-2.5 rounded bg-body border">
                                <strong class="text-navy">Lab Staff:</strong> Test result data entry; no access to financial records.
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-2.5 rounded bg-body border">
                                <strong class="text-navy">Billing Staff:</strong> Invoice and payment records only; no access to diagnosis details.
                            </div>
                        </div>
                    </div>
                    <p class="text-muted-hms mb-0" style="line-height: 1.75;">
                        Every administrative, medical, and billing interaction creates an immutable audit log entry documenting the staff member ID, timestamp, and action taken.
                    </p>
                </div>

                <hr class="my-4">

                <!-- 5. Patient Rights & Physical Copies -->
                <div class="mb-5">
                    <span class="badge bg-teal-subtle text-teal px-3 py-1.5 rounded-pill fw-semibold mb-2" style="font-size: 0.85rem;">
                        Section 5
                    </span>
                    <h2 class="h4 fw-bold text-navy mb-3">5. Patient Rights to Physical & Digital Records</h2>
                    <p class="text-muted-hms mb-3" style="line-height: 1.75;">
                        Every patient admitted to or treated at Meghna Clinic and Nursing Home retains the legal right to:
                    </p>
                    <ul class="text-muted-hms d-flex flex-column gap-2" style="line-height: 1.65;">
                        <li>Receive a complete, physician-signed <strong>Discharge Summary</strong> at the conclusion of any inpatient hospital stay.</li>
                        <li>Request printed, sealed physical copies of diagnostic laboratory test results at the reception desk.</li>
                        <li>Review transparent itemized billing invoices before making settlement payments.</li>
                    </ul>
                </div>

                <hr class="my-4">

                <!-- 6. Governance & Contact -->
                <div>
                    <h2 class="h4 fw-bold text-navy mb-3">6. Privacy Inquiries & Hospital Administration</h2>
                    <p class="text-muted-hms mb-3" style="line-height: 1.75;">
                        For questions or requests concerning medical records, data privacy, or patient identity verification:
                    </p>
                    <div class="p-3 rounded-3 bg-body border">
                        <strong class="d-block text-navy mb-1">Hospital Administrator — Medical Records Division</strong>
                        <span class="d-block text-muted-hms" style="font-size: 0.85rem;">{{ $clinicName }}</span>
                        <span class="d-block text-muted-hms" style="font-size: 0.85rem;">{{ $clinicAddress }}</span>
                        <span class="d-block text-teal fw-semibold mt-1" style="font-size: 0.85rem;">Email: info@meghnaclinic.com | Hotline: {{ $clinicHotline }}</span>
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
                        <i class="bi bi-gear-fill me-1"></i> Admin Managed
                    </span>
                    <span class="text-muted-hms" style="font-size: 0.85rem;">
                        Privacy policy amendments and legal compliance notices are reviewed and published under System Governance settings.
                    </span>
                </div>
                <a href="{{ route('contact') }}" class="btn btn-hms-outline-primary btn-hms-sm">
                    <i class="bi bi-geo-alt"></i> Contact Hospital Office
                </a>
            </div>
        </div>
    </section>
</x-layouts.public>
