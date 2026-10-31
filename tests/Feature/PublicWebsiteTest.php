<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Role;
use App\Models\SystemSetting;
use Database\Seeders\DepartmentAndBedSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Database\Seeders\UserSeeder;
use Tests\TestCase;

class PublicWebsiteTest extends TestCase
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
     * Test public homepage returns a successful response and renders verified branding.
     */
    public function test_public_homepage_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Meghna Clinic');
        $response->assertSee('Shohid Minar Mor, Dinajpur');
        $response->assertSee('+8801700000000');
    }

    /**
     * Test all 12 approved sections exist on the public homepage.
     */
    public function test_all_twelve_sections_are_present(): void
    {
        $response = $this->get('/');

        // 1. Header and Navigation
        $response->assertSee('public-navbar');
        $response->assertSee('Book Appointment');
        $response->assertSee('Patient Portal');
        $response->assertSee('Staff Login');

        // 2. Hero Section
        $response->assertSee('Specialized Clinical Care');
        $response->assertSee('Live Facility Status');
        $response->assertSee('10 Clinical Beds');

        // 3. Clinic Introduction
        $response->assertSee('About Our Institution');
        $response->assertSee('A Trusted Healthcare Landmark in Dinajpur');
        $response->assertSee('14'); // Staff members
        $response->assertSee('Shift Nurses');

        // 4. Clinical Departments
        $response->assertSee('Specialized Clinical Departments');
        $response->assertSee('Gynecology & Obstetrics', false);
        $response->assertSee('Orthopedic Trauma');
        $response->assertSee('General Surgery');
        $response->assertSee('24/7 Emergency Unit');

        // 5. Doctor & Medical Team Information
        $response->assertSee('Our Medical Officers & Nursing Team', false);
        $response->assertSee('Dr. Nasreen Sultana');
        $response->assertSee('Emergency Medical Officer');
        $response->assertSee('Sister Shima Begum');

        // 6. Healthcare Services
        $response->assertSee('Comprehensive Healthcare Services');
        $response->assertSee('10-Bed Inpatient Ward');
        $response->assertSee('Operation Theatre (OT)');
        $response->assertSee('Diagnostic Pathology & Partners', false);

        // 7. Why Choose Us
        $response->assertSee('Why Choose Meghna Clinic & Nursing Home?', false);
        $response->assertSee('24/7 Doctor On Site');
        $response->assertSee('Dedicated Nurse Ratio');
        $response->assertSee('Transparent Tariffs');

        // 8. Emergency Contact
        $response->assertSee('Immediate Medical Response 24/7');
        $response->assertSee('Need Urgent Medical Attention in Dinajpur?');

        // 9. Appointment Call-to-Action
        $response->assertSee('Book a Doctor Appointment');
        $response->assertSee('apt_patient_name');
        $response->assertSee('apt_phone');
        $response->assertSee('apt_department');

        // 10. Contact Information
        $response->assertSee('24/7 Emergency Line');
        $response->assertSee('Reception Desk & Appointments', false);
        $response->assertSee('info@meghnaclinic.com');

        // 11. Location & Accessibility
        $response->assertSee('Physical Clinic Location');
        $response->assertSee('From Railway Station');
        $response->assertSee('From Central Bus Terminal');

        // 12. Footer
        $response->assertSee('public-footer');
        $response->assertSee('Clinical Specialties');
        $response->assertSee('Patient Resources');
    }

    /**
     * Test content is dynamically manageable through SystemSetting model without hardcoding.
     */
    public function test_content_is_dynamically_driven_by_system_settings(): void
    {
        $originalName = SystemSetting::get('clinic_name');

        try {
            SystemSetting::set('clinic_name', 'Meghna Modern Hospital Dinajpur');

            $response = $this->get('/');
            $response->assertStatus(200);
            $response->assertSee('Meghna Modern Hospital Dinajpur');
        } finally {
            // Restore original setting
            SystemSetting::set('clinic_name', $originalName);
        }
    }
}
