<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Consultation;
use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\LabOrderStatusHistory;
use App\Models\LabPartner;
use App\Models\LabSample;
use App\Models\LabTestCatalog;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LabOrderService
{
    /**
     * Create a new laboratory order with multiple diagnostic tests.
     */
    public function createOrder(array $data, User $actor): LabOrder
    {
        return DB::transaction(function () use ($data, $actor) {
            $consultation = Consultation::with('visit.patient')->findOrFail($data['consultation_id']);
            $patient = Patient::findOrFail($data['patient_id']);

            // Verify consultation patient matches
            if ($consultation->visit && $consultation->visit->patient_id !== $patient->id) {
                throw new InvalidArgumentException("Consultation #{$consultation->id} does not belong to patient '{$patient->name}'.");
            }

            // Generate unique Order Number
            $orderNumber = $this->generateOrderNumber();

            // Source and partner routing
            $source = $data['source'] ?? 'internal';
            $partnerId = ($source === 'external' && !empty($data['lab_partner_id'])) ? (int) $data['lab_partner_id'] : null;
            $priority = $data['priority'] ?? 'routine';

            // Calculate total charge and prepare order items
            $testIds = collect($data['tests'])->pluck('lab_test_id')->toArray();
            $catalogItems = LabTestCatalog::whereIn('id', $testIds)->get()->keyBy('id');

            $totalCharge = 0.00;
            $itemsData = [];

            foreach ($data['tests'] as $testInput) {
                $testId = (int) $testInput['lab_test_id'];
                if (!$catalogItems->has($testId)) {
                    throw new InvalidArgumentException("Test ID {$testId} not found in catalog.");
                }

                $catalogTest = $catalogItems->get($testId);
                $charge = (float) $catalogTest->standard_charge;
                $totalCharge += $charge;

                $itemsData[] = [
                    'lab_test_id' => $catalogTest->id,
                    'test_name' => $catalogTest->name,
                    'test_charge' => $charge,
                    'status' => 'pending',
                    'instructions' => !empty($testInput['instructions']) ? trim($testInput['instructions']) : $catalogTest->instructions,
                ];
            }

            // Create parent Lab Order
            $order = LabOrder::create([
                'consultation_id' => $consultation->id,
                'patient_id' => $patient->id,
                'doctor_id' => $consultation->doctor_id ?? $actor->id,
                'order_number' => $orderNumber,
                'source' => $source,
                'lab_partner_id' => $partnerId,
                'status' => 'pending',
                'priority' => $priority,
                'total_charge' => $totalCharge,
                'clinical_indication' => !empty($data['clinical_indication']) ? trim($data['clinical_indication']) : null,
            ]);

            // Save order items
            foreach ($itemsData as $item) {
                $order->items()->create($item);
            }

            // Record initial status history
            LabOrderStatusHistory::create([
                'lab_order_id' => $order->id,
                'from_status' => null,
                'to_status' => 'pending',
                'changed_by' => $actor->id,
                'notes' => 'Diagnostic test requisition created with ' . count($itemsData) . ' requested tests',
            ]);

            // Create AuditLog
            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'lab_order.created',
                'auditable_type' => LabOrder::class,
                'auditable_id' => $order->id,
                'old_values' => null,
                'new_values' => [
                    'order_number' => $order->order_number,
                    'patient_id' => $order->patient_id,
                    'patient_name' => $patient->name,
                    'consultation_id' => $order->consultation_id,
                    'priority' => $order->priority,
                    'source' => $order->source,
                    'total_charge' => $order->total_charge,
                    'tests_count' => count($itemsData),
                ],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
                'created_at' => now(),
            ]);

            return $order->load(['items.labTest', 'patient', 'doctor', 'labPartner']);
        });
    }

    /**
     * Phlebotomy / Laboratory specimen collection and barcode generation.
     */
    public function collectSample(LabOrder $order, array $data, User $actor): LabSample
    {
        if ($order->isCancelled()) {
            throw new InvalidArgumentException("Cannot collect sample for cancelled lab order #{$order->order_number}.");
        }

        return DB::transaction(function () use ($order, $data, $actor) {
            $barcode = !empty($data['barcode']) ? strtoupper(trim($data['barcode'])) : $this->generateBarcode();
            $collectedAt = !empty($data['collected_at']) ? Carbon::parse($data['collected_at']) : now();

            $sample = LabSample::create([
                'lab_order_id' => $order->id,
                'sample_type' => trim($data['sample_type']),
                'barcode' => $barcode,
                'collected_at' => $collectedAt,
                'collected_by' => $actor->id,
                'status' => 'collected',
                'notes' => !empty($data['notes']) ? trim($data['notes']) : null,
            ]);

            // Link items to this sample
            $itemIds = !empty($data['item_ids']) ? $data['item_ids'] : $order->items->pluck('id')->toArray();
            LabOrderItem::whereIn('id', $itemIds)
                ->where('lab_order_id', $order->id)
                ->update([
                    'lab_sample_id' => $sample->id,
                    'status' => 'sample_collected',
                ]);

            // Update parent order status if pending
            $oldStatus = $order->status;
            if ($order->status === 'pending') {
                $order->update(['status' => 'sample_collected']);

                LabOrderStatusHistory::create([
                    'lab_order_id' => $order->id,
                    'from_status' => $oldStatus,
                    'to_status' => 'sample_collected',
                    'changed_by' => $actor->id,
                    'notes' => "Specimen collected [Barcode: {$sample->barcode}] ({$sample->sample_type})",
                ]);
            }

            // Audit Trail
            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'lab_sample.collected',
                'auditable_type' => LabSample::class,
                'auditable_id' => $sample->id,
                'old_values' => null,
                'new_values' => [
                    'order_number' => $order->order_number,
                    'barcode' => $sample->barcode,
                    'sample_type' => $sample->sample_type,
                    'collected_at' => $sample->collected_at->toDateTimeString(),
                    'collected_by' => $actor->name,
                ],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
                'created_at' => now(),
            ]);

            return $sample;
        });
    }

    /**
     * Transition laboratory order status with audit log and history preservation.
     */
    public function updateOrderStatus(LabOrder $order, string $newStatus, User $actor, ?string $notes = null): LabOrder
    {
        return DB::transaction(function () use ($order, $newStatus, $actor, $notes) {
            $lockedOrder = LabOrder::where('id', $order->id)->lockForUpdate()->firstOrFail();
            $oldStatus = $lockedOrder->status;

            if ($oldStatus === $newStatus) {
                return $lockedOrder;
            }

            // Prevent unauthorized modification of completed/cancelled orders
            if (in_array($oldStatus, ['completed', 'cancelled']) && !$actor->hasRole(['super_admin', 'administrator'])) {
                throw new InvalidArgumentException("Status of {$oldStatus} lab order cannot be modified without administrator authorization.");
            }

            $lockedOrder->update(['status' => $newStatus]);

            // If order completed, mark pending/processing items as completed
            if ($newStatus === 'completed') {
                $lockedOrder->items()
                    ->whereIn('status', ['pending', 'sample_collected', 'processing'])
                    ->update(['status' => 'completed']);
            } elseif ($newStatus === 'cancelled') {
                $lockedOrder->items()
                    ->whereIn('status', ['pending', 'sample_collected', 'processing'])
                    ->update(['status' => 'cancelled']);
            }

            // Record status history
            LabOrderStatusHistory::create([
                'lab_order_id' => $lockedOrder->id,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'changed_by' => $actor->id,
                'notes' => $notes,
            ]);

            // Audit Trail
            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'lab_order.status_updated',
                'auditable_type' => LabOrder::class,
                'auditable_id' => $lockedOrder->id,
                'old_values' => ['status' => $oldStatus],
                'new_values' => ['status' => $newStatus, 'notes' => $notes],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
                'created_at' => now(),
            ]);

            return $lockedOrder;
        });
    }

    /**
     * Track and update an individual test separately within a multi-test order.
     */
    public function updateItemStatus(LabOrderItem $item, string $newStatus, User $actor, ?string $notes = null): LabOrderItem
    {
        return DB::transaction(function () use ($item, $newStatus, $actor, $notes) {
            $lockedItem = LabOrderItem::with('labOrder')->where('id', $item->id)->lockForUpdate()->firstOrFail();
            $oldStatus = $lockedItem->status;

            if ($oldStatus === $newStatus) {
                return $lockedItem;
            }

            $lockedItem->update(['status' => $newStatus]);

            $order = $lockedItem->labOrder;

            // Auto-sync parent order:
            // If all items are completed, mark parent order completed
            $allItems = $order->items;
            $allCompleted = $allItems->every(fn($i) => in_array($i->status, ['completed', 'cancelled']));

            if ($allCompleted && $order->status !== 'completed') {
                $orderOldStatus = $order->status;
                $order->update(['status' => 'completed']);

                LabOrderStatusHistory::create([
                    'lab_order_id' => $order->id,
                    'from_status' => $orderOldStatus,
                    'to_status' => 'completed',
                    'changed_by' => $actor->id,
                    'notes' => "All {$allItems->count()} diagnostic tests completed",
                ]);
            } elseif ($newStatus === 'processing' && in_array($order->status, ['pending', 'sample_collected'])) {
                $orderOldStatus = $order->status;
                $order->update(['status' => 'processing']);

                LabOrderStatusHistory::create([
                    'lab_order_id' => $order->id,
                    'from_status' => $orderOldStatus,
                    'to_status' => 'processing',
                    'changed_by' => $actor->id,
                    'notes' => "Test '{$lockedItem->test_name}' moved to active processing in lab",
                ]);
            }

            // Audit Trail
            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'lab_order_item.status_updated',
                'auditable_type' => LabOrderItem::class,
                'auditable_id' => $lockedItem->id,
                'old_values' => ['status' => $oldStatus],
                'new_values' => ['status' => $newStatus, 'notes' => $notes],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
                'created_at' => now(),
            ]);

            return $lockedItem;
        });
    }

    /**
     * Assign or transfer an order to an external reference partner laboratory.
     */
    public function assignPartner(LabOrder $order, int $partnerId, User $actor, ?string $notes = null): LabOrder
    {
        return DB::transaction(function () use ($order, $partnerId, $actor, $notes) {
            $partner = LabPartner::findOrFail($partnerId);
            $oldPartnerId = $order->lab_partner_id;

            $order->update([
                'source' => 'external',
                'lab_partner_id' => $partner->id,
            ]);

            LabOrderStatusHistory::create([
                'lab_order_id' => $order->id,
                'from_status' => $order->status,
                'to_status' => $order->status,
                'changed_by' => $actor->id,
                'notes' => "Assigned to external partner: {$partner->name} ({$partner->code}). " . ($notes ? "Notes: {$notes}" : ''),
            ]);

            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'lab_order.partner_assigned',
                'auditable_type' => LabOrder::class,
                'auditable_id' => $order->id,
                'old_values' => ['lab_partner_id' => $oldPartnerId, 'source' => 'internal'],
                'new_values' => ['lab_partner_id' => $partner->id, 'source' => 'external'],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
                'created_at' => now(),
            ]);

            return $order;
        });
    }

    /**
     * Get paginated laboratory orders list.
     */
    public function getOrdersList(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return LabOrder::with([
            'patient',
            'doctor',
            'consultation',
            'labPartner',
            'items.labTest',
            'samples.collectedBy',
        ])
        ->filter($filters)
        ->recent()
        ->paginate($perPage)
        ->withQueryString();
    }

    /**
     * High-level operational statistics for the laboratory workbench.
     */
    public function getOrderStatistics(): array
    {
        return [
            'total_orders' => LabOrder::count(),
            'pending_samples' => LabOrder::where('status', 'pending')->count(),
            'in_processing' => LabOrder::whereIn('status', ['sample_collected', 'processing'])->count(),
            'completed' => LabOrder::where('status', 'completed')->count(),
            'completed_today' => LabOrder::where('status', 'completed')->whereDate('updated_at', today())->count(),
            'external_referrals' => LabOrder::where('source', 'external')->count(),
            'urgent_stat' => LabOrder::whereIn('priority', ['urgent', 'stat'])->where('status', '!=', 'completed')->count(),
            'samples_collected' => LabSample::count(),
        ];
    }

    /**
     * Generate sequential unique order number: LAB-YYYYMMDD-XXXX.
     */
    public function generateOrderNumber(): string
    {
        $todayStr = now()->format('Ymd');
        $prefix = "LAB-{$todayStr}-";

        $latestOrder = LabOrder::where('order_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $nextSeq = 1;
        if ($latestOrder) {
            $parts = explode('-', $latestOrder->order_number);
            $lastPart = end($parts);
            $nextSeq = ((int) $lastPart) + 1;
        }

        return sprintf("%s%04d", $prefix, $nextSeq);
    }

    /**
     * Generate unique sample barcode identifier: SMP-YYYYMMDD-XXXX.
     */
    public function generateBarcode(): string
    {
        $todayStr = now()->format('Ymd');
        $prefix = "SMP-{$todayStr}-";

        $latestSample = LabSample::where('barcode', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $nextSeq = 1;
        if ($latestSample) {
            $parts = explode('-', $latestSample->barcode);
            $lastPart = end($parts);
            $nextSeq = ((int) $lastPart) + 1;
        }

        return sprintf("%s%04d", $prefix, $nextSeq);
    }
}

