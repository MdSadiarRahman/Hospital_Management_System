<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lab\StoreLabTestCatalogRequest;
use App\Http\Requests\Lab\UpdateLabTestCatalogRequest;
use App\Models\LabTestCatalog;
use App\Services\LabCatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LabTestCatalogController extends Controller
{
    public function __construct(
        protected LabCatalogService $labService
    ) {}

    /**
     * Display the searchable and filterable laboratory test catalog.
     */
    public function index(Request $request): View|JsonResponse
    {
        $user = $request->user();
        if (!$user->hasPermission('lab.view') && !$user->hasRole(['super_admin', 'administrator', 'lab_staff', 'doctor'])) {
            abort(403, 'Unauthorized access to laboratory test catalog.');
        }

        $filters = $request->only(['q', 'category', 'source', 'status']);
        $tests = $this->labService->getCatalog($filters, 15);
        $statistics = $this->labService->getStatistics();
        $categories = $this->labService->getCategories();
        $partners = $this->labService->getActivePartners();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $tests,
                'stats' => $statistics,
            ]);
        }

        return view('portal.lab.catalog.index', [
            'tests' => $tests,
            'statistics' => $statistics,
            'categories' => $categories,
            'partners' => $partners,
            'filters' => $filters,
        ]);
    }

    /**
     * Show form to configure a new diagnostic test.
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        if (!$user->hasPermission('lab.create') && !$user->hasRole(['super_admin', 'administrator', 'lab_staff'])) {
            abort(403, 'Unauthorized. Only laboratory staff and hospital administrators can configure new tests.');
        }

        return view('portal.lab.catalog.create', [
            'categories' => $this->labService->getCategories(),
            'sampleTypes' => $this->labService->getSampleTypes(),
            'partners' => $this->labService->getActivePartners(),
        ]);
    }

    /**
     * Store a newly created diagnostic test in the catalog.
     */
    public function store(StoreLabTestCatalogRequest $request): RedirectResponse|JsonResponse
    {
        $test = $this->labService->createTest($request->validated(), $request->user());

        $message = "Diagnostic test '{$test->name}' [{$test->code}] added to catalog at {$test->formatted_price}.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $test,
            ], 201);
        }

        return redirect()->route('portal.lab-catalog.index')->with('success', $message);
    }

    /**
     * Display detailed specifications of a diagnostic test.
     */
    public function show(Request $request, LabTestCatalog $labTest): View|JsonResponse
    {
        $user = $request->user();
        if (!$user->hasPermission('lab.view') && !$user->hasRole(['super_admin', 'administrator', 'lab_staff', 'doctor'])) {
            abort(403, 'Unauthorized access to diagnostic test details.');
        }

        $labTest->loadMissing(['labPartner', 'orderItems.labOrder.patient']);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $labTest,
            ]);
        }

        return view('portal.lab.catalog.show', [
            'test' => $labTest,
        ]);
    }

    /**
     * Show form to edit an existing diagnostic test configuration.
     */
    public function edit(Request $request, LabTestCatalog $labTest): View
    {
        $user = $request->user();
        if (!$user->hasPermission('lab.update') && !$user->hasRole(['super_admin', 'administrator', 'lab_staff'])) {
            abort(403, 'Unauthorized to edit diagnostic test configurations.');
        }

        return view('portal.lab.catalog.edit', [
            'test' => $labTest,
            'categories' => $this->labService->getCategories(),
            'sampleTypes' => $this->labService->getSampleTypes(),
            'partners' => $this->labService->getActivePartners(),
        ]);
    }

    /**
     * Update an existing diagnostic test configuration.
     */
    public function update(UpdateLabTestCatalogRequest $request, LabTestCatalog $labTest): RedirectResponse|JsonResponse
    {
        $updatedTest = $this->labService->updateTest($labTest, $request->validated(), $request->user());

        $message = "Diagnostic test '{$updatedTest->name}' [{$updatedTest->code}] updated successfully.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $updatedTest,
            ]);
        }

        return redirect()->route('portal.lab-catalog.show', $updatedTest->id)->with('success', $message);
    }

    /**
     * Toggle the active / inactive state of a test in the catalog.
     */
    public function toggleStatus(Request $request, LabTestCatalog $labTest): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        if (!$user->hasPermission('lab.update') && !$user->hasRole(['super_admin', 'administrator', 'lab_staff'])) {
            abort(403, 'Unauthorized to change test status.');
        }

        $updatedTest = $this->labService->toggleStatus($labTest, $user);
        $stateText = $updatedTest->is_active ? 'activated' : 'deactivated';
        $message = "Test '{$updatedTest->name}' [{$updatedTest->code}] successfully {$stateText}.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'is_active' => $updatedTest->is_active,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * API auto-complete search for doctors and receptionist order slips.
     */
    public function apiSearch(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->hasPermission('lab.view') && !$user->hasRole(['super_admin', 'administrator', 'lab_staff', 'doctor', 'receptionist'])) {
            return response()->json(['results' => []], 403);
        }

        $term = trim($request->input('q', ''));
        if (strlen($term) < 2) {
            return response()->json(['results' => []]);
        }

        $results = LabTestCatalog::active()
            ->search($term)
            ->take(15)
            ->get(['id', 'name', 'code', 'category', 'standard_charge', 'sample_type', 'default_source']);

        return response()->json([
            'results' => $results->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'code' => $t->code,
                'category' => $t->category,
                'price' => $t->standard_charge,
                'formatted_price' => $t->formatted_price,
                'sample_type' => $t->sample_type,
                'source' => $t->default_source,
                'source_label' => $t->availability_label,
            ]),
        ]);
    }
}

