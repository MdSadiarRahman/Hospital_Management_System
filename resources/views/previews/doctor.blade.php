<x-layouts.doctor title="Doctor Clinical Workstation" activeNav="dashboard">
    <!-- Page Header Component -->
    <x-page-header 
        title="Dr. Nasreen Sultana — Clinical Workstation" 
        subtitle="Department of Gynecology, Obstetrics & General Surgery &bull; Dinajpur Chamber & Inpatient Care"
    >
        <x-slot:actions>
            <x-button variant="outline-teal" size="sm">
                <i class="bi bi-clock-history me-1"></i> Patient History Lookup
            </x-button>
            <x-button variant="teal" size="sm">
                <i class="bi bi-file-earmark-medical me-1"></i> Write New Prescription
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <!-- Doctor Clinical Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Today's OPD Queue" 
                value="12 Patients" 
                icon="person-lines-fill" 
                color="teal"
                trend="4 Consulted, 8 Waiting"
                trendUp="true"
                subtitle="Chamber 01 active"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Inpatient Ward Care" 
                value="6 Patients" 
                icon="hospital" 
                color="primary"
                trend="10-Bed Inpatient Ward"
                trendUp="true"
                subtitle="Daily rounds scheduled at 11:30 AM"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Lab Reports for Review" 
                value="3 Reports" 
                icon="virus" 
                color="warning"
                trend="1 Urgent Alert (Hb 7.2)"
                trendUp="false"
                subtitle="Diagnostic pathology verified"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="OT Scheduled Today" 
                value="1 Procedure" 
                icon="scissors" 
                color="info"
                trend="Planned: 02:00 PM"
                trendUp="true"
                subtitle="Elective C-Section Operation"
            />
        </div>
    </div>

    <!-- Clinical Workstation Tables -->
    <div class="row g-4">
        <!-- OPD Patient Queue -->
        <div class="col-lg-7">
            <x-card title="OPD Outpatient Consultation Queue">
                <x-slot:actions>
                    <x-badge status="teal" :dot="true">Consulting Now: Serial #05</x-badge>
                </x-slot:actions>

                <div class="table-responsive">
                    <table class="table table-hms table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Serial</th>
                                <th>Patient Name & ID</th>
                                <th>Age / Gender</th>
                                <th>Chief Complaint</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="table-teal-light">
                                <td class="fw-bold text-teal">#05</td>
                                <td>
                                    <span class="d-block fw-semibold text-navy">Most. Rokeya Akhter</span>
                                    <span class="text-muted-hms" style="font-size: 0.75rem;">MCNH-2026-0042</span>
                                </td>
                                <td>28y / Female</td>
                                <td>Lower abdominal pain, 32w ANC</td>
                                <td><x-badge status="teal">In Chamber</x-badge></td>
                                <td class="text-end">
                                    <x-button variant="teal" size="sm">Examine</x-button>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-navy">#06</td>
                                <td>
                                    <span class="d-block fw-semibold text-navy">Salma Parvin</span>
                                    <span class="text-muted-hms" style="font-size: 0.75rem;">MCNH-2026-0045</span>
                                </td>
                                <td>34y / Female</td>
                                <td>Post-op stitch check, 14 days</td>
                                <td><x-badge status="warning">Next Up</x-badge></td>
                                <td class="text-end">
                                    <x-button variant="outline-teal" size="sm">Call Next</x-button>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-navy">#07</td>
                                <td>
                                    <span class="d-block fw-semibold text-navy">Nasrin Jahan</span>
                                    <span class="text-muted-hms" style="font-size: 0.75rem;">MCNH-2026-0047</span>
                                </td>
                                <td>22y / Female</td>
                                <td>Severe nausea, 1st Trimester</td>
                                <td><x-badge status="info">Waiting</x-badge></td>
                                <td class="text-end">
                                    <x-button variant="outline-secondary" size="sm">View Vitals</x-button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

        <!-- Inpatient Rounds Checklist -->
        <div class="col-lg-5">
            <x-card title="Inpatient Ward Care (Assigned Beds)">
                <x-slot:actions>
                    <x-badge status="primary">6 Inpatients</x-badge>
                </x-slot:actions>

                <div class="d-flex flex-column gap-2">
                    <div class="p-3 rounded border bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-navy">Bed 04: Bilkis Banu (36y)</span>
                            <x-badge status="primary">Post-Op Day 2</x-badge>
                        </div>
                        <p class="text-muted-hms mb-2" style="font-size: 0.8rem;">
                            Abdominal Hysterectomy &bull; Vitals stable (BP 120/80, Pulse 76, Temp 98.4°F) &bull; IV Cipro 500mg running.
                        </p>
                        <div class="d-flex gap-2">
                            <x-button variant="outline-teal" size="sm">Prescription & MAR</x-button>
                            <x-button variant="outline-success" size="sm">Clear Discharge</x-button>
                        </div>
                    </div>

                    <div class="p-3 rounded border bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-navy">Bed 07: Rashida Khatun (25y)</span>
                            <x-badge status="warning">Observation</x-badge>
                        </div>
                        <p class="text-muted-hms mb-2" style="font-size: 0.8rem;">
                            Threatened premature labor &bull; Bed rest &bull; Fetal heart rate regular 144 bpm &bull; Vitals checked by Sister Shima.
                        </p>
                        <div class="d-flex gap-2">
                            <x-button variant="outline-teal" size="sm">Order USG Test</x-button>
                            <x-button variant="outline-secondary" size="sm">Round Notes</x-button>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</x-layouts.doctor>
