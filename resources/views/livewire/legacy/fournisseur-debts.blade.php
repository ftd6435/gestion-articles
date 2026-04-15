<div>
    @php
        $company = \App\Models\CompanySetting::query()->first();
        $companyName = $company?->name ?: config('app.name');
        $companyLogoUrl = $company?->logo_path ? asset($company->logo_path) : null;
        $statusLabel = $statusFilter === 'open' ? 'Ouvertes' : ($statusFilter === 'closed' ? 'Clôturées' : 'Toutes');
    @endphp

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div class="mb-3 mb-md-0">
            <h1 class="h3 fw-bold mb-1">Anciens - Dettes fournisseurs</h1>
            <p class="text-muted mb-0">Suivi des dettes envers les fournisseurs (avant système)</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary" type="button" onclick="printElementById('legacy-fournisseur-debts-print')">
                <i class="fas fa-print me-2"></i> Imprimer
            </button>
            @access('legacy.fournisseurs', 'create')
                <button class="btn btn-primary" wire:click="openDebtModal">
                    <i class="fa fa-plus me-2"></i> Nouvelle dette
                </button>
            @endaccess
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Total</div>
                            <div class="fw-bold fs-4">{{ $statsTotal }}</div>
                        </div>
                        <div class="text-muted"><i class="fas fa-list fa-lg"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Ouvertes</div>
                            <div class="fw-bold fs-4">{{ $statsOpen }}</div>
                        </div>
                        <div class="text-success"><i class="fas fa-unlock fa-lg"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Clôturées</div>
                            <div class="fw-bold fs-4">{{ $statsClosed }}</div>
                        </div>
                        <div class="text-secondary"><i class="fas fa-lock fa-lg"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" placeholder="Rechercher fournisseur (nom/téléphone)..."
                            wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="statusFilter">
                        <option value="open">Ouvertes</option>
                        <option value="closed">Clôturées</option>
                        <option value="all">Toutes</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" wire:model.live="dateFrom" placeholder="Début">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" wire:model.live="dateTo" placeholder="Fin">
                </div>
                <div class="col-md-2 d-flex justify-content-md-end">
                    <select class="form-select w-auto" wire:model.live="perPage">
                        <option value="12">12</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
            @if($openRemainingByDevise->count())
                <div class="mt-3 small text-muted">
                    <span class="fw-semibold">Reste à payer (ouvert):</span>
                    @foreach($openRemainingByDevise as $row)
                        @php $currency = $row['devise']?->symbole ?? $row['devise']?->code ?? '—'; @endphp
                        <span class="ms-2">
                            {{ number_format((float) $row['total_remaining'], 2, ',', ' ') }} {{ $currency }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fournisseur</th>
                            <th>Date</th>
                            <th>Devise</th>
                            <th class="text-end">Montant dû</th>
                            <th class="text-end">Payé</th>
                            <th class="text-end">Reste</th>
                            <th class="text-center">Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($debts as $debt)
                            @php
                                $paid = (float) ($debt->paid_sum ?? 0);
                                $due = (float) ($debt->due_amount ?? 0);
                                $remaining = max(0, $due - $paid);
                                $currency = $debt->devise?->symbole ?? $debt->devise?->code ?? '—';
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $debt->fournisseur?->name ?? '—' }}</div>
                                    <div class="text-muted small">{{ $debt->fournisseur?->telephone ?? '' }}</div>
                                </td>
                                <td class="text-muted">{{ $debt->debt_date?->format('d/m/Y') ?? '—' }}</td>
                                <td class="text-muted">{{ $currency }}</td>
                                <td class="text-end fw-semibold">{{ number_format($due, 2, ',', ' ') }}</td>
                                <td class="text-end text-muted">{{ number_format($paid, 2, ',', ' ') }}</td>
                                <td class="text-end fw-semibold">{{ number_format($remaining, 2, ',', ' ') }}</td>
                                <td class="text-center">
                                    @if($debt->is_closed)
                                        <span class="badge bg-secondary">Clôturée</span>
                                    @else
                                        <span class="badge bg-success">Ouverte</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-outline-secondary" wire:click="openViewModal({{ $debt->id }})">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        @access('legacy.fournisseurs', 'update')
                                            <button class="btn btn-outline-primary" wire:click="openDebtModal({{ $debt->id }})"
                                                {{ $debt->is_closed ? 'disabled' : '' }}>
                                                <i class="fa fa-pen"></i>
                                            </button>
                                        @endaccess
                                        @access('legacy.fournisseurs', 'create')
                                            <button class="btn btn-outline-success" wire:click="openPaymentModal({{ $debt->id }})"
                                                {{ $debt->is_closed ? 'disabled' : '' }}>
                                                <i class="fa fa-money-bill"></i>
                                            </button>
                                        @endaccess
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="fa fa-folder-open fa-3x mb-3 opacity-25"></i>
                                    <p class="mb-0">Aucune dette trouvée</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($debts->hasPages())
            <div class="card-footer">
                {{ $debts->links(data: ['scrollTo' => false]) }}
            </div>
        @endif
    </div>

    @if($showDebtModal)
        <div class="modal-backdrop-custom">
            <div class="modal-dialog modal-dialog-centered bg-white" style="max-width: 760px;">
                <div class="modal-content shadow-lg bg-white rounded">
                    <div class="modal-header p-4 bg-primary">
                        <h5 class="modal-title text-white">
                            {{ $debtId ? 'Modifier une dette fournisseur' : 'Nouvelle dette fournisseur' }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeDebtModal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Fournisseur <span class="text-danger">*</span></label>
                                <select class="form-select @error('fournisseur_id') is-invalid @enderror" wire:model.defer="fournisseur_id">
                                    <option value="">Sélectionner</option>
                                    @foreach($fournisseurs as $f)
                                        <option value="{{ $f->id }}">{{ $f->name }} ({{ $f->telephone }})</option>
                                    @endforeach
                                </select>
                                @error('fournisseur_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Devise <span class="text-danger">*</span></label>
                                <select class="form-select @error('devise_id') is-invalid @enderror" wire:model.defer="devise_id">
                                    <option value="">Sélectionner</option>
                                    @foreach($devises as $devise)
                                        <option value="{{ $devise->id }}">{{ $devise->code }} {{ $devise->symbole ? '(' . $devise->symbole . ')' : '' }}</option>
                                    @endforeach
                                </select>
                                @error('devise_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Montant dû <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control @error('due_amount') is-invalid @enderror"
                                    wire:model.defer="due_amount">
                                @error('due_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date</label>
                                <input type="date" class="form-control @error('debt_date') is-invalid @enderror"
                                    wire:model.defer="debt_date">
                                @error('debt_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" rows="3" wire:model.defer="notes"></textarea>
                                @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer p-4">
                        <button class="btn btn-light me-2" type="button" wire:click="closeDebtModal">Annuler</button>
                        <button class="btn btn-primary" type="button" wire:click="saveDebt">Enregistrer</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showPaymentModal)
        @php
            $currency = $paymentDebt?->devise?->symbole ?? $paymentDebt?->devise?->code ?? '—';
            $remaining = $paymentDebt ? $paymentDebt->remainingAmount() : 0;
        @endphp
        <div class="modal-backdrop-custom">
            <div class="modal-dialog modal-dialog-centered bg-white" style="max-width: 900px;">
                <div class="modal-content shadow-lg bg-white rounded">
                    <div class="modal-header p-4 bg-success">
                        <h5 class="modal-title text-white">Paiements - Dette fournisseur</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closePaymentModal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
                            <div>
                                <div class="fw-semibold">{{ $paymentDebt?->fournisseur?->name ?? '—' }}</div>
                                <div class="text-muted small">
                                    Montant dû: {{ number_format((float) ($paymentDebt?->due_amount ?? 0), 2, ',', ' ') }} {{ $currency }}
                                    · Reste: {{ number_format((float) $remaining, 2, ',', ' ') }} {{ $currency }}
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 align-items-end mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Date</label>
                                <input type="date" class="form-control @error('payment_date') is-invalid @enderror"
                                    wire:model.defer="payment_date">
                                @error('payment_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Montant ({{ $currency }})</label>
                                <input type="number" step="0.01" class="form-control @error('payment_amount') is-invalid @enderror"
                                    wire:model.defer="payment_amount">
                                @error('payment_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Mode</label>
                                <select class="form-select @error('payment_mode') is-invalid @enderror" wire:model.defer="payment_mode">
                                    <option value="cash">Cash</option>
                                    <option value="ESPECES">Espèces</option>
                                    <option value="CHEQUE">Chèque</option>
                                    <option value="VIREMENT">Virement</option>
                                    <option value="MOBILE">Mobile Money</option>
                                    <option value="CARTE">Carte bancaire</option>
                                </select>
                                @error('payment_mode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-success w-100" type="button" wire:click="savePayment">
                                    Enregistrer
                                </button>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <input type="text" class="form-control @error('payment_notes') is-invalid @enderror"
                                    wire:model.defer="payment_notes">
                                @error('payment_notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Référence</th>
                                        <th>Mode</th>
                                        <th class="text-end">Montant</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($paymentDebtPayments as $p)
                                        <tr>
                                            <td>{{ $p->date_paiement?->format('d/m/Y') ?? '—' }}</td>
                                            <td class="text-muted">{{ $p->reference }}</td>
                                            <td class="text-muted">{{ $p->mode_paiement }}</td>
                                            <td class="text-end fw-semibold">{{ number_format((float) $p->montant, 2, ',', ' ') }} {{ $currency }}</td>
                                            <td class="text-muted">{{ $p->notes ?? '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">Aucun paiement</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer p-4">
                        <button class="btn btn-light" type="button" wire:click="closePaymentModal">Fermer</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showViewModal)
        @php
            $currency = $viewDebt?->devise?->symbole ?? $viewDebt?->devise?->code ?? '—';
            $paid = (float) ($viewDebt?->paid_sum ?? 0);
            $due = (float) ($viewDebt?->due_amount ?? 0);
            $remaining = max(0, $due - $paid);
        @endphp
        <div class="modal-backdrop-custom">
            <div class="modal-dialog modal-dialog-centered bg-white" style="max-width: 980px;">
                <div class="modal-content shadow-lg bg-white rounded">
                    <div class="modal-header p-4 bg-dark">
                        <h5 class="modal-title text-white">Détails - Dette fournisseur</h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light btn-sm" onclick="printElementById('legacy-fournisseur-debt-detail-print')">
                                <i class="fas fa-print me-1"></i> Imprimer
                            </button>
                            <button type="button" class="btn-close btn-close-white" wire:click="closeViewModal"></button>
                        </div>
                    </div>
                    <div class="modal-body p-4">
                        <div id="legacy-fournisseur-debt-detail-print">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    @if($companyLogoUrl)
                                        <img src="{{ $companyLogoUrl }}" alt="Logo" style="height: 36px;">
                                    @endif
                                    <div class="fw-bold">{{ $companyName }}</div>
                                </div>
                                <div class="text-muted small">{{ now()->format('d/m/Y H:i') }}</div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <div class="border rounded p-3">
                                        <div class="text-muted small mb-1">Fournisseur</div>
                                        <div class="fw-semibold">{{ $viewDebt?->fournisseur?->name ?? '—' }}</div>
                                        <div class="text-muted small">{{ $viewDebt?->fournisseur?->telephone ?? '' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded p-3">
                                        <div class="text-muted small mb-1">Dette</div>
                                        <div class="text-muted small">Date: {{ $viewDebt?->debt_date?->format('d/m/Y') ?? '—' }}</div>
                                        <div class="text-muted small">Statut: {{ $viewDebt?->is_closed ? 'Clôturée' : 'Ouverte' }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <div class="border rounded p-3">
                                        <div class="text-muted small">Montant dû</div>
                                        <div class="fw-bold">{{ number_format($due, 2, ',', ' ') }} {{ $currency }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded p-3">
                                        <div class="text-muted small">Payé</div>
                                        <div class="fw-bold">{{ number_format($paid, 2, ',', ' ') }} {{ $currency }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded p-3">
                                        <div class="text-muted small">Reste</div>
                                        <div class="fw-bold">{{ number_format($remaining, 2, ',', ' ') }} {{ $currency }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="fw-semibold mb-2">Paiements</div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Référence</th>
                                            <th>Mode</th>
                                            <th class="text-end">Montant</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($viewDebtPayments as $p)
                                            <tr>
                                                <td>{{ $p->date_paiement?->format('d/m/Y') ?? '—' }}</td>
                                                <td class="text-muted">{{ $p->reference }}</td>
                                                <td class="text-muted">{{ $p->mode_paiement }}</td>
                                                <td class="text-end fw-semibold">{{ number_format((float) $p->montant, 2, ',', ' ') }} {{ $currency }}</td>
                                                <td class="text-muted">{{ $p->notes ?? '—' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">Aucun paiement</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer p-4">
                        <button class="btn btn-light" type="button" wire:click="closeViewModal">Fermer</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="d-none">
        <div id="legacy-fournisseur-debts-print">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    @if($companyLogoUrl)
                        <img src="{{ $companyLogoUrl }}" alt="Logo" style="height: 36px;">
                    @endif
                    <div class="fw-bold">{{ $companyName }}</div>
                </div>
                <div class="text-muted small">{{ now()->format('d/m/Y H:i') }}</div>
            </div>

            <div class="mb-3">
                <div class="fw-bold">Liste - Dettes fournisseurs</div>
                <div class="text-muted small">
                    Statut: {{ $statusLabel }}
                    @if($dateFrom && $dateTo)
                        · Période: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
                    @endif
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Fournisseur</th>
                            <th>Date</th>
                            <th>Devise</th>
                            <th class="text-end">Dû</th>
                            <th class="text-end">Payé</th>
                            <th class="text-end">Reste</th>
                            <th class="text-center">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($printDebts as $debt)
                            @php
                                $paid = (float) ($debt->paid_sum ?? 0);
                                $due = (float) ($debt->due_amount ?? 0);
                                $remaining = max(0, $due - $paid);
                                $currency = $debt->devise?->symbole ?? $debt->devise?->code ?? '—';
                            @endphp
                            <tr>
                                <td>{{ $debt->fournisseur?->name ?? '—' }}</td>
                                <td class="text-muted">{{ $debt->debt_date?->format('d/m/Y') ?? '—' }}</td>
                                <td class="text-muted">{{ $currency }}</td>
                                <td class="text-end fw-semibold">{{ number_format($due, 2, ',', ' ') }}</td>
                                <td class="text-end text-muted">{{ number_format($paid, 2, ',', ' ') }}</td>
                                <td class="text-end fw-semibold">{{ number_format($remaining, 2, ',', ' ') }}</td>
                                <td class="text-center">
                                    @if($debt->is_closed)
                                        <span class="badge bg-secondary">Clôturée</span>
                                    @else
                                        <span class="badge bg-success">Ouverte</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
