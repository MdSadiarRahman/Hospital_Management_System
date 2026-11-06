<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Consultation',
                'code' => 'CONSULTATION',
                'description' => 'Outpatient and specialist medical consultations',
                'services' => [
                    ['name' => 'General OPD Consultation', 'code' => 'SRV-CONS-001', 'price' => 300.00, 'description' => 'General medical evaluation by duty physician'],
                    ['name' => 'Specialist Consultation (Gynecology / Orthopedic / Surgery)', 'code' => 'SRV-CONS-002', 'price' => 600.00, 'description' => 'Clinical evaluation by senior specialist consultant'],
                    ['name' => 'Emergency Medical Assessment', 'code' => 'SRV-CONS-003', 'price' => 500.00, 'description' => 'Immediate 24/7 triage and emergency doctor assessment'],
                    ['name' => 'Follow-up Consultation', 'code' => 'SRV-CONS-004', 'price' => 200.00, 'description' => 'Follow-up consultation within 7 days of primary visit'],
                ],
            ],
            [
                'name' => 'Bed Charges',
                'code' => 'BED_CHARGES',
                'description' => 'Inpatient ward, cabin, and recovery room tariffs',
                'services' => [
                    ['name' => 'General Ward Bed (Daily Tariff)', 'code' => 'SRV-BED-001', 'price' => 500.00, 'description' => 'Standard shared ward bed including nursing care'],
                    ['name' => 'Semi-Cabin Bed (Daily Tariff)', 'code' => 'SRV-BED-002', 'price' => 1000.00, 'description' => 'Two-patient semi-private cabin with dedicated fan and amenities'],
                    ['name' => 'AC Deluxe Cabin Bed (Daily Tariff)', 'code' => 'SRV-BED-003', 'price' => 1800.00, 'description' => 'Private air-conditioned cabin with attendant couch and private washroom'],
                    ['name' => 'Post-Op Recovery Bed (Hourly Tariff)', 'code' => 'SRV-BED-004', 'price' => 150.00, 'description' => 'OT recovery room bed under continuous monitoring'],
                ],
            ],
            [
                'name' => 'Laboratory Tests',
                'code' => 'LAB_TESTS',
                'description' => 'Clinical pathology, biochemistry, and diagnostic laboratory tests',
                'services' => [
                    ['name' => 'Complete Blood Count (CBC) with ESR', 'code' => 'SRV-LAB-001', 'price' => 400.00, 'description' => 'Hematology automated complete blood count'],
                    ['name' => 'Blood Glucose (Fasting / 2HABF / Random)', 'code' => 'SRV-LAB-002', 'price' => 150.00, 'description' => 'Blood sugar enzymatic assay'],
                    ['name' => 'Serum Creatinine & Urea', 'code' => 'SRV-LAB-003', 'price' => 350.00, 'description' => 'Renal function screening'],
                    ['name' => 'Lipid Profile', 'code' => 'SRV-LAB-004', 'price' => 800.00, 'description' => 'Total cholesterol, HDL, LDL, VLDL, and Triglycerides'],
                    ['name' => 'Urine Routine & Microscopic Examination (R/M/E)', 'code' => 'SRV-LAB-005', 'price' => 250.00, 'description' => 'Chemical and microscopic urinalysis'],
                    ['name' => 'Serum Electrolytes (Na+, K+, Cl-)', 'code' => 'SRV-LAB-006', 'price' => 750.00, 'description' => 'Electrolyte analysis via ISE'],
                    ['name' => 'Liver Function Test (LFT)', 'code' => 'SRV-LAB-007', 'price' => 900.00, 'description' => 'Bilirubin, SGPT, SGOT, and Alkaline Phosphatase'],
                ],
            ],
            [
                'name' => 'Operation Charges',
                'code' => 'OPERATION_CHARGES',
                'description' => 'Surgical theatre fees, procedural packages, and surgeon fees',
                'services' => [
                    ['name' => 'Normal Vaginal Delivery (NVD) Package', 'code' => 'SRV-OT-001', 'price' => 8000.00, 'description' => 'Labor room, normal delivery procedure, and newborn care'],
                    ['name' => 'Caesarean Section (C-Section) OT Charge', 'code' => 'SRV-OT-002', 'price' => 18000.00, 'description' => 'Major OT setup, surgeon, and anesthesia charges for LSCS'],
                    ['name' => 'Emergency Appendectomy OT Charge', 'code' => 'SRV-OT-003', 'price' => 16000.00, 'description' => 'Acute appendectomy emergency surgical intervention'],
                    ['name' => 'Laparoscopic Cholecystectomy', 'code' => 'SRV-OT-004', 'price' => 25000.00, 'description' => 'Minimally invasive gallbladder removal'],
                    ['name' => 'Minor Surgical Debridement / Suturing', 'code' => 'SRV-OT-005', 'price' => 2000.00, 'description' => 'Laceration repair, sterile dressing, and local anesthesia'],
                    ['name' => 'Closed Fracture Reduction & Plaster (POP)', 'code' => 'SRV-OT-006', 'price' => 3500.00, 'description' => 'Orthopedic reduction and plaster casting'],
                ],
            ],
            [
                'name' => 'Other Approved Services',
                'code' => 'OTHER_APPROVED',
                'description' => 'Nursing procedures, oxygen therapy, ECG, and clinical support services',
                'services' => [
                    ['name' => 'Nebulization Session', 'code' => 'SRV-OTH-001', 'price' => 150.00, 'description' => 'Single bronchodilator/salbutamol nebulization therapy'],
                    ['name' => 'Oxygen Therapy (Per Hour)', 'code' => 'SRV-OTH-002', 'price' => 100.00, 'description' => 'Medical grade oxygen with flowmeter and nasal cannula/mask'],
                    ['name' => 'ECG (12-Lead Electrocardiogram)', 'code' => 'SRV-OTH-003', 'price' => 350.00, 'description' => 'Resting 12-lead ECG with physician rhythm interpretation'],
                    ['name' => 'Dressing & Wound Care (Major)', 'code' => 'SRV-OTH-004', 'price' => 400.00, 'description' => 'Sterile surgical dressing for large wounds or burns'],
                    ['name' => 'Dressing & Wound Care (Minor)', 'code' => 'SRV-OTH-005', 'price' => 200.00, 'description' => 'Minor wound cleaning and antiseptic dressing'],
                    ['name' => 'Catheterization / Ryles Tube Insertion', 'code' => 'SRV-OTH-006', 'price' => 300.00, 'description' => 'Foley catheterization or nasogastric tube insertion'],
                    ['name' => 'IV Cannula Insertion & Infusion Setup', 'code' => 'SRV-OTH-007', 'price' => 150.00, 'description' => 'Intravenous cannula placement and fluid set initiation'],
                    ['name' => 'Ambulance Transfer / Dispatch Service', 'code' => 'SRV-OTH-008', 'price' => 1200.00, 'description' => 'Dinajpur town transit ambulance support'],
                ],
            ],
        ];

        foreach ($categories as $catData) {
            $services = $catData['services'];
            unset($catData['services']);

            $category = ServiceCategory::firstOrCreate(
                ['code' => $catData['code']],
                $catData
            );

            foreach ($services as $srvData) {
                Service::firstOrCreate(
                    ['code' => $srvData['code']],
                    array_merge($srvData, ['category_id' => $category->id])
                );
            }
        }
    }
}

