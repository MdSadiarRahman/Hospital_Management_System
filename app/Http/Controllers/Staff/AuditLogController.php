<?php

namespace App\Services;
namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    /**
     * Display the Security Audit Ledger.
     */
    public function index(Request $request): View
    {
        $filters = [
            'event' => $request->query('event'),
            'user_id' => $request->query('user_id'),
            'auditable_type' => $request->query('auditable_type'),
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
            'search' => $request->query('search'),
        ];

        $logs = $this->auditService->getPaginatedLogs($filters, 25);
        $events = $this->auditService->getDistinctEvents();
        $auditableTypes = $this->auditService->getDistinctAuditableTypes();
        $staffUsers = User::where('user_type', 'staff')->orderBy('name')->get();

        return view('portal.audit-logs.index', compact('logs', 'filters', 'events', 'auditableTypes', 'staffUsers'));
    }

    /**
     * Display details of a specific audit log entry.
     */
    public function show(AuditLog $auditLog): View
    {
        $auditLog->load('user');

        return view('portal.audit-logs.show', compact('auditLog'));
    }

    /**
     * Printable version of the security audit ledger.
     */
    public function print(Request $request): View
    {
        $filters = [
            'event' => $request->query('event'),
            'user_id' => $request->query('user_id'),
            'auditable_type' => $request->query('auditable_type'),
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
            'search' => $request->query('search'),
        ];

        $logs = AuditLog::with('user')
            ->filter($filters)
            ->latest('created_at')
            ->limit(100)
            ->get();

        return view('portal.audit-logs.print', compact('logs', 'filters'));
    }
}

