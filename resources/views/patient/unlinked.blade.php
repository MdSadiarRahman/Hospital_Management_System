<x-layouts.patient title="Account Verification Notice" activeNav="dashboard">
    <div class="row justify-content-center py-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4 p-md-5 bg-white text-center">
                <div class="d-inline-flex p-3 rounded-circle bg-warning-subtle text-warning mx-auto mb-3" style="width: 64px; height: 64px; align-items: center; justify-content: center;">
                    <i class="bi bi-shield-exclamation fs-2"></i>
                </div>
                <h1 class="h3 fw-bold text-navy mb-2">Physical In-Person Verification Required</h1>
                <p class="text-muted-hms fs-6 max-w-600 mx-auto mb-4">
                    To comply with healthcare data confidentiality standards, self-registered online accounts are not automatically linked to past hospital medical records.
                </p>

                <div class="p-3 rounded-3 bg-light border text-start mb-4">
                    <h5 class="h6 fw-bold text-navy mb-2">
                        <i class="bi bi-info-circle-fill text-teal me-1"></i> How to link your medical chart:
                    </h5>
                    <ol class="text-muted-hms mb-0 ps-3" style="font-size: 0.875rem; line-height: 1.8;">
                        <li>Visit the <strong>Reception Desk</strong> at Meghna Clinic & Nursing Home (Shohid Minar Mor, Dinajpur).</li>
                        <li>Show your phone number (<code class="text-navy fw-semibold">{{ Auth::user()->phone ?? 'Your Registered Number' }}</code>) or National ID card.</li>
                        <li>The receptionist will verify your identity in the HMS and securely link your past visits, prescriptions, and lab reports.</li>
                    </ol>
                </div>

                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route('home') }}" class="btn btn-hms-outline-teal">
                        <i class="bi bi-house me-1"></i> Return Home
                    </a>
                    <form method="POST" action="{{ route('patient.logout') }}">
                        @csrf
                        <x-button type="submit" variant="outline-danger">
                            <i class="bi bi-box-arrow-right me-1"></i> Sign Out
                        </x-button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.patient>
