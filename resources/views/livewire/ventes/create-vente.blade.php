<div class="container-fluid py-4">

    {{-- Stacked Responsive Design --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3 p-md-4">
            {{-- Mobile top bar --}}
            <div class="d-flex justify-content-between align-items-center d-md-none mb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle">
                        <i class="fas fa-shopping-cart text-primary"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">Nouvelle Vente</h5>
                        <small class="text-muted">#{{ $reference }}</small>
                    </div>
                </div>
                <button class="btn btn-success btn-sm d-flex align-items-center"
                        wire:click="openClientModal"
                        type="button">
                    <i class="fas fa-plus-circle me-1"></i>
                    <span class="d-none d-sm-inline">Client</span>
                </button>
            </div>

            {{-- Desktop layout --}}
            <div class="d-none d-md-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                        <i class="fas fa-shopping-cart fa-lg text-primary"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0">Nouvelle Vente</h4>
                        <div class="mt-1">
                            <span class="badge bg-primary fs-6">#{{ $reference }}</span>
                        </div>
                    </div>
                </div>

                <button class="btn btn-success px-4 py-3 d-flex align-items-center fw-medium shadow"
                        wire:click="openClientModal"
                        type="button">
                    <i class="fas fa-plus-circle me-2"></i>
                    Ajouter un Client
                </button>
            </div>
        </div>
    </div>

    @include('components.shared.alerts')

    @if (!$showPaiementForm)
        <form wire:submit.prevent="store">

            {{-- Mobile Summary Card --}}
            <div class="card border-0 shadow-sm mb-3 d-md-none">
                <div class="card-body p-3">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="text-center">
                                <div class="text-muted small">Total</div>
                                <div class="fw-bold h5 text-success">{{ number_format($this->getSubTotal(), 0) }} {{ $currency }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="text-muted small">Remise</div>
                                <div class="fw-bold h6 text-danger">{{ $remise }}%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Infos --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3 d-none d-md-block">Informations générales</h6>

                    <div class="row g-3">
                        {{-- Searchable Client Select --}}
                        <div class="col-12 col-md-4">
                            <label class="form-label">Client *</label>
                            <div class="position-relative">
                                <div class="input-group input-group-sm">
                                    <input type="text"
                                        class="form-control @error('client_id') is-invalid @enderror"
                                        wire:model.live.debounce.300ms="clientSearch"
                                        wire:blur="closeClientDropdown"
                                        placeholder="Rechercher client..."
                                        autocomplete="off"
                                        @if($client_id) disabled @endif>

                                    @if($client_id)
                                        <button class="btn btn-outline-secondary btn-sm" type="button" wire:click="clearClient">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @else
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-search"></i>
                                        </span>
                                    @endif
                                </div>

                                @error('client_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror

                                {{-- Client Dropdown --}}
                                @if($showClientDropdown && !$client_id && count($filteredClients) > 0)
                                    <div class="position-absolute top-100 start-0 end-0 z-3 mt-1">
                                        <div class="card border shadow-sm" style="max-height: 200px; overflow-y: auto;">
                                            <div class="list-group list-group-flush">
                                                @foreach($filteredClients as $client)
                                                    <button type="button"
                                                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2"
                                                        wire:click="selectClient({{ $client['id'] }})"
                                                        wire:key="client-{{ $client['id'] }}">
                                                        <div class="text-start">
                                                            <div class="fw-medium text-truncate" style="max-width: 150px;">{{ $client['name'] }}</div>
                                                            <small class="text-muted">{{ $client['telephone'] }}</small>
                                                        </div>
                                                        <span class="badge bg-light text-dark d-none d-sm-inline">{{ $client['type'] }}</span>
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Selected Client Info --}}
                                @if($client_id)
                                    <div class="mt-2">
                                        <div class="d-flex align-items-center gap-2 p-2 bg-light rounded">
                                            <div class="flex-grow-1 text-truncate">
                                                <div class="fw-medium">{{ $clientName }}</div>
                                                <small class="text-muted">{{ $clientTelephone }}</small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-success">✓</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control form-control-sm" wire:model="date_facture">
                        </div>

                        <div class="col-6 col-md-2">
                            <label class="form-label">Type</label>
                            <select class="form-select form-select-sm" wire:model="type_vente">
                                <option value="GROS">Gros</option>
                                <option value="DETAIL">Detail</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-2">
                            <label class="form-label">Devise *</label>
                            <select class="form-select form-select-sm @error('devise_id') is-invalid @enderror" wire:model.live="devise_id">
                                <option value="">Sélectionner</option>
                                @foreach($devises as $devise)
                                    <option value="{{ $devise->id }}">
                                       {{ $devise->code }}
                                    </option>
                                @endforeach
                            </select>
                            @error('devise_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-6 col-md-2">
                            <label class="form-label">Remise (%)</label>
                            <input type="number"
                                class="form-control form-control-sm @error('remise') is-invalid @enderror"
                                min="0"
                                max="100"
                                step="1"
                                wire:model.live.debounce.300ms="remise">
                            @error('remise')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Articles --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">Articles vendus</h6>
                        <button type="button" class="btn btn-outline-primary btn-sm d-flex align-items-center"
                                wire:click="addLine">
                            <i class="fas fa-plus me-1"></i>
                            <span class="d-none d-sm-inline">Ajouter</span>
                        </button>
                    </div>

                    {{-- Desktop Table --}}
                    <div class="d-none d-md-block">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Article</th>
                                        <th>Étagère</th>
                                        <th class="text-end">Qté</th>
                                        <th class="text-end">PU</th>
                                        <th class="text-end">Total</th>
                                        <th></th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($lignes as $index => $ligne)
                                        @php
                                            // Accéder aux valeurs du tableau
                                            $article_id = $ligne['article_id'] ?? null;
                                            $etagere_id = $ligne['etagere_id'] ?? null;
                                            $quantity = floatval($ligne['quantity'] ?? 0);
                                            $unit_price = floatval($ligne['unit_price'] ?? 0);
                                            $available = intval($ligne['available'] ?? 0);
                                            $article_designation = $ligne['article_designation'] ?? '';
                                            $article_reference = $ligne['article_reference'] ?? '';
                                        @endphp

                                        <tr>
                                            <td class="align-middle">{{ $index + 1 }}</td>

                                            {{-- Searchable Article --}}
                                            <td>
                                                <div class="position-relative">
                                                    <div class="input-group input-group-sm">
                                                        <input type="text"
                                                            class="form-control @error('lignes.' . $index . '.article_id') is-invalid @enderror"
                                                            wire:model.live.debounce.300ms="articleSearches.{{ $index }}"
                                                            wire:blur="closeArticleDropdown({{ $index }})"
                                                            placeholder="Rechercher article..."
                                                            autocomplete="off">

                                                        @if($article_id)
                                                            <button class="btn btn-outline-secondary btn-sm" type="button" wire:click="clearArticle({{ $index }})">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        @else
                                                            <span class="input-group-text bg-light">
                                                                <i class="fas fa-search"></i>
                                                            </span>
                                                        @endif
                                                    </div>

                                                    @error('lignes.' . $index . '.article_id')
                                                        <div class="text-danger small">{{ $message }}</div>
                                                    @enderror

                                                    {{-- Article Dropdown --}}
                                                    @if(($showArticleDropdowns[$index] ?? false) && !$article_id && isset($filteredArticles[$index]) && count($filteredArticles[$index]) > 0)
                                                        <div class="position-absolute top-100 start-0 end-0 z-3 mt-1">
                                                            <div class="card border shadow-sm" style="max-height: 200px; overflow-y: auto;">
                                                                <div class="list-group list-group-flush">
                                                                    @foreach($filteredArticles[$index] as $article)
                                                                        <button type="button"
                                                                            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2"
                                                                            wire:click="selectArticle({{ $index }}, {{ $article['id'] }})"
                                                                            wire:key="article-{{ $index }}-{{ $article['id'] }}">
                                                                            <div class="text-start">
                                                                                <div class="fw-medium text-truncate" style="max-width: 200px;">{{ $article['designation'] }}</div>
                                                                                <small class="text-muted">Réf: {{ $article['reference'] }}</small>
                                                                            </div>
                                                                            <div class="text-end">
                                                                                <small class="text-success fw-bold">{{ number_format($article['prix_vente'] ?? 0, 0) }} {{ $currency }}</small>
                                                                            </div>
                                                                        </button>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>

                                            {{-- Etagere --}}
                                            <td>
                                                <select class="form-select form-select-sm @error('lignes.' . $index . '.etagere_id') is-invalid @enderror"
                                                        wire:model.live.debounce.300ms="lignes.{{ $index }}.etagere_id">
                                                    <option value="">---</option>
                                                    @php
                                                        $lineEtageres = $this->etageres[$index] ?? collect();
                                                    @endphp
                                                    @foreach($lineEtageres as $etagere)
                                                        @if ($etagere->available > 0)
                                                            <option value="{{ $etagere->id }}">
                                                                {{ $etagere->code }}
                                                                ({{ $etagere->magasin }})
                                                                - Dis: {{ $etagere->available }}
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                @error('lignes.' . $index . '.etagere_id')
                                                    <div class="text-danger small">{{ $message }}</div>
                                                @enderror
                                            </td>

                                            {{-- Quantity --}}
                                            <td class="text-end">
                                                <input type="number"
                                                    class="form-control form-control-sm text-end @error('lignes.' . $index . '.quantity') is-invalid @enderror @if($quantity > $available) border-danger @endif"
                                                    wire:model.live.debounce.300ms="lignes.{{ $index }}.quantity"
                                                    min="1">
                                                @if($quantity > $available)
                                                    <div class="text-danger small mt-1">
                                                        Stock: {{ $available }}
                                                    </div>
                                                @endif
                                                @error('lignes.' . $index . '.quantity')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>

                                            {{-- Unit price --}}
                                            <td class="text-end">
                                                <input type="number"
                                                    class="form-control form-control-sm text-end @error('lignes.' . $index . '.unit_price') is-invalid @enderror"
                                                    wire:model.live.debounce.300ms="lignes.{{ $index }}.unit_price"
                                                    min="0"
                                                    step="0.01">
                                                @error('lignes.' . $index . '.unit_price')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>

                                            {{-- Line total --}}
                                            <td class="text-end fw-bold align-middle">
                                                @php
                                                    $lineTotal = ($quantity ?? 0) * ($unit_price ?? 0);
                                                @endphp
                                                {{ number_format($lineTotal, 0) }} {{ $currency }}
                                            </td>

                                            {{-- Delete --}}
                                            <td class="text-end align-middle">
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                        wire:click="removeLine({{ $index }})"
                                                        title="Supprimer cette ligne">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>

                                {{-- FOOTER TOTAL --}}
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="5" class="text-end">Sous-total</th>
                                        <th class="text-end">
                                            {{ number_format($this->getSubTotal(), 0) }} {{ $currency }}
                                        </th>
                                        <th></th>
                                    </tr>

                                    <tr>
                                        <th colspan="5" class="text-end">Remise ({{ $remise }}%)</th>
                                        <th class="text-end text-danger">
                                            - {{ number_format($this->getRemiseAmount(), 0) }} {{ $currency }}
                                        </th>
                                        <th></th>
                                    </tr>

                                    <tr>
                                        <th colspan="5" class="text-end fw-bold">Total à payer</th>
                                        <th class="text-end fw-bold text-success">
                                            {{ number_format($this->getTotalAfterRemise(), 0) }} {{ $currency }}
                                        </th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    {{-- Mobile Cards for Lines --}}
                    <div class="d-md-none">
                        @foreach($lignes as $index => $ligne)
                            @php
                                $article_id = $ligne['article_id'] ?? null;
                                $etagere_id = $ligne['etagere_id'] ?? null;
                                $quantity = floatval($ligne['quantity'] ?? 0);
                                $unit_price = floatval($ligne['unit_price'] ?? 0);
                                $available = intval($ligne['available'] ?? 0);
                                $article_designation = $ligne['article_designation'] ?? '';
                            @endphp

                            <div class="card border mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <div class="fw-bold">Ligne {{ $index + 1 }}</div>
                                            @if($article_designation)
                                                <small class="text-muted">{{ $article_designation }}</small>
                                            @endif
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                wire:click="removeLine({{ $index }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>

                                    {{-- Searchable Article --}}
                                    <div class="mb-2">
                                        <label class="form-label small">Article *</label>
                                        <div class="position-relative">
                                            <div class="input-group input-group-sm">
                                                <input type="text"
                                                    class="form-control @error('lignes.' . $index . '.article_id') is-invalid @enderror"
                                                    wire:model.live.debounce.300ms="articleSearches.{{ $index }}"
                                                    wire:blur="closeArticleDropdown({{ $index }})"
                                                    placeholder="Rechercher article..."
                                                    autocomplete="off">

                                                @if($article_id)
                                                    <button class="btn btn-outline-secondary btn-sm" type="button" wire:click="clearArticle({{ $index }})">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @else
                                                    <span class="input-group-text bg-light">
                                                        <i class="fas fa-search"></i>
                                                    </span>
                                                @endif
                                            </div>

                                            @error('lignes.' . $index . '.article_id')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror

                                            {{-- Article Dropdown --}}
                                            @if(($showArticleDropdowns[$index] ?? false) && !$article_id && isset($filteredArticles[$index]) && count($filteredArticles[$index]) > 0)
                                                <div class="position-absolute top-100 start-0 end-0 z-3 mt-1">
                                                    <div class="card border shadow-sm" style="max-height: 200px; overflow-y: auto;">
                                                        <div class="list-group list-group-flush">
                                                            @foreach($filteredArticles[$index] as $article)
                                                                <button type="button"
                                                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2"
                                                                    wire:click="selectArticle({{ $index }}, {{ $article['id'] }})"
                                                                    wire:key="article-{{ $index }}-{{ $article['id'] }}">
                                                                    <div class="text-start">
                                                                        <div class="fw-medium text-truncate" style="max-width: 200px;">{{ $article['designation'] }}</div>
                                                                        <small class="text-muted">Réf: {{ $article['reference'] }}</small>
                                                                    </div>
                                                                    <div class="text-end">
                                                                        <small class="text-success fw-bold">{{ number_format($article['prix_vente'] ?? 0, 0) }} {{ $currency }}</small>
                                                                    </div>
                                                                </button>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="row g-2">
                                        {{-- Etagere --}}
                                        <div class="col-6">
                                            <label class="form-label small">Étagère *</label>
                                            <select class="form-select form-select-sm @error('lignes.' . $index . '.etagere_id') is-invalid @enderror"
                                                    wire:model.live.debounce.300ms="lignes.{{ $index }}.etagere_id">
                                                <option value="">---</option>
                                                @php
                                                    $lineEtageres = $this->etageres[$index] ?? collect();
                                                @endphp
                                                @foreach($lineEtageres as $etagere)
                                                    @if ($etagere->available > 0)
                                                            <option value="{{ $etagere->id }}">
                                                                {{ $etagere->code }}
                                                                - Dis: {{ $etagere->available }}
                                                            </option>
                                                        @endif
                                                @endforeach
                                            </select>
                                            @error('lignes.' . $index . '.etagere_id')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        {{-- Quantity --}}
                                        <div class="col-6">
                                            <label class="form-label small">Quantité *</label>

                                            <input type="number"
                                                class="form-control form-control-sm @error('lignes.' . $index . '.quantity') is-invalid @enderror @if($quantity > $available) border-danger @endif"
                                                wire:model.live.debounce.300ms="lignes.{{ $index }}.quantity"
                                                min="1">
                                            @if($quantity > $available)
                                                <div class="text-danger small mt-1">
                                                    Stock: {{ $available }}
                                                </div>
                                            @endif
                                            @error('lignes.' . $index . '.quantity')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        {{-- Unit price --}}
                                        <div class="col-6">
                                            <label class="form-label small">Prix unitaire *</label>
                                            <input type="number"
                                                class="form-control form-control-sm text-end @error('lignes.' . $index . '.unit_price') is-invalid @enderror"
                                                wire:model.live.debounce.300ms="lignes.{{ $index }}.unit_price"
                                                min="0"
                                                step="0.01">
                                            @error('lignes.' . $index . '.unit_price')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        {{-- Line total --}}
                                        <div class="col-6">
                                            <label class="form-label small">Total ligne</label>
                                            <div class="form-control form-control-sm bg-light text-end fw-bold">
                                                @php
                                                    $lineTotal = ($quantity ?? 0) * ($unit_price ?? 0);
                                                @endphp
                                                {{ number_format($lineTotal, 0) }} {{ $currency }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        {{-- Mobile Summary --}}
                        <div class="card border mt-3">
                            <div class="card-body">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="text-center">
                                            <div class="text-muted small">Sous-total</div>
                                            <div class="fw-bold h5">{{ number_format($this->getSubTotal(), 0) }} {{ $currency }}</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center">
                                            <div class="text-muted small">Remise</div>
                                            <div class="fw-bold h5 text-danger">- {{ number_format($this->getRemiseAmount(), 0) }} {{ $currency }}</div>
                                        </div>
                                    </div>
                                    <div class="col-12 mt-2 pt-2 border-top">
                                        <div class="text-center">
                                            <div class="text-muted small">Total à payer</div>
                                            <div class="fw-bold h4 text-success">{{ number_format($this->getTotalAfterRemise(), 0) }} {{ $currency }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Desktop Add Line Button --}}
                    <div class="d-none d-md-block">
                        <button type="button" class="btn btn-outline-primary mt-3"
                                wire:click="addLine">
                            <i class="fas fa-plus me-2"></i>Ajouter ligne
                        </button>
                    </div>
                </div>
            </div>

            {{-- Footer Actions --}}
            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('ventes.ventes') }}" class="btn btn-secondary">
                    Annuler
                </a>
                <button type="submit" class="btn btn-success"
                        @disabled(
                            !$client_id || !$devise_id ||
                            empty($lignes) ||
                            collect($lignes)->contains(fn($l) =>
                                empty($l['article_id']) ||
                                empty($l['etagere_id']) ||
                                empty($l['quantity']) ||
                                $l['quantity'] <= 0 ||
                                $l['quantity'] > ($l['available'] ?? 0)
                            )
                        )>
                    <i class="fas fa-save me-2"></i>Enregistrer
                </button>
            </div>

        </form>
    @endif

    @if($showPaiementForm)
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body">

                <h5 class="fw-bold mb-3">Facture</h5>

                {{-- Invoice summary --}}
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Article</th>
                                <th class="text-end">Qté</th>
                                <th class="text-end">PU</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lignes as $l)
                                @php
                                    // CORRECTION: Utiliser la syntaxe tableau
                                    $article = $articles->firstWhere('id', $l['article_id'] ?? null);
                                @endphp
                                <tr>
                                    <td>{{ $article->designation ?? '' }}</td>
                                    <td class="text-end">{{ $l['quantity'] ?? 0 }}</td>
                                    <td class="text-end">{{ number_format($l['unit_price'] ?? 0, 2) }}</td>
                                    <td class="text-end">
                                        @php
                                            $quantity = $l['quantity'] ?? 0;
                                            $unit_price = $l['unit_price'] ?? 0;
                                            $lineTotal = $quantity * $unit_price;
                                        @endphp
                                        {{ number_format($lineTotal, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="3" class="text-end">Total</th>
                                <th class="text-end">{{ number_format($this->getSubTotal(), 2) }}</th>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end">Remise</th>
                                <th class="text-end text-danger">
                                    - {{ number_format($this->getRemiseAmount(), 2) }}
                                </th>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end fw-bold">Net à payer</th>
                                <th class="text-end fw-bold text-success">
                                    {{ number_format($this->getTotalAfterRemise(), 2) }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Payment --}}
                <div class="row g-3 mt-3">
                    <div class="col-md-4">
                        <label class="form-label">Date paiement *</label>
                        <input type="date" class="form-control @error('paiement_date') is-invalid @enderror"
                            wire:model="paiement_date">
                        @error('paiement_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Mode paiement *</label>
                        <select wire:model="mode_paiement" class="form-select @error('mode_paiement') is-invalid @enderror">
                            <option value="">Sélectionner</option>
                            <option value="ESPECES">Espèces</option>
                            <option value="VIREMENT">Virement</option>
                            <option value="MOBILE MONEY">Mobile Money</option>
                        </select>
                        @error('mode_paiement')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-md-4">
                        <label class="form-label">Montant payé *</label>
                        <input type="number" class="form-control @error('paiement_montant') is-invalid @enderror"
                            wire:model.live.debounce.300ms="paiement_montant"
                            step="0.01"
                            min="0"
                            max="{{ $this->getTotalAfterRemise() }}">
                        @error('paiement_montant')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if($paiement_montant > $this->getTotalAfterRemise())
                            <div class="text-danger small mt-1">
                                Le montant payé ne peut pas dépasser {{ number_format($this->getTotalAfterRemise(), 2) }}
                            </div>
                        @endif
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Payé</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                {{ $currency }}
                            </span>
                            <input class="form-control bg-light"
                                value="{{ number_format((float) $paiement_montant ?? 0, 2) }}"
                                disabled>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Reste</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                {{ $currency }}
                            </span>
                            <input class="form-control bg-light text-danger fw-bold"
                                value="{{ number_format(
                                    max(0, $this->getTotalAfterRemise() - ((float) $paiement_montant ?? 0)),
                                    2
                                ) }}"
                                disabled>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="form-label">Notes (optionnel)</label>
                    <textarea class="form-control"
                            wire:model="paiement_notes"
                            rows="2"
                            placeholder="Notes additionnelles sur le paiement..."></textarea>
                </div>

                <div class="text-end mt-4">
                    <button class="btn btn-success"
                            wire:click="storePaiement"
                            @disabled($paiement_montant > $this->getTotalAfterRemise() || $paiement_montant <= 0)
                            title="{{ $paiement_montant > $this->getTotalAfterRemise() ? 'Le montant payé est trop élevé' : '' }}">
                        <i class="fas fa-check me-2"></i>Finaliser le paiement
                    </button>
                </div>

            </div>
        </div>
    @endif

    @if ($showModal)
        @include('livewire.client-modal')
    @endif

</div>

<script>
document.addEventListener('livewire:initialized', () => {
    // Add a small delay before closing dropdown to allow click on items
    Livewire.on('dropdown-close-delay', () => {
        setTimeout(() => {
            Livewire.dispatch('close-dropdown');
        }, 200);
    });

    Livewire.on('article-dropdown-close-delay', (event) => {
        setTimeout(() => {
            Livewire.dispatch('close-article-dropdown', {index: event.index});
        }, 200);
    });
});
</script>
