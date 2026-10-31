<x-layouts.nurse title="Nurse Ward Station & Inpatient Care" activeNav="dashboard">
    <!-- Page Header Component -->
    <x-page-header 
        title="Nursing Station — 10-Bed Inpatient Ward" 
        subtitle="Sister Shima Begum &bull; Morning Shift (08:00 AM – 04:00 PM) &bull; Ward Station Desk"
    >
        <x-slot:actions>
            <x-button variant="outline-teal" size="sm">
                <i class="bi bi-journal-medical me-1"></i> Shift Handover Notes
            </x-button>
            <x-button variant="success" size="sm">
                <i class="bi bi-heart-pulse me-1"></i> Record Vital Signs
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <!-- Nurse Station Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Current Ward Census" 
                value="8 / 10 Beds" 
                icon="door-open" 
                color="primary"
                trend="80% Bed Occupancy"
                trendUp="true"
                subtitle="Beds 09, 10 currently empty"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Pending Vitals Check" 
                value="2 Patients Due" 
                icon="heart-pulse" 
                color="danger"
                trend="12:00 PM Schedule"
                trendUp="false"
                subtitle="Beds 02 & 07 vital signs due"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Medication Administrations (MAR)" 
                value="14 Doses" 
                icon="capsule" 
                color="teal"
                trend="11 Given, 3 Upcoming"
                trendUp="true"
                subtitle="Morning shift IV & oral meds"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Emergency On-Duty Doctor" 
                value="Dr. Tariqul Islam" 
                icon="person-badge" 
                color="success"
                trend="Station Present"
                trendUp="true"
                subtitle="24/7 emergency escalation ready"
            />
        </div>
    </div>

    <!-- 10-Bed Inpatient Station Live Grid -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <x-card title="10-Bed Inpatient Ward — Live Bedside Monitor">
                <x-slot:actions>
                    <span class="text-muted-hms" style="font-size: 0.8rem;">
                        <i class="bi bi-arrow-repeat text-teal me-1"></i> Live Auto-refreshing
                    </span>
                </x-slot:actions>

                <div class="row g-3">
                    @for($i = 1; $i <= 10; $i++)
                        @php
                            $isOccupied = $i <= 8;
                            $patientNames = [
                                1 => 'Abdul Karim (54y, M) - Hernia Repair',
                                2 => 'Mohammad Ali (42y, M) - Fracture Tibia',
                                3 => 'Rezaul Karim (61y, M) - Post-Appendectomy',
                                4 => 'Bilkis Banu (36y, F) - Hysterectomy',
                                5 => 'Fatema Khatun (29y, F) - Normal Delivery Obs',
                                6 => 'Taslima Begum (48y, F) - Cholecystectomy',
                                7 => 'Rashida Khatun (25y, F) - Threatened Labor',
                                8 => 'Rabeya Sultana (31y, F) - C-Section Day 1',
                                9 => 'Vacant - Cleaned & Sanitized',
                                10 => 'Vacant - Emergency Standby',
                            ];
                        @endphp
                        <div class="col-md-6 col-xl-4">
                            <div class="p-3 rounded border {{ $isOccupied ? 'bg-white shadow-sm' : 'bg-light border-dashed' }}">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge {{ $isOccupied ? 'bg-primary' : 'bg-secondary' }} px-2 py-1">
                                            BED {{ sprintf('%02d', $i) }}
                                        </span>
                                        <span class="fw-bold text-navy" style="font-size: 0.85rem;">
                                            {{ $i <= 3 ? 'General Male' : ($i <= 6 ? 'General Female' : ($i <= 8 ? 'Cabin Twin' : 'Emergency Obs')) }}
                                        </span>
                                    </div>
                                    <x-badge :status="$isOccupied ? 'danger' : 'success'">
                                        {{ $isOccupied ? 'Occupied' : 'Vacant' }}
                                    </x-badge>
                                </div>
                                
                                @if($isOccupied)
                                    <p class="text-navy fw-semibold mb-1" style="font-size: 0.85rem;">{{ $patientNames[$i] }}</p>
                                    <div class="d-flex justify-content-between text-muted-hms mb-2" style="font-size: 0.75rem;">
                                        <span>BP: 120/80 &bull; Pulse: 78</span>
                                        <span class="text-teal fw-medium">IV Dextrose 5%</span>
                                    </div>
                                    <div class="d-flex gap-1">
                                        <x-button variant="outline-teal" size="sm" class="flex-grow-1">Vitals</x-button>
                                        <x-button variant="outline-primary" size="sm" class="flex-grow-1">MAR</x-button>
                                    </div>
                                @else
                                    <p class="text-muted-hms mb-2" style="font-size: 0.85rem;">{{ $patientNames[$i] }}</p>
                                    <x-button variant="outline-success" size="sm" class="w-100">Ready for Admission</x-button>
                                @endif
                            </div>
                        </div>
                    @endfor
                </div>
            </x-card>
        </div>
    </div>
</x-layouts.nurse>
