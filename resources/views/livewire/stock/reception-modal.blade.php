<div
    wire:show="showModal"
    x-transition.opacity.duration.200ms
    x-transition.scale.duration.200ms
    class="modal-backdrop-custom">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg bg-white rounded">

            <!-- Header -->
            <div class="modal-header text-white p-4 bg-primary">
                <h5 class="modal-title">
                    {{ $receptionId ? 'Modifier la réception' : 'Nouvelle réception' }}
                </h5>
                <button class="btn-close btn-close-white"
                    wire:click="closeModal"></button>
            </div>

            <!-- Body -->
            <div class="modal-body p-4">

                <!-- Fournisseur -->
                <div class="mb-3">
                    <label class="form-label">Commande <span class="text-danger">*</span></label>
                    <select wire:model.defer="commande_id"
                        class="form-select @error('commande_id') is-invalid @enderror">
                        <option value="">Sélectionner</option>
                        @foreach($commandes as $commande)
                        <option value="{{ $fournisseur->id }}">
                            {{ $commande->reference }} - {{ $commande->fournisseur->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('commande_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <!-- Date -->
                <div class="mb-3">
                    <label class="form-label">Date de réception</label>
                    <input type="date"
                        wire:model.defer="date_reception"
                        class="form-control">
                </div>

            </div>

            <!-- Footer -->
            <div class="modal-footer p-4">
                <button class="btn btn-light me-2" wire:click="closeModal">Annuler</button>
                <button class="btn btn-primary" wire:click="store">Enregistrer</button>
            </div>

        </div>
    </div>
</div>