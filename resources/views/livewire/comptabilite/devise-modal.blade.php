<div wire:show="showModal"
            x-transition.opacity.duration.200ms
            x-transition.scale.duration.200ms
            class="modal-backdrop-custom"
        >
    <div class="modal-dialog modal-dialog-centered bg-white">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white px-3 py-2">
                <h5 class="modal-title">
                    {{ $deviseId ? 'Modifier la devise' : 'Nouvelle devise' }}
                </h5>
                <button wire:click="closeModal" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body bg-white px-3 py-2">
                <div class="mb-3">
                    <label class="form-label">Code Devise <span class="text-danger">*</span></label>
                    <input type="text" wire:model.defer="code"
                           class="form-control @error('code') is-invalid @enderror" placeholder="Ex: GNF">
                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Nom Devise <span class="text-danger">*</span></label>
                    <input type="text" wire:model.defer="libelle"
                           class="form-control @error('libelle') is-invalid @enderror" placeholder="Ex: Franc Guinéen">
                    @error('libelle') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Symbole</label>
                    <input type="text" wire:model.defer="symbole"
                           class="form-control @error('symbole') is-invalid @enderror" placeholder="Ex: FG">
                    @error('symbole') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row g-2">
                    <div class="col-6">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" wire:model="status"
                                   id="status-switch">
                            <label class="form-check-label" for="status-switch">Devise active</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" wire:model="is_default"
                                   id="default-switch"
                                   {{ !$status && !$deviseId ? 'disabled' : '' }}>
                            <label class="form-check-label" for="default-switch">Devise par défaut</label>
                        </div>
                    </div>
                </div>

                @if($is_default)
                <div class="alert alert-info small p-2 mb-3">
                    <i class="fas fa-info-circle me-1"></i>
                    En définissant cette devise comme par défaut, toute autre devise définie comme par défaut sera automatiquement désactivée.
                </div>
                @endif

                @if(!$status && $is_default)
                <div class="alert alert-warning small p-2 mb-3">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Une devise inactive ne peut pas être définie comme devise par défaut.
                </div>
                @endif
            </div>

            <div class="modal-footer bg-white px-3 py-2">
                <button wire:click="closeModal" class="btn btn-light me-2" data-bs-dismiss="modal">Annuler</button>
                @if ($deviseId)
                    @access('configuration.devises', 'update')
                        <button wire:click="store" class="btn btn-primary">
                            Enregistrer
                        </button>
                    @endaccess
                @else
                    @access('configuration.devises', 'create')
                        <button wire:click="store" class="btn btn-primary">
                            Enregistrer
                        </button>
                    @endaccess
                @endif
            </div>
        </div>
    </div>
</div>
