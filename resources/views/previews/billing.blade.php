<x-layouts.billing title="Billing & Cashier Portal" activeNav="dashboard">
    <!-- Page Header Component -->
    <x-page-header 
        title="Billing & Cashier Portal" 
        subtitle="Aftab Ahmed &bull; Accounts Counter &bull; Invoices, Payment Settlements, and Dual Discharge Clearance"
    >
        <x-slot:actions>
            <x-button variant="outline-teal" size="sm">
                <i class="bi bi-printer me-1"></i> Daily Collection Summary
            </x-button>
            <x-button variant="primary" size="sm">
                <i class="bi bi-plus-circle me-1"></i> Create Patient Invoice
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <!-- Financial Cashier Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Today's Collections" 
                value="৳ 45,200.00" 
                icon="cash-stack" 
                color="primary"
                trend="18 Transactions"
                trendUp="true"
                subtitle="All payment channels"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Cash in Register" 
                value="৳ 28,400.00" 
                icon="wallet2" 
                color="teal"
                trend="Physical Cash Verified"
                trendUp="true"
                subtitle="Counter drawer balance"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="MFS (bKash & Nagad)" 
                value="৳ 16,800.00" 
                icon="phone-vibrate" 
                color="warning"
                trend="TrxID Verified"
                trendUp="true"
                subtitle="Digital mobile wallets"
            />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card 
                title="Pending Inpatient Dues" 
                value="৳ 8,400.00" 
                icon="exclamation-circle" 
                color="danger"
                trend="3 Unsettled Bills"
                trendUp="false"
                subtitle="Discharge hold active"
            />
        </div>
    </div>

    <!-- Active Invoices Table -->
    <div class="row g-4">
        <div class="col-12">
            <x-card title="Patient Invoices & Settlement Desk">
                <x-slot:actions>
                    <div class="d-flex gap-2">
                        <x-search-input placeholder="Filter by Bill No or Patient..." />
                        <x-button variant="outline-teal" size="sm">All Statuses</x-button>
                    </div>
                </x-slot:actions>

                <div class="table-responsive">
                    <table class="table table-hms table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Invoice Number</th>
                                <th>Patient Name & ID</th>
                                <th>Bill Category</th>
                                <th>Total Amount</th>
                                <th>Paid</th>
                                <th>Due Balance</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-bold text-navy">INV-2026-0089</td>
                                <td>
                                    <span class="d-block fw-semibold text-navy">Abdul Karim</span>
                                    <span class="text-muted-hms" style="font-size: 0.75rem;">MCNH-2026-0038 &bull; Bed 01</span>
                                </td>
                                <td>Inpatient 3-Day Stay + Surgery</td>
                                <td class="fw-bold tabular-nums">৳ 12,500.00</td>
                                <td class="text-success tabular-nums">৳ 10,000.00</td>
                                <td class="text-danger fw-bold tabular-nums">৳ 2,500.00</td>
                                <td><x-badge status="warning">Partial Paid</x-badge></td>
                                <td class="text-end">
                                    <x-button variant="teal" size="sm">Collect Due</x-button>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-navy">INV-2026-0090</td>
                                <td>
                                    <span class="d-block fw-semibold text-navy">Most. Rokeya Akhter</span>
                                    <span class="text-muted-hms" style="font-size: 0.75rem;">MCNH-2026-0042 &bull; OPD</span>
                                </td>
                                <td>Doctor Consultation + USG</td>
                                <td class="fw-bold tabular-nums">৳ 1,200.00</td>
                                <td class="text-success tabular-nums">৳ 1,200.00</td>
                                <td class="tabular-nums">৳ 0.00</td>
                                <td><x-badge status="success">Settled</x-badge></td>
                                <td class="text-end">
                                    <x-button variant="outline-primary" size="sm">Print Receipt</x-button>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-navy">INV-2026-0091</td>
                                <td>
                                    <span class="d-block fw-semibold text-navy">Fatema Khatun</span>
                                    <span class="text-muted-hms" style="font-size: 0.75rem;">MCNH-2026-0044 &bull; Bed 05</span>
                                </td>
                                <td>Emergency Admission + Oxygen</td>
                                <td class="fw-bold tabular-nums">৳ 5,900.00</td>
                                <td class="text-success tabular-nums">৳ 0.00</td>
                                <td class="text-danger fw-bold tabular-nums">৳ 5,900.00</td>
                                <td><x-badge status="danger">Unpaid</x-badge></td>
                                <td class="text-end">
                                    <x-button variant="primary" size="sm">Settle Now</x-button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
</x-layouts.billing>
