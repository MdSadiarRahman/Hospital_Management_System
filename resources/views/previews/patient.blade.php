<x-layouts.patient title="Patient Self-Care Portal" activeNav="dashboard">
    <!-- Page Header Component -->
    <x-page-header 
        title="Welcome back, Most. Rokeya Akhter" 
        subtitle="Patient ID: MCNH-2026-0042 &bull; Dinajpur Sadar &bull; Verified Self-Care Health Portal"
    >
        <x-slot:actions>
            <x-button variant="outline-teal" size="sm">
                <i class="bi bi-clock-history me-1"></i> My Health History
            </x-button>
            <x-button variant="teal" size="sm">
                <i class="bi bi-calendar2-plus me-1"></i> Book Next Appointment
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <!-- Patient Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Next Appointment" 
                value="Today, 11:30 AM" 
                icon="calendar2-check" 
                color="teal"
                trend="Dr. Nasreen Sultana"
                trendUp="true"
                subtitle="Chamber 01 &bull; Serial #05"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Active Prescriptions" 
                value="2 Medicines" 
                icon="capsule" 
                color="primary"
                trend="Take with food"
                trendUp="true"
                subtitle="Iron Folate & Calcium Tab"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Diagnostic Reports" 
                value="1 New Report" 
                icon="file-earmark-pdf" 
                color="success"
                trend="Ready for Download"
                trendUp="true"
                subtitle="CBC blood test published"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Billing & Due Balance" 
                value="৳ 0.00 Due" 
                icon="check2-circle" 
                color="info"
                trend="All Invoices Cleared"
                trendUp="true"
                subtitle="Receipt #INV-2026-0090 paid"
            />
        </div>
    </div>

    <!-- Patient Information Bento Grid -->
    <div class="row g-4">
        <!-- Active Prescription Card -->
        <div class="col-lg-6">
            <x-card title="My Current Doctor Prescriptions">
                <x-slot:actions>
                    <x-badge status="teal">Active Advice</x-badge>
                </x-slot:actions>

                <div class="p-3 rounded border bg-light mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-navy">Tab. Fefol-Z (Iron + Folic Acid)</span>
                        <span class="badge bg-teal text-white">1 + 0 + 1</span>
                    </div>
                    <p class="text-muted-hms mb-1" style="font-size: 0.825rem;">
                        Take orally after meals with a glass of water &bull; Continue for 30 days.
                    </p>
                    <span class="text-muted-hms" style="font-size: 0.75rem;">Prescribed by Dr. Nasreen Sultana &bull; 28 Oct 2026</span>
                </div>

                <div class="p-3 rounded border bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-navy">Tab. Calbo-D (Calcium + Vit D3)</span>
                        <span class="badge bg-teal text-white">0 + 0 + 1</span>
                    </div>
                    <p class="text-muted-hms mb-1" style="font-size: 0.825rem;">
                        Take once daily at night before sleep &bull; Continue for 60 days.
                    </p>
                    <span class="text-muted-hms" style="font-size: 0.75rem;">Prescribed by Dr. Nasreen Sultana &bull; 28 Oct 2026</span>
                </div>
            </x-card>
        </div>

        <!-- Downloadable Lab Reports -->
        <div class="col-lg-6">
            <x-card title="My Verified Diagnostic Lab Reports">
                <x-slot:actions>
                    <x-badge status="success" :dot="true">Verified Storage</x-badge>
                </x-slot:actions>

                <div class="table-responsive">
                    <table class="table table-hms table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Test Name</th>
                                <th>Test Date</th>
                                <th>Status</th>
                                <th class="text-end">Download</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <span class="fw-semibold text-navy d-block">Complete Blood Count (CBC)</span>
                                    <span class="text-muted-hms" style="font-size: 0.75rem;">Ref: Dr. Nasreen Sultana</span>
                                </td>
                                <td>30 Oct 2026</td>
                                <td><x-badge status="success">Verified PDF</x-badge></td>
                                <td class="text-end">
                                    <x-button variant="outline-teal" size="sm">
                                        <i class="bi bi-file-earmark-arrow-down"></i> PDF
                                    </x-button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="fw-semibold text-navy d-block">Pregnancy Profile Ultrasonography</span>
                                    <span class="text-muted-hms" style="font-size: 0.75rem;">Ref: Dr. Nasreen Sultana</span>
                                </td>
                                <td>15 Oct 2026</td>
                                <td><x-badge status="success">Verified PDF</x-badge></td>
                                <td class="text-end">
                                    <x-button variant="outline-teal" size="sm">
                                        <i class="bi bi-file-earmark-arrow-down"></i> PDF
                                    </x-button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
</x-layouts.patient>
