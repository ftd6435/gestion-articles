<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold">
            <i class="fas fa-cart-plus me-2"></i>Nouvelle Vente
        </h4>

        <span class="badge bg-primary fs-6">
            {{ $reference }}
        </span>
    </div>

    @include('components.shared.alerts')

    @if (!$showPaiementForm)
        <form wire:submit.prevent="store">

            {{-- Infos --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Client *</label>
                        <select class="form-select @error('client_id') is-invalid @enderror" wire:model="client_id">
                            <option value="">Sélectionner</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }} - {{ $client->telephone }}</option>
                            @endforeach
                        </select>
                        @error('client_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" wire:model="date_facture">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Type</label>
                        <select class="form-select" wire:model="type_vente">
                            <option value="GROS">Gros</option>
                            <option value="DETAIL">Detail</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Devise *</label>
                        <select class="form-select @error('devise_id') is-invalid @enderror" wire:model="devise_id">
                            <option value="">Sélectionner</option>
                            @foreach($devises as $devise)
                                <option value="{{ $devise->id }}">
                                    {{ $devise->symbole ?? $devise->code }}
                                </option>
                            @endforeach
                        </select>
                        @error('devise_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Remise (%)</label>
                        <input type="number"
                            class="form-control @error('remise') is-invalid @enderror"
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

            {{-- Articles --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">

                    <h6 class="fw-bold mb-3">Articles vendus</h6>

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
                                    <tr>
                                        <td>{{ $index + 1 }}</td>

                                        {{-- Article --}}
                                        <td>
                                            <select class="form-select form-select-sm @error('lignes.' . $index . '.article_id') is-invalid @enderror"
                                                    wire:model.live.debounce.300ms="lignes.{{ $index }}.article_id">
                                                <option value="">---</option>
                                                @foreach($articles as $article)
                                                    <option value="{{ $article->id }}">
                                                        {{ $article->designation }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('lignes.' . $index . '.article_id')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </td>

                                        {{-- Etagere --}}
                                        <td>
                                            <select class="form-select form-select-sm @error('lignes.' . $index . '.etagere_id') is-invalid @enderror"
                                                    wire:model.live.debounce.300ms="lignes.{{ $index }}.etagere_id">
                                                <option value="">---</option>
                                                @foreach($this->etageres[$index] ?? [] as $etagere)
                                                    @if ($etagere['available'] > 0)
                                                        <option value="{{ $etagere['id'] }}">
                                                            {{ $etagere['code'] }}
                                                            ({{ $etagere['magasin'] }})
                                                            - Dis: {{ $etagere['available'] }}
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
                                                class="form-control form-control-sm text-end @error('lignes.' . $index . '.quantity') is-invalid @enderror @if($ligne['quantity'] > $ligne['available']) border-danger @endif"
                                                wire:model.live.debounce.300ms="lignes.{{ $index }}.quantity"
                                                min="1">
                                            @if($ligne['quantity'] > $ligne['available'])
                                                <div class="text-danger small mt-1">
                                                    Stock disponible: {{ $ligne['available'] }}
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
                                        <td class="text-end fw-bold">
                                            {{ number_format(
                                                ((float) $ligne['quantity'] ?? 0) * ((float) $ligne['unit_price'] ?? 0),
                                                2
                                            ) }}
                                        </td>

                                        {{-- Delete --}}
                                        <td class="text-end">
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
                                        {{ number_format($this->subTotal(), 2) }}
                                    </th>
                                    <th></th>
                                </tr>

                                <tr>
                                    <th colspan="5" class="text-end">Remise ({{ $remise }}%)</th>
                                    <th class="text-end text-danger">
                                        - {{ number_format($this->remiseAmount(), 2) }}
                                    </th>
                                    <th></th>
                                </tr>

                                <tr>
                                    <th colspan="5" class="text-end fw-bold">Total à payer</th>
                                    <th class="text-end fw-bold text-success">
                                        {{ number_format($this->totalAfterRemise(), 2) }}
                                    </th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <button type="button" class="btn btn-outline-primary mt-3"
                            wire:click="addLine">
                        <i class="fas fa-plus me-2"></i>Ajouter ligne
                    </button>
                </div>
            </div>

            {{-- Footer --}}
            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('ventes.ventes') }}" class="btn btn-secondary">
                    Annuler
                </a>
                <button type="submit" class="btn btn-success"
                        @disabled(
                            !$client_id || !$devise_id ||
                            collect($lignes)->isEmpty() ||
                            collect($lignes)->contains(fn($l) =>
                                $l['quantity'] > $l['available']
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
                                <tr>
                                    <td>{{ $articles->firstWhere('id', $l['article_id'])->designation ?? '' }}</td>
                                    <td class="text-end">{{ $l['quantity'] }}</td>
                                    <td class="text-end">{{ number_format($l['unit_price'], 2) }}</td>
                                    <td class="text-end">
                                        {{ number_format($l['quantity'] * $l['unit_price'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="3" class="text-end">Total</th>
                                <th class="text-end">{{ number_format($this->subTotal(), 2) }}</th>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end">Remise</th>
                                <th class="text-end text-danger">
                                    - {{ number_format($this->remiseAmount(), 2) }}
                                </th>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end fw-bold">Net à payer</th>
                                <th class="text-end fw-bold text-success">
                                    {{ number_format($this->totalAfterRemise(), 2) }}
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
                            max="{{ $this->totalAfterRemise() }}">
                        @error('paiement_montant')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if($paiement_montant > $this->totalAfterRemise())
                            <div class="text-danger small mt-1">
                                Le montant payé ne peut pas dépasser {{ number_format($this->totalAfterRemise(), 2) }}
                            </div>
                        @endif
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Payé</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                {{ $devises->firstWhere('id', $devise_id)->symbole ?? '' }}
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
                                {{ $devises->firstWhere('id', $devise_id)->symbole ?? '' }}
                            </span>
                            <input class="form-control bg-light text-danger fw-bold"
                                value="{{ number_format(
                                    max(0, $this->totalAfterRemise() - ((float) $paiement_montant ?? 0)),
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
                            @disabled($paiement_montant > $this->totalAfterRemise() || $paiement_montant <= 0)
                            title="{{ $paiement_montant > $this->totalAfterRemise() ? 'Le montant payé est trop élevé' : '' }}">
                        <i class="fas fa-check me-2"></i>Finaliser le paiement
                    </button>
                </div>

            </div>
        </div>
    @endif

</div>
