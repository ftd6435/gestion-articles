        <div wire:show="showCreateModal" x-transition.opacity.duration.200ms x-transition.scale.duration.200ms
            class="modal-backdrop-custom">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow-lg bg-white rounded">
                    <!-- Header -->
                    <div class="modal-header text-white p-4 bg-primary">
                        <h5 class="modal-title">
                            <i class="fas fa-user-plus me-2"></i> Nouvel utilisateur
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeCreateModal"></button>
                    </div>

                    <!-- Body -->
                    <div class="modal-body p-4">
                        <!-- Name -->
                        <div class="mb-3">
                            <label class="form-label">Nom complet <span class="text-danger">*</span></label>
                            <input type="text" wire:model="name"
                                class="form-control @error('name') is-invalid @enderror" placeholder="Ex: Fanta Diallo">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" wire:model="email"
                                class="form-control @error('email') is-invalid @enderror"
                                placeholder="Ex: fanta@gmail.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Telephone -->
                        <div class="mb-3">
                            <label class="form-label">Téléphone</label>
                            <input type="text" wire:model="telephone"
                                class="form-control @error('telephone') is-invalid @enderror"
                                placeholder="Ex: 620000000">
                            @error('telephone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Role -->
                        <div class="mb-3">
                            <label class="form-label">Rôle <span class="text-danger">*</span></label>
                            <select wire:model="role" class="form-select @error('role') is-invalid @enderror">
                                <option value="user">Utilisateur</option>
                                @if ($currentUser->isSuperAdmin())
                                    <option value="admin">Administrateur</option>
                                    <option value="super_admin">Super Administrateur</option>
                                @endif
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label class="form-label">Mot de passe <span class="text-danger">*</span></label>
                            <input type="password" wire:model="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="Minimum 8 caractères">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password Confirmation -->
                        <div class="mb-3">
                            <label class="form-label">Confirmer le mot de passe <span
                                    class="text-danger">*</span></label>
                            <input type="password" wire:model="password_confirmation"
                                class="form-control @error('password_confirmation') is-invalid @enderror"
                                placeholder="Répétez le mot de passe">
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="modal-footer p-4">
                        <button class="btn btn-light me-2" wire:click="closeCreateModal">Annuler</button>
                        <button class="btn btn-primary" wire:click="createUser" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="createUser">Créer</span>
                            <span wire:loading wire:target="createUser">
                                <span class="spinner-border spinner-border-sm me-1" role="status"
                                    aria-hidden="true"></span>
                                Création...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
