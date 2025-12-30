<div wire:show="showPaiementModal"
            x-transition.opacity.duration.200ms
            x-transition.scale.duration.200ms
            class="modal-backdrop-custom"
        >
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable a4-modal">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-primary text-white p-4">
                        <h5 class="modal-title">
                            <i class="fas fa-money-bill-wave me-2"></i>Enregistrer un paiement
                        </h5>
                        <button type="button" class="btn-close btn-close-white"
                                wire:click="closePaiementModal"></button>
                    </div>

                    <div class="modal-body bg-white p-4">
                        @php
                            $total = $selectedVente->totalAfterRemise();
                            $paid = $selectedVente->paiements()->sum('montant');
                            $remaining = max(0, $total - $paid);
                            $currency = $selectedVente->devise?->symbole ?? $selectedVente->devise?->code ?? 'FC';
                        @endphp

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card border-0 bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted">Total à payer</h6>
                                        <h4 class="fw-bold">{{ number_format($total, 0, ',', ' ') }} {{ $currency }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-0 bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted">Déjà payé</h6>
                                        <h4 class="fw-bold text-success">{{ number_format($paid, 0, ',', ' ') }} {{ $currency }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-0 bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted">Reste à payer</h6>
                                        <h4 class="fw-bold text-danger">{{ number_format($remaining, 0, ',', ' ') }} {{ $currency }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form wire:submit.prevent="storePaiement">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Date paiement *</label>
                                    <input type="date"
                                           class="form-control @error('paiement_date') is-invalid @enderror"
                                           wire:model="paiement_date">
                                    @error('paiement_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Mode paiement *</label>
                                    <select class="form-select @error('mode_paiement') is-invalid @enderror"
                                            wire:model="mode_paiement">
                                        <option value="ESPECES">Espèces</option>
                                        <option value="VIREMENT">Virement</option>
                                        <option value="MOBILE MONEY">Mobile Money</option>
                                    </select>
                                    @error('mode_paiement')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Montant à payer *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">{{ $currency }}</span>
                                        <input type="number"
                                               class="form-control @error('paiement_montant') is-invalid @enderror"
                                               wire:model.live.debounce.300ms="paiement_montant"
                                               step="0.01"
                                               min="0"
                                               max="{{ $remaining }}">
                                        @error('paiement_montant')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    @if($paiement_montant > $remaining)
                                        <div class="text-danger small mt-1">
                                            Le montant ne peut pas dépasser {{ number_format($remaining, 2) }} {{ $currency }}
                                        </div>
                                    @endif
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Notes (optionnel)</label>
                                    <textarea class="form-control"
                                              wire:model="paiement_notes"
                                              rows="3"
                                              placeholder="Référence de transaction, numéro de reçu..."></textarea>
                                </div>
                            </div>

                            <div class="modal-footer bg-white border-top border-secondary mt-4 pt-4">
                                <button type="button" class="btn btn-secondary me-2"
                                        wire:click="closePaiementModal">
                                    Annuler
                                </button>
                                <button type="submit"
                                        class="btn btn-primary"
                                        @disabled($paiement_montant <= 0 || $paiement_montant > $remaining)
                                        title="{{ $paiement_montant > $remaining ? 'Montant trop élevé' : '' }}">
                                    <i class="fas fa-check me-2"></i>Enregistrer le paiement
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
