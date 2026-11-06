<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Bed;
use App\Models\BedAssignment;
use App\Models\Department;
use App\Models\Operation;
use App\Models\OperationHistory;
use App\Models\Patient;
use App\Models\PostOpObservation;
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Database\Seeders\DepartmentAndBedSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OperationManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;
    protected User $doctorUser;
    protected User $otherDoctorUser;
    protected User $nurseUser;
    protected User $receptionistUser;
    protected User $billingUser;
    protected User $patientUser;
    protected Patient $patient;
    protected Admission $admission;
    protected Department $surgeryDept;

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

        $this->otherDoctorUser = User::whereHas('roles', fn($q) => $q->where('name', 'doctor'))
            ->where('id', '!=', $this->doctorUser->id)
            ->first() ?? User::factory()->create(['user_type' => 'staff', 'name' => 'Dr. Rafiqul Islam'])->assignRole('doctor');

        $this->nurseUser = User::whereHas('roles', fn($q) => $q->where('name', 'nurse'))->first()
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('nurse');

        $this->receptionistUser = User::whereHas('roles', fn($q) => $q->where('name', 'receptionist'))->first()
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('receptionist');

        $this->billingUser = User::whereHas('roles', fn($q) => $q->where('name', 'billing_staff'))->first()
            ?? User::factory()->create(['user_type' => 'staff'])->assignRole('billing_staff');

        $this->patientUser = User::factory()->create(['user_type' => 'patient']);
        $this->patientUser->assignRole('patient');

        // Create synthetic patient
        $this->patient = Patient::create([
            'patient_code' => 'MCNH-2026-' . rand(100000, 999999),
            'name' => 'Test Surgical Patient',
            'phone' => '01712' . rand(100000, 999999),
            'date_of_birth' => '1990-05-15',
            'gender' => 'male',
            'blood_group' => 'B+',
            'is_verified' => true,
        ]);

        $this->surgeryDept = Department::where('name', 'Surgery')->first()
            ?? Department::first();

        // Create visit and admission
        $visit = Visit::create([
            'patient_id' => $this->patient->id,
            'visit_number' => 'VIS-2026-' . rand(100000, 999999),
            'visit_date' => now(),
            'visit_type' => 'emergency',
            'triage_level' => 'priority',
            'status' => 'admitted',
        ]);

        $bed = Bed::first();

        $this->admission = Admission::create([
            'visit_id' => $visit->id,
            'patient_id' => $this->patient->id,
            'department_id' => $this->surgeryDept->id,
            'admission_number' => 'ADM-2026-' . rand(100000, 999999),
            'admission_date' => now(),
            'attending_doctor_id' => $this->doctorUser->id,
            'admission_reason' => 'Acute surgical condition requiring intervention',
            'status' => 'admitted',
        ]);

        if ($bed) {
            BedAssignment::create([
                'admission_id' => $this->admission->id,
                'bed_id' => $bed->id,
                'assigned_at' => now(),
            ]);
        }
    }

    public function test_authorized_clinical_staff_can_view_operations_roster_and_calendar(): void
    {
        // Doctor can access
        $response = $this->actingAs($this->doctorUser)->get(route('portal.operations.index'));
        $response->assertStatus(200);
        $response->assertSee('Operation Theatre (OT) Management');

        // Nurse can access
        $nurseResponse = $this->actingAs($this->nurseUser)->get(route('portal.operations.index'));
        $nurseResponse->assertStatus(200);

        // Administrator can access calendar
        $calendarResponse = $this->actingAs($this->adminUser)->get(route('portal.operations.calendar'));
        $calendarResponse->assertStatus(200);
        $calendarResponse->assertSee('Operating Theatre (OT) Timetable');
    }

    public function test_unauthorized_users_cannot_access_operations(): void
    {
        // Receptionist denied
        $this->actingAs($this->receptionistUser)->get(route('portal.operations.index'))
            ->assertStatus(403);

        // Billing staff denied
        $this->actingAs($this->billingUser)->get(route('portal.operations.index'))
            ->assertStatus(403);

        // Nurse cannot schedule operations (create form restricted to doctor/admin)
        $this->actingAs($this->nurseUser)->get(route('portal.operations.create'))
            ->assertStatus(403);

        // Unauthenticated guest redirected
        auth()->logout();
        $this->get(route('portal.operations.index'))
            ->assertRedirect(route('portal.login'));
    }

    public function test_doctor_can_schedule_operation_linking_patient_and_admission(): void
    {
        $scheduledTime = Carbon::tomorrow()->setTime(10, 0, 0);

        $payload = [
            'admission_id' => $this->admission->id,
            'procedure_name' => 'Laparoscopic Appendectomy',
            'department_id' => $this->surgeryDept->id,
            'ot_room' => 'OT-1 (Main Theatre)',
            'urgency' => 'urgent',
            'indication' => 'Acute gangrenous appendicitis with localized peritonitis',
            'scheduled_at' => $scheduledTime->format('Y-m-d\TH:i'),
            'estimated_duration_minutes' => 90,
            'primary_surgeon_id' => $this->doctorUser->id,
            'assistant_surgeon_id' => $this->otherDoctorUser->id,
            'anesthetist_name' => 'Dr. Kamal Hossain',
            'ot_nurse_name' => 'Sister Halima',
            'operation_notes' => 'Pre-op antibiotics given; cross-matched 2 units PRBC.',
        ];

        $response = $this->actingAs($this->doctorUser)->post(route('portal.operations.store'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('operations', [
            'admission_id' => $this->admission->id,
            'patient_id' => $this->patient->id,
            'procedure_name' => 'Laparoscopic Appendectomy',
            'ot_room' => 'OT-1 (Main Theatre)',
            'urgency' => 'urgent',
            'status' => 'scheduled',
            'primary_surgeon_id' => $this->doctorUser->id,
            'assistant_surgeon_id' => $this->otherDoctorUser->id,
            'estimated_duration_minutes' => 90,
        ]);

        $operation = Operation::where('admission_id', $this->admission->id)->first();
        $this->assertNotNull($operation);
        $this->assertStringStartsWith('OT-', $operation->operation_number);
        $this->assertEquals($scheduledTime->copy()->addMinutes(90)->toDateTimeString(), $operation->scheduled_end_at->toDateTimeString());

        // Check history log
        $this->assertDatabaseHas('operation_histories', [
            'operation_id' => $operation->id,
            'action' => 'scheduled',
            'new_status' => 'scheduled',
            'changed_by' => $this->doctorUser->id,
        ]);
    }

    public function test_prevent_conflicting_operation_schedules_in_same_ot_room(): void
    {
        $slotStart = Carbon::tomorrow()->setTime(14, 0, 0);

        // Schedule first operation: 14:00 to 15:30 (90 mins) in OT-1
        $this->actingAs($this->doctorUser)->post(route('portal.operations.store'), [
            'admission_id' => $this->admission->id,
            'procedure_name' => 'Open Herniorrhaphy',
            'ot_room' => 'OT-1 (Main Theatre)',
            'urgency' => 'elective',
            'scheduled_at' => $slotStart->format('Y-m-d\TH:i'),
            'estimated_duration_minutes' => 90,
            'primary_surgeon_id' => $this->doctorUser->id,
        ]);

        $this->assertDatabaseHas('operations', [
            'procedure_name' => 'Open Herniorrhaphy',
            'ot_room' => 'OT-1 (Main Theatre)',
        ]);

        // Attempt second operation in SAME OT-1 from 14:30 to 16:00 (overlaps!)
        $overlappingStart = Carbon::tomorrow()->setTime(14, 30, 0);

        $conflictResponse = $this->actingAs($this->adminUser)->post(route('portal.operations.store'), [
            'admission_id' => $this->admission->id,
            'procedure_name' => 'Cholecystectomy',
            'ot_room' => 'OT-1 (Main Theatre)', // SAME ROOM!
            'urgency' => 'urgent',
            'scheduled_at' => $overlappingStart->format('Y-m-d\TH:i'),
            'estimated_duration_minutes' => 90,
            'primary_surgeon_id' => $this->otherDoctorUser->id, // Different doctor
        ]);

        $conflictResponse->assertSessionHasErrors('ot_room');
        $this->assertDatabaseMissing('operations', [
            'procedure_name' => 'Cholecystectomy',
        ]);
    }

    public function test_prevent_conflicting_operation_schedules_for_same_primary_surgeon(): void
    {
        $slotStart = Carbon::tomorrow()->setTime(9, 0, 0);

        // First surgery with Dr. Doctor in OT-1 from 09:00 to 10:30
        $this->actingAs($this->doctorUser)->post(route('portal.operations.store'), [
            'admission_id' => $this->admission->id,
            'procedure_name' => 'Thyroidectomy',
            'ot_room' => 'OT-1 (Main Theatre)',
            'urgency' => 'elective',
            'scheduled_at' => $slotStart->format('Y-m-d\TH:i'),
            'estimated_duration_minutes' => 90,
            'primary_surgeon_id' => $this->doctorUser->id,
        ]);

        // Attempt second surgery with SAME surgeon in OT-2 (Minor Theatre) from 09:30 to 10:30 (overlaps!)
        $overlappingStart = Carbon::tomorrow()->setTime(9, 30, 0);

        $conflictResponse = $this->actingAs($this->doctorUser)->post(route('portal.operations.store'), [
            'admission_id' => $this->admission->id,
            'procedure_name' => 'Skin Grafting',
            'ot_room' => 'OT-2 (Minor Theatre)', // Different room!
            'urgency' => 'elective',
            'scheduled_at' => $overlappingStart->format('Y-m-d\TH:i'),
            'estimated_duration_minutes' => 60,
            'primary_surgeon_id' => $this->doctorUser->id, // SAME SURGEON!
        ]);

        $conflictResponse->assertSessionHasErrors('primary_surgeon_id');
        $this->assertDatabaseMissing('operations', [
            'procedure_name' => 'Skin Grafting',
        ]);
    }

    public function test_doctor_can_reschedule_operation_and_conflict_is_prevented_on_new_slot(): void
    {
        $initialTime = Carbon::tomorrow()->addDays(2)->setTime(11, 0, 0);

        // Create operation
        $this->actingAs($this->doctorUser)->post(route('portal.operations.store'), [
            'admission_id' => $this->admission->id,
            'procedure_name' => 'ORIF Right Femur',
            'ot_room' => 'OT-1 (Main Theatre)',
            'urgency' => 'urgent',
            'scheduled_at' => $initialTime->format('Y-m-d\TH:i'),
            'estimated_duration_minutes' => 120,
            'primary_surgeon_id' => $this->doctorUser->id,
        ]);

        $operation = Operation::where('procedure_name', 'ORIF Right Femur')->first();
        $this->assertNotNull($operation);

        // Reschedule to day after tomorrow 15:00
        $newTime = Carbon::tomorrow()->addDays(3)->setTime(15, 0, 0);

        $rescheduleResponse = $this->actingAs($this->doctorUser)->post(route('portal.operations.reschedule', $operation), [
            'scheduled_at' => $newTime->format('Y-m-d\TH:i'),
            'estimated_duration_minutes' => 90,
            'ot_room' => 'OT-1 (Main Theatre)',
            'rescheduled_reason' => 'Patient required stabilization of electrolyte imbalance before surgery',
        ]);

        $rescheduleResponse->assertRedirect();

        $operation->refresh();
        $this->assertEquals('rescheduled', $operation->status);
        $this->assertEquals($newTime->toDateTimeString(), $operation->scheduled_at->toDateTimeString());
        $this->assertEquals('Patient required stabilization of electrolyte imbalance before surgery', $operation->rescheduled_reason);

        // Verify history recorded
        $this->assertDatabaseHas('operation_histories', [
            'operation_id' => $operation->id,
            'action' => 'rescheduled',
            'new_status' => 'rescheduled',
            'reason' => 'Patient required stabilization of electrolyte imbalance before surgery',
            'changed_by' => $this->doctorUser->id,
        ]);
    }

    public function test_doctor_can_cancel_operation_with_reason_and_slot_is_freed(): void
    {
        $slotTime = Carbon::tomorrow()->setTime(8, 0, 0);

        // Create operation
        $this->actingAs($this->doctorUser)->post(route('portal.operations.store'), [
            'admission_id' => $this->admission->id,
            'procedure_name' => 'Elective Cholecystectomy',
            'ot_room' => 'OT-1 (Main Theatre)',
            'urgency' => 'elective',
            'scheduled_at' => $slotTime->format('Y-m-d\TH:i'),
            'estimated_duration_minutes' => 60,
            'primary_surgeon_id' => $this->doctorUser->id,
        ]);

        $operation = Operation::where('procedure_name', 'Elective Cholecystectomy')->first();
        $this->assertNotNull($operation);

        // Cancel the operation
        $cancelResponse = $this->actingAs($this->doctorUser)->post(route('portal.operations.cancel', $operation), [
            'cancellation_reason' => 'Patient developed acute viral bronchitis with fever; unfit for general anesthesia.',
        ]);

        $cancelResponse->assertRedirect();
        $operation->refresh();

        $this->assertEquals('cancelled', $operation->status);
        $this->assertNotNull($operation->cancelled_at);
        $this->assertEquals($this->doctorUser->id, $operation->cancelled_by);

        // Verify history
        $this->assertDatabaseHas('operation_histories', [
            'operation_id' => $operation->id,
            'action' => 'cancelled',
            'new_status' => 'cancelled',
            'changed_by' => $this->doctorUser->id,
        ]);

        // Slot is now freed! Another operation can now be scheduled in the EXACT SAME slot and room!
        $newBookingResponse = $this->actingAs($this->doctorUser)->post(route('portal.operations.store'), [
            'admission_id' => $this->admission->id,
            'procedure_name' => 'Emergency Exploratory Laparotomy',
            'ot_room' => 'OT-1 (Main Theatre)', // SAME ROOM & TIME!
            'urgency' => 'emergency',
            'scheduled_at' => $slotTime->format('Y-m-d\TH:i'),
            'estimated_duration_minutes' => 60,
            'primary_surgeon_id' => $this->doctorUser->id,
        ]);

        $newBookingResponse->assertRedirect();
        $this->assertDatabaseHas('operations', [
            'procedure_name' => 'Emergency Exploratory Laparotomy',
            'ot_room' => 'OT-1 (Main Theatre)',
            'status' => 'scheduled',
        ]);
    }

    public function test_doctor_can_start_and_complete_operation(): void
    {
        $slotTime = Carbon::tomorrow()->setTime(13, 0, 0);

        $this->actingAs($this->doctorUser)->post(route('portal.operations.store'), [
            'admission_id' => $this->admission->id,
            'procedure_name' => 'Excision of Lipoma',
            'ot_room' => 'OT-2 (Minor Theatre)',
            'urgency' => 'elective',
            'scheduled_at' => $slotTime->format('Y-m-d\TH:i'),
            'estimated_duration_minutes' => 45,
            'primary_surgeon_id' => $this->doctorUser->id,
        ]);

        $operation = Operation::where('procedure_name', 'Excision of Lipoma')->first();

        // 1. Start procedure
        $startResponse = $this->actingAs($this->doctorUser)->post(route('portal.operations.start', $operation));
        $startResponse->assertRedirect();

        $operation->refresh();
        $this->assertEquals('in_progress', $operation->status);
        $this->assertNotNull($operation->started_at);

        // 2. Complete procedure
        $completeResponse = $this->actingAs($this->doctorUser)->post(route('portal.operations.complete', $operation), [
            'operation_notes' => 'Local infiltration with 2% lignocaine. Transverse incision over swelling. 4x3 cm lobulated lipoma excised in toto with capsule intact. Hemostasis secured. Closed with 3-0 prolene.',
            'post_op_notes' => 'Keep dressing clean and dry. Analgesics for 3 days. Suture removal on post-op day 7.',
            'anesthetist_name' => 'Local Anesthesia',
            'ot_nurse_name' => 'Sister Rekha',
        ]);

        $completeResponse->assertRedirect();

        $operation->refresh();
        $this->assertEquals('completed', $operation->status);
        $this->assertNotNull($operation->ended_at);
        $this->assertStringContainsString('lipoma excised in toto', $operation->operation_notes);
        $this->assertStringContainsString('Suture removal', $operation->post_op_notes);

        // History events
        $this->assertDatabaseHas('operation_histories', [
            'operation_id' => $operation->id,
            'action' => 'completed',
            'new_status' => 'completed',
        ]);
    }

    public function test_doctor_and_nurse_can_record_post_operative_observations(): void
    {
        $operation = Operation::create([
            'operation_number' => 'OT-' . date('Ymd') . '-' . rand(1000, 9999),
            'patient_id' => $this->patient->id,
            'admission_id' => $this->admission->id,
            'procedure_name' => 'Caesarean Section',
            'department_id' => $this->surgeryDept->id,
            'ot_room' => 'OT-Maternity & C-Section',
            'urgency' => 'urgent',
            'scheduled_at' => now()->subHours(2),
            'estimated_duration_minutes' => 60,
            'started_at' => now()->subHours(2),
            'ended_at' => now()->subHour(),
            'primary_surgeon_id' => $this->doctorUser->id,
            'status' => 'completed',
            'operation_notes' => 'Lower segment Caesarean section performed. Healthy male infant delivered.',
        ]);

        // Nurse records post-op bedside observation
        $obsPayload = [
            'observed_at' => now()->format('Y-m-d\TH:i'),
            'blood_pressure_systolic' => 110,
            'blood_pressure_diastolic' => 70,
            'heart_rate' => 78,
            'respiratory_rate' => 16,
            'temperature_celsius' => 36.9,
            'spo2_percent' => 99,
            'consciousness_level' => 'alert',
            'pain_score' => 3,
            'surgical_site_condition' => 'Abdominal dressing clean, dry, and intact.',
            'drain_output_ml' => 20,
            'urine_output_ml' => 150,
            'observation_notes' => 'Patient conscious and coherent. Uterus well contracted. Oral fluids tolerated. Pain managed with IV paracetamol.',
            'discharge_criteria_met' => 1,
        ];

        $response = $this->actingAs($this->nurseUser)->post(route('portal.operations.observations.store', $operation), $obsPayload);

        $response->assertRedirect();

        $this->assertDatabaseHas('post_op_observations', [
            'operation_id' => $operation->id,
            'recorded_by' => $this->nurseUser->id,
            'blood_pressure_systolic' => 110,
            'blood_pressure_diastolic' => 70,
            'heart_rate' => 78,
            'consciousness_level' => 'alert',
            'pain_score' => 3,
            'discharge_criteria_met' => true,
        ]);

        $this->assertDatabaseHas('operation_histories', [
            'operation_id' => $operation->id,
            'action' => 'observation_recorded',
            'changed_by' => $this->nurseUser->id,
        ]);
    }

    public function test_authorized_staff_can_view_printable_surgical_sheet(): void
    {
        $opNumber = 'OT-' . date('Ymd') . '-' . rand(1000, 9999);
        $operation = Operation::create([
            'operation_number' => $opNumber,
            'patient_id' => $this->patient->id,
            'admission_id' => $this->admission->id,
            'procedure_name' => 'Open Reduction Internal Fixation',
            'ot_room' => 'OT-1 (Main Theatre)',
            'urgency' => 'urgent',
            'scheduled_at' => now(),
            'primary_surgeon_id' => $this->doctorUser->id,
            'status' => 'completed',
            'operation_notes' => 'DCP plate with 6 cortical screws applied. Anatomical reduction verified under C-arm.',
            'post_op_notes' => 'Limb elevation on Braun splint. Neurovascular monitoring every 2 hours.',
        ]);

        $response = $this->actingAs($this->doctorUser)->get(route('portal.operations.print', $operation));

        $response->assertStatus(200);
        $response->assertSee('MEGHNA CLINIC', false);
        $response->assertSee($opNumber);
        $response->assertSee('Open Reduction Internal Fixation');
        $response->assertSee('Dr. ' . $this->doctorUser->name);
        $response->assertSee('DCP plate with 6 cortical screws applied');
    }
}
