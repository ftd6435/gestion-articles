@php
    $total =
        (int) $outOfStockCount +
        (int) $lowStockCount +
        (int) $unpaidSalesCount +
        (int) $partialSalesCount +
        (int) $unpaidReceptionsCount +
        (int) $partialReceptionsCount;
@endphp

<div class="dropdown">
    <button class="btn btn-link position-relative p-2 me-2 me-md-3" type="button" data-bs-toggle="dropdown"
        aria-expanded="false">
        <i class="fas fa-bell fs-5"></i>
        @if ($total > 0)
            <span class="notification-badge">{{ $total > 99 ? '99+' : $total }}</span>
        @endif
    </button>

    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width: 340px;">
        <li class="px-3 pt-2 pb-1">
            <div class="fw-semibold">Alertes</div>
            <div class="small text-muted">Aperçu rapide</div>
        </li>
        <li>
            <hr class="dropdown-divider my-1">
        </li>

        {{-- Stock --}}
        <li class="px-3 py-1">
            <div class="small text-muted fw-semibold text-uppercase" style="font-size: 0.7rem; letter-spacing: .05em;">
                <i class="fas fa-warehouse me-1"></i> Stock
            </div>
        </li>
        <li>
            <a class="dropdown-item d-flex align-items-center justify-content-between py-2"
                href="{{ route('articles', ['filterStockLevel' => 'out']) }}">
                <span><i class="fas fa-times-circle me-2 text-danger"></i>Rupture de stock</span>
                <span
                    class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">{{ $outOfStockCount }}</span>
            </a>
        </li>
        <li>
            <a class="dropdown-item d-flex align-items-center justify-content-between py-2"
                href="{{ route('articles', ['filterStockLevel' => 'low']) }}">
                <span><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Stock faible</span>
                <span
                    class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">{{ $lowStockCount }}</span>
            </a>
        </li>

        <li>
            <hr class="dropdown-divider my-1">
        </li>

        {{-- Sales --}}
        <li class="px-3 py-1">
            <div class="small text-muted fw-semibold text-uppercase" style="font-size: 0.7rem; letter-spacing: .05em;">
                <i class="fas fa-cash-register me-1"></i> Ventes clients
            </div>
        </li>
        <li>
            <a class="dropdown-item d-flex align-items-center justify-content-between py-2"
                href="{{ route('ventes.ventes', ['status' => 'IMPAYEE']) }}">
                <span><i class="fas fa-file-invoice-dollar me-2 text-danger"></i>Impayées</span>
                <span
                    class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">{{ $unpaidSalesCount }}</span>
            </a>
        </li>
        <li>
            <a class="dropdown-item d-flex align-items-center justify-content-between py-2"
                href="{{ route('ventes.ventes', ['status' => 'PARTIELLE']) }}">
                <span><i class="fas fa-clock me-2 text-warning"></i>Partiellement payées</span>
                <span
                    class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">{{ $partialSalesCount }}</span>
            </a>
        </li>

        <li>
            <hr class="dropdown-divider my-1">
        </li>

        {{-- Receptions --}}
        <li class="px-3 py-1">
            <div class="small text-muted fw-semibold text-uppercase" style="font-size: 0.7rem; letter-spacing: .05em;">
                <i class="fas fa-truck-loading me-1"></i> Réceptions fournisseurs
            </div>
        </li>
        <li>
            <a class="dropdown-item d-flex align-items-center justify-content-between py-2"
                href="{{ route('stock.approvisions', ['paymentStatusFilter' => 'NON_PAYE']) }}">
                <span><i class="fas fa-file-invoice me-2 text-danger"></i>Impayées</span>
                <span
                    class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">{{ $unpaidReceptionsCount }}</span>
            </a>
        </li>
        <li>
            <a class="dropdown-item d-flex align-items-center justify-content-between py-2"
                href="{{ route('stock.approvisions', ['paymentStatusFilter' => 'PARTIEL']) }}">
                <span><i class="fas fa-clock me-2 text-warning"></i>Partiellement payées</span>
                <span
                    class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">{{ $partialReceptionsCount }}</span>
            </a>
        </li>

        <li>
            <hr class="dropdown-divider my-1">
        </li>
        <li class="px-3 pb-2 pt-1">
            <button type="button" class="btn btn-sm btn-outline-secondary w-100" wire:click="refreshCounts">
                <i class="fas fa-sync-alt me-1"></i> Actualiser
            </button>
        </li>
    </ul>
</div>
