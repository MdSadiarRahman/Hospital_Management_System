<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\LabPartner;
use App\Models\LabTestCatalog;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LabCatalogService
{
    /**
     * Standard laboratory diagnostic categories in Meghna Clinic.
     */
    public const STANDARD_CATEGORIES = [
        'Hematology',
        'Biochemistry',
        'Clinical Pathology',
        'Microbiology & Serology',
        'Immunology',
        'Radiology & Imaging',
        'Ultrasonography',
        'Histopathology & Cytology',
        'Cardiology / ECG',
    ];

    /**
     * Standard specimen / sample types.
     */
    public const STANDARD_SAMPLE_TYPES = [
        'Whole Blood',
        'Serum',
        'Plasma',
        'Random Urine',
        '24-Hour Urine',
        'Stool',
        'Sputum',
        'CSF (Cerebrospinal Fluid)',
        'Pleural Fluid',
        'Throat Swab',
        'Wound Swab',
        'Tissue Biopsy',
        'None (Radiology / Imaging)',
    ];

    /**
     * Get paginated laboratory test catalog with dynamic filters.
     */
    public function getCatalog(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return LabTestCatalog::with('labPartner')
            ->filter($filters)
            ->orderBy('category', 'asc')
            ->orderBy('name', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Create a new diagnostic test in the catalog.
     */
    public function createTest(array $data, User $actor): LabTestCatalog
    {
        return DB::transaction(function () use ($data, $actor) {
            $test = LabTestCatalog::create([
                'name' => trim($data['name']),
                'code' => strtoupper(trim($data['code'])),
                'category' => trim($data['category']),
                'description' => !empty($data['description']) ? trim($data['description']) : null,
                'standard_charge' => (float) ($data['standard_charge'] ?? $data['price'] ?? 0.00),
                'sample_type' => !empty($data['sample_type']) ? trim($data['sample_type']) : 'Blood',
                'turnaround_time' => !empty($data['turnaround_time']) ? trim($data['turnaround_time']) : '4 hours',
                'instructions' => !empty($data['instructions']) ? trim($data['instructions']) : null,
                'default_source' => $data['default_source'] ?? 'internal',
                'lab_partner_id' => !empty($data['lab_partner_id']) ? (int) $data['lab_partner_id'] : null,
                'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : true,
            ]);

            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'lab_test.created',
                'auditable_type' => LabTestCatalog::class,
                'auditable_id' => $test->id,
                'old_values' => null,
                'new_values' => [
                    'name' => $test->name,
                    'code' => $test->code,
                    'category' => $test->category,
                    'standard_charge' => $test->standard_charge,
                    'sample_type' => $test->sample_type,
                    'turnaround_time' => $test->turnaround_time,
                    'default_source' => $test->default_source,
                    'is_active' => $test->is_active,
                ],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
                'created_at' => now(),
            ]);

            return $test;
        });
    }

    /**
     * Update an existing diagnostic test configuration.
     */
    public function updateTest(LabTestCatalog $test, array $data, User $actor): LabTestCatalog
    {
        return DB::transaction(function () use ($test, $data, $actor) {
            $oldValues = [
                'name' => $test->name,
                'code' => $test->code,
                'category' => $test->category,
                'description' => $test->description,
                'standard_charge' => $test->standard_charge,
                'sample_type' => $test->sample_type,
                'turnaround_time' => $test->turnaround_time,
                'instructions' => $test->instructions,
                'default_source' => $test->default_source,
                'lab_partner_id' => $test->lab_partner_id,
                'is_active' => $test->is_active,
            ];

            $test->update([
                'name' => trim($data['name']),
                'code' => strtoupper(trim($data['code'])),
                'category' => trim($data['category']),
                'description' => !empty($data['description']) ? trim($data['description']) : null,
                'standard_charge' => (float) ($data['standard_charge'] ?? $data['price'] ?? $test->standard_charge),
                'sample_type' => !empty($data['sample_type']) ? trim($data['sample_type']) : $test->sample_type,
                'turnaround_time' => !empty($data['turnaround_time']) ? trim($data['turnaround_time']) : $test->turnaround_time,
                'instructions' => !empty($data['instructions']) ? trim($data['instructions']) : null,
                'default_source' => $data['default_source'] ?? $test->default_source,
                'lab_partner_id' => !empty($data['lab_partner_id']) ? (int) $data['lab_partner_id'] : null,
                'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : $test->is_active,
            ]);

            $newValues = [
                'name' => $test->name,
                'code' => $test->code,
                'category' => $test->category,
                'description' => $test->description,
                'standard_charge' => $test->standard_charge,
                'sample_type' => $test->sample_type,
                'turnaround_time' => $test->turnaround_time,
                'instructions' => $test->instructions,
                'default_source' => $test->default_source,
                'lab_partner_id' => $test->lab_partner_id,
                'is_active' => $test->is_active,
            ];

            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'lab_test.updated',
                'auditable_type' => LabTestCatalog::class,
                'auditable_id' => $test->id,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
                'created_at' => now(),
            ]);

            return $test;
        });
    }

    /**
     * Toggle active/inactive status of a diagnostic test.
     */
    public function toggleStatus(LabTestCatalog $test, User $actor): LabTestCatalog
    {
        return DB::transaction(function () use ($test, $actor) {
            $oldStatus = $test->is_active;
            $newStatus = !$oldStatus;

            $test->update(['is_active' => $newStatus]);

            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'lab_test.status_toggled',
                'auditable_type' => LabTestCatalog::class,
                'auditable_id' => $test->id,
                'old_values' => ['is_active' => $oldStatus],
                'new_values' => ['is_active' => $newStatus],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
                'created_at' => now(),
            ]);

            return $test;
        });
    }

    /**
     * High-level laboratory catalog KPI statistics.
     */
    public function getStatistics(): array
    {
        return [
            'total_tests' => LabTestCatalog::count(),
            'active_tests' => LabTestCatalog::where('is_active', true)->count(),
            'inactive_tests' => LabTestCatalog::where('is_active', false)->count(),
            'internal_tests' => LabTestCatalog::whereIn('default_source', ['internal', 'both'])->count(),
            'external_tests' => LabTestCatalog::where('default_source', 'external')->count(),
            'categories_count' => LabTestCatalog::distinct('category')->count('category'),
            'partners_count' => LabPartner::where('is_active', true)->count(),
        ];
    }

    /**
     * Retrieve all distinct categories (standard + custom).
     */
    public function getCategories(): array
    {
        $existing = LabTestCatalog::distinct()->pluck('category')->filter()->toArray();
        return array_values(array_unique(array_merge(self::STANDARD_CATEGORIES, $existing)));
    }

    /**
     * Retrieve standard sample types.
     */
    public function getSampleTypes(): array
    {
        $existing = LabTestCatalog::distinct()->pluck('sample_type')->filter()->toArray();
        return array_values(array_unique(array_merge(self::STANDARD_SAMPLE_TYPES, $existing)));
    }

    /**
     * Active external reference partners.
     */
    public function getActivePartners(): Collection
    {
        return LabPartner::where('is_active', true)->orderBy('name')->get();
    }
}

