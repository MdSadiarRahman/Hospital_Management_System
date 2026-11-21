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

    // 2. Comprehensive HTML Cleaner for GitHub Pages (Relative Assets & CDN Fallback)
    $cleanHtml = function (string $html) {
        // Strip any absolute host before /build/ and make it ./build/
        $html = preg_replace('/https?:\/\/[^\/"\']+\/build\//', './build/', $html);
        $html = str_replace(['href="/build/', 'src="/build/'], ['href="./build/', 'src="./build/'], $html);
        $html = str_replace(['href="http://localhost:5173/', 'src="http://localhost:5173/'], ['href="./build/', 'src="./build/'], $html);

        // Inject Bootstrap 5 & Bootstrap Icons CDN so styles and icons 100% load
        $cdnStyles = '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">' . "\n"
                   . '<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">' . "\n"
                   . '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n"
                   . '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n"
                   . '<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">' . "\n";
        $html = str_replace('</head>', $cdnStyles . '</head>', $html);

        // Inject modern responsive style overrides for Bento cards and brand colors
        $customStyles = '<style>' . "\n"
                      . ':root { --hms-navy: #123B5D; --hms-teal: #087F8C; --hms-bg: #F4F7FA; }' . "\n"
                      . 'body { font-family: "Inter", "Hind Siliguri", sans-serif; background-color: var(--hms-bg); }' . "\n"
                      . '.text-navy { color: var(--hms-navy) !important; }' . "\n"
                      . '.text-teal { color: var(--hms-teal) !important; }' . "\n"
                      . '.bg-teal { background-color: var(--hms-teal) !important; }' . "\n"
                      . '.btn-hms-primary { background-color: var(--hms-navy); color: #fff; border: none; }' . "\n"
                      . '.btn-hms-primary:hover { background-color: #0e2d47; color: #fff; }' . "\n"
                      . '.btn-hms-teal { background-color: var(--hms-teal); color: #fff; border: none; }' . "\n"
                      . '.btn-hms-teal:hover { background-color: #066974; color: #fff; }' . "\n"
                      . '.hms-card { border-radius: 12px; transition: transform 0.2s ease, box-shadow 0.2s ease; }' . "\n"
                      . '.hover-lift:hover { transform: translateY(-4px); box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important; }' . "\n"
                      . '</style>' . "\n";
        $html = str_replace('</head>', $customStyles . '</head>', $html);

        // Replace route URLs
        $routeMap = [
            'previews/public' => './public-preview.html',
            'previews/admin' => './admin.html',
            'previews/doctor' => './doctor.html',
            'previews/nurse' => './nurse.html',
            'previews/reception' => './reception.html',
            'previews/billing' => './billing.html',
            'previews/lab' => './lab.html',
            'previews/patient' => './patient.html',
            'previews' => './index.html',
        ];

        foreach ($routeMap as $path => $dest) {
            $html = preg_replace('/https?:\/\/[^\/"\']+\/' . preg_quote($path, '/') . '(?=[\/"\'\s#?]|$)/', $dest, $html);
            $html = preg_replace('/([\'"])\/' . preg_quote($path, '/') . '(?=[\/"\'\s#?]|$)/', '$1' . $dest, $html);
        }

        // Clean any leftover localhost references
        $html = str_replace('http://127.0.0.1:8000./', './', $html);
        $html = str_replace('http://localhost:8000./', './', $html);
        $html = str_replace('http://127.0.0.1:8000', '.', $html);
        $html = str_replace('http://localhost', '.', $html);

        return $html;
    };

    // 3. Render previews.index as index.html
    $indexHtml = view('previews.index')->render();
    $indexHtml = $cleanHtml($indexHtml);
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
        try {
            $html = view($viewName)->render();
            $html = $cleanHtml($html);
            file_put_contents($distPath . DIRECTORY_SEPARATOR . "{$fileName}.html", $html);
            $this->info("Rendered {$fileName}.html");
        } catch (\Throwable $e) {
            $this->warn("Could not render {$viewName}: " . $e->getMessage());
        }
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
        $publicHtml = $cleanHtml($publicHtml);
        // Map public navigation buttons
        $publicHtml = str_replace(
            ['href="/patient/login"', 'href="/portal/login"', 'href="/login"'],
            ['href="./patient.html"', 'href="./admin.html"', 'href="./admin.html"'],
            $publicHtml
        );
        $publicHtml = preg_replace('/https?:\/\/[^\/"\']+\/patient\/login/', './patient.html', $publicHtml);
        $publicHtml = preg_replace('/https?:\/\/[^\/"\']+\/portal\/login/', './admin.html', $publicHtml);
        $publicHtml = preg_replace('/https?:\/\/[^\/"\']+\/login/', './admin.html', $publicHtml);
        $publicHtml = preg_replace('/https?:\/\/[^\/"\']+\/(#\w+)/', '$1', $publicHtml);

        // Add navigation links back to previews
        $banner = '<div style="background:#087F8C;color:#fff;text-align:center;padding:10px 14px;font-size:14px;position:sticky;top:0;z-index:9999;box-shadow:0 2px 10px rgba(0,0,0,0.15);">'
            . '<span>🏥 <strong>Meghna Clinic Live Showcase</strong> &bull; </span>'
            . '<a href="./index.html" style="color:#fff;font-weight:bold;text-decoration:underline;margin-left:8px;">Switch to Portals Hub (Admin, Doctor, Nurse, Billing, Patient) &rarr;</a>'
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

