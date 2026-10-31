@props([
    'id',
    'title' => 'Confirm Action',
    'message' => 'Are you sure you want to proceed with this action? This operation cannot be undone.',
    'confirmText' => 'Confirm',
    'confirmVariant' => 'danger',
    'icon' => 'exclamation-triangle',
    'action' => null,
    'method' => 'POST',
])

<div
    class="modal fade"
    id="{{ $id }}"
    tabindex="-1"
    aria-labelledby="{{ $id }}Label"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content hms-modal">
            <div class="modal-body text-center p-4">
                <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded-circle"
                     style="width: 56px; height: 56px; background-color: {{ $confirmVariant === 'danger' ? 'var(--hms-danger-bg)' : 'var(--hms-teal-light)' }}; color: {{ $confirmVariant === 'danger' ? 'var(--hms-danger)' : 'var(--hms-teal)' }}; font-size: 1.5rem;">
                    <i class="bi bi-{{ $icon }}"></i>
                </div>

                <h5 class="modal-title hms-modal-title mb-2" id="{{ $id }}Label">{{ $title }}</h5>
                <p class="text-muted-hms mb-4" style="font-size: 0.85rem; line-height: 1.5;">
                    {{ $message }}
                </p>

                @if($action)
                    <form method="POST" action="{{ $action }}">
                        @csrf
                        @if(!in_array(strtoupper($method), ['GET', 'POST']))
                            @method($method)
                        @endif

                        <div class="d-flex gap-2 justify-content-center">
                            <button type="button" class="btn btn-hms-ghost btn-hms-sm" data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="submit" class="btn {{ $confirmVariant === 'danger' ? 'btn-danger' : 'btn-hms-teal' }} btn-hms-sm">
                                {{ $confirmText }}
                            </button>
                        </div>
                    </form>
                @else
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-hms-ghost btn-hms-sm" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="button" class="btn {{ $confirmVariant === 'danger' ? 'btn-danger' : 'btn-hms-teal' }} btn-hms-sm" data-bs-dismiss="modal">
                            {{ $confirmText }}
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
