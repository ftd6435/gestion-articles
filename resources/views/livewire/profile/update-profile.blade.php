<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
            <!-- Message de succès -->
            @include('components.shared.alerts')

            <div class="card shadow-sm border-0 overflow-hidden">
                <!-- En-tête du profil -->
                <div class="card-header bg-primary text-white py-4">
                    <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            @if($imagePreview)
                                <div class="profile-image-container">
                                    <img src="{{ $imagePreview }}"
                                         alt="Photo de profil"
                                         class="rounded-circle border border-3 border-white"
                                         style="width: 80px; height: 80px; object-fit: cover;">
                                </div>
                            @else
                                <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center"
                                     style="width: 80px; height: 80px;">
                                    <i class="fas fa-user fa-2x"></i>
                                </div>
                            @endif
                            <div>
                                <h1 class="h3 mb-1">{{ $user->name }}</h1>
                                <p class="mb-0 opacity-75">
                                    <i class="fas fa-envelope me-1"></i>{{ $user->email }}
                                </p>
                            </div>
                        </div>

                        <!-- Compteur de modifications de mot de passe -->
                        <div class="text-center text-md-end">
                            <div class="badge bg-white text-primary fs-6 px-3 py-2">
                                <i class="fas fa-key me-1"></i>
                                <span class="fw-bold">{{ $remainingUpdates }}</span>/3 modifications restantes
                            </div>
                            @if($lastPasswordReset)
                                <p class="small text-white-75 mt-1 mb-0">
                                    Dernière modification : {{ $lastPasswordReset }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Formulaire de profil -->
                <div class="card-body p-4">
                    <form wire:submit.prevent="updateProfile">
                        <div class="row g-4">
                            <!-- Colonne de gauche - Informations personnelles -->
                            <div class="col-12 col-md-6">
                                <h5 class="mb-4 border-bottom pb-2">
                                    <i class="fas fa-user-circle me-2 text-primary"></i>Informations Personnelles
                                </h5>

                                <!-- Section photo de profil -->
                                <div class="mb-4">
                                    <label class="form-label fw-medium">Photo de Profil</label>
                                    <div class="d-flex flex-column flex-sm-row align-items-start gap-3">
                                        <!-- Aperçu de l'image -->
                                        <div class="position-relative">
                                            @if($imagePreview)
                                                <img src="{{ $imagePreview }}"
                                                     alt="Aperçu de la photo"
                                                     class="rounded-circle border shadow-sm"
                                                     style="width: 120px; height: 120px; object-fit: cover;">
                                                <button type="button"
                                                        wire:click="removeImage"
                                                        class="btn btn-danger btn-sm position-absolute top-0 end-0 rounded-circle"
                                                        style="width: 32px; height: 32px; transform: translate(30%, -30%);"
                                                        title="Supprimer la photo">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @else
                                                <div class="bg-light text-secondary rounded-circle d-flex align-items-center justify-content-center border"
                                                     style="width: 120px; height: 120px;">
                                                    <i class="fas fa-user fa-3x"></i>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Bouton de téléchargement simple -->
                                        <div class="flex-grow-1">
                                            <!-- Bouton avec icône centrée -->
                                            <div class="mb-3">
                                                <div class="upload-btn-container">
                                                    <button type="button"
                                                            class="btn btn-outline-primary btn-upload w-100 py-4"
                                                            onclick="document.getElementById('fileInput').click()"
                                                            title="Télécharger une photo">
                                                        <div class="upload-icon-container">
                                                            <i class="fas fa-cloud-upload-alt fa-2x"></i>
                                                        </div>
                                                    </button>
                                                    <input type="file"
                                                           id="fileInput"
                                                           class="d-none"
                                                           wire:model="image"
                                                           accept="image/*">
                                                </div>
                                                <p class="small text-muted text-center mt-2">
                                                    Cliquez pour télécharger une photo
                                                    <br>
                                                    <span class="text-muted">JPG, PNG, GIF (Max 2MB)</span>
                                                </p>
                                            </div>

                                            <!-- Information de l'image sélectionnée -->
                                            @if($image)
                                                <div class="selected-file-info alert alert-info mt-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-image me-3"></i>
                                                        <div class="flex-grow-1">
                                                            <p class="mb-0 fw-medium">{{ $image->getClientOriginalName() }}</p>
                                                            <p class="mb-0 small">{{ round($image->getSize() / 1024, 2) }} KB</p>
                                                        </div>
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-danger"
                                                                wire:click="removeImage">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    @error('image')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Nom complet -->
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-medium">
                                        <i class="fas fa-signature me-1 text-muted"></i>Nom Complet
                                    </label>
                                    <input type="text"
                                           class="form-control @error('name') is-invalid @enderror"
                                           id="name"
                                           wire:model="name"
                                           placeholder="Votre nom complet">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Adresse email -->
                                <div class="mb-3">
                                    <label for="email" class="form-label fw-medium">
                                        <i class="fas fa-envelope me-1 text-muted"></i>Adresse Email
                                    </label>
                                    <input type="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           id="email"
                                           wire:model="email"
                                           placeholder="votre@email.com">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Numéro de téléphone -->
                                <div class="mb-4">
                                    <label for="telephone" class="form-label fw-medium">
                                        <i class="fas fa-phone me-1 text-muted"></i>Numéro de Téléphone
                                    </label>
                                    <input type="tel"
                                           class="form-control @error('telephone') is-invalid @enderror"
                                           id="telephone"
                                           wire:model="telephone"
                                           placeholder="Votre numéro de téléphone">
                                    @error('telephone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Colonne de droite - Sécurité -->
                            <div class="col-12 col-md-6">
                                <h5 class="mb-4 border-bottom pb-2">
                                    <i class="fas fa-lock me-2 text-primary"></i>Paramètres de Sécurité
                                </h5>

                                <!-- Alerte limite de modification -->
                                @if($remainingUpdates <= 0)
                                    <div class="alert alert-warning mb-4">
                                        <div class="d-flex">
                                            <i class="fas fa-exclamation-triangle fa-lg me-3"></i>
                                            <div>
                                                <strong>Limite de Modification Atteinte</strong>
                                                <p class="mb-0 small">Vous avez utilisé vos 3 modifications de mot de passe ce mois-ci. Réessayez le mois prochain.</p>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-info mb-4">
                                        <div class="d-flex">
                                            <i class="fas fa-info-circle fa-lg me-3"></i>
                                            <div>
                                                <strong>Limite de Modification : {{ $remainingUpdates }}/3 restantes</strong>
                                                <p class="mb-0 small">Vous pouvez modifier votre mot de passe {{ $remainingUpdates }} fois ce mois-ci.</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Mot de passe actuel -->
                                <div class="mb-3">
                                    <label for="current_password" class="form-label fw-medium">
                                        <i class="fas fa-key me-1 text-muted"></i>Mot de Passe Actuel
                                    </label>
                                    <div class="input-group">
                                        <input type="password"
                                               class="form-control @error('current_password') is-invalid @enderror"
                                               id="current_password"
                                               wire:model="current_password"
                                               placeholder="Saisissez votre mot de passe actuel"
                                               {{ $remainingUpdates <= 0 ? 'disabled' : '' }}>
                                        <button class="btn btn-outline-secondary"
                                                type="button" onclick="toggleProfilePassword('current_password', this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    @error('current_password')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Nouveau mot de passe -->
                                <div class="mb-3">
                                    <label for="new_password" class="form-label fw-medium">
                                        <i class="fas fa-lock me-1 text-muted"></i>Nouveau Mot de Passe
                                    </label>
                                    <div class="input-group">
                                        <input type="password"
                                               class="form-control @error('new_password') is-invalid @enderror"
                                               id="new_password"
                                               wire:model="new_password"
                                               placeholder="Saisissez votre nouveau mot de passe"
                                               {{ $remainingUpdates <= 0 ? 'disabled' : '' }}>
                                        <button class="btn btn-outline-secondary"
                                                type="button" onclick="toggleProfilePassword('new_password', this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    @error('new_password')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Confirmation du nouveau mot de passe -->
                                <div class="mb-4">
                                    <label for="new_password_confirmation" class="form-label fw-medium">
                                        <i class="fas fa-lock me-1 text-muted"></i>Confirmer le Nouveau Mot de Passe
                                    </label>
                                    <div class="input-group">
                                        <input type="password"
                                               class="form-control"
                                               id="new_password_confirmation"
                                               wire:model="new_password_confirmation"
                                               placeholder="Confirmez votre nouveau mot de passe"
                                               {{ $remainingUpdates <= 0 ? 'disabled' : '' }}>
                                        <button class="btn btn-outline-secondary"
                                                type="button" onclick="toggleProfilePassword('new_password_confirmation', this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Exigences du mot de passe -->
                                <div class="card bg-light border-0 mb-4">
                                    <div class="card-body p-3">
                                        <p class="small mb-2 fw-medium">Exigences du Mot de Passe :</p>
                                        <ul class="small mb-0 text-muted" style="list-style: none; padding-left: 0;">
                                            <li class="mb-1">
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                Minimum 8 caractères
                                            </li>
                                            <li class="mb-1">
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                Lettres majuscules et minuscules
                                            </li>
                                            <li class="mb-1">
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                Chiffres et symboles
                                            </li>
                                            <li>
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                Pas d'espaces
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bouton de soumission -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 border-top pt-4">
                                    <div class="text-center text-sm-start">
                                        <p class="small text-muted mb-0">
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            Le compteur de modifications se réinitialise mensuellement
                                        </p>
                                    </div>
                                    <button type="submit"
                                            class="btn btn-primary px-5 py-3 fw-medium"
                                            wire:loading.attr="disabled"
                                            wire:target="updateProfile,image">
                                        <span wire:loading.remove wire:target="updateProfile">
                                            <i class="fas fa-save me-2"></i>Mettre à jour le Profil
                                        </span>
                                        <span wire:loading wire:target="updateProfile">
                                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                            Mise à jour en cours...
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Carte d'informations supplémentaires -->
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-4 text-center">
                            <div class="p-3 bg-primary bg-opacity-10 rounded">
                                <i class="fas fa-user-clock fa-2x text-primary mb-2"></i>
                                <h6 class="fw-bold mb-1">Membre Depuis</h6>
                                <p class="mb-0 text-muted small">
                                    {{ $user->created_at->translatedFormat('d M Y') }}
                                </p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 text-center">
                            <div class="p-3 bg-warning bg-opacity-10 rounded">
                                <i class="fas fa-shield-alt fa-2x text-warning mb-2"></i>
                                <h6 class="fw-bold mb-1">Sécurité du Compte</h6>
                                <p class="mb-0 text-muted small">
                                    {{ $passwordUpdateCount }} modification(s) ce mois-ci
                                </p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 text-center">
                            <div class="p-3 bg-info bg-opacity-10 rounded">
                                <i class="fas fa-sync-alt fa-2x text-info mb-2"></i>
                                <h6 class="fw-bold mb-1">Dernière Mise à Jour</h6>
                                <p class="mb-0 text-muted small">
                                    {{ $user->updated_at->diffForHumans(['locale' => 'fr']) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // CORRECTION : Fonction togglePassword corrigée
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion du fichier image
        const fileInput = document.getElementById('fileInput');
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    console.log('Fichier sélectionné :', this.files[0].name);
                }
            });
        }
    });
</script>
@endpush

@push('styles')
<style>
    .profile-image-container {
        position: relative;
        transition: transform 0.3s ease;
    }

    .profile-image-container:hover {
        transform: scale(1.05);
    }

    .card-header {
        background: linear-gradient(135deg, #4e54c8, #8f94fb);
    }

    .form-control:focus {
        border-color: #8f94fb;
        box-shadow: 0 0 0 0.25rem rgba(142, 148, 251, 0.25);
    }

    .btn-primary {
        background: linear-gradient(135deg, #4e54c8, #8f94fb);
        border: none;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #4348b1, #7a80e8);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(142, 148, 251, 0.3);
    }

    /* Styles pour le bouton de téléchargement */
    .upload-btn-container {
        position: relative;
        overflow: hidden;
    }

    .btn-upload {
        border: 2px solid #8f94fb;
        background-color: #f8f9ff;
        transition: all 0.3s ease;
        height: 140px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }

    .btn-upload:hover {
        background-color: #eef1ff;
        border-color: #4e54c8;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(142, 148, 251, 0.2);
    }

    .btn-upload:active {
        transform: translateY(0);
    }

    .upload-icon-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #4e54c8;
        transition: transform 0.3s ease;
    }

    .btn-upload:hover .upload-icon-container {
        transform: scale(1.1);
    }

    .selected-file-info {
        animation: fadeIn 0.5s ease;
        border-left: 4px solid #4e54c8;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 768px) {
        .card-header {
            text-align: center;
        }

        .card-header .d-flex {
            flex-direction: column;
        }

        .btn-upload {
            height: 120px;
        }
    }

    @media (max-width: 576px) {
        .btn-upload {
            height: 100px;
        }

        .upload-icon-container i {
            font-size: 1.5rem;
        }
    }
</style>
@endpush
