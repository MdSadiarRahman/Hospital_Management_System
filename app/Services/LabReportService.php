<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\LabPartner;
use App\Models\LabReport;
use App\Models\LabReportHistory;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LabReportService
{
    /**
     * Storage disk used for private, non-public medical reports.
     */
    public const STORAGE_DISK = 'secure_reports';

    /**
     * Record internal laboratory test results with structured parameters and findings.
     */
    public function recordInternalResult(LabOrderItem $orderItem, array $data, User $actor): LabReport
    {
        return DB::transaction(function () use ($orderItem, $data, $actor) {
            $orderItem->loadMissing('labOrder.patient', 'labTest');

            // Find or create report for this order item
            $report = LabReport::where('lab_order_item_id', $orderItem->id)->lockForUpdate()->first();
            $isNew = !$report;

            if ($report && $report->isReviewed()) {
                throw new InvalidArgumentException("Report #{$report->id} has already been medically reviewed and finalized.");
            }

            $parameters = !empty($data['parameters']) && is_array($data['parameters'])
                ? $data['parameters']
                : [];

            $isCritical = !empty($data['is_critical']) || (!empty($data['has_abnormal_values']) && $data['has_abnormal_values'] === true);

            $payload = [
                'lab_order_item_id' => $orderItem->id,
                'source' => 'internal',
                'report_status' => 'received',
                'review_status' => 'pending_review', // Uploading/entering results must NOT automatically mark it reviewed
                'uploaded_by' => $actor->id,
                'uploaded_at' => now(),
                'findings_summary' => $data['findings_summary'] ?? null,
                'parameters' => $parameters,
                'is_critical' => $isCritical,
            ];

            if ($isNew) {
                $report = LabReport::create($payload);
                $prevStatus = null;
                $action = 'result_entered';
            } else {
                $prevStatus = $report->report_status;
                $report->update($payload);
                $action = 'amended';
            }

            // Progress item status to processing if not completed
            if ($orderItem->status === 'pending' || $orderItem->status === 'sample_collected') {
                $orderItem->update(['status' => 'processing']);
            }

            // Record status progression in history
            LabReportHistory::create([
                'lab_report_id' => $report->id,
                'action' => $action,
                'changed_by' => $actor->id,
                'previous_status' => $prevStatus,
                'new_status' => 'received',
                'notes' => 'Internal test results entered with ' . count($parameters) . ' structured parameters.' . ($isCritical ? ' [CRITICAL/PANIC VALUES FLAGGED]' : ''),
                'metadata' => [
                    'parameters_count' => count($parameters),
                    'is_critical' => $isCritical,
                ],
            ]);

            // Audit Trail
            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'lab_report.internal_result_entered',
                'auditable_type' => LabReport::class,
                'auditable_id' => $report->id,
                'old_values' => $isNew ? null : ['status' => $prevStatus],
                'new_values' => [
                    'test_name' => $orderItem->test_name,
                    'patient_id' => $orderItem->labOrder?->patient_id,
                    'parameters_count' => count($parameters),
                    'is_critical' => $isCritical,
                    'report_status' => 'received',
                    'review_status' => 'pending_review',
                ],
            ]);

            try {
                app(\App\Services\NotificationService::class)->notifyLabReportReady($report);
            } catch (\Throwable $e) {
                report($e);
            }

            return $report;
        });
    }

    /**
     * Upload external laboratory test report file (PDF / Image) with source tracking.
     */
    public function uploadExternalReport(LabOrderItem $orderItem, array $data, UploadedFile $file, User $actor): LabReport
    {
        return DB::transaction(function () use ($orderItem, $data, $file, $actor) {
            $orderItem->loadMissing('labOrder.patient', 'labTest');

            // Find or create report for this order item
            $report = LabReport::where('lab_order_item_id', $orderItem->id)->lockForUpdate()->first();
            $isNew = !$report;

            if ($report && $report->isReviewed()) {
                throw new InvalidArgumentException("Report #{$report->id} has already been medically reviewed and finalized.");
            }

            // 1. Generate secure randomized filename outside public access
            $extension = strtolower($file->getClientOriginalExtension());
            $safeName = 'lab_rep_' . $orderItem->id . '_' . Str::random(24) . '.' . $extension;
            $subFolder = 'reports/' . date('Y/m');

            // 2. Store on private disk outside public accessibility
            $filePath = $file->storeAs($subFolder, $safeName, self::STORAGE_DISK);

            // Determine external partner
            $partnerId = !empty($data['lab_partner_id']) 
                ? (int) $data['lab_partner_id'] 
                : ($orderItem->labOrder?->lab_partner_id ?? null);

            $partner = $partnerId ? LabPartner::find($partnerId) : null;
            $partnerName = $partner ? $partner->name : 'External Reference Laboratory';

            $payload = [
                'lab_order_item_id' => $orderItem->id,
                'source' => 'external',
                'lab_partner_id' => $partnerId,
                'external_reference_number' => $data['external_reference_number'] ?? null,
                'report_status' => 'received',
                'review_status' => 'pending_review', // Uploading a report MUST NOT automatically mark it medically reviewed
                'uploaded_by' => $actor->id,
                'uploaded_at' => now(),
                'file_path' => $filePath,
                'original_file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'findings_summary' => $data['findings_summary'] ?? null,
                'is_critical' => !empty($data['is_critical']),
            ];

            if ($isNew) {
                $report = LabReport::create($payload);
                $prevStatus = null;
                $action = 'report_uploaded';
            } else {
                // Remove previous file if replacing
                if ($report->file_path && Storage::disk(self::STORAGE_DISK)->exists($report->file_path)) {
                    Storage::disk(self::STORAGE_DISK)->delete($report->file_path);
                }
                $prevStatus = $report->report_status;
                $report->update($payload);
                $action = 'file_replaced';
            }

            // Progress item status to processing
            if ($orderItem->status === 'pending' || $orderItem->status === 'sample_collected') {
                $orderItem->update(['status' => 'processing']);
            }

            // Record history
            LabReportHistory::create([
                'lab_report_id' => $report->id,
                'action' => $action,
                'changed_by' => $actor->id,
                'previous_status' => $prevStatus,
                'new_status' => 'received',
                'notes' => "External diagnostic report uploaded from {$partnerName} [File: {$file->getClientOriginalName()}, Size: " . round($file->getSize() / 1024, 1) . " KB]. Awaiting medical doctor review.",
                'metadata' => [
                    'partner_name' => $partnerName,
                    'external_ref' => $data['external_reference_number'] ?? null,
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                ],
            ]);

            // Audit Trail
            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'lab_report.external_file_uploaded',
                'auditable_type' => LabReport::class,
                'auditable_id' => $report->id,
                'old_values' => $isNew ? null : ['status' => $prevStatus],
                'new_values' => [
                    'test_name' => $orderItem->test_name,
                    'partner_name' => $partnerName,
                    'file_name' => $file->getClientOriginalName(),
                    'report_status' => 'received',
                    'review_status' => 'pending_review',
                ],
            ]);

            try {
                app(\App\Services\NotificationService::class)->notifyLabReportReady($report);
            } catch (\Throwable $e) {
                report($e);
            }

            return $report;
        });
    }

    /**
     * Review and validate laboratory report by doctor.
     */
    public function reviewReport(LabReport $labReport, array $data, User $doctor): LabReport
    {
        return DB::transaction(function () use ($labReport, $data, $doctor) {
            $lockedReport = LabReport::where('id', $labReport->id)->lockForUpdate()->firstOrFail();

            $prevReviewStatus = $lockedReport->review_status;
            $doctorNotes = !empty($data['doctor_notes']) ? trim($data['doctor_notes']) : ($data['clinical_notes'] ?? null);

            $lockedReport->update([
                'report_status' => 'reviewed',
                'review_status' => 'reviewed',
                'reviewed_by' => $doctor->id,
                'reviewed_at' => now(),
                'doctor_notes' => $doctorNotes,
                'findings_summary' => !empty($data['findings_summary']) ? trim($data['findings_summary']) : $lockedReport->findings_summary,
                'is_critical' => isset($data['is_critical']) ? (bool) $data['is_critical'] : $lockedReport->is_critical,
            ]);

            // Complete the linked order item
            $orderItem = $lockedReport->orderItem;
            if ($orderItem) {
                $orderItem->update(['status' => 'completed']);

                // Auto-complete parent order if all items in order are finished
                $parentOrder = $orderItem->labOrder;
                if ($parentOrder) {
                    $allCompleted = $parentOrder->items()->where('id', '!=', $orderItem->id)
                        ->whereNotIn('status', ['completed', 'cancelled'])
                        ->doesntExist();

                    if ($allCompleted && $parentOrder->status !== 'completed') {
                        $parentOrder->update(['status' => 'completed']);
                    }
                }
            }

            // Record History
            LabReportHistory::create([
                'lab_report_id' => $lockedReport->id,
                'action' => 'doctor_reviewed',
                'changed_by' => $doctor->id,
                'previous_status' => $prevReviewStatus,
                'new_status' => 'reviewed',
                'notes' => "Medically reviewed and verified by Dr. {$doctor->name}." . ($doctorNotes ? " Comments: {$doctorNotes}" : ''),
                'metadata' => [
                    'doctor_name' => $doctor->name,
                    'doctor_notes' => $doctorNotes,
                ],
            ]);

            // Audit Trail
            AuditLog::create([
                'user_id' => $doctor->id,
                'event' => 'lab_report.reviewed',
                'auditable_type' => LabReport::class,
                'auditable_id' => $lockedReport->id,
                'old_values' => ['review_status' => $prevReviewStatus],
                'new_values' => [
                    'review_status' => 'reviewed',
                    'reviewed_by' => $doctor->id,
                    'reviewed_at' => now()->toIso8601String(),
                ],
            ]);

            try {
                app(\App\Services\NotificationService::class)->notifyLabReportReady($lockedReport);
            } catch (\Throwable $e) {
                report($e);
            }

            return $lockedReport;
        });
    }

    /**
     * Authorize and securely stream or download the non-public report file.
     */
    public function getReportStream(LabReport $labReport, User $user, bool $download = false): BinaryFileResponse|StreamedResponse
    {
        // 1. Authorization check
        $this->authorizeReportAccess($labReport, $user);

        // 2. Validate file existence
        if (!$labReport->file_path || !Storage::disk(self::STORAGE_DISK)->exists($labReport->file_path)) {
            abort(404, 'Diagnostic report file not found on secure storage.');
        }

        $fullPath = Storage::disk(self::STORAGE_DISK)->path($labReport->file_path);
        $fileName = $labReport->original_file_name ?: basename($labReport->file_path);
        $headers = [
            'Content-Type' => $labReport->file_type ?: 'application/pdf',
            'Cache-Control' => 'no-cache, private',
        ];

        if ($download) {
            return response()->download($fullPath, $fileName, $headers);
        }

        return response()->file($fullPath, $headers);
    }

    /**
     * Verify strict model-level authorization to access or download diagnostic report.
     */
    public function authorizeReportAccess(LabReport $labReport, User $user): void
    {
        // Staff with clinical or administrative roles have access
        if ($user->user_type === 'staff' && ($user->hasRole(['super_admin', 'administrator', 'doctor', 'lab_staff', 'nurse']) || $user->hasPermission('lab.view'))) {
            return;
        }

        // Patient access: Only the verified patient who owns the report can view/download
        if ($user->user_type === 'patient') {
            $patient = $labReport->patient;
            if ($patient && $patient->user_id === $user->id) {
                return;
            }
        }

        abort(403, 'Unauthorized access to confidential patient laboratory report.');
    }

    /**
     * Get paginated laboratory reports list with filtering.
     */
    public function getReportsList(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return LabReport::with([
            'orderItem.labOrder.patient',
            'orderItem.labOrder.doctor',
            'orderItem.labTest',
            'labPartner',
            'uploadedBy',
            'reviewedBy',
        ])
        ->when(!empty($filters['q']), function ($query) use ($filters) {
            $term = trim($filters['q']);
            $query->where(function ($q) use ($term) {
                $q->whereHas('orderItem', function ($sub) use ($term) {
                    $sub->where('test_name', 'like', "%{$term}%")
                        ->orWhereHas('labOrder', function ($orderSub) use ($term) {
                            $orderSub->where('order_number', 'like', "%{$term}%")
                                ->orWhereHas('patient', function ($pSub) use ($term) {
                                    $pSub->where('name', 'like', "%{$term}%")
                                        ->orWhere('patient_code', 'like', "%{$term}%");
                                });
                        });
                })
                ->orWhere('findings_summary', 'like', "%{$term}%")
                ->orWhere('external_reference_number', 'like', "%{$term}%");
            });
        })
        ->when(!empty($filters['source']) && $filters['source'] !== 'all', function ($query) use ($filters) {
            $query->where('source', $filters['source']);
        })
        ->when(!empty($filters['review_status']) && $filters['review_status'] !== 'all', function ($query) use ($filters) {
            $query->where('review_status', $filters['review_status']);
        })
        ->when(isset($filters['is_critical']) && $filters['is_critical'] !== '' && $filters['is_critical'] !== 'all', function ($query) use ($filters) {
            $query->where('is_critical', (bool) $filters['is_critical']);
        })
        ->when(!empty($filters['partner_id']), function ($query) use ($filters) {
            $query->where('lab_partner_id', (int) $filters['partner_id']);
        })
        ->latest('updated_at')
        ->paginate($perPage);
    }

    /**
     * Get KPI statistics for report management workbench.
     */
    public function getReportStatistics(): array
    {
        return [
            'total_reports' => LabReport::count(),
            'pending_review' => LabReport::where('review_status', 'pending_review')->count(),
            'reviewed' => LabReport::where('review_status', 'reviewed')->count(),
            'internal_reports' => LabReport::where('source', 'internal')->count(),
            'external_reports' => LabReport::where('source', 'external')->count(),
            'critical_reports' => LabReport::where('is_critical', true)->count(),
        ];
    }
}

