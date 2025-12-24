<div
    wire:show="showModal"
    x-transition.opacity.duration.200ms
    x-transition.scale.duration.200ms
    class="modal-backdrop-custom"
>

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg bg-white rounded">

            <!-- Header -->
            <div class="modal-header text-white p-4 bg-primary">
                <h5 class="modal-title">
                    {{ $commandeId ? 'Modifier la commande' : 'Nouvelle commande' }}
                </h5>
                <button class="btn-close btn-close-white"
                        wire:click="closeModal"></button>
            </div>

            <!-- Body -->
            <div class="modal-body p-4">

                <!-- Référence -->
                <div class="mb-3">
                    <label class="form-label">Référence <span class="text-danger">*</span></label>
                    <input type="text" wire:model.defer="reference"
                           class="form-control @error('reference') is-invalid @enderror">
                    @error('reference') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <!-- Fournisseur -->
                <div class="mb-3">
                    <label class="form-label">Fournisseur <span class="text-danger">*</span></label>
                    <select wire:model.defer="fournisseur_id"
                            class="form-select @error('fournisseur_id') is-invalid @enderror">
                        <option value="">Sélectionner</option>
                        @foreach($fournisseurs as $fournisseur)
                            <option value="{{ $fournisseur->id }}">
                                {{ $fournisseur->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('fournisseur_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <!-- Devise -->
                <div class="mb-3">
                    <label class="form-label">Devise <span class="text-danger">*</span></label>
                    <select wire:model.defer="devise_id"
                            class="form-select @error('devise_id') is-invalid @enderror">
                        <option value="">Sélectionner</option>
                        @foreach($devises as $devise)
                            <option value="{{ $devise->id }}">
                                {{ $devise->code }}
                            </option>
                        @endforeach
                    </select>
                    @error('devise_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <!-- Taux & remise -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Taux de change</label>
                        <input type="number" min="0"
                               wire:model.defer="taux_change"
                               class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Remise</label>
                        <input type="number" step="0.01" min="0" max="100"
                               wire:model.defer="remise"
                               class="form-control">
                    </div>
                </div>

                <!-- Date -->
                <div class="mb-3">
                    <label class="form-label">Date de commande</label>
                    <input type="date"
                           wire:model.defer="date_commande"
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
