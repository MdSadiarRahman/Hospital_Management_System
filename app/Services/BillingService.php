<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Patient;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BillingService
{
    /**
     * Generate unique, non-colliding invoice number: INV-YYYYMMDD-XXXX
     */
    public function generateInvoiceNumber(): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "INV-{$today}-";

        return DB::transaction(function () use ($prefix) {
            $latest = Bill::where('invoice_number', 'like', "{$prefix}%")
                ->lockForUpdate()
                ->orderBy('invoice_number', 'desc')
                ->value('invoice_number');

            if ($latest) {
                $lastSequence = (int) substr($latest, -4);
                $nextSequence = str_pad($lastSequence + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $nextSequence = '0001';
            }

            return "{$prefix}{$nextSequence}";
        });
    }

    /**
     * Create a new Bill with multiple items and optional authorized discount.
     */
    public function createBill(array $data, User $actor): Bill
    {
        return DB::transaction(function () use ($data, $actor) {
            $patient = Patient::findOrFail($data['patient_id']);

            $invoiceNumber = $this->generateInvoiceNumber();
            $billDate = !empty($data['bill_date']) ? Carbon::parse($data['bill_date']) : Carbon::now();

            $bill = Bill::create([
                'patient_id' => $patient->id,
                'admission_id' => !empty($data['admission_id']) ? (int) $data['admission_id'] : null,
                'visit_id' => !empty($data['visit_id']) ? (int) $data['visit_id'] : null,
                'invoice_number' => $invoiceNumber,
                'bill_date' => $billDate,
                'subtotal_amount' => '0.00',
                'discount_amount' => '0.00',
                'discount_reason' => null,
                'discount_authorized_by' => null,
                'total_amount' => '0.00',
                'paid_amount' => '0.00',
                'due_amount' => '0.00',
                'status' => 'unpaid',
                'notes' => !empty($data['notes']) ? trim($data['notes']) : null,
                'created_by' => $actor->id,
            ]);

            // Add bill items
            if (!empty($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $itemData) {
                    $this->addItemToBill($bill, $itemData, $actor, false);
                }
            }

            // Recalculate subtotal
            $bill->load('items');
            $subtotal = '0.00';
            foreach ($bill->items as $item) {
                $subtotal = bcadd((string) $subtotal, (string) $item->total_price, 2);
            }
            $bill->subtotal_amount = $subtotal;

            // Handle discount if requested
            $discountRequested = isset($data['discount_amount']) ? (float) $data['discount_amount'] : 0.00;
            if ($discountRequested > 0.00) {
                if (!$this->canAuthorizeDiscount($actor)) {
                    throw new AuthorizationException("You are not authorized to grant financial discounts on invoices.");
                }

                $discountStr = number_format($discountRequested, 2, '.', '');
                if (bccomp($discountStr, (string) $subtotal, 2) > 0) {
                    throw new InvalidArgumentException("Discount amount (BDT {$discountStr}) cannot exceed bill subtotal (BDT {$subtotal}).");
                }

                $bill->discount_amount = $discountStr;
                $bill->discount_reason = !empty($data['discount_reason']) ? trim($data['discount_reason']) : 'Authorized clinic concession';
                $bill->discount_authorized_by = $actor->id;
            }

            $total = bcsub((string) $bill->subtotal_amount, (string) $bill->discount_amount, 2);
            $bill->total_amount = $total;
            $bill->due_amount = $total;
            $bill->status = 'unpaid';
            $bill->save();

            // Record Financial Audit Log
            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'bill.created',
                'auditable_type' => Bill::class,
                'auditable_id' => $bill->id,
                'old_values' => null,
                'new_values' => [
                    'invoice_number' => $bill->invoice_number,
                    'patient_id' => $patient->id,
                    'patient_code' => $patient->patient_code,
                    'subtotal_amount' => $bill->subtotal_amount,
                    'discount_amount' => $bill->discount_amount,
                    'total_amount' => $bill->total_amount,
                    'items_count' => $bill->items->count(),
                ],
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'created_at' => Carbon::now(),
            ]);

            try {
                app(\App\Services\NotificationService::class)->notifyBillingUpdate($bill, 'generated');
            } catch (\Throwable $e) {
                report($e);
            }

            return $bill->fresh(['patient', 'items.service', 'createdBy', 'discountAuthorizedBy']);
        });
    }

    /**
     * Add a line item to an existing bill and recalculate totals.
     */
    public function addBillItem(Bill $bill, array $itemData, User $actor): BillItem
    {
        return DB::transaction(function () use ($bill, $itemData, $actor) {
            $item = $this->addItemToBill($bill, $itemData, $actor, true);

            $bill->load('items');
            $bill->recalculateTotals();
            $bill->save();

            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'bill.item_added',
                'auditable_type' => Bill::class,
                'auditable_id' => $bill->id,
                'old_values' => null,
                'new_values' => [
                    'item_id' => $item->id,
                    'item_name' => $item->item_name,
                    'unit_price' => $item->unit_price,
                    'quantity' => $item->quantity,
                    'total_price' => $item->total_price,
                    'new_subtotal' => $bill->subtotal_amount,
                    'new_total' => $bill->total_amount,
                ],
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'created_at' => Carbon::now(),
            ]);

            return $item;
        });
    }

    /**
     * Internal helper to persist an item to a bill.
     */
    protected function addItemToBill(Bill $bill, array $itemData, User $actor, bool $refreshBill = false): BillItem
    {
        $service = null;
        if (!empty($itemData['service_id'])) {
            $service = Service::with('category')->findOrFail($itemData['service_id']);
        }

        $serviceId = $service?->id;
        $serviceCode = $service?->code ?? ($itemData['service_code'] ?? null);
        $itemName = !empty($itemData['item_name']) ? trim($itemData['item_name']) : ($service?->name ?? 'Medical Service');

        // Determine item type matching enum: ['bed', 'consultation', 'lab', 'surgery', 'service']
        $itemType = $itemData['item_type'] ?? null;
        if (!$itemType && $service) {
            $catCode = strtoupper($service->category->code ?? '');
            $itemType = match ($catCode) {
                'BED_CHARGES' => 'bed',
                'CONSULTATION' => 'consultation',
                'LAB_TESTS' => 'lab',
                'OPERATION_CHARGES' => 'surgery',
                default => 'service',
            };
        }
        $itemType = $itemType ?? 'service';

        // Historical price preservation: snapshot current service price or given unit price
        $unitPriceVal = isset($itemData['unit_price']) && is_numeric($itemData['unit_price'])
            ? (float) $itemData['unit_price']
            : (float) ($service?->price ?? 0.00);

        $quantity = !empty($itemData['quantity']) && (int) $itemData['quantity'] > 0
            ? (int) $itemData['quantity']
            : 1;

        $unitPriceStr = number_format($unitPriceVal, 2, '.', '');
        $totalPriceStr = bcmul($unitPriceStr, (string) $quantity, 2);

        return BillItem::create([
            'bill_id' => $bill->id,
            'service_id' => $serviceId,
            'service_code' => $serviceCode,
            'item_type' => $itemType,
            'item_name' => $itemName,
            'unit_price' => $unitPriceStr,
            'quantity' => $quantity,
            'total_price' => $totalPriceStr,
        ]);
    }

    /**
     * Remove a line item from a bill and recalculate totals.
     */
    public function removeBillItem(Bill $bill, BillItem $item, User $actor): void
    {
        DB::transaction(function () use ($bill, $item, $actor) {
            if ($item->bill_id !== $bill->id) {
                throw new InvalidArgumentException("Bill item does not belong to invoice {$bill->invoice_number}.");
            }

            $deletedValues = [
                'item_id' => $item->id,
                'item_name' => $item->item_name,
                'total_price' => $item->total_price,
            ];

            $item->delete();

            $bill->load('items');
            $bill->recalculateTotals();
            $bill->save();

            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'bill.item_removed',
                'auditable_type' => Bill::class,
                'auditable_id' => $bill->id,
                'old_values' => $deletedValues,
                'new_values' => [
                    'new_subtotal' => $bill->subtotal_amount,
                    'new_total' => $bill->total_amount,
                ],
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'created_at' => Carbon::now(),
            ]);
        });
    }

    /**
     * Apply or update an authorized concession/discount on an existing bill.
     */
    public function applyDiscount(Bill $bill, float|string $discountAmount, ?string $reason, User $actor): Bill
    {
        return DB::transaction(function () use ($bill, $discountAmount, $reason, $actor) {
            if (!$this->canAuthorizeDiscount($actor)) {
                throw new AuthorizationException("You do not have authorization to grant or modify discounts.");
            }

            $bill->load('items');
            $subtotal = '0.00';
            foreach ($bill->items as $item) {
                $subtotal = bcadd((string) $subtotal, (string) $item->total_price, 2);
            }

            $discountStr = number_format((float) $discountAmount, 2, '.', '');
            if (bccomp($discountStr, '0.00', 2) < 0) {
                throw new InvalidArgumentException("Discount amount cannot be negative.");
            }

            if (bccomp($discountStr, (string) $subtotal, 2) > 0) {
                throw new InvalidArgumentException("Discount amount (BDT {$discountStr}) cannot exceed bill subtotal (BDT {$subtotal}).");
            }

            $oldDiscount = $bill->discount_amount;
            $oldTotal = $bill->total_amount;

            $bill->subtotal_amount = $subtotal;
            $bill->discount_amount = $discountStr;
            $bill->discount_reason = !empty($reason) ? trim($reason) : 'Authorized clinical concession';
            $bill->discount_authorized_by = $actor->id;

            $bill->recalculateTotals();
            $bill->save();

            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'bill.discount_applied',
                'auditable_type' => Bill::class,
                'auditable_id' => $bill->id,
                'old_values' => [
                    'discount_amount' => $oldDiscount,
                    'total_amount' => $oldTotal,
                ],
                'new_values' => [
                    'discount_amount' => $bill->discount_amount,
                    'discount_reason' => $bill->discount_reason,
                    'discount_authorized_by' => $actor->id,
                    'total_amount' => $bill->total_amount,
                    'due_amount' => $bill->due_amount,
                ],
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'created_at' => Carbon::now(),
            ]);

            try {
                app(\App\Services\NotificationService::class)->notifyBillingUpdate($bill, 'generated');
            } catch (\Throwable $e) {
                report($e);
            }

            return $bill->fresh(['patient', 'items.service', 'createdBy', 'discountAuthorizedBy']);
        });
    }

    /**
     * Check if a user is authorized to approve discounts.
     */
    public function canAuthorizeDiscount(User $user): bool
    {
        if ($user->hasRole(['super_admin', 'administrator'])) {
            return true;
        }

        return $user->hasPermission('billing.approve');
    }

    /*
    |--------------------------------------------------------------------------
    | Service Catalog Management
    |--------------------------------------------------------------------------
    */

    /**
     * Check if a user is authorized to manage service catalog & pricing.
     */
    public function canManageServices(User $user): bool
    {
        if ($user->hasRole(['super_admin', 'administrator'])) {
            return true;
        }

        return $user->hasPermission('billing.update') || $user->hasPermission('settings.manage');
    }

    /**
     * Create a new catalog service.
     */
    public function createService(array $data, User $actor): Service
    {
        if (!$this->canManageServices($actor)) {
            throw new AuthorizationException("You are not authorized to create catalog services.");
        }

        return DB::transaction(function () use ($data, $actor) {
            $category = ServiceCategory::findOrFail($data['category_id']);

            $priceStr = number_format((float) ($data['price'] ?? 0.00), 2, '.', '');

            $service = Service::create([
                'category_id' => $category->id,
                'name' => trim($data['name']),
                'code' => strtoupper(trim($data['code'])),
                'price' => $priceStr,
                'description' => !empty($data['description']) ? trim($data['description']) : null,
                'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : true,
            ]);

            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'service.created',
                'auditable_type' => Service::class,
                'auditable_id' => $service->id,
                'old_values' => null,
                'new_values' => [
                    'name' => $service->name,
                    'code' => $service->code,
                    'category' => $category->name,
                    'price' => $service->price,
                ],
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'created_at' => Carbon::now(),
            ]);

            return $service;
        });
    }

    /**
     * Update an existing service and its price.
     * Crucial: Historical bill item prices remain completely preserved.
     */
    public function updateService(Service $service, array $data, User $actor): Service
    {
        if (!$this->canManageServices($actor)) {
            throw new AuthorizationException("You are not authorized to alter service pricing or details.");
        }

        return DB::transaction(function () use ($service, $data, $actor) {
            $oldValues = [
                'name' => $service->name,
                'price' => (string) $service->price,
                'is_active' => $service->is_active,
            ];

            $newPrice = isset($data['price']) ? number_format((float) $data['price'], 2, '.', '') : (string) $service->price;
            $priceChanged = bccomp((string) $service->price, $newPrice, 2) !== 0;

            $service->update([
                'category_id' => !empty($data['category_id']) ? (int) $data['category_id'] : $service->category_id,
                'name' => !empty($data['name']) ? trim($data['name']) : $service->name,
                'price' => $newPrice,
                'description' => array_key_exists('description', $data) ? (!empty($data['description']) ? trim($data['description']) : null) : $service->description,
                'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : $service->is_active,
            ]);

            // Financial audit log specifically for price modification
            if ($priceChanged) {
                AuditLog::create([
                    'user_id' => $actor->id,
                    'event' => 'service.price_updated',
                    'auditable_type' => Service::class,
                    'auditable_id' => $service->id,
                    'old_values' => ['price' => $oldValues['price']],
                    'new_values' => ['price' => $service->price],
                    'ip_address' => request()?->ip(),
                    'user_agent' => request()?->userAgent(),
                    'created_at' => Carbon::now(),
                ]);
            }

            return $service;
        });
    }

    /**
     * Toggle active/inactive status of a service.
     */
    public function toggleServiceStatus(Service $service, User $actor): Service
    {
        if (!$this->canManageServices($actor)) {
            throw new AuthorizationException("You are not authorized to toggle service status.");
        }

        $oldStatus = $service->is_active;
        $service->is_active = !$oldStatus;
        $service->save();

        AuditLog::create([
            'user_id' => $actor->id,
            'event' => 'service.status_toggled',
            'auditable_type' => Service::class,
            'auditable_id' => $service->id,
            'old_values' => ['is_active' => $oldStatus],
            'new_values' => ['is_active' => $service->is_active],
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'created_at' => Carbon::now(),
        ]);

        return $service;
    }

    /**
     * Get paginated bills with filtering.
     */
    public function getBills(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Bill::with(['patient', 'items.service', 'createdBy', 'discountAuthorizedBy'])
            ->latest('bill_date');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['patient_id'])) {
            $query->where('patient_id', (int) $filters['patient_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('bill_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('bill_date', '<=', $filters['date_to']);
        }

        return $query->paginate($perPage)->withQueryString();
    }
}

