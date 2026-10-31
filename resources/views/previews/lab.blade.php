<x-layouts.lab title="Diagnostic Laboratory Worklist" activeNav="dashboard">
    <!-- Page Header Component -->
    <x-page-header 
        title="Diagnostic Laboratory Workstation" 
        subtitle="Md. Faruk Hossain &bull; Medical Technologist &bull; Internal Pathology & External Partner Diagnostics"
    >
        <x-slot:actions>
            <x-button variant="outline-teal" size="sm">
                <i class="bi bi-upc-scan me-1"></i> Scan Sample Barcode
            </x-button>
            <x-button variant="teal" size="sm">
                <i class="bi bi-file-earmark-arrow-up me-1"></i> Upload Secure Report
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <!-- Laboratory Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Today's Test Orders" 
                value="24 Orders" 
                icon="clipboard2-pulse" 
                color="primary"
                trend="18 Internal, 6 External"
                trendUp="true"
                subtitle="Routine & emergency orders"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Barcode Samples Collected" 
                value="19 Samples" 
                icon="upc-scan" 
                color="teal"
                trend="EDTA & Serum Tubes"
                trendUp="true"
                subtitle="Phlebotomy station active"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Reports Published" 
                value="15 Verified" 
                icon="file-earmark-check" 
                color="success"
                trend="Secure PDF Storage"
                trendUp="true"
                subtitle="Streamed via authorized portal"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Partner Lab Referrals" 
                value="4 Tests Sent" 
                icon="buildings" 
                color="info"
                trend="Popular Diagnostic"
                trendUp="true"
                subtitle="Biopsy & special hormone tests"
            />
        </div>
    </div>

    <!-- Diagnostic Lab Orders Worklist -->
    <div class="row g-4">
        <div class="col-12">
            <x-card title="Diagnostic Test Queue & Verification Worklist">
                <x-slot:actions>
                    <div class="d-flex gap-2">
                        <x-search-input placeholder="Search sample ID or patient..." />
                        <x-button variant="outline-teal" size="sm">Filter Test Type</x-button>
                    </div>
                </x-slot:actions>

                <div class="table-responsive">
                    <table class="table table-hms table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Sample Barcode</th>
                                <th>Patient Name & ID</th>
                                <th>Test Name</th>
                                <th>Laboratory</th>
                                <th>Prescribed By</th>
                                <th>Report Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <span class="font-monospace fw-bold text-navy">SMP-883901</span>
                                    <span class="d-block text-muted-hms" style="font-size: 0.7rem;">EDTA Whole Blood</span>
                                </td>
                                <td>
                                    <span class="d-block fw-semibold text-navy">Most. Rokeya Akhter</span>
                                    <span class="text-muted-hms" style="font-size: 0.75rem;">MCNH-2026-0042</span>
                                </td>
                                <td class="fw-medium">Complete Blood Count (CBC)</td>
                                <td><x-badge status="info">Internal Lab</x-badge></td>
                                <td>Dr. Nasreen Sultana</td>
                                <td><x-badge status="warning">Awaiting Results</x-badge></td>
                                <td class="text-end">
                                    <x-button variant="teal" size="sm">Enter Values</x-button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="font-monospace fw-bold text-navy">SMP-883902</span>
                                    <span class="d-block text-muted-hms" style="font-size: 0.7rem;">Serum Yellow Top</span>
                                </td>
                                <td>
                                    <span class="d-block fw-semibold text-navy">Abdul Karim</span>
                                    <span class="text-muted-hms" style="font-size: 0.75rem;">MCNH-2026-0038 &bull; Bed 01</span>
                                </td>
                                <td class="fw-medium">Serum Creatinine & Electrolytes</td>
                                <td><x-badge status="info">Internal Lab</x-badge></td>
                                <td>Dr. Tariqul Islam</td>
                                <td><x-badge status="success">Verified PDF</x-badge></td>
                                <td class="text-end">
                                    <x-button variant="outline-primary" size="sm">View PDF</x-button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="font-monospace fw-bold text-navy">SMP-883903</span>
                                    <span class="d-block text-muted-hms" style="font-size: 0.7rem;">Tissue Specimen</span>
                                </td>
                                <td>
                                    <span class="d-block fw-semibold text-navy">Bilkis Banu</span>
                                    <span class="text-muted-hms" style="font-size: 0.75rem;">MCNH-2026-0039 &bull; Bed 04</span>
                                </td>
                                <td class="fw-medium">Histopathology (Uterine Tissue)</td>
                                <td><x-badge status="primary">External Partner</x-badge></td>
                                <td>Dr. Nasreen Sultana</td>
                                <td><x-badge status="info">Dispatched to Lab</x-badge></td>
                                <td class="text-end">
                                    <x-button variant="outline-secondary" size="sm">Track Courier</x-button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
</x-layouts.lab>
