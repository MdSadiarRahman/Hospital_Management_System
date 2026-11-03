<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\AppointmentHistory;
use App\Models\Department;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DepartmentAndBedSeeder;
use Database\Seeders\DoctorScheduleSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Database\Seeders\UserSeeder;
use Tests\TestCase;

class AppointmentManagementTest extends TestCase
{
    protected User $adminUser;
    protected User $receptionistUser;
    protected User $doctorUser;
    protected User $patientUser;
    protected User $otherPatientUser;
    protected Department $gynDept;

    protected function setUp(): void
    {
        parent::setUp();

        if (Role::count() === 0) {
            $this->seed(RolePermissionSeeder::class);
            $this->seed(DepartmentAndBedSeeder::class);
            $this->seed(SystemSettingsSeeder::class);
            $this->seed(UserSeeder::class);
        }

        if (DoctorSchedule::count() === 0) {
            $this->seed(DoctorScheduleSeeder::class);
        }

        $adminRole = Role::where('name', 'administrator')->firstOrFail();
        $receptionistRole = Role::where('name', 'receptionist')->firstOrFail();
        $doctorRole = Role::where('name', 'doctor')->firstOrFail();
        $patientRole = Role::where('name', 'patient')->firstOrFail();

        // 1. Admin
        $this->adminUser = User::updateOrCreate(
            ['email' => 'admin.apt.test@meghnaclinic.com'],
            [
                'name' => 'Admin Appointment Tester',
                'phone' => '01718880001',
                'password' => bcrypt('Password123!'),
                'user_type' => 'staff',
                'is_active' => true,
            ]
        );
        $this->adminUser->roles()->syncWithoutDetaching([$adminRole->id]);

        // 2. Receptionist
        $this->receptionistUser = User::updateOrCreate(
            ['email' => 'reception.apt.test@meghnaclinic.com'],
            [
                'name' => 'Reception Appointment Officer',
                'phone' => '01718880002',
                'password' => bcrypt('Password123!'),
                'user_type' => 'staff',
                'is_active' => true,
            ]
        );
        $this->receptionistUser->roles()->syncWithoutDetaching([$receptionistRole->id]);

        // 3. Doctor (Dr. Nasreen Sultana)
        $this->doctorUser = User::where('email', 'doctor@meghnaclinic.com')->first();
        if (!$this->doctorUser) {
            $this->doctorUser = User::updateOrCreate(
                ['email' => 'dr.nasreen.test@meghnaclinic.com'],
                [
                    'name' => 'Dr. Nasreen Sultana',
                    'phone' => '01718880003',
                    'password' => bcrypt('Password123!'),
                    'user_type' => 'doctor',
                    'is_active' => true,
                ]
            );
            $this->doctorUser->roles()->syncWithoutDetaching([$doctorRole->id]);
        }

        $this->gynDept = Department::where('code', 'GYN')->first() ?? Department::first();

        // Ensure Dr. Nasreen has schedule on Monday
        DoctorSchedule::updateOrCreate(
            [
                'doctor_id' => $this->doctorUser->id,
                'day_of_week' => 'monday',
                'start_time' => '09:00:00',
            ],
            [
                'department_id' => $this->gynDept->id,
                'end_time' => '13:00:00',
                'slot_duration_minutes' => 20,
                'max_patients' => 15,
                'room_number' => 'OPD Room 101',
                'is_active' => true,
            ]
        );

        // 4. Primary Patient User
        $this->patientUser = User::updateOrCreate(
            ['email' => 'patient.apt.test@meghnaclinic.com'],
            [
                'name' => 'Fatema Begum',
                'phone' => '01718880004',
                'password' => bcrypt('Password123!'),
                'user_type' => 'patient',
                'is_active' => true,
            ]
        );
        $this->patientUser->roles()->syncWithoutDetaching([$patientRole->id]);

        $patientRecord = Patient::updateOrCreate(
            ['patient_code' => 'MCNH-2026-9901'],
            [
                'user_id' => $this->patientUser->id,
                'name' => 'Fatema Begum',
                'gender' => 'female',
                'date_of_birth' => '1995-05-15',
                'blood_group' => 'A+',
                'phone' => '01718880004',
                'address' => 'Shohid Minar Mor, Dinajpur',
                'is_verified' => true,
                'verified_by_user_id' => $this->adminUser->id,
                'verified_at' => now(),
            ]
        );
        $this->patientUser->refresh();

        // 5. Secondary Patient User (for authorization checks)
        $this->otherPatientUser = User::updateOrCreate(
            ['email' => 'other.patient.test@meghnaclinic.com'],
            [
                'name' => 'Rahim Uddin',
                'phone' => '01718880005',
                'password' => bcrypt('Password123!'),
                'user_type' => 'patient',
                'is_active' => true,
            ]
        );
        $this->otherPatientUser->roles()->syncWithoutDetaching([$patientRole->id]);
    }

    /**
     * Helper to get next Monday date string (ensured scheduled)
     */
    protected function getNextScheduledDate(): string
    {
        return Carbon::now()->next(Carbon::MONDAY)->toDateString();
    }

    /**
     * Helper to get next Friday date string (ensured doctor off-day)
     */
    protected function getNextUnscheduledDate(): string
    {
        return Carbon::now()->next(Carbon::FRIDAY)->toDateString();
    }

    /**
     * Test 1: Public website visitor can submit an appointment request.
     */
    public function test_public_website_visitor_can_request_appointment(): void
    {
        $testDate = $this->getNextScheduledDate();
        $testTime = '09:00:00';

        // Clean any existing test booking on this slot
        Appointment::where('doctor_id', $this->doctorUser->id)
            ->whereDate('appointment_date', $testDate)
            ->where('appointment_time', $testTime)
            ->delete();

        $response = $this->post(route('appointments.store'), [
            'guest_name' => 'Rokeya Sultana',
            'guest_phone' => '01712000011',
            'guest_email' => 'rokeya.guest@example.com',
            'department_id' => $this->gynDept->id,
            'doctor_id' => $this->doctorUser->id,
            'appointment_date' => $testDate,
            'appointment_time' => $testTime,
            'reason' => 'Routine prenatal consultation',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('appointment_success', true);

        $this->assertDatabaseHas('appointments', [
            'doctor_id' => $this->doctorUser->id,
            'department_id' => $this->gynDept->id,
            'guest_name' => 'Rokeya Sultana',
            'guest_phone' => '01712000011',
            'status' => Appointment::STATUS_PENDING,
        ]);

        $appointment = Appointment::where('guest_email', 'rokeya.guest@example.com')->latest()->first();
        $this->assertNotNull($appointment);
        $this->assertNull($appointment->serial_number, 'Pending appointment should not have a serial number assigned yet.');

        // History entry
        $this->assertDatabaseHas('appointment_histories', [
            'appointment_id' => $appointment->id,
            'action' => 'requested',
            'new_status' => Appointment::STATUS_PENDING,
        ]);
    }

    /**
     * Test 2: Cannot book appointment when doctor has no schedule on the selected date.
     */
    public function test_cannot_book_appointment_when_doctor_has_no_schedule_on_selected_day(): void
    {
        $fridayDate = $this->getNextUnscheduledDate();

        $response = $this->post(route('appointments.store'), [
            'guest_name' => 'Rokeya OffDay',
            'guest_phone' => '01712000012',
            'department_id' => $this->gynDept->id,
            'doctor_id' => $this->doctorUser->id,
            'appointment_date' => $fridayDate,
            'appointment_time' => '09:00:00',
            'reason' => 'Should fail due to doctor off-day',
        ]);

        $response->assertSessionHasErrors('appointment_date');
    }

    /**
     * Test 3: Prevents double booking on the same doctor and time slot.
     */
    public function test_prevents_conflicting_double_booking_on_same_slot(): void
    {
        $testDate = $this->getNextScheduledDate();
        $testTime = '09:20:00';

        // Clear existing slot
        Appointment::where('doctor_id', $this->doctorUser->id)
            ->whereDate('appointment_date', $testDate)
            ->where('appointment_time', $testTime)
            ->delete();

        // 1st booking: should succeed
        $firstResponse = $this->post(route('appointments.store'), [
            'guest_name' => 'First Patient',
            'guest_phone' => '01712000021',
            'department_id' => $this->gynDept->id,
            'doctor_id' => $this->doctorUser->id,
            'appointment_date' => $testDate,
            'appointment_time' => $testTime,
            'reason' => 'First booking',
        ]);
        $firstResponse->assertSessionHas('appointment_success', true);

        // 2nd booking for EXACT same doctor, date and time: must fail
        $secondResponse = $this->post(route('appointments.store'), [
            'guest_name' => 'Second Conflicting Patient',
            'guest_phone' => '01712000022',
            'department_id' => $this->gynDept->id,
            'doctor_id' => $this->doctorUser->id,
            'appointment_date' => $testDate,
            'appointment_time' => $testTime,
            'reason' => 'Conflicting booking attempt',
        ]);

        $secondResponse->assertSessionHasErrors('appointment_time');
    }

    /**
     * Test 4: Registered patient can book an appointment through Patient Self-Care Portal.
     */
    public function test_registered_patient_can_request_appointment_in_portal(): void
    {
        $testDate = $this->getNextScheduledDate();
        $testTime = '09:40:00';

        Appointment::where('doctor_id', $this->doctorUser->id)
            ->whereDate('appointment_date', $testDate)
            ->where('appointment_time', $testTime)
            ->delete();

        $response = $this->actingAs($this->patientUser)
            ->post(route('patient.appointments.store'), [
                'department_id' => $this->gynDept->id,
                'doctor_id' => $this->doctorUser->id,
                'appointment_date' => $testDate,
                'appointment_time' => $testTime,
                'reason' => 'Patient self-care appointment',
            ]);

        $response->assertRedirect(route('patient.appointments.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('appointments', [
            'doctor_id' => $this->doctorUser->id,
            'patient_id' => $this->patientUser->patient->id,
            'status' => Appointment::STATUS_PENDING,
        ]);
    }

    /**
     * Test 5: Receptionist can approve and confirm a pending appointment, assigning a serial.
     */
    public function test_receptionist_can_approve_and_confirm_pending_appointment(): void
    {
        $testDate = $this->getNextScheduledDate();
        $testTime = '10:00:00';

        Appointment::where('doctor_id', $this->doctorUser->id)
            ->whereDate('appointment_date', $testDate)
            ->where('appointment_time', $testTime)
            ->delete();

        // Create pending appointment
        $appointment = Appointment::create([
            'patient_id' => $this->patientUser->patient->id,
            'doctor_id' => $this->doctorUser->id,
            'department_id' => $this->gynDept->id,
            'appointment_date' => $testDate . ' ' . $testTime,
            'appointment_time' => $testTime,
            'status' => Appointment::STATUS_PENDING,
            'type' => 'walk_in',
        ]);

        $response = $this->actingAs($this->receptionistUser)
            ->post(route('portal.appointments.confirm', $appointment->id), [
                'notes' => 'Confirmed by front desk reception',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $appointment->refresh();
        $this->assertEquals(Appointment::STATUS_CONFIRMED, $appointment->status);
        $this->assertNotNull($appointment->serial_number);
        $this->assertGreaterThanOrEqual(1, $appointment->serial_number);
        $this->assertEquals($this->receptionistUser->id, $appointment->confirmed_by);
        $this->assertNotNull($appointment->confirmed_at);

        // History entry
        $this->assertDatabaseHas('appointment_histories', [
            'appointment_id' => $appointment->id,
            'action' => 'confirmed',
            'new_status' => Appointment::STATUS_CONFIRMED,
            'changed_by' => $this->receptionistUser->id,
        ]);
    }

    /**
     * Test 6: Receptionist can create a walk-in appointment with auto-confirm.
     */
    public function test_receptionist_can_create_walk_in_appointment_with_auto_confirm(): void
    {
        $testDate = $this->getNextScheduledDate();
        $testTime = '10:20:00';

        Appointment::where('doctor_id', $this->doctorUser->id)
            ->whereDate('appointment_date', $testDate)
            ->where('appointment_time', $testTime)
            ->delete();

        $response = $this->actingAs($this->receptionistUser)
            ->post(route('portal.appointments.store'), [
                'booking_mode' => 'guest',
                'guest_name' => 'Walk-in Test Patient',
                'guest_phone' => '01712999888',
                'department_id' => $this->gynDept->id,
                'doctor_id' => $this->doctorUser->id,
                'appointment_date' => $testDate,
                'appointment_time' => $testTime,
                'auto_confirm' => '1',
                'reason' => 'Walk-in acute abdominal pain',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'guest_name' => 'Walk-in Test Patient',
            'status' => Appointment::STATUS_CONFIRMED,
            'confirmed_by' => $this->receptionistUser->id,
        ]);
    }

    /**
     * Test 7: Rescheduling an appointment updates the schedule and logs history.
     */
    public function test_appointment_can_be_rescheduled_to_another_slot(): void
    {
        $testDate = $this->getNextScheduledDate();
        $originalTime = '10:40:00';
        $newTime = '11:00:00';

        Appointment::where('doctor_id', $this->doctorUser->id)
            ->whereDate('appointment_date', $testDate)
            ->whereIn('appointment_time', [$originalTime, $newTime])
            ->delete();

        $appointment = Appointment::create([
            'patient_id' => $this->patientUser->patient->id,
            'doctor_id' => $this->doctorUser->id,
            'department_id' => $this->gynDept->id,
            'appointment_date' => $testDate . ' ' . $originalTime,
            'appointment_time' => $originalTime,
            'status' => Appointment::STATUS_CONFIRMED,
            'serial_number' => 5,
            'type' => 'walk_in',
        ]);

        $response = $this->actingAs($this->receptionistUser)
            ->post(route('portal.appointments.reschedule', $appointment->id), [
                'new_date' => $testDate,
                'new_time' => $newTime,
                'reschedule_reason' => 'Doctor requested schedule adjustment',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $appointment->refresh();
        $this->assertEquals($newTime, $appointment->appointment_time);
        $this->assertEquals(Appointment::STATUS_RESCHEDULED, $appointment->status);

        // History entry
        $this->assertDatabaseHas('appointment_histories', [
            'appointment_id' => $appointment->id,
            'action' => 'rescheduled',
            'new_status' => Appointment::STATUS_RESCHEDULED,
            'changed_by' => $this->receptionistUser->id,
        ]);
    }

    /**
     * Test 8: Cancellation requires a valid reason and records auditor metadata.
     */
    public function test_appointment_cancellation_requires_reason(): void
    {
        $testDate = $this->getNextScheduledDate();
        $testTime = '11:20:00';

        Appointment::where('doctor_id', $this->doctorUser->id)
            ->whereDate('appointment_date', $testDate)
            ->where('appointment_time', $testTime)
            ->delete();

        $appointment = Appointment::create([
            'patient_id' => $this->patientUser->patient->id,
            'doctor_id' => $this->doctorUser->id,
            'department_id' => $this->gynDept->id,
            'appointment_date' => $testDate . ' ' . $testTime,
            'appointment_time' => $testTime,
            'status' => Appointment::STATUS_CONFIRMED,
            'serial_number' => 7,
            'type' => 'walk_in',
        ]);

        // Missing reason: must fail
        $failResponse = $this->actingAs($this->receptionistUser)
            ->post(route('portal.appointments.cancel', $appointment->id), []);
        $failResponse->assertSessionHasErrors('cancellation_reason');

        // With reason: must succeed
        $successResponse = $this->actingAs($this->receptionistUser)
            ->post(route('portal.appointments.cancel', $appointment->id), [
                'cancellation_reason' => 'Patient called unable to travel from Birganj',
            ]);

        $successResponse->assertRedirect();
        $appointment->refresh();

        $this->assertEquals(Appointment::STATUS_CANCELLED, $appointment->status);
        $this->assertEquals('Patient called unable to travel from Birganj', $appointment->cancellation_reason);
        $this->assertEquals($this->receptionistUser->id, $appointment->cancelled_by);
        $this->assertNotNull($appointment->cancelled_at);

        $this->assertDatabaseHas('appointment_histories', [
            'appointment_id' => $appointment->id,
            'action' => 'cancelled',
            'new_status' => Appointment::STATUS_CANCELLED,
        ]);
    }

    /**
     * Test 9: Staff can mark appointment status to completed or no_show.
     */
    public function test_staff_can_mark_status_completed_or_no_show(): void
    {
        $testDate = $this->getNextScheduledDate();
        $testTime = '11:40:00';

        Appointment::where('doctor_id', $this->doctorUser->id)
            ->whereDate('appointment_date', $testDate)
            ->where('appointment_time', $testTime)
            ->delete();

        $appointment = Appointment::create([
            'patient_id' => $this->patientUser->patient->id,
            'doctor_id' => $this->doctorUser->id,
            'department_id' => $this->gynDept->id,
            'appointment_date' => $testDate . ' ' . $testTime,
            'appointment_time' => $testTime,
            'status' => Appointment::STATUS_CONFIRMED,
            'type' => 'walk_in',
        ]);

        // Mark completed
        $compResponse = $this->actingAs($this->receptionistUser)
            ->post(route('portal.appointments.status', $appointment->id), [
                'status' => Appointment::STATUS_COMPLETED,
                'notes' => 'Consultation completed by Dr. Nasreen',
            ]);

        $compResponse->assertRedirect();
        $appointment->refresh();
        $this->assertEquals(Appointment::STATUS_COMPLETED, $appointment->status);

        // Create second confirmed appointment to test no_show
        $testTime2 = '12:40:00';
        Appointment::where('doctor_id', $this->doctorUser->id)
            ->whereDate('appointment_date', $testDate)
            ->where('appointment_time', $testTime2)
            ->delete();

        $appointment2 = Appointment::create([
            'patient_id' => $this->patientUser->patient->id,
            'doctor_id' => $this->doctorUser->id,
            'department_id' => $this->gynDept->id,
            'appointment_date' => $testDate . ' ' . $testTime2,
            'appointment_time' => $testTime2,
            'status' => Appointment::STATUS_CONFIRMED,
            'type' => 'walk_in',
        ]);

        // Mark no show
        $noShowResponse = $this->actingAs($this->receptionistUser)
            ->post(route('portal.appointments.status', $appointment2->id), [
                'status' => Appointment::STATUS_NO_SHOW,
                'notes' => 'Patient did not attend chamber',
            ]);

        $noShowResponse->assertRedirect();
        $appointment2->refresh();
        $this->assertEquals(Appointment::STATUS_NO_SHOW, $appointment2->status);
    }

    /**
     * Test 10: Authorized staff can configure doctor consultation schedules.
     */
    public function test_authorized_staff_can_configure_doctor_schedules(): void
    {
        // Delete schedule for friday if any
        DoctorSchedule::where('doctor_id', $this->doctorUser->id)
            ->where('day_of_week', 'friday')
            ->delete();

        $response = $this->actingAs($this->adminUser)
            ->post(route('portal.doctor-schedules.store'), [
                'doctor_id' => $this->doctorUser->id,
                'department_id' => $this->gynDept->id,
                'day_of_week' => 'friday',
                'start_time' => '17:00',
                'end_time' => '20:00',
                'slot_duration_minutes' => 15,
                'max_patients' => 12,
                'room_number' => 'Special Clinic Room B',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('portal.doctor-schedules.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('doctor_schedules', [
            'doctor_id' => $this->doctorUser->id,
            'day_of_week' => 'friday',
            'room_number' => 'Special Clinic Room B',
            'slot_duration_minutes' => 15,
        ]);
    }

    /**
     * Test 11: Security & Unauthorized operations are properly denied.
     */
    public function test_unauthorized_operations_are_blocked(): void
    {
        // 1. Unauthenticated guest cannot view staff appointments
        $guestResponse = $this->get(route('portal.appointments.index'));
        $guestResponse->assertRedirect(route('portal.login'));

        // 2. Regular patient cannot view staff portal appointments (redirected to patient dashboard)
        $patientPortalResponse = $this->actingAs($this->patientUser)
            ->get(route('portal.appointments.index'));
        $patientPortalResponse->assertRedirect(route('patient.dashboard'));

        // 3. Regular patient cannot access staff approval route (blocked by EnsureStaff middleware)
        $testDate = $this->getNextScheduledDate();
        $appointment = Appointment::create([
            'patient_id' => $this->patientUser->patient->id,
            'doctor_id' => $this->doctorUser->id,
            'department_id' => $this->gynDept->id,
            'appointment_date' => $testDate . ' 12:00:00',
            'appointment_time' => '12:00:00',
            'status' => Appointment::STATUS_PENDING,
            'type' => 'walk_in',
        ]);

        $patientApproveResponse = $this->actingAs($this->patientUser)
            ->post(route('portal.appointments.confirm', $appointment->id));
        $patientApproveResponse->assertRedirect(route('patient.dashboard'));

        // Non-receptionist staff (nurse) without appointments.approve permission gets 403 Forbidden
        $nurseRole = Role::where('name', 'nurse')->firstOrFail();
        $nurseUser = User::updateOrCreate(
            ['email' => 'nurse.apt.test@meghnaclinic.com'],
            [
                'name' => 'Sister Shima Test',
                'phone' => '01718880009',
                'password' => bcrypt('Password123!'),
                'user_type' => 'staff',
                'is_active' => true,
            ]
        );
        $nurseUser->roles()->syncWithoutDetaching([$nurseRole->id]);

        $nurseApproveResponse = $this->actingAs($nurseUser)
            ->post(route('portal.appointments.confirm', $appointment->id));
        $nurseApproveResponse->assertStatus(403);

        // 4. Patient A cannot cancel or reschedule Patient B's appointment in Patient Portal
        $otherPatientAppt = Appointment::create([
            'patient_id' => $this->patientUser->patient->id,
            'doctor_id' => $this->doctorUser->id,
            'department_id' => $this->gynDept->id,
            'appointment_date' => $testDate . ' 12:20:00',
            'appointment_time' => '12:20:00',
            'status' => Appointment::STATUS_PENDING,
            'type' => 'walk_in',
        ]);

        $hackCancelResponse = $this->actingAs($this->otherPatientUser)
            ->post(route('patient.appointments.cancel', $otherPatientAppt->id), [
                'cancellation_reason' => 'Unauthorized attempt by another patient',
            ]);
        $hackCancelResponse->assertStatus(403);

        $hackRescheduleResponse = $this->actingAs($this->otherPatientUser)
            ->post(route('patient.appointments.reschedule', $otherPatientAppt->id), [
                'new_date' => $testDate,
                'new_time' => '12:40:00',
                'reschedule_reason' => 'Unauthorized reschedule attempt',
            ]);
        $hackRescheduleResponse->assertStatus(403);
    }
}
