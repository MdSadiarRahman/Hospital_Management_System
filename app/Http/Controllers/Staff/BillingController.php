<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\ApplyDiscountRequest;
use App\Http\Requests\Billing\StoreBillRequest;
use App\Models\Admission;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Patient;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Visit;
use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function __construct(
        protected BillingService $billingService
    ) {}

    /**
     * Display the Billing & Invoices workbench.
     */
    public function index(Request $request): View
    {
        $filters = [
            'status' => $request->query('status'),
            'search' => $request->query('search'),
            'patient_id' => $request->query('patient_id'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
        ];

        $bills = $this->billingService->getBills($filters);

        // Aggregate statistics for dashboard summary cards
        $stats = [
            'total_invoices' => Bill::count(),
            'unpaid_count' => Bill::where('status', 'unpaid')->count(),
            'partial_count' => Bill::where('status', 'partial')->count(),
            'paid_count' => Bill::where('status', 'paid')->count(),
            'total_billed' => Bill::sum('total_amount'),
            'total_collected' => Bill::sum('paid_amount'),
            'total_due' => Bill::sum('due_amount'),
        ];

        return view('portal.billing.bills.index', compact('bills', 'stats', 'filters'));
    }

    /**
     * Show form to generate a new invoice.
     */
    public function create(Request $request): View
    {
        $selectedPatient = null;
        if ($request->filled('patient_id')) {
            $selectedPatient = Patient::find($request->patient_id);
        }

        $selectedAdmission = null;
        if ($request->filled('admission_id')) {
            $selectedAdmission = Admission::with('patient', 'currentBedAssignment.bed')->find($request->admission_id);
            if ($selectedAdmission && !$selectedPatient) {
                $selectedPatient = $selectedAdmission->patient;
            }
        }

        $selectedVisit = null;
        if ($request->filled('visit_id')) {
            $selectedVisit = Visit::with('patient')->find($request->visit_id);
            if ($selectedVisit && !$selectedPatient) {
                $selectedPatient = $selectedVisit->patient;
            }
        }

        $categories = ServiceCategory::with(['services' => function ($q) {
            $q->where('is_active', true)->orderBy('name');
        }])->where('is_active', true)->orderBy('name')->get();

        $canDiscount = $this->billingService->canAuthorizeDiscount($request->user());

        return view('portal.billing.bills.create', compact(
            'selectedPatient',
            'selectedAdmission',
            'selectedVisit',
            'categories',
            'canDiscount'
        ));
    }

    /**
     * Store a new bill with multiple line items.
     */
    public function store(StoreBillRequest $request): RedirectResponse
    {
        $bill = $this->billingService->createBill($request->validated(), $request->user());

        return redirect()->route('portal.billing.show', $bill)
            ->with('success', "Invoice #{$bill->invoice_number} generated successfully.");
    }

    /**
     * Show invoice dossier with line items, concession and payment options.
     */
    public function show(Bill $bill, Request $request): View
    {
        $bill->load([
            'patient',
            'admission.currentBedAssignment.bed',
            'visit',
            'items.service.category',
            'payments.receivedBy',
            'createdBy',
            'discountAuthorizedBy',
            'auditLogs.user',
        ]);

        $categories = ServiceCategory::with(['services' => function ($q) {
            $q->where('is_active', true)->orderBy('name');
        }])->where('is_active', true)->orderBy('name')->get();

        $canDiscount = $this->billingService->canAuthorizeDiscount($request->user());
        $canManage = $this->billingService->canManageServices($request->user());

        return view('portal.billing.bills.show', compact('bill', 'categories', 'canDiscount', 'canManage'));
    }

    /**
     * Add a line item to an existing invoice.
     */
    public function addItem(Request $request, Bill $bill): RedirectResponse
    {
        $validated = $request->validate([
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'item_type' => ['nullable', 'string', 'in:bed,consultation,lab,surgery,service'],
            'item_name' => ['required_without:service_id', 'nullable', 'string', 'max:191'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $this->billingService->addBillItem($bill, $validated, $request->user());

        return redirect()->route('portal.billing.show', $bill)
            ->with('success', 'Line item successfully added to invoice.');
    }

    /**
     * Remove a line item from an invoice.
     */
    public function removeItem(Bill $bill, BillItem $item, Request $request): RedirectResponse
    {
        $this->billingService->removeBillItem($bill, $item, $request->user());

        return redirect()->route('portal.billing.show', $bill)
            ->with('success', 'Item removed and invoice recalculation updated.');
    }

    /**
     * Apply or update authorized concession/discount.
     */
    public function applyDiscount(ApplyDiscountRequest $request, Bill $bill): RedirectResponse
    {
        $this->billingService->applyDiscount(
            $bill,
            $request->discount_amount,
            $request->discount_reason,
            $request->user()
        );

        return redirect()->route('portal.billing.show', $bill)
            ->with('success', "Discount of BDT " . number_format($request->discount_amount, 2) . " authorized and applied.");
    }

    /**
     * Print official hospital money receipt / patient invoice.
     */
    public function printInvoice(Bill $bill): View
    {
        $bill->load([
            'patient',
            'admission.currentBedAssignment.bed',
            'visit',
            'items.service.category',
            'payments.receivedBy',
            'createdBy',
            'discountAuthorizedBy',
        ]);

        return view('portal.billing.bills.print', compact('bill'));
    }
}

