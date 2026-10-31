<x-layouts.admin title="Administrator Control Center" activeNav="dashboard">
    <!-- Page Header Component -->
    <x-page-header 
        title="Hospital Administration Overview" 
        subtitle="Meghna Clinic & Nursing Home operations, 10-bed ward status, 14 medical personnel, and financial health."
    >
        <x-slot:actions>
            <x-button variant="outline-teal" size="sm">
                <i class="bi bi-file-earmark-arrow-down me-1"></i> Export Daily Report
            </x-button>
            <x-button variant="primary" size="sm">
                <i class="bi bi-person-plus me-1"></i> Register New Staff
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <!-- Top Bento Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="10-Bed Inpatient Status" 
                value="8 / 10 Beds" 
                icon="door-open" 
                color="primary"
                trend="80% Occupancy"
                trendUp="true"
                subtitle="2 vacant beds ready for admission"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Active Hospital Staff" 
                value="14 Personnel" 
                icon="people" 
                color="teal"
                trend="All Shifts Covered"
                trendUp="true"
                subtitle="2 Doctors 24/7, 6 Shift Nurses"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Today's Collections" 
                value="৳ 45,200.00" 
                icon="cash-stack" 
                color="warning"
                trend="+12.4% vs yesterday"
                trendUp="true"
                subtitle="18 Transactions settled"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Security & Audit Events" 
                value="142 Logs" 
                icon="shield-check" 
                color="info"
                trend="Normal Activity"
                trendUp="true"
                subtitle="Zero unauthorized attempts"
            />
        </div>
    </div>

    <!-- Administrative Operations Bento Grid -->
    <div class="row g-4">
        <!-- Staff Shift Roster (14 Personnel) -->
        <div class="col-lg-8">
            <x-card title="Staff On-Duty & Shift Roster (14 Active Personnel)">
                <x-slot:actions>
                    <x-badge status="success" :dot="true">Full Attendance</x-badge>
                </x-slot:actions>

                <div class="table-responsive">
                    <table class="table table-hms table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Staff Name</th>
                                <th>Role / Department</th>
                                <th>Active Shift</th>
                                <th>Status</th>
                                <th>Duty Station</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-semibold text-navy">Dr. Nasreen Sultana</td>
                                <td>Gynecologist & Surgeon</td>
                                <td>Morning OPD + Rounds</td>
                                <td><x-badge status="success">On Duty</x-badge></td>
                                <td>Chamber 01</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold text-navy">Dr. Tariqul Islam</td>
                                <td>Emergency Medical Officer</td>
                                <td>24/7 Emergency Rotation</td>
                                <td><x-badge status="danger">Emergency Active</x-badge></td>
                                <td>Triage Room</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold text-navy">Sister Shima Begum</td>
                                <td>Senior Staff Nurse</td>
                                <td>Morning Shift (8h)</td>
                                <td><x-badge status="success">On Duty</x-badge></td>
                                <td>Inpatient 10-Bed Ward</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold text-navy">Kowshik Roy</td>
                                <td>Front Desk Receptionist</td>
                                <td>Morning Shift (8h)</td>
                                <td><x-badge status="info">Reception Desk</x-badge></td>
                                <td>Main Entrance Desk</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold text-navy">Aftab Ahmed</td>
                                <td>Billing & Cashier</td>
                                <td>General Shift</td>
                                <td><x-badge status="warning">Cash Counter</x-badge></td>
                                <td>Accounts Booth</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold text-navy">Md. Faruk Hossain</td>
                                <td>Lab Medical Technologist</td>
                                <td>Day Shift</td>
                                <td><x-badge status="info">Processing Tests</x-badge></td>
                                <td>Pathology Lab</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

        <!-- 10 Bed Status Widget -->
        <div class="col-lg-4">
            <x-card title="10-Bed Inpatient Live Map">
                <div class="d-flex flex-column gap-2">
                    @for($i = 1; $i <= 10; $i++)
                        @php
                            $isOccupied = $i <= 8;
                            $bedType = $i <= 3 ? 'General Male' : ($i <= 6 ? 'General Female' : ($i <= 8 ? 'Cabin Twin' : 'Emergency Obs'));
                        @endphp
                        <div class="d-flex align-items-center justify-content-between p-2 rounded border {{ $isOccupied ? 'bg-light' : 'bg-success-subtle border-success' }}">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge {{ $isOccupied ? 'bg-primary' : 'bg-success' }} text-white" style="width: 28px;">
                                    {{ sprintf('%02d', $i) }}
                                </span>
                                <div>
                                    <span class="d-block fw-semibold text-navy" style="font-size: 0.8rem;">Bed #{{ sprintf('%02d', $i) }} ({{ $bedType }})</span>
                                    <span class="text-muted-hms" style="font-size: 0.7rem;">
                                        {{ $isOccupied ? 'Admitted (Patient ID: MCNH-2026-00'.(40+$i).')' : 'Available for immediate admission' }}
                                    </span>
                                </div>
                            </div>
                            <x-badge :status="$isOccupied ? 'danger' : 'success'">
                                {{ $isOccupied ? 'Occupied' : 'Vacant' }}
                            </x-badge>
                        </div>
                    @endfor
                </div>
            </x-card>
        </div>
    </div>
</x-layouts.admin>
