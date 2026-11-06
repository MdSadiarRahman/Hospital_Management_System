<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\StoreServiceRequest;
use App\Http\Requests\Billing\UpdateServiceRequest;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceCatalogController extends Controller
{
    public function __construct(
        protected BillingService $billingService
    ) {}

    /**
     * Display the hospital service catalog and tariff master.
     */
    public function index(Request $request): View
    {
        $categories = ServiceCategory::orderBy('name')->get();

        $query = Service::with('category')->latest('id');

        if ($request->filled('category_id')) {
            $query->where('category_id', (int) $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $services = $query->paginate(20)->withQueryString();

        $stats = [
            'total_services' => Service::count(),
            'active_services' => Service::where('is_active', true)->count(),
            'inactive_services' => Service::where('is_active', false)->count(),
            'total_categories' => $categories->count(),
        ];

        $canManage = $this->billingService->canManageServices($request->user());

        return view('portal.billing.services.index', compact('services', 'categories', 'stats', 'canManage'));
    }

    /**
     * Store a newly created service in catalog.
     */
    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $service = $this->billingService->createService($request->validated(), $request->user());

        return redirect()->route('portal.services.index')
            ->with('success', "Service '{$service->name}' (Code: {$service->code}) registered in catalog.");
    }

    /**
     * Update an existing service and tariff price.
     */
    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        $this->billingService->updateService($service, $request->validated(), $request->user());

        return redirect()->route('portal.services.index')
            ->with('success', "Service '{$service->name}' updated. Existing historical bills remain preserved.");
    }

    /**
     * Toggle active/inactive status of a service.
     */
    public function toggle(Service $service, Request $request): RedirectResponse
    {
        $this->billingService->toggleServiceStatus($service, $request->user());

        $statusStr = $service->fresh()->is_active ? 'activated' : 'deactivated';
        return redirect()->route('portal.services.index')
            ->with('success', "Service '{$service->name}' {$statusStr}.");
    }
}

