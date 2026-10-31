<?php

namespace Tests\Feature;

use Tests\TestCase;

class LayoutPreviewTest extends TestCase
{
    /**
     * Test previews index route.
     */
    public function test_previews_index_renders_successfully(): void
    {
        $response = $this->get('/previews');
        $response->assertStatus(200);
        $response->assertSee('Meghna Clinic & Nursing Home', false);
        $response->assertSee('1. Public Website Layout');
        $response->assertSee('2. Admin Dashboard Layout');
        $response->assertSee('8. Patient Portal Layout');
    }

    /**
     * Test public website layout preview.
     */
    public function test_public_website_layout_renders(): void
    {
        $response = $this->get('/previews/public');
        $response->assertStatus(200);
        $response->assertSee('Meghna Clinic');
        $response->assertSee('Shohid Minar Mor, Dinajpur');
        $response->assertSee('Emergency 24/7');
        $response->assertSee('10 Clinical Beds');
    }

    /**
     * Test admin dashboard layout preview.
     */
    public function test_admin_dashboard_layout_renders(): void
    {
        $response = $this->get('/previews/admin');
        $response->assertStatus(200);
        $response->assertSee('Administrator Control Center');
        $response->assertSee('14 Personnel');
        $response->assertSee('10-Bed Inpatient Live Map');
        $response->assertSee('Admin Authority');
    }

    /**
     * Test doctor dashboard layout preview.
     */
    public function test_doctor_dashboard_layout_renders(): void
    {
        $response = $this->get('/previews/doctor');
        $response->assertStatus(200);
        $response->assertSee('Doctor Clinical Workstation');
        $response->assertSee('Dr. Nasreen Sultana');
        $response->assertSee('OPD Outpatient Consultation Queue');
        $response->assertSee('Medical Officer');
    }

    /**
     * Test nurse dashboard layout preview.
     */
    public function test_nurse_dashboard_layout_renders(): void
    {
        $response = $this->get('/previews/nurse');
        $response->assertStatus(200);
        $response->assertSee('Nurse Ward Station');
        $response->assertSee('Sister Shima Begum');
        $response->assertSee('10-Bed Inpatient Ward');
        $response->assertSee('Inpatient Sister');
    }

    /**
     * Test reception dashboard layout preview.
     */
    public function test_reception_dashboard_layout_renders(): void
    {
        $response = $this->get('/previews/reception');
        $response->assertStatus(200);
        $response->assertSee('Reception &amp; Patient Intake Desk', false);
        $response->assertSee('Kowshik Roy');
        $response->assertSee('Intake Desk');
    }

    /**
     * Test billing dashboard layout preview.
     */
    public function test_billing_dashboard_layout_renders(): void
    {
        $response = $this->get('/previews/billing');
        $response->assertStatus(200);
        $response->assertSee('Billing &amp; Cashier Portal', false);
        $response->assertSee('Aftab Ahmed');
        $response->assertSee('Financial Desk');
    }

    /**
     * Test laboratory dashboard layout preview.
     */
    public function test_laboratory_dashboard_layout_renders(): void
    {
        $response = $this->get('/previews/lab');
        $response->assertStatus(200);
        $response->assertSee('Diagnostic Laboratory Workstation');
        $response->assertSee('Md. Faruk Hossain');
        $response->assertSee('Lab Diagnostics');
    }

    /**
     * Test patient portal layout preview.
     */
    public function test_patient_portal_layout_renders(): void
    {
        $response = $this->get('/previews/patient');
        $response->assertStatus(200);
        $response->assertSee('Patient Self-Care Portal');
        $response->assertSee('Most. Rokeya Akhter');
        $response->assertSee('Verified Patient');
    }
}
