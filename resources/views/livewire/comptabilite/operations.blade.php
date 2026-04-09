<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div class="mb-3 mb-md-0">
            <h1 class="h3 fw-bold mb-1">Opérations</h1>
            <p class="text-muted mb-0">Suivi des dépenses et revenus divers</p>
        </div>
        <div class="d-flex gap-2 no-print">
            <button wire:click="create" class="btn btn-primary">
                <i class="fa fa-plus me-2"></i> Nouvelle opération
            </button>
            <button type="button" class="btn btn-outline-secondary" onclick="printOperations()">
                <i class="fa fa-print me-2"></i> Imprimer
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Total entrées</div>
                            <div class="h4 fw-bold text-success mb-0">{{ number_format($totalEntrees, 0, ',', ' ') }}
                                <span class="text-muted fw-semibold small">
                                    {{ $currentDevise ? $currentDevise->symbole ?? $currentDevise->code : '' }}
                                </span>
                            </div>
                        </div>
                        <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center"
                            style="width: 44px; height: 44px;">
                            <i class="fas fa-arrow-down text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Total sorties</div>
                            <div class="h4 fw-bold text-danger mb-0">{{ number_format($totalSorties, 0, ',', ' ') }}
                                <span class="text-muted fw-semibold small">
                                    {{ $currentDevise ? $currentDevise->symbole ?? $currentDevise->code : '' }}
                                </span>
                            </div>
                        </div>
                        <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center"
                            style="width: 44px; height: 44px;">
                            <i class="fas fa-arrow-up text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="no-print">
                @include('components.shared.alerts')
            </div>

            <div class="row g-2 align-items-end mb-3 no-print">
                <div class="col-12 col-md-5">
                    <label class="form-label small text-muted">Recherche</label>
                    <input type="text" class="form-control" wire:model.live.debounce.300ms="search"
                        placeholder="Motif...">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small text-muted">Type</label>
                    <select class="form-select" wire:model.live="typeFilter">
                        <option value="">Tous</option>
                        @foreach ($types as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}
                                ({{ (int) $type->nature === 1 ? 'Entrée' : 'Sortie' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small text-muted">Devise</label>
                    <select class="form-select" wire:model.live="deviseFilter">
                        @foreach ($devises as $devise)
                            <option value="{{ $devise->id }}">
                                {{ $devise->code }} ({{ $devise->symbole ?? $devise->code }})
                                @if ($devise->is_default)
                                    - Par défaut
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-end mb-2 no-print">
                <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="toggleDateFilters">
                    {{ $showDateFilters ? 'Masquer filtre dates' : 'Filtrer par dates' }}
                </button>
            </div>

            @if ($showDateFilters)
                <div class="row g-2 align-items-end mb-3 no-print">
                    <div class="col-12 col-md-5">
                        <label class="form-label small text-muted">Du</label>
                        <input type="date" class="form-control" wire:model.live="dateFrom">
                    </div>
                    <div class="col-12 col-md-5">
                        <label class="form-label small text-muted">Au</label>
                        <input type="date" class="form-control" wire:model.live="dateTo">
                    </div>
                    <div class="col-12 col-md-2 d-grid">
                        <button type="button" class="btn btn-outline-secondary" wire:click="clearFilters">
                            Réinitialiser
                        </button>
                    </div>
                </div>
            @endif

            <div id="operationsPrintArea">
                <div class="operations-print-header d-none">
                    @php
                        $company = \App\Models\CompanySetting::query()->first();
                        $companyName = $company?->name ?: config('app.name');
                        $companyTel = $company?->telephone;
                        $companyEmail = $company?->email;
                        $companyLogoUrl = $company?->logo_path ? asset($company->logo_path) : null;
                        $currencyLabel = $currentDevise ? $currentDevise->symbole ?? $currentDevise->code : '';
                    @endphp
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div class="d-flex align-items-center gap-3">
                            @if ($companyLogoUrl)
                                <img src="{{ $companyLogoUrl }}" alt="Logo"
                                    style="max-height: 60px; max-width: 160px;">
                            @endif
                            <div>
                                <div class="fw-bold fs-5">{{ $companyName }}</div>
                                <div class="small text-muted">
                                    @if ($companyTel)
                                        <span>{{ $companyTel }}</span>
                                    @endif
                                    @if ($companyTel && $companyEmail)
                                        <span class="mx-2">|</span>
                                    @endif
                                    @if ($companyEmail)
                                        <span>{{ $companyEmail }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="small text-muted mb-3">
                            @if ($typeFilter)
                                <span class="me-3">Type:
                                    {{ $types->firstWhere('id', (int) $typeFilter)?->name ?? '—' }}</span>
                            @endif
                            @if ($search)
                                <span class="me-3">Recherche: {{ $search }}</span>
                            @endif
                            @if ($showDateFilters && ($dateFrom || $dateTo))
                                <span class="me-3">
                                    Période:
                                    {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : '—' }}
                                    au
                                    {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d/m/Y') : '—' }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <hr class="mt-0 mb-3">
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle operations-table">
                        <thead class="operations-table-head">
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Motif</th>
                                <th class="text-end">Montant</th>
                                <th class="text-end no-print">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($operations as $op)
                                <tr>
                                    <td class="text-muted">{{ $op->created_at?->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-semibold">{{ $op->typeOperation?->name ?? '—' }}</span>
                                            @if ((int) ($op->typeOperation?->nature ?? 0) === 1)
                                                <span class="badge bg-success">Entrée</span>
                                            @else
                                                <span class="badge bg-danger">Sortie</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $op->reason }}</td>
                                    <td class="text-end">
                                        @if ((int) ($op->typeOperation?->nature ?? 0) === 1)
                                            <span
                                                class="fw-bold text-success">{{ number_format((float) $op->amount, 0, ',', ' ') }}
                                                {{ $op->devise?->symbole ?? ($op->devise?->code ?? '') }}</span>
                                        @else
                                            <span
                                                class="fw-bold text-danger">{{ number_format((float) $op->amount, 0, ',', ' ') }}
                                                {{ $op->devise?->symbole ?? ($op->devise?->code ?? '') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end no-print">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary"
                                                wire:click="edit({{ $op->id }})">
                                                <i class="fa fa-pen"></i>
                                            </button>
                                            <button class="btn btn-outline-danger"
                                                wire:click="delete({{ $op->id }})">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        <i class="fa fa-receipt fa-2x mb-3 opacity-25"></i>
                                        <p class="mb-0">Aucune opération</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center no-print">
                    <button type="button" class="btn btn-outline-secondary" onclick="printOperations()">
                        <i class="fa fa-print me-2"></i> Imprimer
                    </button>
                    {{ $operations->links() }}
                </div>
            </div>
        </div>

        @if ($showModal)
            <div wire:show="showModal" class="modal-backdrop-custom">
                <div class="modal-dialog modal-dialog-centered bg-white">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white px-4 py-2">
                            <h5 class="modal-title">
                                {{ $operationId ? 'Modifier une opération' : 'Nouvelle opération' }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white"
                                wire:click="closeModal"></button>
                        </div>

                        <div class="modal-body px-4 py-3">
                            <div class="mb-3">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('type_operation_id') is-invalid @enderror"
                                    wire:model.defer="type_operation_id">
                                    <option value="">— Sélectionner —</option>
                                    @foreach ($types as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}
                                            ({{ (int) $type->nature === 1 ? 'Entrée' : 'Sortie' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('type_operation_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Devise <span class="text-danger">*</span></label>
                                <select class="form-select @error('devise_id') is-invalid @enderror"
                                    wire:model.defer="devise_id">
                                    <option value="">— Sélectionner —</option>
                                    @foreach ($devises as $devise)
                                        <option value="{{ $devise->id }}">
                                            {{ $devise->code }} ({{ $devise->symbole ?? $devise->code }})
                                            @if ($devise->is_default)
                                                - Par défaut
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('devise_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Motif <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('reason') is-invalid @enderror"
                                    wire:model.defer="reason" placeholder="Ex: Achat mobilier">
                                @error('reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Montant <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('amount') is-invalid @enderror"
                                    id="operationAmountInput" wire:model.defer="amount" wire:blur="formatAmount"
                                    inputmode="numeric" placeholder="0">
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="modal-footer px-4 py-2">
                            <button class="btn btn-light" type="button" wire:click="closeModal">Annuler</button>
                            <button class="btn btn-primary" type="button" wire:click="store">Enregistrer</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <script>
            (function() {
                function formatAmountInput(el) {
                    const original = el.value ?? '';
                    const selectionStart = el.selectionStart ?? original.length;

                    const digitsBeforeCursor = original
                        .slice(0, selectionStart)
                        .replace(/[^\d]/g, '').length;

                    let raw = original.replace(/[\s\u00A0]/g, '');
                    raw = raw.replace(/[^\d.,]/g, '');

                    const lastComma = raw.lastIndexOf(',');
                    const lastDot = raw.lastIndexOf('.');
                    const sepIndex = Math.max(lastComma, lastDot);

                    let intPart = raw;
                    let decPart = '';
                    if (sepIndex >= 0) {
                        intPart = raw.slice(0, sepIndex);
                        decPart = raw.slice(sepIndex + 1);
                    }

                    intPart = intPart.replace(/[^\d]/g, '');
                    decPart = decPart.replace(/[^\d]/g, '').slice(0, 2);

                    const grouped = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
                    const formatted = decPart.length > 0 ? grouped + ',' + decPart : grouped;

                    el.value = formatted;

                    let newPos = 0;
                    let seenDigits = 0;
                    while (newPos < formatted.length && seenDigits < digitsBeforeCursor) {
                        if (/\d/.test(formatted.charAt(newPos))) {
                            seenDigits++;
                        }
                        newPos++;
                    }

                    try {
                        el.setSelectionRange(newPos, newPos);
                    } catch (e) {}
                }

                function bind() {
                    const el = document.getElementById('operationAmountInput');
                    if (!el) return;
                    if (el.dataset.bound === '1') return;
                    el.dataset.bound = '1';

                    el.addEventListener('input', function() {
                        formatAmountInput(el);
                    });
                }

                document.addEventListener('livewire:initialized', bind);
                document.addEventListener('livewire:navigated', bind);
                bind();
            })();
        </script>

        <script>
            (function() {
                function printOperations() {
                    const area = document.getElementById('operationsPrintArea');
                    if (!area) return;

                    const clone = area.cloneNode(true);
                    clone.querySelectorAll('.no-print').forEach((el) => el.remove());
                    const header = clone.querySelector('.operations-print-header');
                    if (header) {
                        header.classList.remove('d-none');
                    }

                    const printFrame = document.createElement('iframe');
                    printFrame.style.position = 'fixed';
                    printFrame.style.right = '0';
                    printFrame.style.bottom = '0';
                    printFrame.style.width = '0';
                    printFrame.style.height = '0';
                    printFrame.style.border = '0';
                    printFrame.style.opacity = '0';
                    document.body.appendChild(printFrame);

                    const html = `
                    <!DOCTYPE html>
                    <html lang="fr">
                    <head>
                        <meta charset="utf-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1">
                        <title></title>
                        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                        <style>
                            :root { --ink: #111827; --muted: #6b7280; --line: #e5e7eb; --sidebar: #4e54c8; --sidebar-border: #3f45a7; }
                            body { padding: 18px; font-size: 12px; color: var(--ink); background: #fff; }
                            .operations-print-header { display: block !important; }
                            .operations-print-header hr { border-color: var(--line); opacity: 1; }
                            .operations-print-header .text-muted { color: var(--muted) !important; }

                            table { font-size: 12px; width: 100%; }
                            .table { border-collapse: collapse; }
                            .table thead th {
                                background: var(--sidebar) !important;
                                color: #ffffff !important;
                                border-bottom: 2px solid var(--sidebar-border) !important;
                                border-top: 1px solid var(--sidebar-border) !important;
                                font-weight: 700;
                                text-transform: uppercase;
                                letter-spacing: .02em;
                                font-size: 11px;
                                padding: 8px 10px !important;
                            }
                            .table tbody td {
                                border-bottom: 1px solid var(--line) !important;
                                padding: 9px 10px !important;
                                vertical-align: middle;
                            }
                            .table tbody tr:nth-child(even) td { background: #fafafa; }
                            .table-hover tbody tr:hover td { background: #f9fafb; }
                            .table td.text-end, .table th.text-end { text-align: right !important; }

                            .badge { border: 1px solid rgba(0,0,0,0.08); font-weight: 600; }
                            .badge.bg-success { background: #16a34a !important; }
                            .badge.bg-danger { background: #dc2626 !important; }

                            @media print {
                                body { padding: 0; }
                                .table { page-break-inside: auto; }
                                tr { page-break-inside: avoid; page-break-after: auto; }
                                thead { display: table-header-group; }
                                .table tbody tr:nth-child(even) td { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                                .table thead th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                            }
                        </style>
                    </head>
                    <body>${clone.innerHTML}</body>
                    </html>
                `;

                    printFrame.contentWindow.document.open();
                    printFrame.contentWindow.document.write(html);
                    printFrame.contentWindow.document.close();

                    printFrame.onload = function() {
                        try {
                            printFrame.contentWindow.focus();
                            printFrame.contentWindow.print();
                        } finally {
                            setTimeout(() => {
                                if (printFrame && printFrame.parentNode) {
                                    document.body.removeChild(printFrame);
                                }
                            }, 500);
                        }
                    };
                }

                window.printOperations = printOperations;
            })();
        </script>

        <style>
            .operations-table-head th {
                background: linear-gradient(180deg, #4e54c8 0%, #8f94fb 100%) !important;
                color: #ffffff !important;
                border-color: rgba(255, 255, 255, 0.18) !important;
            }
        </style>
    </div>
</div>
