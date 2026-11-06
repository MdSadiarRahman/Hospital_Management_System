<?php

namespace Database\Seeders;

use App\Models\LabPartner;
use App\Models\LabTestCatalog;
use Illuminate\Database\Seeder;

class LabTestCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure reference partner laboratory exists for outsourced tests
        $partner = LabPartner::firstOrCreate(
            ['code' => 'IBN-SINA-DNP'],
            [
                'name' => 'Ibn Sina Diagnostic & Reference Center, Dinajpur',
                'contact_person' => 'Dr. Rafiqul Islam (Lab Director)',
                'phone' => '01712345678',
                'address' => 'Hospital Road, Dinajpur, Bangladesh',
                'is_active' => true,
            ]
        );

        $partner2 = LabPartner::firstOrCreate(
            ['code' => 'POPULAR-DNP'],
            [
                'name' => 'Popular Diagnostic Center Reference Unit',
                'contact_person' => 'Md. Anwarul Karim',
                'phone' => '01812345679',
                'address' => 'Station Road, Dinajpur',
                'is_active' => true,
            ]
        );

        // 2. Realistic Diagnostic Tests for Meghna Clinic & Nursing Home
        $tests = [
            [
                'name' => 'Complete Blood Count (CBC) with ESR',
                'code' => 'CBC01',
                'category' => 'Hematology',
                'description' => 'Automated cell counter analysis measuring Hemoglobin, Hematocrit, Total RBC, Total WBC, Differential Count, Platelet Count, and Westergren ESR.',
                'standard_charge' => 450.00,
                'sample_type' => 'Whole Blood (EDTA)',
                'turnaround_time' => '2 hours',
                'instructions' => 'No special fasting required. 2-3ml venous blood in lavender-top EDTA vial.',
                'default_source' => 'internal',
                'is_active' => true,
            ],
            [
                'name' => 'Random Blood Sugar (RBS) with Urine Sugar',
                'code' => 'RBS01',
                'category' => 'Biochemistry',
                'description' => 'Rapid enzymatic hexokinase assay for blood glucose status.',
                'standard_charge' => 120.00,
                'sample_type' => 'Serum / Plasma (Fluoride)',
                'turnaround_time' => '1 hour',
                'instructions' => 'Note exact time of last meal/intake.',
                'default_source' => 'internal',
                'is_active' => true,
            ],
            [
                'name' => 'Fasting Blood Sugar (FBS)',
                'code' => 'FBS01',
                'category' => 'Biochemistry',
                'description' => 'Quantitative determination of blood glucose after an overnight fast.',
                'standard_charge' => 150.00,
                'sample_type' => 'Fluoride Plasma',
                'turnaround_time' => '2 hours',
                'instructions' => 'Strict 8-10 hours overnight fasting required. Water allowed.',
                'default_source' => 'internal',
                'is_active' => true,
            ],
            [
                'name' => 'Serum Creatinine with eGFR',
                'code' => 'CREAT01',
                'category' => 'Biochemistry',
                'description' => 'Modified Jaffe photometric kinetic method for renal function evaluation.',
                'standard_charge' => 300.00,
                'sample_type' => 'Serum (Plain Vial)',
                'turnaround_time' => '2 hours',
                'instructions' => 'Avoid heavy meat meal and intense exercise 24 hours prior.',
                'default_source' => 'internal',
                'is_active' => true,
            ],
            [
                'name' => 'Liver Function Test (LFT) Complete Profile',
                'code' => 'LFT01',
                'category' => 'Biochemistry',
                'description' => 'Comprehensive panel: Serum Bilirubin (Total/Direct), SGPT/ALT, SGOT/AST, Alkaline Phosphatase, Total Protein, Albumin, Globulin, and A/G ratio.',
                'standard_charge' => 950.00,
                'sample_type' => 'Serum (Yellow Top Gel)',
                'turnaround_time' => '4 hours',
                'instructions' => 'Overnight fasting preferred for optimal enzymatic baseline.',
                'default_source' => 'internal',
                'is_active' => true,
            ],
            [
                'name' => 'Lipid Profile (Full Fasting)',
                'code' => 'LIPID01',
                'category' => 'Biochemistry',
                'description' => 'Total Cholesterol, HDL-C, LDL-C, VLDL-C, and Triglycerides.',
                'standard_charge' => 800.00,
                'sample_type' => 'Serum',
                'turnaround_time' => '4 hours',
                'instructions' => '12-14 hours strict overnight fasting mandatory.',
                'default_source' => 'internal',
                'is_active' => true,
            ],
            [
                'name' => 'Serum Electrolytes (Na+, K+, Cl-)',
                'code' => 'ELECT01',
                'category' => 'Biochemistry',
                'description' => 'Ion selective electrode (ISE) assay for Sodium, Potassium, and Chloride.',
                'standard_charge' => 750.00,
                'sample_type' => 'Serum (Non-hemolyzed)',
                'turnaround_time' => '2 hours',
                'instructions' => 'Immediate separation to avoid in-vitro hemolysis.',
                'default_source' => 'internal',
                'is_active' => true,
            ],
            [
                'name' => 'Urine Routine & Microscopic Examination (R/M/E)',
                'code' => 'URINE01',
                'category' => 'Clinical Pathology',
                'description' => 'Physical, chemical (dipstick pH, albumin, sugar, pus, ket), and microscopic sediment examination (RBC, WBC, casts, crystals).',
                'standard_charge' => 200.00,
                'sample_type' => 'Random Urine',
                'turnaround_time' => '1 hour',
                'instructions' => 'Clean-catch midstream urine sample in sterile container.',
                'default_source' => 'internal',
                'is_active' => true,
            ],
            [
                'name' => 'Stool Routine & Occult Blood Test (OBT)',
                'code' => 'STOOL01',
                'category' => 'Clinical Pathology',
                'description' => 'Macroscopic and microscopic inspection with chemical occult blood test.',
                'standard_charge' => 250.00,
                'sample_type' => 'Fresh Stool',
                'turnaround_time' => '2 hours',
                'instructions' => 'Collect in clean dry container; avoid iron/red meat 48h prior for OBT.',
                'default_source' => 'internal',
                'is_active' => true,
            ],
            [
                'name' => 'Widal Slide Agglutination Test (Typhoid)',
                'code' => 'WIDAL01',
                'category' => 'Microbiology & Serology',
                'description' => 'Semi-quantitative detection of Salmonella Typhi and Paratyphi antibodies.',
                'standard_charge' => 350.00,
                'sample_type' => 'Serum',
                'turnaround_time' => '2 hours',
                'instructions' => 'Standard venous blood collection.',
                'default_source' => 'internal',
                'is_active' => true,
            ],
            [
                'name' => 'Dengue Duo Combo (NS1 Ag + IgG/IgM Ab)',
                'code' => 'DENGUE01',
                'category' => 'Microbiology & Serology',
                'description' => 'Rapid immunochromatographic rapid assay for early and convalescent dengue fever.',
                'standard_charge' => 600.00,
                'sample_type' => 'Whole Blood / Serum',
                'turnaround_time' => '30 mins',
                'instructions' => 'Emergency rapid testing available 24/7.',
                'default_source' => 'internal',
                'is_active' => true,
            ],
            [
                'name' => 'HbA1c (Glycated Hemoglobin)',
                'code' => 'HBA1C01',
                'category' => 'Biochemistry',
                'description' => 'HPLC / Nephelometric determination of 3-month average glycaemic control.',
                'standard_charge' => 900.00,
                'sample_type' => 'Whole Blood (EDTA)',
                'turnaround_time' => '4 hours',
                'instructions' => 'Fasting not required.',
                'default_source' => 'internal',
                'is_active' => true,
            ],
            [
                'name' => 'Thyroid Panel (TSH, FT4, FT3)',
                'code' => 'THYROID01',
                'category' => 'Immunology',
                'description' => 'Chemiluminescent microparticle immunoassay (CMIA) for thyroid disorders.',
                'standard_charge' => 1400.00,
                'sample_type' => 'Serum',
                'turnaround_time' => '24 hours',
                'instructions' => 'Morning sample preferred before taking thyroid medication.',
                'default_source' => 'both',
                'lab_partner_id' => $partner->id,
                'is_active' => true,
            ],
            [
                'name' => 'Digital Chest X-Ray P/A View',
                'code' => 'CXR01',
                'category' => 'Radiology & Imaging',
                'description' => 'High-frequency digital radiography with radiologist findings report.',
                'standard_charge' => 600.00,
                'sample_type' => 'None (Radiology / Imaging)',
                'turnaround_time' => '1 hour',
                'instructions' => 'Remove metallic jewelry and accessories around chest.',
                'default_source' => 'internal',
                'is_active' => true,
            ],
            [
                'name' => 'USG of Whole Abdomen & Pelvis with PVR',
                'code' => 'USG01',
                'category' => 'Ultrasonography',
                'description' => 'Real-time multi-frequency ultrasound of liver, gallbladder, pancreas, spleen, kidneys, urinary bladder, and prostate/pelvic organs.',
                'standard_charge' => 1200.00,
                'sample_type' => 'None (Radiology / Imaging)',
                'turnaround_time' => 'Same Day',
                'instructions' => '6 hours fasting + full urinary bladder (drink 1L water 1 hour prior).',
                'default_source' => 'internal',
                'is_active' => true,
            ],
            [
                'name' => 'Histopathology Biopsy (Small Specimen)',
                'code' => 'HISTO01',
                'category' => 'Histopathology & Cytology',
                'description' => 'Tissue fixation, microtomy, H&E staining, and histopathologist microscopic diagnostic evaluation.',
                'standard_charge' => 2200.00,
                'sample_type' => 'Tissue Biopsy (10% Formalin)',
                'turnaround_time' => '4 days',
                'instructions' => 'Specimen immediately fixed in 10% buffered neutral formalin.',
                'default_source' => 'external',
                'lab_partner_id' => $partner2->id,
                'is_active' => true,
            ],
        ];

        foreach ($tests as $testData) {
            LabTestCatalog::updateOrCreate(
                ['code' => $testData['code']],
                $testData
            );
        }
    }
}

