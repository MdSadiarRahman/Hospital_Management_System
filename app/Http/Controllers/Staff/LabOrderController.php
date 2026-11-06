<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lab\CollectSampleRequest;
use App\Http\Requests\Lab\StoreLabOrderRequest;
use App\Http\Requests\Lab\UpdateOrderItemStatusRequest;
use App\Http\Requests\Lab\UpdateOrderStatusRequest;
use App\Models\Consultation;
use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\LabPartner;
use App\Models\LabTestCatalog;
use App\Models\Patient;
use App\Services\LabOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LabOrderController extends Controller
{
    public function __construct(
        protected LabOrderService $labOrderService
    ) {}

    /**
     * Display the laboratory orders workbench and status queue.
     */
    public function index(Request $request): View|JsonResponse
    {
        $user = $request->user();
        if (!$user->hasPermission('lab.view') && !$user->hasRole(['super_admin', 'administrator', 'lab_staff', 'doctor', 'nurse'])) {
            abort(403, 'Unauthorized access to laboratory orders.');
        }

        $filters = $request->only(['q', 'status', 'source', 'priority', 'patient_id', 'date_from', 'date_to']);
        $orders = $this->labOrderService->getOrdersList($filters, 15);
        $statistics = $this->labOrderService->getOrderStatistics();
        $partners = LabPartner::where('is_active', true)->orderBy('name')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $orders,
                'stats' => $statistics,
            ]);
        }

        return view('portal.lab.orders.index', [
            'orders' => $orders,
            'statistics' => $statistics,
            'partners' => $partners,
            'filters' => $filters,
        ]);
    }

    /**
     * Show the doctor requisition form for laboratory tests.
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        if (!$user->hasPermission('lab.create') && !$user->hasRole(['super_admin', 'administrator', 'doctor', 'lab_staff'])) {
            abort(403, 'Unauthorized to author diagnostic test orders.');
        }

        $patientId = $request->input('patient_id');
        $selectedPatient = $patientId ? Patient::find($patientId) : null;
        $consultations = $selectedPatient
            ? Consultation::whereHas('visit', fn($q) => $q->where('patient_id', $selectedPatient->id))
                ->with('doctor.staffProfile')
                ->latest()
                ->take(10)
                ->get()
            : collect();

        $activePatients = Patient::latest()->take(30)->get();
        $catalogTests = LabTestCatalog::active()->orderBy('category')->orderBy('name')->get();
        $partners = LabPartner::where('is_active', true)->orderBy('name')->get();

        return view('portal.lab.orders.create', [
            'selectedPatient' => $selectedPatient,
            'consultations' => $consultations,
            'activePatients' => $activePatients,
            'catalogTests' => $catalogTests,
            'partners' => $partners,
            'preselectedConsultationId' => $request->input('consultation_id'),
        ]);
    }

    /**
     * Store a newly created doctor lab order with multiple tests.
     */
    public function store(StoreLabOrderRequest $request): RedirectResponse|JsonResponse
    {
        $order = $this->labOrderService->createOrder($request->validated(), $request->user());

        $message = "Diagnostic order #{$order->order_number} created with {$order->items->count()} tests for {$order->patient?->name}.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $order,
            ], 201);
        }

        return redirect()->route('portal.lab-orders.show', $order->id)->with('success', $message);
    }

    /**
     * Display complete laboratory order dossier, specimen tracking, and status timeline.
     */
    public function show(Request $request, LabOrder $labOrder): View|JsonResponse
    {
        $user = $request->user();
        if (!$user->hasPermission('lab.view') && !$user->hasRole(['super_admin', 'administrator', 'lab_staff', 'doctor', 'nurse'])) {
            abort(403, 'Unauthorized access to laboratory order dossier.');
        }

        $labOrder->loadMissing([
            'patient',
            'doctor.staffProfile',
            'consultation.diagnoses',
            'labPartner',
            'items.labTest',
            'items.sample',
            'samples.collectedBy',
            'statusHistories.changedBy',
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $labOrder,
            ]);
        }

        $partners = LabPartner::where('is_active', true)->orderBy('name')->get();

        return view('portal.lab.orders.show', [
            'order' => $labOrder,
            'partners' => $partners,
        ]);
    }

    /**
     * Record phlebotomy specimen collection and barcode generation.
     */
    public function collectSample(CollectSampleRequest $request, LabOrder $labOrder): RedirectResponse|JsonResponse
    {
        $sample = $this->labOrderService->collectSample($labOrder, $request->validated(), $request->user());

        $message = "Specimen collected successfully for order #{$labOrder->order_number} [Barcode: {$sample->barcode}].";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $sample,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Update order-level status (pending -> sample_collected -> processing -> completed / cancelled).
     */
    public function updateStatus(UpdateOrderStatusRequest $request, LabOrder $labOrder): RedirectResponse|JsonResponse
    {
        $updatedOrder = $this->labOrderService->updateOrderStatus(
            $labOrder,
            $request->input('status'),
            $request->user(),
            $request->input('notes')
        );

        $message = "Order #{$updatedOrder->order_number} status updated to '{$updatedOrder->status_label}'.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $updatedOrder->status,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Track and update an individual test separately within the order.
     */
    public function updateItemStatus(UpdateOrderItemStatusRequest $request, LabOrderItem $labOrderItem): RedirectResponse|JsonResponse
    {
        $updatedItem = $this->labOrderService->updateItemStatus(
            $labOrderItem,
            $request->input('status'),
            $request->user(),
            $request->input('notes')
        );

        $message = "Test '{$updatedItem->test_name}' status updated to '{$updatedItem->status_label}'.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'item_status' => $updatedItem->status,
                'order_status' => $updatedItem->labOrder?->status,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Assign or transfer order to external partner reference laboratory.
     */
    public function assignPartner(Request $request, LabOrder $labOrder): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        if (!$user->hasPermission('lab.update') && !$user->hasRole(['super_admin', 'administrator', 'lab_staff'])) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'lab_partner_id' => ['required', 'integer', 'exists:lab_partners,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $updatedOrder = $this->labOrderService->assignPartner(
            $labOrder,
            (int) $validated['lab_partner_id'],
            $user,
            $validated['notes'] ?? null
        );

        $message = "Order #{$updatedOrder->order_number} assigned to external reference lab: {$updatedOrder->labPartner?->name}.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $updatedOrder,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Printable laboratory test requisition slip for the patient.
     */
    public function printSlip(Request $request, LabOrder $labOrder): View
    {
        $user = $request->user();
        if (!$user->hasPermission('lab.view') && !$user->hasRole(['super_admin', 'administrator', 'lab_staff', 'doctor', 'nurse', 'receptionist'])) {
            abort(403, 'Unauthorized.');
        }

        $labOrder->loadMissing([
            'patient',
            'doctor.staffProfile',
            'consultation.diagnoses',
            'labPartner',
            'items.labTest',
            'samples',
        ]);

        return view('portal.lab.orders.print_requisition', [
            'order' => $labOrder,
        ]);
    }
}

