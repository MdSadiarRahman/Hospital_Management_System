<x-layouts.reception title="Reception & Patient Intake Desk" activeNav="dashboard">
    <!-- Page Header Component -->
    <x-page-header 
        title="Reception & Patient Intake Desk" 
        subtitle="Kowshik Roy &bull; Main Entrance Desk &bull; Patient Registration, Triage & Bed Admissions"
    >
        <x-slot:actions>
            <x-button variant="outline-teal" size="sm">
                <i class="bi bi-calendar-plus me-1"></i> Book Appointment
            </x-button>
            <x-button variant="primary" size="sm">
                <i class="bi bi-person-plus me-1"></i> Register New Patient
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <!-- Reception Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Today's Registered Patients" 
                value="18 Patients" 
                icon="person-plus" 
                color="primary"
                trend="+4 vs yesterday"
                trendUp="true"
                subtitle="New MRN generation active"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="OPD Appointments" 
                value="26 Booked" 
                icon="calendar2-check" 
                color="teal"
                trend="14 Completed, 12 Pending"
                trendUp="true"
                subtitle="Chambers 01 & 02"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="10-Bed Inpatient Status" 
                value="2 Beds Vacant" 
                icon="door-open" 
                color="warning"
                trend="Beds 09, 10 ready"
                trendUp="true"
                subtitle="Immediate admission ready"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Portal Account Verifications" 
                value="3 Pending" 
                icon="person-check" 
                color="info"
                trend="Privacy Protection"
                trendUp="true"
                subtitle="Physical identity check required"
            />
        </div>
    </div>

    <!-- Patient Search & Today's Intake Table -->
    <div class="row g-4">
        <div class="col-12">
            <x-card title="Today's Patient Registration & Intake Queue">
                <x-slot:actions>
                    <div class="d-flex gap-2">
                        <x-search-input placeholder="Search by name, phone or MRN..." />
                        <x-button variant="outline-teal" size="sm">
                            <i class="bi bi-filter"></i>
                        </x-button>
                    </div>
                </x-slot:actions>

                <div class="table-responsive">
                    <table class="table table-hms table-hover mb-0">
                        <thead>
                            <tr>
                                <th>MRN / Patient ID</th>
                                <th>Patient Name</th>
                                <th>Contact Number</th>
                                <th>Assigned Doctor</th>
                                <th>Visit Type</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-bold text-navy">MCNH-2026-0051</td>
                                <td class="fw-semibold text-navy">Md. Rafiqul Islam</td>
                                <td>+880 1711-223344</td>
                                <td>Dr. Tariqul Islam</td>
                                <td><x-badge status="danger">Emergency</x-badge></td>
                                <td><x-badge status="warning">Triage Active</x-badge></td>
                                <td class="text-end">
                                    <x-button variant="outline-teal" size="sm">Admit Bed</x-button>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-navy">MCNH-2026-0050</td>
                                <td class="fw-semibold text-navy">Naznin Akhter</td>
                                <td>+880 1722-334455</td>
                                <td>Dr. Nasreen Sultana</td>
                                <td><x-badge status="teal">OPD Consultation</x-badge></td>
                                <td><x-badge status="info">Serial #08</x-badge></td>
                                <td class="text-end">
                                    <x-button variant="outline-secondary" size="sm">Print Token</x-button>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-navy">MCNH-2026-0049</td>
                                <td class="fw-semibold text-navy">Sumon Chandra Deb</td>
                                <td>+880 1733-445566</td>
                                <td>Dr. Nasreen Sultana</td>
                                <td><x-badge status="primary">Follow-up</x-badge></td>
                                <td><x-badge status="success">Completed</x-badge></td>
                                <td class="text-end">
                                    <x-button variant="outline-primary" size="sm">View Bill</x-button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
</x-layouts.reception>
