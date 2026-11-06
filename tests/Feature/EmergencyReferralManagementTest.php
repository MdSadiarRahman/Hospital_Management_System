<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Bed;
use App\Models\BedAssignment;
use App\Models\Department;
use App\Models\Patient;
use App\Models\Referral;
use App\Models\ReferralHistory;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\DepartmentAndBedSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EmergencyReferralManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;
    protected User $doctorUser;
    protected User $nurseUser;
    protected User $receptionistUser;
    protected User $billingUser;
    protected Patient $patient;
    protected Admission $admission;
    protected Bed $bed;

    protected function setUp(): void
    {
        parent::setUp();

        if (Department::count() === 0 || Bed::count() === 0) {
            $this->seed(RolePermissionSeeder::class);
            $this->seed(DepartmentAndBedSeeder::class);
            $this->seed(SystemSettingsSeeder::class);
            $this->seed(UserSeeder::class);
        }

        $this->adminUser = User::whereHas('roles', fn($q) => $q->where('name', 'administrator'))->first()
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('administrator');

        $this->doctorUser = User::whereHas('roles', fn($q) => $q->where('name', 'doctor'))->first()
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('doctor');

        $this->nurseUser = User::whereHas('roles', fn($q) => $q->where('name', 'nurse'))->first()
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('nurse');

        $this->receptionistUser = User::whereHas('roles', fn($q) => $q->where('name', 'receptionist'))->first()
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('receptionist');

        $this->billingUser = User::whereHas('roles', fn($q) => $q->where('name', 'billing_staff'))->first()
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('billing_staff');

        // Create synthetic patient
        $this->patient = Patient::create([
            'patient_code' => 'MCNH-2026-' . rand(100000, 999999),
            'name' => 'Emergency Test Patient',
            'phone' => '01712' . rand(100000, 999999),
            'date_of_birth' => '1985-04-12',
            'gender' => 'male',
            'blood_group' => 'O+',
            'is_verified' => true,
        ]);

        $dept = Department::first();

        // Create emergency visit
        $visit = Visit::create([
            'patient_id' => $this->patient->id,
            'visit_number' => 'VIS-2026-' . rand(100000, 999999),
            'visit_date' => now(),
            'visit_type' => 'emergency',
            'triage_level' => 'critical',
            'status' => 'admitted',
        ]);

        $this->bed = Bed::first();

        $this->admission = Admission::create([
            'visit_id' => $visit->id,
            'patient_id' => $this->patient->id,
            'department_id' => $dept->id,
            'admission_number' => 'ADM-2026-' . rand(100000, 999999),
            'admission_date' => now(),
            'attending_doctor_id' => $this->doctorUser->id,
            'admission_reason' => 'Severe acute chest pain with cardiogenic shock',
            'status' => 'admitted',
        ]);

        if ($this->bed) {
            $this->bed->update(['status' => 'occupied']);
            BedAssignment::create([
                'admission_id' => $this->admission->id,
                'bed_id' => $this->bed->id,
                'assigned_at' => now(),
            ]);
        }
    }

    public function test_authorized_clinical_staff_can_view_emergency_referrals_workbench(): void
    {
        // Doctor can access
        $doctorResponse = $this->actingAs($this->doctorUser)->get(route('portal.referrals.index'));
        $doctorResponse->assertStatus(200);
        $doctorResponse->assertSee('Emergency Referrals');

        // Nurse can access
        $nurseResponse = $this->actingAs($this->nurseUser)->get(route('portal.referrals.index'));
        $nurseResponse->assertStatus(200);

        // Administrator can access
        $adminResponse = $this->actingAs($this->adminUser)->get(route('portal.referrals.index'));
        $adminResponse->assertStatus(200);
    }

    public function test_unauthorized_users_are_denied_access_to_referrals(): void
    {
        // Receptionist denied
        $this->actingAs($this->receptionistUser)->get(route('portal.referrals.index'))
            ->assertStatus(403);

        // Billing staff denied
        $this->actingAs($this->billingUser)->get(route('portal.referrals.index'))
            ->assertStatus(403);

        // Nurse cannot initiate new referral (creation form restricted to doctor/admin)
        $this->actingAs($this->nurseUser)->get(route('portal.referrals.create'))
            ->assertStatus(403);

        // Unauthenticated guest redirected
        auth()->logout();
        $this->get(route('portal.referrals.index'))
            ->assertRedirect(route('portal.login'));
    }

    public function test_doctor_can_initiate_emergency_referral_with_assessment_and_icu_flag(): void
    {
        $payload = [
            'admission_id' => $this->admission->id,
            'patient_id' => $this->patient->id,
            'facility_name' => 'M. Abdur Rahim Medical College Hospital, Dinajpur',
            'destination_department' => 'Coronary Care Unit (CCU)',
            'referral_type' => 'icu_ccu_emergency',
            'is_critical' => 1,
            'requires_icu' => 1,
            'clinical_reason' => 'Acute anterior STEMI complicated by cardiogenic shock requiring emergency cardiac catheterization (PCI) and continuous inotropic ICU support.',
            'emergency_assessment' => 'Patient in severe distress. Diaphoretic, pale, cold extremities. Airway patent, bilateral basal crackles.',
            'interventions_given' => 'Aspirin 300mg + Clopidogrel 300mg + Atorvastatin 80mg loading dose administered. High flow O2 via mask 8L/min. IV Dopamine infusion initiated at 5 mcg/kg/min.',
            'receiving_doctor_contact' => 'Dr. Shahed (Duty Cardiologist) / 01711-223344',
            'blood_pressure_systolic' => 85,
            'blood_pressure_diastolic' => 50,
            'heart_rate' => 124,
            'respiratory_rate' => 28,
            'temperature_celsius' => 36.6,
            'spo2_percent' => 88,
            'consciousness_level' => 'alert',
            'gcs_score' => 14,
            'airway_status' => 'oxygen_needed',
            'transport_arranged' => 1,
            'transport_type' => 'ICU Ambulance with Ventilator',
            'ambulance_driver_contact' => '01712-334455',
            'accompanying_staff' => 'Sister Halima (Senior Staff Nurse)',
            'oxygen_support_transit' => 1,
        ];

        $response = $this->actingAs($this->doctorUser)->post(route('portal.referrals.store'), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('referrals', [
            'patient_id' => $this->patient->id,
            'admission_id' => $this->admission->id,
            'facility_name' => 'M. Abdur Rahim Medical College Hospital, Dinajpur',
            'destination_department' => 'Coronary Care Unit (CCU)',
            'referral_type' => 'icu_ccu_emergency',
            'is_critical' => true,
            'requires_icu' => true,
            'status' => 'initiated',
            'authorized_by' => $this->doctorUser->id,
        ]);

        $referral = Referral::where('patient_id', $this->patient->id)->first();
        $this->assertNotNull($referral);
        $this->assertStringStartsWith('REF-', $referral->referral_number);
        $this->assertEquals(85, $referral->vitals_snapshot['bp_systolic']);
        $this->assertEquals(88, $referral->vitals_snapshot['spo2']);
        $this->assertEquals(14, $referral->vitals_snapshot['gcs_score']);

        // Check history
        $this->assertDatabaseHas('referral_histories', [
            'referral_id' => $referral->id,
            'action' => 'initiated',
            'new_status' => 'initiated',
            'changed_by' => $this->doctorUser->id,
        ]);
    }

    public function test_nurse_and_doctor_can_dispatch_patient_in_transit_via_ambulance(): void
    {
        $referral = Referral::create([
            'referral_number' => 'REF-' . date('Ymd') . '-' . rand(1000, 9999),
            'patient_id' => $this->patient->id,
            'admission_id' => $this->admission->id,
            'facility_name' => 'M. Abdur Rahim Medical College Hospital, Dinajpur',
            'referral_type' => 'icu_ccu_emergency',
            'is_critical' => true,
            'requires_icu' => true,
            'clinical_reason' => 'Severe acute respiratory distress syndrome (ARDS) requiring mechanical ventilation.',
            'status' => 'initiated',
            'authorized_by' => $this->doctorUser->id,
            'referred_at' => now(),
        ]);

        // Nurse dispatches patient in transit
        $dispatchPayload = [
            'transport_type' => 'ICU Ambulance with Ventilator',
            'ambulance_driver_contact' => '01711-998877',
            'accompanying_staff' => 'Sister Rekha + Oxygen Attendant',
            'oxygen_support_transit' => 1,
        ];

        $response = $this->actingAs($this->nurseUser)->post(route('portal.referrals.dispatch', $referral), $dispatchPayload);

        $response->assertRedirect();
        $referral->refresh();

        $this->assertEquals('in_transit', $referral->status);
        $this->assertTrue($referral->transport_arranged);
        $this->assertEquals('ICU Ambulance with Ventilator', $referral->transport_type);
        $this->assertTrue($referral->oxygen_support_transit);

        $this->assertDatabaseHas('referral_histories', [
            'referral_id' => $referral->id,
            'action' => 'dispatched_in_transit',
            'new_status' => 'in_transit',
            'changed_by' => $this->nurseUser->id,
        ]);
    }

    public function test_clinical_staff_can_complete_tertiary_handover(): void
    {
        $referral = Referral::create([
            'referral_number' => 'REF-' . date('Ymd') . '-' . rand(1000, 9999),
            'patient_id' => $this->patient->id,
            'admission_id' => $this->admission->id,
            'facility_name' => 'M. Abdur Rahim Medical College Hospital, Dinajpur',
            'referral_type' => 'icu_ccu_emergency',
            'is_critical' => true,
            'requires_icu' => true,
            'clinical_reason' => 'Polytrauma with subdural hematoma requiring urgent neurosurgical decompression.',
            'status' => 'in_transit',
            'authorized_by' => $this->doctorUser->id,
            'referred_at' => now()->subHour(),
        ]);

        $completePayload = [
            'transferred_at' => now()->format('Y-m-d\TH:i'),
            'handover_notes' => 'Received by Dr. Mamun (Associate Professor, Neurosurgery) at Trauma ICU Bed #02. Handover file and CT scans delivered. Relatives briefed.',
        ];

        $response = $this->actingAs($this->doctorUser)->post(route('portal.referrals.complete', $referral), $completePayload);

        $response->assertRedirect();
        $referral->refresh();

        $this->assertEquals('transferred', $referral->status);
        $this->assertNotNull($referral->transferred_at);
        $this->assertStringContainsString('Received by Dr. Mamun', $referral->referral_notes);

        // Crucial Check: Inpatient stay at Meghna Clinic marked referred_external and bed released for cleaning!
        $this->admission->refresh();
        $this->assertEquals('referred_external', $this->admission->status);
        $this->assertNotNull($this->admission->discharge_date);

        if ($this->bed) {
            $this->bed->refresh();
            $this->assertEquals('cleaning', $this->bed->status);
        }

        $this->assertDatabaseHas('referral_histories', [
            'referral_id' => $referral->id,
            'action' => 'transferred',
            'new_status' => 'transferred',
        ]);
    }

    public function test_doctor_can_cancel_referral_with_reason(): void
    {
        $referral = Referral::create([
            'referral_number' => 'REF-' . date('Ymd') . '-' . rand(1000, 9999),
            'patient_id' => $this->patient->id,
            'admission_id' => $this->admission->id,
            'facility_name' => 'Dinajpur 250 Bed General Hospital (Sadar Hospital)',
            'referral_type' => 'tertiary_transfer',
            'clinical_reason' => 'Uncontrolled epistaxis requiring posterior nasal packing.',
            'status' => 'initiated',
            'authorized_by' => $this->doctorUser->id,
            'referred_at' => now(),
        ]);

        $cancelPayload = [
            'cancellation_reason' => 'Epistaxis successfully controlled locally with anterior pack and tranexamic acid. Vital signs normalized. Transfer deferred.',
        ];

        $response = $this->actingAs($this->doctorUser)->post(route('portal.referrals.cancel', $referral), $cancelPayload);

        $response->assertRedirect();
        $referral->refresh();

        $this->assertEquals('cancelled', $referral->status);
        $this->assertEquals($cancelPayload['cancellation_reason'], $referral->cancellation_reason);
        $this->assertNotNull($referral->cancelled_at);
        $this->assertEquals($this->doctorUser->id, $referral->cancelled_by);

        $this->assertDatabaseHas('referral_histories', [
            'referral_id' => $referral->id,
            'action' => 'cancelled',
            'new_status' => 'cancelled',
        ]);
    }

    public function test_clinician_can_update_emergency_assessment_and_vitals(): void
    {
        $referral = Referral::create([
            'referral_number' => 'REF-' . date('Ymd') . '-' . rand(1000, 9999),
            'patient_id' => $this->patient->id,
            'admission_id' => $this->admission->id,
            'facility_name' => 'M. Abdur Rahim Medical College Hospital, Dinajpur',
            'referral_type' => 'icu_ccu_emergency',
            'clinical_reason' => 'Acute exacerbation of COPD with hypercapnic respiratory failure.',
            'status' => 'initiated',
            'authorized_by' => $this->doctorUser->id,
            'referred_at' => now(),
        ]);

        $updatePayload = [
            'emergency_assessment' => 'Severe dyspnea, use of accessory muscles of respiration. ABG shows pH 7.22, PaCO2 68 mmHg (respiratory acidosis).',
            'interventions_given' => 'Nebulization with Salbutamol + Ipratropium given. IV Hydrocortisone 100mg. BiPAP non-invasive ventilation initiated awaiting ICU ambulance.',
            'blood_pressure_systolic' => 140,
            'blood_pressure_diastolic' => 90,
            'heart_rate' => 110,
            'respiratory_rate' => 32,
            'spo2_percent' => 91,
            'consciousness_level' => 'alert',
            'referral_notes' => 'Urgent BiPAP or invasive ventilation required at medical college ICU.',
        ];

        $response = $this->actingAs($this->doctorUser)->post(route('portal.referrals.assessment.update', $referral), $updatePayload);

        $response->assertRedirect();
        $referral->refresh();

        $this->assertStringContainsString('respiratory acidosis', $referral->emergency_assessment);
        $this->assertStringContainsString('BiPAP non-invasive ventilation', $referral->interventions_given);
        $this->assertEquals(140, $referral->vitals_snapshot['bp_systolic']);
        $this->assertEquals(91, $referral->vitals_snapshot['spo2']);

        $this->assertDatabaseHas('referral_histories', [
            'referral_id' => $referral->id,
            'action' => 'assessment_updated',
        ]);
    }

    public function test_authorized_staff_can_view_printable_handover_slip(): void
    {
        $opNumber = 'REF-' . date('Ymd') . '-' . rand(1000, 9999);
        $referral = Referral::create([
            'referral_number' => $opNumber,
            'patient_id' => $this->patient->id,
            'admission_id' => $this->admission->id,
            'facility_name' => 'M. Abdur Rahim Medical College Hospital, Dinajpur',
            'destination_department' => 'Medical ICU',
            'referral_type' => 'icu_ccu_emergency',
            'is_critical' => true,
            'requires_icu' => true,
            'clinical_reason' => 'Acute pulmonary edema secondary to severe mitral regurgitation.',
            'emergency_assessment' => 'Severe orthopnea, bilateral coarse crepitations, pink frothy sputum.',
            'interventions_given' => 'IV Furosemide 80mg, CPAP support, sublingual GTN.',
            'vitals_snapshot' => [
                'bp_systolic' => 170,
                'bp_diastolic' => 105,
                'heart_rate' => 118,
                'spo2' => 86,
            ],
            'status' => 'initiated',
            'authorized_by' => $this->doctorUser->id,
            'referred_at' => now(),
        ]);

        $response = $this->actingAs($this->doctorUser)->get(route('portal.referrals.print', $referral));

        $response->assertStatus(200);
        $response->assertSee('MEGHNA CLINIC', false);
        $response->assertSee($opNumber);
        $response->assertSee('M. Abdur Rahim Medical College Hospital, Dinajpur');
        $response->assertSee('Acute pulmonary edema secondary to severe mitral regurgitation');
        $response->assertSee('Dr. ' . $this->doctorUser->name);
    }
}
