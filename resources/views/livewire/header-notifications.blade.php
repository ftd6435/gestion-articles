@php
    $total = (int) $lowStockCount + (int) $unpaidSalesCount + (int) $unpaidReceptionsCount;
@endphp

<div class="dropdown">
    <button class="btn btn-link position-relative p-2 me-2 me-md-3"
            type="button"
            data-bs-toggle="dropdown"
            aria-expanded="false">
        <i class="fas fa-bell fs-5"></i>
        @if($total > 0)
            <span class="notification-badge">{{ $total }}</span>
        @endif
    </button>

    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width: 320px;">
        <li class="px-3 pt-2 pb-1">
            <div class="fw-semibold">Alertes</div>
            <div class="small text-muted">Aperçu rapide</div>
        </li>
        <li><hr class="dropdown-divider"></li>

        <li>
            <a class="dropdown-item d-flex align-items-center justify-content-between"
               href="{{ route('articles', ['filterStockLevel' => 'low']) }}">
                <span><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Stock faible</span>
                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">{{ $lowStockCount }}</span>
            </a>
        </li>

        <li>
            <a class="dropdown-item d-flex align-items-center justify-content-between"
               href="{{ route('ventes.ventes', ['status' => 'IMPAYEE']) }}">
                <span><i class="fas fa-file-invoice-dollar me-2 text-danger"></i>Ventes impayées</span>
                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">{{ $unpaidSalesCount }}</span>
            </a>
        </li>

        <li>
            <a class="dropdown-item d-flex align-items-center justify-content-between"
               href="{{ route('stock.approvisions', ['paymentStatusFilter' => 'NON_PAYE']) }}">
                <span><i class="fas fa-truck-loading me-2 text-info"></i>Réceptions impayées</span>
                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">{{ $unpaidReceptionsCount }}</span>
            </a>
        </li>

        <li><hr class="dropdown-divider"></li>
        <li class="px-3 pb-2">
            <button type="button" class="btn btn-sm btn-outline-secondary w-100" wire:click="refreshCounts">
                Actualiser
            </button>
        </li>
    </ul>
</div>
