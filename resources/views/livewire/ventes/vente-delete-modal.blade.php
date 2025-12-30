<div wire:show="showDeleteModal"
            x-transition.opacity.duration.200ms
            x-transition.scale.duration.200ms
            class="modal-backdrop-custom"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-danger text-white p-4">
                        <h5 class="modal-title">
                            <i class="fas fa-trash-alt me-2"></i>Confirmer la suppression
                        </h5>
                        <button type="button" class="btn-close btn-close-white"
                                wire:click="closeDeleteModal"></button>
                    </div>

                    <div class="modal-body bg-white">
                        <div class="text-center mb-4 mt-4">
                            <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
                            <h5>Supprimer définitivement cette vente ?</h5>
                            <p class="text-muted">
                                Référence: <strong>{{ $selectedVente->reference }}</strong><br>
                                Client: {{ $selectedVente->client?->name ?? '—' }}<br>
                                Statut: {{ $selectedVente->status }}
                            </p>
                            <p class="text-danger">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Cette action supprimera définitivement la vente et tous ses paiements.
                                <br>Cette action est irréversible !
                            </p>
                        </div>
                    </div>

                    <div class="modal-footer p-4 bg-white border-top border-secondary">
                        <button type="button" class="btn btn-secondary me-2"
                                wire:click="closeDeleteModal">
                            Annuler
                        </button>
                        <button type="button" class="btn btn-danger"
                                wire:click="deleteVente">
                            <i class="fas fa-trash-alt me-2"></i>Supprimer définitivement
                        </button>
                    </div>
                </div>
            </div>
        </div>
