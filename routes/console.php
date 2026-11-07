<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('export:gh-pages', function () {
    $this->info('Starting static export for GitHub Pages...');

    // Share empty ViewErrorBag for views that check for validation errors
    view()->share('errors', new \Illuminate\Support\ViewErrorBag());

    $distPath = base_path('dist');
    if (!is_dir($distPath)) {
        mkdir($distPath, 0755, true);
    }

    // 1. Copy public build assets to dist/build
    $sourceBuild = public_path('build');
    $targetBuild = $distPath . DIRECTORY_SEPARATOR . 'build';
    if (is_dir($sourceBuild)) {
        if (!is_dir($targetBuild)) {
            mkdir($targetBuild, 0755, true);
        }
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceBuild, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            $dest = $targetBuild . DIRECTORY_SEPARATOR . $iterator->getSubPathname();
            if ($item->isDir()) {
                if (!is_dir($dest)) {
                    mkdir($dest, 0755, true);
                }
            } else {
                copy($item->getPathname(), $dest);
            }
        }
        $this->info('Copied build assets to dist/build.');
    }

    // 2. Helper to clean asset paths for GitHub Pages relative URL support
    $fixAssetPaths = function (string $html) {
        // Replace /build/ with ./build/
        $html = str_replace(['href="/build/', 'src="/build/'], ['href="./build/', 'src="./build/'], $html);
        $html = str_replace(['href="http://localhost:5173/', 'src="http://localhost:5173/'], ['href="./build/', 'src="./build/'], $html);
        return $html;
    };

    // 3. Render previews.index as index.html
    $indexHtml = view('previews.index')->render();
    $indexHtml = $fixAssetPaths($indexHtml);
    // Replace preview URLs with static html links
    $indexHtml = str_replace(
        ['/previews/public', '/previews/admin', '/previews/doctor', '/previews/nurse', '/previews/reception', '/previews/billing', '/previews/lab', '/previews/patient'],
        ['./public-preview.html', './admin.html', './doctor.html', './nurse.html', './reception.html', './billing.html', './lab.html', './patient.html'],
        $indexHtml
    );
    file_put_contents($distPath . DIRECTORY_SEPARATOR . 'index.html', $indexHtml);
    $this->info('Rendered index.html');

    // 4. Render other preview views
    $views = [
        'admin' => 'previews.admin',
        'doctor' => 'previews.doctor',
        'nurse' => 'previews.nurse',
        'reception' => 'previews.reception',
        'billing' => 'previews.billing',
        'lab' => 'previews.lab',
        'patient' => 'previews.patient',
    ];

    foreach ($views as $fileName => $viewName) {
        $html = view($viewName)->render();
        $html = $fixAssetPaths($html);
        // Replace links to back to index
        $html = str_replace(['href="/previews"', 'href="{{ route(\'previews.index\') }}"'], 'href="./index.html"', $html);
        $html = str_replace(
            ['/previews/admin', '/previews/doctor', '/previews/nurse', '/previews/reception', '/previews/billing', '/previews/lab', '/previews/patient', '/previews/public'],
            ['./admin.html', './doctor.html', './nurse.html', './reception.html', './billing.html', './lab.html', './patient.html', './public-preview.html'],
            $html
        );
        file_put_contents($distPath . DIRECTORY_SEPARATOR . "{$fileName}.html", $html);
        $this->info("Rendered {$fileName}.html");
    }

    // 5. Render public landing page preview
    $clinicSettings = [
        'clinicName' => 'Meghna Clinic and Nursing Home',
        'clinicAddress' => 'Shohid Minar Mor, Dinajpur, Bangladesh',
        'clinicHotline' => '+8801700000000',
        'totalBedCapacity' => 10,
        'availableBedsCount' => 6,
        'departments' => collect([
            (object)['id' => 1, 'name' => 'Gynecology & Obstetrics', 'code' => 'GYN', 'beds_count' => 2, 'description' => 'Maternal care, antenatal care, and normal/C-section delivery.'],
            (object)['id' => 2, 'name' => 'Orthopedic & Trauma Surgery', 'code' => 'ORTHO', 'beds_count' => 2, 'description' => 'Bone fracture management, joint care, and trauma surgery.'],
            (object)['id' => 3, 'name' => 'General & Laparoscopic Surgery', 'code' => 'SURG', 'beds_count' => 2, 'description' => 'Abdominal procedures, appendectomy, and hernia operations.'],
            (object)['id' => 4, 'name' => '24/7 Emergency Medicine', 'code' => 'EMERG', 'beds_count' => 2, 'description' => 'Emergency resuscitation, acute triage, and overnight observation.'],
        ]),
        'doctors' => collect([
            (object)[
                'id' => 1,
                'name' => 'Dr. Nasreen Sultana',
                'staffProfile' => (object)[
                    'designation' => 'Senior Consultant',
                    'specialization' => 'Gynecology, Obstetrics & Maternal Care',
                    'department' => (object)['name' => 'Gynecology & Obstetrics'],
                    'qualification' => 'MBBS (DMC), FCPS (Gynae & Obs)',
                ],
            ],
            (object)[
                'id' => 2,
                'name' => 'Dr. M. A. Samad',
                'staffProfile' => (object)[
                    'designation' => 'Consultant Orthopedic Surgeon',
                    'specialization' => 'Trauma, Joint Replacement & Bone Surgery',
                    'department' => (object)['name' => 'Orthopedics'],
                    'qualification' => 'MBBS, MS (Orthopedics), FICS',
                ],
            ],
            (object)[
                'id' => 3,
                'name' => 'Dr. Farhana Yasmin',
                'staffProfile' => (object)[
                    'designation' => 'Resident Medical Officer',
                    'specialization' => 'Emergency Triage & Surgical Resuscitation',
                    'department' => (object)['name' => 'Emergency'],
                    'qualification' => 'MBBS (RU), CCD (BIRDEM)',
                ],
            ],
        ]),
        'beds' => collect(),
        'bedCategories' => [
            [
                'category' => 'General Inpatient Ward',
                'code' => 'GEN',
                'beds' => '4 Beds (GEN-01 to GEN-04)',
                'rate' => 800.00,
                'amenities' => 'Oxygen line, 24/7 nursing duty, attendant seating, ceiling fan',
                'care_level' => 'Standard Inpatient Observation',
            ],
            [
                'category' => 'Gynecology & Maternity Ward',
                'code' => 'GYN',
                'beds' => '2 Beds (GYN-01, GYN-02)',
                'rate' => 1200.00,
                'amenities' => 'Post-op observation, female nursing staff, privacy curtains, baby bassinet access',
                'care_level' => 'Maternal & Neonatal Postpartum',
            ],
            [
                'category' => 'Surgical Inpatient Ward',
                'code' => 'SUR',
                'beds' => '2 Beds (SUR-01, SUR-02)',
                'rate' => 1500.00,
                'amenities' => 'Post-operative monitoring, dressing care, surgeon rounds, adjustable backrest',
                'care_level' => 'Post-Surgical Intermediate Care',
            ],
            [
                'category' => 'Emergency Acute Observation',
                'code' => 'EM',
                'beds' => '2 Beds (EM-01, EM-02)',
                'rate' => 1500.00,
                'amenities' => 'Emergency monitor, immediate triage, central oxygen support, crash cart access',
                'care_level' => 'High-Dependency Acute Stabilization',
            ],
        ],
    ];

    try {
        $publicHtml = view('public.index', $clinicSettings)->render();
        $publicHtml = $fixAssetPaths($publicHtml);
        // Add navigation links back to previews
        $banner = '<div style="background:#087F8C;color:#fff;text-align:center;padding:8px 12px;font-size:14px;position:sticky;top:0;z-index:9999;">'
            . '<span>🏥 Meghna Clinic Live Preview &bull; </span>'
            . '<a href="./index.html" style="color:#fff;font-weight:bold;text-decoration:underline;margin-left:8px;">Switch to Portals Showcase (Admin / Doctor / Nurse / Patient)</a>'
            . '</div>';
        $publicHtml = preg_replace('/<body([^>]*)>/i', '<body$1>' . $banner, $publicHtml, 1);
        file_put_contents($distPath . DIRECTORY_SEPARATOR . 'public-preview.html', $publicHtml);
        $this->info('Rendered public-preview.html');
    } catch (\Throwable $e) {
        $this->warn('Could not render public.index: ' . $e->getMessage());
    }

    // 6. Create .nojekyll for GitHub Pages (ensures directories like _next, _assets or standard dotfiles are not ignored by Jekyll)
    file_put_contents($distPath . DIRECTORY_SEPARATOR . '.nojekyll', '');
    $this->info('Created .nojekyll');

    $this->info('GitHub Pages static export completed in ./dist!');
})->purpose('Export static HTML pages for GitHub Pages deployment');

// Production maintenance routines
Schedule::command('queue:prune-failed --hours=168')->daily();
Schedule::command('queue:prune-batches --hours=48')->daily();
Schedule::command('model:prune')->daily();

