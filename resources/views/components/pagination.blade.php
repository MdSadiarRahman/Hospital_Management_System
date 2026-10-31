@props([
    'paginator' => null,
    'total' => null,
    'from' => 1,
    'to' => 10,
])

@if($paginator instanceof \Illuminate\Pagination\LengthAwarePaginator)
    <div class="hms-pagination">
        <div class="text-muted-hms">
            Showing <span class="fw-semibold text-main">{{ $paginator->firstItem() ?? 0 }}</span>
            to <span class="fw-semibold text-main">{{ $paginator->lastItem() ?? 0 }}</span>
            of <span class="fw-semibold text-main">{{ $paginator->total() }}</span> results
        </div>
        <div>
            {{ $paginator->links('pagination::bootstrap-5') }}
        </div>
    </div>
@else
    <div class="hms-pagination">
        <div class="text-muted-hms">
            Showing <span class="fw-semibold text-main">{{ $from }}</span>
            to <span class="fw-semibold text-main">{{ $to }}</span>
            of <span class="fw-semibold text-main">{{ $total ?? 45 }}</span> results
        </div>
        <nav aria-label="Page navigation">
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item disabled">
                    <span class="page-link"><i class="bi bi-chevron-left" aria-hidden="true"></i></span>
                </li>
                <li class="page-item active"><span class="page-link">1</span></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item">
                    <a class="page-link" href="#"><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
                </li>
            </ul>
        </nav>
    </div>
@endif
