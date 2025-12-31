<div
    wire:show="showModal"
    x-transition.opacity.duration.200ms
    x-transition.scale.duration.200ms
    class="modal-backdrop-custom"
>

    <div
        class="modal-dialog modal-dialog-centered"
        x-transition
    >
        <div class="modal-content shadow-lg bg-white rounded">

            <!-- Header -->
            <div class="modal-header text-white p-4 mb-3 bg-primary"
                 style="background: var(--primary-color);">
                <h5 class="modal-title">
                    {{ $fournisseurId ? 'Modifier le fournisseur' : 'Nouveau fournisseur' }}
                </h5>
                <button
                    type="button"
                    class="btn-close btn-close-white"
                    wire:click="closeModal">
                </button>
            </div>

            <!-- Body -->
            <div class="modal-body p-4">

                <!-- Nom -->
                <div class="mb-3">
                    <label class="form-label">Nom <span class="text-danger">*</span></label>
                    <input type="text"
                           wire:model.defer="name"
                           class="form-control @error('name') is-invalid @enderror" placeholder="Ex: Ibrahim Diallo">

                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Téléphone -->
                <div class="mb-3">
                    <label class="form-label">Téléphone <span class="text-danger">*</span></label>
                    <input type="text"
                           wire:model.defer="telephone"
                           class="form-control @error('telephone') is-invalid @enderror" placeholder="Ex: 620000000">

                    @error('telephone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Adresse -->
                <div class="mb-3">
                    <label class="form-label">Adresse</label>
                    <input type="text"
                           wire:model.defer="adresse"
                           class="form-control @error('adresse') is-invalid @enderror" placeholder="Ex: Labé">

                    @error('adresse')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Status -->
                <div class="form-check form-switch mt-3">
                    <input class="form-check-input" type="checkbox" wire:model="status">
                    <label class="form-check-label">
                        Fournisseur actif
                    </label>
                </div>

            </div>

            <!-- Footer -->
            <div class="modal-footer p-4">
                <button class="btn btn-light me-2" wire:click="closeModal">
                    Annuler
                </button>
                <button class="btn btn-primary" wire:click="store">
                    Enregistrer
                </button>
            </div>

        </div>
    </div>
</div>
