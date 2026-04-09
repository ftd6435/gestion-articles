<div>
    <!-- Header Section with Add Button -->
    <div class="mb-4">
        <div class="row g-3 align-items-center">
            <!-- Title and Description -->
            <div class="col-lg-6">
                <div class="d-flex align-items-center">
                    <h4 class="mb-0 me-3">Gestion des Utilisateurs</h4>
                </div>
                <p class="text-muted mb-0 mt-2">Créez et gérez les comptes utilisateurs et leurs permissions</p>
            </div>

            <!-- Actions -->
            <div class="col-lg-6">
                <div class="d-flex justify-content-end align-items-center gap-3">
                    <!-- Quick Stats -->
                    <div class="d-flex gap-3 me-2">
                        <div class="text-center">
                            <div class="text-primary fw-bold fs-4">{{ $users->total() }}</div>
                            <div class="text-muted small">Total</div>
                        </div>
                        @php
                            $activeUsers = $users->where('status', true)->count();
                            $inactiveUsers = $users->where('status', false)->count();
                        @endphp
                        <div class="text-center">
                            <div class="text-success fw-bold fs-4">{{ $activeUsers }}</div>
                            <div class="text-muted small">Actifs</div>
                        </div>
                        <div class="text-center">
                            <div class="text-secondary fw-bold fs-4">{{ $inactiveUsers }}</div>
                            <div class="text-muted small">Inactifs</div>
                        </div>
                    </div>

                    <!-- Add Button -->
                    <div>
                        @access('settings.users', 'create')
                            <button class="btn btn-primary px-4 py-2 d-flex align-items-center"
                                wire:click="openCreateModal">
                                <i class="fas fa-user-plus me-2"></i>
                                <span>Nouvel utilisateur</span>
                            </button>
                        @endaccess
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control"
                            placeholder="Rechercher par nom, email ou téléphone...">
                    </div>
                </div>
                <div class="col-md-6 d-flex justify-content-end">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted">Afficher:</span>
                        <select wire:model.live="perPage" class="form-select form-select-sm w-auto">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nom & Email</th>
                            <th>Téléphone</th>
                            <th>Rôle</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if ($user->image)
                                            <img class="rounded-circle me-3" src="{{ asset($user->image) }}"
                                                alt="{{ $user->name }}" width="40" height="40">
                                        @else
                                            <div class="rounded-circle bg-primary bg-gradient d-flex align-items-center justify-content-center me-3"
                                                style="width: 40px; height: 40px;">
                                                <span
                                                    class="text-white fw-semibold">{{ substr($user->name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-semibold">{{ $user->name }}</div>
                                            <div class="text-muted small">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $user->telephone ?? 'N/A' }}</td>
                                <td>
                                    <span
                                        class="badge
                                        {{ $user->role === 'super_admin' ? 'bg-primary' : ($user->role === 'admin' ? 'bg-secondary' : 'bg-danger') }}">
                                        {{ $user->role === 'super_admin' ? 'Super Admin' : ($user->role === 'admin' ? 'Administrateur' : 'Utilisateur') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $user->status ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $user->status ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                                <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        <!-- Enable/Disable Button -->
                                        @access('settings.users', 'toggle_status')
                                            @if ($user->id !== $currentUser->id)
                                                <button wire:click="confirmStatusChange({{ $user->id }})"
                                                    class="btn btn-sm {{ $user->status ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                                    title="{{ $user->status ? 'Désactiver' : 'Activer' }}">
                                                    <i
                                                        class="fas {{ $user->status ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                                </button>
                                            @endif
                                        @endaccess

                                        <!-- Change Role Button (Only for super_admin) -->
                                        @if ($currentUser->isSuperAdmin())
                                            @if (!$user->isSuperAdmin() || $user->id === $currentUser->id)
                                                <button
                                                    wire:click="confirmRoleChange('{{ $user->id }}', '{{ $user->role }}')"
                                                    class="btn btn-sm btn-outline-primary" title="Modifier le rôle">
                                                    <i class="fas fa-user-tag"></i>
                                                </button>
                                            @endif
                                        @endif

                                        @if ($currentUser->isSuperAdmin())
                                            <button wire:click="openAccessModal({{ $user->id }})"
                                                class="btn btn-sm btn-outline-secondary" title="Gérer les accès">
                                                <i class="fas fa-key"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-users fa-2x mb-3"></i>
                                        <p class="mb-1">Aucun utilisateur trouvé</p>
                                        <p class="small">Essayez de modifier vos critères de recherche</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if ($users->hasPages())
            <div class="card-footer">
                {{ $users->links(data: ['scrollTo' => false]) }}
            </div>
        @endif
    </div>

    <!-- Create User Modal -->
    @if ($showCreateModal)
        @include('livewire.user-create-modal')
    @endif

    @if ($showAccessModal)
        @php
            $grouped = collect($accessMatrix)->groupBy('group', true);
            $userForAccess = \App\Models\User::find($accessUserId);
            $selectedGroup = $accessGroup ?: ($grouped->keys()->first() ?? '');
            $itemsForGroup = $selectedGroup ? ($grouped->get($selectedGroup) ?? collect()) : collect();
        @endphp
        <div class="modal-backdrop-custom">
            <div class="modal-dialog modal-dialog-centered bg-white" style="max-width: 980px;">
                <div class="modal-content shadow-lg bg-white rounded">
                    <div class="modal-header p-4 bg-primary">
                        <h5 class="modal-title text-white">
                            <i class="fas fa-key me-2"></i> Accès utilisateur
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeAccessModal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                            <div>
                                <div class="fw-semibold">{{ $userForAccess?->name ?? '—' }}</div>
                                <div class="text-muted small">{{ $userForAccess?->email ?? '' }}</div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted small">Catégorie</span>
                                <select class="form-select form-select-sm w-auto" wire:model.live="accessGroup">
                                    @foreach ($grouped->keys() as $group)
                                        <option value="{{ $group }}">{{ $group }} ({{ $grouped->get($group)?->count() ?? 0 }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive" style="max-height: 60vh; overflow: auto;">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Module</th>
                                        <th class="text-center">Voir</th>
                                        <th class="text-center">Créer</th>
                                        <th class="text-center">Modifier</th>
                                        <th class="text-center">Supprimer</th>
                                        <th class="text-center">Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($itemsForGroup as $key => $row)
                                        <tr>
                                            <td class="fw-semibold">{{ $row['label'] ?? $key }}</td>
                                            <td class="text-center">
                                                <input class="form-check-input" type="checkbox"
                                                    wire:model="accessMatrix.{{ $key }}.view">
                                            </td>
                                            <td class="text-center">
                                                <input class="form-check-input" type="checkbox"
                                                    wire:model="accessMatrix.{{ $key }}.create">
                                            </td>
                                            <td class="text-center">
                                                <input class="form-check-input" type="checkbox"
                                                    wire:model="accessMatrix.{{ $key }}.update">
                                            </td>
                                            <td class="text-center">
                                                <input class="form-check-input" type="checkbox"
                                                    wire:model="accessMatrix.{{ $key }}.delete">
                                            </td>
                                            <td class="text-center">
                                                <input class="form-check-input" type="checkbox"
                                                    wire:model="accessMatrix.{{ $key }}.toggle_status">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer p-4">
                        <button class="btn btn-light me-2" type="button"
                            wire:click="closeAccessModal">Annuler</button>
                        <button class="btn btn-primary" type="button" wire:click="saveAccess">Enregistrer</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Confirmation Modal for Status Change -->
    @if ($confirmingStatusChange)
        @php
            $user = \App\Models\User::find($confirmingStatusChange);
        @endphp
        <div x-transition.opacity.duration.200ms x-transition.scale.duration.200ms class="modal-backdrop-custom">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow-lg bg-white rounded">
                    <div class="modal-header p-4 {{ $user && $user->status ? 'bg-warning' : 'bg-success' }}">
                        <h5 class="modal-title text-white">
                            <i class="fas fa-exclamation-triangle me-2"></i> Confirmation
                        </h5>
                        <button type="button" class="btn-close btn-close-white"
                            wire:click="cancelConfirmation"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="mb-0">
                            Êtes-vous sûr de vouloir
                            <strong>{{ $user && $user->status ? 'désactiver' : 'activer' }}</strong>
                            le compte de <strong>{{ $user->name ?? '' }}</strong> ?
                        </p>
                        <p class="text-muted small mt-2 mb-0">
                            {{ $user && $user->status ? 'L\'utilisateur ne pourra plus se connecter.' : 'L\'utilisateur pourra à nouveau se connecter.' }}
                        </p>
                    </div>
                    <div class="modal-footer p-4">
                        <button class="btn btn-light me-2" wire:click="cancelConfirmation">Annuler</button>
                        <button class="btn {{ $user && $user->status ? 'btn-warning' : 'btn-success' }}"
                            wire:click="toggleUserStatus({{ $confirmingStatusChange }})">
                            {{ $user && $user->status ? 'Désactiver' : 'Activer' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Confirmation Modal for Role Change -->
    @if ($confirmingRoleChange)
        @php
            $user = \App\Models\User::find($confirmingRoleChange);
        @endphp
        <div x-transition.opacity.duration.200ms x-transition.scale.duration.200ms class="modal-backdrop-custom">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow-lg bg-white rounded">
                    <div class="modal-header p-4 bg-primary">
                        <h5 class="modal-title text-white">
                            <i class="fas fa-user-tag me-2"></i> Modifier le rôle
                        </h5>
                        <button type="button" class="btn-close btn-close-white"
                            wire:click="cancelConfirmation"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="mb-3">
                            Sélectionnez le nouveau rôle pour <strong>{{ $user->name ?? '' }}</strong>
                        </p>
                        <select wire:model="newRole" class="form-select @error('newRole') is-invalid @enderror">
                            <option value="">Sélectionnez un rôle</option>
                            <option value="admin">Administrateur</option>
                            <option value="user">Utilisateur</option>
                        </select>
                        @error('newRole')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <p class="text-muted small mt-3 mb-0">
                            <i class="fas fa-info-circle me-1"></i> Seul un super administrateur peut modifier les
                            rôles.
                        </p>
                    </div>
                    <div class="modal-footer p-4">
                        <button class="btn btn-light me-2" wire:click="cancelConfirmation">Annuler</button>
                        <button class="btn btn-primary" wire:click="updateUserRole({{ $confirmingRoleChange }})"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="updateUserRole">Mettre à jour</span>
                            <span wire:loading wire:target="updateUserRole">
                                <span class="spinner-border spinner-border-sm me-1" role="status"
                                    aria-hidden="true"></span>
                                Mise à jour...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
