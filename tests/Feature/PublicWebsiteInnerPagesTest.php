<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Role;
use App\Models\SystemSetting;
use App\Models\User;
use Database\Seeders\DepartmentAndBedSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Database\Seeders\UserSeeder;
use Tests\TestCase;

class PublicWebsiteInnerPagesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (Role::count() === 0) {
            $this->seed(RolePermissionSeeder::class);
            $this->seed(DepartmentAndBedSeeder::class);
            $this->seed(SystemSettingsSeeder::class);
            $this->seed(UserSeeder::class);
        }
    }

    /**
     * Test About Us page renders with verified clinic metrics.
     */
    public function test_about_us_page_renders(): void
    {
        $response = $this->get('/about');

        $response->assertStatus(200);
        $response->assertSee('About Meghna Clinic');
        $response->assertSee('Shohid Minar Mor, Dinajpur');
        $response->assertSee('10'); // Inpatient beds
        $response->assertSee('14'); // Staff members
        $response->assertSee('2 Doctors Present');
        $response->assertSee('6'); // Shift nurses
        $response->assertSee('Admin Managed Content');
    }

    /**
     * Test Departments listing page renders active clinical units.
     */
    public function test_departments_page_renders_with_database_records(): void
    {
        $response = $this->get('/departments');

        $response->assertStatus(200);
        $response->assertSee('Specialized Clinical Departments');
        $response->assertSee('Emergency');
        $response->assertSee('Gynecology');
        $response->assertSee('Orthopedic');
        $response->assertSee('Surgery');
        $response->assertSee('10-Bed Inpatient Allocation Across Departments');
    }

    /**
     * Test Department Detail page renders for specific department ID and code.
     */
    public function test_department_detail_page_renders(): void
    {
        $dept = Department::where('code', 'GYN')->firstOrFail();

        $response = $this->get('/departments/' . $dept->id);

        $response->assertStatus(200);
        $response->assertSee($dept->name);
        $response->assertSee('GYN');
        $response->assertSee('Inpatient Beds in this Unit');
        $response->assertSee('Consulting Medical Officers');
        $response->assertSee('Inpatient Visiting & Attendant Policy', false);

        // Also test slug/code access
        $responseCode = $this->get('/departments/GYN');
        $responseCode->assertStatus(200);
    }

    /**
     * Test Doctors directory page renders verified doctor and nursing leadership.
     */
    public function test_doctors_page_renders_verified_medical_team(): void
    {
        $response = $this->get('/doctors');

        $response->assertStatus(200);
        $response->assertSee('Our Medical Officers & Clinical Team', false);
        $response->assertSee('Dr. Nasreen Sultana');
        $response->assertSee('BMDC Verified');
        $response->assertSee('On-Duty Emergency Medical Officer');
        $response->assertSee('Sister Shima Begum');
        $response->assertSee('3 Continuous 8-Hour Nurse Shifts');
    }

    /**
     * Test individual Doctor Profile page renders qualifications and schedule.
     */
    public function test_doctor_profile_page_renders(): void
    {
        $doctor = User::where('user_type', 'staff')
            ->whereHas('roles', fn ($q) => $q->where('name', 'doctor'))
            ->firstOrFail();

        $response = $this->get('/doctors/' . $doctor->id);

        $response->assertStatus(200);
        $response->assertSee($doctor->name);
        $response->assertSee('Weekly Consultation Hours');
        $response->assertSee('9:00 AM – 1:00 PM', false);
        $response->assertSee('5:00 PM – 9:00 PM', false);
        $response->assertSee('Request Consultation Token');
    }

    /**
     * Test Healthcare Services page renders 6 core clinical services and tariffs.
     */
    public function test_services_page_renders(): void
    {
        $response = $this->get('/services');

        $response->assertStatus(200);
        $response->assertSee('Comprehensive Healthcare Services');
        $response->assertSee('10-Bed Inpatient Ward');
        $response->assertSee('24/7 Emergency Triage');
        $response->assertSee('Sterile Operation Theatre');
        $response->assertSee('Maternity & Safe Delivery', false);
        $response->assertSee('Diagnostic Pathology');
        $response->assertSee('Standard Inpatient Bed Tariffs');
        $response->assertSee('800.00');
    }

    /**
     * Test Contact & Location page renders hotlines, transit guide, and inquiry form.
     */
    public function test_contact_page_renders(): void
    {
        $response = $this->get('/contact');

        $response->assertStatus(200);
        $response->assertSee('Contact & Location Guide', false);
        $response->assertSee('+8801700000000');
        $response->assertSee('+880 1711-000004');
        $response->assertSee('info@meghnaclinic.com');
        $response->assertSee('From Railway Station');
        $response->assertSee('From Bus Terminal');
        $response->assertSee('Send an Inquiry to Reception');
    }

    /**
     * Test Privacy Policy page renders BMDC compliance and unlinked registration rule.
     */
    public function test_privacy_policy_page_renders(): void
    {
        $response = $this->get('/privacy-policy');

        $response->assertStatus(200);
        $response->assertSee('Healthcare Privacy & Security Policy', false);
        $response->assertSee('Medical Confidentiality & Commitment', false);
        $response->assertSee('Critical Patient Privacy Rule');
        $response->assertSee('Patient Self-Care Portal');
        $response->assertSee('in-person physical verification');
        $response->assertSee('secure_medical_reports');
    }
}
