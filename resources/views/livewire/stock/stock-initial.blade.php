<div>
    @php
        $company = \App\Models\CompanySetting::query()->first();
        $companyName = $company?->name ?: config('app.name');
        $companyTel = $company?->telephone;
        $companyEmail = $company?->email;
        $companyLogoUrl = $company?->logo_path ? asset($company->logo_path) : null;

        // Build filters for QR code
        $filters = [];
        if ($search) {
            $filters['search'] = $search;
        }
        if ($filterMagasin) {
            $filters['magasin_id'] = $filterMagasin;
        }
        if ($filterEtagere) {
            $filters['etagere_id'] = $filterEtagere;
        }
        if ($filterExpirable !== '') {
            $filters['expirable'] = $filterExpirable;
        }

        $publicUrl = \Illuminate\Support\Facades\URL::signedRoute('public.stock-initial.show', $filters);
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=' . urlencode($publicUrl);
    @endphp

    <!-- Print Header (Hidden on screen, visible on print) -->
    <div class="d-none d-print-block mb-4">
        <div class="d-flex justify-content-between align-items-start gap-3">
            <div class="d-flex align-items-center gap-3">
                @if ($companyLogoUrl)
                    <img src="{{ $companyLogoUrl }}" alt="Logo" style="max-height: 60px; max-width: 160px;">
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

            <div class="text-end">
                <img src="{{ $qrUrl }}" alt="QR" style="height: 90px; width: 90px;">
                <div class="small text-muted mt-1">Scanner pour ouvrir</div>
            </div>
        </div>

        <div class="text-center mt-3">
            <h2 class="mb-1">STOCK INITIAL DES ARTICLES</h2>
            <div class="mb-0">Généré le {{ now()->format('d/m/Y à H:i') }}</div>
        </div>

        @if ($search || $filterMagasin || $filterEtagere || $filterExpirable !== '')
            <div class="mt-3 text-muted small">
                <strong>Filtres appliqués:</strong>
                @if ($search)
                    <span class="badge bg-secondary ms-2">Recherche: {{ $search }}</span>
                @endif
                @if ($filterMagasin)
                    @php
                        $selectedMagasin = $magasins->firstWhere('id', $filterMagasin);
                    @endphp
                    @if ($selectedMagasin)
                        <span class="badge bg-secondary ms-2">Magasin: {{ $selectedMagasin->nom }}</span>
                    @endif
                @endif
                @if ($filterEtagere)
                    @php
                        $selectedEtagere = $etageresList->firstWhere('id', $filterEtagere);
                    @endphp
                    @if ($selectedEtagere)
                        <span class="badge bg-secondary ms-2">Étagère: {{ $selectedEtagere->code_etagere }}</span>
                    @endif
                @endif
                @if ($filterExpirable === '1')
                    <span class="badge bg-secondary ms-2">Avec expiration</span>
                @elseif($filterExpirable === '0')
                    <span class="badge bg-secondary ms-2">Sans expiration</span>
                @endif
            </div>
        @endif

        <hr class="mt-3 mb-0">
    </div>

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div class="mb-3 mb-md-0">
            <h1 class="h3 fw-bold mb-1">Stock Initial des Articles</h1>
            <p class="text-muted mb-0">Gérer les quantités initiales en stock par emplacement</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-primary">
                <i class="fa fa-print me-2"></i> Imprimer
            </button>
            <button wire:click="create" class="btn btn-primary">
                <i class="fa fa-plus me-2"></i> Ajouter stock initial
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Rechercher</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control"
                        placeholder="Référence ou désignation d'article...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Magasin</label>
                    <select wire:model.live="filterMagasin" class="form-select">
                        <option value="">Tous les magasins</option>
                        @foreach ($magasins as $magasin)
                            <option value="{{ $magasin->id }}">{{ $magasin->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Étagère</label>
                    <select wire:model.live="filterEtagere" class="form-select">
                        <option value="">Toutes les étagères</option>
                        @foreach ($etageresList as $etagere)
                            <option value="{{ $etagere->id }}">{{ $etagere->code_etagere }}
                                ({{ $etagere->magasin->nom }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Périssable</label>
                    <select wire:model.live="filterExpirable" class="form-select">
                        <option value="">Tous</option>
                        <option value="1">Avec expiration</option>
                        <option value="0">Sans expiration</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">Liste des stocks initiaux</h5>
            <span class="badge bg-primary">{{ $stocks->total() }} enregistrement(s)</span>
        </div>

        <div class="card-body p-0">
            @include('components.shared.alerts')

            <!-- Desktop -->
            <div class="table-responsive d-none d-md-block">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Article</th>
                            <th>Magasin</th>
                            <th>Étagère</th>
                            <th>Quantité</th>
                            <th>Date inventaire</th>
                            <th>Expiration</th>
                            <th>Notes</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stocks as $stock)
                            <tr>
                                <td>{{ $loop->iteration + ($stocks->currentPage() - 1) * $stocks->perPage() }}</td>
                                <td>
                                    <div>
                                        <div class="fw-semibold">{{ $stock->article->reference }}</div>
                                        <small class="text-muted">{{ $stock->article->designation }}</small>
                                    </div>
                                </td>
                                <td>{{ $stock->magasin->nom }}</td>
                                <td><span class="badge bg-info">{{ $stock->etagere->code_etagere }}</span></td>
                                <td><strong>{{ number_format($stock->quantity, 0) }}</strong></td>
                                <td>{{ $stock->date_inventaire->format('d/m/Y') }}</td>
                                <td>
                                    @if ($stock->date_expiration)
                                        @if ($stock->isExpired())
                                            <span class="badge bg-danger">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                {{ $stock->date_expiration->format('d/m/Y') }}
                                            </span>
                                        @elseif($stock->isExpiringSoon(30))
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-clock me-1"></i>
                                                {{ $stock->date_expiration->format('d/m/Y') }}
                                            </span>
                                        @else
                                            <span class="badge bg-success">
                                                {{ $stock->date_expiration->format('d/m/Y') }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-muted small">
                                    {{ \Illuminate\Support\Str::limit($stock->notes, 30) ?? '—' }}
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button wire:click="edit({{ $stock->id }})" class="btn btn-outline-primary"
                                            data-bs-toggle="tooltip" title="Modifier">
                                            <i class="fa fa-pen"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    Aucun stock initial trouvé
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light d-print-table-footer-group">
                        <tr>
                            <td colspan="4" class="text-end fw-bold">Total des quantités:</td>
                            <td class="fw-bold">{{ number_format($stocks->sum('quantity'), 0) }}</td>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Mobile -->
            <div class="d-md-none">
                @foreach ($stocks as $stock)
                    <div class="border-bottom p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1">{{ $stock->article->reference }}</h6>
                                <p class="text-muted small mb-1">{{ $stock->article->designation }}</p>
                                <div class="d-flex gap-2 flex-wrap mb-2">
                                    <span class="badge bg-secondary">{{ $stock->magasin->nom }}</span>
                                    <span class="badge bg-info">{{ $stock->etagere->code_etagere }}</span>
                                    <span class="badge bg-dark">Qté: {{ number_format($stock->quantity, 0) }}</span>
                                </div>
                                @if ($stock->date_expiration)
                                    @if ($stock->isExpired())
                                        <span class="badge bg-danger">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            Exp: {{ $stock->date_expiration->format('d/m/Y') }}
                                        </span>
                                    @elseif($stock->isExpiringSoon(30))
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-clock me-1"></i>
                                            Exp: {{ $stock->date_expiration->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            Exp: {{ $stock->date_expiration->format('d/m/Y') }}
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-3">
                            <button wire:click="edit({{ $stock->id }})"
                                class="btn btn-sm btn-outline-primary flex-fill">
                                <i class="fa fa-pen me-1"></i> Modifier
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>

        <!-- Pagination -->
        @if ($stocks->hasPages())
            <div class="card-footer bg-white">
                {{ $stocks->links() }}
            </div>
        @endif
    </div>

    <!-- Modal -->
    @if ($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $isEditMode ? 'Modifier' : 'Ajouter' }} un stock initial
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>

                    <form wire:submit.prevent="save">
                        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                            <div class="row g-3">
                                <!-- Article with Search -->
                                <div class="col-md-12">
                                    <label for="article_search" class="form-label">
                                        Article <span class="text-danger">*</span>
                                    </label>
                                    <div class="position-relative">
                                        <input type="text" wire:model.live.debounce.300ms="articleSearch"
                                            id="article_search"
                                            class="form-control @error('article_id') is-invalid @enderror"
                                            placeholder="Rechercher un article..." autocomplete="off"
                                            {{ $isEditMode ? 'disabled' : '' }}
                                            wire:focus="$set('showArticleDropdown', true)"
                                            wire:blur="closeArticleDropdown">

                                        @if ($showArticleDropdown && !$isEditMode)
                                            <div class="position-absolute w-100 bg-white border rounded shadow-sm mt-1"
                                                style="z-index: 1050; max-height: 200px; overflow-y: auto;">
                                                @forelse ($filteredArticles as $article)
                                                    <div wire:click="selectArticle({{ $article['id'] }})"
                                                        class="p-2 cursor-pointer hover-bg-light border-bottom"
                                                        style="cursor: pointer;">
                                                        <div class="fw-semibold">{{ $article['reference'] }}</div>
                                                        <small
                                                            class="text-muted">{{ $article['designation'] }}</small>
                                                    </div>
                                                @empty
                                                    <div class="p-2 text-muted">Aucun article trouvé</div>
                                                @endforelse
                                            </div>
                                        @endif
                                    </div>
                                    @error('article_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Magasin -->
                                <div class="col-md-6">
                                    <label for="magasin_id" class="form-label">
                                        Magasin <span class="text-danger">*</span>
                                    </label>
                                    <select wire:model.live="magasin_id" id="magasin_id"
                                        class="form-select @error('magasin_id') is-invalid @enderror"
                                        {{ $isEditMode ? 'disabled' : '' }}>
                                        <option value="">Sélectionner un magasin</option>
                                        @foreach ($magasins as $magasin)
                                            <option value="{{ $magasin->id }}">{{ $magasin->nom }}</option>
                                        @endforeach
                                    </select>
                                    @error('magasin_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Étagère -->
                                <div class="col-md-6">
                                    <label for="etagere_id" class="form-label">
                                        Étagère <span class="text-danger">*</span>
                                    </label>
                                    <select wire:model="etagere_id" id="etagere_id"
                                        class="form-select @error('etagere_id') is-invalid @enderror"
                                        {{ $isEditMode ? 'disabled' : '' }}>
                                        <option value="">Sélectionner une étagère</option>
                                        @foreach ($etageres as $etagere)
                                            <option value="{{ $etagere->id }}">{{ $etagere->code_etagere }}</option>
                                        @endforeach
                                    </select>
                                    @error('etagere_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if (!$magasin_id)
                                        <small class="text-muted">Sélectionnez d'abord un magasin</small>
                                    @endif
                                </div>

                                <!-- Quantity -->
                                <div class="col-md-6">
                                    <label for="quantity" class="form-label">
                                        Quantité <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" wire:model="quantity" id="quantity" step="1"
                                        class="form-control @error('quantity') is-invalid @enderror" placeholder="0">
                                    @error('quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Date inventaire -->
                                <div class="col-md-6">
                                    <label for="date_inventaire" class="form-label">
                                        Date inventaire <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" wire:model="date_inventaire" id="date_inventaire"
                                        class="form-control @error('date_inventaire') is-invalid @enderror">
                                    @error('date_inventaire')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Date expiration -->
                                <div class="col-md-12">
                                    <label for="date_expiration" class="form-label">
                                        Date d'expiration <small class="text-muted">(optionnel, si périssable)</small>
                                    </label>
                                    <input type="date" wire:model="date_expiration" id="date_expiration"
                                        class="form-control @error('date_expiration') is-invalid @enderror">
                                    @error('date_expiration')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Notes -->
                                <div class="col-md-12">
                                    <label for="notes" class="form-label">
                                        Notes <small class="text-muted">(optionnel)</small>
                                    </label>
                                    <textarea wire:model="notes" id="notes" rows="3" class="form-control @error('notes') is-invalid @enderror"
                                        placeholder="Ex: Inventaire d'ouverture, Comptage physique..."></textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">Annuler</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save me-2"></i>
                                {{ $isEditMode ? 'Modifier' : 'Enregistrer' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <style>
        @media print {

            /* Show print header */
            .d-print-block {
                display: block !important;
            }

            .d-print-table-footer-group {
                display: table-footer-group !important;
            }

            /* Hide non-printable elements */
            .btn,
            button,
            .card-header .badge,
            .card-footer,
            .modal,
            .pagination {
                display: none !important;
            }

            /* Hide filter section */
            .card.shadow-sm.border-0.mb-3 {
                display: none !important;
            }

            /* Optimize table for print */
            .table {
                border-collapse: collapse;
                width: 100%;
            }

            .table th,
            .table td {
                border: 1px solid #dee2e6;
                padding: 8px;
                font-size: 11px;
            }

            .table thead,
            .table tfoot {
                background-color: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* Show only desktop table */
            .d-none.d-md-block {
                display: block !important;
            }

            .d-md-none {
                display: none !important;
            }

            /* Page header */
            h1,
            h2 {
                font-size: 18px;
                margin-bottom: 10px;
            }

            .text-muted {
                color: #6c757d !important;
            }

            /* Badges with proper colors */
            .badge {
                border: 1px solid rgba(0, 0, 0, 0.15);
                padding: 2px 6px;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .badge.bg-info {
                background-color: #0dcaf0 !important;
                color: #000 !important;
            }

            .badge.bg-success {
                background-color: #198754 !important;
                color: #fff !important;
            }

            .badge.bg-warning {
                background-color: #ffc107 !important;
                color: #000 !important;
            }

            .badge.bg-danger {
                background-color: #dc3545 !important;
                color: #fff !important;
            }

            .badge.bg-secondary {
                background-color: #6c757d !important;
                color: #fff !important;
            }

            /* Card styling */
            .card {
                border: 1px solid #000;
                box-shadow: none !important;
            }

            .card-header {
                background-color: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* Page setup */
            @page {
                size: A4 landscape;
                margin: 1cm;
            }

            body {
                margin: 0;
                padding: 15px;
            }
        }

        /* Hover effect for article dropdown */
        .hover-bg-light:hover {
            background-color: #f8f9fa;
        }
    </style>

    <script>
        document.addEventListener('livewire:init', () => {
            // Close article dropdown on blur with delay
            Livewire.on('article-dropdown-close-delay', () => {
                setTimeout(() => {
                    @this.set('showArticleDropdown', false);
                }, 200);
            });
        });
    </script>
</div>
