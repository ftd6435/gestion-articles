<div wire:show="showCancelModal"
            x-transition.opacity.duration.200ms
            x-transition.scale.duration.200ms
            class="modal-backdrop-custom"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-warning text-white p-4">
                        <h5 class="modal-title">
                            <i class="fas fa-exclamation-triangle me-2"></i>Confirmer l'annulation
                        </h5>
                        <button type="button" class="btn-close btn-close-white"
                                wire:click="closeCancelVente"></button>
                    </div>

                    <div class="modal-body bg-white">
                        <div class="text-center mb-4 mt-4">
                            <i class="fas fa-ban fa-3x text-warning mb-3"></i>
                            <h5>Êtes-vous sûr de vouloir annuler cette vente ?</h5>
                            <p class="text-muted">
                                Référence: <strong>{{ $selectedVente->reference }}</strong><br>
                                Client: {{ $selectedVente->client?->name ?? '—' }}<br>
                                Montant: {{ number_format($selectedVente->totalAfterRemise(), 0, ',', ' ') }} {{ $selectedVente->devise?->symbole ?? '' }}
                            </p>
                            <p class="text-danger">
                                <i class="fas fa-exclamation-circle me-1"></i>
                                Cette action est irréversible. La vente sera marquée comme annulée.
                            </p>
                        </div>
                    </div>

                    <div class="modal-footer p-4 border-top border-secondary bg-white">
                        <button type="button" class="btn btn-secondary me-2"
                                wire:click="closeCancelVente">
                            Non, annuler
                        </button>
                        <button type="button" class="btn btn-warning"
                                wire:click="cancelVente">
                            <i class="fas fa-ban me-2"></i>Oui, annuler la vente
                        </button>
                    </div>
                </div>
            </div>
        </div>
