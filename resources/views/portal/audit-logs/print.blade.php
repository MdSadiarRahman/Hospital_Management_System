<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Security Audit Ledger — Meghna Clinic & Nursing Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; color: #263445; font-size: 11pt; }
        .clinic-brand { color: #123B5D; }
        .text-teal { color: #087F8C; }
        .table-sm th, .table-sm td { padding: 0.4rem 0.5rem; font-size: 9pt; }
        @media print {
            .no-print { display: none !important; }
            body { font-size: 10pt; }
            @page { margin: 1.2cm; size: A4 landscape; }
        }
    </style>
</head>
<body class="bg-white p-4">

    {{-- Print Controls --}}
    <div class="no-print d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <a href="{{ route('portal.audit-logs.index') }}" class="btn btn-outline-secondary btn-sm">&larr; Back to Portal</a>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-sm">Print Ledger</button>
        </div>
    </div>

    {{-- Official Hospital Letterhead --}}
    <div class="text-center pb-3 mb-3 border-bottom">
        <h2 class="fw-bold clinic-brand mb-0" style="letter-spacing: 0.5px;">MEGHNA CLINIC &amp; NURSING HOME</h2>
        <div class="text-muted small">Shohid Minar Mor, Dinajpur, Bangladesh &middot; Emergency Hotline: 01712-345678</div>
        <div class="fw-bold text-uppercase mt-2 text-teal" style="font-size: 11pt; letter-spacing: 1px;">
            INFORMATION SECURITY &amp; COMPLIANCE AUDIT TRAIL
        </div>
        <div class="text-muted small">Generated on {{ now()->format('d M Y, h:i A') }}</div>
    </div>

    {{-- Parameters Summary --}}
    <div class="bg-light p-2 rounded mb-3 small">
        <div class="row g-2">
            <div class="col-3"><strong>Event Filter:</strong> {{ $filters['event'] ?: 'All Events' }}</div>
            <div class="col-3"><strong>User Filter:</strong> {{ $filters['user_id'] ? 'User #' . $filters['user_id'] : 'All Users' }}</div>
            <div class="col-3"><strong>Target Entity:</strong> {{ $filters['auditable_type'] ?: 'All Models' }}</div>
            <div class="col-3"><strong>Total Records:</strong> {{ $logs->count() }} Entries</div>
        </div>
    </div>

    {{-- Audit Log Table --}}
    <table class="table table-bordered table-sm align-middle font-monospace mb-4">
        <thead class="table-light text-uppercase">
            <tr>
                <th style="width: 130px;">Timestamp</th>
                <th>Event Action</th>
                <th>Responsible User</th>
                <th>Affected Model</th>
                <th>Client IP</th>
                <th>Payload Summary</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                    <td class="fw-bold">{{ $log->event }}</td>
                    <td>{{ $log->user ? $log->user->name . " (#{$log->user->id})" : 'System' }}</td>
                    <td>{{ $log->formatted_auditable_name }}</td>
                    <td>{{ $log->ip_address ?? '127.0.0.1' }}</td>
                    <td style="font-size: 8pt; max-width: 300px; word-break: break-all;">
                        @if(!empty($log->new_values))
                            {{ json_encode($log->new_values, JSON_UNESCAPED_SLASHES) }}
                        @elseif(!empty($log->old_values))
                            Prior: {{ json_encode($log->old_values, JSON_UNESCAPED_SLASHES) }}
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">No audit logs found for the selected scope.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Official Signatures --}}
    <div class="row pt-5 mt-4 text-center">
        <div class="col-4">
            <div class="border-top pt-2 small fw-semibold">Data Protection Officer</div>
        </div>
        <div class="col-4">
            <div class="border-top pt-2 small fw-semibold">IT Systems Administrator</div>
        </div>
        <div class="col-4">
            <div class="border-top pt-2 small fw-semibold">Medical Superintendent</div>
        </div>
    </div>

</body>
</html>

